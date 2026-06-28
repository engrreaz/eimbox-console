<?php
session_start();

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/func.php';

$prs = $_GET['prs'] ?? '';
$mode = $_GET['mode'] ?? 'print'; // print | pdf

if ($prs == '') {
    die('No receipt selected');
}

$prnos = array_map('trim', explode(',', $prs));
$in = implode(',', array_fill(0, count($prnos), '?'));
$types = str_repeat('s', count($prnos));

$sql = "
SELECT 
    sp.id, sp.prno, sp.prdate, sp.amount, sp.stid, sp.sccode,
    si.classname, si.sectionname, si.rollno,
    st.stnameeng, st.stnameben
FROM stpr sp
LEFT JOIN sessioninfo si ON si.stid = sp.stid AND si.sccode = sp.sccode AND si.sessionyear LIKE '$sessionyear_param'
LEFT JOIN students st ON st.stid = si.stid AND st.sccode = si.sccode
WHERE sp.prno IN ($in)
ORDER BY sp.prdate, sp.id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$prnos);
$stmt->execute();
$res = $stmt->get_result();

// QR Function - PDF এর জন্য QR কোড জেনারেশন অনেক সময় স্লো হয়, তাই বিকল্প ব্যবস্থা রাখা ভালো
function qrBase64($text)
{
    $url = 'https://quickchart.io/qr?size=100&text=' . urlencode($text);
    $img = @file_get_contents($url);
    if ($img === false)
        return '';
    return 'data:image/png;base64,' . base64_encode($img);
}

