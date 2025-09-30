<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

$perm_color = [0 => 'red', 1 => 'orange', 2 => 'blue', 3 => 'seagreen'];

// ==================== Load Module List ====================
$module_list = [];
$result = $conn->query("SELECT * FROM modulelist where (module_name != 'Core' AND module_name != 'Backend')");
while ($row = $result->fetch_assoc())
    $module_list[] = $row;

// ==================== Load status List ====================
$status_list = [];
$result = $conn->query("SELECT * FROM status_list where status!='' ORDER BY id");
while ($row = $result->fetch_assoc())
    $status_list[] = $row;

// ==================== Load Sub Module List ====================
$sub_module_list = [];
$result = $conn->query("SELECT * FROM modulemanager");
while ($row = $result->fetch_assoc())
    $sub_module_list[] = $row;

// ==================== Load Role List ====================
$user_level_list = [];
echo "SELECT * FROM rolemanager where sccode=0 or sccode='$sccode'";
$result = $conn->query("SELECT * FROM rolemanager where sccode=0 or sccode='$sccode'");
while ($row = $result->fetch_assoc())
    $user_level_list[] = $row;

// ==================== Load Permission Map ====================
$permission_map = [];
$result = $conn->query("SELECT * FROM permission_map ORDER BY sccode desc");
while ($row = $result->fetch_assoc())
    $permission_map[] = $row;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Permission Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .module {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .sub-module {
            border: 1px solid pink;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            margin-left: 20px;
        }

        .role-boxx {
            border: 1px solid teal;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            margin-left: 20px;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            opacity: 0.9;
            display: none;
        }

        #customModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            z-index: 1000;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>

    <?php include 'core/block-print.php'; ?>

</head>

<body>
    <div class="toast" id="toast"></div>

    <h2>Permission Management Panel</h2>

    <?php foreach ($module_list as $module) { ?>
        <div class="module">
            <h3><?php echo $module['module_name']; ?></h3>

            <?php foreach ($sub_module_list as $sub_module) {
                if ($sub_module['module_name'] == $module['module_name']) {
                    $page_name = $sub_module['related_pages']; ?>
                    <div class="sub-module ">

                        <h4><?php echo $sub_module['module_topic']; ?></h4>
                        <button class="custom-permission">Add Custom Permission</button>

                        <?php
                        // Role-level permission
            
                        foreach ($user_level_list as $u) {
                            $found = false;
                            $cur_id = 0;
                            $cur_sccode = 0;
                            foreach ($permission_map as $p) {
                                if ($p['page_name'] == $page_name && $p['userlevel'] == $u['userlevel']) {
                                    $current_permission = $p['permission'];
                                    $found = true;
                                    $cur_id = $p['id'];
                                    $cur_sccode = $p['sccode'];
                                    break;
                                }
                            }
                            if (!$found)
                                $current_permission = 0;
                            ?>
                            <div class="role-box">
                                <h5 style="display:inline-block; margin:0;"><?php echo $u['userlevel'] . '//' . $cur_id; ?></h5>
                                <select class="permission-select" data-id="<?php echo $p['id']; ?>"
                                    data-role="<?php echo $u['userlevel']; ?>" data-sccode="<?php echo $cur_sccode; ?>" data-user=""
                                    data-page="<?php echo $page_name; ?>"
                                    style="color:<?php echo $perm_color[$current_permission]; ?>;">
                                    <option value="0" style="color:red;" <?php if ($current_permission == 0)
                                        echo 'selected'; ?>>No Access
                                    </option>
                                    <option value="1" style="color:orange;" <?php if ($current_permission == 1)
                                        echo 'selected'; ?>>Read
                                        Only</option>
                                    <option value="2" style="color:blue;" <?php if ($current_permission == 2)
                                        echo 'selected'; ?>>Read &
                                        Write</option>
                                    <option value="3" style="color:seagreen;" <?php if ($current_permission == 3)
                                        echo 'selected'; ?>>Full
                                        Access</option>
                                </select>
                                <?php if ($cur_sccode > 100000) { ?>
                                    <button class="remove-permission" data-id="<?php echo $p['id']; ?>">Set Default</button>
                                <?php } ?>
                            </div>

                        <?php }

                        // Custom email-level permission
                        echo '<div>&nbsp;</div>';
                        foreach ($permission_map as $p) {
                            if ($p['page_name'] == $page_name && !empty($p['email'])) {
                                ?>
                                <div class="role-box">
                                    <h5 style="display:inline-block; color: gray; margin:0;"><?php echo $p['email']; ?> (Custom)
                                        <?php echo $p['id']; ?></h5>
                                    <select class="permission-select" data-id="<?php echo $p['id']; ?>"
                                        data-sccode="<?php echo $u['sccode']; ?>" data-role="" data-user="<?php echo $p['email']; ?>"
                                        data-page="<?php echo $page_name; ?>" style="color:<?php echo $perm_color[$p['permission']]; ?>;">
                                        <option value="0" style="color:red;" <?php if ($p['permission'] == 0)
                                            echo 'selected'; ?>>No Access
                                        </option>
                                        <option value="1" style="color:orange;" <?php if ($p['permission'] == 1)
                                            echo 'selected'; ?>>Read Only
                                        </option>
                                        <option value="2" style="color:blue;" <?php if ($p['permission'] == 2)
                                            echo 'selected'; ?>>Read &
                                            Write
                                        </option>
                                        <option value="3" style="color:seagreen;" <?php if ($p['permission'] == 3)
                                            echo 'selected'; ?>>Full
                                            Access</option>
                                    </select>


                                    <button class="remove-permission" data-id="<?php echo $p['id']; ?>">Remove</button>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                <?php }
            } ?>
        </div>
    <?php } ?>

    <!-- Modal HTML -->
    <div id="customModal">
        <h4>Select User for Custom Permission</h4>
        <select id="customUserSelect">
            <option value="">-- Select User --</option>
        </select>
        <button id="addCustomPermissionBtn">Add</button>
        <button id="closeModalBtn">Close</button>
    </div>
    <div id="modalOverlay"></div>

    <script>
        function showToast(msg) { $('#toast').text(msg).fadeIn(200).delay(10000).fadeOut(400); }

        $(document).on('change', '.permission-select', function () {
            let sel = $(this);
            let id = sel.data('id');  // userlevel
            let role_id = sel.data('role');  // userlevel
            let sccode = sel.data('sccode');  // userlevel
            let user_id = sel.data('user');  // email
            let page_name = sel.data('page');
            let permission = sel.val();
            sel.css('color', { 0: 'red', 1: 'orange', 2: 'blue', 3: 'seagreen' }[permission]);

            $.ajax({
                url: 'core/save_permission_full.php',
                type: 'POST',
                data: { id, role_id, sccode, user_id, page_name, permission, entryby: '<?php echo $usr; ?>' },
                success: function (res) { showToast(res); },
                error: function (xhr, status, error) { console.error(xhr.responseText); showToast("Error saving permission!"); }
            });
        });


        $(document).on('click', '.remove-permission', function () {
            if (!confirm("Are you sure you want to remove this permission?")) return;
            let btn = $(this);
            let permissionId = btn.data('id');

            $.ajax({
                url: 'core/remove_permission.php',
                type: 'POST',
                data: { id: permissionId, entryby: '<?php echo $usr; ?>' },
                success: function (res) {
                    showToast(res);
                    // Remove the permission div from UI
                    btn.closest('.role-box').fadeOut(300, function () { $(this).remove(); });
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    showToast("Error removing permission!");
                }
            });
        });



        // Open custom modal
        $(document).on('click', '.custom-permission', function () {
            let subModuleDiv = $(this).closest('.sub-module');
            let pageName = subModuleDiv.find('.permission-select').first().data('page');
            $('#customModal').data('page', pageName);
            $('#customModal, #modalOverlay').fadeIn(200);

            $.ajax({
                url: 'core/fetch_users.php',
                type: 'GET',
                dataType: 'json',
                data: { page: pageName },
                success: function (users) {
                    let select = $('#customUserSelect');
                    select.empty().append('<option value="">-- Select User --</option>');
                    users.forEach(u => select.append('<option value="' + u.email + '">' + u.fullname + ' (' + u.email + ')</option>'));
                },
                error: function () { alert('Failed to fetch users!'); }
            });
        });

        // Close modal
        $('#closeModalBtn, #modalOverlay').on('click', function () { $('#customModal, #modalOverlay').fadeOut(200); });

        // Add custom permission
        $('#addCustomPermissionBtn').on('click', function () {
            let email = $('#customUserSelect').val();
            if (!email) return alert("Select a user!");
            let pageName = $('#customModal').data('page');
            let subModuleDiv = $(".sub-module").filter(function () { return $(this).find('.permission-select').first().data('page') === pageName; }).first();

            let newDiv = $('<div class="role-box"><h5>' + email + ' (Custom)</h5></div>');
            let selectHTML = '<select class="permission-select" data-id="0"  data-role="" data-user="' + email + '" data-page="' + pageName + '" style="color:red;">' +
                '<option value="" style="color:black;" selected></option>' +
                '<option value="0" style="color:red;" >No Access</option>' +
                '<option value="1" style="color:orange;">Read Only</option>' +
                '<option value="2" style="color:blue;">Read & Write</option>' +
                '<option value="3" style="color:seagreen;">Full Access</option>' +
                '</select>';
            newDiv.append(selectHTML);
            subModuleDiv.append(newDiv);
            $('#customModal, #modalOverlay').fadeOut(200);
        });
    </script>

</body>

</html>