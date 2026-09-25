<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
$sessionyear = mysqli_real_escape_string($conn, trim($_POST['sessionyear'] ?? ''));
$slot = mysqli_real_escape_string($conn, trim($_POST['slot'] ?? ''));
$classname = mysqli_real_escape_string($conn, trim($_POST['classname'] ?? ''));

$where_class = "sccode='$sccode' AND classname IS NOT NULL AND classname != ''";
if (!empty($sessionyear)) {
    $where_class .= " AND sessionyear='$sessionyear'";
}
if (!empty($slot)) {
    $where_class .= " AND slot='$slot'";
}

// 1. Fetch Classes
$classes = [];
$cq = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE $where_class ORDER BY id ASC");
if ($cq) {
    while ($r = $cq->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// 2. Fetch Sections
$where_sec = "sccode='$sccode' AND sectionname IS NOT NULL AND sectionname != ''";
if (!empty($sessionyear)) {
    $where_sec .= " AND sessionyear='$sessionyear'";
}
if (!empty($slot)) {
    $where_sec .= " AND slot='$slot'";
}
if (!empty($classname)) {
    $where_sec .= " AND classname='$classname'";
}

$sections = [];
$sq = $conn->query("SELECT DISTINCT sectionname FROM sessioninfo WHERE $where_sec ORDER BY id ASC");
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
