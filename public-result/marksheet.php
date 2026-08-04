<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/functions.php'; // Assuming you have a functions file for grade calculations

// 1. URL থেকে ডেটা গ্রহণ করা
$sccode = $_GET['sccode'] ?? '';
$slot = $_GET['slot'] ?? '';
$sessionyear = $_GET['sessionyear'] ?? '';
$exam = $_GET['exam'] ?? '';
$classname = $_GET['classname'] ?? '';
$sectionname = $_GET['sectionname'] ?? '';
$rollno = $_GET['rollno'] ?? '';

// 2. ন্যূনতম তথ্য যাচাই করা
function showError($message)
{
    echo "<!DOCTYPE html><html><head><title>Error</title><link rel='stylesheet' href='../assets/vendor/css/core.css' /></head><body>";
    echo "<div class='container'><div class='alert alert-danger mt-5'>" . htmlspecialchars($message) . "</div></div>";
    echo "</body></html>";
    exit;
}

if (empty($sccode) || empty($rollno) || empty($exam) || empty($classname) || empty($sessionyear) || empty($slot)) {
    showError('প্রয়োজনীয় তথ্য পাওয়া যায়নি। অনুগ্রহ করে সঠিক তথ্য দিয়ে আবার চেষ্টা করুন।');
}

// 3. ডেটাবেস থেকে তথ্য আনা
$conn->set_charset("utf8");

// Institute Info
$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? ");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$institute_result = $stmt->get_result();
if ($institute_result->num_rows === 0) {
    showError('Institute not found.');
}
$institute_info = $institute_result->fetch_assoc();
$institute_info['address'] = $institute_info['address'] . ', ' . $institute_info['ps'] . ', ' . $institute_info['dist'];

