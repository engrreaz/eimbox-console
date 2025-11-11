<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$src = $_POST['src'] ?? '';
$tgt = $_POST['tgt'] ?? '';
$sccode = $_SESSION['sccode'] ?? '';

if (!$src || !$tgt || !$sccode) {
    exit("<span class='text-danger'>Invalid Input!</span>");
}

// Explode and pad
$srcParts = array_pad(explode('|', $src), 3, '');
$tgtParts = array_pad(explode('|', $tgt), 3, '');
[$srcYear, $srcArea, $srcSub] = $srcParts;
[$tgtYear, $tgtArea, $tgtSub] = $tgtParts;

// Current timestamp
$cur = date('Y-m-d H:i:s');

// Determine cloning level
$level = !empty($srcSub) ? 3 : (!empty($srcArea) ? 2 : 1);

switch ($level) {

    case 1: // Year → Year
        $tgtYear = $tgtParts[0] ?: $srcYear;

        $sql = "INSERT INTO areas (sccode, sessionyear, areaname, subarea, user, slot, medium, version, modifieddate)
                SELECT '$sccode', '$tgtYear', a.areaname, a.subarea, a.user, a.slot, a.medium, a.version, '$cur'
                FROM areas a
                LEFT JOIN areas t
                  ON t.sccode='$sccode' 
                  AND t.sessionyear='$tgtYear' 
                  AND t.areaname=a.areaname 
                  AND t.subarea=a.subarea
                WHERE a.sccode='$sccode' 
                  AND a.sessionyear='$srcYear'
                  AND t.sessionyear IS NULL";
        break;

    case 2: // Class → Class
        $tgtYear = $tgtParts[0] ?: $srcYear;
        $tgtArea = $tgtParts[1] ?: $srcArea;

        $sql = "INSERT INTO areas (sccode, sessionyear, areaname, subarea, user, slot, medium, version, modifieddate)
                SELECT '$sccode', '$tgtYear', '$tgtArea', a.subarea, a.user, a.slot, a.medium, a.version, '$cur'
                FROM areas a
                LEFT JOIN areas t
                  ON t.sccode='$sccode' 
                  AND t.sessionyear='$tgtYear' 
                  AND t.areaname='$tgtArea' 
                  AND t.subarea=a.subarea
                WHERE a.sccode='$sccode' 
                  AND a.sessionyear='$srcYear' 
                  AND a.areaname='$srcArea'
                  AND t.subarea IS NULL";
        break;

    case 3: // Section → Section
        $tgtYear = $tgtParts[0] ?: $srcYear;
        $tgtArea = $tgtParts[1] ?: $srcArea;
        $tgtSub  = $tgtParts[2] ?: $srcSub;

        $sql = "INSERT INTO areas (sccode, sessionyear, areaname, subarea, user, slot, medium, version, modifieddate)
                SELECT '$sccode', '$tgtYear', '$tgtArea', '$tgtSub', a.user, a.slot, a.medium, a.version, '$cur'
                FROM areas a
                LEFT JOIN areas t
                  ON t.sccode='$sccode'
                  AND t.sessionyear='$tgtYear'
                  AND t.areaname='$tgtArea'
                  AND t.subarea='$tgtSub'
                WHERE a.sccode='$sccode'
                  AND a.sessionyear='$srcYear'
                  AND a.areaname='$srcArea'
                  AND a.subarea='$srcSub'
                  AND t.subarea IS NULL";
        break;

    default:
        exit("<span class='text-danger'>Invalid cloning level!</span>");
}

// Execute
if ($conn->query($sql)) {
    echo "<span class='text-success'>✅ Clone successful!</span>";
} else {
    echo "<span class='text-danger'>❌ Clone failed: " . htmlspecialchars($conn->error) . "</span>";
}
