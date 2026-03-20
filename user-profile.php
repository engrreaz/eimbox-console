<?php
require_once 'header.php';

/** * ডাটা ফেচ করা 
 * মনে রাখবেন: আপনার SQL ডাম্প অনুযায়ী $usr যদি প্রাইমারি key 'id' হয়, তবে কুয়েরি হবে id = ?
 * আর যদি 'email' দিয়ে চেক করতে চান তবে bind_param এ "si" হবে।
 */
$stmt = $conn->prepare("SELECT * FROM usersapp WHERE email = ? AND sccode = ?");
$stmt->bind_param("ii", $usr, $sccode);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData) {
  echo "<div class='alert alert-danger m-3'>User record not found!</div>";
  exit;
}

// প্রোফাইল আপডেট হ্যান্ডলিং
if (isset($_POST['update_profile'])) {
  $p_name = $_POST['profilename'];
  $mobile = $_POST['mobile'];
  $theme = $_POST['theme'];

  // শুধুমাত্র আপডেটযোগ্য ফিল্ডগুলো কুয়েরিতে রাখা হয়েছে
  $up_stmt = $conn->prepare("UPDATE usersapp SET profilename = ?, mobile = ?, theme = ? WHERE id = ?");
  $up_stmt->bind_param("sssi", $p_name, $mobile, $theme, $usr);

  if ($up_stmt->execute()) {
    echo "<script>alert('Profile Updated Successfully!'); window.location.href='user-profile.php';</script>";
  }
}

