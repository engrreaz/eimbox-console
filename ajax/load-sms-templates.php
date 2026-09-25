<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$cat = mysqli_real_escape_string($conn, $_POST['cat'] ?? '');
$block = mysqli_real_escape_string($conn, $_POST['block'] ?? '');

echo '<input type="hidden" id="newtemptype" value="' . htmlspecialchars($cat) . '"/>';

$where_cat = !empty($cat) ? "AND (temp_type='$cat' OR temp_type='general')" : "";
$q = "SELECT id, sccode, temp_type, target_audience, temp_title, temp_text, is_default 
      FROM sms_templete 
      WHERE (sccode='$sccode' OR sccode=0) AND status=1 $where_cat 
      ORDER BY is_default DESC, id DESC";
$sql = mysqli_query($conn, $q);

if (!$sql || mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-info d-flex align-items-center mb-0'>
            <i class='bi bi-info-circle-fill me-2'></i>
            <span>No template found for this category. You can create a new template using the button above.</span>
          </div>";
    exit;
}

echo "<div class='table-responsive'>
        <table class='table table-bordered table-hover table-sm mb-0'>
            <thead class='table-dark'>
                <tr>
                    <th style='width:50px;'>#</th>
                    <th>Title</th>
                    <th>Audience</th>
                    <th>Template Body</th>
                    <th class='text-center' style='width:90px;'>Action</th>
                </tr>
            </thead>
            <tbody>";

$sl = 1;
while ($row = mysqli_fetch_assoc($sql)) {
    $badge_type = ($row['sccode'] == 0) ? "<span class='badge bg-secondary ms-1'>System</span>" : "<span class='badge bg-info ms-1'>Custom</span>";
    $aud = ucfirst($row['target_audience'] ?? 'student');
    
    echo "<tr>
            <td>{$sl}</td>
            <td class='fw-semibold'>{$row['temp_title']} {$badge_type}</td>
            <td><span class='badge bg-light text-dark border'>{$aud}</span></td>
            <td><div style='white-space:pre-wrap; max-height:100px; overflow-y:auto;' class='small text-muted'>{$row['temp_text']}</div></td>
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
