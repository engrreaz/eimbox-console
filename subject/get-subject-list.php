<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$sessionyear = $_COOKIE['chain-session'] ?? '';
$cls = $_COOKIE['chain-class'] ?? '';
$sec = $_COOKIE['chain-section'] ?? '';


$q = "
SELECT s.subcode, s.subject FROM subjects s LEFT JOIN subsetup ss ON ss.subject = s.subcode AND ss.sccode = '$sccode' AND ss.sessionyear = '$sessionyear' AND ss.classname = '$cls' AND ss.sectionname = '$sec' WHERE ( s.sccode = '$sccode' OR s.sccode=0) AND ss.sessionyear = '$sessionyear' AND ss.classname = '$cls' AND ss.sectionname = '$sec' AND s.sccategory = '$sctype' ORDER BY ss.slno, ss.subject;
";
$r = $conn->query($q);

while ($row = $r->fetch_assoc()) {
    echo '<option value="' . $row['subcode'] . '">' . $row['subcode'] . ' - ' . $row['subject'] . '</option>';
}