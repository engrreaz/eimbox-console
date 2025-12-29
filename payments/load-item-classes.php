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


// Fetch classes
$classSql = "SELECT DISTINCT areaname FROM areas 
             WHERE sccode='$sccode' AND sessionyear='$selectedSession' 
             ORDER BY idno";
$classRs = $conn->query($classSql);

if (!$classRs->num_rows) {
    echo "<div class='text-muted'>No class found</div>";
    exit;
}

while ($c = $classRs->fetch_assoc()) {
    $class = $c['areaname'];

    // Total amount per class (optional)
    $amtRs = $conn->query("SELECT amount FROM financesetupvalue WHERE  classname='$class' and sccode='$sccode' and sessionyear='$session' and slot='$slot' AND itemcode='$itemcode'");
    $totalClassAmount = ($amtRs->num_rows) ? $amtRs->fetch_assoc()['amount'] : 0;
    ?>
    <div class="class-row border rounded mb-2 p-2">
        <div class="d-flex justify-content-between align-items-center pointer class-toggle"
            onclick="$(this).siblings('.session-list').slideToggle(150); $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down');">
            <div><i class="bi bi-chevron-right me-2"></i><strong><?= $class ?></strong></div>
            <small class="text-success">--,৳ <?= number_format($totalClassAmount, 2) ?></small>


            <!-- RIGHT : AMOUNT + BUTTON -->
            <div class="d-flex align-items-center gap-2">
                <span class="text-success fw-bold">
                    ৳ <?= number_format($totalClassAmount, 2) ?>
                </span>

                <button class="btn btn-sm btn-outline-primary" onclick="openAmountModal(
                    <?= $fid ?>,
                    '<?= $itemcode ?>',
                    '<?= $class ?>',
                    ''
                )">
                    Set
                </button>
            </div>





        </div>
        <div class="session-list mt-2" style="display:none;">
            <?php
            // Fetch sessions for this class
            $secSql = "SELECT subarea FROM areas 
                   WHERE sccode='$sccode' AND sessionyear='$session' AND areaname='$class' AND slot='$slot'
                   ORDER BY subarea";
            $secRs = $conn->query($secSql);
            if (!$secRs->num_rows) {
                echo "<div class='text-muted ms-3'>No sections</div>";
                continue;
            }
            while ($s = $secRs->fetch_assoc()) {
                $section = $s['subarea'];

                // Total amount per section
                $amtRs = $conn->query("SELECT amount FROM financesetupvalue WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND classname='$class' AND sectionname='$section'  AND itemcode='$itemcode'");
                $secAmount = ($amtRs->num_rows) ? $amtRs->fetch_assoc()['amount'] : 0;
                ?>
                <div
                    class="session-row border rounded mb-1 p-2 d-flex justify-content-between align-items-center ms-3 section-toggle">
                    <span><?= $section ?></span>
                    <button class="btn btn-sm btn-outline-secondary" data-class="<?= $class ?>" data-section="<?= $section ?>"
                        onclick="openAmountModal(
                        <?= $fid ?>,
                        '<?= $itemcode ?>',
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