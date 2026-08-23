<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$sessionyear = $_COOKIE['chain-session'] ?? '';
$cls = $_COOKIE['chain-class'] ?? '';
$sec = $_COOKIE['chain-section'] ?? '';


$q = "
SELECT ss.subject AS subcode,
COALESCE(
  (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = '$sccode' OR s.sccode = 0) AND (s.sccategory = '$sctype' OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = '$sccode') DESC, s.sccode DESC LIMIT 1),
  (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = '$sccode' OR s.sccode = 0) ORDER BY (s.sccode = '$sccode') DESC, s.sccode DESC LIMIT 1),
  CONCAT('Subject ', ss.subject)
) AS subject
FROM subsetup ss
WHERE ss.sccode = '$sccode' AND ss.sessionyear = '$sessionyear' AND ss.classname = '$cls' AND ss.sectionname = '$sec'
ORDER BY ss.slno, ss.subject;
";
$r = $conn->query($q);

while ($row = $r->fetch_assoc()) {
    echo '<option value="' . $row['subcode'] . '">' . $row['subcode'] . ' - ' . $row['subject'] . '</option>';
}