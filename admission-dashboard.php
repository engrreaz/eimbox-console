<?php
session_start();
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'header-plain.php';
require_once 'core/core-val.php';


$sccode = $_COOKIE['sccode'];
if ($sccode == '') {
    header("Location: admission-login.php");
    exit;
}


$reg = $_SESSION['student_reg'] ?? null;
$scode = $_SESSION['scode'] ?? null;
if (!$reg) {
  header("Location: admission-login.php");
  exit;
}
$stmt = $conn->prepare("SELECT * FROM registrations WHERE reg_id = ? LIMIT 1");
$stmt->bind_param("s", $reg);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

$sccode = $data['sccode'];
include_once('actions/get-sc-data.php');
?>


<div class="container">

  <?php include_once('actions/sc-header.php'); ?>

  <h4>Welcome, <?= htmlspecialchars($data['stnameeng']) ?></h4>
  <p>Your Reg ID: <?= htmlspecialchars($data['reg_id']) ?></p>

  <div class="alert alert-info">
    ইতিমধ্যে যদি তোমার প্রবেশ পত্রটি ডাউনলোড না করে থাকো, তাহলে নিচের বাটনে ক্লিক করে ডাউনলোড করো।
  </div>



  <p><a class="btn btn-primary" href="admit_card.php?id=<?= urlencode($data['id']) ?>" target="_blank">Download Entry
      Ticket</a></p>
  <p><a href="logout.php?admission" class="btn btn-outline-secondary">Logout</a></p>
</div>



<?php include_once('footer-plain.php'); ?>
</body>

</html>