<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

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
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
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
            height: 297mm;
            margin: 20px auto;
            padding: 20mm 20mm 15mm;
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
        }

        @media print {
            body {
                background: none;
            }

            .testimonial-page {
                margin: 0;
                box-shadow: none;
                border: none;
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
            $student_info_sql = "SELECT * FROM students WHERE sccode='$sccode' AND stid='{$row['stid']}'";
            $student_result = $conn->query($student_info_sql);
            $student_data = $student_result->fetch_assoc();

            // রেন্ডারিং এর জন্য ডেটা অ্যারে তৈরি
            $render_data = array_merge($row, $student_data);

            // টেমপ্লেট রেন্ডার করা
            $testimonial_body = render_testimonial_template($default_template_body, $render_data);
            ?>

            <div class="testimonial-page">
                <img class="watermark-logo" src="<?= institute_logo_path($sccode) ?>">

                <div class="content-wrapper">
                    <div style="text-align:center;">
                        <?php include ('templete/letter-head-01.php'); ?>
                    </div>

                    <table style="width:100%; border:0; margin-top: 20px;">
                        <tr>
                            <td style="height:10mm;" valign="middle">SL: <b><?= htmlspecialchars($row['testslno']) ?></b></td>
                            <td style="text-align:right" valign="middle">Date:
                                <b><?= date('d F, Y', strtotime($row['testdate'])) ?></b></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:center; padding: 20px 0;">
                                <img src="assets/images/testimonials-02.png" width="250" />
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
                            <td colspan="2" style="padding-top: 40px;">
                                <table style="width:100%; border:0;">
                                    <tr>
                                        <td style="width: 20%; vertical-align: bottom;">
                                            <?php $lnk = 'https://eimbox.com/verify/testimonial.php?sl=' . $row['testslno'] . '&id=' . $row['stid']; ?>
                                            <img style="padding: 5px; border: 1px solid #eee;"
                                                src="https://quickchart.io/qr?text=<?= urlencode($lnk) ?>&size=120" />
                                        </td>
                                        <td style="width: 80%; text-align:right; vertical-align: bottom;">
                                            <img src="<?= headmaster_signature_path($sccode) ?>" style="height:40px;"><br>
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

    <!-- Floating Menu -->
    <div class="fab-wrapper">
        <button class="fab-main" onclick="window.print()">
            <i class="bi bi-printer"></i>
        </button>
    </div>

</body>

</html>