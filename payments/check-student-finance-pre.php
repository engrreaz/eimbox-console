<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$typeg = $_POST['type'];
$partg = $_POST['part'];
$icodeg = $_POST['icode'];
$stidg = $_POST['stid'];
$clsg = $_POST['cls'];
$secg = $_POST['sec'];
$tailg = $_POST['tail'];

if ($typeg == '') {
    $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' ;";
} else if ($typeg == 'item') {
    if ($partg == 'all') {
        $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' ;";
    } else {
        if ($secg != '' && $clsg != '') {
            $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' and sectionname='$secg' and classname='$clsg' ;";
        } else if ($secg == '' && $clsg != '') {
            $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' and classname='$clsg' ;";
        } else {
            $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%'  ;";
        }
    }
} else if ($typeg == 'stid') {
    $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' and stid = '$stidg'  ;";
} else if ($typeg == 'cls') {
    $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' and classname='$clsg' ;";
} else if ($typeg == 'sec') {
    $sql0xg = "UPDATE sessioninfo set validate=0 where  sccode='$sccode' and sessionyear LIKE '%$sy%' and sectionname='$secg' and classname='$clsg' ;";
} else {
    //
}

$conn->query($sql0xg);
echo '...';