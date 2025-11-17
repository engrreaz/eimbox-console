<?php
session_start();
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/core-val.php';


$reg = $_SESSION['student_reg'] ?? null;

$sccode = $_SESSION['scode'] ?? null;

if ($reg) {
    header("Location: admission-dashboard.php");
    exit;
}

if ($sccode == null || $sccode == '') {
    $sccode = $_COOKIE['sccode'] ?? '';
}
echo '/' . $sccode . '/';
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
        header("Location: admission-dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials or not verified.";
    }
}

// ALTER TABLE `registrations` ADD `admit_class` VARCHAR(20) NULL DEFAULT 'Six' AFTER `sccode`;

include_once 'actions/get-sc-data.php';
require_once 'header-plain.php';
?>


<style>
    /* মডাল ব্যাকগ্রাউন্ড */
    .modal {
        display: none;
        /* hide by default */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(241, 5, 5, 0.5);
        /* আংশিক কালো ব্যাকগ্রাউন্ড */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* মডাল কন্টেন্ট */
    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        text-align: center;
    }
</style>
<!-- EIIN Input Modal -->
<div id="eiinModal" class="modal" style="display:none;">
    <div class="modal-content"
        style="max-width:400px;margin:auto;padding:20px;border-radius:10px;box-shadow:0 0 10px #888;background:#fff;">
        <h5 class="text-center m-0 p-0 ">Enter Your EIIN Number</h5>
        <h5 class="text-center m-0 p-0">প্রতিষ্ঠানের ইআইআইএন নম্বর দাও</h5>
        <input type="text" id="eiinInput" maxlength="6" placeholder="Enter 6-digit EIIN"
            style="width:100%;padding:8px;font-size:16px;margin-top:10px;text-align:center;">

        <div class="row mt-4">
            <div class="col">
                <button id=""
                    style="margin-top:10px;width:100%;padding:8px;background:black;color:white;border:none;border-radius:6px;"
                    onclick="closemodal();">
                    Cancel
                </button>


            </div>
            <div class="col">
                <button id="saveEiin"
                    style="margin-top:10px;width:100%;padding:8px;background:red;color:white;border:none;border-radius:6px;">
                    Submit EIIN
                </button>
            </div>
        </div>

    </div>
</div>


<button style="position:fixed; top:20px; right:20px;" class="btn btn-outline-warning" onclick="openmodal();">Change
    EIIN</button>


<div class="container">

    <?php if ($sccode != '') { ?>
        <div class="row">
            <div class="col-12  ">
                <table class="mt-4 mb-6 " style="margin:auto;">
                    <tr>
                        <td><img src="<?php echo BASE_PATH . 'logo/' . $sccode . '.png'; ?> "
                                style="max-width:50px; max-height:50px;" />
                        </td>
                        <td style="width:15px; border-right:5px solid gray;"></td>
                        <td style="width:15px;"></td>
                        <td>
                            <h3 class="m-0 p-0 fw-bold"><?= $scname; ?></h3>
                            <h6 class="m-0 p-0"><?= $address; ?></h6>

                        </td>

                    </tr>
                </table>
            </div>
        </div>

    <?php } ?>

    <div class="card mx-auto" style="max-width:420px; top:25%;">
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

<script>
    // Set cookie
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    // Get cookie
    function getCookie(name) {
        const cname = name + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(cname) === 0) return c.substring(cname.length, c.length);
        }
        return "";
    }

</script>


<script>
    $(document).ready(function () {
        // PHP থেকে পাওয়া sccode
        const phpSccode = "<?php echo $sccode ?? ''; ?>";
        const localSccode = getCookie("sccode");

        if (!phpSccode) {
            if (!localSccode) {
                // মডাল দেখাও
                // $('#eiinModal').fadeIn(200);
                document.getElementById('eiinModal').style.display = 'block';
                document.getElementById('eiinModal').style.opacity = 0;

                setTimeout(() => {
                    document.getElementById('eiinModal').style.transition = "opacity .3s";
                    document.getElementById('eiinModal').style.opacity = 1;
                }, 10);
            } else {
                console.log('Using local sccode:', localSccode);
            }
        }

        // EIIN সংরক্ষণ বাটন
        $('#saveEiin').on('click', function () {
            const eiin = $('#eiinInput').val().trim();
            if (!/^\d{6}$/.test(eiin)) {
                alert('Please enter a valid 6-digit EIIN number.');
                return;
            }

            // লোকাল স্টোরেজে সংরক্ষণ
            setCookie("sccode", eiin, 30); // ৩০ দিন মেয়াদ

            // মডাল বন্ধ ও পেজ রিফ্রেশ
            $('#eiinModal').fadeOut(200, function () {
                location.reload();
            });
        });
    });

    function openmodal() {
        // $('#eiinModal').fadeIn(500);

        document.getElementById('eiinModal').style.display = 'block';
                document.getElementById('eiinModal').style.opacity = 0;

                setTimeout(() => {
                    document.getElementById('eiinModal').style.transition = "opacity .3s";
                    document.getElementById('eiinModal').style.opacity = 1;
                }, 10);
    }

    function closemodal() {
        $('#eiinModal').fadeOut(200, function () {
            location.reload();
        });
    }
</script>
</body>

</html>