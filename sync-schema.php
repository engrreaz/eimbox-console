<?php
require_once 'header.php';
?>

<script>

    function reve(current) {
        if (current == '1') {
            window.location.href = "sync-schema.php?reverse=1";
        }
        else if (current == '0') {
            window.location.href = "sync-schema.php?reverse=0";
        }
        else {
            window.location.href = "sync-schema.php";
        }
    }

</script>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php

    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $db = DB_NAME;

    $ttl = 'Local';




    if (!is_dir('schema')) {
        mkdir('schema', 0755, true);
    }
    $outputFile = __DIR__ . '/schema/localhost.sql';
    if (isset($_GET['reverse']) && $_GET['reverse'] == '1') {
        $db = DB_SYNC;
        $outputFile = __DIR__ . '/schema/remote.sql';
        $ttl = 'Remote';
    }

    ?>


    <button class="btn btn-primary float-end" onclick="reve(2);">
        <i class="bi bi-arrow-repeat"></i>
        &nbsp;Switch</button>
    <button class="btn btn-primary float-end" onclick="reve(0);">
        <i class="bi bi-database-down"></i>
        &nbsp;Local</button>
    <button class="btn btn-primary float-end" onclick="reve(1);">
        <i class="bi bi-database-fill-down"></i>
        &nbsp;Remote</button>



    <h4 class="mb-4"> Download Schema <span class="text-warning fw-bold"><?php echo $ttl; ?></span></h4>



    <?php

    if (!$_GET) {
        exit;
    }

    $mysqldump = 'D:\\XAMPP\\mysql\\bin\\mysqldump.exe';

    $command = "\"$mysqldump\" --host=$host --user=$user --password=$pass --no-data --triggers --routines --events $db > \"$outputFile\" 2>&1";

    exec($command, $output, $return_var);

    if ($return_var === 0) {
        echo "<div class='alert alert-success'>Schema exported to <strong>$outputFile</strong> successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error exporting schema!<br>";
        echo "<pre>" . implode("\n", $output) . "</pre></div>";
    }
    ?>
</div>

<?php require_once 'footer.php'; ?>






<!-- ----------------------------------- -->
</body>

</html>