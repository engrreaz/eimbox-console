<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    $current_sccode = isset($_GET['sccode']) ? $_GET['sccode'] : '';

    // ================= SCINFO আপডেট =================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // প্রতিষ্ঠান তথ্য আপডেট
        if (isset($_POST['update_scinfo'])) {
            $scname = $conn->real_escape_string($_POST['scname']);
            $address = $conn->real_escape_string($_POST['address']);
            $phone = $conn->real_escape_string($_POST['phone']);

            $conn->query("UPDATE scinfo SET scname='$scname', address='$address', phone='$phone' WHERE sccode='$current_sccode'");
            echo '<div class="alert alert-success">প্রতিষ্ঠান তথ্য আপডেট হয়েছে।</div>';
        }

        // মডিউল আপডেট
        if (isset($_POST['update_module'])) {
            $modid = intval($_POST['modid']);
            $modname = $conn->real_escape_string($_POST['modname']);
            $conn->query("UPDATE modulelist SET module_name='$modname' WHERE id='$modid' ");
            echo '<div class="alert alert-success">মডিউল আপডেট হয়েছে।</div>';
        }

        // প্যাকেজ আপডেট
        if (isset($_POST['update_package'])) {
            $pkgid = intval($_POST['pkgid']);
            $pkgname = $conn->real_escape_string($_POST['pkgname']);
            $pkgprice = $conn->real_escape_string($_POST['pkgprice']);
            $conn->query("UPDATE packages SET package_name='$pkgname', package_price='$pkgprice' WHERE id='$pkgid' ");
            echo '<div class="alert alert-success">প্যাকেজ আপডেট হয়েছে।</div>';
        }

        // ইনভয়েস আপডেট
        if (isset($_POST['update_invoice'])) {
            $inv_id = intval($_POST['inv_id']);
            $status = $conn->real_escape_string($_POST['status']);
            $conn->query("UPDATE billing_invoices SET status='$status' WHERE id='$inv_id' AND sccode='$current_sccode'");
            echo '<div class="alert alert-success">ইনভয়েস আপডেট হয়েছে।</div>';
        }
    }

    // ================= SCINFO রিড =================
    $ins_q = $conn->query("SELECT * FROM scinfo WHERE sccode='$current_sccode'");
    $ins = $ins_q->fetch_assoc();

    // ================= মডিউল তালিকা =================
    $modules_q = $conn->query("SELECT * FROM modulelist ");
    $modules = [];
    while ($row = $modules_q->fetch_assoc()) {
        $modules[] = $row;
    }

    // ================= প্যাকেজ তালিকা =================
    $packages_q = $conn->query("SELECT * FROM packages ");
    $packages = [];
    while ($row = $packages_q->fetch_assoc()) {
        $packages[] = $row;
    }

    // ================= ইনভয়েস তালিকা =================
    $billing_invoices_q = $conn->query("SELECT * FROM billing_invoices WHERE sccode='$current_sccode'");
    $invoices = [];
    while ($row = $billing_invoices_q->fetch_assoc()) {
        $invoices[] = $row;
    }

    // ================= ইউজার লেভেল =================
    $userlevels_q = $conn->query("SELECT userlevel, COUNT(*) as total FROM usersapp WHERE sccode='$current_sccode' GROUP BY userlevel");
    $userlevels = [];
    while ($row = $userlevels_q->fetch_assoc()) {
        $userlevels[] = $row;
    }
    ?>

    <!-- SCINFO -->
    <h3>প্রতিষ্ঠান তথ্য</h3>
    <form method="post">
        <input type="hidden" name="update_scinfo" value="1">
        <div class="mb-3">
            <label class="form-label">প্রতিষ্ঠানের নাম</label>
            <input type="text" name="scname" class="form-control" value="<?= $ins['scname'] ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">ঠিকানা</label>
            <input type="text" name="address" class="form-control" value="<?= $ins['address'] ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">ফোন</label>
            <input type="text" name="phone" class="form-control" value="<?= $ins['phone'] ?>">
        </div>
        <button type="submit" class="btn btn-primary">আপডেট করুন</button>
    </form>

    <!-- মডিউল তালিকা -->
    <h3 class="mt-4">মডিউল তালিকা</h3>
    <table class="table table-bordered">
        <tr>
            <th>মডিউল নাম</th>
            <th>Action</th>
        </tr>
        <?php foreach ($modules as $mod): ?>
            <tr>
                <form method="post">
                    <td>
                        <input type="text" name="modname" class="form-control" value="<?= $mod['module_name'] ?>">
                    </td>
                    <td>
                        <input type="hidden" name="modid" value="<?= $mod['id'] ?>">
                        <button type="submit" name="update_module" class="btn btn-sm btn-success">Update</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- প্যাকেজ তালিকা -->
    <h3 class="mt-4">প্যাকেজ তালিকা</h3>
    <table class="table table-bordered">
        <tr>
            <th>প্যাকেজ নাম</th>
            <th>মূল্য</th>
            <th>Action</th>
        </tr>
        <?php foreach ($packages as $pkg): ?>
            <tr>
                <form method="post">
                    <td><input type="text" name="pkgname" class="form-control" value="<?= $pkg['package_name'] ?>"></td>
                    <td><input type="text" name="pkgprice" class="form-control" value="<?= $pkg['package_price'] ?>"></td>
                    <td>
                        <input type="hidden" name="pkgid" value="<?= $pkg['id'] ?>">
                        <button type="submit" name="update_package" class="btn btn-sm btn-success">Update</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- ইনভয়েস তালিকা -->
    <h3 class="mt-4">বিল সংক্রান্ত তথ্য</h3>
    <table class="table table-striped">
        <tr>
            <th>Invoice No</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($invoices as $inv): ?>
            <tr>
                <form method="post">
                    <td><?= $inv['invoice_no'] ?></td>
                    <td><?= $inv['total_amount'] ?></td>
                    <td><?= $inv['invoice_date'] ?></td>
                    <td><input type="text" name="status" class="form-control" value="<?= $inv['status'] ?>"></td>
                    <td>
                        <input type="hidden" name="inv_id" value="<?= $inv['id'] ?>">
                        <button type="submit" name="update_invoice" class="btn btn-sm btn-success">Update</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- ইউজার লেভেল -->
    <h3 class="mt-4">ইউজার লেভেল অনুযায়ী ব্যবহারকারী সংখ্যা</h3>
    <table class="table table-bordered">
        <tr>
            <th>User Level</th>
            <th>Total Users</th>
        </tr>
        <?php foreach ($userlevels as $ul): ?>
            <tr>
                <td><?= $ul['userlevel'] ?></td>
                <td><?= $ul['total'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>

<?php require_once 'footer.php'; ?>

<script>
    // AJAX ভিত্তিক আপডেট পরে যোগ করা যাবে
</script>
</body>

</html>