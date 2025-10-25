<?php
include('../core/config.php');
include('../core/db.php');

$action = $_POST['action'] ?? '';

/* ==========================
   1️⃣ ADD PACKAGE
   ========================== */
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

    $id = $_POST['data_id'];
    $package_id = $_POST['package_id'];
    $ins_cat = $_POST['ins_cat'];
    $billing = $_POST['billing_cycle'];
    $payment = $_POST['payment_model'];
    $status = $_POST['status'];

    $pA = $_POST['price_cat_a'] ?? 0;
    $pB = $_POST['price_cat_b'] ?? 0;
    $pC = $_POST['price_cat_c'] ?? 0;
    $pD = $_POST['price_cat_d'] ?? 0;
    $pE = $_POST['price_cat_e'] ?? 0;

    // চেক করো আগেই সেটিং আছে কিনা
    $check = $conn->prepare("SELECT id FROM package_settings WHERE package_id=? AND ins_cat=?");
    $check->bind_param("is", $package_id, $ins_cat);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // 🔹 Update existing
        $stmt = $conn->prepare("UPDATE package_settings SET 
            price_cat_a=?, price_cat_b=?, price_cat_c=?, price_cat_d=?, price_cat_e=?,
            billing_cycle=?, payment_model=?, status=?
            WHERE package_id=? AND ins_cat=? AND id=?");
        $stmt->bind_param(
            "dddddsssisi",
            $pA, $pB, $pC, $pD, $pE,
            $billing, $payment, $status,
            $package_id, $ins_cat, $id
        );

        if ($stmt->execute()) {
            echo "✅ Settings updated successfully!";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
        $stmt->close();

    } else {
        // 🔹 Insert new
        $stmt = $conn->prepare("INSERT INTO package_settings 
            (package_id, ins_cat, price_cat_a, price_cat_b, price_cat_c, price_cat_d, price_cat_e, billing_cycle, payment_model, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isddddssss",
            $package_id, $ins_cat,
            $pA, $pB, $pC, $pD, $pE,
            $billing, $payment, $status
        );

        if ($stmt->execute()) {
            echo "✅ New settings added successfully!";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    }

    $check->close();
    exit;
}

// ==============================
// LOAD $settings
// ===============================

if ($action == 'load_settings') {
    $package_id = $_POST['package_id'];
    $query = "SELECT * FROM package_settings WHERE package_id='$package_id' ORDER BY ins_cat ASC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        echo "<tr><td colspan='9' class='text-center text-muted'>No settings found for this package</td></tr>";
        exit;
    }

    while ($r = mysqli_fetch_assoc($result)) {
        echo "
        <tr>
          <td>{$r['ins_cat']}</td>
          <td>{$r['billing_cycle']}</td>
          <td>{$r['payment_model']}</td>
          <td>{$r['status']}</td>
          <td>{$r['price_cat_a']}</td>
          <td>{$r['price_cat_b']}</td>
          <td>{$r['price_cat_c']}</td>
          <td>{$r['price_cat_d']}</td>
          <td>{$r['price_cat_e']}</td>
          <td>
            <button class='btn btn-sm btn-outline-primary btn-edit-setting' 
                data-id='{$r['id']}'
                data-inscat='{$r['ins_cat']}'
                data-billing='{$r['billing_cycle']}'
                data-payment='{$r['payment_model']}'
                data-status='{$r['status']}'
                data-pa='{$r['price_cat_a']}'
                data-pb='{$r['price_cat_b']}'
                data-pc='{$r['price_cat_c']}'
                data-pd='{$r['price_cat_d']}'
                data-pe='{$r['price_cat_e']}'>
              <i class='bi bi-pencil'></i>
            </button>
          </td>
        </tr>";
    }
    exit;
}


echo "❌ Invalid Action";
?>
