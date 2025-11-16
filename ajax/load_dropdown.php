<?php
$session_start();
require_once "../core/config.php";
require_once "../core/db.php";
require_once "../core/global_values.php";



$type = $_GET['type'];

// Parents (dynamic)
$class  = $_GET['class']  ?? "";
$exam   = $_GET['exam']   ?? "";
$slot   = $_GET['slot']   ?? "";
$session= $_GET['session']?? "";

$data = [];

switch ($type) {

    // no dependency
    case "session":
        $q = $conn->query("SELECT syear AS id, syear AS name 
                           FROM sessionyear where sccode='$sccode' and active=1 ORDER BY syear DESC");

        break;

    case "class":
        $q = $conn->query("SELECT class_id AS id, classname AS name 
                           FROM classinfo ORDER BY sortorder");
        break;

    // single dependency
    case "section":
        $q = $conn->query("SELECT id, sectionname AS name 
                           FROM sectioninfo 
                           WHERE class_id='$class'");
        break;

    // multiple dependency → example: subject depends on class + exam
    case "subject":
        $q = $conn->query("SELECT id, subjectname AS name 
                           FROM subjectinfo 
                           WHERE class_id='$class'");
        break;

    case "exam":
        $q = $conn->query("SELECT examid AS id, examname AS name 
                           FROM examinfo ORDER BY examid");
        break;

    case "slot":
        $q = $conn->query("SELECT id, slotname AS name 
                           FROM slotinfo ORDER BY id");
        break;
}

while ($row = $q->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
