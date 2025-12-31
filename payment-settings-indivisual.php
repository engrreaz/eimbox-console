<?php
require_once 'header.php';

$year = $_GET['year'] ?? date('Y');
$cls = trim($_GET['cls'] ?? '');
$sec = trim($_GET['sec'] ?? '');
$roll = $_GET['roll'] ?? '';

$stid = '';
$sql = "
    SELECT stid, rate
    FROM sessioninfo 
    WHERE sccode='$sccode'
      AND sessionyear LIKE '%$year%'
      AND classname='$cls'
      AND sectionname='$sec'
      AND rollno='$roll'
    ORDER BY id DESC
    LIMIT 1
";

$res = $conn->query($sql);

$stid = '';
$rate = 0;

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();   // ✅ একবারই fetch
    $stid = $row['stid'];
    $rate = $row['rate'];
}


/* ---------------- FINANCE SETUP ---------------- */
$finsetup = [];
$res = $conn->query("
    SELECT * FROM financesetup 
    WHERE sccode='$sccode' 
      AND sessionyear LIKE '%$year%'
    ORDER BY slno
");
while ($row = $res->fetch_assoc()) {
    $finsetup[] = $row;
}

/* ---------------- SETUP VALUES ---------------- */
$finsetupval = [];
$res = $conn->query("
    SELECT * FROM financesetupvalue 
    WHERE sccode='$sccode'
      AND sessionyear LIKE '%$year%'
");
while ($row = $res->fetch_assoc()) {
    $finsetupval[] = $row;
}


/* ======================================================
   1️⃣ LOAD MASTER ITEM LIST
====================================================== */
$finItems = [];
$sql = "
    SELECT *
    FROM financesetup
    WHERE sccode = '$sccode'
      AND sessionyear LIKE '%$year%'
    ORDER BY slno
";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $finItems[] = $row;
}


/* ======================================================
   2️⃣ LOAD DEFAULT AMOUNT (itemcode => amount)
====================================================== */
$defaultAmt = [];
$sql = "
    SELECT id, itemcode, amount
    FROM financesetupvalue
    WHERE sccode = '$sccode'
    AND sessionyear LIKE '%$year%'
    AND (
            (sectionname = '$sec') 
        OR (sectionname IS NULL OR sectionname = '')
        )
    AND (
            (classname = '$cls') 
        OR (classname IS NULL OR classname = '')
        )
    ORDER BY 
        CASE 
            WHEN sectionname = '$sec' THEN 3
            WHEN sectionname IS NULL OR sectionname = '' THEN 2
            ELSE 1
        END,
        CASE
            WHEN classname = '$cls' THEN 3
            WHEN classname IS NULL OR classname = '' THEN 2
            ELSE 1
        END,
        id
    ";
// echo $sql;
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $defaultAmt[$row['itemcode']] = $row;
}

// var_dump($defaultAmt);

/* ======================================================
   3️⃣ LOAD INDIVIDUAL AMOUNT (stid priority)
====================================================== */
$indAmt = [];
$sql = "
    SELECT id, stid, itemcode, amount
    FROM financesetupind
    WHERE sccode = '$sccode'
      AND sessionyear LIKE '%$year%'
      AND stid = '$stid'
    ";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $indAmt[$row['itemcode']] = $row;
}



?>


