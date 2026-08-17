<?php
session_start(); // Add this line to start the session
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
require_once 'core/functions.php';

// আপনার ডাটাবেস কানেকশন এবং অন্যান্য গ্লোবাল ভ্যারিয়েবল

// =================================================================
// 🔹 টেমপ্লেট রেন্ডারিং ফাংশন
// এই ফাংশনটি প্লেসহোল্ডার পরিবর্তন করে চূড়ান্ত টেক্সট তৈরি করবে।
// =================================================================
function render_testimonial_template($template_body, $data)
{
    $pronoun_he_she = (in_array($data['gender'], ['Male', 'Boy'])) ? 'He' : 'She';

    $placeholders = [
        '[[STUDENT_NAME]]' => $data['stnameeng'] ?? '',
        '[[STUDENT_NAME_BN]]' => $data['stnameben'] ?? '',
        '[[SON_DAUGHTER]]' => ($data['gender'] === 'Male' || $data['gender'] === 'Boy') ? 'S/O' : 'D/O',
        '[[FATHER_NAME]]' => $data['fname'] ?? '',
        '[[MOTHER_NAME]]' => $data['mname'] ?? '',
        '[[VILLAGE]]' => $data['previll'] ?? '',
        '[[POST_OFFICE]]' => $data['prepo'] ?? '',
        '[[PS]]' => $data['preps'] ?? '',
        '[[DISTRICT]]' => $data['predist'] ?? '',
        '[[PRONOUN_HE_SHE]]' => $pronoun_he_she,
        '[[PRONOUN_HE_SHE_LOWER]]' => strtolower($pronoun_he_she),
        '[[PRONOUN_HIS_HER]]' => ($pronoun_he_she === 'He') ? 'His' : 'Her',
        '[[EXAM_NAME]]' => $data['pubexam'] ?? '',
        '[[PASSING_YEAR]]' => $data['passyear'] ?? '',
        '[[BOARD_NAME]]' => 'Comilla', // এটি ডাইনামিক করা যেতে পারে
        '[[ROLL_NO]]' => $data['rollno'] ?? '',
        '[[REGD_NO]]' => $data['regdno'] ?? '',
        '[[SESSION]]' => $data['session'] ?? '',
        '[[GROUP]]' => $data['groupsection'] ?? '',
        '[[GPA]]' => $data['gpa'] ?? '',
        '[[GRADE]]' => $data['grade'] ?? '',
        '[[DOB]]' => !empty($data['dob']) ? date('d F, Y', strtotime($data['dob'])) : '',
    ];

    $rendered_text = str_replace(array_keys($placeholders), array_values($placeholders), $template_body);
    return nl2br($rendered_text); // Newlines কে <br> ট্যাগ দিয়ে পরিবর্তন করা
}

// =================================================================
// 🔹 ইনপুট এবং ডেটাবেস কুয়েরি
// =================================================================

$examname = $_GET['exam'] ?? 'SSC';
$passingyear = $_GET['year'] ?? date('Y');
$groupsection = $_GET['sec'] ?? '';
$stids_param = $_GET['stids'] ?? $_GET['stid'] ?? '0';

// ডিফল্ট টেমপ্লেট ডেটাবেস থেকে লোড করা
$template_query = $conn->query("SELECT template_body FROM testimonial_templates WHERE is_default = 1 AND sccode IN (0, '$sccode') ORDER BY sccode DESC LIMIT 1");
$default_template_body = $template_query->fetch_assoc()['template_body'] ?? 'Default template not found in database.';

$stids = array_filter(array_map('intval', explode(',', $stids_param)));

