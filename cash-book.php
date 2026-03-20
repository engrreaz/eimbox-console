<?php require_once 'header.php'; ?>

<?php
/** ---------------- ১. ডেটা প্রসেসিং (CRUD) ---------------- **/
if (isset($_COOKIE['form-submitted']) && $_COOKIE['form-submitted'] == "true") {
    $form_submitted = true;
} else {
    $form_submitted = false;
}


// ১.১ এন্ট্রি সেভ/আপডেট লজিক
if (isset($_POST['save_entry']) && $form_submitted) {
    $id = $_POST['entry_id'];
    $date = $_POST['date'];
    $partid = $_POST['partid']; // sub_head_id
    $head_id = $_POST['head_code'];
    $particulars = mysqli_real_escape_string($conn, $_POST['particulars']);
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $memono = $_POST['memono'] ?? '';
    $month = $_POST['month'] ?? date('n');
    $year = $_POST['year'] ?? date('Y');
    $entryby = $usr;
    $new_sccode = $sccode * 10; // সরাসরি স্যানকশনড হিসেবে সেভ হবে

    if (!empty($id)) {
        // Update
        $sql = "UPDATE cashbook SET date='$date', account_head='$head_id', partid='$partid', particulars='$particulars', amount='$amount', type='$type', memono='$memono', month='$month', year='$year' WHERE id='$id'";
        $conn->query($sql);
    } else {
        // Insert (সরাসরি স্যানকশনড হিসেবে সেভ হবে)
        $sql = "INSERT INTO cashbook (sccode, date, account_head, partid, particulars, amount, type, memono, month, year, entryby, entrytime, sessionyear) 
                      VALUES ('$new_sccode', '$date', '$head_id', '$partid', '$particulars', '$amount', '$type', '$memono', '$month', '$year', '$usr', '$cur', '$sessionyear')";
        $conn->query($sql);
    }
    // echo $sql;
    // header("Location: accounts-cashbook-advanced.php"); exit();
}

/** ---------------- ২. ডেটা লোড ---------------- **/

// সাব-হেড লিস্ট (ড্রপডাউনের জন্য) - হেড নামসহ জয়েন করা হয়েছে
$sub_heads = $conn->query("SELECT s.id, s.sub_head, h.account_head ,  s.account_head_id
                           FROM account_sub_head s 
                           LEFT JOIN account_head h ON s.account_head_id = h.id 
                           WHERE s.sccode='$sccode' ORDER BY h.account_head, s.sub_head");

// ক্যাশবুক ডেটা লোড
$datefrom = date('2025-01-01');
$dateto = date('Y-m-t');
$sql_main = "SELECT c.*, s.sub_head as head_name FROM cashbook c 
             LEFT JOIN account_sub_head s ON c.partid = s.id 
             WHERE (c.sccode='$sccode' OR c.sccode='" . ($sccode * 10) . "') 
             AND c.date BETWEEN '$datefrom' AND '$dateto' 
             ORDER BY c.date DESC, c.id DESC";
$res_main = $conn->query($sql_main);

$approved = [];
$pending = [];
$total_in = 0;
$total_ex = 0;

while ($row = $res_main->fetch_assoc()) {
    if ($row['sccode'] == $sccode) {
        $approved[] = $row;
        if ($row['type'] == 'Income')
            $total_in += $row['amount'];
        else
            $total_ex += $row['amount'];
    } else {
        $pending[] = $row;
    }
}
?>


<style>
    :root {
        --bs-primary: #4361ee;
        --income-color: #2ec4b6;
        --expense-color: #e71d36;
        --bg-light: #f8f9fa;
    }

    body {
        background-color: #f4f7fe;
        font-family: 'Inter', sans-serif;
    }

    /* Summary Card Styles */
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    /* Custom Tabs */
    .nav-pills .nav-link {
        border-radius: 10px;
        color: #6c757d;
        font-weight: 600;
        padding: 12px;
    }

    .nav-pills .nav-link.active {
        background-color: var(--bs-primary);
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }

    /* Chips */
    .filter-chip-group {
        display: flex;
        gap: 10px;
        margin: 20px 0;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .chip {
        padding: 8px 20px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #ddd;
        cursor: pointer;
        white-space: nowrap;
        font-size: 14px;
        transition: 0.3s;
    }

    .chip.active {
        background: var(--bs-primary);
        color: #fff;
        border-color: var(--bs-primary);
    }

    /* Transaction Cards */
    .v-card {
        border: none;
        border-radius: 16px;
        margin-bottom: 15px;
        transition: 0.3s;
        border-left: 5px solid transparent;
        background: #fff;
    }

    .v-card.Income {
        border-left-color: var(--income-color);
    }

    .v-card.Expenditure {
        border-left-color: var(--expense-color);
    }

    .v-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05) !important;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .c-inst {
        background: rgba(46, 196, 182, 0.1);
        color: var(--income-color);
    }

    .c-exit {
        background: rgba(231, 29, 54, 0.1);
        color: var(--expense-color);
    }

    /* FAB (Floating Action Button) */
    .m3-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 18px;
        background: var(--bs-primary);
        color: #fff;
        border: none;
        box-shadow: 0 10px 20px rgba(67, 97, 238, 0.4);
        z-index: 1000;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        color: var(--bs-primary);
    }
