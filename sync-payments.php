<?php require_once 'header.php'; ?>

<?php
$slot = $_COOKIE['chain-slot'] ?? '';
$sy = $_COOKIE['chain-session'] ?? '';


$type = $_GET['type'] ?? '';
$part = $_GET['part'] ?? 'all';
$icode = $_GET['icode'] ?? '';
$stid = $_GET['stid'] ?? '';
$cls = $_GET['cls'] ?? '';
$sec = $_GET['sec'] ?? '';
$reset = isset($_GET['reset']) ? 1 : 0;

/* RESET VALIDATION */
$where = "sccode='$sccode' AND sessionyear LIKE '%$sy%'";
if ($stid)
    $where .= " AND stid='$stid'";
elseif ($sec)
    $where .= " AND classname='$cls' AND sectionname='$sec'";
elseif ($cls)
    $where .= " AND classname='$cls'";

if ($reset == 1) {
    $conn->query("UPDATE sessioninfo SET validate=0 WHERE $where");
}


/* COUNT */
$q = $conn->query("SELECT COUNT(*) c FROM sessioninfo WHERE $where AND validate=0");
$total = $q->fetch_assoc()['c'];
?>

<div class="container-xxl py-4">

    <?php
                        $chain_param = '-c 10 -t Choose Values -u -r -b View Students';
                        include 'components/slot-tree-ui.php';
                        ?>


    <div class="card shadow-sm">
        <div class="card-header ">

    

            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-3">Payment Sync</h5>
                </div>
                <div class="col-12">

                    <div class="row align-items-end g-2">
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- PARAMETERS -->
            <?php include 'payments/six-parameters.php'; ?>


            <!-- STATUS -->
            <div class="mb-2">
                <strong class="text-danger" id="countus"><?= $total ?></strong>
                <span class="ms-1">students pending</span>
            </div>

            <div class="text-warning small mb-2">
                Estimated time ≈ <b><span id="remain"><?= $total * 3 ?></span></b> seconds
            </div>

            <!-- PROGRESS -->
            <div class="progress mb-3" style="height:20px;">
                <div id="prog" class="progress-bar" style="width:0%; height:20px;">0%</div>
            </div>

            <!-- ACTION -->
            <div class="d-flex mb-3 justify-content-between">
                <button id="syncBtn" class="btn btn-success btn-sm" onclick="runSync()">Sync Now</button>
                <button class="btn btn-danger btn-sm" onclick="resetAll()">Reset All Settings</button>
            </div>



            <!-- LOG -->
            <div id="log" class="small fst-italic" <?php if ($is_admin < 5)
                echo 'hidden'; ?>></div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function resetAll() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will reset all data!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = new URL(window.location.href);
                url.searchParams.set('reset', '1');
                window.location.href = url.toString();
            }
        });
    }

    function runSync() {
        $('#syncBtn').prop('disabled', true);
        processSync();
    }

    function processSync() {
        let sy = document.getElementById('session-main').value;
        $.post('payments/check-student-finance.php', {
            sy: '<?= $sy ?>',
            type: '<?= $type ?>',
            part: '<?= $part ?>',
            icode: '<?= $icode ?>',
            stid: '<?= $stid ?>',
            cls: '<?= $cls ?>',
            sec: '<?= $sec ?>'
        }, function (res) {

            $('#log').prepend(res);

            let left = parseInt($('#totaltotal').text());
            let total = <?= max($total, 1) ?>;

            $('#countus').text(left);

            let p = Math.round((1 - left / total) * 100);
            $('#prog').css('width', p + '%').text(p + '%');
            let current = parseInt($('#remain').text()) || 0;
            let newVal = current - 3;
            $('#remain').text(newVal);

            if (left > 0) {
                processSync();
            } else {
                $('#prog').css('width', '100%').text('100%');
                $('#log').prepend('<div class="text-success">Completed</div>');
            }

            if ('<?= $type ?>' == 'stid') {

                setCookie('payment-stid', '<?= $stid ?>');
                window.history.back();
            }

        });
    }
    $('#slot-main').val(<?= $slot ?>);
    $('#session-main').val(<?= $sy ?>);


</script>

<script>
    if (<?= $reset ?> == 1) {
        const url = new URL(window.location.href);
        url.searchParams.delete('reset');
        history.replaceState(null, '', url.toString());
        runSync();
    }


    $('#class-main').on('change', function () {
        let cls = $(this).val();

        $('#section-main').html('<option value="">Loading...</option>');

        $.post('payments/get-sections.php', { cls: cls }, function (res) {
            $('#section-main').html(res);
        });
    });
</script>

<script>
    $('#applyFilter').on('click', function () {

        let params = {
            type: $('#type-main').val(),
            part: $('#part-main').val(),
            icode: $('#icode-main').val(),
            stid: $('#student-main').val(),
            cls: $('#class-main').val(),
            sec: $('#section-main').val()
        };

        // remove empty values
        let query = [];
        $.each(params, function (k, v) {
            if (v !== '' && v !== null) {
                query.push(k + '=' + encodeURIComponent(v));
            }
        });

        let qs = query.join('&');
        let url = window.location.pathname + (qs ? '?' + qs : '');

        window.location.href = url;
    });
</script>

<script>
    $(function () {

        // simple inputs
        $('#type-main').val('<?= htmlspecialchars($type) ?>');
        $('#part-main').val('<?= htmlspecialchars($part) ?>');
        $('#icode-main').val('<?= htmlspecialchars($icode) ?>');
        $('#student-main').val('<?= htmlspecialchars($stid) ?>');
        $('#class-main').val('<?= htmlspecialchars($cls) ?>');

        // section depends on class (AJAX)
        let sec = '<?= htmlspecialchars($sec) ?>';

        if ($('#class-main').val()) {
            $.post('payments/get-sections.php', {
                cls: $('#class-main').val()
            }, function (res) {
                $('#section-main').html(res);
                $('#section-main').val(sec);
            });
        }
    });


    $('#slot-main').on('change', function () {
        setCookie('chain-slot', $(this).val());
    });

    // session-main পরিবর্তনের সময় cookie update
    $('#session-main').on('change', function () {
        setCookie('chain-session', $(this).val());
    });


    if ('<?= $type ?>' == 'stid') {
        ;
        runSync();
    }
</script>


</body>

</html>