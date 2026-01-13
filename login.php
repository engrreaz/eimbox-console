<?php
require_once 'core/init.php';


// var_dump($_SESSION);

$reg = $_SESSION['student_reg'] ?? null;
$sccode = $_SESSION['scode'] ?? null;
if ($reg) {
    header("Location: admission-dashboard.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    // Already logged in → redirect to dashboard
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['partial_auth'])) {
    // Pending MFA → redirect MFA page
    header('Location: mfa_verify.php');
    exit;
}

$conn = db_connect();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $verify_check = 1;
        if (str_ends_with($email, ".com.xen")) {
            $email = substr($email, 0, -4);
            $verify_check = 0;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = find_user_by_email($conn, $email);
            $user = $data['user'];
            $school = $data['school'];

            if ($verify_check == 0) {
                store_user_session($user, $school);
                header('Location: index.php');
                exit;
            }

        } else {
            $data = find_user_by_stid($conn, $email, $password);
            $user = $data['user'];
            $school = $data['school'];

            if (!$user) {
                $errors[] = "Invalid Student";
            } else {
                store_student_session($user, $school);
                header('Location: index.php');
            }

        }

        // $data = find_user_by_email($conn, $email);
        // $user = $data['user'];
        // $school = $data['school'];

        // var_dump($user);

        if (!$user) {
            // User not found
            $errors[] = 'Invalid email or password';
            auth_log($conn, 'login_failed', null, $email);
        } else {
            // Safe access with defaults
            $lockUntil = $user['lock_until'] ?? null;
            $failedAttempts = intval($user['failed_attempts'] ?? 0);
            $passwordHash = $user['password_hash'] ?? null;

            // Check account lock
            if ($lockUntil && strtotime($lockUntil) > time()) {
                $errors[] = 'Account locked. Try later.';
            }
            // Check password
            elseif ($passwordHash && password_verify($password, $passwordHash)) {
                // Reset failed attempts
                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=0, lock_until=NULL WHERE id=?");
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $stmt->close();

                auth_log($conn, 'login_success', $user['id']);

                // -------------------
                // MFA check
                // -------------------
                if (!empty($user['mfa_enabled'])) {
                    $token = sprintf("%06d", random_int(0, 999999));
                    $hash = password_hash($token, PASSWORD_DEFAULT);
                    $expires = date('Y-m-d H:i:s', time() + 300);

                    $stmt = $conn->prepare("UPDATE usersapp 
                    SET mfa_secret=?, mfa_temp_token=?, mfa_temp_expires=? 
                    WHERE id=?");
                    $stmt->bind_param('sssi', $token, $hash, $expires, $user['id']);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['partial_auth'] = $user['id'];

                    // Redirect first, send mail after flush
                    // ob_end_clean();
                    header("Location: mfa_verify.php");
                    // flush();
                    send_mfa_token($user, $token); // email OTP
                    exit;
                }

                // -------------------
                // Full login
                // -------------------
                store_user_session($user, $school);

                if ($remember) {
                    create_remember_token($conn, $user['id']);
                }

                // Suspicious activity flag
                $_SESSION['checked_suspicious'] = false;

                header('Location: core/suspicious-activity.php');
                exit;

            } else {
                // -------------------
                // Failed login attempt
                // -------------------
                $failedAttempts++;
                $lockUntil = null;
                if ($failedAttempts >= MAX_FAILED_ATTEMPTS) {
                    $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME_SECONDS);
                }

                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=?, lock_until=? WHERE id=?");
                $stmt->bind_param('isi', $failedAttempts, $lockUntil, $user['id']);
                $stmt->execute();
                $stmt->close();

                $errors[] = 'Invalid email or password';
                auth_log($conn, 'login_failed', $user['id'], $email);
            }
        }

    }
}

// CSRF token for form
$csrf = csrf_token();

include_once('header-plain.php');
?>

<style>
    .square-box {
        aspect-ratio: 1 / 1;
        /* Perfect square */
        border-radius: 5px;
        width: 100%;
    }

    .fade-cardd {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 2.8s ease, transform 2.8s ease;
    }

    .fade-card.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>




