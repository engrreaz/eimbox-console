<?php
session_start();
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/core-val.php';
require_once 'header-plain.php';

$sccode = 103187;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reg_id = $_POST['reg_id'] ?? '';
    $pin = $_POST['pin'] ?? '';
    $scode = $_POST['scode'] ?? '0';
    $stmt = $conn->prepare("SELECT id, reg_id FROM registrations WHERE reg_id = ? AND pin = ? AND verified = 1 LIMIT 1");
    $stmt->bind_param("ss", $reg_id, $pin);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if ($row) {
        $_SESSION['student_reg'] = $row['reg_id'];
        $_SESSION['scode'] = $scode;
        // echo $_SESSION['student_reg'];
        header("Location: admission-dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials or not verified.";
    }
}


include_once 'actions/get-sc-data.php';
?>



<div class="container">

    <div class="row">
        <div class="col-12  ">
            <?php include_once('actions/sc-header.php'); ?>
        </div>
    </div>



    <div class="card mx-auto" style="max-width:420px;">
        <div class="card-body">
            <h5 class="p-0 m-0">Student Login (Admission Only)</h5>

            <lable class="label-text pb-3">শিক্ষার্থী লগইন (শুধুমাত্র ভর্তির আবেদন) <br><br></lable>
            <?php if (!empty($error))
                echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
            <form method="post">
                <input type="hidden" name="scode" value="<?= $sccode; ?>" />
                <div class="mb-3"><input name="reg_id" class="form-control"
                        placeholder="Registration ID / রেজিস্ট্রেশন নম্বর"></div>
                <div class="mb-3"><input name="pin" class="form-control" placeholder="PIN / পিন নম্বর"></div>
                <div class="d-flex justify-content-between"><button class="btn btn-secondary" type="button"
                        onclick="window.location='admission-form.php'"> New Admission </button>
                    <button class="btn btn-primary" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once('footer-plain.php'); ?>

</body>

</html>