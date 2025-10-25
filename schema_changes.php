<?php
// SET GLOBAL general_log = 'ON';
// SET GLOBAL log_output = 'TABLE';

include 'header.php'; // এতে তোমার $conn আছে
$log_file = "schema_changes.txt";
$last_run_file = "schema_last_export_time.txt";

?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Database Schema Changes Record Export to <strong>schema_changes.txt</strong>
                </div>
                <div class="card-body">

                    <?php




                    // শেষবার কখন এক্সপোর্ট করেছিলাম সেটা বের করি
                    $last_time = file_exists($last_run_file) ? trim(file_get_contents($last_run_file)) : '1970-01-01 00:00:00';

                    $sql = "SELECT event_time, argument 
        FROM mysql.general_log
        WHERE event_time > '$last_time'
        AND (
            argument LIKE 'ALTER TABLE%' OR 
            argument LIKE 'CREATE TABLE%' OR 
            argument LIKE 'DROP TABLE%' OR
            argument LIKE 'RENAME TABLE%'
        )
        ORDER BY event_time ASC";

                    $res = $conn->query($sql);

                    if ($res && $res->num_rows > 0) {
                        $f = fopen($log_file, 'a');
                        while ($row = $res->fetch_assoc()) {
                            $time = $row['event_time'];
                            $sql_text = trim($row['argument']);
                            echo "-- " . $sql_text . '<br>';
                            fwrite($f, "-- $time\n$sql_text;\n\n");
                            $last_time = $time;
                        }
                        fclose($f);
                        file_put_contents($last_run_file, $last_time);
                        echo "✅ Schema changes exported successfully up to $last_time\n";
                    } else {
                        echo "ℹ️ No new schema changes found since $last_time.\n";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>




<?php include_once('footer.php'); ?>

</body>

</html>