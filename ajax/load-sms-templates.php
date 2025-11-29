<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';

$cat = mysqli_real_escape_string($conn, $_POST['cat']);
$block = mysqli_real_escape_string($conn, $_POST['block']);

echo '<input type="hidden" id="newtemptype" value="'.$cat.'"/>';

$q = "SELECT id, temp_title, temp_text FROM sms_templete WHERE temp_type='$cat' ORDER BY id DESC";
$sql = mysqli_query($conn, $q);

if (mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-warning'>No Template Found!</div>";
    exit;
}



echo "<table class='table table-bordered table-striped table-sm'>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Template</th>
                <th>Choose</th>
            </tr>
        </thead>
        <tbody>";

$sl = 1;

while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>
            <td>{$sl}</td>
            <td>{$row['temp_title']}</td>
            <td><pre style='white-space:pre-wrap;'>{$row['temp_text']}</pre></td>
            <td>
                <button class='btn btn-sm btn-success chooseTemp'
                data-block=\"" . htmlspecialchars($block, ENT_QUOTES) . "\"
                        data-text=\"" . htmlspecialchars($row['temp_text'], ENT_QUOTES) . "\">
                    Choose
                </button>
            </td>
          </tr>";
    $sl++;
}

echo "</tbody></table>";