// পাসওয়ার্ড পরিবর্তন হ্যান্ডলিং (Argon2id)
if (isset($_POST['change_password'])) {
  $current_pass = $_POST['current_password'];
  $new_pass = $_POST['new_password'];
  $confirm_pass = $_POST['confirm_password'];

  if (password_verify($current_pass, $userData['password_hash'])) {
    if ($new_pass === $confirm_pass) {
      $new_hash = password_hash($new_pass, PASSWORD_ARGON2ID);
      $pass_stmt = $conn->prepare("UPDATE usersapp SET password_hash = ? WHERE id = ?");
      $pass_stmt->bind_param("si", $new_hash, $usr);
      if ($pass_stmt->execute()) {
        echo "<script>alert('Password Changed Successfully!');</script>";
      }
    } else {
      echo "<script>alert('New Passwords do not match!');</script>";
    }
  } else {
    echo "<script>alert('Current Password is incorrect!');</script>";
  }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">User /</span> Account Settings</h4>

  <div class="row">
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
          <div class="user-avatar-section">
            <div class="d-flex align-items-center flex-column">
              <img class="img-fluid rounded mb-3 pt-1"
                src="<?= $userData['photourl'] ?: 'https://via.placeholder.com/150' ?>" height="100" width="100"
                alt="User avatar" style="object-fit: cover; border: 3px solid #eee;">
              <div class="user-info text-center">
                <h4 class="mb-2"><?= $userData['profilename'] ?></h4>
                <span class="badge bg-label-primary mt-1"><?= $userData['userlevel'] ?></span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-around flex-wrap mt-3 py-3 border-bottom border-top">
            <div class="d-flex align-items-start me-4 mt-3 gap-2">
              <span class="badge bg-label-primary p-2 rounded"><i class="bi bi-shield-check"></i></span>
              <div>
                <h5 class="mb-0">Admin</h5>
                <small>Level <?= $userData['admin'] ?></small>
              </div>
            </div>
            <div class="d-flex align-items-start mt-3 gap-2">
              <span class="badge bg-label-success p-2 rounded"><i class="bi bi-person-badge"></i></span>
              <div>
                <h5 class="mb-0">Chief</h5>
                <small><?= $userData['is_chief'] ? 'Verified' : 'Regular' ?></small>
              </div>
            </div>
          </div>
          <p class="mt-4 small text-uppercase text-muted">Account Details</p>
          <div class="info-container">
            <ul class="list-unstyled">
              <li class="mb-2">
                <span class="fw-bold me-1">Email:</span>
                <span><?= $userData['email'] ?></span>
              </li>
              <li class="mb-2">
                <span class="fw-bold me-1">Status:</span>
                <span
                  class="badge bg-<?= $userData['active'] ? 'success' : 'danger' ?>"><?= $userData['active'] ? 'Active' : 'Inactive' ?></span>
              </li>
              <li class="mb-2">
                <span class="fw-bold me-1">Last Login:</span>
                <span class="small"><?= $userData['lastlogin'] ?></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
      <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs border-bottom-0 shadow-sm bg-white rounded-top" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
              data-bs-target="#navs-profile">
              <i class="bi bi-person me-1"></i> Profile & System Info
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-security">
              <i class="bi bi-lock me-1"></i> Security
            </button>
          </li>
        </ul>
        <div class="tab-content bg-white shadow-sm rounded-bottom p-4">

          <div class="tab-pane fade show active" id="navs-profile" role="tabpanel">
            <form method="POST">
              <div class="row">
                <div class="col-12 mb-3">
                  <h6 class="fw-bold text-primary border-bottom pb-2">Editable Information</h6>
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label fw-semibold text-dark">Full Name</label>
                  <input class="form-control" type="text" name="profilename" value="<?= $userData['profilename'] ?>"
                    required>
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label fw-semibold text-dark">Mobile Number</label>
                  <input class="form-control" type="text" name="mobile" value="<?= $userData['mobile'] ?>">
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label fw-semibold text-dark">System Theme</label>
                  <select name="theme" class="form-select">
                    <option value="light" <?= $userData['theme'] == 'light' ? 'selected' : '' ?>>Light Mode</option>
                    <option value="dark" <?= $userData['theme'] == 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                  </select>
                </div>

                <div class="col-12 mt-4 mb-3">
                  <h6 class="fw-bold text-muted border-bottom pb-2">Read-Only System Data</h6>
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label small text-muted">User ID</label>
                  <input class="form-control bg-light text-muted" type="text" value="<?= $userData['userid'] ?>"
                    readonly>
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label small text-muted">Admin Privilege</label>
                  <input class="form-control bg-light text-muted fw-bold" type="text"
                    value="Level <?= $userData['admin'] ?>" readonly>
                </div>
                <div class="mb-3 col-md-4">
                  <label class="form-label small text-muted">Chief Access</label>
                  <div class="form-control bg-light text-muted fw-bold">
                    <?= $userData['is_chief'] ? '<i class="bi bi-check-circle text-success me-1"></i>Yes' : '<i class="bi bi-x-circle text-danger me-1"></i>No' ?>
                  </div>
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label small text-muted">Active Session</label>
                  <input class="form-control bg-light text-muted" type="text" value="<?= $userData['session'] ?>"
                    readonly>
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label small text-muted">User Level</label>
                  <input class="form-control bg-light text-muted" type="text" value="<?= $userData['userlevel'] ?>"
                    readonly>
                </div>
              </div>
              <div class="mt-3 text-end border-top pt-3">
                <button type="submit" name="update_profile" class="btn btn-primary px-4 shadow-sm">Save Profile
                  Changes</button>
              </div>
            </form>
          </div>

          <div class="tab-pane fade" id="navs-security" role="tabpanel">
            <form method="POST">
              <div class="row">
                <div class="mb-3 col-12">
                  <label class="form-label fw-semibold text-dark">Current Password</label>
                  <input class="form-control" type="password" name="current_password" required
                    placeholder="············">
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label fw-semibold text-dark">New Password</label>
                  <input class="form-control" type="password" name="new_password" required placeholder="············">
                </div>
                <div class="mb-3 col-md-6">
                  <label class="form-label fw-semibold text-dark">Confirm New Password</label>
                  <input class="form-control" type="password" name="confirm_password" required
                    placeholder="············">
                </div>
              </div>
              <div class="alert alert-warning border-0 bg-light-warning py-2 mt-2">
                <small><i class="bi bi-shield-lock me-1 text-warning"></i> Password is secured with high-entropy
                  <strong>Argon2id</strong> hashing.</small>
              </div>
              <div class="mt-3 text-end border-top pt-3">
                <button type="submit" name="change_password" class="btn btn-danger px-4 shadow-sm">Update
                  Password</button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<style>
  /* অতিরিক্ত কিছু কাস্টম স্টাইল */
  .bg-label-primary {
    background-color: #e7e7ff;
    color: #696cff;
  }

  .bg-label-success {
    background-color: #e8fadf;
    color: #71dd37;
  }

  .nav-tabs .nav-link.active {
    border-bottom: 3px solid #696cff !important;
    color: #696cff !important;
    font-weight: bold;
  }

  .tab-content {
    border: 1px solid #eee;
  }
</style>

<script>
  console.log("Account Settings Interface Loaded.");
</script>
</body>

</html>