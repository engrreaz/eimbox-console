<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = isset($_POST['slot']) ? trim($_POST['slot']) : null;

if (empty($slot)) {
    echo "<div class='alert alert-warning'>Slot/Unit not selected.</div>";
    $slot = null; // নিশ্চিতভাবে null সেট করা
}

// GP লিস্ট
$gpList = [];
if ($slot === null || $slot == '') {
    $sqlx = "SELECT gp FROM gpa WHERE slot IS NULL GROUP BY gp ORDER BY gp DESC";
} else {
    $sqlx = "SELECT gp FROM gpa WHERE slot='$slot' GROUP BY gp ORDER BY gp DESC";
}


$resx = mysqli_query($conn, $sqlx);
while ($row = mysqli_fetch_assoc($resx)) {
    $gpList[] = $row['gp']; // শুধু gp-এর ভ্যালু নেওয়া হলো
}


// টেবিল হেডার
echo "<table class='table table-bordered table-sm'>
        <thead>
            <tr>
                <th>Min</th><th>Max</th><th>GP</th><th>GL</th><th>Remark</th><th>Color</th><th>Action</th>
            </tr>
        </thead><tbody>";


foreach ($gpList as $gp) {

    if ($slot === null) {
        $sql = "SELECT * FROM gpa 
                WHERE (sccode='$sccode' OR sccode = '0') 
                AND slot IS NULL 
                AND gp='$gp'
                ORDER BY  sccode DESC LIMIT 1";
    } else {
        $sql = "SELECT * FROM gpa 
                WHERE (sccode='$sccode' OR sccode = '0') 
                AND (slot='$slot' OR slot IS NULL)
                AND gp='$gp'
                ORDER BY slot DESC, gp, sccode DESC LIMIT 1";
    }


//   echo  $sql;

    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $id = $row['id'];
        $disableDelete = ($row['sccode'] == 0) ? "disabled" : "";

        echo "<tr>
            <td>{$row['minvalues']}</td>
            <td>{$row['maxvalues']}</td>
            <td>{$row['gp']}</td>
            <td>{$row['gl']}</td>
            <td>{$row['remark']}</td>
            <td><div style='width:20px; height:20px; background:{$row['colorcode']}'></div></td>
            <td>
                <button class='btn btn-sm btn-warning editBtn' data-id='$id'>Edit</button>
                <button class='btn btn-sm btn-danger delBtn' data-id='$id' $disableDelete>Delete</button>
            </td>
        </tr>";
    }
}


echo "</tbody></table>";