<div class="container-xxl container-p-y">

    <div class="row">
        <div class="col-3">
            <div class="card h-100">
                <div class="card-body">

                    <h6>Select Student</h6>

                    <select id="slot-main" class="form-select form-select-sm">
                        <option value="">Select Slot</option>
                        <?php
                        $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname");
                        while ($r = $q->fetch_assoc()) {
                            $sel = ($slot == $r['slotname']) ? 'selected' : '';
                            echo "<option value='{$r['slotname']}' $sel>{$r['slotname']}</option>";
                        }
                        ?>
                    </select>

                    <label class="form-label">Session</label>
                    <select id="session-main" class="form-select form-select-sm">
                        <option value="">Select Session</option>
                        <?php
                        $q = $conn->query("SELECT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY syear DESC");
                        while ($r = $q->fetch_assoc()) {
                            $sel = ($r['syear'] == $sy) ? 'selected' : '';
                            echo "<option value='{$r['syear']}' $sel>{$r['syear']}</option>";
                        }
                        ?>
                    </select>

                    <label class="form-label small">Class</label>
                    <select class="form-select form-select-sm" name="cls" id="class-main">
                        <option value=""></option>
                        <?php
                        $q = "SELECT DISTINCT areaname
                  FROM areas
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
                  ORDER BY areaname";
                        $r = $conn->query($q);
                        while ($row = $r->fetch_assoc()) {
                            echo "<option value='{$row['areaname']}'>{$row['areaname']}</option>";
                        }
                        ?>
                    </select>

                    <label class="form-label small">Section</label>
                    <select class="form-select form-select-sm" name="sec" id="section-main">
                        <option value=""></option>
                    </select>

                    <label class="mt-2">Roll</label>
                    <input id="roll" class="form-control form-control-sm" value="<?= $roll ?>">

                    <button onclick="go()" class="btn btn-primary btn-sm  mt-3">
                        Show Payment Items
                    </button>

                </div>
            </div>
        </div>

        <div class="col-9 ">
            <div class="card h-100">

                <div class="card-header">
                    <div class="row">
                        <div class="col-10">
                            <h5>Payment Items</h5>
                        </div>

                        <div class="col-2">
                            <input type="text" class="form-control form-control-sm" value="<?= $rate ?>">
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <div class="finance-list">

                        <?php
                        foreach ($finItems as $item):

                            $code = $item['itemcode'];

                            /* -------- amount resolve priority -------- */
                            if (isset($indAmt[$code])) {
                                $amount = $indAmt[$code]['amount'];
                                $rowid = $indAmt[$code]['id'];
                                $tag = 'IND';
                            } elseif (isset($defaultAmt[$code])) {
                                $amount = $defaultAmt[$code]['amount'];
                                // $rowid = $defaultAmt[$code]['id'];
                                $rowid = 0;
                                $tag = 'DEF';
                            } else {
                                $amount = 0;
                                $rowid = 0;
                                $tag = 'NEW';
                            }
                            ?>

                            <div class="row align-items-center small mt-2">

                                <!-- ITEM INFO -->
                                <div class="col">
                                    <strong><?= $item['particulareng']; ?></strong><br>
                                    <span class="text-muted"><?= $item['particularben']; ?></span>
                                    <span class="badge bg-secondary ms-1"><?= $tag ?></span>
                                </div>

                                <!-- AMOUNT INPUT -->
                                <div class="col-auto text-end pe-0">
                                    <input type="text" id="rowid<?= $code ?>" value="<?= $rowid ?>" hidden>

                                    <input type="text" class="form-control form-control-sm text-end float-end "
                                        id="amt<?= $code ?>" style="width: 70px; margin:0;" value="<?= $amount ?>" onblur="updateFinanceAmount(
                                                '<?= $item['slot'] ?>',
                                                '<?= $year ?>',
                                                '<?= $code ?>',
                                                <?= $rowid ?>,
                                                '<?= $tag ?>'
                                            )">
                                </div>

                                <!-- DELETE BUTTON -->
                                <div class="col-auto text-end ms-0">
                                    <button class="btn btn-sm btn-danger" <?= $rowid > 0 ? "" : "disabled" ?>
                                        onclick="deleteFinanceRow(<?= $rowid ?>, '<?= $code ?>')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>

                            </div>


                        <?php endforeach; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<script>
    function go() {
        let aslot = $('#slot-main').val();
        let ayear = $('#session-main').val();
        let acls = $('#class-main').val();
        let asec = $('#section-main').val();
        let p = new URLSearchParams({
            year: ayear,
            cls: acls,
            sec: asec,
            roll: roll.value
        });
        location.href = 'payment-settings-indivisual.php?' + p.toString();
    }

    /* ---------- UPDATE AMOUNT ---------- */
    function upddata(slot, sy, item, cls, sec, indid) {
        let ind = item + cls + sec;
        let amt = document.getElementById('amt' + ind).value;
        let id = document.getElementById('id' + ind).value;
        let stid = <?= (int) $stid ?>;

        $.post('payments/crud-set-financed-ind.php', {
            id, slot, sy, item, cls, sec, amt, stid, indid
        }, function (res) {
            $('#status' + ind).html(res);
            $('#amt' + ind).prop('disabled', true);
        });
    }

    /* ---------- APPLY / PRELOAD ---------- */
    function preloads(type, part, icode, cls, sec) {
        $.post('payments/check-student-finance-pre.php', {
            type, part, icode, cls, sec
        }, function () {
            location.href = 'sync-payments.php';
        });
    }

    /* ---------- UI TOGGLE ---------- */
    function toggle(id) {
        let el = document.getElementById(id);
        el.style.display = el.style.display === 'block' ? 'none' : 'block';
    }

    function no(e) { e.stopPropagation(); }
</script>


<script>
    function updateFinanceAmount(slot, session, itemcode, rowid, tag) {
        let amt = $('#amt' + itemcode).val();
        $.post('payments/crud-set-financed-ind.php', {
            slot: slot,
            session: session,
            itemcode: itemcode,
            amount: amt,
            rowid: rowid,
            stid: <?= (int) $stid ?>,
            tag: tag
        }, function (res) {
            showToast('info', 'Update Amount', 'Update ');
        });
    }
</script>

<script>
    function deleteFinanceRow(rowid, code) {
        if (rowid <= 0) return;

        if (!confirm("Are you sure you want to delete this item?")) return;

        $.post('payments/delete-finance-ind.php', { rowid: rowid }, function (response) {
            alert(response); // Response as HTML/text
            // Optional: remove the row from DOM
            if (response.includes("Deleted")) {
                $('#amt' + code).closest('.row').remove();
            }
        });
    }

</script>




<script>
    $(function () {

        // simple inputs

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

    $('#class-main').on('change', function () {
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
</script>


</body>

</html>