if (empty($stids)) {
    // যদি কোনো নির্দিষ্ট শিক্ষার্থী না থাকে, তাহলে সেকশন ও বছর অনুযায়ী সব ইস্যু করা টেস্টিমোনিয়াল দেখাও
    $sql = "SELECT * FROM testimonial WHERE sccode='$sccode' AND pubexam='$examname' AND passyear='$passingyear' AND groupsection='$groupsection' AND testslno!=''";
} else {
    // নির্দিষ্ট শিক্ষার্থীদের জন্য
    $in_clause = implode(',', $stids);
    $sql = "SELECT * FROM testimonial WHERE sccode='$sccode' AND stid IN ($in_clause) AND testslno!=''";
}

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Testimonial</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f0f2f5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .testimonial-page {
            background: white;
            width: 210mm;
            min-height: 297mm; /* Use min-height to avoid content overflow issues */
            margin: 20px auto;
            padding: 20mm 20mm 15mm;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: always;
            box-sizing: border-box;
        }

        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 1;
            width: 50%;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        .fab-wrapper {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
        }

        .fab-main {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: #696cff;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.2s;
        }

        .fab-main:hover {
            background-color: #5355d8;
        }

        #settings-panel {
            position: fixed;
            top: 0;
            right: -350px;
            width: 300px;
            height: 100%;
            background: #fff;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.15);
            padding: 20px;
            transition: right 0.3s ease-in-out;
            z-index: 10000;
            overflow-y: auto;
        }

        #settings-panel.open {
            right: 0;
        }

        #settings-panel h5 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        @media print {
            body {
                background: none;
            }

            .testimonial-page {
                margin: 0;
                box-shadow: none;
                min-height: 0;
                border: none;
            }

            .fab-wrapper {
                display: none;
            }
        }
    </style>
</head>

