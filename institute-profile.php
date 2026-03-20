<?php require_once 'header.php'; ?>

<style>
    .package-card {
        transition: .2s;
        border-radius: 6px;
    }

    .package-card:hover {
        background: #f8f9fa;
    }

    .package-header {
        user-select: none;
    }

    .tier-box {
        cursor: pointer;
        transition: .15s;
    }

    .tier-box:hover {
        background: #f3f6fa;
    }

    .collapse {
        transition: .2s;
    }
</style>

<?php require_once 'header.php'; ?>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_modules_info') {

    $current_sccode = $sccode; // header.php থেকে
    $active_modules = isset($_POST['active_module']) ? implode(' | ', $_POST['active_module']) : '';

    // Update query
    $sql = "UPDATE scinfo SET active_module=? WHERE sccode=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $active_modules, $current_sccode);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Active Modules updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }

    $stmt->close();
}

echo $_POST['action'];
// ================= SCINFO আপডেট - PANELS =================
if (isset($_POST['action']) && $_POST['action'] == 'update_panels_info') {
    echo 'Y';
    // valid_panels database-এ readonly, তাই শুধু active_panel আপডেট হবে

    $active_panel = isset($_POST['active_panel']) ? implode(' | ', $_POST['active_panel']) : '';
    echo $active_panel;

    $sql = "UPDATE scinfo SET 
        active_panel='$active_panel'
        WHERE sccode='$sccode'";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Panels updated successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}



// ================= Institute Info Update =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_scinfo') {

    $scname = $conn->real_escape_string($_POST['scname']);
    $scadd1 = $conn->real_escape_string($_POST['scadd1']);
    $scadd2 = $conn->real_escape_string($_POST['scadd2']);
    $ps = $conn->real_escape_string($_POST['ps']);
    $dist = $conn->real_escape_string($_POST['dist']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $zone = $conn->real_escape_string($_POST['zone']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $scmail = $conn->real_escape_string($_POST['scmail']);
    $scweb = $conn->real_escape_string($_POST['scweb']);
    $headname = $conn->real_escape_string($_POST['headname']);
    $headtitle = $conn->real_escape_string($_POST['headtitle']);

    $sql = "UPDATE scinfo SET
                scname='$scname',
                scadd1='$scadd1',
                scadd2='$scadd2',
                ps='$ps',
                dist='$dist',
                postal_code='$postal_code',
                zone='$zone',
                mobile='$mobile',
                scmail='$scmail',
                scweb='$scweb',
                headname='$headname',
                headtitle='$headtitle'
            WHERE sccode='$sccode'";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Institute info updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: {$conn->error}</div>";
    }
}


// ================= Logo Upload =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_logo') {

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {

        $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        $fileType = mime_content_type($_FILES['logo']['tmp_name']);

        if (!in_array($fileType, $allowed)) {
            echo "<div class='alert alert-danger'>Invalid image type.</div>";
            exit;
        }

        $uploadDir = BASE_ROOT . "/logo/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetFile = $uploadDir . $sccode . ".png";

        // পুরোনো লোগো থাকলে মুছে ফেলো
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        // PNG বানিয়ে সেভ (সব ফরম্যাট একরকম রাখার জন্য)
        $imgData = file_get_contents($_FILES['logo']['tmp_name']);
        $img = imagecreatefromstring($imgData);

        if ($img !== false) {
            imagepng($img, $targetFile, 6);
            imagedestroy($img);

            echo "<div class='alert alert-success'>Logo uploaded successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Image processing failed.</div>";
        }

    } else {
        echo "<div class='alert alert-danger'>No file uploaded.</div>";
    }
}
?>

