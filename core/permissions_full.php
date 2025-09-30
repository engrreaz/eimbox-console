<?php
require_once 'config.php';
require_once 'db.php';

// Fetch modules, submodules, roles, usersapp
$modules = $conn->query("SELECT * FROM modulelist ORDER BY module_name");
$submodules = $conn->query("SELECT * FROM modulemanager ORDER BY module_name, module_topic");
$roles = $conn->query("SELECT * FROM rolemanager ORDER BY userlevel");
$users = $conn->query("SELECT id, name FROM usersapp ORDER BY name"); // Assuming users table exists

function getPermission($conn, $user_id, $role_id, $page_name)
{
    $stmt = $conn->prepare("SELECT permission FROM permission_map WHERE user_id=? AND page_name=? LIMIT 1");
    $stmt->bind_param('is', $user_id, $page_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows)
        return $res->fetch_assoc()['permission'];

    $stmt = $conn->prepare("SELECT permission FROM permission_map WHERE role_id=? AND page_name=? LIMIT 1");
    $stmt->bind_param('is', $role_id, $page_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows)
        return $res->fetch_assoc()['permission'];

    return 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Permission Management Pro</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .override {
            background-color: #ffeeba;
        }

        .filter {
            margin: 10px 0;
        }

        button {
            padding: 5px 10px;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h2>Permission Management Panel - Pro Version</h2>

    <div class="filter">
        Module:
        <select id="filter_module">
            <option value="">All</option>
            <?php $modules->data_seek(0);
            while ($m = $modules->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($m['module_name']) ?>"><?= htmlspecialchars($m['module_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        User Override:
        <select id="filter_user">
            <option value="0">None</option>
            <?php while ($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php endwhile; ?>
        </select>

        Role Bulk Apply:
        <select id="bulk_role">
            <option value="">Select Role</option>
            <?php $roles->data_seek(0);
            while ($r = $roles->fetch_assoc()): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['userlevel']) ?></option>
            <?php endwhile; ?>
        </select>
        <button id="apply_bulk">Apply All Pages</button>
    </div>

    <table id="perm_table">
        <tr>
            <th>Module</th>
            <th>Sub Module / Page</th>
            <?php $roles->data_seek(0);
            while ($role = $roles->fetch_assoc()): ?>
                <th><?= htmlspecialchars($role['userlevel']) ?></th>
            <?php endwhile; ?>
        </tr>

        <?php
        $modules->data_seek(0);
        while ($mod = $modules->fetch_assoc()):
            $submodules->data_seek(0);
            $first = true;
            while ($sub = $submodules->fetch_assoc()):
                if ($sub['module_name'] != $mod['module_name'])
                    continue;
                if (!$first)
                    echo '<tr>';
                echo '<td>' . ($first ? htmlspecialchars($mod['module_name']) : '') . '</td>';
                echo '<td>' . htmlspecialchars($sub['module_topic']) . '</td>';

                $roles->data_seek(0);
                while ($role = $roles->fetch_assoc()):
                    $perm = getPermission($conn, 0, $role['id'], $sub['module_topic']);
                    echo '<td>
                <select class="perm-dropdown" data-page="' . htmlspecialchars($sub['module_topic']) . '" data-role="' . $role['id'] . '" data-user="0">
                    <option value="0" ' . ($perm == 0 ? 'selected' : '') . '>0-Denied</option>
                    <option value="1" ' . ($perm == 1 ? 'selected' : '') . '>1-Read</option>
                    <option value="2" ' . ($perm == 2 ? 'selected' : '') . '>2-Write</option>
                    <option value="3" ' . ($perm == 3 ? 'selected' : '') . '>3-Full</option>
                </select>
            </td>';
                endwhile;

                echo '</tr>';
                $first = false;
            endwhile;
        endwhile;
        ?>
    </table>

    <button id="save_all">Save All Changes</button>

    <script>
        $(document).ready(function () {
            // Single dropdown change
            $('.perm-dropdown').change(function () {
                var role_id = $(this).data('role');
                var user_id = $(this).data('user');
                var page_name = $(this).data('page');
                var permission = $(this).val();

                $.post('save_permission_full.php', {
                    role_id: role_id,
                    user_id: user_id,
                    page_name: page_name,
                    permission: permission,
                    entryby: 'admin'
                }, function (resp) {
                    console.log(resp);
                });
            });

            // Filter
            $('#filter_module, #filter_user').change(function () {
                var module = $('#filter_module').val();
                var user_id = $('#filter_user').val();

                $('#perm_table tr').each(function (i, row) {
                    if (i == 0) return;
                    var mod_name = $(row).find('td:first').text();
                    $(row).toggle(!module || mod_name == module);

                    $(row).find('.perm-dropdown').each(function () {
                        if ($(this).data('user') != 0 && user_id != 0) $(this).parent().addClass('override');
                        else $(this).parent().removeClass('override');
                    });
                });
            });

            // Bulk Apply
            $('#apply_bulk').click(function () {
                var role_id = $('#bulk_role').val();
                if (!role_id) { alert('Select a role'); return; }

                $('#perm_table tr').each(function (i, row) {
                    if (i == 0) return;
                    $(row).find('select[data-role="' + role_id + '"]').each(function () {
                        $(this).val('3').trigger('change'); // Apply Full permission
                    });
                });
                alert('Bulk applied Full permission to selected role');
            });

            // Save All button
            $('#save_all').click(function () {
                $('.perm-dropdown').each(function () {
                    $(this).trigger('change');
                });
                alert('All changes saved!');
            });
        });
    </script>
</body>

</html>