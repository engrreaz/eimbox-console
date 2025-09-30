<?php

$ipaddr = $_SERVER['REMOTE_ADDR'];
$platform = $_SERVER['HTTP_USER_AGENT']; // চাইলে OS ডিটেক্টর লাইব্রেরি ব্যবহার করতে পারেন
$browser = $_SERVER['HTTP_USER_AGENT'];
$location = ''; // চাইলে GeoIP ব্যবহার করতে পারেন

$stmt = $conn->prepare("INSERT INTO logbook (email, sccode, pagename, ipaddr, platform, browser, entrytime) 
VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssss", $usr, $sccode, $currentFile, $ipaddr, $platform, $browser, $cur);
$stmt->execute();
$log_id = $stmt->insert_id; // পরবর্তী আপডেটের জন্য কাজে লাগবে
echo $log_id;
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

