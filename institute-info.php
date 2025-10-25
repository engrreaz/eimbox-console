<?php require_once 'header.php'; ?>

<?php
// উদাহরণস্বরূপ sccode GET থেকে নেওয়া

// ডেটাবেজ থেকে তথ্য ফেচ করা
$query = "SELECT * FROM scinfo WHERE sccode = '$sccode' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // admin_data JSON ডিকোড
    $admin_data = json_decode($row['admin_data'], true);

    // কিছু ভ্যালু বের করা
    $modules = isset($admin_data['module']) ? implode(', ', $admin_data['module']) : 'N/A';
    $theme = $admin_data['settings']['theme'] ?? 'N/A';
    $api_key = $admin_data['settings']['sms']['api']['api_key'] ?? 'N/A';
    $api_secret = $admin_data['settings']['sms']['api']['api_secret'] ?? 'N/A';
    $sms_time = $admin_data['settings']['sms']['in_time']['time'] ?? 'N/A';
    ?>
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">🏫 <?= htmlspecialchars($row['scname']); ?> (<?= htmlspecialchars($row['short']); ?>)</h4>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3 text-center mt-4">
                        <img src="<?php echo $logo_path; ?>" class="img-thumbnail mb-2 rounded-3" alt="Logo"
                            style="max-width:150px;">
                        <p class="text-muted small"><?= htmlspecialchars($row['sccategory']); ?></p>
                    </div>

                    <div class="col-md-9">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th>School Code</th>
                                <td><?= $row['sccode']; ?></td>
                            </tr>
                            <tr>
                                <th>Head</th>
                                <td><?= htmlspecialchars($row['headname']) . " (" . htmlspecialchars($row['headtitle']) . ")"; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>
                                    <?= htmlspecialchars($row['scadd1']); ?>, <?= htmlspecialchars($row['scadd2']); ?>,
                                    <?= htmlspecialchars($row['dist']); ?>
                                    <?= $row['postal_code'] ? ' - ' . htmlspecialchars($row['postal_code']) : ''; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Mobile</th>
                                <td><?= htmlspecialchars($row['mobile']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($row['scmail']); ?></td>
                            </tr>
                            <tr>
                                <th>Website</th>
                                <td><a href="<?= htmlspecialchars($row['scweb']); ?>"
                                        target="_blank"><?= htmlspecialchars($row['scweb']); ?></a></td>
                            </tr>
                            <tr>
                                <th>Package</th>
                                <td><?= $row['pack']; ?> (<?= $row['packdate']; ?>)</td>
                            </tr>
                            <tr>
                                <th>Expire</th>
                                <td><?= $row['expire']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h5 class="mt-3">🔧 Admin Settings</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Theme</th>
                        <td><?= htmlspecialchars($theme); ?></td>
                    </tr>
                    <tr>
                        <th>Modules</th>
                        <td><?= htmlspecialchars($modules); ?></td>
                    </tr>
                    <tr>
                        <th>SMS API Key</th>
                        <td><?= htmlspecialchars($api_key); ?></td>
                    </tr>
                    <tr>
                        <th>SMS Secret</th>
                        <td><?= htmlspecialchars($api_secret); ?></td>
                    </tr>
                    <tr>
                        <th>In-Time SMS</th>
                        <td><?= htmlspecialchars($sms_time); ?></td>
                    </tr>
                </table>

            </div>
        </div>

    </div>

    <?php
} else {
    echo "<div class='container py-5'><div class='alert alert-danger'>No school found for sccode = $sccode</div></div>";
}
?>

<?php require_once 'footer.php'; ?>