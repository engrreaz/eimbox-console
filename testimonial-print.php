<?php
session_start(); // Add this line to start the session
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
require_once 'core/functions.php';

// আপনার ডাটাবেস কানেকশন এবং অন্যান্য গ্লোবাল ভ্যারিয়েবল

// =================================================================
// 🔹 টেমপ্লেট রেন্ডারিং ফাংশন
// এই ফাংশনটি প্লেসহোল্ডার পরিবর্তন করে চূড়ান্ত টেক্সট তৈরি করবে।
// =================================================================
function render_testimonial_template($template_body, $data)
{
    $pronoun_he_she = (in_array($data['gender'], ['Male', 'Boy'])) ? 'He' : 'She';

    $placeholders = [
        '[[STUDENT_NAME]]' => $data['stnameeng'] ?? '',
        '[[STUDENT_NAME_BN]]' => $data['stnameben'] ?? '',
        '[[SON_DAUGHTER]]' => ($data['gender'] === 'Male' || $data['gender'] === 'Boy') ? 'S/O' : 'D/O',
        '[[FATHER_NAME]]' => $data['fname'] ?? '',
        '[[MOTHER_NAME]]' => $data['mname'] ?? '',
        '[[VILLAGE]]' => $data['previll'] ?? '',
        '[[POST_OFFICE]]' => $data['prepo'] ?? '',
        '[[PS]]' => $data['preps'] ?? '',
        '[[DISTRICT]]' => $data['predist'] ?? '',
        '[[PRONOUN_HE_SHE]]' => $pronoun_he_she,
        '[[PRONOUN_HE_SHE_LOWER]]' => strtolower($pronoun_he_she),
        '[[PRONOUN_HIS_HER]]' => ($pronoun_he_she === 'He') ? 'His' : 'Her',
        '[[EXAM_NAME]]' => $data['pubexam'] ?? '',
        '[[PASSING_YEAR]]' => $data['passyear'] ?? '',
        '[[BOARD_NAME]]' => 'Comilla', // এটি ডাইনামিক করা যেতে পারে
        '[[ROLL_NO]]' => $data['rollno'] ?? '',
        '[[REGD_NO]]' => $data['regdno'] ?? '',
        '[[SESSION]]' => $data['session'] ?? '',
        '[[GROUP]]' => $data['groupsection'] ?? '',
        '[[GPA]]' => $data['gpa'] ?? '',
        '[[GRADE]]' => $data['grade'] ?? '',
        '[[DOB]]' => !empty($data['dob']) ? date('d F, Y', strtotime($data['dob'])) : '',
    ];

    $rendered_text = str_replace(array_keys($placeholders), array_values($placeholders), $template_body);
    return nl2br($rendered_text); // Newlines কে <br> ট্যাগ দিয়ে পরিবর্তন করা
}

// =================================================================
// 🔹 ইনপুট এবং ডেটাবেস কুয়েরি
// =================================================================

$examname = $_GET['exam'] ?? 'SSC';
$passingyear = $_GET['year'] ?? date('Y');
$groupsection = $_GET['sec'] ?? '';
$stids_param = $_GET['stids'] ?? $_GET['stid'] ?? '0';

// ডিফল্ট টেমপ্লেট ডেটাবেস থেকে লোড করা
$template_query = $conn->query("SELECT template_body FROM testimonial_templates WHERE is_default = 1 AND sccode IN (0, '$sccode') ORDER BY sccode DESC LIMIT 1");
$default_template_body = $template_query->fetch_assoc()['template_body'] ?? 'Default template not found in database.';

$stids = array_filter(array_map('intval', explode(',', $stids_param)));

