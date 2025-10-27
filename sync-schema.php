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

    // যদি reverse request আসে
    if (isset($_GET['reverse']) && $_GET['reverse'] == '1') {
        $db = DB_SYNC;
        $ttl = 'Remote';
    }

    // schema folder তৈরি ও permission
    $schemaDir = __DIR__ . '/schema';
    if (!is_dir($schemaDir)) {
        mkdir($schemaDir, 0755, true);
    }
    $outputFile = $schemaDir . '/' . ($ttl === 'Local' ? 'localhost.sql' : 'remote.sql');

    // OS অনুযায়ী mysqldump path
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $mysqldump = 'D:\\XAMPP\\mysql\\bin\\mysqldump.exe';
    } else {
        // Linux/macOS default
        $mysqldump = trim(shell_exec('which mysqldump'));
        if (!$mysqldump) {
            echo "<div class='alert alert-danger'>mysqldump not found on server!</div>";
            exit;
        }
    }

    // buttons
    ?>
    <button class="btn btn-primary float-end" onclick="reve(2);">
        <i class="bi bi-arrow-repeat"></i> &nbsp;Switch
    </button>
    <button class="btn btn-primary float-end" onclick="reve(0);">
        <i class="bi bi-database-down"></i> &nbsp;Local
    </button>
    <button class="btn btn-primary float-end" onclick="reve(1);">
        <i class="bi bi-database-fill-down"></i> &nbsp;Remote
    </button>

    <h4 class="mb-4"> Download Schema <span class="text-warning fw-bold"><?php echo $ttl; ?></span></h4>

    <?php
    if (!$_GET)
        exit;

    // Command তৈরি (Linux/Windows compatible quoting)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = "\"$mysqldump\" --host=$host --user=$user --password=$pass --no-data --triggers --routines --events $db > \"$outputFile\" 2>&1";
    } else {
        $command = "$mysqldump --host='$host' --user='$user' --password='$pass' --no-data --triggers --routines --events '$db' > '$outputFile' 2>&1";
    }

    // exec call
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


</body>

</html>