<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // Start output buffering
require_once '../core/config.php'; 
require_once '../core/db.php'; 
require_once '../core/global_values.php'; 
header('Content-Type: application/json');

$sccode = $_SESSION['sccode'] ?? $sccode ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $stid = $_POST['stid'] ?? null;
    $stnameeng = trim($_POST['stnameeng'] ?? '');
    $stnameben = trim($_POST['stnameben'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $rollno = trim($_POST['rollno'] ?? ''); // This is actually Board Roll
    $regdno = trim($_POST['regdno'] ?? '');
    $gpa = trim($_POST['gpa'] ?? '');

    // New field for passing year
    $passing_year = trim($_POST['passing_year'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    // Basic validation
    if (empty($stid) || empty($rollno) || empty($regdno)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Error: Missing required fields (Student ID, Board Roll, Registration No)."]);
        exit;
    }

    $gla = null; // Initialize grade letter
    $numeric_gpa = ($gpa !== '' && is_numeric($gpa)) ? floatval($gpa) : null;

    // Calculate GLA based on GPA if GPA is provided and numeric
    if ($numeric_gpa !== null) {
        if ($numeric_gpa <= 0) {
            $gla = 'F';
        } else {
            // Query the 'gpa' table to find the corresponding grade letter (gl)
            $stmt_gla = $conn->prepare("
                SELECT gl FROM gpa 
                WHERE (sccode = ? OR sccode = 0 OR sccode = '0' OR sccode IS NULL) 
                  AND gp <= ? 
                ORDER BY 
                  (CASE WHEN sccode = ? THEN 1 WHEN sccode = 0 OR sccode = '0' THEN 2 ELSE 3 END) ASC,
                  gp DESC, 
                  id ASC 
                LIMIT 1
            ");
            error_log(""
                SELECT gl FROM gpa 
                WHERE (sccode = ? OR sccode = 0 OR sccode = '0' OR sccode IS NULL) 
                  AND gp <= ? 
                ORDER BY 
                  (CASE WHEN sccode = ? THEN 1 WHEN sccode = 0 OR sccode = '0' THEN 2 ELSE 3 END) ASC,
                  gp DESC, 
                  id ASC 
                LIMIT 1
            "");
            error_log($sscode . '/' . $numeric_gpa . '/'    . $sccode);
            if ($stmt_gla) {
                $stmt_gla->bind_param("sds", $sccode, $numeric_gpa, $sccode);
                $stmt_gla->execute();
                $res_gla = $stmt_gla->get_result();
                if ($res_gla && $res_gla->num_rows > 0) {
                    $row_gla = $res_gla->fetch_assoc();
                    $gla = $row_gla['gl'] ?? null;
                }
                $stmt_gla->close();
            } else {
                error_log("Failed to prepare GLA statement: " . $conn->error);
            }

            // Reliable standard fallback if table entry is missing or empty
            if (empty($gla)) {
                if ($numeric_gpa >= 5.0) {
                    $gla = 'A+';
                } elseif ($numeric_gpa >= 4.0) {
                    $gla = 'A';
                } elseif ($numeric_gpa >= 3.5) {
                    $gla = 'A-';
                } elseif ($numeric_gpa >= 3.0) {
                    $gla = 'B';
                } elseif ($numeric_gpa >= 2.0) {
                    $gla = 'C';
                } elseif ($numeric_gpa >= 1.0) {
                    $gla = 'D';
                } else {
                    $gla = 'F';
                }
            }
        }
    }

    error_log("Updating student: " . $stid . " with GPA: " . $gpa . " and GLA: " . $gla);
    // Prepare the update statement
    $stmt = $conn->prepare("
        UPDATE students  
        SET 
            stnameeng = ?, stnameben = ?, fname = ?, mname = ?, 
            rollno = ?, regdno = ?, gpa = ?, gla = ?, sscpassyear = ?, gender = ?, dob = ?
        WHERE 
            stid = ? AND sccode = ? 
    ");

    // Bind parameters: 11 for SET, 2 for WHERE. All treated as strings for simplicity.
    $stmt->bind_param("sssssssssssss", $stnameeng, $stnameben, $fname, $mname, $rollno, $regdno, $gpa, $gla, $passing_year, $gender, $dob, $stid, $sccode);

    if ($stmt->execute()) {
        // Synchronize existing testimonial record if already issued
        $check_test = $conn->prepare("SELECT id FROM testimonial WHERE stid = ? AND sccode = ?");
        if ($check_test) {
            $check_test->bind_param("ss", $stid, $sccode);
            $check_test->execute();
            $res_test = $check_test->get_result();
            if ($res_test && $res_test->num_rows > 0) {
                $upd_test = $conn->prepare("UPDATE testimonial SET rollno = ?, regdno = ?, gpa = ?, grade = ?, passyear = ?, modifieddate = NOW() WHERE stid = ? AND sccode = ?");
                if ($upd_test) {
                    $upd_test->bind_param("ssdssss", $rollno, $regdno, $numeric_gpa, $gla, $passing_year, $stid, $sccode);
                    $upd_test->execute();
                    $upd_test->close();
                }
            }
            $check_test->close();
        }

        ob_end_clean(); // Clean (discard) the buffer
        header('Content-Type: application/json'); // Re-set header just in case

        echo json_encode(["status" => "success", "gla" => $gla]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error updating student: " . $stmt->error]);
    }
    $stmt->close();
}
?>