<body>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // শিক্ষার্থীর তথ্য সংগ্রহ
            $student_stmt = $conn->prepare("SELECT * FROM students WHERE sccode = ? AND stid = ?");
            if ($student_stmt) {
                $student_stmt->bind_param("ss", $sccode, $row['stid']);
                $student_stmt->execute();
                $student_data = $student_stmt->get_result()->fetch_assoc();
                $student_stmt->close();
            }
            $student_data = $student_data ?? []; // যদি কোনো কারণে ডেটা না পাওয়া যায়

            // রেন্ডারিং এর জন্য ডেটা অ্যারে তৈরি
            $render_data = array_merge($row, $student_data);

            // টেমপ্লেট রেন্ডার করা
            $testimonial_body = render_testimonial_template($default_template_body, $render_data);
            ?>

            <div class="testimonial-page">
                <img class="watermark-logo" src="<?= institute_logo($sccode) ?>">

                <div class="content-wrapper">
                    <div style="text-align:center;" class="letter-head">
                        <?php include ('templete/letter-head-01.php'); ?>
                    </div>

                    <table style="width:100%; border:0; margin-top: 20px;" class="main-table">
                        <tr>
                            <td style="height:10mm;" valign="middle">SL: <b><?= htmlspecialchars($row['testslno']) ?></b></td>
                            <td style="text-align:right" valign="middle">Date:
                                <b><?= date('d F, Y', strtotime($row['testdate'])) ?></b></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:center; padding: 20px 0;">
                                <img src="assets/images/testimonials-02.png" width="250" class="testimonial-title-img" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="line-height:1.8; font-size:16px; text-align:justify;" valign="top">
                                <p class="editable dynamic-text">
                                    <?= $testimonial_body ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 40px;" class="footer-section">
                                <table style="width:100%; border:0;">
                                    <tr>
                                        <td style="width: 20%; vertical-align: bottom;">
                                            <?php $lnk = 'https://eimbox.com/verify/testimonial.php?sl=' . $row['testslno'] . '&id=' . $row['stid']; ?>
                                            <img style="padding: 5px; border: 1px solid #eee;"
                                                src="https://quickchart.io/qr?text=<?= urlencode($lnk) ?>&size=120" />
                                        </td>
                                        <td style="width: 80%; text-align:right; vertical-align: bottom;">
                                            <img src="https://eimbox.com/sign/<?= $sccode ?>.png" style="height:40px;" class="head-signature-img"><br>
                                            <b><?= $headname; ?></b><br>
                                            <?= $headtitle; ?><br>
                                            <?= $scname; ?><br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php
        } // end while
    } else {
        echo "<div class='alert alert-danger m-5'>No issued testimonials found for the selected criteria.</div>";
    }
    ?>

    <!-- Floating Menu -->
    <div class="fab-wrapper">
        <button class="fab-main mb-2" onclick="toggleSettings()">
            <i class="bi bi-gear"></i>
        </button>
        <button class="fab-main" onclick="window.print()">
            <i class="bi bi-printer"></i>
        </button>
    </div>

    <!-- Settings Panel -->
    <div id="settings-panel">
        <h5>Customize Design</h5>
        <div class="mb-3">
            <label class="form-label small">Font Size</label>
            <input type="range" class="form-range" id="font-size-slider" min="12" max="20" step="1">
        </div>
        <div class="mb-3">
            <label class="form-label small">Page Border</label>
            <input type="color" class="form-control form-control-color" id="border-color-picker" title="Choose border color">
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="toggle-letterhead">
            <label class="form-check-label small" for="toggle-letterhead">Show Letter Head</label>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="toggle-title-img">
            <label class="form-check-label small" for="toggle-title-img">Show "Testimonial" Title Image</label>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="toggle-signature">
            <label class="form-check-label small" for="toggle-signature">Show Head's Signature</label>
        </div>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="toggle-footer">
            <label class="form-check-label small" for="toggle-footer">Show Footer Section</label>
        </div>
        <button class="btn btn-sm btn-outline-danger" onclick="resetSettings()">Reset to Default</button>
    </div>

    <script>
        const settingsPanel = document.getElementById('settings-panel');
        const body = document.body;

        // কুকি থেকে সেটিংস লোড করার ফাংশন
        function loadSettings() {
            const settings = JSON.parse(getCookie('testimonialSettings') || '{}');
            applySettings(settings);

            // UI আপডেট করা
            document.getElementById('font-size-slider').value = settings.fontSize || 16;
            document.getElementById('border-color-picker').value = settings.borderColor || '#ffffff';
            document.getElementById('toggle-letterhead').checked = settings.showLetterhead !== false;
            document.getElementById('toggle-title-img').checked = settings.showTitleImg !== false;
            document.getElementById('toggle-signature').checked = settings.showSignature !== false;
            document.getElementById('toggle-footer').checked = settings.showFooter !== false;
        }

        // সেটিং প্রয়োগ করার ফাংশন
        function applySettings(settings) {
            document.querySelectorAll('.dynamic-text').forEach(el => el.style.fontSize = (settings.fontSize || 16) + 'px');
            document.querySelectorAll('.testimonial-page').forEach(el => el.style.borderColor = settings.borderColor || 'transparent');
            document.querySelectorAll('.letter-head').forEach(el => el.style.display = settings.showLetterhead === false ? 'none' : '');
            document.querySelectorAll('.testimonial-title-img').forEach(el => el.style.display = settings.showTitleImg === false ? 'none' : '');
            document.querySelectorAll('.head-signature-img').forEach(el => el.style.display = settings.showSignature === false ? 'none' : '');
            document.querySelectorAll('.footer-section').forEach(el => el.style.display = settings.showFooter === false ? 'none' : '');
        }

        // কুকিতে সেটিংস সেভ করার ফাংশন
        function saveSettings() {
            const settings = {
                fontSize: document.getElementById('font-size-slider').value,
                borderColor: document.getElementById('border-color-picker').value,
                showLetterhead: document.getElementById('toggle-letterhead').checked,
                showTitleImg: document.getElementById('toggle-title-img').checked,
                showSignature: document.getElementById('toggle-signature').checked,
                showFooter: document.getElementById('toggle-footer').checked,
            };
            setCookie('testimonialSettings', JSON.stringify(settings), 30);
            applySettings(settings);
        }

        // সেটিংস রিসেট করার ফাংশন
        function resetSettings() {
            deleteCookie('testimonialSettings');
            loadSettings();
        }

        // সেটিংস প্যানেল টগল
        function toggleSettings() {
            settingsPanel.classList.toggle('open');
        }

        // ইভেন্ট লিসেনার যোগ করা
        document.querySelectorAll('#settings-panel input').forEach(input => {
            input.addEventListener('input', saveSettings);
        });

        // পেজ লোড হলে সেটিংস লোড করা
        document.addEventListener('DOMContentLoaded', loadSettings);
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>