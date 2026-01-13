<?php
$page_status = 0;

$status_name = 0;
$page_title = 'EIMBox';
$page_icon = 'app';
$cur_page_module = '&mdash;';

$stmt = $conn->prepare("
            SELECT module_name, nav_title, nav_icon, status_name, root_page 
            FROM modulemanager 
            WHERE FIND_IN_SET(?, related_pages)
            ORDER BY id DESC 
            LIMIT 1
        ");
if ($stmt) {
    $stmt->bind_param("s", $currentFile);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $page_status = $row['status_name'];
        $page_title = $row['nav_title'] ?? 'EIMBox';
        $page_icon = $row['nav_icon'] ?? 'app';
        $cur_page_module = $row['module_name'] ?? '';
        $MUL_PATA = $row['root_page'] ?? '';
    }
}
$stmt->close();


// $stmt = $conn->prepare("SELECT * FROM project_documentation WHERE page_name = ? order by id DESC LIMIT 1");
$stmt = $conn->prepare("SELECT * FROM project_documentation WHERE page_name = ? ORDER BY id");
$stmt->bind_param("s", $currentFile);
$stmt->execute();
$res = $stmt->get_result();

$documentation_data = [];
while ($row = $res->fetch_assoc()) {
    $documentation_data[] = $row;
}

$stmt->close();



$sett = [];
$sql = "SELECT * FROM settings WHERE sccode='$sccode' ORDER BY id";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $sett[] = $row;
}