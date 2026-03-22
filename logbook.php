<?php

$GLOBALS['script_end'] = microtime(true);
$GLOBALS['execution_time'] = round(($GLOBALS['script_end'] - $GLOBALS['script_start']), 4);

echo "Queries: {$GLOBALS['queries_count']}, Time: {$GLOBALS['execution_time']}s";

$ipaddr = $_SERVER['REMOTE_ADDR'];
$platform = 'Console';
$browser = $_SERVER['HTTP_USER_AGENT'];
$location = '';


// --------------- mySQL Connection -------------------------
$q = $conn->query("SHOW STATUS LIKE 'Threads_connected'");
$row = $q->fetch_assoc();
echo " -- Open connections: " . $row['Value'];

$q = $conn->query("SHOW VARIABLES LIKE 'max_connections'");
$row = $q->fetch_assoc();
echo " -- Max connections: " . $row['Value'];

$q = $conn->query("SHOW STATUS LIKE 'Max_used_connections'");
$row = $q->fetch_assoc();
echo " -- Max Limit: " . $row['Value'];

// $q = $conn->query("SHOW FULL PROCESSLIST");
// $row = $q->fetch_assoc();
// echo " -- Full Process: " . $row['Value'];
// --------------- mySQL Connection -------------------------

$stmt = $conn->prepare("INSERT INTO logbook (email, sccode, pagename, ipaddr, platform, browser, entrytime) 
VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssss", $usr, $sccode, $currentFile, $ipaddr, $platform, $browser, $cur);
$stmt->execute();
$log_id = $stmt->insert_id;
?>



<script>
    window.addEventListener("beforeunload", function () {
        let startTime = window.performance.timing.navigationStart;
        let endTime = Date.now();
        let duration = Math.round((endTime - startTime) / 1000); // সেকেন্ডে সময়

        navigator.sendBeacon("core/log_update.php", JSON.stringify({
            id: "<?php echo $log_id; ?>",
            duration: duration
        }));
    });
</script>