<style>
    .card-section {
        margin-bottom: 1.5rem;
    }

    .card-section .card {
        border-radius: 6px;
        transition: 0.2s;
    }

    .card-section .card:hover {
        background: #f8f9fa;
    }

    .list-group-item {
        cursor: default;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    $ins_q = $conn->query("SELECT * FROM scinfo WHERE sccode='$sccode'");
    $ins = $ins_q->fetch_assoc();

    $valid_modules = array_filter(explode(' | ', $ins['valid_module']));
    $active_modules = array_filter(explode(' | ', $ins['active_module']));
    $valid_panels = array_filter(explode(' | ', $ins['valid_panel'] ?? ''));
    $active_panels = array_filter(explode(' | ', $ins['active_panel'] ?? ''));

    // Billing
    $billing_q = $conn->query("SELECT * FROM billing_invoices WHERE sccode='$sccode' AND due_amount>0");
    $invoices = [];
    while ($row = $billing_q->fetch_assoc())
        $invoices[] = $row;

    // User stats
    $userlevels_q = $conn->query("SELECT userlevel,is_chief,COUNT(*) as total FROM usersapp WHERE sccode='$sccode' AND admin=0 GROUP BY userlevel,is_chief");
    $userlevels = [];
    while ($row = $userlevels_q->fetch_assoc())
        $userlevels[] = $row;

    // Students
    $student_q = $conn->query("SELECT SUM(CASE WHEN sessionyear LIKE '%$y_v2%' THEN 1 ELSE 0 END) AS current_students,
                          SUM(CASE WHEN sessionyear LIKE '%$y_v2%' THEN 0 ELSE 1 END) AS other_students
                          FROM sessioninfo WHERE sccode='$sccode'");
    $students = $student_q->fetch_assoc();
    ?>

    <!-- ====== Header ====== -->
   <div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
            
            <div class="position-relative">
                <div class="p-1 bg-white border rounded-3 shadow-sm">
                    <img src="<?= institute_logo($ins['sccode']) ?>" 
                         class="rounded-2 object-fit-contain" 
                         style="height: 100px; width: 100px;" 
                         id="currentLogo" 
                         alt="Logo">
                </div>
                <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm border border-2 border-white"
                        style="transform: translate(25%, 25%); width: 32px; height: 32px; padding: 0;"
                        data-bs-toggle="modal" data-bs-target="#logoModal" title="Change Logo">
                    <i class="bi bi-camera-fill" style="font-size: 0.8rem;"></i>
                </button>
            </div>

            <div class="flex-grow-1 text-center text-md-start">
                <div class="d-flex flex-column flex-md-row align-items-center gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-dark"><?= $ins['scname'] ?></h3>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 rounded-pill">
                            <?= $ins['sccategory'] ?>
                        </span>
                        <span class="badge bg-<?= $ins['active'] ? 'success' : 'secondary' ?>-subtle text-<?= $ins['active'] ? 'success' : 'secondary' ?> border border-<?= $ins['active'] ? 'success' : 'secondary' ?>-subtle px-3 rounded-pill">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                            <?= $ins['status'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>

                <div class="text-muted mb-3">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= "{$ins['scadd1']}, {$ins['scadd2']}, {$ins['ps']}, {$ins['dist']} - {$ins['postal_code']}" ?>
                    <span class="ms-2 badge bg-light text-dark fw-normal border">Zone: <?= $ins['zone'] ?></span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-auto">
                        <div class="small"><i class="bi bi-telephone text-primary me-1"></i> <?= $ins['mobile'] ?></div>
                    </div>
                    <div class="col-auto px-md-3 border-start-md">
                        <div class="small"><i class="bi bi-envelope text-primary me-1"></i> <?= $ins['scmail'] ?></div>
                    </div>
                    <div class="col-auto px-md-3 border-start-md">
                        <div class="small"><i class="bi bi-globe text-primary me-1"></i> <a href="<?= $ins['scweb'] ?>" target="_blank" class="text-decoration-none"><?= $ins['scweb'] ?></a></div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 pt-3 border-top">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-2 rounded-circle me-2">
                            <i class="bi bi-person-badge text-secondary"></i>
                        </div>
                        <div class="small text-start">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Head of Institution</span>
                            <span class="fw-bold text-dark"><?= $ins['headname'] ?></span> 
                            <span class="text-muted">(<?= $ins['headtitle'] ?>)</span>
                        </div>
                    </div>
                    
                    <button class="btn btn-sm btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#updateInfoModal">
                        <i class="bi bi-pencil-square me-1"></i> Update Profile Info
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        .border-start-md {
            border-left: 1px solid #dee2e6 !important;
        }
    }
</style>


    <!-- ====== Package / Subscription ====== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="bi bi-box-seam me-2"></i>Package & Subscription
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="text-muted mb-1 small uppercase fw-bold">Current Plan</p>
                <h4 class="mb-1 text-dark"><?= $ins['package_name'] ?></h4>
                <span class="badge bg-soft-primary text-primary border border-primary-subtle">
                    ID: <?= $ins['package_id'] ?>
                </span>
                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle ms-1">
                    Tier: <?= $ins['tier'] ?>
                </span>
            </div>

            <div class="col-md-6 border-start-md">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Pack Date:</span>
                    <span class="fw-medium"><?= $ins['packdate'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Expiry Date:</span>
                    <span class="fw-medium text-danger"><?= $ins['expire'] ?></span>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <div class="col-12 d-flex flex-wrap gap-4">
                <div>
                    <small class="text-muted d-block">Display Mode</small>
                    <span class="fw-semibold"><i class="bi bi-monitor me-1"></i><?= $ins['display'] ?></span>
                </div>
                <div>
                    <small class="text-muted d-block">Active Theme</small>
                    <span class="fw-semibold"><i class="bi bi-palette me-1"></i><?= $ins['theme'] ?></span>
                </div>
                <div>
                    <small class="text-muted d-block">Progress Guardian</small>
                    <?php if($ins['progressguar']): ?>
                        <span class="text-success fw-bold small"><i class="bi bi-check-circle-fill me-1"></i>Enabled</span>
                    <?php else: ?>
                        <span class="text-muted fw-bold small"><i class="bi bi-x-circle me-1"></i>Disabled</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- ====== Modules ====== -->

<?php
// ১. ডাটাবেজ থেকে মডিউল লিস্ট নিন (Core মডিউলগুলো আগে দেখানোর জন্য ORDER BY core DESC ব্যবহার করা হয়েছে)
$modules_q = $conn->query("SELECT * FROM modulelist WHERE is_public=1 ORDER BY core DESC, slno ASC");

if ($modules_q && $modules_q->num_rows > 0): 
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-grid-fill me-2 text-primary"></i>System Modules
        </h5>
        <span class="badge bg-light text-dark border fw-normal">Total: <?= $modules_q->num_rows ?> Modules</span>
    </div>
    
    <div class="card-body">
        <form method="post" action="" id="modulesForm">
            <input type="hidden" name="action" value="update_modules_info">
            
            <div class="row g-3">
                <?php
                while ($mod = $modules_q->fetch_assoc()):
                    $mod_name = $mod['module_name'];
                    $is_core = ($mod['core'] == 1);
                    
                    // আপনার বিদ্যমান অ্যারে থেকে চেক করুন
                    $is_valid  = in_array($mod_name, $valid_modules);
                    $is_active = in_array($mod_name, $active_modules);

                    // লজিক সেটআপ
                    $valid_checked   = ($is_core || $is_valid) ? 'checked' : '';
                    $active_disabled = ($is_core || $is_valid) ? '' : 'disabled';
                    $card_style      = $is_core ? 'border-primary-subtle bg-light' : 'border-light-subtle';
                ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 <?= $card_style ?> shadow-none border">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0 fw-bold text-truncate" style="max-width: 70%;" title="<?= $mod_name ?>">
                                    <?= $mod_name ?>
                                </h6>
                                <?php if ($is_core): ?>
                                    <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">CORE</span>
                                <?php elseif (!$is_valid): ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 0.6rem;">NOT VALID</span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" <?= $valid_checked ?> disabled>
                                    <label class="form-check-label small text-muted">Permission Valid</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input active-mod" type="checkbox" 
                                           name="active_module[]" value="<?= $mod_name ?>" 
                                           <?= $is_active ? 'checked' : '' ?> 
                                           <?= $active_disabled ?>>
                                    <label class="form-check-label small fw-bold <?= $is_active ? 'text-success' : 'text-secondary' ?>">
                                        <?= $is_active ? 'Active' : 'Inactive' ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="bi bi-cloud-arrow-up me-2"></i>Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
    <div class="alert alert-warning border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle me-2"></i> No public modules found in the system.
    </div>
<?php endif; ?>

    <!-- ====== Panels ====== -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-person-badge me-2 text-primary"></i>System Panels
        </h5>
    </div>
    <div class="card-body">
        <form id="panelForm" method="post">
            <input type="hidden" name="action" value="update_panels_info">
            
            <div class="row g-3">
                <?php
                $panel_list = [
                    'Admin'      => 'bi-shield-lock',
                    'Teacher'    => 'bi-person-workspace',
                    'Accountant' => 'bi-calculator',
                    'Staff'      => 'bi-people',
                    'Student'    => 'bi-mortarboard',
                    'Guardian'   => 'bi-person-heart',
                    'SMC'        => 'bi-briefcase'
                ];

                foreach ($panel_list as $panel => $icon):
                    $is_valid = in_array($panel, $valid_panels);
                    $is_active = in_array($panel, $active_panels);
                    
                    // ইনভ্যালিড হলে কার্ডটি একটু হালকা হবে
                    $opacity = !$is_valid ? 'opacity-50' : '';
                    $border = $is_active ? 'border-primary' : 'border-light-subtle';
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 <?= $border ?> <?= $opacity ?> shadow-none border">
                        <div class="card-body p-3 text-center">
                            <div class="mb-2">
                                <i class="<?= $icon ?> fs-2 <?= $is_active ? 'text-primary' : 'text-muted' ?>"></i>
                            </div>
                            
                            <h6 class="fw-bold mb-1"><?= $panel ?></h6>
                            
                            <?php if (!$is_valid): ?>
                                <span class="badge bg-secondary-subtle text-secondary border mb-2" style="font-size: 0.7rem;">
                                    <i class="bi bi-lock-fill"></i> Not Valid
                                </span>
                            <?php endif; ?>

                            <div class="form-check form-switch d-flex justify-content-center mt-2 p-0">
                                <input class="form-check-input ms-0 active-panel-switch" type="checkbox" 
                                       name="active_panel[]" value="<?= $panel ?>"
                                       id="panel_<?= $panel ?>" 
                                       <?= $is_active ? 'checked' : '' ?> 
                                       <?= !$is_valid ? 'disabled' : '' ?>>
                                <label class="form-check-label ms-2 small fw-medium" for="panel_<?= $panel ?>">
                                    <?= $is_active ? 'Active' : 'Inactive' ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                <p class="small text-muted mb-0">* Disabled panels require license update.</p>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-repeat me-2"></i>Update Panels
                </button>
            </div>
        </form>
    </div>
</div>


    <!-- ====== Geo Fencing ====== -->
  <div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-geo-fill me-2 text-danger"></i>Geo Fencing
        </h5>
        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">
            <i class="bi bi-broadcast me-1"></i> Active
        </span>
    </div>

    <div class="card-body bg-light-subtle">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3 h-100 shadow-none">
                    <small class="text-muted d-block mb-2 text-uppercase fw-bold letter-spacing-1">Target Coordinates</small>
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3">
                            <i class="bi bi-pin-map-fill"></i>
                        </div>
                        <div>
                            <p class="mb-0 small text-muted">Latitude</p>
                            <span class="fw-bold font-monospace"><?= $ins['geolat'] ?></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info-subtle text-info rounded-circle p-2 me-3">
                            <i class="bi bi-compass-fill"></i>
                        </div>
                        <div>
                            <p class="mb-0 small text-muted">Longitude</p>
                            <span class="fw-bold font-monospace"><?= $ins['geolon'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3 h-100 shadow-none">
                    <small class="text-muted d-block mb-2 text-uppercase fw-bold">Tolerance Thresholds</small>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><i class="bi bi-rulers me-1"></i> Distance Diff</span>
                            <span class="badge bg-dark"><?= $ins['dista_differ'] ?> m</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 65%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><i class="bi bi-stopwatch me-1"></i> Time Diff</span>
                            <span class="badge bg-dark"><?= $ins['time_differ'] ?> s</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 45%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-footer bg-white border-top-0 pb-3 pt-0">
        <button class="btn btn-outline-primary btn-sm w-100 fw-bold rounded-2">
            <i class="bi bi-map me-1"></i> Update Location on Map
        </button>
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 1px; font-size: 0.7rem; }
    .icon-box { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; }
</style>

    <!-- ====== Billing / Invoices ====== -->
    <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Billing / Invoices
        </h5>
        <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download me-1"></i> Statement
        </button>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Invoice No</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Date</th>
                        <th class="border-0" style="width: 180px;">Due Amount</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): 
                        // স্ট্যাটাস লজিক
                        $total = $inv['total_amount'];
                        $due = $inv['due_amount'];
                        
                        if ($due <= 0) {
                            $status_label = 'Paid';
                            $status_class = 'bg-success';
                        } elseif ($due < $total) {
                            $status_label = 'Partial';
                            $status_class = 'bg-warning text-dark';
                        } else {
                            $status_label = 'Unpaid';
                            $status_class = 'bg-danger';
                        }
                    ?>
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-dark">#<?= $inv['invoice_no'] ?></span>
                        </td>
                        <td>
                            <span class="fw-semibold text-primary"><?= number_format($total, 2) ?></span>
                        </td>
                        <td class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i><?= $inv['invoice_date'] ?>
                        </td>
                        <td>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted">৳</span>
                                    <input type="number" name="due_amount" 
                                           class="form-control border-start-0 ps-0 fw-medium" 
                                           value="<?= $due ?>" step="0.01">
                                </div>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $status_class ?> rounded-pill" style="font-size: 0.7rem;">
                                <?= $status_label ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                                <input type="hidden" name="inv_id" value="<?= $inv['id'] ?>">
                                <input type="hidden" name="action" value="update_invoice">
                                <button type="submit" name="update_invoice" class="btn btn-sm btn-success px-3 shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Update
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- ====== SMS & Payment Gateways ====== -->
    <div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-credit-card-2-front me-2 text-primary"></i>Payment Gateways & SMS Analytics
        </h5>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <p class="small text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">Active Gateways</p>
                <div class="d-flex flex-wrap gap-3">
                    <div class="gateway-box p-2 border rounded-3 d-flex align-items-center bg-light">
                        <span class="dot me-2 <?= $ins['bkash'] ? 'bg-success' : 'bg-secondary' ?>"></span>
                        <span class="small fw-bold">bKash</span>
                    </div>
                    <div class="gateway-box p-2 border rounded-3 d-flex align-items-center bg-light">
                        <span class="dot me-2 <?= $ins['rocket'] ? 'bg-success' : 'bg-secondary' ?>"></span>
                        <span class="small fw-bold">Rocket</span>
                    </div>
                    <div class="gateway-box p-2 border rounded-3 d-flex align-items-center bg-light">
                        <span class="dot me-2 <?= $ins['nagad'] ? 'bg-success' : 'bg-secondary' ?>"></span>
                        <span class="small fw-bold">Nagad</span>
                    </div>
                    <div class="gateway-box p-2 border rounded-3 d-flex align-items-center bg-light">
                        <span class="dot me-2 <?= $ins['bank'] ? 'bg-success' : 'bg-secondary' ?>"></span>
                        <span class="small fw-bold">Bank</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="p-3 bg-dark rounded-3 text-white">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <span class="small text-secondary d-block">bKash Token</span>
                            <span class="fw-bold <?= !empty($ins['bkash_token']) ? 'text-success' : 'text-danger' ?>">
                                <i class="bi <?= !empty($ins['bkash_token']) ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                                <?= !empty($ins['bkash_token']) ? 'Found' : 'Not Found' ?>
                            </span>
                        </div>
                        <div class="col-md-3 border-start border-secondary border-opacity-25">
                            <span class="small text-secondary d-block">Refresh Token</span>
                            <span class="fw-bold <?= !empty($ins['bkash_refresh_token']) ? 'text-success' : 'text-danger' ?>">
                                <i class="bi <?= !empty($ins['bkash_refresh_token']) ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                                <?= !empty($ins['bkash_refresh_token']) ? 'Found' : 'Not Found' ?>
                            </span>
                        </div>
                        <div class="col-md-4 border-start border-secondary border-opacity-25">
                            <span class="small text-secondary d-block">Token Expiry</span>
                            <span class="fw-bold text-info"><?= $ins['bkash_token_expire'] ?: 'N/A' ?></span>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <button class="btn btn-sm btn-outline-light">Refresh Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <p class="small text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">SMS Transmission Status</p>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 border rounded">
                    <div class="icon-circle bg-primary-subtle text-primary me-3">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Sent</small>
                        <h5 class="mb-0 fw-bold"><?= $ins['sms_send'] ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 border rounded">
                    <div class="icon-circle bg-success-subtle text-success me-3">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Success / Error</small>
                        <h5 class="mb-0 fw-bold">
                            <span class="text-success"><?= $ins['sms_success'] ?></span> 
                            <span class="text-muted mx-1">/</span> 
                            <span class="text-danger"><?= $ins['sms_error'] ?></span>
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 border rounded">
                    <div class="icon-circle bg-warning-subtle text-warning me-3">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Balance / Cost</small>
                        <h5 class="mb-0 fw-bold">
                            <span class="text-dark">৳<?= $ins['sms_balance'] ?></span> 
                            <small class="text-muted small fw-normal">(Cost: ৳<?= $ins['sms_cost'] ?>)</small>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .gateway-box { min-width: 100px; }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .icon-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
</style>

    <!-- ====== Backup & Cloud ====== -->
   <div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-cloud-arrow-up-fill me-2 text-info"></i>Backup & Cloud Synchronization
        </h5>
    </div>
    
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="text-center p-3 border rounded bg-light-subtle">
                    <p class="small text-muted mb-1 text-uppercase fw-bold">Daily</p>
                    <span class="badge <?= $ins['daily_backup'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                        <?= $ins['daily_backup'] ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 border rounded bg-light-subtle">
                    <p class="small text-muted mb-1 text-uppercase fw-bold">Monthly</p>
                    <span class="badge <?= $ins['monthly_backup'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                        <?= $ins['monthly_backup'] ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 border rounded bg-light-subtle">
                    <p class="small text-muted mb-1 text-uppercase fw-bold">Cloud Sync</p>
                    <span class="text-<?= $ins['cloud_storage'] ? 'primary' : 'muted' ?> fw-bold">
                        <i class="bi bi-clouds-fill me-1"></i><?= $ins['cloud_storage'] ? 'Active' : 'Offline' ?>
                    </span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 border rounded bg-light-subtle">
                    <p class="small text-muted mb-1 text-uppercase fw-bold">Type</p>
                    <span class="fw-semibold text-dark"><?= $ins['backup'] ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 border-end">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-warning-subtle text-warning me-3">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-muted small">Last Successful Backup</p>
                        <h6 class="mb-0 fw-bold text-dark"><?= $ins['last_backup_time'] ?></h6>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ps-md-4">
                <p class="mb-2 text-muted small fw-bold text-uppercase">Notification Emails</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white text-dark border fw-normal py-2 px-3">
                        <i class="bi bi-envelope me-1 text-muted"></i><?= $ins['backup_mail_2'] ?>
                    </span>
                    <span class="badge bg-white text-dark border fw-normal py-2 px-3">
                        <i class="bi bi-envelope me-1 text-muted"></i><?= $ins['backup_mail_3'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Cloud Storage ID: <span class="text-dark fw-medium">CS-<?= rand(1000,9999) ?></span></small>
            <button class="btn btn-sm btn-outline-info shadow-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Run Manual Backup
            </button>
        </div>
    </div>
</div>

<style>
    .icon-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

    <!-- ====== API & Security ====== -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-dark py-3">
        <h5 class="mb-0 fw-bold text-white">
            <i class="bi bi-shield-lock-fill me-2 text-warning"></i>API & Security Configuration
        </h5>
    </div>
    
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="small text-muted fw-bold">ALGORITHM</label>
                <div class="p-2 border rounded bg-light font-monospace small">
                    <?= $ins['algorithm'] ?>
                </div>
            </div>
            <div class="col-md-4">
                <label class="small text-muted fw-bold">SECRET KEY</label>
                <div class="input-group input-group-sm">
                    <input type="password" class="form-control font-monospace" value="<?= $ins['secret_key'] ?>" readonly id="secretKey">
                    <button class="btn btn-outline-secondary" type="button" onclick="toggleVisibility('secretKey')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label class="small text-muted fw-bold">API KEY</label>
                <div class="input-group input-group-sm">
                    <input type="password" class="form-control font-monospace" value="<?= $ins['api_key'] ?>" readonly id="apiKey">
                    <button class="btn btn-outline-secondary" type="button" onclick="toggleVisibility('apiKey')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <span class="small text-muted"><i class="bi bi-hash me-1"></i> Registration Hash</span>
                        <span class="badge bg-secondary-subtle text-dark font-monospace"><?= $ins['reg_hash'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <span class="small text-muted"><i class="bi bi-calendar-event me-1"></i> Hash Expiry</span>
                        <span class="fw-bold text-danger small"><?= $ins['hash_expire'] ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 border rounded text-center <?= $ins['self_control'] ? 'border-primary' : '' ?>">
                            <small class="text-muted d-block mb-1">Self Control</small>
                            <?php if($ins['self_control']): ?>
                                <span class="text-primary fw-bold"><i class="bi bi-cpu-fill me-1"></i>Enabled</span>
                            <?php else: ?>
                                <span class="text-muted fw-bold">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded text-center <?= $ins['profile_track'] ? 'border-success' : '' ?>">
                            <small class="text-muted d-block mb-1">Profile Track</small>
                            <?php if($ins['profile_track']): ?>
                                <span class="text-success fw-bold"><i class="bi bi-radar me-1"></i>Active</span>
                            <?php else: ?>
                                <span class="text-muted fw-bold">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVisibility(id) {
    const el = document.getElementById(id);
    el.type = el.type === "password" ? "text" : "password";
}
</script>

    <!-- ====== User Stats / Students ====== -->
    <div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-people-fill me-2 text-primary"></i>User Stats
                </h5>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($userlevels as $ul): 
                        $is_chief = $ul['is_chief'];
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-sm rounded-circle <?= $is_chief ? 'bg-warning-subtle text-warning' : 'bg-light text-secondary' ?> me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi <?= $is_chief ? 'bi-star-fill' : 'bi-person' ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold"><?= $is_chief ? 'Chief Administrator' : $ul['userlevel'] ?></h6>
                                <small class="text-muted">Access Level: <?= $is_chief ? 'Full' : 'Limited' ?></small>
                            </div>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3"><?= $ul['total'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-mortarboard-fill me-2 text-success"></i>Students Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 border rounded-3 bg-success bg-opacity-10 border-success-subtle text-center">
                            <div class="text-success mb-1">
                                <i class="bi bi-person-check-fill fs-3"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-0"><?= $students['current_students'] ?></h3>
                            <small class="text-uppercase fw-bold text-success" style="font-size: 0.65rem;">Current</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-3 bg-light text-center">
                            <div class="text-secondary mb-1">
                                <i class="bi bi-archive-fill fs-3"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-0"><?= $students['other_students'] ?></h3>
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Archive</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 rounded bg-light border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 small text-muted">Total Managed Strength</p>
                            <h5 class="mb-0 fw-bold"><?= $students['current_students'] + $students['other_students'] ?></h5>
                        </div>
                        <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>







<!-- ******************************************************************************* -->
 
    <!-- ================= Update Info Modal ================= -->
    <div class="modal fade" id="updateInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="post" id="updateInfoForm">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Update Institute Information
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="update_scinfo">

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-primary fw-bold border-bottom pb-2">
                                <i class="bi bi-info-circle me-1"></i> General Information
                            </h6>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Institute Name</label>
                            <input type="text" name="scname" class="form-control shadow-sm" value="<?= $ins['scname'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Zone / Area</label>
                            <input type="text" name="zone" class="form-control shadow-sm" value="<?= $ins['zone'] ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-primary fw-bold border-bottom pb-2">
                                <i class="bi bi-geo-alt me-1"></i> Address Details
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Address Line 1</label>
                            <input type="text" name="scadd1" class="form-control shadow-sm" value="<?= $ins['scadd1'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Address Line 2</label>
                            <input type="text" name="scadd2" class="form-control shadow-sm" value="<?= $ins['scadd2'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Post Office / Thana</label>
                            <input type="text" name="ps" class="form-control shadow-sm" value="<?= $ins['ps'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">District</label>
                            <input type="text" name="dist" class="form-control shadow-sm" value="<?= $ins['dist'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control shadow-sm" value="<?= $ins['postal_code'] ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-primary fw-bold border-bottom pb-2">
                                <i class="bi bi-telephone me-1"></i> Contact & Online
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control shadow-sm" value="<?= $ins['mobile'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="scmail" class="form-control shadow-sm" value="<?= $ins['scmail'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Website URL</label>
                            <input type="text" name="scweb" class="form-control shadow-sm" value="<?= $ins['scweb'] ?>">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary fw-bold border-bottom pb-2">
                                <i class="bi bi-person-badge me-1"></i> Administration
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Head of Institution Name</label>
                            <input type="text" name="headname" class="form-control shadow-sm" value="<?= $ins['headname'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Designation (Title)</label>
                            <input type="text" name="headtitle" class="form-control shadow-sm" value="<?= $ins['headtitle'] ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- ================= Logo Upload Modal ================= -->
    <div class="modal fade" id="logoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="post" enctype="multipart/form-data" id="logoForm">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-image me-2 text-info"></i>Upload Institute Logo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="action" value="upload_logo">
                    
                    <div class="mb-4">
                        <div id="previewContainer" class="mx-auto border rounded-3 d-flex align-items-center justify-content-center bg-light shadow-inner" 
                             style="width: 150px; height: 150px; overflow: hidden; border: 2px dashed #dee2e6 !important;">
                            <img id="logoPreview" src="<?= institute_logo($ins['sccode']) ?>" class="img-fluid" alt="Preview">
                        </div>
                        <small class="text-muted d-block mt-2">Current Logo Preview</small>
                    </div>

                    <div class="upload-zone p-4 border rounded-3 bg-light position-relative">
                        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                        <p class="mb-2 fw-medium">Choose a file or drag it here</p>
                        <p class="text-muted small mb-3">Recommended size: 512x512px (PNG/JPG)</p>
                        
                        <input type="file" name="logo" id="logoInput" accept="image/*" 
                               class="form-control shadow-none position-absolute top-0 start-0 w-100 h-100 opacity-0" 
                               style="cursor: pointer;" required>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm px-4">Browse Files</button>
                    </div>

                    <div id="fileInfo" class="mt-3 d-none">
                        <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3">
                            <i class="bi bi-check2-circle me-1"></i> <span id="fileName">File Selected</span>
                        </span>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="bi bi-upload me-2"></i>Upload Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .upload-zone:hover {
        background-color: #f0f7ff !important;
        border-color: #0d6efd !important;
    }
    .shadow-inner {
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
    }
</style>

<script>
    document.getElementById('logoInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('logoPreview');
        const fileInfo = document.getElementById('fileInfo');
        const fileNameDisp = document.getElementById('fileName');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
            
            // ফাইল ইনফো দেখানো
            fileInfo.classList.remove('d-none');
            fileNameDisp.textContent = file.name;
        }
    });
</script>










<?php require_once 'footer.php'; ?>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // প্রতিটি Active চেকবক্স চেক করো
        document.querySelectorAll('.active-mod').forEach(cb => {
            const row = cb.closest('.form-check');
            const validCheckbox = row.querySelector('.valid-mod');

            // যদি valid না হয়, active disable
            if (!validCheckbox.checked) {
                cb.disabled = true;
            }
        });

        // Core modules সবসময় checked & disabled valid
        document.querySelectorAll('.valid-mod').forEach(cb => {
            const label = cb.closest('.form-check').querySelector('small');
            if (label && label.textContent.includes('Core')) {
                cb.checked = true;
                cb.disabled = true;
            }
        });

        // Active checkboxes editable only if valid
        document.querySelectorAll('.active-mod').forEach(cb => {
            const row = cb.closest('.form-check');
            const validCheckbox = row.querySelector('.valid-mod');
            if (validCheckbox.disabled && validCheckbox.checked) {
                cb.disabled = false; // Core or Valid editable active
            }
        });
    });
</script>


<script>
    $('#panelForm').on('submit', function (e) {
        e.preventDefault();

        $.post('', $(this).serialize(), function (res) {
            location.reload(); // আপডেটের পর পেজ রিফ্রেশ
        });
    });
</script>


<script>
    // Update Info AJAX
    $('#updateInfoForm').on('submit', function (e) {
        e.preventDefault();
        $.post('', $(this).serialize(), function (res) {
            location.reload();
        });
    });

    // Logo Upload AJAX
    $('#logoForm').on('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        $.ajax({
            url: '',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function (res) {
                location.reload();
            }
        });
    });
</script>