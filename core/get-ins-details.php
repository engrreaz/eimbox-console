<?php

require_once 'config.php';
require_once 'db.php';
require_once 'core-val.php';
require_once 'global_values.php';

$sccode = $_GET['sccode'] ?? '';
$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode=? LIMIT 1");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row) {
    echo "<p><strong>School Code:</strong> {$row['sccode']}</p>";
    echo "<p><strong>Name:</strong> {$row['scname']}</p>";
    echo "<p><strong>Category:</strong> {$row['sccategory']}</p>";
    echo "<p><strong>PS:</strong> {$row['ps']}</p>";
    echo "<p><strong>District:</strong> {$row['dist']}</p>";
    echo "<p><strong>Address:</strong> {$row['scadd1']}, {$row['scadd2']}</p>";
    echo "<p><strong>Mobile:</strong> {$row['mobile']}</p>";
}
