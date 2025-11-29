<?php
include 'header.php';
include 'core/sms-var.php';

$sql = "SELECT * FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "<div class='alert alert-danger'>School info not found!</div>";
    include 'footer.php';
    exit;
}

$scinfo = $res->fetch_assoc();
$sms_setting = explode(' | ', trim($scinfo['sms_gateway'] ?? ""))[0] ?? 0;
$sms_gateway = explode(' | ', trim($scinfo['sms_gateway'] ?? ""));
$sms_in = explode(' | ', trim($scinfo['sms_in'] ?? ""));
$sms_out = explode(' | ', trim($scinfo['sms_out'] ?? ""));
$sms_absent = explode(' | ', trim($scinfo['sms_absent'] ?? ""));
$sms_payment = explode(' | ', trim($scinfo['sms_payment'] ?? ""));
$sms_dues = explode(' | ', trim($scinfo['sms_dues'] ?? ""));
$sms_month_report = explode(' | ', trim($scinfo['sms_month_report'] ?? ""));

$sms_in_checked = ($sms_in[0] == 1) ? 'checked' : '';
$sms_out_checked = ($sms_out[0] == 1) ? 'checked' : '';
$sms_absent_checked = ($sms_absent[0] == 1) ? 'checked' : '';
$sms_payment_checked = ($sms_payment[0] == 1) ? 'checked' : '';
$sms_dues_checked = ($sms_dues[0] == 1) ? 'checked' : '';
$sms_month_report_checked = ($sms_month_report[0] == 1) ? 'checked' : '';

?>
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- <h3 class="d-print-none">Messaging Settings</h3> -->

    <?php include 'core/sms-settings-block-0.php'; ?>

    <div class="row d-print-none mt-4">
        <div class="col-12 grid-margin stretch-card">
            <h4 class="m-0">Messages Management</h4>
            <?php

            // $stnameeng = "Mr. Modhushodan Datt";
            // $text = "Dear [[STUDENT_NAME_ENG]], you're absent [[CUR]]";
            // echo sms_templete_2_text($text);
            // global_send_sms('01919629672', $text);
          
            // createNotification($user_id_no,'Test', 'for user only');


            $blockList = [
                ["blockName" => "block_1", "blockType" => "sms_in", "blockTitle" => "Student Attendance (In-Time)"],
                ["blockName" => "block_2", "blockType" => "sms_out", "blockTitle" => "Student Attendance (Out-Time)"],
                ["blockName" => "block_3", "blockType" => "sms_absent", "blockTitle" => "Student Absence"],
                ["blockName" => "block_4", "blockType" => "sms_payment", "blockTitle" => "Payment Notifications"],
                ["blockName" => "block_5", "blockType" => "sms_dues", "blockTitle" => "Due Notifications"],
                ["blockName" => "block_6", "blockType" => "sms_month_report", "blockTitle" => "Monthly Reports"]
            ];

            foreach ($blockList as $index => $block) {
                $blockName = $block['blockName'];
                $blockType = $block['blockType'];
                $blockTitle = $block['blockTitle'];

                include 'core/sms-settings-block.php';
                // You can use $blockName and $blockType as needed
            }



            ?>






        </div>
    </div>


    <!-- ************************************************** -->






</div>

<?php
include 'footer.php';
?>

<!-- SMS Template Modal -->
<div class="modal fade" id="smsTempModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:70vh; overflow-y:auto;">
            <div class="modal-header">
                <h5 class="modal-title me-10">SMS Templates</h5>
                <button type="button" class="btn btn-success btn-sm float-end createNew"
                    data-type="<?= $blockType ?>">Create New Teplete</button>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="smsTempBody">
                Loading...
            </div>
        </div>
    </div>
</div>


<!-- Create New Template Modal -->
<div class="modal fade" id="createTempModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="newTempForm">

                    <input type="text" name="temp_type" id="temp_type">

                    <div class="mb-3">
                        <label class="form-label">Template Title</label>
                        <input type="text" name="temp_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Text</label>
                        <textarea name="temp_text" class="form-control" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Template</button>

                </form>

            </div>

        </div>
    </div>
</div>