// Student Info
$stmt = $conn->prepare("
    SELECT s.*, si.stid FROM students s
    JOIN sessioninfo si ON s.stid = si.stid
    WHERE si.sccode = ? AND si.sessionyear = ? AND si.classname = ? AND si.sectionname = ? AND si.rollno = ?
");
$stmt->bind_param("sssss", $sccode, $sessionyear, $classname, $sectionname, $rollno);
$stmt->execute();
$student_result = $stmt->get_result();
if ($student_result->num_rows === 0) {
    showError('Student not found with the provided details.');
}
$student_info = $student_result->fetch_assoc();
$stid = $student_info['stid'];

// Result Info from tabulatingsheet
$stmt = $conn->prepare("
    SELECT * FROM tabulatingsheet 
    WHERE sccode = ? AND sessionyear = ? AND exam = ? AND classname = ? AND sectionname = ? AND stid = ?
");
$stmt->bind_param("ssssss", $sccode, $sessionyear, $exam, $classname, $sectionname, $stid);
$stmt->execute();
$result_res = $stmt->get_result();
if ($result_res->num_rows === 0) {
    showError('Result data not found for this student.');
}
$result_summary = $result_res->fetch_assoc();

// Marks Data
$marks_data = [];
$all_subjects_str = $result_summary['allsubject'];
$subject_codes = explode('.', $all_subjects_str);
// Normalize subject string: replace '/' with '.' then explode by '.'
// This matches the behavior of marksheet-sample.php
$all_subjects_str_normalized = str_replace('/', '.', $all_subjects_str);
$subject_codes_raw = explode('.', $all_subjects_str_normalized);

$processed_subject_codes = []; // To track unique subject codes already processed
$subject_counter = 0; // Counter for 'sub_X' column names



$extend_id = $result_summary['id'] ?? 0; // Assuming 'id' from tabulatingsheet is the foreign key

$extend_stmt = $conn->prepare("SELECT * FROM tabulatingsheetex WHERE tsheet_id = ?");
$extend_stmt->bind_param("i", $extend_id);
$extend_stmt->execute();
$extend_result = $extend_stmt->get_result();
$extend_data = $extend_result->fetch_assoc() ?? [];

$extended_marks = []; // Create a new array for extended subjects
for ($i = 1; $i <= 10; $i++) {
    $col_prefix = 'sub_' . $i;
    // Check if the subject code exists and is valid
    if (!empty($extend_data[$col_prefix])) {
        $extended_marks[] = [
            'subcode' => (int) $extend_data[$col_prefix]+1000,
            'sub' => $extend_data[$col_prefix . '_sub'] ?? 0,
            'obj' => $extend_data[$col_prefix . '_obj'] ?? 0,
            'pra' => $extend_data[$col_prefix . '_pra'] ?? 0,
            'ca' => $extend_data[$col_prefix . '_ca'] ?? 0,
            'total' => $extend_data[$col_prefix . '_total'] ?? 0,
            'gp' => $extend_data[$col_prefix . '_gp'] ?? 0,
            'gl' => $extend_data[$col_prefix . '_gl'] ?? 'F',
            'full' => $extend_data['sub_fm_' . $i] ?? 100,
        ];
    }
}


$sub_count = count($subject_codes_raw);


foreach ($subject_codes_raw as $sub_code) {
    // Trim whitespace and ensure it's a non-empty, numeric subject code
    $sub_code = trim($sub_code);
    if (empty($sub_code) || !is_numeric($sub_code) || $sub_code <= 100) { // Added $sub_code <= 100 check as per sample's logic ($ek > 100)
        continue;
    }

    // Skip if this subject code has already been processed (handles duplicates in allsubject string)
    if (in_array($sub_code, $processed_subject_codes)) {
        continue;
    }
    $processed_subject_codes[] = $sub_code;
    $subject_counter++;

    $subject_details_stmt = $conn->prepare("SELECT subject FROM subjects WHERE subcode = ? and sccategory = 'school'  ");
    $subject_details_stmt->bind_param("i", $sub_code);
    $subject_details_stmt->execute();
    $subject_details_result = $subject_details_stmt->get_result();
    $subject_name = ($subject_details_result->num_rows > 0) ? $subject_details_result->fetch_assoc()['subject'] : 'Unknown Subject';

    $subject_setup_stmt = $conn->prepare("SELECT fullmarks FROM subsetup WHERE sccode = ? AND sessionyear = ? AND classname = ? AND subject = ?");
    $subject_setup_stmt->bind_param("sssi", $sccode, $sessionyear, $classname, $sub_code);
    $subject_setup_stmt->execute();
    $subject_setup_result = $subject_setup_stmt->get_result();
    $full_marks = ($subject_setup_result->num_rows > 0) ? $subject_setup_result->fetch_assoc()['fullmarks'] : 100;

    // Find the column prefix (e.g., 'sub_1', 'sub_2') that holds this sub_code
    $col_prefix = null;
    $keys = array_keys($result_summary, $sub_code);
    if (!empty($keys)) {
        // Find the key that starts with 'sub_' and is not a mark/gp/gl column
        foreach ($keys as $key) {
            if (strpos($key, 'sub_') === 0 && !preg_match('/_(sub|obj|pra|ca|total|gp|gl)$/', $key)) {
                $col_prefix = $key;
                break;
            }
        }
    }
    $total_mark = $result_summary[$col_prefix . '_total'] ?? 0;

    // Skip subjects with 0 marks if they are not compulsory
    if ($total_mark == 0) {
        // Add logic here to check if the subject is optional and can be skipped.
        // For now, we are showing all subjects from 'allsubject' field.
    }

    if ($sub_code > 1000) {
        $key = array_search($sub_code, array_column($extended_marks, 'subcode'));
        if ($key !== false && $col_prefix === null) { // Only process if not found in main sheet
            $marks_data[$sub_code] = [
                'subcode' => $sub_code,
                'subject' => $subject_name,
                'full' => $extended_marks[$key]['full'],
                'obtained' => $extended_marks[$key]['total'],
                'sub' => $extended_marks[$key]['sub'],
                'obj' => $extended_marks[$key]['obj'],
                'pra' => $extended_marks[$key]['pra'],
                'ca' => $extended_marks[$key]['ca'],
                'grade' => $extended_marks[$key]['gl'],
                'gp' => number_format($extended_marks[$key]['gp'], 2),
            ];
            continue; // Move to the next subject in the loop
        }
    }

    if ($sub_code == 1000) {
       $marks_data[$sub_code] = [
                'subcode' =>  '<b>' . $sub_code . '</b>',
                'subject' => $subject_name,
                'full' => 0,
                'obtained' => 0,
                'sub' =>0,
                'obj' => 0,
                'pra' => 0,
                'ca' => 0,
                'grade' => 0,
                'gp' => 0
       ];
    }

    if ($col_prefix === null) {
        continue; // If no column found for this subject code, skip it.
    }

    $marks_data[$sub_code] = [
        'subcode' => $sub_code,
        'subject' => $subject_name,
        'full' => $full_marks,
        'obtained' => $total_mark,
        'sub' => $result_summary[$col_prefix . '_sub'] ?? 0,
        'obj' => $result_summary[$col_prefix . '_obj'] ?? 0,
        'pra' => $result_summary[$col_prefix . '_pra'] ?? 0,
        'ca' => $result_summary[$col_prefix . '_ca'] ?? 0,
        'grade' => $result_summary[$col_prefix . '_gl'] ?? 'N/A',
        'gp' => number_format($result_summary[$col_prefix . '_gp'] ?? 0, 2),
    ];
}





?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Marksheet</title>
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/eimbox.css" />
    <style>
        body {
            background-color: #f4f4f4;
        }

        .marksheet-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .institute-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .marks-table th,
        .marks-table td {
            padding: 4px;
        }

        @media print {
            body {
                background-color: #fff;
            }

            .marksheet-container {
                margin: 0;
                padding: 10px;
                border: none;
                box-shadow: none;
            }

            .btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="marksheet-container">
        <!-- Institute Header -->
        <div class="institute-header">
            <h2><?php echo htmlspecialchars($institute_info['school_name']); ?></h2>
            <p><?php echo htmlspecialchars($institute_info['address']); ?></p>
            <h4>ACADEMIC TRANSCRIPT</h4>
            <h5><?php echo htmlspecialchars($exam); ?> - <?php echo htmlspecialchars($sessionyear); ?></h5>
        </div>

        <!-- Student Information -->
        <table class="table table-sm table-bordered mb-4">
            <tr>
                <th>Student's Name</th>
                <td><?php echo htmlspecialchars($student_info['stnameeng']); ?></td>
                <th>Roll No</th>
                <td><?php echo htmlspecialchars($rollno); ?></td>
            </tr>
            <tr>
                <th>Father's Name</th>
                <td><?php echo htmlspecialchars($student_info['fname']); ?></td>
                <th>Class</th>
                <td><?php echo htmlspecialchars($classname); ?></td>
            </tr>
            <tr>
                <th>Mother's Name</th>
                <td><?php echo htmlspecialchars($student_info['mname']); ?></td>
                <th>Section</th>
                <td><?php echo htmlspecialchars($sectionname); ?></td>
            </tr>
        </table>

        <!-- Marks Table -->
        <table class="table table-bordered table-sm marks-table text-center">
            <thead class="table-light">
                <tr>
                    <th>Subject Name</th>
                    <th>Full<br>Marks</th>
                    <th>Sub</th>
                    <th>Obj</th>
                    <th>Pra</th>
                    <th>CA</th>
                    <th>Total</th>
                    
                    <th colspan="2">Grade</th> <th></th> 
                </tr>
            </thead>
            <tbody>
                <?php
                $flag = false;
                $summary_cell_added = false; // Flag to check if summary cell is added
                 foreach ($marks_data as $mark): ?>
                    <?php 
                    if((int)htmlspecialchars($mark['obtained']) > 0 || $flag == false)
                         { 
                        if((int)htmlspecialchars($mark['subcode']) == 1000) {
                            $colspan = ' colspan="9"';
                            $flag = true;
                            ?>
                            <tr>
                        <td class="text-start fw-bold" <?php echo $colspan; ?>><?php echo htmlspecialchars($mark['subject']); ?></td>
                       
                    </tr>
                    <?php

                        } else {
                            $colspan = '';
                            ?>
<tr>
                        <td class="text-start" <?php echo $colspan; ?>><?php echo $mark['subject']; ?></td>
                        <td><?php echo htmlspecialchars($mark['full']); ?></td>
                        <td><?php echo htmlspecialchars($mark['sub']); ?></td>
                        <td><?php echo htmlspecialchars($mark['obj']); ?></td>
                        <td><?php echo htmlspecialchars($mark['pra']); ?></td>
                        <td><?php echo htmlspecialchars($mark['ca']); ?></td>
                        <td><?php echo htmlspecialchars($mark['obtained']); ?></td>
                        <td><?php echo htmlspecialchars($mark['grade']); ?></td>
                        <td><?php echo htmlspecialchars($mark['gp']); ?></td>
                        <?php if (!$summary_cell_added): ?>
                            <td rowspan="<?php echo $sub_count; ?>" class="align-middle">
                                <div class="p-2" >
                                    <p class="mb-1">Total Marks: <strong><?php echo htmlspecialchars($result_summary['totalmarks']); ?></strong></p>
                                    <p class="mb-1">GPA: <strong><?php echo htmlspecialchars(number_format($result_summary['gpa'], 2)); ?></strong></p>
                                    <p class="mb-1">Grade: <strong><?php echo htmlspecialchars($result_summary['gla']); ?></strong></p>
                                    <?php if($result_summary['gla'] === 'F'): ?>
                                        <p class="mb-1 text-danger">Failed in: <strong><?php echo htmlspecialchars($result_summary['totalfail']); ?></strong> subject(s)</p>
                                    <?php endif; ?>
                                    <hr>
                                    <p class="mb-1">Merit Place: <strong><?php echo htmlspecialchars($result_summary['meritplace']); ?></strong></p>
                                </div>
                            </td>
                            <?php 
                            $summary_cell_added = true; 
                            ?>
                        <?php endif; ?>
                    </tr>

<?php
                        }
                        
                        
                        ?>
                    
                    <?php } ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <!-- Footer can be used for other summary if needed, or removed -->
            </tfoot>
        </table>

        <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-primary" onclick="window.print()">Print Result</button>
        </div>

    </div>
</body>

</html>