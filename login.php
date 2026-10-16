<?php
require_once 'core/init.php';

// Check if already logged-in session should be blocked under maintenance
if (isset($_SESSION['user_id'])) {
    if ($platformStatus['status'] === 'maintenance' && !MaterioPlatformGuard::isAdminUser()) {
        MaterioPlatformGuard::renderScreen(503, 'maintenance', $platformStatus);
        exit;
    }
    // Already logged in → redirect to dashboard
    header('Location: index.php');
    exit;
}

$reg = $_SESSION['student_reg'] ?? null;
$sccode = $_SESSION['scode'] ?? null;
if ($reg) {
    if ($platformStatus['status'] === 'maintenance') {
        MaterioPlatformGuard::renderScreen(503, 'maintenance', $platformStatus);
        exit;
    }
    header("Location: admission-dashboard.php");
    exit;
}

if (isset($_SESSION['partial_auth'])) {
    // Pending MFA → redirect MFA page
    header('Location: mfa_verify.php');
    exit;
}

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
                if ($platformStatus['status'] === 'maintenance' && !MaterioPlatformGuard::isAdminUser([
                    'is_admin' => $user['admin'] ?? 0,
                    'userlevel' => $user['userlevel'] ?? '',
                    'user_role' => $user['role'] ?? '',
                    'is_chief' => $user['is_chief'] ?? 0
                ])) {
                    $errors[] = 'Platform is currently undergoing scheduled maintenance. Only system administrators can sign in at this time.';
                } else {
                    store_user_session($user, $school);
                    header('Location: index.php');
                    exit;
                }
            }

        } else {
            $data = find_user_by_stid($conn, $email, $password);
            $user = $data['user'];
            $school = $data['school'];

            if (!$user) {
                $errors[] = "Invalid Student";
            } elseif ($platformStatus['status'] === 'maintenance') {
                $errors[] = "Platform is currently undergoing scheduled maintenance. Student login is temporarily disabled.";
            } else {
                store_student_session($user, $school);
                header('Location: index.php');
                exit;
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
            // Maintenance mode check: block non-admins
            elseif ($platformStatus['status'] === 'maintenance' && !(intval($user['admin'] ?? 0) >= 4 || intval($user['is_chief'] ?? 0) > 0 || in_array(strtolower($user['userlevel'] ?? ''), ['super administrator', 'chief', 'administrator']))) {
                $errors[] = 'Platform is currently undergoing scheduled maintenance. Only system administrators can sign in at this time.';
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
    /* Material Design 3 (M3) Variables */
    :root, [data-bs-theme="light"] {
        --m3-sys-bg: #F8F9FA;
        --m3-sys-surface: #FFFFFF;
        --m3-sys-surface-container: #F1F3F5;
        --m3-sys-surface-container-high: #E9ECEF;
        --m3-sys-primary: #5A489B;
        --m3-sys-on-primary: #FFFFFF;
        --m3-sys-outline: #E2E8F0;
        --m3-sys-text-high: #1E293B;
        --m3-sys-text-med: #64748B;
        --m3-sys-text-muted: #94A3B8;
        --m3-sys-tonal-btn: #F1F3F5;
        --m3-sys-tonal-text: #334155;
    }

    [data-bs-theme="dark"] {
        --m3-sys-bg: #141218;
        --m3-sys-surface: #1D1B20;
        --m3-sys-surface-container: #211F26;
        --m3-sys-surface-container-high: #2B2930;
        --m3-sys-primary: #D0BCFF;
        --m3-sys-on-primary: #381E72;
        --m3-sys-outline: #332F37;
        --m3-sys-text-high: #E6E1E5;
        --m3-sys-text-med: #CAC4D0;
        --m3-sys-text-muted: #938F99;
        --m3-sys-tonal-btn: #2B2930;
        --m3-sys-tonal-text: #E6E1E5;
    }

    .m3-login-wrapper * {
        font-weight: normal !important;
        box-sizing: border-box;
    }

    .m3-login-wrapper {
        min-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 12px;
    }

    .m3-card {
        background-color: var(--m3-sys-surface);
        border: 1px solid var(--m3-sys-outline);
        border-radius: 28px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 32px 28px;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    /* M3 Input Box - No harsh borders */
    .m3-input-box {
        background-color: var(--m3-sys-surface-container);
        border-radius: 14px;
        padding: 10px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid transparent !important;
        transition: background-color 0.2s, box-shadow 0.2s;
    }

    .m3-input-box:focus-within {
        background-color: var(--m3-sys-surface-container-high);
        box-shadow: 0 0 0 2px var(--m3-sys-primary);
    }

    .m3-input-box i {
        font-size: 1.15rem;
        color: var(--m3-sys-text-med);
        flex-shrink: 0;
    }

    .m3-input-field {
        width: 100%;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        color: var(--m3-sys-text-high) !important;
        font-size: 0.95rem;
        padding: 6px 0;
    }

    .m3-input-field::placeholder {
        color: var(--m3-sys-text-muted) !important;
    }

    .m3-eye-btn {
        background: transparent;
        border: none !important;
        outline: none !important;
        color: var(--m3-sys-text-med);
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .m3-eye-btn:hover {
        color: var(--m3-sys-text-high);
    }

    /* Buttons & Tonal items */
    .btn-m3-filled {
        background-color: var(--m3-sys-primary) !important;
        color: var(--m3-sys-on-primary) !important;
        border: none !important;
        border-radius: 100px;
        padding: 12px 24px;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        transition: opacity 0.2s, transform 0.1s;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-m3-filled:hover {
        opacity: 0.92;
        color: var(--m3-sys-on-primary) !important;
    }

    .btn-m3-tonal {
        background-color: var(--m3-sys-tonal-btn) !important;
        color: var(--m3-sys-tonal-text) !important;
        border: 1px solid var(--m3-sys-outline) !important;
        border-radius: 16px;
        padding: 12px 14px;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: background-color 0.2s, border-color 0.2s;
        width: 100%;
    }

    .btn-m3-tonal:hover {
        background-color: var(--m3-sys-surface-container-high) !important;
        color: var(--m3-sys-text-high) !important;
    }

    /* Full circle icon container in KPI & Action cards */
    .m3-circle-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.2rem;
    }

    /* M3 Chips */
    .m3-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 100px;
        background-color: var(--m3-sys-surface-container);
        border: 1px solid var(--m3-sys-outline);
        color: var(--m3-sys-text-high);
        text-decoration: none;
        font-size: 0.84rem;
        transition: background-color 0.2s, border-color 0.2s;
        cursor: pointer;
    }

    .m3-chip:hover {
        background-color: var(--m3-sys-surface-container-high);
        color: var(--m3-sys-text-high);
    }
</style>

<div class="container-xxl m3-login-wrapper">
    <div class="row justify-content-center align-items-stretch g-4 w-100" style="max-width: 980px;">
        
        <!-- Primary Login Card (Mobile: Top, Desktop: Right) -->
        <div class="col-lg-6 col-md-7 d-flex order-1 order-md-2">
            <div class="m3-card flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                    <!-- App Logo & Branding -->
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(90, 72, 155, 0.12);">
                            <img src="assets/images/logo.png" alt="EIMBox Logo" style="width: 36px; height: 36px; object-fit: contain;" />
                        </div>
                        <h4 class="m-0" style="color: var(--m3-sys-text-high); font-size: 1.35rem; letter-spacing: 0.3px;">EIMBox</h4>
                        <div style="color: var(--m3-sys-text-muted); font-size: 0.82rem; margin-top: 3px;">Education Institute Management System</div>
                    </div>

                    <!-- Maintenance Notification -->
                    <?php if ($platformStatus['status'] === 'maintenance'): ?>
                        <div style="background-color: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 14px; padding: 12px 14px; font-size: 0.84rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="bi bi-tools flex-shrink-0" style="font-size: 1.1rem;"></i>
                            <div>Scheduled maintenance active. Only authorized system administrators may sign in.</div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div style="background-color: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 14px; padding: 12px 14px; font-size: 0.86rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="bi bi-exclamation-circle flex-shrink-0" style="font-size: 1.15rem;"></i>
                            <div>
                                <?php foreach ($errors as $e) echo h($e) . "<br>"; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Authentication Form -->
                    <form id="formAuthentication" method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">

                        <!-- Email / Username Input -->
                        <div class="m3-input-box">
                            <i class="bi bi-person"></i>
                            <input type="text" class="m3-input-field" id="email" name="email"
                                placeholder="Email, Username or Student ID" autofocus required />
                        </div>

                        <!-- Password Input -->
                        <div class="m3-input-box">
                            <i class="bi bi-shield-lock"></i>
                            <input type="password" id="password" class="m3-input-field" name="password"
                                placeholder="Enter password" required />
                            <button type="button" class="m3-eye-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                                <label class="form-check-label" for="remember" style="font-size: 0.86rem; color: var(--m3-sys-text-med); cursor: pointer;">Remember Me</label>
                            </div>
                            <a href="forgot_password.php" style="color: var(--m3-sys-primary); text-decoration: none; font-size: 0.86rem;">
                                Forgot Password?
                            </a>
                        </div>

                        <!-- Sign In Button -->
                        <button class="btn-m3-filled" type="submit">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </form>
                </div>

                <!-- Registration / Quick Portals Footer -->
                <div class="mt-4 pt-3 border-top" style="border-color: var(--m3-sys-outline) !important;">
                    <div class="text-center mb-3" style="font-size: 0.86rem; color: var(--m3-sys-text-med);">
                        New on our platform? <a href="regd_new.php" style="color: var(--m3-sys-primary); text-decoration: none;">Create an account</a>
                    </div>

                    <!-- Admission & Guest Quick Chips -->
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" class="m3-chip" onclick="openAdmissionForm()">
                            <i class="bi bi-pencil-square" style="color: #ef4444;"></i> Admission Form
                        </button>
                        <button type="button" class="m3-chip" onclick="openAdmissionLogin()">
                            <i class="bi bi-person-check" style="color: #3b82f6;"></i> Admission Login
                        </button>
                        <button type="button" class="m3-chip" onclick="openGuestModal()">
                            <i class="bi bi-key" style="color: #f59e0b;"></i> Guest Login
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Companion Card: Downloads & Applications (Mobile: Bottom, Desktop: Left) -->
        <div class="col-lg-5 col-md-5 d-flex order-2 order-md-1">
            <div class="m3-card flex-grow-1 d-flex flex-column justify-content-between">
                <div>
                    <!-- Card Header -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="m3-circle-icon" style="background: rgba(90, 72, 155, 0.12); color: var(--m3-sys-primary);">
                            <i class="bi bi-cloud-arrow-down"></i>
                        </div>
                        <div>
                            <h5 class="m-0" style="color: var(--m3-sys-text-high); font-size: 1.1rem;">EIMBox Applications</h5>
                            <div style="color: var(--m3-sys-text-muted); font-size: 0.78rem;">Official Android &amp; Desktop Software</div>
                        </div>
                    </div>

                    <!-- Download Items -->
                    <div class="d-flex flex-column gap-3 mb-4">
                        
                        <!-- 1. Android Lite -->
                        <a href="apps/EIMBox_Lite.apk" download="EIMBox_Lite.apk" class="btn-m3-tonal">
                            <div class="m3-circle-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                                <i class="bi bi-android2"></i>
                            </div>
                            <div class="text-start flex-grow-1">
                                <div style="color: var(--m3-sys-text-high); font-size: 0.9rem;">EIMBox Lite</div>
                                <div style="color: var(--m3-sys-text-muted); font-size: 0.76rem;">Android APK &bull; Fast &amp; Lightweight</div>
                            </div>
                            <i class="bi bi-download" style="color: var(--m3-sys-text-med); font-size: 1rem;"></i>
                        </a>

                        <!-- 2. Android Native -->
                        <a href="apps/EIMBox.apk" download="EIMBox.apk" class="btn-m3-tonal">
                            <div class="m3-circle-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div class="text-start flex-grow-1">
                                <div style="color: var(--m3-sys-text-high); font-size: 0.9rem;">EIMBox Native</div>
                                <div style="color: var(--m3-sys-text-muted); font-size: 0.76rem;">Android APK &bull; Full Feature Set</div>
                            </div>
                            <i class="bi bi-download" style="color: var(--m3-sys-text-med); font-size: 1rem;"></i>
                        </a>

                        <!-- 3. Desktop IDE -->
                        <a href="https://github.com/eimbox-official/eimbox-desktop-releases/releases/download/v2.0.0/EIMBox-Desktop-IDE-Setup-2.0.0.exe" target="_blank" rel="noopener noreferrer" class="btn-m3-tonal">
                            <div class="m3-circle-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                                <i class="bi bi-display"></i>
                            </div>
                            <div class="text-start flex-grow-1">
                                <div style="color: var(--m3-sys-text-high); font-size: 0.9rem;">Desktop IDE (Windows)</div>
                                <div style="color: var(--m3-sys-text-muted); font-size: 0.76rem;">Setup v2.0.0 &bull; Offline &amp; Biometric Sync</div>
                            </div>
                            <i class="bi bi-box-arrow-up-right" style="color: var(--m3-sys-text-med); font-size: 0.95rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- Guidance & Help note -->
                <div class="p-3 rounded-4" style="background-color: var(--m3-sys-surface-container); border: 1px solid var(--m3-sys-outline);">
                    <div class="d-flex align-items-center gap-2 mb-1" style="color: var(--m3-sys-text-high); font-size: 0.84rem;">
                        <i class="bi bi-info-circle" style="color: var(--m3-sys-primary);"></i>
                        <span>Seamless Multi-Platform Access</span>
                    </div>
                    <div style="color: var(--m3-sys-text-med); font-size: 0.76rem; line-height: 1.45;">
                        Install EIMBox on Android or Windows Desktop for real-time notifications, offline capability, and high-speed operations.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Guest Login Modal (M3 Material Dialog) -->
<div class="modal fade" id="guestLoginModal" tabindex="-1" aria-labelledby="guestLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: var(--m3-sys-surface); border: 1px solid var(--m3-sys-outline); border-radius: 28px; box-shadow: 0 16px 40px rgba(0,0,0,0.2); color: var(--m3-sys-text-high);">

            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="m3-circle-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-key"></i>
                    </div>
                    <h5 class="modal-title m-0" id="guestLoginModalLabel" style="font-size: 1.15rem; color: var(--m3-sys-text-high);">Guest Student Login</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="row mb-3">
                    <div class="col-md-6" id="guestPanelStatus"></div>
                    <div class="col-md-6" id="resultBlock"></div>
                </div>

                <form id="guestLoginForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Institute Code</label>
                            <div class="m3-input-box mb-0">
                                <input type="text" id="sccode" name="sccode" class="m3-input-field" placeholder="e.g. 1001" required>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Institute Name</label>
                            <div class="m3-input-box mb-0">
                                <input type="text" id="insname" name="insname" class="m3-input-field" placeholder="Institution Name">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Slot/Shift</label>
                            <div class="m3-input-box mb-0">
                                <select id="unit" name="unit" class="m3-input-field" required></select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Session</label>
                            <div class="m3-input-box mb-0">
                                <select id="session" name="session" class="m3-input-field" required></select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Class</label>
                            <div class="m3-input-box mb-0">
                                <select id="class" name="class" class="m3-input-field" required></select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Section</label>
                            <div class="m3-input-box mb-0">
                                <select id="section" name="section" class="m3-input-field" required></select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem; color: var(--m3-sys-text-med);">Roll</label>
                            <div class="m3-input-box mb-0">
                                <input type="text" id="roll" name="roll" class="m3-input-field" placeholder="Roll" required>
                            </div>
                        </div>

                        <div class="col-md-12" hidden>
                            <label class="form-label">Class Teacher</label>
                            <div class="m3-input-box mb-0">
                                <select id="teacher" name="teacher" class="m3-input-field" required></select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-4" onclick="submitGuestLogin()">Log in</button>
            </div>

        </div>
    </div>
</div>

<?php include_once('footer-plain.php'); ?>

<script>
    function togglePasswordVisibility() {
        const pwdInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (!pwdInput || !eyeIcon) return;

        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            pwdInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    }

    function openAdmissionForm() {
        window.location.href = "admission-form.php";
    }

    function openAdmissionLogin() {
        window.location.href = "admission-login.php";
    }

    function openGuestModal() {
        let modalEl = document.getElementById('guestLoginModal');
        if (modalEl) {
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
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
                    let ad = data.admin_data?.["Panel Settings"]?.["Guest Student"] ?? {};
                    let isActive = (ad.panel_active || '').toLowerCase() === 'yes';

                    $("#guestLoginForm")
                        .find("input, select, textarea, button")
                        .prop("disabled", !isActive);

                    $("#sccode").prop("disabled", false);

                    if (!isActive) {
                        $("#guestPanelStatus").html(
                            '<span class="text-danger">Guest login panel is disabled</span>'
                        );
                    } else {
                        let adHtml = `
                        <div class="fs-6" style="color: var(--m3-sys-text-high);">
                            Panel: Active<br>
                            Access Times: ${ad.access_times ?? 0}<br>
                            Max Stay: ${ad.max_stay_time ?? 0}
                        </div>
                        `;
                        $("#guestPanelStatus").html(adHtml);
                    }
                }
            });
        }, 400);
    });

    $("#class").change(function () {
        let sccode = $("#sccode").val();
        let classname = $(this).val();

        $.post("guest-login/get-section.php", {
            sccode: sccode,
            class: classname
        }, function (data) {
            $("#section").html(data);
        });
    });

    $("#section").change(function () {
        $.post("guest-login/get-teacher.php", {
            sccode: $("#sccode").val(),
            unit: $("#unit").val(),
            session: $("#session").val(),
            class: $("#class").val(),
            section: $(this).val()
        }, function (data) {
            $("#teacher").html(data);
        });
    });

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

    function submitGuestLogin() {
        let formData = $("#guestLoginForm").serialize();
        $.post("guest-login/guest-login.php", formData, function (res) {
            if (res.status === "ok") {
                window.location.href = "index.php";
            } else {
                alert(res.message);
            }
        }, "json");
    }
</script>
</body>
</html>