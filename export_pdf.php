<?php
require_once 'header.php';
require_once __DIR__ . '/vendor/autoload.php';
use Mpdf\Mpdf;

// Reuse same filters as list (simple)
$page_name  = trim($_GET['page_name'] ?? '');
$feature    = trim($_GET['feature_name'] ?? '');
$created_by = trim($_GET['created_by'] ?? '');
$date_from  = trim($_GET['date_from'] ?? '');
$date_to    = trim($_GET['date_to'] ?? '');

$where = " WHERE 1=1 ";
$params = []; $types = "";
if ($page_name !== '') { $where .= " AND page_name LIKE ?"; $params[] = "%$page_name%"; $types .= "s"; }
if ($feature !== '')   { $where .= " AND feature_name LIKE ?"; $params[] = "%$feature%"; $types .= "s"; }
if ($created_by !== ''){ $where .= " AND created_by LIKE ?"; $params[] = "%$created_by%"; $types .= "s"; }
if ($date_from !== '') { $where .= " AND created_at >= ?"; $params[] = $date_from . " 00:00:00"; $types .= "s"; }
if ($date_to !== '')   { $where .= " AND created_at <= ?"; $params[] = $date_to . " 23:59:59"; $types .= "s"; }

$sql = "SELECT feature_title, page_name, feature_name, created_by, created_at, full_documentation FROM project_documentation $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$html = '<h1>Documentation Export</h1>';
$html .= '<p>Export date: '.date('Y-m-d H:i').'</p>';
while ($r = $res->fetch_assoc()) {
    $html .= '<h2>'.htmlspecialchars($r['feature_title']).'</h2>';
    $html .= '<p><strong>Page:</strong> '.htmlspecialchars($r['page_name']).' | <strong>Feature:</strong> '.htmlspecialchars($r['feature_name']).' | <strong>By:</strong> '.htmlspecialchars($r['created_by']).' | '.htmlspecialchars($r['created_at']).'</p>';
    // full_documentation contains HTML created by Quill — it's okay to include, but sanitize if needed.
    $html .= '<div style="border:1px solid #ddd; padding:10px; margin-bottom:20px;">';
    $html .= $r['full_documentation']; // if you worry about HTML, pass through HTMLPurifier before adding.
    $html .= '</div>';
}

$mpdf = new Mpdf(['tempDir' => __DIR__ . '/tmp']);
$mpdf->WriteHTML($html);
$mpdf->Output('documentation_export_'.date('Ymd_His').'.pdf','D');
exit;
