<?php require_once 'header.php'; ?>

<?php


// echo "<pre>" . print_r($_SESSION) . " </pre>";

if (isset($_POST['save_settings'])) {

    // POST থেকে ডেটা নেওয়া)
    $theme = $_POST['theme'] ?? 'light';
    $allowed_module = $_POST['allowed_module'] ?? [];
    $active_module = $_POST['active_module'] ?? [];
    $allowed_module = implode(' | ', $allowed_module);
    $active_module = implode(' | ', $active_module);


    // DB-তে আপডেট
    $update_sql = "UPDATE scinfo SET theme = ?, valid_module = ?, active_module = ? WHERE sccode = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssss", $theme, $allowed_module, $active_module, $sccode);
    if ($stmt->execute()) {
        echo "<script>alert('✅ Settings updated successfully!');location.href=location.href;</script>";
    } else {
        echo "<script>alert('❌ Failed to update settings!');</script>";
    }
    $stmt->close();
}

$query = "SELECT * FROM scinfo WHERE sccode = '$sccode' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $theme = $row['theme'] ?? 'light';
    $allowed_modules = explode(' | ', $row['valid_module'] ?? []);
    $active_modules = explode(' | ', $row['active_module'] ?? []);


    $modules_sql = "SELECT module_name FROM modulelist ORDER BY module_name ASC";
    $modules_res = mysqli_query($conn, $modules_sql);
    ?>
    <div class="container-xxl flex-grow-1 container-p-y">
        <form method="post" action="">
            <div class="card shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">⚙️ Admin Settings - <?= htmlspecialchars($row['scname']); ?></h4>
                    <button type="submit" name="save_settings" class="btn btn-light btn-sm">💾 Save Settings</button>
                </div>

                <div class="card-body">

                    <h6 class="text-secondary">Theme</h6>
                    <select name="theme" class="form-select w-auto mb-3">
                        <option value="light" <?= $theme == 'Light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?= $theme == 'Dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>

                    <h6 class="text-secondary">Modules</h6>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:8px;">
                        <?php while ($m = mysqli_fetch_assoc($modules_res)) {
                            $name = $m['module_name'];
                            $is_allowed = in_array($name, $allowed_modules);
                            $is_active = in_array($name, $active_modules);
                            $hid = '';
                            if ($is_admin < 4 && $is_allowed === false) {
                                $hid = ' disabled';
                            }
                            ?>
                            <label
                                style="display:flex;align-items:center;gap:5px;
                                border:1px solid #ccc; padding:6px; border-radius:6px; background:<?= $is_allowed ? '#fff' : '#f5f5f5'; ?>">
                                <input type="checkbox" title="Module Allowed" name="allowed_module[]"
                                    value="<?= htmlspecialchars($name); ?>" <?= $is_allowed ? 'checked' : ''; ?>         <?php if ($is_admin < 4)
                                                         echo 'hidden'; ?>>
                                <input type="checkbox" title="Module Active/Inactive" name="active_module[]"
                                    value="<?= htmlspecialchars($name); ?>" <?= $is_active ? 'checked' : ''; ?>         <?= $hid; ?>>
                                <span class="ms-1"><?= htmlspecialchars($name); ?></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php
} else {
    echo "<div class='alert alert-danger m-3'>No school found for sccode = $sccode</div>";
}
?>

<?php require_once 'footer.php'; ?>

</body>

</html>