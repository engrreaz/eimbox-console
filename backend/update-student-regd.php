<?php
require_once '../core/init.php';
header('Content-Type: application/json');

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

    // Basic validation
    if (empty($stid) || empty($rollno) || empty($regdno)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Error: Missing required fields (Student ID, Board Roll, Registration No)."]);
        exit;
    }

    $gla = null; // Initialize grade letter

    // Calculate GLA based on GPA if GPA is provided and numeric
    if (!empty($gpa) && is_numeric($gpa)) {
        // Query the 'gpa' table to find the corresponding grade letter (gl)
        // It looks for a grade point (gp) that is less than or equal to the provided GPA.
        // Orders by gp descending to get the highest matching grade.
        // Considers school-specific (sccode) or general (sccode = '0') GPA scales.
        $stmt_gla = $conn->prepare("SELECT gl FROM gpa WHERE (sccode = ? OR sccode = '0') AND gp <= ? ORDER BY gp DESC LIMIT 1");
        if ($stmt_gla) {
            $stmt_gla->bind_param("ss", $sccode, $gpa); // sccode and gpa are treated as strings for binding
            $stmt_gla->execute();
            $res_gla = $stmt_gla->get_result();
            if ($res_gla && $res_gla->num_rows > 0) {
                $gla = $res_gla->fetch_assoc()['gl'];
            }
            $stmt_gla->close();
        } else {
            error_log("Failed to prepare GLA statement: " . $conn->error);
        }
    }

    // Prepare the update statement
    $stmt = $conn->prepare("
        UPDATE students 
        SET 
            stnameeng = ?, stnameben = ?, fname = ?, mname = ?, 
            rollno = ?, regdno = ?, gpa = ?, gla = ?, sscpassyear = ?
        WHERE 
            stid = ? AND sccode = ? 
    ");

echo $stmt;
echo "\n";
echo $stnameeng . '/' . $stnameben . '/' . $fname . '/' . $mname . '/' . $rollno . '/' . $regdno . '/' . $gpa . '/' . $gla . '/' . $passing_year . '/' . $stid . '/' . $sccode;

    // Bind parameters: 9 for SET, 2 for WHERE. All treated as strings for simplicity. sscroll is used for board roll.
    $stmt->bind_param("sssssssssss", $stnameeng, $stnameben, $fname, $mname, $rollno, $regdno, $gpa, $gla, $passing_year, $stid, $sccode);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "gla" => $gla]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error updating student: " . $stmt->error]);
    }
    $stmt->close();
}
?>