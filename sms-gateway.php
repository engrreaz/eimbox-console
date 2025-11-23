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
    <h3 class="d-print-none">Messaging Settings</h3>

    <?php include 'core/sms-settings-block-0.php'; ?>

    <div class="row d-print-none">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">
                    <h4 class="m-0">Messages</h4>
                </div>

                <?php



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

                <!-- *********************************************************************X -->
                <!-- *********************************************************************X -->
                <!-- *********************************************************************X -->
                <!-- Header -->

                <!-- *********************************************************************X -->
                <!-- *********************************************************************X -->
                <!-- *********************************************************************X -->




            </div>





        </div>
    </div>


    <!-- ************************************************** -->






</div>

<?php
include 'footer.php';
?>




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
            }
        });
    }

</script>

</body>

</html>