<!-- SMS Variable Modal -->
<!-- SMS Variable Modal -->
<div class="modal fade" id="smsVarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:70vh; overflow-y:auto;">

            <div class="modal-header pb-3">
                <h5 class="modal-title">SMS Variables</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-bodys">

                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Use Values</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        for ($i = 0; $i < count($sms_hint); $i++) {
                            echo "<tr>
                                    <td>{$sms_hint[$i]}</td>
                                    <td>{$sms_var[$i]}</td>
                                    <td>{$sms_sample[$i]}</td>
                                    <td>
                                        <button class='btn btn-sm btn-success chooseVar'
                                            data-var=\"{$sms_hint[$i]}\">
                                            Choose
                                        </button>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>




<script>
    function readBlockInputs(containerId) {
        // containerId এর ভেতরের সব ইনপুট/সিলেক্ট/টেক্সটএরিয়া খুঁজে বের করব
        const elements = document.querySelectorAll(`#${containerId} input, #${containerId} select, #${containerId} textarea`);

        let values = {};
        let count = 0;

        elements.forEach(el => {
            let id = el.id || el.name || `no_id_${count}`;
            let val;

            if (el.type === "checkbox") {
                val = el.checked ? 1 : 0;
            } else {
                val = el.value;
            }

            values[id] = val;
            count++;
        });

        // Debugging output
        document.getElementById('jsondata_' + containerId).innerHTML =
            "Total Elements: " + count + "<br>" + JSON.stringify(values, null, 2);

        console.log("Total Elements:", count);
        console.log(values);

        return { count, values };
    }

</script>


<!-- http://cpanel.smsvaults.work/sendtext?apikey=$appKey&secretkey=$secretKey&callerID=01234567890&toUser=$mobile&messageContent=$message -->

<script>
    function savesetting(block, blockbox) {
        // block_1 থেকে সব ইনপুট সংগ্রহ
        let { values } = readBlockInputs(block);

        console.log("Sending to backend:", values);

        // Ajax এ JSON হিসেবে পাঠানো
        $.ajax({
            type: "POST",
            url: "backend/save-sms-settings.php",
            data: {
                sms_settings: JSON.stringify(values),
                blockbox: blockbox,
            },


            cache: false,
            beforeSend: function () {
                $('#jsondata_' + block).html('<small>Updating...</small>');
            },
            success: function (html) {
                $("#jsondata_" + block).html(html);
                showToast('success', 'Updated successfully', 'Updated');
            }
        });
    }

</script>


<script>
    $(document).ready(function () {

        // Modal open & load templates
        $(document).on("click", ".loadTemp", function () {
            let cat = $(this).data("cat");
            let block = $(this).data("block");; // PHP variable

            $("#smsTempBody").html("Loading...");

            $.ajax({
                url: "ajax/load-sms-templates.php",
                type: "POST",
                data: { cat: cat, block: block },
                success: function (res) {
                    $("#smsTempBody").html(res);
                    // $("#smsTempModal").modal("show");
                    var modal = new bootstrap.Modal(document.getElementById('smsTempModal'));
                    modal.show();
                }
            });
        });

        // Choose template
        $(document).on("click", ".chooseTemp", function () {
            let txt = $(this).data("text");
            let block = $(this).data("block");

            $("#" + block + "_text").val(txt);

            var modalEl = document.getElementById('smsTempModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);

            modalInstance.hide(); // এখন ঠিকমতো hide হবে
        });

    });
</script>

<script>
    $(document).on("click", ".createNew", function () {

        // let type = $(this).data("type");
        let type = document.getElementById('newtemptype').value;

        // hidden field এ সেট করো
        alert(type);
        $("#temp_type").val(type);

        // Modal open
        var modal = new bootstrap.Modal(document.getElementById('createTempModal'));
        modal.show();
    });



    $("#newTempForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: "ajax/sms-new-template.php",
            type: "POST",
            data: $(this).serialize(),
            success: function (res) {
                if (res === "SUCCESS") {
                    // success message
                    alert("Template Saved Successfully!");
                    // Close the create modal
                    var modalEl = document.getElementById('createTempModal');
                    var modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance.hide();

                    // Reload list (optional)
                    $(".loadTemp[data-cat='" + $("#temp_type").val() + "']").click();
                }
            }
        });
    });

</script>

<script>
    $(document).on("click", ".loadVar", function () {

        activeBlock = $(this).data("block"); // blockName store

        var modal = new bootstrap.Modal(document.getElementById('smsVarModal'));
        modal.show();
    });



    // Choose variable → insert into textarea
    $(document).on("click", ".chooseVar", function () {

        let variable = $(this).data("var"); // [[STUDENT_NAME_ENG]] etc.

        let textarea = $("#" + activeBlock + "_text");

        // Cursor position এ text insert
        let startPos = textarea[0].selectionStart;
        let endPos = textarea[0].selectionEnd;

        let oldText = textarea.val();

        textarea.val(
            oldText.substring(0, startPos) +
            variable +
            oldText.substring(endPos, oldText.length)
        );

        // close modal
        var modalEl = document.getElementById('smsVarModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();
    });
</script>


<script>
    $(document).on("click", ".sendTest", function () {

        let text = $(this).data("text");

        let mobile = prompt("Enter test mobile number:");

        if (!mobile || mobile.trim() == "") {
            alert("Mobile number is required!");
            return;
        }

        $.ajax({
            url: "core/ajax-send-sms.php",
            type: "POST",
            data: {
                mobile: mobile,
                text: text,
                camp: 'Test'
            },
            success: function (res) {
                // alert(res);
                showToast('info', res, 'Message Sent');
            }
        });

    });

</script>

</body>

</html>