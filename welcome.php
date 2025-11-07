<?php
session_start();
$reg_id = $_SESSION['verified_reg_id'] ?? null;
if (!$reg_id) {
    // header("Location: register.php");
    // exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Welcome</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <div class="card mx-auto" style="max-width:700px;">
    <div class="card-body">
      <h5 class="card-title">Registration Complete</h5>
      <p>Your registration ID: <strong><?= htmlspecialchars($reg_id) ?></strong></p>
      <p>
        <a class="btn btn-primary" href="generate_pdf.php?reg=<?= urlencode($reg_id) ?>" target="_blank">Download Entry Ticket (PDF)</a>
      </p>
      <p>You can also <a href="login.php">login</a> with your ID & PIN to see notices.</p>
    </div>
  </div>
</div>
</body>
</html>
