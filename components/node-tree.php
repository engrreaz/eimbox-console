<?php
session_start();
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$type = $_POST['type'] ?? '';
$context = $_POST['context'] ?? [];

$slot = $context['slot'] ?? '';
$sessionyear = $context['sessionyear'] ?? '';
$areaname = $context['areaname'] ?? '';
$subarea = $context['subarea'] ?? '';
$exam = $context['exam'] ?? '';
$subject = $context['subject'] ?? '';

$data = [];

/* ---------- SLOT ---------- */
if ($type === 'slot') {

    $sql = "
        SELECT slotname AS text
        FROM slots
        WHERE sccode='$sccode'
        ORDER BY slotname
    ";
}

/* ---------- SESSION ---------- */ elseif ($type === 'session') {

    $sql = "
        SELECT syear AS text
        FROM sessionyear
        WHERE sccode='$sccode'
          AND active=1
        ORDER BY syear DESC
    ";
}

/* ---------- CLASS ---------- */ elseif ($type === 'class') {

    $sql = "
        SELECT DISTINCT areaname AS text
        FROM areas
        WHERE sccode='$sccode'
          AND slot='$slot'
          AND sessionyear='$sessionyear'
        ORDER BY areaname
    ";
}

/* ---------- SECTION ---------- */ elseif ($type === 'section') {

    $sql = "
        SELECT id, subarea AS text
        FROM areas
        WHERE sccode='$sccode'
          AND slot='$slot'
          AND sessionyear='$sessionyear'
          AND areaname='$areaname'
        ORDER BY subarea
    ";
}

/* ---------- EXAM ---------- */ elseif ($type === 'exam') {

    $sql = "
        SELECT id, examtitle AS text
        FROM examlist
        WHERE sccode='$sccode'
          AND sessionyear='$sessionyear'
        ORDER BY id
    ";
}

/* ---------- SUBJECT ---------- */ 
elseif ($type === 'subject') {

    $sql = "
        SELECT 
            ss.id,
            CONCAT(ss.subject, ' - ', s.subject) AS text
        FROM subsetup ss
        INNER JOIN subjects s 
            ON s.subcode = ss.subject
        WHERE ss.sccode = '$sccode'
          AND ss.sessionyear = '$sessionyear'
          AND ss.classname = '$areaname'
          AND ss.sectionname = '$subarea'
          AND ss.slot = '$slot'
          AND s.sccategory = '$sctype'
          AND (s.sccode = '$sccode' OR s.sccode = 0)
        ORDER BY ss.subject ASC, s.sccode DESC
    ";
}

if (!empty($sql)) {
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}

echo json_encode($data);