// Global school info (Fallback values if not set in config)
$scname ??= 'School Name';
$scaddress ??= 'School Address';
$scmobile ??= '';
$headtitle ??= 'Headmaster';

ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            margin: 0;
            font-family: 'solaimanlipi', 'dejavusans', sans-serif;
            font-size: 13px;
            color: #333;
        }

        .page-container {
            width: 100%;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .receipt-cell {
            width: 33.33%;
            vertical-align: top;
            padding: 5mm;
            border-right: 0.5pt dashed #999;
            box-sizing: border-box;
            page-break-inside: avoid;
            height: 210mm;
        }

        .receipt-cell:last-child {
            border-right: none;
        }

        .inner-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .item-table th,
        .item-table td {
            border: 0.1pt solid #444;
            padding: 4px;
            font-size: 10px;
        }

        .title-box {
            text-align: center;
            margin: 5px auto;
            width: 100%;
            text-align: center;
        }

        .title {
            border: 1pt solid #000;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: bold;
            display: inline;
            font-size: 12px;
            margin: auto;
        }

        .small {
            font-size: 9px;
            line-height: 1.2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-sign {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <?php
    $colCount = 0;
    $totalItems = $res->num_rows;
    $currentIndex = 0;

    while ($row = $res->fetch_assoc()) {
        if ($colCount % 3 == 0) {
            if ($colCount > 0)
                echo '</tr></table></div>';
            echo '<div class="page-container"><table class="receipt-table"><tr>';
        }

        $name = $row['stnameeng'] ?: $row['stnameben'];
        $qr = qrBase64("http://android.eimbox.com/stpr.php?prno=" . $row['prno']);
        $sccode_row = $row['sccode']; // Row specific sccode
    
        echo '<td class="receipt-cell">';

        // Header
        echo "<table class=\"inner-table\"><tr>
            <td width=\"40\"><img src=\"https://eimbox.com/logo/{$sccode_row}.png\" width=\"35\"></td>
            <td>
                <b style=\"font-size:12px;\">{$scname}</b><br>
                <span class=\"small\">{$scaddress}<br>Mobile: {$scmobile}</span>
            </td>
          </tr></table>";

        ?>
        <div class="title-box">
            <table width="100%">
                <tr>
                    <td align="center">
                        <div class="title">Payment Receipt</div>
                    </td>
                </tr>
            </table>
        </div>
        <?php

        echo '<table class="inner-table">
            <tr>
                <td><b>#' . $row['prno'] . '/' . $row['id'] . '</b></td>
                <td class="text-right">' . date('d M, Y', strtotime($row['prdate'])) . '</td>
            </tr>
          </table>';

        echo '<div style="margin-bottom:8px;">
            <b>' . $name . '</b><br>
            ID: ' . $row['stid'] . ' | Class: ' . $row['classname'] . '<br>
            Sec: ' . $row['sectionname'] . ' | Roll: ' . $row['rollno'] . '
          </div>';

        // Items Table
        echo '<table class="item-table">
            <thead>
                <tr><th width="20">#</th><th>Particulars</th><th width="60" class="text-right">Amount</th></tr>
            </thead>
            <tbody>';

        $fsql = "SELECT particularben, (pr1 + pr2) amt FROM stfinance WHERE sccode = ? AND (pr1no = ? OR pr2no = ?)";
        $fst = $conn->prepare($fsql);
        $fst->bind_param("sss", $sccode_row, $row['prno'], $row['prno']);
        $fst->execute();
        $fres = $fst->get_result();
        $sl = 1;
        $calc_tot = 0;

        while ($f = $fres->fetch_assoc()) {
            $calc_tot += $f['amt'];
            echo '<tr>
                <td class="text-center">' . $sl++ . '</td>
                <td>' . $f['particularben'] . '</td>
                <td class="text-right">' . number_format($f['amt'], 2) . '</td>
              </tr>';
        }

        echo '<tr>
            <th colspan="2" class="text-right">Total</th>
            <th class="text-right">' . number_format($row['amount'], 2) . '</th>
          </tr>';
        echo '</tbody></table>';

        // Amount in words
        echo '<p class="small"><i>Taka In Word: ';
        taka($row['amount']); // Ensure this function echo's text
        echo ' Only.</i></p>';

        // Footer with signatures
        echo "<table class=\"inner-table footer-sign\">
            <tr>
                <td class=\"text-center\" width=\"33%\">" . ($qr ? "<img src=\"{$qr}\" width=\"60\">" : '') . "</td>
                <td class=\"text-center\" width=\"33%\"><img src=\"https://eimbox.com/sign/{$sccode_row}.png\" height=\"30\" style=\"display:block; margin:auto;\"><br><span class=\"small\">{$headtitle}</span></td>
                <td class=\"text-center\" width=\"33%\"><img src=\"https://eimbox.com/sign/1031879988.png\" height=\"30\" style=\"display:block; margin:auto;\"><br><span class=\"small\">Class Teacher</span></td>
            </tr>
          </table>";

        echo '</td>';

        $colCount++;
        $currentIndex++;
    }

    // Fill empty cells if the last row is not full
    if ($colCount % 3 != 0) {
        for ($i = ($colCount % 3); $i < 3; $i++) {
            echo '<td class="receipt-cell"></td>';
        }
        echo '</tr></table></div>';
    } else {
        echo '</tr></table></div>';
    }
    ?>

</body>

</html>

<?php
$html = ob_get_clean();

if ($mode === 'print') {
    echo $html;
    echo "<script>window.onload=function(){window.print();};</script>";
    exit;
}

if ($mode === 'pdf') {
    require_once '../vendor/autoload.php';

    ini_set("pcre.backtrack_limit", "5000000");

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/fonts']),
        'fontdata' => $fontData + [
            'solaimanlipi' => [
                'R' => 'SolaimanLipi.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75
            ]
        ],
        'default_font' => 'solaimanlipi'
    ]);

    $mpdf->SetDisplayMode('fullpage');

    // নিচের লাইনগুলো টেবিল ফিক্স করার জন্য যথেষ্ট
    // $mpdf->packTableData = true; 
    // $mpdf->shrink_tables_to_fit = 1;

    $mpdf->WriteHTML($html);
    $mpdf->Output('Receipts_' . date('Y-m-d') . '.pdf', 'I');
    exit;
}
?>