<?php
require_once 'db_config.php'; 

header('Content-Type: application/json'); // JSON ফরম্যাট সেট করা

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tid'])) {
    
    $tid    = mysqli_real_escape_string($conn, $_POST['tid']);
    $tname  = mysqli_real_escape_string($conn, $_POST['tname']);
    $slots  = mysqli_real_escape_string($conn, $_POST['slots']);
    $sccode = $sccode; // আপনার সেশন বা গ্লোবাল ভ্যারিয়েবল

    // SL বের করা
    $sl_query = "SELECT MAX(sl) as max_sl FROM teacher WHERE sccode = '$sccode'";
    $sl_res = $conn->query($sl_query);
    $sl_row = $sl_res->fetch_assoc();
    $next_sl = ($sl_row['max_sl'] ?? 0) + 1;

    $modifieddate = date('Y-m-d H:i:s');

    $sql = "INSERT INTO teacher (sl, tid, tname, slots, sccode, status, modifieddate) 
            VALUES ('$next_sl', '$tid', '$tname', '$slots', '$sccode', '1', '$modifieddate')";

    if ($conn->query($sql) === TRUE) {
        // সফল হলে JSON পাঠানো
        echo json_encode(['status' => 'success', 'message' => 'Saved successfully']);
    } else {
        // এরর হলে JSON পাঠানো
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
exit();
?>