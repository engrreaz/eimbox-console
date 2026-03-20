<?php
$package_check = true;
$upgradeMessage = '';

$mapQ = $conn->prepare("SELECT * FROM package_map WHERE page_name=? AND package_id=?");
$mapQ->bind_param("si", $currentFile, $sccode_current_package);
$mapQ->execute();
$mapR = $mapQ->get_result();
$map = $mapR->fetch_assoc();

$totalDurationPage = $totalDurationAll = $totalRecordsPage = 0;

$access = $map ? $map['access'] : 'No';
$entry_limit = $map ? $map['entry_limit'] : '0';
$view_limit = $map ? $map['view_limit'] : '0';
$total_time_limit = $map ? $map['total_time_limit'] : '0';
$access_count_limit = $map ? $map['access_count_limit'] : '0';
$max_stay_limit = $map ? $map['max_stay_limit'] : '0';
$print = $map ? $map['print'] : 'No';
$modified_time = $map ? $map['modified_time'] : '—';

$total_time_limit_all = 0;



// $module_name = $map ? $map['module_name'] : '—';
$module_name = $cur_page_module;

$_SESSION['max_limit'] = $max_stay_limit;

// echo 'Package LIMIT : ' . $access . '/' . $entry_limit . '/' . $total_time_limit . '/' . $access_count_limit . '/' . $max_stay_limit . '....';

if ($access != 'Yes') {
    $upgrageMessage = 'Access Denied';
    $package_check = false;
} else {
    if ($entry_limit > 0) {

        $monitorPanel = true;
    }

    if ($total_time_limit > 0) {
        $stmt = $conn->prepare("SELECT SUM(duration) AS total_duration FROM logbook WHERE sccode = ? AND pagename = ?");
        $stmt->bind_param("is", $sccode, $currentFile);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $totalDurationPage = $result['total_duration'] ?? 0;
        $monitorPanel = true;
    }

    if ($total_time_limit_all > 0) {
        $stmt = $conn->prepare("SELECT SUM(duration) AS total_duration_all FROM logbook WHERE sccode = ?");
        $stmt->bind_param("i", $sccode);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $totalDurationAll = $result['total_duration_all'] ?? 0;
        $monitorPanel = true;
    }


    if ($access_count_limit > 0) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_records FROM logbook WHERE sccode = ? AND pagename = ?");
        $stmt->bind_param("is", $sccode, $currentFile);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $totalRecordsPage = $result['total_records'] ?? 0;
        $monitorPanel = true;
    }

    if ($totalDurationPage > $total_time_limit * 60 || $totalDurationAll > $total_time_limit_all * 60 || $totalRecordsPage > $access_count_limit) {
        if ($totalDurationPage > $total_time_limit * 60) {
            $upgradeMessage = 'Page Stay Limit Exceeded';
        } else if ($totalDurationAll > $total_time_limit_all * 60) {
            $upgradeMessage = 'Total Time Limit Exceeded';
        } else if ($totalRecordsPage > $access_count_limit) {
            $upgradeMessage = 'Access Count Limit Exceeded';
        }
        $package_check = false;
    }

    if ($print == 'No') {
        $permission = 1;
        include_once('core/block-print.php');
    }
}



if ($module_name == 'Backend' || $cur_page_module == 'Orion') {
    $package_check = false;
}

if ($is_admin >= 4 || $cur_page_module == 'Core' || $cur_page_module == 'Support') {
    $package_check = true;
}




function percent_safe($used, $limit)
{

    if ($used == 0 || $limit <= 0)
        return 0;
    return min(100, round(($used / $limit) * 100));
}
$pagePercent = percent_safe($totalDurationPage, $total_time_limit);
$totalPercent = percent_safe($totalDurationAll, $total_time_limit_all);
$accessPercent = percent_safe($totalRecordsPage, $access_count_limit);