</style>




<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">Total Income</small>
                    <h4 class="mb-0 fw-bold text-success">৳<?= number_format($total_in) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">Total Expense</small>
                    <h4 class="mb-0 fw-bold text-danger">৳<?= number_format($total_ex) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card stat-card shadow-sm bg-primary text-white">
                <div class="card-body p-3 text-end">
                    <small class="opacity-75 d-block mb-1">Available Balance</small>
                    <h4 class="mb-0 fw-bold">৳<?= number_format($total_in - $total_ex) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills nav-justified bg-white p-2 rounded-3 shadow-sm mb-4" id="cb-tabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#approved-list">
                <i class="bi bi-patch-check-fill me-2"></i>SANCTIONED
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pending-list">
                <i class="bi bi-hourglass-split me-2"></i>PENDING (<?= count($pending) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="approved-list">
            <div class="filter-chip-group">
                <div class="chip active" onclick="filterData('all', 'approved-list')">All Items</div>
                <div class="chip" onclick="filterData('Income', 'approved-list')">Incomes</div>
                <div class="chip" onclick="filterData('Expenditure', 'approved-list')">Expenditures</div>
            </div>

            <div class="list-container">
                <?php foreach ($approved as $v): ?>
                    <div class="card v-card shadow-sm <?= $v['type']; ?>">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box <?= ($v['type'] == 'Income' ? 'c-inst' : 'c-exit'); ?> me-3">
                                    <i class="bi <?= ($v['type'] == 'Income' ? 'bi-plus-lg' : 'bi-dash-lg'); ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark"><?= $v['particulars']; ?></h6>
                                    <small class="text-primary fw-semibold"
                                        style="font-size: 11px;"><?= strtoupper($v['head_name']); ?></small>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M, Y', strtotime($v['date'])); ?>
                                        <span class="mx-1">|</span> Memo: <?= $v['memono'] ?: 'N/A'; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h5
                                        class="mb-1 fw-bold <?= $v['type'] == 'Income' ? 'text-success' : 'text-danger'; ?>">
                                        <?= $v['type'] == 'Income' ? '+' : '-'; ?>৳<?= number_format($v['amount']); ?>
                                    </h5>
                                    <div class="btn-group">
                                        <button class="btn btn-link btn-sm text-primary p-0 me-2"
                                            onclick='editEntry(<?= json_encode($v); ?>)'><i
                                                class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-link btn-sm text-danger p-0"
                                            onclick="deleteEntry(<?= $v['id']; ?>)"><i class="bi bi-trash3"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pending-list">
            <?php foreach ($pending as $v): ?>
                <div class="card v-card shadow-sm border-warning mb-3" style="background: #fffdf0;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-bold text-warning-emphasis"><?= $v['particulars']; ?></h6>
                                <small class="text-muted">By: <?= $v['entryby']; ?> |
                                    <?= date('d M', strtotime($v['date'])); ?></small>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-2 fw-bold text-dark">৳<?= number_format($v['amount']); ?></h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-success rounded-pill px-3"
                                        onclick="processVoucher(<?= $v['id']; ?>, 2)">Approve</button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="processVoucher(<?= $v['id']; ?>, 1)">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<button class="m3-fab" onclick="addEntry()"><i class="bi bi-plus-lg fs-4"></i></button>




</div>

<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content m3-modal-content border-0 shadow-lg" style="width:90vw; margin:auto; padding:20px;">
            <h5 class="fw-bold mb-4" id="modalTitle" style="color: var(--m3-primary);">Add Transaction</h5>
            <form method="post" id="entryForm">
                <input type="hidden" name="entry_id" id="entry_id">
                <input type="hidden" name="head_code" id="head_code">

                <div class="row g-2">
                    <div class="col-6">
                        <div class="m3-floating-group">
                            <i class="bi bi-calendar-event m3-field-icon"></i>
                            <input type="date" name="date" id="e_date" class="m3-input-floating"
                                value="<?php echo date('Y-m-d'); ?>" required>
                            <label class="m3-floating-label">DATE</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="m3-floating-group">
                            <i class="bi bi-arrow-left-right m3-field-icon"></i>
                            <select name="type" id="e_type" class="m3-select-floating" required>
                                <option value="Income">Income</option>
                                <option value="Expenditure">Expenditure</option>
                            </select>
                            <label class="m3-floating-label">TYPE</label>
                        </div>
                    </div>
                </div>

                <div class="m3-floating-group">
                    <i class="bi bi-tags m3-field-icon"></i>
                    <select name="partid" id="e_partid" class="m3-select-floating" required>
                        <option value=""></option>

                        <?php
                        $sub_heads->data_seek(0);

                        $current_head = '';

                        while ($sh = $sub_heads->fetch_assoc()):

                            // নতুন group শুরু
                            if ($current_head != $sh['account_head']) {

                                // আগের group close
                                if ($current_head != '') {
                                    echo '</optgroup>';
                                }

                                echo '<optgroup label="' . $sh['account_head'] . '">';

                                $current_head = $sh['account_head'];
                            }
                            ?>

                            <option value="<?php echo $sh['id']; ?>" data-head="<?php echo $sh['account_head_id']; ?>">
                                <?php echo $sh['sub_head']; ?>
                            </option>

                        <?php endwhile;

                        // শেষ group close
                        if ($current_head != '') {
                            echo '</optgroup>';
                        }
                        ?>
                    </select>

                    <label class="m3-floating-label">ACCOUNT SECTOR (SUB-HEAD)</label>
                </div>

                <div class="m3-floating-group">
                    <i class="bi bi-pencil m3-field-icon"></i>
                    <input type="text" name="particulars" id="e_particulars" class="m3-input-floating" placeholder=" "
                        required>
                    <label class="m3-floating-label">DESCRIPTION</label>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="m3-floating-group">
                            <i class="bi bi-cash-stack m3-field-icon"></i>
                            <input type="number" name="amount" id="e_amount" class="m3-input-floating" placeholder=" "
                                required>
                            <label class="m3-floating-label">AMOUNT</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="m3-floating-group">
                            <i class="bi bi-hash m3-field-icon"></i>
                            <input type="text" name="memono" id="e_memono" class="m3-input-floating" placeholder=" ">
                            <label class="m3-floating-label">MEMO NO.</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-fill py-2"
                        style="border-radius:12px; font-weight:700;" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="save_entry" class="btn btn-primary flex-fill py-2"
                        style="border-radius:12px; font-weight:700;">SAVE RECORD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    const entryModal = new bootstrap.Modal('#entryModal');

    // ১. ফিল্টারিং লজিক
    function filterData(type, tabId) {
        const tab = document.getElementById(tabId);
        const chips = tab.querySelectorAll('.chip');
        const cards = tab.querySelectorAll('.v-card');

        chips.forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');

        cards.forEach(card => {
            if (type === 'all' || card.classList.contains(type)) card.style.display = 'block';
            else card.style.display = 'none';
        });
    }

    // ২. অ্যাড/এডিট ফাংশন
    function addEntry() {
        document.getElementById('modalTitle').innerText = "New Voucher";
        document.getElementById('entry_id').value = "";
        document.getElementById('entryForm').reset();
        document.getElementById('e_type').value = 'Expenditure';

        $('#e_type').trigger('change');

        entryModal.show();

    }

    function editEntry(data) {
        document.getElementById('modalTitle').innerText = "Edit Voucher";
        document.getElementById('entry_id').value = data.id;
        document.getElementById('e_date').value = data.date;
        document.getElementById('e_partid').value = data.partid;
        document.getElementById('e_particulars').value = data.particulars;
        document.getElementById('e_amount').value = data.amount;
        document.getElementById('e_type').value = data.type;
        document.getElementById('e_memono').value = data.memono;
        $('#e_type').trigger('change');
        entryModal.show();
    }

    // ৩. ডিলিট এবং অ্যাপ্রুভ
    function deleteEntry(id) {
        Swal.fire({ title: 'Delete Item?', text: "This will remove the transaction forever!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#B3261E', confirmButtonText: 'Yes, Delete' })
            .then((result) => { if (result.isConfirmed) { window.location.href = "accounts-cashbook-advanced.php?del_id=" + id; } });
    }

    function processVoucher(id, tail) {
        $.post("delcashbook.php", { sccode: '<?php echo $sccode; ?>', id: id, tail: tail }, function () { setCookie("form-submitted", "true", 1); window.location.href = "cashbookview.php"; });
    }
</script>

<script>
    document.getElementById('e_type').addEventListener('change', function () {

        let type = this.value;

        $.ajax({
            url: 'ajax/ajax-get-subhead.php',
            type: 'POST',
            data: { type: type },
            success: function (data) {
                $('#e_partid').html(data);
            }
        });

    });


    $('#e_partid').on('change', function () {

        let opt = this.options[this.selectedIndex];

        $('#head_code').val(opt.getAttribute('data-head'));

    });

</script>
<!-- ----------------------------------- -->
</body>

</html>