<?php
require_once 'header.php';

// Filter parameters from cookies / session
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

$students_list = [];
if (!empty($class) && !empty($sessionyear)) {
    $stmt = $conn->prepare("
        SELECT 
           si.id, si.stid, si.rollno, si.icardst, si.voter_no,
            s.stnameeng, s.stnameben, s.fname, s.mname, 
            s.previll, s.prepo, s.preps, s.predist,
            s.fmobile, s.mmobile, s.fnid, s.mnid, s.guarmobile
        FROM sessioninfo AS si
        JOIN students AS s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? 
        AND si.sessionyear = ? 
        AND si.slot = ? 
        AND si.classname = ? 
        AND si.sectionname = ?
        AND si.status = 1
        ORDER BY si.rollno ASC
    ");
    $stmt->bind_param("issss", $sccode, $sessionyear, $slot, $class, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
    $stmt->close();
}

?>

<style>
    .backpic {
        filter: grayscale(100);
        background: black;
    }

    #main-table td {
        border: 1px solid black;
        padding: 5px;
    }

    .txt-right {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }

    .editable {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print,
        .layout-navbar,
        .layout-menu,
        .footer {
            display: none !important;
        }

        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>


<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card no-print mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="mb-0 text-primary fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Section-Wise Voter Roll</h4>
                <div class="d-flex gap-2">
                    <a href="managing-voter-list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Selection Criteria</a>
                    <a href="voter-master-list.php" class="btn btn-outline-primary"><i class="bi bi-list-stars me-1"></i> Master Voter List</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($students_list)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-center mb-4 pb-2 border-bottom">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($scname) ?></h4>
                    <p class="text-muted mb-1"><?= htmlspecialchars($scaddress) ?></p>
                    <h5 class="fw-bold mt-2">Managing Committee Election &mdash; Section-Wise Guardian Voter List</h5>
                    <div class="text-secondary small">Class: <strong><?= htmlspecialchars($class) ?></strong> | Section: <strong><?= htmlspecialchars($section) ?></strong> | Session: <strong><?= htmlspecialchars($sessionyear) ?></strong></div>
                </div>

                <table class="table table-bordered table-sm align-middle" id="main-table">
                    <thead class="table-light">
                        <tr class="txt-right">
                            <td style="width: 5%;">Roll</td>
                            <td style="width: 10%;">Voter No.</td>
                            <td style="width: 20%;">Student's Name</td>
                            <td style="width: 20%;">Parents' Name</td>
                            <td style="width: 15%;">Parents' NID</td>
                            <td style="width: 14%;">Address</td>
                            <td style="width: 16%;">Mobile No.</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($students_list as $student):
                            $vno = intval($student['voter_no'] ?? 0);
                            ?>
                            <tr>
                                <td class="txt-right fw-bold"><?= htmlspecialchars($student['rollno']) ?></td>
                                <td class="txt-right">
                                    <?php if ($vno > 0): ?>
                                        <span class="badge bg-label-primary fs-6 fw-bold"><?= str_pad($vno, 3, '0', STR_PAD_LEFT) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="editable fw-semibold" data-stid="<?= $student['stid'] ?>" data-field="stnameeng"><?= htmlspecialchars($student['stnameeng']) ?></div>
                                    <div class="editable text-muted small" data-stid="<?= $student['stid'] ?>" data-field="stnameben"><?= htmlspecialchars($student['stnameben']) ?></div>
                                </td>
                                <td>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="fname">F: <?= htmlspecialchars($student['fname']) ?></div>
                                    <div class="editable text-muted small" data-stid="<?= $student['stid'] ?>" data-field="mname">M: <?= htmlspecialchars($student['mname']) ?></div>
                                </td>
                                <td>
                                    <div class="editable small" data-stid="<?= $student['stid'] ?>" data-field="fnid">F: <?= htmlspecialchars($student['fnid']) ?></div>
                                    <div class="editable small text-muted" data-stid="<?= $student['stid'] ?>" data-field="mnid">M: <?= htmlspecialchars($student['mnid']) ?></div>
                                </td>
                                <td>
                                    <div class="editable small" data-stid="<?= $student['stid'] ?>" data-field="previll">Vill: <?= htmlspecialchars($student['previll']) ?></div>
                                    <div class="editable small text-muted" data-stid="<?= $student['stid'] ?>" data-field="prepo">PO: <?= htmlspecialchars($student['prepo']) ?></div>
                                </td>
                                <td>
                                    <div class="editable small fw-semibold" data-stid="<?= $student['stid'] ?>" data-field="fmobile">F: <?= htmlspecialchars($student['fmobile']) ?></div>
                                    <div class="editable small text-muted" data-stid="<?= $student['stid'] ?>" data-field="mmobile">M: <?= htmlspecialchars($student['mmobile']) ?></div> 
                                    <div class="editable small text-secondary" data-stid="<?= $student['stid'] ?>" data-field="guarmobile">G: <?= htmlspecialchars($student['guarmobile']) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            No voter information found for the selected criteria. Please go back and select again.
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>

<script>
    $(document).on('click', '.editable', function () {
        var cell = $(this);

        if (cell.find('input').length) {
            return;
        }

        var originalText = cell.text().trim();
        var stid = cell.data('stid');
        var field = cell.data('field');

        var contentToEdit = originalText;
        if (originalText.startsWith('F: ') || originalText.startsWith('M: ')  || originalText.startsWith('G: ') ) {
            contentToEdit = originalText.substring(3);
        } else if (originalText.startsWith('Vill: ')) {
            contentToEdit = originalText.substring(6);
        } else if (originalText.startsWith('PO: ')) {
            contentToEdit = originalText.substring(4);
        }

        var input = $('<input type="text" class="form-control form-control-sm" />');
        input.val(contentToEdit);

        cell.html(input);
        input.focus();

        input.on('blur', function () {
            var newValue = $(this).val().trim();

            if (newValue === contentToEdit) {
                cell.text(originalText);
                return;
            }

            $.ajax({
                url: 'backend/update_voter_info.php',
                method: 'POST',
                data: {
                    stid: stid,
                    field: field,
                    value: newValue
                },
                dataType: 'json',
                beforeSend: function () {
                    cell.html('<i class="bi bi-arrow-repeat"></i> Saving...');
                },
                success: function (response) {
                    if (response.status === 'success') {
                        var newDisplayText = newValue;
                        if (originalText.startsWith('F: ')) {
                            newDisplayText = 'F: ' + newValue;
                        } else if (originalText.startsWith('M: ')) {
                            newDisplayText = 'M: ' + newValue;
                        } else if (originalText.startsWith('Vill: ')) {
                            newDisplayText = 'Vill: ' + newValue;
                        } else if (originalText.startsWith('PO: ')) {
                            newDisplayText = 'PO: ' + newValue;
                        } else if (originalText.startsWith('G: ')) {
                            newDisplayText = 'G: ' + newValue;
                        }
                        cell.text(newDisplayText);
                        showToast('success', 'Information updated successfully.', 'Updated');
                    } else {
                        cell.text(originalText);
                        showToast('danger', response.message || 'Update failed!', 'Error');
                    }
                },
                error: function () {
                    cell.text(originalText);
                    showToast('danger', 'An error occurred on the server.', 'Server Error');
                }
            });
        });

        input.on('keypress', function (e) {
            if (e.which === 13) {
                $(this).blur();
            }
        });
    });
</script>
</body>

</html>