<?php
// apply_schema_updates.php
// --- REQUIREMENTS: includes/header.php creates $conn (mysqli) ---
// DO NOT re-include config/db separately

include 'header.php'; // তোমার প্রকজেক্টের header যা $conn তৈরি করে

set_time_limit(0);
date_default_timezone_set('Asia/Dhaka');

$baseDir = __DIR__;
$logFile = $baseDir . '/schema_changes.txt';                       // uploaded file
$dailyLogFile = $baseDir . '/schema_log_' . date('Y-m-d') . '.txt';
$backupDir = $baseDir . '/backups';

?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-warning">
                    Database Schema Updates from  <strong>schema_changes.txt</strong>
                </div>
                <div class="card-body">

                    <?php

                    // Ensure backup dir exists
                    if (!is_dir($backupDir)) {
                        @mkdir($backupDir, 0755, true);
                    }

                    // Check file
                    if (!file_exists($logFile)) {
                        die("<h3 style='color:red'>❌ schema_changes.txt not found in script directory.</h3>");
                    }

                    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if ($lines === false) {
                        die("<h3 style='color:red'>❌ Failed to read schema_changes.txt</h3>");
                    }

                    $newFileLines = []; // will hold updated file content with tags
                    $summary = ['applied' => 0, 'failed' => 0, 'skipped' => 0];

                    foreach ($lines as $rawLine) {
                        $line = trim($rawLine);
                        if ($line === '')
                            continue;

                        // If line already has tag, skip (keep as-is)
                        if (stripos($line, '-- [APPLIED') !== false || stripos($line, '-- [FAILED') !== false || stripos($line, '-- [SKIPPED') !== false) {
                            $newFileLines[] = $line;
                            $summary['skipped']++;
                            continue;
                        }

                        // Extract SQL (after '] ' if timestamp present)
                        $sql = $line;
                        if (preg_match('/\] (.+)$/', $line, $m)) {
                            $sql = trim($m[1]);
                        }
                        $sql = trim($sql);

                        if ($sql === '') {
                            $newFileLines[] = $line . " -- [SKIPPED at " . date('Y-m-d H:i:s') . " - empty sql]";
                            $summary['skipped']++;
                            continue;
                        }

                        // Check if same SQL already applied (in schema_update_log)
                        $already = false;
                        $stmt_check = $conn->prepare("SELECT COUNT(*) AS cnt FROM schema_update_log WHERE sql_statement = ? AND status = 'APPLIED'");
                        $stmt_check->bind_param('s', $sql);
                        $stmt_check->execute();
                        $res_check = $stmt_check->get_result();
                        if ($res_check) {
                            $r = $res_check->fetch_assoc();
                            if ($r && isset($r['cnt']) && $r['cnt'] > 0) {
                                $already = true;
                            }
                        }
                        $stmt_check->close();

                        if ($already) {
                            $newFileLines[] = $line . " -- [SKIPPED at " . date('Y-m-d H:i:s') . " - already applied]";
                            $summary['skipped']++;
                            file_put_contents($dailyLogFile, "[" . date('Y-m-d H:i:s') . "] SKIPPED: $sql" . PHP_EOL, FILE_APPEND);
                            continue;
                        }

                        // Execute SQL
                        $executed_at = date('Y-m-d H:i:s');
                        $status = '';
                        $error_message = null;
                        $backup_path = null;

                        try {
                            if ($conn->query($sql) === TRUE) {
                                $status = 'APPLIED';
                                $summary['applied']++;
                                echo "<div style='color:green'>✅ APPLIED: " . htmlspecialchars($sql) . "</div>";
                            } else {
                                $status = 'FAILED';
                                $error_message = $conn->error;
                                $summary['failed']++;
                                echo "<div style='color:red'>❌ FAILED: " . htmlspecialchars($sql) . "<br><small>Error: " . htmlspecialchars($error_message) . "</small></div>";
                            }
                        } catch (mysqli_sql_exception $ex) {
                            $error_message = $ex->getMessage();

                            // ডুপ্লিকেট কলাম / ইন্ডেক্স / টেবিল হলে স্কিপ হিসেবে ধরা হবে
                            if (
                                stripos($error_message, 'Duplicate column') !== false ||
                                stripos($error_message, 'Duplicate key') !== false ||
                                stripos($error_message, 'Duplicate entry') !== false ||
                                stripos($error_message, 'already exists') !== false
                            ) {

                                $status = 'SKIPPED';
                                $summary['skipped']++;
                                echo "<div style='color:orange'>⚠️ SKIPPED (Already Exists): " . htmlspecialchars($sql) . "</div>";
                            } else {
                                $status = 'FAILED';
                                $summary['failed']++;
                                echo "<div style='color:red'>❌ FAILED: " . htmlspecialchars($sql) . "<br><small>Error: " . htmlspecialchars($error_message) . "</small></div>";
                            }
                        }


                        // Insert into schema_update_log
                        $ins = $conn->prepare("INSERT INTO schema_update_log (executed_at, sql_statement, status, error_message, backup_path) VALUES (?, ?, ?, ?, ?)");
                        $ins->bind_param('sssss', $executed_at, $sql, $status, $error_message, $backup_path);
                        $ins->execute();
                        $ins->close();

                        // Append tag to the line
                        $tag = " -- [{$status} at " . date('Y-m-d H:i:s') . "]";
                        if ($status === 'FAILED' && $backup_path) {
                            $tag .= " -- [BACKUP: $backup_path]";
                        }
                        $newFileLines[] = $line . $tag;

                        // Append to daily log
                        $dailyEntry = "[" . date('Y-m-d H:i:s') . "] $status: $sql";
                        if ($status === 'FAILED')
                            $dailyEntry .= " -- ERROR: " . $error_message;
                        if (!empty($backup_path))
                            $dailyEntry .= " -- BACKUP: " . $backup_path;
                        file_put_contents($dailyLogFile, $dailyEntry . PHP_EOL, FILE_APPEND);
                    }

                    // Write back updated log file (overwrites)
                    file_put_contents($logFile, implode(PHP_EOL, $newFileLines) . PHP_EOL);

                    // Summary output
                    echo "<hr>";
                    echo "<b>Summary:</b><br>";
                    echo "Applied: {$summary['applied']} <br>";
                    echo "Failed: {$summary['failed']} <br>";
                    echo "Skipped: {$summary['skipped']} <br>";

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include_once('footer.php'); ?>

</body>

</html>