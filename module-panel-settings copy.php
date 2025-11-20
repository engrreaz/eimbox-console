<?php require_once 'header.php'; ?>

<?php


// echo "<pre>" . print_r($_SESSION) . " </pre>";

if (isset($_POST['save_settings'])) {

    // POST থেকে ডেটা নেওয়া)
    $theme = $_POST['theme'] ?? 'light';
    $allowed_module = $_POST['allowed_module'] ?? [];
    $active_module = $_POST['active_module'] ?? [];
    $api_key = trim($_POST['api_key'] ?? '');
    $api_secret = trim($_POST['api_secret'] ?? '');
    $sms_time = $_POST['sms_time'] ?? '';
    $sms_active = isset($_POST['sms_active']) ? '1' : '0';

    // --- SMS Gateway ---
    $gateway = [
        'sms_api' => isset($_POST['gateway_active']) ? 1 : 0,
        'api_key' => trim($_POST['gateway_api_key'] ?? ''),
        'secret_key' => trim($_POST['gateway_secret_key'] ?? ''),
        'username' => trim($_POST['gateway_username'] ?? ''),
        'password' => trim($_POST['gateway_password'] ?? ''),
        'uri' => trim($_POST['gateway_uri'] ?? '')
    ];

    // নতুন admin_data অ্যারে তৈরি
    $new_admin_data = [
        "module" => $allowed_module,
        "active_module" => $active_module,
        "settings" => [
            "theme" => $theme,
            "sms" => [
                "api" => [
                    "api_key" => $api_key,
                    "api_secret" => $api_secret
                ],
                "in_time" => [
                    "active" => $sms_active,
                    "time" => $sms_time
                ],
                "gateway" => $gateway
            ]
        ]
    ];

    // JSON encode
    $json_data = json_encode($new_admin_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // DB-তে আপডেট
    $update_sql = "UPDATE scinfo SET admin_data = ? WHERE sccode = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $json_data, $sccode);
    if ($stmt->execute()) {
        echo "<script>alert('✅ Settings updated successfully!');location.href=location.href;</script>";
    } else {
        echo "<script>alert('❌ Failed to update settings!');</script>";
    }
    $stmt->close();
}
?>


{"module":["Attendance","Orion","Payment","Seed","Student", "Finance"],"active_module":["Attendance","Seed","Student","Finance"], "package":{"id":5} ,"settings":{"theme":"dark","payment_gateway":[{"gateway":"bkash","active":"1","type":"1","app_key":"0vWQuCRGiUX7EPVjQDr0EUAYtc","app_secret":"jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx","username":"01770618567","password":"D7DaC<*E*eG"},{"gateway":"nagad","active":"0","type":"1","app_key":"11111111111111ddd","app_secret":"222222222222222222","username":"3333333333333","password":"444444444"},{"gateway":"rocket","active":"0","type":"1","app_key":"","app_secret":"","username":"","password":""},{"gateway":"bank","active":"1","type":"1","app_key":"","app_secret":"","username":"","password":""}],"sms":{"api":{"api_key":"sf;sfskldfjs","api_secret":"123456000"},"in_time":{"active":"1","time":"11:00"},"gateway":{"sms_api":1,"api_key":"sfsdfs","secret_key":"teertre","username":"aaa","password":"bbb","uri":"sfsfdsfsdffsfs"}}}}

<?php
// --- scinfo থেকে তথ্য আনা ---
$query = "SELECT * FROM scinfo WHERE sccode = '$sccode' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $admin_data = json_decode($row['admin_data'], true);

    $_SESSION['admin_data'] = $row['admin_data'];

    $theme = $admin_data['settings']['theme'] ?? 'light';
    $allowed_modules = $admin_data['module'] ?? [];
    $active_modules = $admin_data['active_module'] ?? [];
    $api_key = $admin_data['settings']['sms']['api']['api_key'] ?? '';
    $api_secret = $admin_data['settings']['sms']['api']['api_secret'] ?? '';
    $sms_time = $admin_data['settings']['sms']['in_time']['time'] ?? '';
    $sms_active = $admin_data['settings']['sms']['in_time']['active'] ?? '0';

    // --- SMS Gateway data ---
    $gateway = $admin_data['settings']['sms']['gateway'] ?? [];
    $gateway_api_key = $gateway['api_key'] ?? '';
    $gateway_secret_key = $gateway['secret_key'] ?? '';
    $gateway_username = $gateway['username'] ?? '';
    $gateway_password = $gateway['password'] ?? '';
    $gateway_uri = $gateway['uri'] ?? '';
    $gateway_active = $gateway['sms_api'] ?? 0;

    // modulelist থেকে মডিউলগুলো আনা
    $modules_sql = "SELECT module_name FROM modulelist ORDER BY module_name ASC";
    $modules_res = mysqli_query($conn, $modules_sql);
    ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <form method="post" action="">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">⚙️ Admin Settings - <?= htmlspecialchars($row['scname']); ?></h4>
                    <button type="submit" name="save_settings" class="btn btn-light btn-sm">💾 Save Settings</button>
                </div>

                <div class="card-body">

                    <h6 class="text-secondary">Theme</h6>
                    <select name="theme" class="form-select w-auto mb-3">
                        <option value="light" <?= $theme == 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?= $theme == 'dark' ? 'selected' : ''; ?>>Dark</option>
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

                    <hr>
                    <h6 class="text-secondary mt-3">SMS Settings (In-Time)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>API Key</label>
                            <input type="text" name="api_key" class="form-control"
                                value="<?= htmlspecialchars($api_key); ?>">
                        </div>
                        <div class="col-md-4">
                            <label>API Secret</label>
                            <input type="text" name="api_secret" class="form-control"
                                value="<?= htmlspecialchars($api_secret); ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Time</label>
                            <input type="time" name="sms_time" class="form-control"
                                value="<?= htmlspecialchars($sms_time); ?>">
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="sms_active" value="1" <?= $sms_active == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label">In-Time SMS Active</label>
                    </div>

                    <hr>
                    <h6 class="text-secondary mt-3">SMS Gateway Settings</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Gateway API Key</label>
                            <input type="text" name="gateway_api_key" class="form-control"
                                value="<?= htmlspecialchars($gateway_api_key); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Gateway Secret Key</label>
                            <input type="text" name="gateway_secret_key" class="form-control"
                                value="<?= htmlspecialchars($gateway_secret_key); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Username</label>
                            <input type="text" name="gateway_username" class="form-control"
                                value="<?= htmlspecialchars($gateway_username); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Password</label>
                            <input type="text" name="gateway_password" class="form-control"
                                value="<?= htmlspecialchars($gateway_password); ?>">
                        </div>
                        <div class="col-md-12">
                            <label>URI / API URL</label>
                            <input type="text" name="gateway_uri" class="form-control"
                                value="<?= htmlspecialchars($gateway_uri); ?>">
                            <small class="text-muted">Use variables: <code>$appKey</code>, <code>$secretKey</code>,
                                <code>$mobile</code>, <code>$message</code></small>
                        </div>
                        <div class="col-md-12">
                            <label>Active?</label>
                            <select name="gateway_active" class="form-select">
                                <option value="1" <?= $gateway_active ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?= !$gateway_active ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
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