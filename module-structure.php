<?php
require_once 'core/init.php';


$release_colors = [
    0 => "#f8f8f8ff",  // white
    1 => "#808080",  // Gray
    2 => "#FF0000",  // Red
    3 => "#FFA500",  // Orange
    4 => "#FFD700",  // Yellow
    5 => "#1E90FF",  // Blue
    6 => "#07af07ff",  // Green
    7 => "#800080",  // Purple
    8 => "#012201ff"   // Dark Green
];

$release_text = [
    0 => "#140202ff",  // white
    1 => "#1a0a0aff",    // Gray
    2 => "#eccacaff",    // Red
    3 => "#332306ff",    // Orange
    4 => "#1a180dff",    // Yellow
    5 => "#072746ff",    // Blue
    6 => "#8ea88eff",  // Green
    7 => "#f1bbf1ff",    // Purple
    8 => "#c5e9c5ff"   // Dark Green
];

// সব রোল ফেচ
$roles = [];
$res = $conn->query("SELECT userlevel FROM rolemanager where sccode <100000 ORDER BY id ASC");
while ($r = $res->fetch_assoc()) {
    $roles[] = $r['userlevel'];
}

// সব মডিউল ফেচ
$modules = [];
$res = $conn->query("SELECT module_name FROM modulelist ORDER BY module_name ASC");
while ($m = $res->fetch_assoc()) {
    $modules[] = $m['module_name'];
}

// সব মডিউল ফেচ
$status_list = [];
$res = $conn->query("SELECT * FROM status_list where status!='' ORDER BY id ASC");
while ($m = $res->fetch_assoc()) {
    $status_list[] = $m;
}

// বর্তমান ডিরেক্টরির ফাইল লিস্ট
$files = array_filter(scandir(__DIR__), function ($f) {
    return is_file($f) && substr($f, -4) === '.php'; // শুধু php ফাইল ধরলাম
});

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<style>
    .perm-select option {
        color: black !important;
        /* dropdown অপশনে টেক্সট কালো */
    }

    .perm-select.perm-none {
        background-color: #f7f7f7ff !important;
        color: white !important;
    }

    .perm-select.perm-0 {
        background-color: red !important;
        color: white !important;
    }

    .perm-select.perm-1 {
        background-color: orange !important;
        color: white !important;
    }

    .perm-select.perm-2 {
        background-color: blue !important;
        color: white !important;
    }

    .perm-select.perm-3 {
        background-color: seagreen !important;
        color: white !important;
    }
</style>

<h2>Module Structure & Default Permission Manager</h2>

<?php
echo "<table id='permissionTable' class='table table-bordered table-striped table-hover'>";
echo "<thead class='table-dark'>
        <tr>
            <th>Page Name</th>
            <th>Status</th>
            <th>Module</th>
            <th>Topic</th>
            <th>Description</th>";
foreach ($roles as $role) {
    echo "<th>{$role}</th>";
}
echo "</tr></thead><tbody>";

