<?php 

$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("i", $sccode);
$stmt->execute();
$res = $stmt->get_result();
$datainfo = $res->fetch_assoc();
$stmt->close();

$scname = $datainfo['scname'];
$address = $datainfo['scadd1'] . ', ' . $datainfo['scadd1'] . ', ' . $datainfo['ps'] . ', ' . $datainfo['dist'];
