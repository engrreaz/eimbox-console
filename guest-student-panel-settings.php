<?php
require_once 'header.php';


/* -------------------------------
   Load existing settings
-------------------------------- */
$sql = "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$q = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($q);

$adminData = json_decode($row['admin_data'] ?? '{}', true);

$settings = $adminData['Panel Settings']['Guest Student'] ?? [];

/* default values */
function g($arr, $key, $def = null)
{
    return $arr[$key] ?? $def;
}

/* -------------------------------
   Save settings
-------------------------------- */
if (isset($_POST['save_settings'])) {

    $settings = [
        'panel_active' => $_POST['panel_active'] ?? 'no',
        'access_times' => (int) ($_POST['access_times'] ?? 0),
        'max_stay_time' => (int) ($_POST['max_stay_time'] ?? 0),
        'login_security' => $_POST['login_security'] ?? [],

        'result' => isset($_POST['result']),
        'result_details' => isset($_POST['result_details']),
        'result_pdf' => isset($_POST['result_pdf']),
        'result_archive' => isset($_POST['result_archive']),

        'attendance' => isset($_POST['attendance']),
        'attendance_details' => isset($_POST['attendance_details']),

        'payment' => isset($_POST['payment']),
        'payment_details' => isset($_POST['payment_details']),
        'payment_history' => isset($_POST['payment_history']),
        'online_payment' => isset($_POST['online_payment']),

        'download_profile' => isset($_POST['download_profile']),
        'notice' => isset($_POST['notice']),
        'notification' => isset($_POST['notification']),
    ];

    $adminData['Panel Settings']['Guest Student'] = $settings;

    $json = mysqli_real_escape_string(
        $conn,
        json_encode($adminData, JSON_UNESCAPED_UNICODE)
    );

    mysqli_query(
        $conn,
        "UPDATE scinfo SET admin_data='$json' WHERE sccode='$sccode'"
    );

    echo "<div class='container-xxl flex-grow-1 pb-0 mb-0'><div class='alert alert-success'>Settings Updated</div></div>";
}
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <form method="post">

        <div class="card mb-3 p-3 px-5">
            <div class="card-header"><b>Guest Student Panel Settings</b></div>
            <div class="card-body">

                <!-- Panel Active -->
                <div class="row">

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input " style="width:50px; margin-right:10px; height:20px;"
                            type="checkbox" name="panel_active" value="yes" <?= g($settings, 'panel_active') == 'yes' ? 'checked' : '' ?>>
                        <label for="panel_active" class="form-check-label fw-bold fs-6 pt-1">Panel Active /
                            In-active</label>
                    </div>


                </div>

                <div class="row">

                    <div class="col-12 alert alert-info">
                        <b>Note:</b> After enabling the guest student panel, make sure to configure the
                        "Login Security" options below to enhance the security of student logins.
                        <div class="fs-small text-secondary">
                            Enable or disable the guest student panel for users with guest access. After enable this
                            features
                            anybody can login as a student without any credentions. It's need only some information
                            to login
                            like session, class, section roll no. <br> You may also set some mandatory extra
                            information to
                            increase security described below.
                        </div>

                    </div>


                    <div class="fs-5 pb-3">Checking Validation Options</div>


                    <div>

                        <?php
                        $sec = ['Class Teacher', 'DOB', 'Mobile Number', 'Name', 'ID Number', 'Village', 'Blood Group'];
                        foreach ($sec as $s):
                            ?>
                            <div class="form-check form-check-block mb-2">
                                <input class="form-check-input me-3"  type="checkbox" name="login_security[]" value="<?= $s ?>"
                                    <?= in_array($s, g($settings, 'login_security', [])) ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    <?= $s ?>
                                    <br>
                                    <span class="alert-info p-1 text-info fs-small d-block">Enable to check validation with <b>Class Teacher</b></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr>


                    <!-- Access Times -->
                    <label>Access Times</label>
                    <select name="access_times" class="form-select mb-2">
                        <?php foreach ([0, 5, 10, 20, 50, 100] as $v): ?>
                            <option value="<?= $v ?>" <?= g($settings, 'access_times') == $v ? 'selected' : '' ?>>
                                <?= $v == 0 ? 'Unlimited' : $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Max Stay Time -->
                    <label>Max Stay Time (Min)</label>
                    <select name="max_stay_time" class="form-select mb-3">
                        <?php foreach ([0, 2, 5, 10, 20, 50, 100] as $v): ?>
                            <option value="<?= $v ?>" <?= g($settings, 'max_stay_time') == $v ? 'selected' : '' ?>>
                                <?= $v == 0 ? 'Unlimited' : $v . ' Min' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <hr>
                    <!-- Common checkbox helper -->
                    <?php
                    function chk($name, $label, $settings)
                    {
                        $c = !empty($settings[$name]) ? 'checked' : '';
                        echo "<div class='form-check'>
            <input class='form-check-input' type='checkbox' name='$name' $c>
            <label class='form-check-label'>$label</label>
          </div>";
                    }
                    ?>

                    <h6>Result</h6>
                    <?php chk('result', 'Enable Result', $settings); ?>
                    <?php chk('result_details', 'Show Details', $settings); ?>
                    <?php chk('result_pdf', 'View / Print PDF', $settings); ?>
                    <?php chk('result_archive', 'Show Archive', $settings); ?>

                    <h6 class="mt-2">Attendance</h6>
                    <?php chk('attendance', 'Enable Attendance', $settings); ?>
                    <?php chk('attendance_details', 'Show Details', $settings); ?>

                    <h6 class="mt-2">Payment</h6>
                    <?php chk('payment', 'Enable Payment', $settings); ?>
                    <?php chk('payment_details', 'Show Details', $settings); ?>
                    <?php chk('payment_history', 'Show History', $settings); ?>
                    <?php chk('online_payment', 'Enable Online Payment', $settings); ?>

                    <h6 class="mt-2">Others</h6>
                    <?php chk('download_profile', 'Download Profile', $settings); ?>
                    <?php chk('notice', 'Show Notice', $settings); ?>
                    <?php chk('notification', 'Show Notification', $settings); ?>

                </div>

                <div class="card-footer text-end">
                    <button class="btn btn-primary" name="save_settings">
                        Save Settings
                    </button>
                </div>
            </div>

    </form>
</div>

<?php require_once 'footer.php'; ?>


</body>

</html>