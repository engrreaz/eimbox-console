<?php
require_once 'header.php';

// কুকি থেকে ফিল্টার প্যারামিটার গ্রহণ
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

$students_list = [];
if (!empty($class) && !empty($sessionyear)) {
    $stmt = $conn->prepare("
        SELECT 
           si.id, si.stid, si.rollno, si.icardst,
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

    <div class="card no-print mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voter Information</h4>
                <div>
                    <a href="managing-voter-list.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to
                        Selection</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($students_list)): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h4><?= htmlspecialchars($scname) ?></h4>
                    <p><?= htmlspecialchars($scaddress) ?></p>
                    <h5>Voter List: <?= htmlspecialchars($class) ?> (<?= htmlspecialchars($section) ?>) -
                        <?= htmlspecialchars($sessionyear) ?></h5>
                </div>

                <table class="table table-bordered" id="main-table">
                    <thead>
                        <tr class="txt-right">
                            <td>SL</td>
                            <td>Student's Name</td>
                            <td>Parents' Name</td>
                            <td>Parents' NID</td>
                            <td>Address</td>
                            <td>Mobile No</td>
                            <td>Signature</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sl = 1;
                        foreach ($students_list as $student):
                            ?>
                            <tr>
                                <td class="txt-right"><?= $sl++ ?></td>
                                <td>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="stnameeng"><?= htmlspecialchars($student['stnameeng']) ?></div>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="stnameben"><?= htmlspecialchars($student['stnameben']) ?></div>
                                </td>
                                <td>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="fname">F: <?= htmlspecialchars($student['fname']) ?></div>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="mname">M: <?= htmlspecialchars($student['mname']) ?></div>
                                </td>
                                <td>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="fnid">F: <?= htmlspecialchars($student['fnid']) ?></div>
                                    <div class="editable" data-stid="<?= $student['stid'] ?>" data-field="mnid">M: <?= htmlspecialchars($student['mnid']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($student['previll'] . ', ' . $student['prepo']) ?></td>
                                <td>
                                    <?php
                                    $mobiles = array_unique(array_filter([$student['guarmobile'], $student['fmobile'], $student['mmobile']]));
                                    echo htmlspecialchars(implode(', ', $mobiles));
                                    ?>
                                </td>
                                <td style="height: 50px;"></td>
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

        // যদি সেলটি ইতিমধ্যে ইনপুট মোডে থাকে, তাহলে কিছু করবেনা
        if (cell.find('input').length) {
            return;
        }

        var originalContent = cell.text().trim();
        var stid = cell.data('stid');
        var field = cell.data('field');

        // F: বা M: প্রিফিক্স থাকলে সেটা বাদ দিয়ে শুধু মূল টেক্সট নেওয়া
        var contentToEdit = originalContent;
        if (originalContent.startsWith('F: ') || originalContent.startsWith('M: ')) {
            contentToEdit = originalContent.substring(3);
        }

        // ইনপুট ফিল্ড তৈরি করা
        var input = $('<input type="text" class="form-control form-control-sm" />');
        input.val(contentToEdit);

        // সেল কন্টেন্ট ইনপুট দিয়ে পরিবর্তন করা
        cell.html(input);
        input.focus();

        // ইনপুট থেকে ফোকাস সরে গেলে (blur) ডেটা সেভ হবে
        input.on('blur', function () {
            var newValue = $(this).val().trim();

            // যদি ডেটা পরিবর্তন না হয়, তাহলে আগের অবস্থায় ফিরে যাবে
            if (newValue === contentToEdit) {
                cell.text(originalContent);
                return;
            }

            // AJAX এর মাধ্যমে ডেটা সেভ করা
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
                        // প্রিফিক্সসহ নতুন ভ্যালু দেখানো
                        var newContent = newValue;
                        if (originalContent.startsWith('F: ')) {
                            newContent = 'F: ' + newValue;
                        } else if (originalContent.startsWith('M: ')) {
                            newContent = 'M: ' + newValue;
                        }
                        cell.text(newContent);
                        showToast('success', 'Information updated successfully.', 'Updated');
                    } else {
                        cell.text(originalContent); // ব্যর্থ হলে আগের ডেটা ফিরিয়ে আনা
                        showToast('danger', response.message || 'Update failed!', 'Error');
                    }
                },
                error: function () {
                    cell.text(originalContent);
                    showToast('danger', 'An error occurred on the server.', 'Server Error');
                }
            });
        });

        // Enter চাপলেও blur ট্রিগার হবে
        input.on('keypress', function (e) {
            if (e.which === 13) {
                $(this).blur();
            }
        });
    });
</script>
</body>

</html>