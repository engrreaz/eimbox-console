<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    $current_sccode = isset($_GET['sccode']) ? $_GET['sccode'] : '';

    // ================= SCINFO আপডেট =================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // প্রতিষ্ঠান তথ্য আপডেট
    



        if (isset($_POST['action']) && $_POST['action'] == 'update_modules_info') {

            $valid_module = isset($_POST['valid_module']) ? implode(' | ', $_POST['valid_module']) : '';
            $active_module = isset($_POST['active_module']) ? implode(' | ', $_POST['active_module']) : '';

            $sql = "UPDATE scinfo SET 
                valid_module='$valid_module',
                active_module='$active_module'
            WHERE sccode='$current_sccode' ";

            if ($conn->query($sql)) {
                echo "<div class='alert alert-success'>Modules updated successfully</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }



        // ইনভয়েস আপডেট
        if (isset($_POST['update_invoice'])) {
            $inv_id = intval($_POST['inv_id']);
            $status = $conn->real_escape_string($_POST['due_amount']);
            $conn->query("UPDATE billing_invoices SET status='$status' WHERE id='$inv_id' AND sccode='$current_sccode'");
            echo '<div class="alert alert-success">ইনভয়েস আপডেট হয়েছে।</div>';
        }
    }


    // ======================================================================================
    

    // ================= SCINFO রিড =================
    $ins_q = $conn->query("SELECT * FROM scinfo WHERE sccode='$current_sccode'");
    $ins = $ins_q->fetch_assoc();

    $valid_arr = explode(' | ', $ins['valid_module']);
    $active_arr = explode(' | ', $ins['active_module']);

    // ================= মডিউল তালিকা =================
    $modules_q = $conn->query("SELECT * FROM modulelist where is_public=1 order by slno");
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
    $billing_invoices_q = $conn->query("SELECT * FROM billing_invoices WHERE sccode='$current_sccode' and due_amount>0");
    $invoices = [];
    while ($row = $billing_invoices_q->fetch_assoc()) {
        $invoices[] = $row;
    }

    // ================= ইউজার লেভেল =================
    $userlevels_q = $conn->query("SELECT userlevel, is_chief, COUNT(*) as total FROM usersapp WHERE sccode='$current_sccode' and admin = 0  GROUP BY userlevel, is_chief");
    $userlevels = [];
    while ($row = $userlevels_q->fetch_assoc()) {
        $userlevels[] = $row;
    }
    ?>
    <div class="row ">
        <div class="col-auto">
            <img src="<?= BASE_PATH ?>logo/<?= $current_sccode ?>.png" style="height:80px;">
        </div>

        <div class="col-auto ms-2">
            <div class="mt-3 fw-bold">
                <?= $ins['scname'] ?>
            </div>
            <div>
                Address : <?= $ins['scadd1'] . ', ' . $ins['scadd2'] . ', ' . $ins['ps'] . ', ' . $ins['dist'] ?>
            </div>
            <div class="fs-tiny">
                Mobile : <?= $ins['mobile'] ?>
            </div>

        </div>

        <div class="col ms-3 text-end">
            <?= $ins['sccategory'] ?>
        </div>
    </div>


    <div class="row mt-3">
        <div class="col-md-6">
            <!-- SCINFO -->





            <?php

            $rdgd_date = '2016-01-01';

            $date1 = new DateTime($rdgd_date);
            $date2 = new DateTime(); // আজকের তারিখ
            
            $diff = $date1->diff($date2);

            $years = $diff->y;
            $months = $diff->m;
            $days = $diff->d;



            ?>

            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">Engaged since :</div>
                        <div class="col">
                            <?php
                            echo date('l, d F, Y', strtotime($rdgd_date)) . '<br>';
                            echo "Difference: {$years} Years, {$months} Months, {$days} Days";
                            ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-auto">Last Activities :</div>
                        <div class="col">
                            <?php
                            echo date('l, d F, Y', strtotime($rdgd_date)) . '<br>';
                            echo "{$years} Years, {$months} Months, {$days} Days ago";
                            ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <?php
                        $sql = "
                                SELECT 
                                    SUM(CASE WHEN sessionyear LIKE '%$y_v2%' THEN 1 ELSE 0 END) AS current_students,
                                    SUM(CASE WHEN sessionyear LIKE '%$y_v2%' THEN 0 ELSE 1 END) AS other_students
                                FROM sessioninfo
                                WHERE sccode = '$sccode'
                                ";

                        $q = $conn->query($sql);
                        $data = $q->fetch_assoc();

                        $current_students = $data['current_students'];
                        $other_students = $data['other_students'];


                        ?>
                        <h6> Total Students</h6>


                        <div class="col">
                            Current : <?= $current_students ?>
                        </div>
                        <div class="col">
                            Archive : <?= $other_students ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            today engazed usser
                        </div>
                        <div class="col">
                            engage month
                        </div>

                    </div>

                </div>




            </div>



        </div>

        <div class="col-md-6 h-100">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            Current Package : <b><?= $ins['package_name'] ?></b> (<?= $ins['package_id'] ?>)
                        </div>
                        <div class="col text-end">
                            <button 
    class="btn btn-info btn-sm"
    id="pkgBtn"
    data-package="<?= $ins['package_id'] ?>"
>
    Upgrade / Downgrade
</button>

                        </div>
                    </div>

                    <div class="row mt-3">
                        <h5>Billing Info</h5>

                        <?php
                        $billing_data = explode(' | ', $ins['billing_data']);

                        // ডিফল্ট ভ্যালুগুলো
                        $defaults = [
                            0 => "monthly",
                            1 => "fixed",
                            2 => "0",
                            3 => "0"
                        ];

                        // Billing array ফাইনালাইজ
                        for ($i = 0; $i < 4; $i++) {
                            if (!isset($billing_data[$i]) || $billing_data[$i] === "") {
                                $billing_data[$i] = $defaults[$i];
                            }
                        }

                        ?>
                        <div class="col-3">
                            <b><?= $billing_data[0] ?></b>
                            <br><span class="fs-tiny text-secondary">Bill Cycle</span>
                        </div>

                        <div class="col-3"><b><?= $billing_data[1] ?></b>
                            <br><span class="fs-tiny text-secondary">Policy</span>
                        </div>
                        <div class="col-3"><b><?= $billing_data[2] ?></b>
                            <br><span class="fs-tiny text-secondary">Rate/unit</span>
                        </div>
                        <div class="col-3"><b><?= $billing_data[3] ?></b>
                            <br><span class="fs-tiny text-secondary">Amount</span>
                        </div>
                    </div>
                    <hr class="m-0 mt-3" />
                    <div class="row mt-3">
                        <div class="col">
                            <div id="dues" class="fs-3 fw-bold text-danger"></div>
                            <div class="fs-tiny mt-0 text-senconday">Total Dues</div>
                        </div>

                        <div class="col text-end pt-3">
                            <button id="overdue" style="display:none;" class="btn btn-danger btn-sm">You've some
                                overdue</button>
                        </div>
                    </div>


                </div>
            </div>

            <div class="card h-100 mt-3">
                <div class="card-body">
                    monthly bandwidth --
                    data- size --


                    scan invalid students/ data --
                    make a report for error / invalid information --
                </div>
            </div>










        </div>
    </div>


    <!-- মডিউল তালিকা -->
    <div class="card mb-4 mt-4">
        <div class="card-header fs-4 fw-bold">
            <button type="submit" class="btn btn-primary float-end">Update Modules</button>
            Modules
        </div>

        <form method="post">
            <input type="hidden" name="action" value="update_modules_info">

            <div class="card-body">
                <div class="row">
                    <?php foreach ($modules as $module):
                        $mname = $module['module_name'];
                        $text_color = 'dark';
                        // checkbox checked states
                        $is_valid = in_array($mname, $valid_arr) ? 'checked' : '';
                        $is_active = in_array($mname, $active_arr) ? 'checked' : '';

                        if ($module['core'] == 1) {
                            $is_valid = 'checked';
                            $is_active = 'checked';
                            $text_color = 'gray';
                        }

                        ?>
                        <div class="col-md-3 mb-3">

                            <input class="form-check-input" type="checkbox" name="valid_module[]" title="Subscribed"
                                value="<?= $mname ?>" <?= $is_valid ?>>

                            <input class="form-check-input" type="checkbox" name="active_module[]" title="Active"
                                value="<?= $mname ?>" <?= $is_active ?>>


                            <label class="fw-bold  mb-2 text-<?= $text_color ?>"><?= $mname ?></label>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-footer text-start">

            </div>
        </form>
    </div>



    <div class="card mt-4">
        <div class="card-header fs-4 fw-bold">
            Bill / Invoices
        </div>
        <table class="table table-striped table-sm">
            <tr>
                <th>Invoice No</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $overdue = 0;
            $dues = 0;
            foreach ($invoices as $inv):
                ?>
                <tr>
                    <form method="post">
                        <td><?= $inv['invoice_no'] ?></td>
                        <td><?= $inv['total_amount'] ?></td>
                        <td><?= $inv['invoice_date'] ?></td>
                        <td><input type="text" name="status" class="form-control form-control-sm"
                                value="<?= $inv['due_amount'] ?>"></td>
                        <td>
                            <input type="hidden" name="inv_id" value="<?= $inv['id'] ?>">
                            <button type="submit" name="update_invoice" class="btn btn-sm btn-success">Update</button>
                        </td>
                    </form>
                </tr>
                <?php
                if (strtotime($inv['due_date']) < strtotime($cur)) {
                    $overdue = 1;
                }
                $dues += $inv['due_amount'];

            endforeach; ?>
        </table>
    </div>


    <!-- ইনভয়েস তালিকা -->

    <div class="row mt-4">
        <div class="col-md-6">

            <div class="card">
                <div class="card-header fs-4 fw-bold">
                    User Stats
                </div>
                <div class="row">
                    <table class="table table-responsive  table-sm">
                        <tr>
                            <th style="width:10px; font-weight:bold;"></th>
                            <th>User Role</th>
                            <th>Count</th>
                            <th style="width:10px; font-weight:bold;"></th>
                        </tr>

                        <?php foreach ($userlevels as $ul):
                            $ulevel = ($ul['is_chief'] == 1) ? 'Chief' : $ul['userlevel'];
                            ?>

                            <tr>
                                <td></td>
                                <td><?= $ulevel ?></td>
                                <td><?= $ul['total'] ?></td>
                                <td></td>

                            </tr>

                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    Last Scanned @ <?= date('d/m/y') ?>

                    <?php
                    
                    $dbname = $conn->query("SELECT DATABASE()")->fetch_row()[0];

                    // সব টেবিল বের করা
                    $tables = $conn->query("SHOW TABLES");

                    echo "<table border='1' cellpadding='8' cellspacing='0'>";
                    echo "<tr>
                        <th>Table Name</th>
                        <th>sccode</th>
                        <th>modifieddate</th>
                    </tr>";

                    while ($row = $tables->fetch_array()) {
                        $table = $row[0];

                        // টেবিলের কলাম লোড করা
                        $columns = $conn->query("SHOW COLUMNS FROM `$table`");

                        $has_sccode = "No";
                        $has_modifieddate = "No";

                        while ($col = $columns->fetch_assoc()) {
                            if ($col['Field'] == 'sccode')
                                $has_sccode = "Yes";
                            if ($col['Field'] == 'modifieddate')
                                $has_modifieddate = "Yes";
                        }

                        echo "<tr>
                        <td>$table</td>
                        <td>$has_sccode</td>
                        <td>$has_modifieddate</td>
                    </tr>";
                    }

                    echo "</table>";
                    ?>

                </div>
            </div>
        </div>
    </div>





</div>









<div class="modal fade" id="packageModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="packageForm">
                <div class="modal-header">
                    <h5 class="modal-title">Change Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="packageList" class="list-group">
                        <!-- packages will load here -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Update Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // AJAX ভিত্তিক আপডেট পরে যোগ করা যাবে


    if (<?= $overdue ?> == 1) {
        $("#overdue").show();
    }
    $("#dues").text("<?= $dues ?>.00");
</script>




<script>
let pkgModal = new bootstrap.Modal(document.getElementById('packageModal'));

$('#pkgBtn').on('click', function () {
    let currentPkg = $(this).data('package');

    $.post('settings/get-packages.php', { current: currentPkg }, function (html) {
        $('#packageList').html(html);
        pkgModal.show();
    });
});

$('#packageForm').on('submit', function (e) {
    e.preventDefault();

    let pkg = $('input[name=package]:checked').val();
    if (!pkg) {
        alert('Please select a package');
        return;
    }

    $.post('settings/update-package.php', { package: pkg }, function (res) {
        if (res.status === 'ok') {
            pkgModal.hide();
            showToast('success', 'Package updated successfully');
            location.reload();
        } else {
            showToast('error', res.msg);
        }
    }, 'json');
});
</script>

</body>

</html>