if (empty($stids)) {
    // যদি কোনো নির্দিষ্ট শিক্ষার্থী না থাকে, তাহলে সেকশন ও বছর অনুযায়ী সব ইস্যু করা টেস্টিমোনিয়াল দেখাও
    $sql = "SELECT * FROM testimonial WHERE sccode='$sccode' AND pubexam='$examname' AND passyear='$passingyear' AND groupsection='$groupsection' AND testslno!=''";
} else {
    // নির্দিষ্ট শিক্ষার্থীদের জন্য
    $in_clause = implode(',', $stids);
    $sql = "SELECT * FROM testimonial WHERE sccode='$sccode' AND stid IN ($in_clause) AND testslno!=''";
}

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Testimonial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f0f2f5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .testimonial-page {
            background: white;
            width: 210mm;
            min-height: 297mm; /* Use min-height to avoid content overflow issues */
            margin: 20px auto; 
            padding: 25.4mm; /* 1 inch margin */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: always;
            box-sizing: border-box;
        }

        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 1;
            width: 50%;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        .fab-wrapper {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
        }

        .fab-main {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: #696cff;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.2s;
        }
        .image-select-label {
            cursor: pointer;
            border: 2px solid transparent;
        }

        #settings-panel {
            transition: transform .3s ease-in-out;
        }

        @media print {
            body {
                background: none;
            }

            .testimonial-page {
                margin: 0;
                box-shadow: none;
                min-height: 0;
                border: none;
            }

            #settings-panel {
                display: none !important;
            }

            .fab-wrapper {
                display: none;
            }
        }
    </style>
</head>

