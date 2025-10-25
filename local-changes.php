<?php
ob_start();
require_once 'header.php';

$track_tables = ['modulelist', 'modulemanager', 'payments'];

// ======================
// Trigger utility functions
// ======================
function trigger_status($conn, $table)
{
    $q = mysqli_query($conn, "SHOW TRIGGERS LIKE '$table'");
    return (mysqli_num_rows($q) > 0) ? 'ON' : 'OFF';
}

function enable_triggers($conn, $table)
{
    // Drop old triggers
    $trigs = ["after_insert", "after_update", "after_delete"];
    foreach ($trigs as $t) {
        mysqli_query($conn, "DROP TRIGGER IF EXISTS {$table}_{$t}");
    }

    // Get column names
    $columns = [];
    $res = mysqli_query($conn, "SHOW COLUMNS FROM $table");
    while ($col = mysqli_fetch_assoc($res)) {
        $columns[] = $col['Field'];
    }

    // Primary key (first column used for WHERE in update/delete)
    $pk = $columns[0];

    // ======================
    // CREATE INSERT TRIGGER
    // ======================
    $col_names = implode(', ', $columns);

    $vals_concat = "CONCAT(";
    foreach ($columns as $i => $col) {
        $vals_concat .= "'\"', IFNULL(NEW.$col,'NULL'), '\"'";
        if ($i < count($columns) - 1)
            $vals_concat .= ", ',' ,";
    }
    $vals_concat .= ")";

    $insert_trigger = "
    CREATE TRIGGER {$table}_after_insert
    AFTER INSERT ON $table
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('$table',
            CONCAT('INSERT INTO $table ($col_names) VALUES (', $vals_concat, ');'),
            'INSERT');
    END
    ";
    mysqli_query($conn, $insert_trigger);

    // ======================
    // CREATE UPDATE TRIGGER
    // ======================
    $update_concat_parts = [];
    foreach ($columns as $col) {
        $update_concat_parts[] = "'$col=', '\"', IFNULL(NEW.$col,'NULL'), '\"'";
    }
    $update_concat = implode(", ',', ", $update_concat_parts);

    $update_trigger = "
    CREATE TRIGGER {$table}_after_update
    AFTER UPDATE ON $table
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('$table',
            CONCAT('UPDATE $table SET ', $update_concat, ' WHERE $pk=', IFNULL(OLD.$pk,'NULL'), ';'),
            'UPDATE');
    END
    ";
    mysqli_query($conn, $update_trigger);

    // ======================
    // CREATE DELETE TRIGGER
    // ======================
    $delete_trigger = "
    CREATE TRIGGER {$table}_after_delete
    AFTER DELETE ON $table
    FOR EACH ROW
    BEGIN
        INSERT INTO sql_backup_log(table_name, sql_statement, action_type)
        VALUES ('$table',
            CONCAT('DELETE FROM $table WHERE $pk=', IFNULL(OLD.$pk,'NULL'), ';'),
            'DELETE');
    END
    ";
    mysqli_query($conn, $delete_trigger);
}

function disable_triggers($conn, $table)
{
    $trigs = ["after_insert", "after_update", "after_delete"];
    foreach ($trigs as $t) {
        mysqli_query($conn, "DROP TRIGGER IF EXISTS {$table}_{$t}");
    }
}

// ======================
// Handle trigger ON/OFF
// ======================
if (isset($_POST['toggle_table'])) {
    $table = $_POST['table'];
    $action = $_POST['toggle_table'];
    if ($action == 'ON')
        enable_triggers($conn, $table);
    else
        disable_triggers($conn, $table);
}

// ======================
// Delete log record
// ======================
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM sql_backup_log WHERE id=$id");
}

// ======================
// Update SQL statement manually
// ======================
if (isset($_POST['update_sql'])) {
    $id = intval($_POST['id']);
    $new_sql = mysqli_real_escape_string($conn, $_POST['sql_statement']);
    mysqli_query($conn, "UPDATE sql_backup_log SET sql_statement='$new_sql' WHERE id=$id");
}

// ======================
// Export selected to project folder
// ======================
if (isset($_POST['export_selected'])) {
    $ids = $_POST['selected_ids'] ?? [];
    if (!empty($ids)) {
        $ids_list = implode(',', array_map('intval', $ids));
        $res = mysqli_query($conn, "SELECT * FROM sql_backup_log WHERE id IN ($ids_list)");
        $file_content = "";
        while ($r = mysqli_fetch_assoc($res)) {
            $file_content .= $r['sql_statement'] . ";\n";
        }
        mysqli_query($conn, "UPDATE sql_backup_log SET exported=1 WHERE id IN ($ids_list)");

        $folder = __DIR__ . '/exports';
        if (!is_dir($folder))
            mkdir($folder, 0777, true);

        $filename = $folder . '/exported_statements_' . date('Y_m_d_H_i_s') . '.sql';
        file_put_contents($filename, $file_content);

        echo "<div class='alert alert-success'>Selected SQL statements exported to <b>$filename</b></div>";
    } else {
        echo "<div class='alert alert-warning'>No records selected!</div>";
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h3>🧠 Table Change Tracker</h3>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Table</th>
                <th>Trigger Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($track_tables as $t):
                $status = trigger_status($conn, $t);
                ?>
                <tr>
                    <td><?= $t ?></td>
                    <td>
                        <?php if ($status == 'ON'): ?>
                            <span class="badge bg-success">ON</span>
                        <?php else: ?>
                            <span class="badge bg-danger">OFF</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="table" value="<?= $t ?>">
                            <button type="submit" name="toggle_table" value="<?= $status == 'ON' ? 'OFF' : 'ON' ?>"
                                class="btn btn-sm btn-<?= $status == 'ON' ? 'danger' : 'success' ?>">
                                <?= $status == 'ON' ? '🛑 Turn OFF' : '✅ Turn ON' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <h4>🗂 SQL Backup Log</h4>
    <form method="post">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>ID</th>
                    <th>Table</th>
                    <th>Action</th>
                    <th>SQL Statement</th>
                    <th>Changed At</th>
                    <th>Exported</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM sql_backup_log ORDER BY changed_at DESC");
                while ($r = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $r['id'] ?>"></td>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['table_name']) ?></td>
                        <td><span class="badge bg-info"><?= $r['action_type'] ?></span></td>
                        <td>
                            <textarea name="sql_statement_<?= $r['id'] ?>" rows="3"
                                class="form-control"><?= htmlspecialchars($r['sql_statement']) ?></textarea>
                        </td>
                        <td><?= $r['changed_at'] ?></td>
                        <td>
                            <?php if ($r['exported']): ?>
                                <span class="badge bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?del=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')"
                                class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" name="export_selected" class="btn btn-primary mt-2">Export Selected to File</button>
    </form>
</div>

<script>
    // Check/uncheck all
    document.getElementById('checkAll').addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('input[name="selected_ids[]"]').forEach(chk => {
            chk.checked = checked;
        });
    });
</script>

<?php
require_once 'footer.php';
ob_end_flush();
?>