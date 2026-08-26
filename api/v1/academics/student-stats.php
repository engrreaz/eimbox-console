<?php
/**
 * EIMBox REST API — Multi-Dimensional Student Demographics & Statistical Analytics
 * Endpoint: /api/v1/academics/student-stats.php
 * Tables: students (students.sql), sessioninfo (sessioninfo.sql)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = api_authenticate_request();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code & Parameters
$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
$classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
$slot = trim($_GET['slot'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Aggregate Multi-Dimensional Demographic Metrics

// Dimension 1: Total Enrollment KPI
$kpiSql = "SELECT COUNT(*) as total_enrolled,
                  SUM(CASE WHEN LOWER(s.gender) LIKE 'f%' THEN 1 ELSE 0 END) as total_girls,
                  SUM(CASE WHEN LOWER(s.gender) LIKE 'm%' THEN 1 ELSE 0 END) as total_boys,
                  SUM(CASE WHEN si.rate < 100 THEN 1 ELSE 0 END) as total_waivers,
                  SUM(CASE WHEN s.disables IS NOT NULL AND s.disables != '0' AND s.disables != '' THEN 1 ELSE 0 END) as special_needs
           FROM sessioninfo si
           JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
           WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')";

$stmtKpi = $conn->prepare($kpiSql);
$stmtKpi->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtKpi->execute();
$kpi = $stmtKpi->get_result()->fetch_assoc();
$stmtKpi->close();

// Dimension 2: Class & Gender Distribution
$clsSql = "SELECT si.classname,
                  COUNT(*) as total,
                  SUM(CASE WHEN LOWER(s.gender) LIKE 'f%' THEN 1 ELSE 0 END) as girls,
                  SUM(CASE WHEN LOWER(s.gender) LIKE 'm%' THEN 1 ELSE 0 END) as boys
           FROM sessioninfo si
           JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
           WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')
           GROUP BY si.classname
           ORDER BY si.classname ASC";

$stmtCls = $conn->prepare($clsSql);
$stmtCls->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtCls->execute();
$resCls = $stmtCls->get_result();
$classDist = [];
while ($r = $resCls->fetch_assoc()) {
    $classDist[] = $r;
}
$stmtCls->close();

// Dimension 3: Religion Distribution
$relSql = "SELECT COALESCE(NULLIF(TRIM(s.religion), ''), 'Not Specified') as religion,
                  COUNT(*) as count
           FROM sessioninfo si
           JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
           WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')
           GROUP BY s.religion
           ORDER BY count DESC";

$stmtRel = $conn->prepare($relSql);
$stmtRel->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtRel->execute();
$resRel = $stmtRel->get_result();
$religionDist = [];
while ($r = $resRel->fetch_assoc()) {
    $religionDist[] = $r;
}
$stmtRel->close();

// Dimension 4: Catchment Village Distribution (Top 15)
$vilSql = "SELECT COALESCE(NULLIF(TRIM(s.previll), ''), 'Unassigned') as village,
                  COUNT(*) as count
           FROM sessioninfo si
           JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
           WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')
           GROUP BY s.previll
           ORDER BY count DESC
           LIMIT 15";

$stmtVil = $conn->prepare($vilSql);
$stmtVil->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtVil->execute();
$resVil = $stmtVil->get_result();
$villageDist = [];
while ($r = $resVil->fetch_assoc()) {
    $villageDist[] = $r;
}
$stmtVil->close();

// Dimension 5: Blood Group Distribution
$bgSql = "SELECT COALESCE(NULLIF(TRIM(s.bgroup), ''), 'Unknown') as blood_group,
                 COUNT(*) as count
          FROM sessioninfo si
          JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
          WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')
          GROUP BY s.bgroup
          ORDER BY count DESC";

$stmtBg = $conn->prepare($bgSql);
$stmtBg->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtBg->execute();
$resBg = $stmtBg->get_result();
$bloodGroupDist = [];
while ($r = $resBg->fetch_assoc()) {
    $bloodGroupDist[] = $r;
}
$stmtBg->close();

// Dimension 6: Age Groups Bracket
$ageSql = "SELECT 
             CASE 
               WHEN s.dob IS NULL OR s.dob = '' OR s.dob = '0000-00-00' THEN 'Unknown'
               WHEN TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) < 10 THEN 'Below 10 Yrs'
               WHEN TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) BETWEEN 10 AND 12 THEN '10 - 12 Yrs'
               WHEN TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) BETWEEN 13 AND 15 THEN '13 - 15 Yrs'
               WHEN TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) BETWEEN 16 AND 18 THEN '16 - 18 Yrs'
               ELSE 'Above 18 Yrs'
             END as age_bracket,
             COUNT(*) as count
           FROM sessioninfo si
           JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
           WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')
           GROUP BY age_bracket
           ORDER BY count DESC";

$stmtAge = $conn->prepare($ageSql);
$stmtAge->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtAge->execute();
$resAge = $stmtAge->get_result();
$ageDist = [];
while ($r = $resAge->fetch_assoc()) {
    $ageDist[] = $r;
}
$stmtAge->close();

// Dimension 7: Parental Status (Orphan Tracking)
$orphanSql = "SELECT 
                SUM(CASE WHEN s.fdeath = 1 AND s.mdeath = 1 THEN 1 ELSE 0 END) as both_deceased,
                SUM(CASE WHEN s.fdeath = 1 AND (s.mdeath = 0 OR s.mdeath IS NULL) THEN 1 ELSE 0 END) as father_deceased,
                SUM(CASE WHEN s.mdeath = 1 AND (s.fdeath = 0 OR s.fdeath IS NULL) THEN 1 ELSE 0 END) as mother_deceased,
                SUM(CASE WHEN (s.fdeath = 0 OR s.fdeath IS NULL) AND (s.mdeath = 0 OR s.mdeath IS NULL) THEN 1 ELSE 0 END) as both_alive
              FROM sessioninfo si
              JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
              WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')";

$stmtOrph = $conn->prepare($orphanSql);
$stmtOrph->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtOrph->execute();
$orphanDist = $stmtOrph->get_result()->fetch_assoc();
$stmtOrph->close();

// Dimension 8: Fee Waiver Tiers
$waiverSql = "SELECT 
                SUM(CASE WHEN si.rate = 100 THEN 1 ELSE 0 END) as full_pay,
                SUM(CASE WHEN si.rate = 0 THEN 1 ELSE 0 END) as full_free_100pct,
                SUM(CASE WHEN si.rate = 25 THEN 1 ELSE 0 END) as concession_75pct,
                SUM(CASE WHEN si.rate = 50 THEN 1 ELSE 0 END) as concession_50pct,
                SUM(CASE WHEN si.rate = 75 THEN 1 ELSE 0 END) as concession_25pct,
                SUM(CASE WHEN si.rate NOT IN (0, 25, 50, 75, 100) AND si.rate < 100 THEN 1 ELSE 0 END) as custom_waiver
              FROM sessioninfo si
              WHERE si.sccode = ? AND (si.sessionyear = ? OR ? = '')";

$stmtWaiver = $conn->prepare($waiverSql);
$stmtWaiver->bind_param("iss", $sccode, $sessionyear, $sessionyear);
$stmtWaiver->execute();
$waiverDist = $stmtWaiver->get_result()->fetch_assoc();
$stmtWaiver->close();

// Send Aggregated Analytical Payload
api_response('success', 'Student multi-dimensional statistics generated.', [
    'sccode' => $sccode,
    'sessionyear' => $sessionyear,
    'kpis' => [
        'total_enrolled' => intval($kpi['total_enrolled'] ?? 0),
        'total_girls' => intval($kpi['total_girls'] ?? 0),
        'total_boys' => intval($kpi['total_boys'] ?? 0),
        'total_waivers' => intval($kpi['total_waivers'] ?? 0),
        'special_needs' => intval($kpi['special_needs'] ?? 0)
    ],
    'class_gender' => $classDist,
    'religions' => $religionDist,
    'villages' => $villageDist,
    'blood_groups' => $bloodGroupDist,
    'age_groups' => $ageDist,
    'parental_status' => $orphanDist,
    'waiver_tiers' => $waiverDist
]);
