<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h3>🛠 Batch Execute SQL Files</h3>

    <?php
    $export_folder = __DIR__ . '/exports';
    $execution_report = [];

    // ======================
    // Helper function: execute SQL file line by line (statement by statement)
    // ======================
    function execute_sql_file($conn, $file_path)
    {
        $report = [];
        $content = file_get_contents($file_path);
        if ($content === false)
            return [['stmt' => '', 'status' => 'Fail', 'error' => 'Cannot read file']];

        // normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // split statements by semicolon followed by newline
        $statements = preg_split('/;\s*\n/', $content);

        $new_content = ""; // to store updated file with -- EXECUTED
    
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt))
                continue;

            // skip already executed
            if (str_starts_with($stmt, '-- EXECUTED')) {
                $new_content .= $stmt . ";\n";
                $report[] = ['stmt' => $stmt, 'status' => 'Skipped', 'error' => null];
                continue;
            }

            // execute statement
            if (mysqli_query($conn, $stmt)) {
                $report[] = ['stmt' => $stmt, 'status' => 'Success', 'error' => null];
                // mark executed
                $stmt = "-- EXECUTED\n" . $stmt;
            } else {
                $report[] = ['stmt' => $stmt, 'status' => 'Fail', 'error' => mysqli_error($conn)];
            }

            $new_content .= $stmt . ";\n";
        }

        // write back updated file
        file_put_contents($file_path, $new_content);

        return $report;
    }

    // ======================
    // Handle uploaded files
    // ======================
    if (isset($_POST['run_uploaded_files'])) {
        $uploaded_files = $_FILES['uploaded_sql_files'] ?? null;
        if ($uploaded_files && !empty($uploaded_files['tmp_name'][0])) {
            foreach ($uploaded_files['tmp_name'] as $idx => $tmp_path) {
                $file_name = $uploaded_files['name'][$idx];
                $target_path = $export_folder . '/' . $file_name;
                move_uploaded_file($tmp_path, $target_path); // move to exports folder
                $execution_report = array_merge($execution_report, execute_sql_file($conn, $target_path));
            }
        }
    }

    // ======================
    // Handle selected export folder files
    // ======================
    if (isset($_POST['run_selected_files'])) {
        $selected_files = $_POST['sql_files'] ?? [];
        foreach ($selected_files as $file_name) {
            $file_path = $export_folder . '/' . basename($file_name);
            if (file_exists($file_path)) {
                $execution_report = array_merge($execution_report, execute_sql_file($conn, $file_path));
            } else {
                $execution_report[] = ['stmt' => '', 'status' => 'Fail', 'error' => 'File not found: ' . $file_name];
            }
        }
    }

    // ======================
    // Show execution report
    // ======================
    if (!empty($execution_report)) {
        echo '<div class="card mb-3">';
        echo '<div class="card-header bg-info text-white">📋 Execution Report</div>';
        echo '<div class="card-body">';
        echo '<table class="table table-bordered table-sm">';
        echo '<thead><tr><th>Status</th><th>Statement / Error</th></tr></thead><tbody>';
        foreach ($execution_report as $r) {
            $badge_class = $r['status'] == 'Success' ? 'success' : ($r['status'] == 'Skipped' ? 'secondary' : 'danger');
            $stmt_text = htmlspecialchars($r['stmt']);
            if ($r['error'])
                $stmt_text .= "<br><span class='text-danger'>Error: " . htmlspecialchars($r['error']) . "</span>";
            echo "<tr>";
            echo "<td><span class='badge bg-{$badge_class}'>{$r['status']}</span></td>";
            echo "<td>{$stmt_text}</td>";
            echo "</tr>";
        }
        echo '</tbody></table></div></div>';
    }
    ?>

    <!-- Collapsible card: Exports folder -->
    <div class="card mb-3">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sqlFilesList" style="cursor:pointer">
            <h5 class="mb-0">📂 Exported SQL Files</h5>
            <small class="text-muted">Click to expand/collapse</small>
        </div>
        <div id="sqlFilesList" class="collapse">
            <div class="card-body">
                <?php
                if (is_dir($export_folder)) {
                    $files = array_diff(scandir($export_folder, SCANDIR_SORT_DESCENDING), ['.', '..']);
                    $sql_files = array_filter($files, fn($f) => str_ends_with($f, '.sql'));
                    if (!empty($sql_files)) {
                        echo '<form method="post">';
                        echo '<table class="table table-bordered table-sm">';
                        echo '<thead><tr><th>Select</th><th>File Name</th><th>Modified Date</th></tr></thead><tbody>';
                        foreach ($sql_files as $f) {
                            $mtime = date("Y-m-d H:i:s", filemtime($export_folder . '/' . $f));
                            echo '<tr>';
                            echo '<td><input type="checkbox" name="sql_files[]" value="' . htmlspecialchars($f) . '"></td>';
                            echo '<td>' . htmlspecialchars($f) . '</td>';
                            echo '<td>' . $mtime . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                        echo '<button type="submit" name="run_selected_files" class="btn btn-primary">Execute Selected Files</button>';
                        echo '</form>';
                    } else {
                        echo "<div class='alert alert-info'>No SQL files found in exports folder.</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>Exports folder does not exist.</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Collapsible card: Upload SQL files -->
    <div class="card mb-3">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#uploadSqlCard" style="cursor:pointer">
            <h5 class="mb-0">⬆ Upload & Execute SQL Files</h5>
        </div>
        <div id="uploadSqlCard" class="collapse card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="uploaded_sql_files" class="form-label">Select SQL Files (multiple allowed)</label>
                    <input type="file" name="uploaded_sql_files[]" id="uploaded_sql_files" class="form-control"
                        accept=".sql" multiple required>
                </div>
                <button type="submit" name="run_uploaded_files" class="btn btn-success">Upload & Execute Selected
                    Files</button>
            </form>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
<script>
    // Bootstrap collapse JS is default
</script>
</body>

</html>