<?php
require_once 'header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='teachers-list.php';</script>";
    exit;
}

$tid = trim($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
$stmt->bind_param("si", $tid, $sccode);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if (!$teacher) {
    echo "<div class='container-xxl p-4'><div class='alert alert-danger'>Teacher not found!</div></div>";
    require_once 'footer.php';
    exit;
}

// Fetch latest salary structure
$sal_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC LIMIT 1");
$sal_stmt->bind_param("si", $tid, $sccode);
$sal_stmt->execute();
$latest_salary = $sal_stmt->get_result()->fetch_assoc();

// Fallback to teacher table values if none exists yet
$cur_sal = $latest_salary ?: $teacher;

$save_status = null;
$save_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_salary_structure'])) {
    $applydate  = !empty($_POST['applydate']) ? $_POST['applydate'] : date('Y-m-d');
    
    // Govt / MPO
    $paycode    = intval($_POST['paycode'] ?? 0);
    $payscale   = intval($_POST['payscale'] ?? 0);
    $basic      = intval($_POST['basic'] ?? 0);
    $incentive  = intval($_POST['incentive'] ?? 0);
    $house      = intval($_POST['house'] ?? 0);
    $medical    = intval($_POST['medical'] ?? 0);
    $arrea      = intval($_POST['arrea'] ?? 0);
    $welfare    = intval($_POST['welfare'] ?? 0);
    $retire     = intval($_POST['retire'] ?? 0);
    $netamtgovt = intval($_POST['netamtgovt'] ?? 0);

    // School / Institutional
    $salary     = intval($_POST['salary'] ?? 0);
    $salery     = $salary;
    $mobilevata = intval($_POST['mobilevata'] ?? 0);
    $travel     = intval($_POST['travel'] ?? 0);
    $medical2   = intval($_POST['medical2'] ?? 0);
    $exam       = intval($_POST['exam'] ?? 0);
    $festival   = intval($_POST['festival'] ?? 0);
    $pf         = intval($_POST['pf'] ?? 0);
    $net2       = intval($_POST['net2'] ?? 0);

    // Banking (safe length constrained)
    $accno      = substr(trim($_POST['accno'] ?? ''), 0, 20);
    $bankname   = substr(trim($_POST['bankname'] ?? ''), 0, 25);
    $branch     = substr(trim($_POST['branch'] ?? ''), 0, 50);
    $routing    = substr(trim($_POST['routing'] ?? ''), 0, 20);

    $accnosch   = substr(trim($_POST['accnosch'] ?? ''), 0, 20);
    $bnamesch   = substr(trim($_POST['bnamesch'] ?? ''), 0, 25);
    $bbrsch     = substr(trim($_POST['bbrsch'] ?? ''), 0, 50);
    $routesch   = substr(trim($_POST['routesch'] ?? ''), 0, 20);

    $accnopf    = substr(trim($_POST['accnopf'] ?? ''), 0, 20);
    $bnamepf    = substr(trim($_POST['bnamepf'] ?? ''), 0, 25);
    $bbrpf      = substr(trim($_POST['bbrpf'] ?? ''), 0, 50);
    $routepf    = substr(trim($_POST['routepf'] ?? ''), 0, 20);

    $modifieddate = date('Y-m-d H:i:s');

    // 1. Insert into teacher_salary_structure
    $ins_sql = "INSERT INTO teacher_salary_structure (
                    tid, sccode, applydate, accno, bankname, branch, routing,
                    accnosch, bnamesch, bbrsch, routesch, accnopf, bnamepf, bbrpf, routepf,
                    paycode, payscale, basic, incentive, house, medical, arrea, welfare, retire, netamtgovt,
                    salary, mobilevata, travel, medical2, exam, festival, pf, net2, modifieddate
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";
    
    $ins_stmt = $conn->prepare($ins_sql);
    $ins_stmt->bind_param(
        "sisssssssssssssiiiiiiiiiiiiiiiiiis",
        $tid, $sccode, $applydate, $accno, $bankname, $branch, $routing,
        $accnosch, $bnamesch, $bbrsch, $routesch, $accnopf, $bnamepf, $bbrpf, $routepf,
        $paycode, $payscale, $basic, $incentive, $house, $medical, $arrea, $welfare, $retire, $netamtgovt,
        $salary, $mobilevata, $travel, $medical2, $exam, $festival, $pf, $net2, $modifieddate
    );

    if ($ins_stmt->execute()) {
        // 2. Also update master teacher table so values stay in sync
        $up_t = $conn->prepare("UPDATE teacher SET
                                    accno=?, bankname=?, branch=?, routing=?,
                                    accnosch=?, bnamesch=?, bbrsch=?, routesch=?,
                                    accnopf=?, bnamepf=?, bbrpf=?, routepf=?,
                                    paycode=?, payscale=?, basic=?, incentive=?, house=?, medical=?,
                                    arrea=?, welfare=?, retire=?, netamtgovt=?, salary=?, salery=?,
                                    mobilevata=?, travel=?, medical2=?, exam=?, festival=?, pf=?, net2=?,
                                    modifieddate=?
                                WHERE tid=? AND sccode=?");
        $up_t->bind_param(
            "ssssssssssssiiiiiiiiiiiiiiiiiiissi",
            $accno, $bankname, $branch, $routing,
            $accnosch, $bnamesch, $bbrsch, $routesch,
            $accnopf, $bnamepf, $bbrpf, $routepf,
            $paycode, $payscale, $basic, $incentive, $house, $medical,
            $arrea, $welfare, $retire, $netamtgovt, $salary, $salery,
            $mobilevata, $travel, $medical2, $exam, $festival, $pf, $net2,
            $modifieddate, $tid, $sccode
        );
        $up_t->execute();

        $save_status = 'success';
        $save_message = 'Salary structure updated successfully!';

        // Refresh current data
        $sal_stmt->execute();
        $latest_salary = $sal_stmt->get_result()->fetch_assoc();
        $cur_sal = $latest_salary;
    } else {
        $save_status = 'error';
        $save_message = 'Failed to save salary structure: ' . $conn->error;
    }
}

// Fetch all history
$hist_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC");
$hist_stmt->bind_param("si", $tid, $sccode);
$hist_stmt->execute();
$history = $hist_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Teacher /</span> Salary Settings</h4>
            <p class="text-muted small mb-0">Configure and record time-based salary structure updates</p>
        </div>
        <div class="d-flex gap-2">
            <a href="teacher-view.php?id=<?= urlencode($teacher['tid']) ?>" class="btn btn-outline-info btn-sm">
                <i class="bi bi-eye me-1"></i> View Profile
            </a>
            <a href="teachers-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Teacher Summary Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <img src="<?= teacher_profile_image_path($teacher['tid']) ?>"
                     class="rounded-circle me-3"
                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #eee;">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($teacher['tname'] ?? '') ?></h5>
                    <div class="small text-muted">
                        ID: <span class="fw-bold text-primary font-monospace"><?= htmlspecialchars($teacher['tid'] ?? '') ?></span> &bull;
                        Designation: <span class="fw-semibold text-dark"><?= htmlspecialchars($teacher['position'] ?? '—') ?></span> &bull;
                        Slot: <span class="badge bg-label-secondary"><?= htmlspecialchars($teacher['slots'] ?? '—') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="salaryForm">
        <div class="row">
            <div class="col-lg-8">
                <!-- Effective Date Box -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark">Effective / Apply Date <span class="text-danger">*</span></label>
                                <input type="date" name="applydate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                <small class="text-muted">Date from which this salary structure becomes effective</small>
                            </div>
                            <div class="col-md-7 border-start">
                                <div class="small text-muted">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Each change will be permanently logged in the salary history table. The most recent effective structure will be automatically applied for monthly payroll generation.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Govt / MPO Salary Breakdown -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-success-subtle py-3 border-bottom">
                        <h6 class="fw-bold text-success m-0"><i class="bi bi-shield-check me-2"></i> Government (MPO) Salary Breakdown</h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Pay Code</label>
                                <input type="number" name="paycode" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['paycode'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Pay Scale</label>
                                <input type="number" name="payscale" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['payscale'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Basic Salary</label>
                                <input type="number" name="basic" id="gov_basic" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['basic'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Incentive</label>
                                <input type="number" name="incentive" id="gov_incentive" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['incentive'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">House Rent</label>
                                <input type="number" name="house" id="gov_house" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['house'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Medical Allowance</label>
                                <input type="number" name="medical" id="gov_medical" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['medical'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Arrear</label>
                                <input type="number" name="arrea" id="gov_arrea" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['arrea'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-danger">Welfare (Deduction)</label>
                                <input type="number" name="welfare" id="gov_welfare" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['welfare'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-danger">Retirement (Deduction)</label>
                                <input type="number" name="retire" id="gov_retire" class="form-control form-control-sm calc-gov" value="<?= htmlspecialchars($cur_sal['retire'] ?? '0') ?>">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label small fw-bold text-success">Net Govt Payable Amount</label>
                                <input type="number" name="netamtgovt" id="gov_net" class="form-control form-control-sm fw-bold border-success text-success bg-light" value="<?= htmlspecialchars($cur_sal['netamtgovt'] ?? '0') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School / Institutional Salary Breakdown -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-primary-subtle py-3 border-bottom">
                        <h6 class="fw-bold text-primary m-0"><i class="bi bi-building me-2"></i> School / Institutional Pay Breakdown</h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Base School Salary</label>
                                <input type="number" name="salary" id="sch_salary" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['salary'] ?? $cur_sal['salery'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Mobile Allowance</label>
                                <input type="number" name="mobilevata" id="sch_mobile" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['mobilevata'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Travel Allowance</label>
                                <input type="number" name="travel" id="sch_travel" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['travel'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Medical Allowance</label>
                                <input type="number" name="medical2" id="sch_med" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['medical2'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Exam Remuneration</label>
                                <input type="number" name="exam" id="sch_exam" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['exam'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Festival Bonus</label>
                                <input type="number" name="festival" id="sch_fest" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['festival'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-danger">PF Contribution (Deduction)</label>
                                <input type="number" name="pf" id="sch_pf" class="form-control form-control-sm calc-sch" value="<?= htmlspecialchars($cur_sal['pf'] ?? '0') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-primary">Net School Payable</label>
                                <input type="number" name="net2" id="sch_net" class="form-control form-control-sm fw-bold border-primary text-primary bg-light" value="<?= htmlspecialchars($cur_sal['net2'] ?? '0') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banking Accounts Associated -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light py-3 border-bottom">
                        <h6 class="fw-bold text-dark m-0"><i class="bi bi-bank me-2"></i> Associated Bank Accounts</h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12"><strong class="small text-success">1. MPO Govt Bank</strong></div>
                            <div class="col-md-3"><input type="text" name="accno" placeholder="Account No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['accno'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="bankname" placeholder="Bank Name" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['bankname'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="branch" placeholder="Branch" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['branch'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="routing" placeholder="Routing No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['routing'] ?? '') ?>"></div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12"><strong class="small text-primary">2. School Unit Bank</strong></div>
                            <div class="col-md-3"><input type="text" name="accnosch" placeholder="Account No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['accnosch'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="bnamesch" placeholder="Bank Name" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['bnamesch'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="bbrsch" placeholder="Branch" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['bbrsch'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="routesch" placeholder="Routing No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['routesch'] ?? '') ?>"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12"><strong class="small text-info">3. Provident Fund (PF) Bank</strong></div>
                            <div class="col-md-3"><input type="text" name="accnopf" placeholder="Account No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['accnopf'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="bnamepf" placeholder="Bank Name" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['bnamepf'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="bbrpf" placeholder="Branch" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['bbrpf'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="routepf" placeholder="Routing No" class="form-control form-control-sm" value="<?= htmlspecialchars($cur_sal['routepf'] ?? '') ?>"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 text-end">
                    <button type="submit" name="save_salary_structure" class="btn btn-primary px-5 shadow-sm">
                        <i class="bi bi-save me-1"></i> Save &amp; Apply Salary Structure
                    </button>
                </div>
            </div>

            <!-- Right Sidebar: Live Preview & History -->
            <div class="col-lg-4">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-calculator me-1"></i> Total Monthly Take-Home</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Net Govt (MPO):</span>
                            <strong class="text-success" id="prev_gov">৳<?= number_format($cur_sal['netamtgovt'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Net School Pay:</span>
                            <strong class="text-primary" id="prev_sch">৳<?= number_format($cur_sal['net2'] ?? 0) ?></strong>
                        </div>
                        <div class="border-top pt-2 d-flex justify-content-between">
                            <span class="fw-bold text-dark fs-6">Combined Total:</span>
                            <strong class="fw-bold text-dark fs-5" id="prev_tot">৳<?= number_format(($cur_sal['netamtgovt'] ?? 0) + ($cur_sal['net2'] ?? 0)) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- History Log -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-clock-history me-1"></i> Change History</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($history)): ?>
                            <div class="p-3 text-muted small text-center">No previous history records recorded yet.</div>
                        <?php else: ?>
                            <div class="list-group list-group-flush small">
                                <?php foreach ($history as $h): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <strong class="text-dark"><?= date('d M, Y', strtotime($h['applydate'])) ?></strong>
                                            <span class="badge bg-label-primary">৳<?= number_format(($h['netamtgovt'] ?? 0) + ($h['net2'] ?? 0)) ?></span>
                                        </div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">
                                            Govt Net: ৳<?= number_format($h['netamtgovt'] ?? 0) ?> &bull; School Net: ৳<?= number_format($h['net2'] ?? 0) ?><br>
                                            <span class="text-secondary">Saved: <?= $h['modifieddate'] ?? '—' ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function calculateGovNet() {
        const basic = parseFloat(document.getElementById('gov_basic').value) || 0;
        const incentive = parseFloat(document.getElementById('gov_incentive').value) || 0;
        const house = parseFloat(document.getElementById('gov_house').value) || 0;
        const medical = parseFloat(document.getElementById('gov_medical').value) || 0;
        const arrea = parseFloat(document.getElementById('gov_arrea').value) || 0;
        const welfare = parseFloat(document.getElementById('gov_welfare').value) || 0;
        const retire = parseFloat(document.getElementById('gov_retire').value) || 0;
        
        const net = (basic + incentive + house + medical + arrea) - (welfare + retire);
        const finalNet = Math.max(0, net);
        document.getElementById('gov_net').value = finalNet;
        document.getElementById('prev_gov').innerText = '৳' + finalNet.toLocaleString();
        updateTotal();
    }

    function calculateSchNet() {
        const sal = parseFloat(document.getElementById('sch_salary').value) || 0;
        const mob = parseFloat(document.getElementById('sch_mobile').value) || 0;
        const trv = parseFloat(document.getElementById('sch_travel').value) || 0;
        const med = parseFloat(document.getElementById('sch_med').value) || 0;
        const exm = parseFloat(document.getElementById('sch_exam').value) || 0;
        const fst = parseFloat(document.getElementById('sch_fest').value) || 0;
        const pf = parseFloat(document.getElementById('sch_pf').value) || 0;
        
        const net = (sal + mob + trv + med + exm + fst) - pf;
        const finalNet = Math.max(0, net);
        document.getElementById('sch_net').value = finalNet;
        document.getElementById('prev_sch').innerText = '৳' + finalNet.toLocaleString();
        updateTotal();
    }

    function updateTotal() {
        const gov = parseFloat(document.getElementById('gov_net').value) || 0;
        const sch = parseFloat(document.getElementById('sch_net').value) || 0;
        document.getElementById('prev_tot').innerText = '৳' + (gov + sch).toLocaleString();
    }

    document.querySelectorAll('.calc-gov').forEach(input => input.addEventListener('input', calculateGovNet));
    document.querySelectorAll('.calc-sch').forEach(input => input.addEventListener('input', calculateSchNet));

    <?php if ($save_status === 'success'): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= addslashes($save_message) ?>',
                timer: 2000,
                showConfirmButton: false
            });
        }
    <?php elseif ($save_status === 'error'): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= addslashes($save_message) ?>'
            });
        }
    <?php endif; ?>
</script>
</body>
</html>
