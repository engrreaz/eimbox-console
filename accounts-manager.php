<?php
require_once 'header.php';

$alert_msg = '';
$alert_type = '';

// ==========================================
// 1. IMPORT DEFAULT ACCOUNT HEADS & SUB-HEADS
// ==========================================
if (isset($_POST['import_default_heads'])) {
    $imported_heads = 0;
    $imported_subs = 0;

    $def_heads_res = $conn->query("SELECT * FROM account_head_default ORDER BY id ASC");
    if ($def_heads_res && $def_heads_res->num_rows > 0) {
        while ($dh = $def_heads_res->fetch_assoc()) {
            $h_name = trim($dh['account_head'] ?? $dh['head_name'] ?? '');
            if ($h_name === '') continue;

            $h_code = trim($dh['head_code'] ?? '');
            $h_bn = trim($dh['head_name_bn'] ?? '');
            $h_type = trim($dh['head_type'] ?? 'expense');
            $disp_order = intval($dh['display_order'] ?? 0);

            // চেক করা অলরেডি এই sccode-এ হেডটি আছে কিনা (head_name কলাম)
            $stmt = $conn->prepare("SELECT id FROM account_head WHERE sccode = ? AND head_name = ? LIMIT 1");
            $stmt->bind_param("ss", $sccode, $h_name);
            $stmt->execute();
            $check_res = $stmt->get_result();

            if ($check_res && $check_res->num_rows > 0) {
                $head_id = $check_res->fetch_assoc()['id'];
            } else {
                $ins_stmt = $conn->prepare("INSERT INTO account_head (sccode, head_code, head_name, head_name_bn, head_type, is_system_default, status, display_order, modifieddate) VALUES (?, ?, ?, ?, ?, 1, 1, ?, NOW())");
                $ins_stmt->bind_param("sssssi", $sccode, $h_code, $h_name, $h_bn, $h_type, $disp_order);
                $ins_stmt->execute();
                $head_id = $ins_stmt->insert_id;
                $imported_heads++;
            }

            // এই হেডের আওতায় থাকা ডিফল্ট সাব-হেডগুলো ইমপোর্ট
            $stmt_sub = $conn->prepare("SELECT * FROM account_sub_head_default WHERE account_head = ? OR head_code = ?");
            $stmt_sub->bind_param("ss", $h_name, $h_code);
            $stmt_sub->execute();
            $def_subs_res = $stmt_sub->get_result();

            if ($def_subs_res) {
                while ($ds = $def_subs_res->fetch_assoc()) {
                    $s_name = trim($ds['sub_head'] ?? '');
                    if ($s_name === '') continue;

                    $s_code = trim($ds['sub_head_code'] ?? '');
                    $s_bn = trim($ds['sub_head_bn'] ?? '');
                    $inc = intval($ds['income'] ?? 0);
                    $exp = intval($ds['expenditure'] ?? 1);
                    $sub_order = intval($ds['display_order'] ?? 0);

                    // সাব-হেড ডুপ্লিকেট চেক
                    $chk_sub = $conn->prepare("SELECT id FROM account_sub_head WHERE sccode = ? AND account_head_id = ? AND sub_head = ? LIMIT 1");
                    $chk_sub->bind_param("sis", $sccode, $head_id, $s_name);
                    $chk_sub->execute();
                    $chk_res = $chk_sub->get_result();

                    if ($chk_res && $chk_res->num_rows === 0) {
                        $ins_sub = $conn->prepare("INSERT INTO account_sub_head (sccode, account_head_id, sub_head_code, sub_head, sub_head_bn, income, expenditure, status, display_order, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())");
                        $ins_sub->bind_param("sisssiii", $sccode, $head_id, $s_code, $s_name, $s_bn, $inc, $exp, $sub_order);
                        $ins_sub->execute();
                        $imported_subs++;
                    }
                }
            }
        }
        $alert_msg = "Successfully imported $imported_heads new Heads and $imported_subs Sub-Heads from default templates!";
        $alert_type = 'success';
    } else {
        $alert_msg = "No default account templates found in account_head_default table.";
        $alert_type = 'warning';
    }
}

