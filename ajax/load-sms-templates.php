<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
$cat = mysqli_real_escape_string($conn, trim($_POST['cat'] ?? 'all'));
$block = mysqli_real_escape_string($conn, trim($_POST['block'] ?? 'composer'));

$where_cat = (!empty($cat) && $cat !== 'all') ? "AND temp_type='$cat'" : "";
$q = "SELECT id, sccode, temp_type, target_audience, temp_title, temp_text, language, is_default 
      FROM sms_templete 
      WHERE (sccode='$sccode' OR sccode=0) AND status=1 $where_cat 
      ORDER BY (sccode='$sccode') DESC, is_default DESC, id DESC";
$sql = mysqli_query($conn, $q);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
    <!-- Category Filter Pills inside Modal -->
    <div class="btn-group btn-group-sm" role="group" id="modal_temp_cats">
        <button type="button" class="btn <?= ($cat === 'all') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="all">All</button>
        <button type="button" class="btn <?= ($cat === 'general') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="general">General</button>
        <button type="button" class="btn <?= ($cat === 'sms_in') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_in">Entry (In)</button>
        <button type="button" class="btn <?= ($cat === 'sms_out') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_out">Exit (Out)</button>
        <button type="button" class="btn <?= ($cat === 'sms_absent') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_absent">Absent</button>
        <button type="button" class="btn <?= ($cat === 'sms_payment') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_payment">Payment</button>
        <button type="button" class="btn <?= ($cat === 'sms_dues') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_dues">Dues</button>
        <button type="button" class="btn <?= ($cat === 'sms_result') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_result">Result</button>
        <button type="button" class="btn <?= ($cat === 'sms_meeting') ? 'btn-primary' : 'btn-outline-secondary' ?> modalCatBtn" data-cat="sms_meeting">Meeting</button>
    </div>

    <a href="sms-templates.php" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-pencil-square me-1"></i> Open Template Manager
    </a>
</div>

<?php
if (!$sql || mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-info d-flex align-items-center mb-0'>
            <i class='bi bi-info-circle-fill me-2 fs-5'></i>
            <div>
                <b>No template found for this category.</b><br>
                <span class='small text-muted'>You can create and customize templates for your institution in the <a href='sms-templates.php' target='_blank' class='fw-bold text-decoration-underline'>Template Manager</a>.</span>
            </div>
          </div>";
    exit;
}

echo "<div class='table-responsive'>
        <table class='table table-bordered table-hover table-sm align-middle mb-0'>
            <thead class='table-dark'>
                <tr>
                    <th style='width:35px;' class='text-center'>#</th>
                    <th>Title & Scope</th>
                    <th>Audience</th>
                    <th>Template Content</th>
                    <th class='text-center' style='width:90px;'>Action</th>
                </tr>
            </thead>
            <tbody>";

$sl = 1;
while ($row = mysqli_fetch_assoc($sql)) {
    $is_system = ($row['sccode'] == 0);
    $badge_type = $is_system 
        ? "<span class='badge bg-secondary ms-1'><i class='bi bi-globe me-1'></i>System</span>" 
        : "<span class='badge bg-info text-white ms-1'><i class='bi bi-building me-1'></i>Custom</span>";
    
    $default_badge = ($row['is_default'] == 1) ? "<span class='badge bg-success ms-1'>Default</span>" : "";
    $aud = ucfirst($row['target_audience'] ?? 'all');
    $highlighted = preg_replace('/(\[\[[A-Z0-9_]+\]\])/', '<span class="badge bg-primary bg-opacity-25 text-primary fw-semibold">$1</span>', htmlspecialchars($row['temp_text']));
    $raw_text_attr = htmlspecialchars($row['temp_text'], ENT_QUOTES, 'UTF-8');

    echo "<tr>
            <td class='text-center text-muted'>{$sl}</td>
            <td>
                <div class='fw-bold text-dark'>{$row['temp_title']}</div>
                <div class='mt-1'>{$badge_type} {$default_badge}</div>
            </td>
            <td><span class='badge bg-light text-dark border'>{$aud}</span></td>
            <td><div style='white-space:pre-wrap; max-height:85px; overflow-y:auto;' class='small font-monospace p-1 bg-light rounded border'>{$highlighted}</div></td>
            <td class='text-center'>
                <button type='button' class='btn btn-sm btn-success chooseTemp py-1 px-2'
                    data-block=\"" . htmlspecialchars($block, ENT_QUOTES) . "\"
                    data-text=\"{$raw_text_attr}\">
                    <i class='bi bi-check2-circle me-1'></i> Use
                </button>
            </td>
          </tr>";
    $sl++;
}

echo "</tbody></table></div>";


