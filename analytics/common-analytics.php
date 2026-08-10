<?php 
$dataset_id = $_SESSION['analytics_dataset_id'] ?? 0;
// Fetch area (class/section) information
$areaQuery = "SELECT areaname, subarea, classteacher FROM areas WHERE sccode = ? AND sessionyear = ? AND slot = ? order by idno";
$areaStmt = $conn->prepare($areaQuery);
$areaStmt->bind_param("iss", $sccode, $sessionyear, $slot);
$areaStmt->execute();
$areaResult = $areaStmt->get_result();
$areas_map = [];
while ($row = $areaResult->fetch_assoc()) {
    $key = $row['areaname'] . '-' . $row['subarea'];
    $clssec[] = $row;
}
$areaStmt->close();

// Fetch teacher information
$teacherQuery = "SELECT tname, tid, position, sl FROM teacher WHERE sccode = ?";
$teacherStmt = $conn->prepare($teacherQuery);
$teacherStmt->bind_param("i", $sccode);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();
$teachers_map = [];
while ($row = $teacherResult->fetch_assoc()) {
    $teachers[$row['tid']] = $row;
}
$teacherStmt->close();

// Fetch subject setup information
$subsetupQuery = "SELECT classname, sectionname, subject, fullmarks FROM subsetup WHERE sccode = ? AND sessionyear = ? AND slot = ?";
$subsetupStmt = $conn->prepare($subsetupQuery);
$subsetupStmt->bind_param("iss", $sccode, $sessionyear, $slot);
$subsetupStmt->execute();
$subsetupResult = $subsetupStmt->get_result();
$subsetup_map = [];
while ($row = $subsetupResult->fetch_assoc()) {
    // Group by class, then section, then subcode for easy access
    $subsetup[$row['classname']][$row['sectionname']][$row['subject']] = $row;
}
$subsetupStmt->close();

// Fetch subject list (school-specific and global)
$subjectsQuery = "SELECT subcode, subject FROM subjects WHERE sccode = ? OR sccode = '0'";
$subjectsStmt = $conn->prepare($subjectsQuery);
$subjectsStmt->bind_param("i", $sccode);
$subjectsStmt->execute();
$subjectsResult = $subjectsStmt->get_result();
$subjects_map = [];
while ($row = $subjectsResult->fetch_assoc()) {
    $subjects[$row['subcode']] = $row['subject'];
}
$subjectsStmt->close();
