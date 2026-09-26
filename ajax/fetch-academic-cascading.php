<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$sccode = (int)($_POST['sccode'] ?? $_SESSION['sccode'] ?? $sccode ?? 0);
$sessionyear = mysqli_real_escape_string($conn, trim($_POST['sessionyear'] ?? ''));
$slot = mysqli_real_escape_string($conn, trim($_POST['slot'] ?? ''));
$classname = mysqli_real_escape_string($conn, trim($_POST['classname'] ?? ''));

$where_class = "sccode='$sccode' AND classname IS NOT NULL AND classname != ''";
if (!empty($sessionyear)) {
    $where_class .= " AND (sessionyear='$sessionyear' OR sessionyear='' OR sessionyear IS NULL)";
}
if (!empty($slot)) {
    $where_class .= " AND (slot='$slot' OR slot='' OR slot IS NULL)";
}

// 1. Fetch Classes
$classes = [];
$cq = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE $where_class ORDER BY classname ASC");
if ($cq) {
    while ($r = $cq->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// 2. Fetch Sections
$where_sec = "sccode='$sccode' AND sectionname IS NOT NULL AND sectionname != ''";
if (!empty($sessionyear)) {
    $where_sec .= " AND (sessionyear='$sessionyear' OR sessionyear='' OR sessionyear IS NULL)";
}
if (!empty($slot)) {
    $where_sec .= " AND (slot='$slot' OR slot='' OR slot IS NULL)";
}
if (!empty($classname) && $classname !== 'all') {
    $where_sec .= " AND classname='$classname'";
}

$sections = [];
$sq = $conn->query("SELECT DISTINCT sectionname FROM sessioninfo WHERE $where_sec ORDER BY sectionname ASC");
if ($sq) {
    while ($r = $sq->fetch_assoc()) {
        $sections[] = $r['sectionname'];
    }
}

echo json_encode([
    'status' => 'success',
    'classes' => $classes,
    'sections' => $sections
]);
