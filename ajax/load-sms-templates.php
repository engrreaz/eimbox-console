<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$cat = mysqli_real_escape_string($conn, $_POST['cat'] ?? '');
$block = mysqli_real_escape_string($conn, $_POST['block'] ?? '');

$where_cat = (!empty($cat) && $cat !== 'all') ? "AND (temp_type='$cat' OR temp_type='general')" : "";
$q = "SELECT id, sccode, temp_type, target_audience, temp_title, temp_text, language, is_default 
      FROM sms_templete 
      WHERE (sccode='$sccode' OR sccode=0) AND status=1 $where_cat 
      ORDER BY (sccode='$sccode') DESC, is_default DESC, id DESC";
$sql = mysqli_query($conn, $q);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="small text-muted mb-0">Select a template to use or customize for your institution.</p>
    <a href="sms-templates.php" target="_blank" class="btn btn-sm btn-primary">
        <i class="bi bi-pencil-square me-1"></i> Open Full Template Editor
    </a>
</div>

<?php
if (!$sql || mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-info d-flex align-items-center mb-0'>
            <i class='bi bi-info-circle-fill me-2'></i>
            <span>No template found for this category. Click the button above to create one in the Template Editor.</span>
          </div>";
    exit;
}

echo "<div class='table-responsive'>
        <table class='table table-bordered table-hover table-sm align-middle mb-0'>
            <thead class='table-dark'>
                <tr>
                    <th style='width:40px;'>#</th>
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
                    data-text=\"" . htmlspecialchars($row['temp_text'], ENT_QUOTES) . "\">
                    <i class='bi bi-check2-circle'></i> Use
                </button>
            </td>
          </tr>";
    $sl++;
}

echo "</tbody></table></div>";

