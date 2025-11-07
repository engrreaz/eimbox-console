<?php
session_start();
require_once 'core/config.php';
require_once 'core/db.php'; // provides $conn

if (isset($_SESSION['_backup'])) {
    unset($_SESSION['locked']);
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

// Generate randomized password field name once per GET load
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['pw_field_name'] = 'pw_' . bin2hex(random_bytes(6));
}
$pwField = $_SESSION['pw_field_name'] ?? null;

// Lock state
$_SESSION['locked'] = true;
$_SESSION['unlock_attempts'] = $_SESSION['unlock_attempts'] ?? 0;
$maxAttempts = 5;
$lockoutTime = 60; // seconds

$lockedOut = false;
if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
    $lockedOut = true;
}

$redi = $_POST['page'] ?? 'index.php';
// ===== AJAX unlock process =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_unlock'])) {
    header('Content-Type: application/json');

    $pwFieldSess = $_SESSION['pw_field_name'] ?? null;
    if (!$pwFieldSess || !isset($_POST[$pwFieldSess])) {
        echo json_encode(['status' => 'error', 'msg' => 'Password not received properly.']);
        exit;
    }

    $password = trim($_POST[$pwFieldSess]);
    $email = $_SESSION['user_email'] ?? '';




    if (!$email) {
        echo json_encode(['status' => 'error', 'msg' => 'No user session']);
        exit;
    }

    $stmt = $conn->prepare("SELECT password_hash FROM usersapp WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($_SESSION['locked']);
        $_SESSION['unlock_attempts'] = 0;
        echo json_encode(['status' => 'ok']);
    } else {
        $_SESSION['unlock_attempts']++;
        if ($_SESSION['unlock_attempts'] >= $maxAttempts) {
            $_SESSION['lockout_until'] = time() + $lockoutTime;
        }
        echo json_encode(['status' => 'error', 'msg' => 'Incorrect password!']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Screen Locked</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background: #111;
        }

        #lockModal .modal-content {
            background: #1c1c1c;
            color: white;
            border-radius: 12px;
            text-align: center;
        }

        #lockModal input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: none;
            font-size: 16px;
        }

        #errorMsg {
            min-height: 24px;
            color: #ff6b6b;
        }
    </style>
</head>

<body>

    <div class="modal show d-block" id="lockModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header justify-content-center border-0">
                    <img src="assets/images/logo.png" alt="Logo" style="max-width:80px;">
                </div>
                <div class="modal-body">
                    <h3 class="mb-3">🔒 Screen Locked</h3>
                    <?php echo $redi; ?>
                    <form id="unlockForm" autocomplete="off" novalidate>
                        <input type="text" style="display:none" autocomplete="username">
                        <input type="password" style="display:none" autocomplete="new-password">

                        <input type="text" id="uiPassword" class="text-center" placeholder="Click to enter password"
                            autocomplete="off" inputmode="text" />

                        <input type="hidden" id="realPwField"
                            name="<?php echo htmlspecialchars($pwField, ENT_QUOTES); ?>" />

                        <button type="submit" class="btn btn-success w-100">Unlock</button>
                    </form>

                    <div id="errorMsg">
                        <?php if ($lockedOut)
                            echo "Too many attempts! Try again in $lockoutTime seconds."; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const ui = document.getElementById('uiPassword');
            const real = document.getElementById('realPwField');
            const form = document.getElementById('unlockForm');
            const btn = form.querySelector('button');
            const error = document.getElementById('errorMsg');

            ui.setAttribute('autocomplete', 'off');
            ui.setAttribute('autocorrect', 'off');
            ui.setAttribute('autocapitalize', 'off');
            ui.setAttribute('spellcheck', 'false');

            // When clicked, convert to real password input
            function activateInput() {
                const p = document.createElement('input');
                p.type = 'password';
                p.id = ui.id;
                p.className = ui.className;
                p.placeholder = ui.placeholder;
                p.autocomplete = 'new-password';
                p.inputMode = 'text';
                ui.parentNode.replaceChild(p, ui);

                ['paste', 'drop', 'dragover', 'dragenter'].forEach(evt =>
                    p.addEventListener(evt, e => e.preventDefault())
                );

                window.__pw_ui = p;
                p.focus();
            }

            ui.addEventListener('mousedown', e => { e.preventDefault(); activateInput(); });
            ui.addEventListener('focus', e => { if (!window.__pw_ui) activateInput(); });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const p = document.getElementById('uiPassword');
                const val = p ? p.value.trim() : '';
                real.value = val;

                btn.disabled = true;
                const fd = new FormData(form);
                fd.append('ajax_unlock', '1');

                fetch('lock.php', { method: 'POST', credentials: 'same-origin', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'ok') {
                            localStorage.setItem('screenLocked', '0');
                            const params = new URLSearchParams(window.location.search);
                            const page = params.get('page') ?? 'index.php';
                            window.location.href = page;
                        } else {
                            error.textContent = data.msg || 'Unlock failed';
                            btn.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        error.textContent = 'Network error';
                        btn.disabled = false;
                    });
            });
        })();
    </script>
</body>

</html>