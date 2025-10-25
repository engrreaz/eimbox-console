<?php
require_once 'config.php';
require_once 'db.php';



if(isset($_POST['eiin'])) {
    $eiin = mysqli_real_escape_string($conn, $_POST['eiin']);
    $sql = "SELECT sccode FROM scinfo WHERE sccode = '$eiin' LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if(mysqli_num_rows($res) > 0){
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
