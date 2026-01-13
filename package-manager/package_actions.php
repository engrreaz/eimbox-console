<?php
include('../core/config.php');
include('../core/db.php');

$action = $_POST['action'] ?? '';

/* ==========================
   1️⃣ ADD PACKAGE
   ========================== */
// GET PACKAGE
if ($action == 'get_package') {
    $id = intval($_POST['id']);
    $r = $conn->query("SELECT * FROM packages WHERE id=$id")->fetch_assoc();
    echo json_encode($r);
    exit;
}

// UPDATE PACKAGE
if ($action == 'update_package') {
    $stmt = $conn->prepare("UPDATE packages SET 
        serial=?,package_name=?,package_code=?,description=?,status=?
        WHERE id=?");
    $stmt->bind_param(
        "issssi",
        $_POST['serial'],
        $_POST['package_name'],
        $_POST['package_code'],
        $_POST['description'],
        $_POST['status'],
        $_POST['id']
    );
    $stmt->execute();
    echo "Package updated";
    exit;
}




if ($action == 'add_package') {

    $serial = $_POST['serial'];
    $name = $_POST['package_name'];
    $code = $_POST['package_code'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO packages (serial, package_name, package_code, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $serial, $name, $code, $desc);

    if ($stmt->execute()) {
        echo "✅ Package added successfully!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

/* ==========================
   2️⃣ LOAD PACKAGES  ---------------------------------
   ========================== */
if ($action == 'load_packages') {
    $result = $conn->query("SELECT * FROM packages ORDER BY serial ASC");

    if ($result->num_rows == 0) {
        echo "<tr><td colspan='6' class='text-center text-muted'>No packages found</td></tr>";
        exit;
    }

    while ($r = $result->fetch_assoc()) {
        $status = $r['status'] ?? 'inactive';
        $badge = ($status == 'active') ? 'success' : 'secondary';

        echo "
        <tr>
          <td>{$r['serial']}</td>
          <td>{$r['package_name']}</td>
          <td>{$r['package_code']}</td>
          <td>{$r['description']}</td>
          <td><span class='badge bg-{$badge}'>{$status}</span></td>
          <td>
            <button class='btn btn-sm btn-outline-primary btn-settings' 
                    data-id='{$r['id']}' data-name='{$r['package_name']}'>
              <i class='bi bi-gear'></i> Settings
            </button>
          </td>
        </tr>";
    }
    exit;
}

/* ==========================
   3️⃣ SAVE PACKAGE SETTINGS
   ========================== */
if ($action == 'save_settings') {

    $id = intval($_POST['id'] ?? 0);
    $package_id = intval($_POST['package_id']);
    $ins_tier = $_POST['ins_tier'];
    $billing = $_POST['billing_cycle'];
    $payment = $_POST['payment_model'];
    $status = $_POST['status'];
    $price = floatval($_POST['price']);

    $total_uses_limit = intval($_POST['total_uses_limit'] ?? 0);
    $photo_upload = intval($_POST['photo_upload'] ?? 0);
    $print_limit = intval($_POST['print'] ?? 0);

    $modulesArr = $_POST['module'] ?? [];
    $modules = implode(',', $modulesArr);
    $panelsArr = $_POST['panel'] ?? [];
    $panels = implode(',', $panelsArr);

 

    // check existing row
    $check = $conn->prepare("
        SELECT id FROM package_settings 
        WHERE package_id=? AND ins_tier=? AND billing_cycle=?
    ");
    $check->bind_param("iss", $package_id, $ins_tier, $billing);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {

        // UPDATE
        $stmt = $conn->prepare("
            UPDATE package_settings SET
                price=?,
                payment_model=?,
                status=?,
                total_uses_limit=?,
                photo_upload=?,
                print=?,
                module=?, panel=?
            WHERE package_id=? AND ins_tier=? AND billing_cycle=? AND id=?
        ");

        $stmt->bind_param(
            "dssiiississi",
            $price,
            $payment,
            $status,
            $total_uses_limit,
            $photo_upload,
            $print_limit,
            $modules,
            $panels,
            $package_id,
            $ins_tier,
            $billing,
            $id
        );

        $stmt->execute();
        echo "Settings updated successfully";

    } else {

        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO package_settings
            (package_id, ins_tier, price, billing_cycle, payment_model, status,
             total_uses_limit, photo_upload, print, module, panel)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "isdsssiiiss",
            $package_id,
            $ins_tier,
            $price,
            $billing,
            $payment,
            $status,
            $total_uses_limit,
            $photo_upload,
            $print_limit,
            $modules,
            $panels
        );

        $stmt->execute();
        echo "New setting added successfully";
    }

    exit;
}



// ==============================
// LOAD $settings
// ===============================

if ($action == 'load_settings') {
    $package_id = intval($_POST['package_id']);
    $q = $conn->query("SELECT * FROM package_settings 
                       WHERE package_id=$package_id 
                       ORDER BY ins_tier,billing_cycle");

    while ($r = $q->fetch_assoc()) {
        echo "<tr>
          <td>{$r['ins_tier']}</td>
          <td>{$r['billing_cycle']}</td>
          <td>{$r['payment_model']}</td>
          <td>{$r['price']}</td>
          <td>{$r['status']}</td>
          <td>
            <button class='btn btn-sm btn-edit-setting'
             data-package-id='{$r['package_id']}'
             data-ins-tier='{$r['ins_tier']}'
             data-billing='{$r['billing_cycle']}'>
            ✏</button>
          </td>
        </tr>";
    }
    exit;
}


echo "❌ Invalid Action";
?>