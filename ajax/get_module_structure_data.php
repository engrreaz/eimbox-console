<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$request = $_POST;


// File list from directory
$files = array_values(array_filter(scandir(dirname(__DIR__)), function ($f) {
    return is_file(dirname(__DIR__) . '/' . $f) && substr($f, -4) === '.php';
}));

$total_data = count($files);
$total_filtered = $total_data;

// Search
$search_value = $request['search']['value'] ?? '';
if (!empty($search_value)) {
    $files = array_filter($files, function ($file) use ($search_value) {
        return stripos($file, $search_value) !== false;
    });
    $total_filtered = count($files);
}

// Pagination
$start = $request['start'] ?? 0;
$length = $request['length'] ?? 50;
$paginated_files = array_slice($files, $start, $length);

// Pre-fetch all required data to avoid N+1 problem
$module_data_map = [];
$all_modules_res = $conn->query("SELECT module_name, module_topic, descrip, status_name, nav_title, nav_icon, root_page, related_pages FROM modulemanager");
if ($all_modules_res) {
    while ($mod_row = $all_modules_res->fetch_assoc()) {
        $pages = explode(',', $mod_row['related_pages']);
        foreach ($pages as $page) {
            $page = trim($page);
            if ($page) {
                $module_data_map[$page] = $mod_row;
            }
        }
    }
}

$permission_map = [];
$perm_sql = "SELECT page_name, userlevel, permission FROM permission_map WHERE (sccode IS NULL OR sccode='' OR sccode='0')";
$all_perms_res = $conn->query($perm_sql);
if ($all_perms_res) {
    while ($perm_row = $all_perms_res->fetch_assoc()) {
        $permission_map[$perm_row['page_name']][$perm_row['userlevel']] = $perm_row['permission'];
    }
}

$roles = [];
$res = $conn->query("SELECT userlevel FROM rolemanager where sccode <100000 ORDER BY id ASC");
while ($r = $res->fetch_assoc()) {
    $roles[] = $r['userlevel'];
}

$status_list = [];
$res = $conn->query("SELECT * FROM status_list where status!='' ORDER BY id ASC");
while ($m = $res->fetch_assoc()) {
    $status_list[] = $m;
}

$modules = [];
$res = $conn->query("SELECT module_name FROM modulelist ORDER BY module_name ASC");
while ($m = $res->fetch_assoc()) {
    $modules[] = $m['module_name'];
}

$release_colors = [0 => "#f8f8f8ff", 1 => "#808080", 2 => "#FF0000", 3 => "#FFA500", 4 => "#FFD700", 5 => "#1E90FF", 6 => "#07af07ff", 7 => "#800080", 8 => "#012201ff"];
$release_text = [0 => "#140202ff", 1 => "#1a0a0aff", 2 => "#f5f5f5ff", 3 => "#332306ff", 4 => "#1a180dff", 5 => "#072746ff", 6 => "#ffffffff", 7 => "#e6d8e6ff", 8 => "#edf0edff"];

$data = [];
foreach ($paginated_files as $file) {
    $file_data = $module_data_map[$file] ?? [];
    $module_name = $file_data['module_name'] ?? '';
    $icon = $file_data['nav_icon'] ?? 'three-dots-vertical';
    $pg_status = $file_data['status_name'] ?? 0;

    $row = [
        "icon" => "<div class='input-group input-group-sm d-inline-flex'>
                     <a href='{$file}' style='width:50px; text-align:center; z-index:1000;' target='_blank'> <span class='input-group-text'><i class='bi bi-{$icon}'></i></span></a>
                     <input type='text' class='form-control inline-input' style='min-width:115px;' data-field='nav_icon' data-id='{$file}' value='{$icon}' />
                   </div>",
        "page_name" => "<input type='text' class='form-control form-control-sm inline-input' data-field='page_name' data-id='{$file}' value='{$file}' readonly disabled />",
    ];

    // Status dropdown
    $status_options = "<option value=''></option>";
    foreach ($status_list as $slist) {
        $selected = ($slist['id'] == $pg_status) ? "selected" : "";
        $status_options .= "<option value='{$slist['id']}' {$selected}>{$slist['status']}</option>";
    }
    $row['status'] = "<select style='background:{$release_colors[$pg_status]}; color:{$release_text[$pg_status]}' class='form-select form-select-sm inline-select' data-field='status_name' data-id='{$file}'>{$status_options}</select>";

    // Module dropdown
    $module_options = "<option value=''></option>";
    foreach ($modules as $mod) {
        $selected = ($mod == $module_name) ? "selected" : "";
        $module_options .= "<option value='{$mod}' {$selected}>{$mod}</option>";
    }
    $row['module_name'] = "<select class='form-select form-control-sm inline-select' data-field='module_name' data-id='{$file}'>{$module_options}</select>";

    // Text inputs
    $row['topic'] = "<input type='text' class='form-control form-control-sm inline-input' data-field='module_topic' data-id='{$file}' value=\"" . htmlspecialchars($file_data['module_topic'] ?? '', ENT_QUOTES) . "\" />";
    $row['title'] = "<input type='text' class='form-control form-control-sm inline-input' data-field='nav_title' data-id='{$file}' value=\"" . htmlspecialchars($file_data['nav_title'] ?? '', ENT_QUOTES) . "\" />";
    $row['descrip'] = "<input type='text' class='form-control form-control-sm inline-input' data-field='descrip' data-id='{$file}' value=\"" . htmlspecialchars($file_data['descrip'] ?? '', ENT_QUOTES) . "\" />";
    $row['root_page'] = "<input type='text' class='form-control form-control-sm inline-input' data-field='root_page' data-id='{$file}' value=\"" . htmlspecialchars($file_data['root_page'] ?? '', ENT_QUOTES) . "\" />";

    // Permissions
    foreach ($roles as $role) {
        $perm_val = $permission_map[$file][$role] ?? '';
        $cssClass = 'perm-none';
        $tooltip = 'Not Assigned';

        if ($perm_val === '0') $cssClass = 'perm-0';
        elseif ($perm_val === '1') $cssClass = 'perm-1';
        elseif ($perm_val === '2') $cssClass = 'perm-2';
        elseif ($perm_val === '3') $cssClass = 'perm-3';

        $block_dis = in_array($module_name, ['Core', 'Backend', 'Orion', 'Seed', 'Authority', 'Invalid', 'Unnecessary']) ? 'disabled' : '';

        $perm_options = "
            <option style='background:gray;' value='' " . ($perm_val === '' ? "selected" : "") . "></option>
            <option style='background:red;' value='0' " . ($perm_val === '0' ? "selected" : "") . ">0 - No Access</option>
            <option style='background:orange;' value='1' " . ($perm_val === '1' ? "selected" : "") . ">1 - Read</option>
            <option style='background:blue;' value='2' " . ($perm_val === '2' ? "selected" : "") . ">2 - Write</option>
            <option style='background:green;' value='3' " . ($perm_val === '3' ? "selected" : "") . ">3 - Full</option>
        ";

        $role_key = 'perm_' . str_replace(' ', '_', $role);
        $row[$role_key] = "<select class='form-select form-select-sm perm-select {$cssClass}' data-page='{$file}' data-role='{$role}' {$block_dis}>{$perm_options}</select>";
    }

    $data[] = $row;
}

$json_data = [
    "draw"            => intval($request['draw'] ?? 0),
    "recordsTotal"    => intval($total_data),
    "recordsFiltered" => intval($total_filtered),
    "data"            => $data
];

header('Content-Type: application/json');
echo json_encode($json_data);

$conn->close();
?>