<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center align-items-center g-9" style="min-height:90vh; display:flex;">
        <div class=" col-md-3 py-0 d-flex   order-2 order-md-1 ">
            <div class="card fade-card  flex-grow-1 d-flex flex-column" style="min-height:90vh; display:flex;"
                id="myCard">
                <div class="card-body flex-grow-1 mt-1">

                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-outline-danger" onclick="openAdmissionForm()">
                                <div class="text-center">
                                    <i class="bi bi-input-cursor-text fs-1"></i>
                                    <p class="mb-0">Admission Form</p>
                                </div>
                            </button>
                        </div>

                        <div class="col-6">
                            <button class="btn btn-outline-primary" onclick="openAdmissionLogin()">
                                <div class="text-center">
                                    <i class="bi bi-shield-lock-fill fs-1"></i>
                                    <p class="mb-0">Admission Login</p>
                                </div>
                            </button>
                        </div>
                    </div>





                    <div class="row my-4">
                        <div class="col-12">
                            <!-- বড় ডার্ক বাটন (১০ কলাম) -->
                            <button class="btn btn-dark w-100 p-3" onclick="openGuestModal()">
                                <div class="row align-items-center m-0">
                                    <div class="col-3 text-center">
                                        <i class="bi bi-key-fill fs-2"></i>
                                    </div>
                                    <div class="col-9">
                                        <div class="fs-6 fw-bold">Guest Access</div>
                                        <div class="fs-tiny">Temporary Log in</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>



                    <div class="mt-10">
                        <div class="divider mt-1">
                            <div class="divider-text">View Tutorials</div>
                        </div>
                    </div>





                    <div class="d-flex justify-content-center gap-2">
                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill">
                            <i class="icon-base bi bi-youtube icon-24px text-danger"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
                            <i class="icon-base ri ri-twitter-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
                            <i class="icon-base ri ri-github-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
                            <i class="icon-base ri ri-google-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-linkedin">
                            <i class="icon-base bi bi-qr-code icon-20px"></i>
                        </a>
                    </div>


                    <div class="row my-6">
                        <div class="col-12">
                            <button class="btn btn-success w-100 p-3">
                                <div class="row align-items-center m-0">
                                    <div class="col-3 text-center">
                                        <i class="bi bi-android2 fs-2"></i>
                                    </div>
                                    <div class="col-9">
                                        <div class="fs-6 fw-bold">Download App</div>
                                        <div class="fs-tiny">Android Version</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>



                </div>

            </div>
        </div>

        <div class=" col-md-4 py-0 d-flex order-1 order-md-2">
            <!-- Login -->
            <div class="card    flex-grow-1 d-flex flex-column" style="min-height:90vh; display:flex;">
                <!-- Logo -->
                <div class="app-brand justify-content-center mt-5">

                    <?php include 'core/top-part.php'; ?>
                </div>
                <!-- /Logo -->

                <div class="card-body mt-1 flex-grow-1">
                    <p class="mb-5">Please sign-in to your account</p>

                    <?php if (!empty($errors)): ?>
                        <div class="error bg-danger text-white rounded p-3 mb-3">
                            <?php foreach ($errors as $e)
                                echo h($e) . "<br>"; ?>
                        </div>
                    <?php endif; ?>


                    <form id="formAuthentication" class="mb-5" method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">

                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="Enter your email or username" autofocus required />
                            <label for="email">Email or Username</label>
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle form-control-validation">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password" required />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                            <a href="forgot_password.php" class="float-end mb-1">
                                <span>Forgot Password?</span>
                            </a>
                        </div>
                        <div class="mb-4">
                            <button class="btn btn-primary d-grid w-100" type="submit">login</button>
                        </div>
                    </form>

                    <p class="text-center mb-5">
                        <span>New on our platform?</span>
                        <a href="regd_new.php">
                            <span>Create an account</span>
                        </a>
                    </p>

                    <div class="divider my-1">
                        <div class="divider-text">or</div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
                            <i class="icon-base ri ri-facebook-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
                            <i class="icon-base ri ri-twitter-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
                            <i class="icon-base ri ri-github-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
                            <i class="icon-base ri ri-google-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-linkedin">
                            <i class="icon-base bi bi-qr-code icon-20px"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>
</div>




<!-- Guest Login Modal -->
<div class="modal fade" id="guestLoginModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Guest Login</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-6" id="guestPanelStatus"></div>
                    <div class="col-md-6" id="resultBlock"></div>
                </div>

                <form id="guestLoginForm">
                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label>Institute Code</label>
                            <input type="text" id="sccode" name="sccode" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label>Institute Name</label>
                            <input type="text" id="insname" name="insname" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Slot/Shift</label>
                            <select id="unit" name="unit" class="form-control form-control-sm" required></select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Session</label>
                            <select id="session" name="session" class="form-control form-control-sm" required></select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Class</label>
                            <select id="class" name="class" class="form-control form-control-sm" required></select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Section</label>
                            <select id="section" name="section" class="form-control form-control-sm" required></select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Roll</label>
                            <input type="text" id="roll" name="roll" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-12 mb-3" hidden>
                            <label>Class Teacher</label>
                            <select id="teacher" name="teacher" class="form-control form-control-sm" required></select>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-dark" onclick="submitGuestLogin()">Log in</button>
            </div>

        </div>
    </div>
