<?php
require_once 'header.php';
?>

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


    <button class="btn btn-primary float-end" onclick="reve();"> <i class="bi bi-arrow-repeat"></i>
        &nbsp;Switch</button>
    <h4 class="mb-4"> Download Schema <span class="text-warning fw-bold"><?php echo $ttl; ?></span></h4>

    <script>
        function reve() {
            const url = new URL(window.location);
            const current = url.searchParams.get('reverse');

            if (current === '1') {
                url.searchParams.set('reverse', '0');
            } else if (current === '0') {
                url.searchParams.set('reverse', '1');
            } else {
                url.searchParams.set('reverse', '0');
            }
            window.location = url.toString();
        }
    </script>

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
<script></script>





<!-- ----------------------------------- -->
</body>

</html>