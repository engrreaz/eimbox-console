<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

set_time_limit(0);
$data = '';

// POST variables
$slot = $_POST['slot'];
$session = $_POST['session'];
$offset = intval($_POST['offset'] ?? 0);
$batchSize = intval($_POST['batchSize'] ?? 1);
$cls = $_POST['classname'] ?? '';
$sec = $_POST['sectionname'] ?? '';


// Base WHERE for sessioninfo
$whereBase = "sessionyear='$session' AND slot='$slot' AND sccode='$sccode' AND grand_merged=0";
if ($cls !== '')
    $whereBase .= " AND classname='$cls'";
if ($sec !== '')
    $whereBase .= " AND sectionname='$sec'";

// Total students count
$q_total = "SELECT COUNT(*) AS cnt FROM sessioninfo WHERE $whereBase";
$res_total = mysqli_query($conn, $q_total);
$total = mysqli_fetch_assoc($res_total)['cnt'];

echo json_encode([
    'done' => true,
    'total' => $total,
]);