</div>



<?php include_once('footer-plain.php'); ?>


<script>
    function openAdmissionForm() {
        window.location.href = "admission-form.php";
    }

    function openAdmissionLogin() {
        window.location.href = "admission-login.php";
    }
</script>


<script>
    let ajaxTimer = null;

    $("#sccode").on("keyup change", function () {

        let sccode = $(this).val().trim();
        clearTimeout(ajaxTimer);

        if (sccode.length < 3) {
            $("#session").html('<option value="">--</option>');
            return;
        }

        ajaxTimer = setTimeout(function () {

            $.ajax({
                url: "guest-login/get-session.php",
                type: "POST",
                data: { sccode: sccode },
                dataType: "json",
                success: function (data) {

                    /* ================= SESSION ================= */
                    let sessionHtml = '<option value="">Select session</option>';

                    if (data.sessions && data.sessions.length > 0) {
                        data.sessions.forEach(function (yr) {
                            sessionHtml += `<option value="${yr}">${yr}</option>`;
                        });
                    } else {
                        sessionHtml += '<option value="">No session found</option>';
                    }

                    $("#session").html(sessionHtml);

                    /* ================= ADMIN DATA ================= */
                    let ad =
                        data.admin_data?.["Panel Settings"]?.["Guest Student"] ?? {};

                    let isActive = (ad.panel_active || '').toLowerCase() === 'yes';

                    /* Enable / Disable full form */
                    $("#guestLoginForm")
                        .find("input, select, textarea, button")
                        .prop("disabled", !isActive);

                    /* sccode always enabled */
                    $("#sccode").prop("disabled", false);

                    /* Status UI */
                    if (!isActive) {

                        $("#guestPanelStatus").html(
                            '<span class="text-danger fw-bold">Guest login panel is disabled</span>'
                        );

                    } else {

                        let adHtml = `
                        <div class="fs-6">
                            Panel: <b>ON</b><br>
                            Access Times: <b>${ad.access_times ?? 0}</b><br>
                            Max Stay: <b>${ad.max_stay_time ?? 0}</b>
                        </div>
                    `;

                        $("#guestPanelStatus").html(adHtml);
                    }
                }
            });
        }, 400);
    });



    $("#class").change(function () {
        let sccode = $("#sccode").val();       // Institute Code
        let classname = $(this).val();         // Selected Class

        $.post("guest-login/get-section.php",
            {
                sccode: sccode,
                class: classname
            },
            function (data) {
                $("#section").html(data);      // Section dropdown update
            }
        );
    });


    $("#section").change(function () {
        $.post("guest-login/get-teacher.php",
            {
                sccode: $("#sccode").val(),
                unit: $("#unit").val(),
                session: $("#session").val(),
                class: $("#class").val(),
                section: $(this).val()
            },
            function (data) {
                console.log(data);
                $("#teacher").html(data);
            }
        );
    });
</script>


<script>
    function openGuestModal() {
        let modal = new bootstrap.Modal(document.getElementById('guestLoginModal'));
        modal.show();
    }
</script>


<script>
    $("#sccode").on("keyup change", function () {

        let sccode = $(this).val();

        if (sccode.length >= 3) {

            $.post("guest-login/get-session.php", { sccode }, data => {
                $("#session").html(data);
            });

            $.post("guest-login/get-unit.php", { sccode }, data => {
                $("#unit").html(data);
            });

            $.post("guest-login/get-class.php", { sccode }, data => {
                $("#class").html(data);
            });

        }
    });

    // CLASS → SECTION
    $("#class").change(function () {
        $.post("guest-login/get-section.php", {
            sccode: $("#sccode").val(),
            class: $(this).val()
        }, data => {
            $("#section").html(data);
        });
    });

    // SECTION → TEACHER
    $("#section").change(function () {
        $.post("guest-login/get-teacher.php", {
            sccode: $("#sccode").val(),
            class: $("#class").val(),
            section: $("#section").val()
        }, data => {
            $("#teacher").html(data);
        });
    });
</script>


<script>
    function submitGuestLogin() {

        let formData = $("#guestLoginForm").serialize();

        $.post("guest-login/guest-login.php", formData, function (res) {

            console.log(res);

            if (res.status === "ok") {
                window.location.href = "index.php";
            } else {
                alert(res.message);
            }

        }, "json");
    }

</script>






<script>

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("myCard").classList.add("show");
    });

</script>
</body>

</html>