// ==========================================
// 2. ACCOUNT HEAD (ADD / UPDATE / DELETE)
// ==========================================
if (isset($_POST['save_head'])) {
    $head_name = trim($_POST['head_name'] ?? '');
    $head_id = intval($_POST['head_id'] ?? 0);

    if ($head_name !== '') {
        if ($head_id > 0) {
            $stmt = $conn->prepare("UPDATE account_head SET head_name = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
            $stmt->bind_param("sis", $head_name, $head_id, $sccode);
            $stmt->execute();

            $alert_msg = "Account Head updated successfully.";
            $alert_type = 'success';
        } else {
            $stmt = $conn->prepare("INSERT INTO account_head (sccode, head_name, status, modifieddate) VALUES (?, ?, 1, NOW())");
            $stmt->bind_param("ss", $sccode, $head_name);
            $stmt->execute();
            $alert_msg = "Account Head created successfully.";
            $alert_type = 'success';
        }
    }
}

if (isset($_GET['del_head'])) {
    $del_id = intval($_GET['del_head']);
    if ($del_id > 0) {
        // ১. প্রথমে চাইল্ড সাব-হেড ডিলিট করতে হবে (FK RESTRICT কনস্ট্রেইন্ট বজায় রাখার জন্য)
        $stmt_sub = $conn->prepare("DELETE FROM account_sub_head WHERE account_head_id = ? AND sccode = ?");
        $stmt_sub->bind_param("is", $del_id, $sccode);
        $stmt_sub->execute();

        // ২. এরপর প্যারেন্ট অ্যাকাউন্ট হেড ডিলিট করতে হবে
        $stmt_head = $conn->prepare("DELETE FROM account_head WHERE id = ? AND sccode = ?");
        $stmt_head->bind_param("is", $del_id, $sccode);
        $stmt_head->execute();

        $alert_msg = "Account Head and related Sub-Heads deleted successfully.";
        $alert_type = 'danger';
    }
}

// ==========================================
// 3. SUB HEAD (ADD / UPDATE / DELETE)
// ==========================================
if (isset($_POST['save_sub'])) {
    $sub_id = intval($_POST['sub_id'] ?? 0);
    $h_id = intval($_POST['h_id'] ?? 0);
    $sub_name = trim($_POST['sub_name'] ?? '');
    $inc = isset($_POST['income']) ? 1 : 0;
    $exp = isset($_POST['expenditure']) ? 1 : 0;

    if ($sub_name !== '' && $h_id > 0) {
        if ($sub_id > 0) {
            $stmt = $conn->prepare("UPDATE account_sub_head SET sub_head = ?, income = ?, expenditure = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
            $stmt->bind_param("siiis", $sub_name, $inc, $exp, $sub_id, $sccode);
            $stmt->execute();
            $alert_msg = "Sub-Head updated successfully.";
            $alert_type = 'success';
        } else {
            $stmt = $conn->prepare("INSERT INTO account_sub_head (sccode, account_head_id, sub_head, income, expenditure, status, modifieddate) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("sisii", $sccode, $h_id, $sub_name, $inc, $exp);
            $stmt->execute();
            $alert_msg = "Sub-Head created successfully.";
            $alert_type = 'success';
        }
    }
}

if (isset($_GET['del_sub'])) {
    $del_sub_id = intval($_GET['del_sub']);
    if ($del_sub_id > 0) {
        $stmt = $conn->prepare("DELETE FROM account_sub_head WHERE id = ? AND sccode = ?");
        $stmt->bind_param("is", $del_sub_id, $sccode);
        $stmt->execute();
        $alert_msg = "Sub-Head deleted.";
        $alert_type = 'danger';
    }
}

// ==========================================
// 4. DATA FETCHING & SUMMARY CALCULATIONS
// ==========================================
$heads_query = $conn->query("SELECT *, head_name AS account_head FROM account_head WHERE sccode='$sccode' ORDER BY display_order ASC, id ASC");
$all_heads = [];
if ($heads_query) {
    while ($h = $heads_query->fetch_assoc()) {
        $all_heads[] = $h;
    }
}

$sub_heads_res = $conn->query("SELECT s.*, COALESCE(h.head_name, '') AS account_head 
                               FROM account_sub_head s 
                               LEFT JOIN account_head h ON s.account_head_id = h.id 
                               WHERE s.sccode='$sccode' 
                               ORDER BY s.display_order ASC, s.id ASC");
$sub_heads = [];
$flat_sub_heads = [];
$total_income = 0;
$total_expense = 0;
$total_subs = 0;

if ($sub_heads_res) {
    while ($row = $sub_heads_res->fetch_assoc()) {
        $sub_heads[$row['account_head_id']][] = $row;
        $flat_sub_heads[] = $row;
        if ($row['income']) $total_income++;
        if ($row['expenditure']) $total_expense++;
        $total_subs++;
    }
}
$head_count = count($all_heads);
?>

<style>
    /* Light & Dark Mode Universal Card Styles */
    .account-card {
        padding: 16px;
        margin-bottom: 16px;
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.08));
        border-radius: 12px;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .account-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .sub-head-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background-color: var(--bs-tertiary-bg, #f8f9fa);
        border: 1px solid var(--bs-border-color, #edf0f2);
        border-radius: 8px;
        margin-bottom: 6px;
    }

    .sub-head-title {
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--bs-body-color, #333333);
    }

    .badge-m3 {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .badge-inc {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .badge-exp {
        background: #ffebee;
        color: #b3261e;
    }

    .tonal-icon-btn {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .tonal-icon {
        width: 28px;
        height: 28px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .c-info {
        background-color: rgba(67, 97, 238, 0.12);
        color: #4361ee;
    }

    .c-info:hover {
        background-color: #4361ee;
        color: #ffffff;
    }

    .c-exit {
        background-color: rgba(225, 29, 72, 0.12);
        color: #e11d48;
    }

    .c-exit:hover {
        background-color: #e11d48;
        color: #ffffff;
    }

    .c-success {
        background-color: rgba(30, 143, 7, 0.12);
        color: #1e8f07;
    }

    .c-success:hover {
        background-color: #0da019;
        color: #ffffff;
    }

    .m3-modal-content {
        background-color: var(--bs-card-bg, #ffffff);
        color: var(--bs-body-color, #333333);
        border-radius: 16px;
        padding: 20px;
        border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.1));
    }

    .m3-fab-add {
        position: fixed;
        bottom: 85px;
        right: 25px;
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #008080;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 6px 16px rgba(0, 128, 128, 0.35);
        border: none;
        z-index: 1000;
        transition: transform 0.2s ease;
    }

    .m3-fab-add:hover {
        transform: scale(1.06);
        color: white;
    }

    /* Dark Mode Specific Overrides */
    [data-bs-theme="dark"] .account-card,
    html.dark-style .account-card,
    body.dark-style .account-card {
        background-color: var(--bs-card-bg, #2b2c40);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .sub-head-item,
    html.dark-style .sub-head-item,
    body.dark-style .sub-head-item {
        background-color: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .sub-head-title,
    html.dark-style .sub-head-title {
        color: #dbdade;
    }

    [data-bs-theme="dark"] .m3-modal-content,
    html.dark-style .m3-modal-content {
        background-color: var(--bs-card-bg, #2b2c40);
        color: #dbdade;
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .badge-inc,
    html.dark-style .badge-inc {
        background: rgba(46, 125, 50, 0.25);
        color: #81c784;
    }

    [data-bs-theme="dark"] .badge-exp,
    html.dark-style .badge-exp {
        background: rgba(179, 38, 30, 0.25);
        color: #e57373;
    }

    [data-bs-theme="dark"] .c-info,
    html.dark-style .c-info {
        background-color: rgba(67, 97, 238, 0.2);
        color: #8fa0ff;
    }

    [data-bs-theme="dark"] .c-exit,
    html.dark-style .c-exit {
        background-color: rgba(225, 29, 72, 0.2);
        color: #ff859d;
    }

    [data-bs-theme="dark"] .c-success,
    html.dark-style .c-success {
        background-color: rgba(30, 143, 7, 0.2);
        color: #66bb6a;
    }

    /* M3 BOTTOM SNACKBAR / TOAST */
    #m3Snackbar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #313033;
        color: #F4EFF4;
        padding: 12px 22px;
        border-radius: 28px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
        font-size: 0.88rem;
        font-weight: 500;
        z-index: 1090;
        transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1), opacity 0.3s ease;
        opacity: 0;
        pointer-events: none;
        max-width: 90%;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #m3Snackbar.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    #m3Snackbar.snackbar-success { background: #198754; color: #fff; }
    #m3Snackbar.snackbar-error { background: #BA1A1A; color: #fff; }
    #m3Snackbar.snackbar-info { background: #4361ee; color: #fff; }
    #m3Snackbar.snackbar-warning { background: #E65100; color: #fff; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-primary"><i class="bi bi-wallet2 me-2"></i>Accounts Manager</h4>
            <p class="text-muted mb-0 small">Institutional Chart of Accounts (Income / Credit & Expense / Debit Heads)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Real-time Filter -->
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchHeadInput" class="form-control border-start-0" placeholder="Search Head/Sub-Head...">
            </div>

            <!-- Import Default Button -->
            <form id="importDefaultForm" method="POST" class="d-inline">
                <input type="hidden" name="import_default_heads" value="1">
                <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm" onclick="confirmImportDefaults('importDefaultForm')">
                    <i class="bi bi-cloud-arrow-down me-1"></i> Import Defaults
                </button>
            </form>

            <!-- Add Head Button -->
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="addHead()">
                <i class="bi bi-plus-circle me-1"></i> Add Head
            </button>
        </div>
    </div>

    <!-- Alert Message -->
    <?php if ($alert_msg): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($alert_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- View & Classification Navigation Tabs -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Classification Tabs (All / Income / Expense) -->
            <ul class="nav nav-pills gap-1" id="accountClassificationTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm px-3 filter-tab-btn" data-filter="all">
                        <i class="bi bi-collection me-1"></i> All Heads
                        <span class="badge bg-primary ms-1"><?= $head_count ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm px-3 text-success filter-tab-btn" data-filter="income">
                        <i class="bi bi-arrow-down-left-circle-fill me-1"></i> Income / Credit (আয় খাত)
                        <span class="badge bg-success ms-1"><?= $total_income ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm px-3 text-danger filter-tab-btn" data-filter="expense">
                        <i class="bi bi-arrow-up-right-circle-fill me-1"></i> Expense / Debit (ব্যয় খাত)
                        <span class="badge bg-danger ms-1"><?= $total_expense ?></span>
                    </button>
                </li>
            </ul>

            <!-- View Switcher (Cards vs Table List) -->
            <div class="btn-group btn-group-sm" role="group" aria-label="View Switcher">
                <button type="button" class="btn btn-outline-secondary active" id="viewCardsBtn" onclick="switchView('cards')">
                    <i class="bi bi-grid-fill me-1"></i> Hierarchy Cards
                </button>
                <button type="button" class="btn btn-outline-secondary" id="viewTableBtn" onclick="switchView('table')">
                    <i class="bi bi-table me-1"></i> Debit / Credit Table
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <?php if ($head_count === 0): ?>
        <div class="card shadow-sm border-0 text-center py-5 my-4">
            <div class="card-body">
                <div class="mb-3 text-muted">
                    <i class="bi bi-folder-x" style="font-size: 3.5rem; color: #adb5bd;"></i>
                </div>
                <h5 class="fw-bold text-body">No Account Heads Found</h5>
                <p class="text-muted small mx-auto" style="max-width: 480px;">
                    Your chart of accounts is currently empty. You can either import the standard institutional default heads or create your custom account heads.
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <form id="importDefaultFormEmpty" method="POST" class="d-inline">
                        <input type="hidden" name="import_default_heads" value="1">
                        <button type="button" class="btn btn-primary btn-sm px-4 py-2 shadow-sm" onclick="confirmImportDefaults('importDefaultFormEmpty')">
                            <i class="bi bi-cloud-arrow-down me-1"></i> Import Standard Defaults
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4 py-2" onclick="addHead()">
                        <i class="bi bi-plus-lg me-1"></i> Create Custom Head
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>

        <!-- VIEW 1: CARDS HIERARCHY VIEW -->
        <div id="cardsViewContainer">
            <?php foreach ($all_heads as $h): ?>
                <div class="card account-card head-item-card" data-title="<?= strtolower(htmlspecialchars($h['account_head'])) ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-box text-primary" style="font-size: 1.5rem;">
                                <i class="bi bi-folder2-open"></i>
                            </div>
                            <div>
                                <div class="head-title text-primary fw-bold" style="font-size: 1rem;">
                                    <?= htmlspecialchars($h['account_head']) ?>
                                </div>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    HEAD ID: #<?= $h['id'] ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="tonal-icon-btn c-success" title="Add Sub-Sector"
                                onclick="addSub('<?= $h['id'] ?>', '<?= htmlspecialchars(addslashes($h['account_head'])) ?>')">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="tonal-icon-btn c-info" title="Edit Head"
                                onclick="editHead('<?= $h['id'] ?>', '<?= htmlspecialchars(addslashes($h['account_head'])) ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="tonal-icon-btn c-exit" title="Delete Head"
                                onclick="deleteItem('accounts-manager.php?del_head=<?= $h['id'] ?>', true)">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>

                    <div style="border-top: 1px dashed var(--bs-border-color, #e9ecef); padding-top: 10px; margin-top: 4px;">
                        <?php if (isset($sub_heads[$h['id']]) && count($sub_heads[$h['id']]) > 0): ?>
                            <div class="row g-2">
                                <?php foreach ($sub_heads[$h['id']] as $sh): ?>
                                    <div class="col-md-6 col-lg-4 sub-item-col"
                                         data-subtitle="<?= strtolower(htmlspecialchars($sh['sub_head'])) ?>"
                                         data-income="<?= $sh['income'] ? '1' : '0' ?>"
                                         data-expense="<?= $sh['expenditure'] ? '1' : '0' ?>">
                                        <div class="sub-head-item">
                                            <div class="overflow-hidden pe-2">
                                                <div class="text-truncate sub-head-title" title="<?= htmlspecialchars($sh['sub_head']) ?>">
                                                    <?= htmlspecialchars($sh['sub_head']) ?>
                                                </div>
                                                <div class="d-flex gap-1 mt-1">
                                                    <?php if ($sh['income']): ?>
                                                        <span class="badge-m3 badge-inc"><i class="bi bi-arrow-down-left me-1"></i>INCOME (Cr)</span>
                                                    <?php endif; ?>
                                                    <?php if ($sh['expenditure']): ?>
                                                        <span class="badge-m3 badge-exp"><i class="bi bi-arrow-up-right me-1"></i>EXPENSE (Dr)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <i class="bi bi-pencil tonal-icon c-info" title="Edit"
                                                    onclick="editSub('<?= $sh['id'] ?>', '<?= htmlspecialchars(addslashes($sh['sub_head'])) ?>', '<?= $sh['income'] ?>', '<?= $sh['expenditure'] ?>', '<?= $h['id'] ?>', '<?= htmlspecialchars(addslashes($h['account_head'])) ?>')"></i>
                                                <i class="bi bi-trash3 tonal-icon c-exit" title="Delete"
                                                    onclick="deleteItem('accounts-manager.php?del_sub=<?= $sh['id'] ?>', false)"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small italic py-2 ps-1" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle me-1"></i>No sub-sectors defined for this head.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- VIEW 2: TABLE / SUMMARY VIEW (DEBIT / CREDIT ORIENTED) -->
        <div id="tableViewContainer" class="d-none">
            <div class="card shadow-sm border-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="accountsTableList">
                        <thead class="table-light">
                            <tr class="small text-uppercase">
                                <th style="width: 60px;">#</th>
                                <th>Primary Account Head</th>
                                <th>Sub-Sector / Item Name</th>
                                <th>Accounting Nature</th>
                                <th>Scope / Type</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sl = 1;
                            if (count($flat_sub_heads) > 0):
                                foreach ($flat_sub_heads as $fsh): 
                            ?>
                                <tr class="table-row-item" 
                                    data-title="<?= strtolower(htmlspecialchars($fsh['account_head'])) ?>"
                                    data-subtitle="<?= strtolower(htmlspecialchars($fsh['sub_head'])) ?>"
                                    data-income="<?= $fsh['income'] ? '1' : '0' ?>"
                                    data-expense="<?= $fsh['expenditure'] ? '1' : '0' ?>">
                                    <td class="text-muted small fw-bold"><?= $sl++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-folder-fill text-primary"></i>
                                            <span class="fw-bold"><?= htmlspecialchars($fsh['account_head']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-body"><?= htmlspecialchars($fsh['sub_head']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($fsh['income'] && $fsh['expenditure']): ?>
                                            <span class="badge bg-label-secondary">Both (Credit & Debit)</span>
                                        <?php elseif ($fsh['income']): ?>
                                            <span class="badge bg-label-success"><i class="bi bi-arrow-down-left me-1"></i>Credit (আয়)</span>
                                        <?php elseif ($fsh['expenditure']): ?>
                                            <span class="badge bg-label-danger"><i class="bi bi-arrow-up-right me-1"></i>Debit (ব্যয়)</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-warning">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($fsh['income']): ?>
                                                <span class="badge-m3 badge-inc">INCOME</span>
                                            <?php endif; ?>
                                            <?php if ($fsh['expenditure']): ?>
                                                <span class="badge-m3 badge-exp">EXPENSE</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1">
                                            <button class="tonal-icon-btn c-info" title="Edit Sub-Head"
                                                onclick="editSub('<?= $fsh['id'] ?>', '<?= htmlspecialchars(addslashes($fsh['sub_head'])) ?>', '<?= $fsh['income'] ?>', '<?= $fsh['expenditure'] ?>', '<?= $fsh['account_head_id'] ?>', '<?= htmlspecialchars(addslashes($fsh['account_head'])) ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="tonal-icon-btn c-exit" title="Delete Sub-Head"
                                                onclick="deleteItem('accounts-manager.php?del_sub=<?= $fsh['id'] ?>', false)">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No sub-sectors available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- Floating Action Button for Quick Head Add -->
<button class="m3-fab-add shadow-lg" title="Create New Head" onclick="addHead()">
    <i class="bi bi-plus-lg"></i>
</button>

<!-- ==========================================
     MODAL: ACCOUNT HEAD (ADD / EDIT)
=========================================== -->
<div class="modal fade" id="headModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content m3-modal-content shadow-lg">
            <h5 class="fw-bold mb-3 text-primary" id="headModalTitle">Create New Head</h5>
            <form method="post">
                <input type="hidden" name="head_id" id="head_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">HEAD NAME (E.G. ADMINISTRATIVE EXPENSES)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-folder2"></i></span>
                        <input type="text" name="head_name" id="head_name" class="form-control" placeholder="Enter Head Name" required>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_head" class="btn btn-primary btn-sm px-4">Save Head</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: SUB HEAD (ADD / EDIT)
=========================================== -->
<div class="modal fade" id="subModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content m3-modal-content shadow-lg">
            <h5 class="fw-bold mb-1 text-primary" id="subModalTitle">Add Sub-Sector</h5>
            <p id="parentHeadLabel" class="small text-muted mb-3"></p>
            <form method="post">
                <input type="hidden" name="sub_id" id="sub_id">
                <input type="hidden" name="h_id" id="h_id">
                <input type="hidden" name="h_name" id="h_name">

                <div class="mb-3">
                    <label class="form-label small fw-bold">SUB SECTOR NAME</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input type="text" name="sub_name" id="sub_name" class="form-control" placeholder="Enter Sub Sector Name" required>
                    </div>
                </div>

                <div class="p-3 bg-body-tertiary rounded-3 mb-3 border">
                    <label class="form-label small fw-bold text-muted mb-2">Category Availability</label>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="income" id="income" value="1" checked>
                        <label class="form-check-label fw-semibold small" for="income">Available for Income (Credit)</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="expenditure" id="expenditure" value="1">
                        <label class="form-check-label fw-semibold small" for="expenditure">Available for Expense (Debit)</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_sub" class="btn btn-primary btn-sm px-4">Save Sub-Head</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     M3 MODAL: ACTION CONFIRMATION (DELETE / IMPORT)
=========================================== -->
<div class="modal fade" id="m3ConfirmModal" tabindex="-1" aria-labelledby="m3ConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content m3-modal-content border-0 shadow-lg" style="border-radius: 28px;">
            <div class="p-4 text-center">
                <div id="m3ConfirmIconBox" class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center bg-danger-subtle text-danger" style="width: 58px; height: 58px; font-size: 1.6rem;">
                    <i id="m3ConfirmIcon" class="bi bi-trash3-fill"></i>
                </div>
                <h5 class="fw-bold mb-2 text-body" id="m3ConfirmTitle">Confirm Action</h5>
                <p class="text-muted small mb-4 px-2" id="m3ConfirmMessage">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="m3ConfirmSubmitBtn" class="btn btn-danger btn-sm px-4 rounded-pill shadow-sm" onclick="executeM3ConfirmAction()">
                        <span id="m3ConfirmBtnText">Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- M3 BOTTOM SNACKBAR / TOAST -->
<div id="m3Snackbar">
    <i id="snackbarIcon" class="bi bi-info-circle-fill fs-5"></i>
    <span id="snackbarMessage">Notification</span>
</div>

<?php require_once 'footer.php'; ?>

<script>
    const headModal = new bootstrap.Modal(document.getElementById('headModal'));
    const subModal = new bootstrap.Modal(document.getElementById('subModal'));
    const confirmModal = new bootstrap.Modal(document.getElementById('m3ConfirmModal'));

    let currentFilterType = 'all'; // 'all', 'income', 'expense'
    let currentViewMode = 'cards';   // 'cards', 'table'
    let m3ConfirmCallback = null;
    let snackbarTimer = null;

    // M3 Bottom Snackbar Function
    function showSnackbar(msg, type = 'info') {
        const snackbar = document.getElementById('m3Snackbar');
        const icon = document.getElementById('snackbarIcon');
        const text = document.getElementById('snackbarMessage');

        if (!snackbar || !icon || !text) return;

        text.textContent = msg;
        snackbar.className = 'show ' + (type === 'success' ? 'snackbar-success' : (type === 'error' ? 'snackbar-error' : (type === 'warning' ? 'snackbar-warning' : 'snackbar-info')));

        if (type === 'success') {
            icon.className = 'bi bi-check-circle-fill fs-5';
        } else if (type === 'error') {
            icon.className = 'bi bi-exclamation-octagon-fill fs-5';
        } else if (type === 'warning') {
            icon.className = 'bi bi-exclamation-triangle-fill fs-5';
        } else {
            icon.className = 'bi bi-info-circle-fill fs-5';
        }

        clearTimeout(snackbarTimer);
        snackbarTimer = setTimeout(() => {
            snackbar.classList.remove('show');
        }, 4000);
    }

    // Trigger Snackbar on Page Load if PHP Alert Exists
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($alert_msg)): ?>
            showSnackbar(<?= json_encode($alert_msg) ?>, '<?= $alert_type === 'danger' ? 'error' : ($alert_type === 'success' ? 'success' : 'info') ?>');
        <?php endif; ?>
    });

    // M3 Confirmation Dialog Trigger
    function showM3Confirm({ title, message, icon, iconBoxClass, btnText, btnClass, onConfirm }) {
        document.getElementById('m3ConfirmTitle').innerText = title || 'Confirm Action';
        document.getElementById('m3ConfirmMessage').innerText = message || 'Are you sure you want to proceed?';
        
        const iconEl = document.getElementById('m3ConfirmIcon');
        const iconBox = document.getElementById('m3ConfirmIconBox');
        if (iconEl && icon) {
            iconEl.className = icon;
        }
        if (iconBox && iconBoxClass) {
            iconBox.className = 'mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center ' + iconBoxClass;
        }

        const btn = document.getElementById('m3ConfirmSubmitBtn');
        if (btn) {
            btn.className = 'btn btn-sm px-4 rounded-pill shadow-sm ' + (btnClass || 'btn-danger');
        }
        const btnTextEl = document.getElementById('m3ConfirmBtnText');
        if (btnTextEl) {
            btnTextEl.innerText = btnText || 'Confirm';
        }

        m3ConfirmCallback = onConfirm;
        confirmModal.show();
    }

    function executeM3ConfirmAction() {
        confirmModal.hide();
        if (typeof m3ConfirmCallback === 'function') {
            m3ConfirmCallback();
        }
    }

    // Import Defaults with M3 Confirmation
    function confirmImportDefaults(formId = 'importDefaultForm') {
        showM3Confirm({
            title: 'Import Default Accounts?',
            message: 'Default institutional account heads and sub-heads will be imported. Existing custom heads will not be overwritten or modified.',
            icon: 'bi bi-cloud-arrow-down-fill',
            iconBoxClass: 'bg-primary-subtle text-primary',
            btnText: 'Import Defaults',
            btnClass: 'btn-primary',
            onConfirm: function() {
                const targetForm = document.getElementById(formId);
                if (targetForm) {
                    targetForm.submit();
                }
            }
        });
    }

    // Delete Item with M3 Confirmation
    function deleteItem(url, isHead = false) {
        showM3Confirm({
            title: isHead ? 'Delete Account Head?' : 'Delete Sub-Head?',
            message: isHead 
                ? 'Warning: Deleting this account head will also delete all of its underlying sub-sectors and related setups. This action cannot be undone.' 
                : 'Are you sure you want to delete this sub-head? This action cannot be undone.',
            icon: 'bi bi-trash3-fill',
            iconBoxClass: 'bg-danger-subtle text-danger',
            btnText: 'Delete Permanently',
            btnClass: 'btn-danger',
            onConfirm: function() {
                window.location.href = url;
            }
        });
    }

    function addHead() {
        document.getElementById('headModalTitle').innerText = "Create New Head";
        document.getElementById('head_id').value = "";
        document.getElementById('head_name').value = "";
        headModal.show();
    }

    function editHead(id, name) {
        document.getElementById('headModalTitle').innerText = "Edit Account Head";
        document.getElementById('head_id').value = id;
        document.getElementById('head_name').value = name;
        headModal.show();
    }

    function addSub(hid, hname) {
        document.getElementById('subModalTitle').innerText = "Add Sub-Sector";
        document.getElementById('parentHeadLabel').innerHTML = '<i class="bi bi-folder-fill me-1"></i> Parent Head: <strong>' + hname + '</strong>';
        document.getElementById('sub_id').value = "";
        document.getElementById('h_id').value = hid;
        document.getElementById('h_name').value = hname;
        document.getElementById('sub_name').value = "";
        document.getElementById('income').checked = false;
        document.getElementById('expenditure').checked = true;
        subModal.show();
    }

    function editSub(id, name, inc, exp, hid, hname) {
        document.getElementById('subModalTitle').innerText = "Edit Sub-Sector";
        document.getElementById('parentHeadLabel').innerHTML = '<i class="bi bi-folder-fill me-1"></i> Parent Head: <strong>' + hname + '</strong>';
        document.getElementById('sub_id').value = id;
        document.getElementById('h_id').value = hid;
        document.getElementById('h_name').value = hname;
        document.getElementById('sub_name').value = name;
        document.getElementById('income').checked = (inc == 1);
        document.getElementById('expenditure').checked = (exp == 1);
        subModal.show();
    }

    // View Switching
    function switchView(mode) {
        currentViewMode = mode;
        const cardsContainer = document.getElementById('cardsViewContainer');
        const tableContainer = document.getElementById('tableViewContainer');
        const btnCards = document.getElementById('viewCardsBtn');
        const btnTable = document.getElementById('viewTableBtn');

        if (mode === 'cards') {
            cardsContainer?.classList.remove('d-none');
            tableContainer?.classList.add('d-none');
            btnCards?.classList.add('active');
            btnTable?.classList.remove('active');
        } else {
            cardsContainer?.classList.add('d-none');
            tableContainer?.classList.remove('d-none');
            btnCards?.classList.remove('active');
            btnTable?.classList.add('active');
        }
        applyFilters();
    }

    // Tab Filter Buttons
    document.querySelectorAll('.filter-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilterType = this.getAttribute('data-filter') || 'all';
            applyFilters();
        });
    });

    // Real-time Search Input Listener
    document.getElementById('searchHeadInput')?.addEventListener('input', applyFilters);

    function applyFilters() {
        const query = document.getElementById('searchHeadInput')?.value.trim().toLowerCase() || '';

        // 1. Filter Cards View
        const cards = document.querySelectorAll('.head-item-card');
        cards.forEach(card => {
            const headTitle = card.getAttribute('data-title') || '';
            const subItems = card.querySelectorAll('.sub-item-col');
            let visibleSubCount = 0;

            subItems.forEach(sub => {
                const subTitle = sub.getAttribute('data-subtitle') || '';
                const isInc = sub.getAttribute('data-income') === '1';
                const isExp = sub.getAttribute('data-expense') === '1';

                // Check Type Filter
                let matchesType = true;
                if (currentFilterType === 'income') matchesType = isInc;
                if (currentFilterType === 'expense') matchesType = isExp;

                // Check Search Query
                const matchesSearch = (query === '' || headTitle.includes(query) || subTitle.includes(query));

                if (matchesType && matchesSearch) {
                    sub.style.display = '';
                    visibleSubCount++;
                } else {
                    sub.style.display = 'none';
                }
            });

            if (visibleSubCount > 0 || (currentFilterType === 'all' && (query === '' || headTitle.includes(query)))) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // 2. Filter Table View
        const rows = document.querySelectorAll('.table-row-item');
        rows.forEach(row => {
            const headTitle = row.getAttribute('data-title') || '';
            const subTitle = row.getAttribute('data-subtitle') || '';
            const isInc = row.getAttribute('data-income') === '1';
            const isExp = row.getAttribute('data-expense') === '1';

            let matchesType = true;
            if (currentFilterType === 'income') matchesType = isInc;
            if (currentFilterType === 'expense') matchesType = isExp;

            const matchesSearch = (query === '' || headTitle.includes(query) || subTitle.includes(query));

            if (matchesType && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>