<body>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // শিক্ষার্থীর তথ্য সংগ্রহ
            $student_stmt = $conn->prepare("SELECT * FROM students WHERE sccode = ? AND stid = ?");
            if ($student_stmt) {
                $student_stmt->bind_param("ss", $sccode, $row['stid']);
                $student_stmt->execute();
                $student_data = $student_stmt->get_result()->fetch_assoc();
                $student_stmt->close();
            }
            $student_data = $student_data ?? []; // যদি কোনো কারণে ডেটা না পাওয়া যায়

            // রেন্ডারিং এর জন্য ডেটা অ্যারে তৈরি
            $render_data = array_merge($row, $student_data);

            // টেমপ্লেট রেন্ডার করা
            $testimonial_body = render_testimonial_template($default_template_body, $render_data);
            ?>

            <div class="testimonial-page">
                <img class="watermark-logo" src="<?= institute_logo($sccode) ?>">
 
                <div class="content-wrapper">
                    <div style="text-align:center;" class="letter-head">
                        <?php include ('templete/letter-head-01.php'); ?>
                    </div>

                    <table style="width:100%; border:0; margin-top: 20px;" class="main-table">
                        <tr>
                            <td style="height:10mm;" valign="middle">SL: <b><?= htmlspecialchars($row['testslno']) ?></b></td>
                            <td style="text-align:right" valign="middle">Date:
                                <b><?= date('d F, Y', strtotime($row['testdate'])) ?></b></td>
                        </tr>
                        <tr style="vertical-align: middle;">
                            <td colspan="2" style="padding: 20px 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div style="width: 120px;"></div> <!-- Left Spacer -->
                                    <div class="testimonial-title-container" style="text-align:center;">
                                        <img src="assets/testimonial/testimonials-02.png" width="250" class="testimonial-title-img" />
                                    </div>
                                    <img src="https://eimbox.com/students/<?= $row['stid'] ?>.jpg" class="student-photo" style="width: 100px; height: 120px; border: 1px solid #ccc; object-fit: cover;" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="line-height:1.8; font-size:16px; text-align:justify;" valign="top">
                                <p class="editable dynamic-text">
                                    <?= $testimonial_body ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 40px;" class="footer-section">
                                <table style="width:100%; border:0;">
                                    <tr>
                                        <td style="width: 20%; vertical-align: bottom;">
                                            <?php $lnk = 'https://eimbox.com/verify/testimonial.php?sl=' . $row['testslno'] . '&id=' . $row['stid']; ?>
                                            <img style="padding: 5px; border: 1px solid #eee;"
                                                src="https://quickchart.io/qr?text=<?= urlencode($lnk) ?>&size=120" />
                                        </td>
                                        <td style="width: 80%; text-align:right; vertical-align: bottom;">
                                            <img src="https://eimbox.com/sign/<?= $sccode ?>.png" style="height:40px;" class="head-signature-img"><br>
                                            <b><?= $headname; ?></b><br>
                                            <?= $headtitle; ?><br>
                                            <?= $scname; ?><br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php
        } // end while
    } else {
        echo "<div class='alert alert-danger m-5'>No issued testimonials found for the selected criteria.</div>";
    }
    ?>

    <!-- Floating Action Buttons -->
    <div class="fab-wrapper">
        <button class="fab-main mb-2" onclick="toggleSettingsPanel()">
            <i class="bi bi-gear"></i>
        </button>
        <button class="fab-main" onclick="window.print()">
            <i class="bi bi-printer"></i>
        </button>
    </div>

    <!-- Settings Panel -->
    <div class="card" id="settings-panel" style="position: fixed; top: 0; right: -350px; width: 350px; height: 100vh; z-index: 1050; background-color: #f7f7ff; border-radius: 0; box-shadow: -3px 0 10px rgba(0,0,0,0.1); transition: right 0.3s ease-in-out;">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: #f0f0ff;">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-gear-wide-connected me-2"></i>Customize Design</h6>
            <button type="button" class="btn-close" onclick="document.getElementById('settings-panel').style.display='none'" aria-label="Close"></button>
        </div>
        <div class="card-body p-3" style="overflow-y: auto;">
            <div class="mb-3">
                <label for="font-size-slider" class="form-label small">Font Size</label>
                <input type="range" class="form-range" id="font-size-slider" min="12" max="20" step="1" value="16">
            </div>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="toggle-letterhead" checked>
                <label class="form-check-label small" for="toggle-letterhead">Show Letter Head</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="toggle-signature" checked>
                <label class="form-check-label small" for="toggle-signature">Show Head's Signature</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="toggle-footer" checked>
                <label class="form-check-label small" for="toggle-footer">Show Footer Section</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="toggle-student-photo" checked>
                <label class="form-check-label small" for="toggle-student-photo">Show Student's Photo</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="toggle-watermark" checked>
                <label class="form-check-label small" for="toggle-watermark">Show Watermark Logo</label>
            </div>
            <hr>
            <!-- Title Image Selector -->
            <div class="mb-3">
                <label class="form-label small">Choose Title Style</label>
                <div id="title-style-selector" class="d-flex flex-wrap gap-2">
                    <?php
                    $title_images = glob('assets/testimonial/testimonials-*.png');
                    foreach ($title_images as $img) {
                        $filename = basename($img);
                        echo "
                        <label class='image-select-label'>
                            <input type='radio' name='title_style' value='{$filename}' class='d-none'>
                            <img src='{$img}' height='30' class='rounded border p-1 bg-white'>
                        </label>
                        ";
                    }
                    ?>
                </div>
            </div>

            <!-- Background Image Selector -->
            <div class="mb-3">
                <label class="form-label small">Page Background</label>
                <div id="background-selector" class="d-flex flex-wrap gap-2">
                    <?php
                    $bg_images = glob('assets/testimonial/testimonial-background-*.{png,jpg,jpeg}', GLOB_BRACE);
                    foreach ($bg_images as $img) {
                        $filename = basename($img);
                        echo "
                        <label class='image-select-label'>
                            <input type='radio' name='background_style' value='{$filename}' class='d-none'>
                            <img src='{$img}' width='50' height='50' class='rounded border p-1 bg-white' style='object-fit: cover;'>
                        </label>";
                    }
                    ?>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button class="btn btn-primary" onclick="saveAndReload()">Save & Apply</button>
                <button class="btn btn-outline-secondary" onclick="resetSettings()">Reset to Default</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSettingsPanel() {
            const panel = document.getElementById('settings-panel');
            if (panel.style.right === '0px') {
                panel.style.right = '-350px';
            } else {
                panel.style.right = '0px';
            }
        }


        // Helper functions for cookies
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            document.cookie = name + '=; Max-Age=-99999999;';
        }

        // কুকি থেকে সেটিংস লোড করার ফাংশন
        function loadSettings() {
            const settings = JSON.parse(getCookie('testimonialSettings') || '{}');
            applySettings(settings);

            // UI কন্ট্রোলগুলো আপডেট করা
            document.getElementById('font-size-slider').value = settings.fontSize || 16;
            document.getElementById('toggle-letterhead').checked = settings.showLetterhead !== false; 
            document.getElementById('toggle-signature').checked = settings.showSignature !== false;
            document.getElementById('toggle-footer').checked = settings.showFooter !== false;
            document.getElementById('toggle-student-photo').checked = settings.showStudentPhoto !== false;
            document.getElementById('toggle-watermark').checked = settings.showWatermark !== false;
            
            // Title and Background style selection
            const titleStyle = settings.titleStyle || 'testimonials-00.png';
            const backgroundStyle = settings.backgroundStyle || 'testimonial-background-00.png';
            
            document.querySelector(`input[name="title_style"][value="${titleStyle}"]`).checked = true;
            document.querySelector(`input[name="background_style"][value="${backgroundStyle}"]`).checked = true;

        }

        // DOM-এ সেটিং প্রয়োগ করার ফাংশন
        function applySettings(settings) {
            // Font size & Border
            document.querySelectorAll('.dynamic-text').forEach(el => el.style.fontSize = (settings.fontSize || 16) + 'px');
            document.querySelectorAll('.letter-head').forEach(el => el.style.display = settings.showLetterhead === false ? 'none' : ''); 
            document.querySelectorAll('.head-signature-img').forEach(el => el.style.display = settings.showSignature === false ? 'none' : '');
            document.querySelectorAll('.footer-section').forEach(el => el.style.display = settings.showFooter === false ? 'none' : '');

            // Student Photo
            document.querySelectorAll('.student-photo').forEach(el => el.style.visibility = settings.showStudentPhoto === false ? 'hidden' : 'visible');

            // Watermark
            document.querySelectorAll('.watermark-logo').forEach(el => el.style.display = settings.showWatermark === false ? 'none' : '');

            // Title Image & container
            const titleStyle = settings.titleStyle || 'testimonials-00.png';
            document.querySelectorAll('.testimonial-title-img').forEach(el => el.src = `assets/testimonial/${titleStyle}`);
            
            // Background Image
            const backgroundStyle = settings.backgroundStyle || 'testimonial-background-00.png';
            if (backgroundStyle !== 'none') {
                document.querySelectorAll('.testimonial-page').forEach(el => {
                    el.style.backgroundImage = `url('assets/testimonial/${backgroundStyle}')`;
                });
            }
        }

        // কুকিতে সেটিংস সেভ করে পেজ রিলোড করার ফাংশন
        function saveAndReload() {
            const settings = {
                fontSize: document.getElementById('font-size-slider').value,
                showLetterhead: document.getElementById('toggle-letterhead').checked,
                showSignature: document.getElementById('toggle-signature').checked,
                showFooter: document.getElementById('toggle-footer').checked,
                showStudentPhoto: document.getElementById('toggle-student-photo').checked,
                showWatermark: document.getElementById('toggle-watermark').checked,
                titleStyle: document.querySelector('input[name="title_style"]:checked')?.value,
                backgroundStyle: document.querySelector('input[name="background_style"]:checked')?.value,
            };
            setCookie('testimonialSettings', JSON.stringify(settings), 30);
            location.reload(); // রিলোড করে নতুন সেটিংস প্রয়োগ করা
        }

        // সেটিংস রিসেট করার ফাংশন
        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to default?')) {
                deleteCookie('testimonialSettings');
                location.reload();
            }
        }

        // পেজ লোড হলে সেটিংস লোড করা
        document.addEventListener('DOMContentLoaded', function() {
            loadSettings();

            function highlightSelection(radio) {
                 // Remove active class from all labels in the same group
                 document.querySelectorAll(`input[name="${radio.name}"]`).forEach(r => {
                    r.parentElement.style.borderColor = 'transparent';
                });
                // Add active class to the selected one
                radio.parentElement.style.borderColor = '#696cff';
            }

            // Image selector logic
            document.querySelectorAll('input[name="title_style"], input[name="background_style"]').forEach(radio => {
                // Highlight on change
                radio.addEventListener('change', () => highlightSelection(radio));
                // Highlight on load if checked
                if(radio.checked) highlightSelection(radio);
            });
        });
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>