foreach ($files as $file) {
    $stmt = $conn->prepare("SELECT module_name, module_topic, descrip , status_name
                            FROM modulemanager 
                            WHERE FIND_IN_SET(?, related_pages)");
    $stmt->bind_param("s", $file);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    $module_name = $data['module_name'] ?? '';
    $topic = $data['module_topic'] ?? '';
    $desc = $data['descrip'] ?? '';
    $pg_status = $data['status_name'] ?? 0;

    echo "<tr>";
    echo "<td><input type='text' class='form-control inline-input' 
                     data-field='page_name' data-id='{$file}' value='{$file}' readonly /></td>";



    echo "<td><select style='background:{$release_colors[$pg_status]}; color:{$release_text[$pg_status]}' class='form-select inline-select' data-field='status_name' data-id='{$file}'>";
    echo "<option value='' {$selected}></option>";
    foreach ($status_list as $slist) {
        $selected = ($slist['id'] == $pg_status) ? "selected" : "";
        echo "<option value='{$slist['id']}' {$selected}>{$slist['status']}</option>";
    }
    echo "</select></td>";

    // if($module_name == 'Core'){
    //     $bgc = 'seagreen';
    // } else if($module_name == 'Backend'){
    //     $bgc = 'red';
    // } else {
    //     $bgc = 'transparent';
    // }
    
    $bgc = 'transparent';
    echo "<td><select style='background:$bgc' class='form-select inline-select' data-field='module_name' data-id='{$file}'>";
    echo "<option value='' {$selected}></option>";
    foreach ($modules as $mod) {
        $selected = ($mod == $module_name) ? "selected" : "";
        echo "<option value='{$mod}' {$selected}>{$mod}</option>";
    }
    echo "</select></td>";




    echo "<td><input type='text' class='form-control inline-input' 
                     data-field='module_topic' data-id='{$file}' value=\"{$topic}\" /></td>";
    echo "<td><input type='text' class='form-control inline-input' 
                     data-field='descrip' data-id='{$file}' value=\"{$desc}\" /></td>";

    foreach ($roles as $role) {
        $stmt2 = $conn->prepare("SELECT permission 
                         FROM permission_map 
                         WHERE (sccode IS NULL OR sccode='' OR sccode='0')
                           AND page_name=? 
                           AND userlevel=? 
                         LIMIT 1");
        $stmt2->bind_param("ss", $file, $role);
        $stmt2->execute();
        $perm_row = $stmt2->get_result()->fetch_assoc();
        $perm_val = isset($perm_row['permission']) ? (string) $perm_row['permission'] : '';



        $perm_val = isset($perm_row['permission']) ? (string) $perm_row['permission'] : '';

        $cssClass = 'perm-none';
        $tooltip = 'Not Assigned';

        if ($perm_val === '0') {
            $cssClass = 'perm-0';
            $tooltip = 'No Access';
        } elseif ($perm_val === '1') {
            $cssClass = 'perm-1';
            $tooltip = 'Read';
        } elseif ($perm_val === '2') {
            $cssClass = 'perm-2';
            $tooltip = 'Write';
        } elseif ($perm_val === '3') {
            $cssClass = 'perm-3';
            $tooltip = 'Full';
        } else {
            $cssClass = 'perm';
            $tooltip = 'Not Assigned';
        }

        if ($module_name == 'Core' || $module_name == 'Backend') {
            $block_dis = 'disabled';
        } else {
            $block_dis = '';
        }


        echo "<td>
        <select class='form-select perm-select {$cssClass}'
                data-bs-toggle='tooltip' title='{$tooltip}'
                data-page='{$file}' data-role='{$role}' $block_dis>
            <option style='background:gray;'  value='' " . ($perm_val === '' ? "selected" : "") . "></option>
            <option style='background:red;' value='0' " . ($perm_val === '0' ? "selected" : "") . ">0 - No Access</option>
            <option style='background:orange;' value='1' " . ($perm_val === '1' ? "selected" : "") . ">1 - Read</option>
            <option style='background:blue;' value='2' " . ($perm_val === '2' ? "selected" : "") . ">2 - Write</option>
            <option style='background:green;' value='3' " . ($perm_val === '3' ? "selected" : "") . ">3 - Full</option>
        </select>
      </td>";
    }

    echo "</tr>";
}
echo "</tbody></table>";


?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize DataTables
        var table = $('#permissionTable').DataTable({
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            responsive: true
        });

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Inline input/select update
    $(document).on('change', '.inline-input, .inline-select', function () {
        let id = $(this).data('id');
        let field = $(this).data('field');
        let value = $(this).val();

        // status_name সিলেক্ট বক্স হলে background রঙ পরিবর্তন
        if (field === 'status_name') {
            // release_colors কে JS এও ডিফাইন করতে হবে
            let release_colors = {
                0: "#f8f8f8ff",  // white
                1: "#808080",    // Gray
                2: "#FF0000",    // Red
                3: "#FFA500",    // Orange
                4: "#FFD700",    // Yellow
                5: "#1E90FF",    // Blue
                6: "#07af07ff",  // Green
                7: "#800080",    // Purple
                8: "#012201ff"   // Dark Green
            };
            let release_text = {
                0: "#f8f8f8ff",  // white
                1: "#1a0a0aff",    // Gray
                2: "#eccacaff",    // Red
                3: "#1a180dff",    // Orange
                4: "#1a180dff",    // Yellow
                5: "#072746ff",    // Blue
                6: "#8ea88eff",  // Green
                7: "#f1bbf1ff",    // Purple
                8: "#c5e9c5ff"   // Dark Green
            };

            // background আপডেট
            $(this).css("background-color", release_colors[value] || "#ffffff");
            $(this).css("color", release_text[value] || "#ffffff");
        }

        $.post('core/update_inline.php', { page_name: id, field: field, value: value }, function (res) {
            console.log(res);
        });
    });

    // Permission select update
    $(document).on('change', '.perm-select', function () {
        let page = $(this).data('page');
        let role = $(this).data('role');
        let perm = $(this).val();

        $(this).removeClass("perm-none perm-0 perm-1 perm-2 perm-3");
        let tooltipText = 'Not Assigned';
        if (perm === '') { $(this).addClass('perm-none'); tooltipText = 'Not Assigned'; }
        else if (perm === '0') { $(this).addClass('perm-0'); tooltipText = 'No Access'; }
        else if (perm === '1') { $(this).addClass('perm-1'); tooltipText = 'Read'; }
        else if (perm === '2') { $(this).addClass('perm-2'); tooltipText = 'Write'; }
        else if (perm === '3') { $(this).addClass('perm-3'); tooltipText = 'Full'; }

        $(this).attr('title', tooltipText).tooltip('dispose').tooltip();

        $.post('core/update_permission.php', { page_name: page, userlevel: role, permission: perm }, function (res) {
            console.log(res);
        });
    });


</script>