<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : '');
$session = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : '');

$selectedSession = $_POST['session'] ?? $session;

$fid = intval($_POST['fid']);
$itemcode = $_POST['itemcode'];
$spl = $_POST['spl'];

// read particulareng, particularben from financesetup where itemcode=$itemcode
$sql = "SELECT particulareng, particularben FROM financesetup WHERE itemcode='$itemcode'";
$rs = $conn->query($sql);
if ($rs->num_rows) {
    $row = $rs->fetch_assoc();
    $particulareng = $row['particulareng'];
    $particularben = $row['particularben'];
} else {
    $particulareng = '';
    $particularben = '';
}
$itemText = $particulareng . ' | ' . $particularben;

//


// Fetch classes
$classSql = "SELECT areaname FROM areas 
             WHERE sccode='$sccode' AND sessionyear='$selectedSession' AND areaname IS NOT NULL AND areaname != ''
             GROUP BY areaname
             ORDER BY MIN(idno) ASC, areaname ASC";
$classRs = $conn->query($classSql);

if (!$classRs || !$classRs->num_rows) {
    echo "<div class='text-muted p-2'>No class found for session: " . htmlspecialchars($selectedSession) . "</div>";
    exit;
}

while ($c = $classRs->fetch_assoc()) {
    $class = $c['areaname'];

    // Total amount per class (optional)
    $amtRs = $conn->query("SELECT amount FROM financesetupvalue WHERE classname='$class' AND sccode='$sccode' AND sessionyear='$selectedSession' AND (slot='$slot' OR slot='' OR slot IS NULL) AND itemcode='$itemcode' AND (sectionname='' OR sectionname IS NULL) LIMIT 1");
    $totalClassAmount = ($amtRs && $amtRs->num_rows) ? $amtRs->fetch_assoc()['amount'] : 0;
    ?>
    <div class="class-row border rounded mb-2 p-2 bg-white">
        <div class="d-flex justify-content-between align-items-center pointer class-toggle"
            onclick="$(this).siblings('.session-list').slideToggle(150); $(this).find('.chev-toggle').toggleClass('bi-chevron-right bi-chevron-down');">
            <div><i class="bi bi-chevron-right me-2 chev-toggle"></i><strong><?= htmlspecialchars($class) ?></strong></div>
            <small class="text-success">৳ <?= number_format($totalClassAmount, 2) ?></small>


            <!-- RIGHT : AMOUNT + BUTTON -->
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); openAmountModal(
                    <?= $fid ?>,
                    '<?= $itemcode ?>',
                    '<?= $spl ?>',
                    '<?= addslashes($itemText) ?>',
                    '<?= addslashes($class) ?>',
                    ''
                )">
                    Set Amount (<span class="fw-bold">৳ <?= number_format($totalClassAmount, 2) ?></span>)
                </button>
            </div>

        </div>
        <div class="session-list mt-2" style="display:none;">
            <?php
            // Fetch sections for this class
            $secSql = "SELECT DISTINCT subarea FROM areas 
                   WHERE sccode='$sccode' AND sessionyear='$selectedSession' AND areaname='$class' AND (slot='$slot' OR slot='' OR slot IS NULL) AND subarea IS NOT NULL AND subarea != ''
                   ORDER BY subarea ASC";
            $secRs = $conn->query($secSql);
            if (!$secRs || !$secRs->num_rows) {
                echo "<div class='text-muted ms-3 small py-1'>No specific sections configured</div>";
                continue;
            }
            while ($s = $secRs->fetch_assoc()) {
                $section = $s['subarea'];

                // Total amount per section
                $amtRs = $conn->query("SELECT amount FROM financesetupvalue WHERE sccode='$sccode' AND sessionyear='$selectedSession' AND (slot='$slot' OR slot='' OR slot IS NULL) AND classname='$class' AND sectionname='$section' AND itemcode='$itemcode' LIMIT 1");
                $secAmount = ($amtRs && $amtRs->num_rows) ? $amtRs->fetch_assoc()['amount'] : 0;

                ?>
                <div
                    class="session-row border rounded mb-1 p-2 d-flex justify-content-between align-items-center ms-3 section-toggle">
                    <span><?= $section ?></span>
                    <button class="btn btn-sm btn-outline-secondary" data-class="<?= $class ?>" data-section="<?= $section ?>"
                        onclick="openAmountModal(
                        <?= $fid ?>,
                        '<?= $itemcode ?>',
                         '<?= $spl ?>',
                          '<?= $itemText ?>',
                        '<?= $class ?>',
                        '<?= $section ?>'
                    )">
                        Set Amount <?= $secAmount ? '(৳ ' . number_format($secAmount, 2) . ')' : '' ?>
                    </button>
                </div>
            <?php } // section endwhile ?>
        </div>
    </div>
<?php } // class endwhile ?>