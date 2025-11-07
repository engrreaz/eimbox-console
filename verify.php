<?php
// verify.php
session_start();
// header.php already included if part of app
$pending = $_SESSION['pending_reg_id'] ?? null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify Mobile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <div class="card mx-auto" style="max-width:500px;">
      <div class="card-body">
        <h5 class="card-title">Mobile Verification</h5>
        <?php if (!$pending): ?>
          <div class="alert alert-warning">No pending registration found. Please register first.</div>
        <?php else: ?>
          <p>We have sent an OTP to your mobile. Enter it below to verify.</p>
          <form method="post" action="actions/verify_submit.php">
            <div class="mb-3">
              <label class="form-label">Registration ID</label>
              <input name="reg_id" class="form-control" value="<?= htmlspecialchars($pending) ?>" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">OTP</label>
              <input name="otp" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between">
              <button class="btn btn-secondary" type="button" onclick="window.location='register.php'">Back</button>
              <button class="btn btn-primary" type="submit">Verify</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
