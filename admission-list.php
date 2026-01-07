<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <button id="btnCalcMerit" class="border-0 float-end text-danger"> <i class="bi bi-sort-numeric-down-alt icon-30px" title="Calculate Merit List"></i> </button>
    <button id="printList" class="  border-0 float-end me-3 text-info"> <i class="bi bi-printer-fill icon-30px" title="Print Student List"></i> </button>
    <h4 class="mb-3">Submitted Admission Form</h4>

    <div class="card table-responsive">
        <table class="table table-bordered table-striped table-sm" id="admitTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Roll</th>
                    <th colspan="2"> Name of Applicant's (English | বাংলা)</th>
                    <th>Father</th>
                    <th>Mother</th>
                    <th>Mobile</th>
                    <th>Mark</th>
                    <th>Merit</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qry = $conn->query("SELECT * FROM registrations where sccode = '$sccode' ORDER BY meritplace ASC, roll_no ASC");
                $i = 1;
                while ($row = $qry->fetch_assoc()) {
                    echo "<tr data-id='{$row['id']}'>
                                <td>{$i}</td>
                                <td><span class='badge badge-primary fs-tiny m-0 p-1'>{$row['admit_class']}</span></td>
                                <td>{$row['roll_no']}</td>
                                <td>{$row['stnameeng']}</td>
                                <td>{$row['stnameben']}</td>
                                <td>{$row['fname']}</td>
                                <td>{$row['mname']}</td>
                                <td>{$row['mnumber']}</td>
                                <td><input style='text-align:center; min-width:75px;' type='number' step='0.01' class='form-control form-control-sm markInput' value='{$row['adm_test_mark']}'></td>
                                <td>{$row['meritplace']}</td>
                                <td class='text-nowrap'>
                                    <button tabindex='-1' class='btn btn-sm btn-outline-success btnSaveMark'><i class='bi bi-floppy fs-8'></i></button>
                                    <button tabindex='-1' class='btn btn-sm btn-outline-info btnView'><i class='bi bi-display fs-8'></i></button>
                                    <button tabindex='-1' class='btn btn-sm btn-outline-primary btnAdmit'><i class='bi bi-person-plus fs-8'></i></button>
                                </td>
                              </tr>";
                    $i++;
                }
                ?>
            </tbody>
        </table>

    </div>
</div>

<!-- 🔹 Modal for View -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header  text-dark">
                <h5 class="modal-title">Applicant's Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDetails">
                <div class="text-center text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
    $(document).ready(function () {

        // ✅ মার্ক সেভ (button click)
        $('.btnSaveMark').on('click', function () {
            const tr = $(this).closest('tr');
            const id = tr.data('id');
            const mark = tr.find('.markInput').val();

            saveMark(id, mark);
        });

        // ✅ blur ইভেন্টেও সেভ
        $('.markInput').on('blur', function () {
            const tr = $(this).closest('tr');
            const id = tr.data('id');
            const mark = $(this).val();

            saveMark(id, mark);
        });

        // ✅ Ajax function
        function saveMark(id, mark) {
            $.post('ajax/admission-save-mark.php', { id, mark }, function (res) {
                showToast('success', "Mark Saved", "Save");
                console.log(res);
                // alert(res); // চাইলে uncomment করতে পারো
            });
        }


        // ✅ মেরিট নির্ধারণ
        $('#btnCalcMerit').on('click', function () {
            if (confirm('মার্ক অনুযায়ী মেরিট নির্ধারণ করতে চাও?')) {
                $.post('ajax/admission-calc-merit.php', {}, function (res) {
                    alert(res);
                    location.reload();
                });
            }
        });

        // ✅ ভিউ বাটন
        $('.btnView').on('click', function () {
            const id = $(this).closest('tr').data('id');
            $('#viewDetails').html('<div class="text-center text-muted">Loading...</div>');
            // $('#viewModal').modal('show');
            const modalEl = document.getElementById("viewModal");
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            $.post('ajax/admission-view.php', { id }, function (res) {
                $('#viewDetails').html(res);
            });
        });

        // ✅ একক চূড়ান্ত ভর্তি
        $('.btnAdmit').on('click', function () {
            const id = $(this).closest('tr').data('id');
            if (confirm('এই শিক্ষার্থীকে চূড়ান্তভাবে ভর্তি করতে চাও?')) {
                $.post('ajax/admission-single-final.php', { id }, function (res) {
                    alert(res);
                });
            }
        });

    });
</script>

<script>
    $('#printList').on('click', function () {
    // collect sccode if needed
    const sccode = '<?= $sccode ?>'; 

    // open new window for print
    const printWin = window.open('admission-print-list.php?sccode=' + sccode, '_blank', 'width=900,height=1200');

    // optional: focus
    printWin.focus();
});

</script>
<!-- ----------------------------------- -->
</body>

</html>