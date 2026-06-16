<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$stid = $_POST['stid'];
$lastpr = $_POST['lastpr'] ?? 0;
$lastpr *= 1;
$datam = $_POST['datam'];
$sy = $_POST['year'];
$tdues = $_POST['tdues'];

$needSync = 0;
// sessionyear can be 2025 or 2024-2025
$sy_raw = $_POST['year'] ?? date('Y');

// extract first 4 digit year
preg_match('/\d{4}/', $sy_raw, $m);
$year4 = $m[0] ?? date('Y');

// last 2 digit
$yy = substr($year4, -2);
$stid4 = str_pad($stid % 10000, 4, '0', STR_PAD_LEFT);
$sql = "
    SELECT prno 
    FROM stpr 
    WHERE sccode='$sccode'
      AND stid='$stid'
      AND sessionyear LIKE '{$sy}%'
    ORDER BY prno DESC 
    LIMIT 1
";

$res = $conn->query($sql);

$next_serial = 1;

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $last_prno = $row['prno'];

    // last 2 digit serial
    $next_serial = (int) substr($last_prno, -2) + 1;
}
$serial2 = str_pad($next_serial, 2, '0', STR_PAD_LEFT);

$prno = $yy . $stid4 . $serial2;



$b = explode("_", $datam);
$ccc = $b[0] ?? '';
$sss = $b[1] ?? '';
$rrr = $b[2] ?? '';
$eee = $b[3] ?? '';
$bbb = $b[4] ?? '';
$mmm = $b[5] ?? '';


$cnt = 0;
$tamt = 0;
// $month = date('m');
$month = (int)date('m');

if ((int)date('d') > 15) {
    $month++;

}
if ($month >= 10) {
    $month = 12;
}

$finValArr = [];

$sql5 = "SELECT itemcode, modifieddate FROM financesetupvalue where sessionyear LIKE '%$sy%' and sccode='$sccode' and (classname='$ccc' or classname='') and (sectionname='$sss' or sectionname='')  order by itemcode, classname desc, sectionname desc , id";
// echo $sql5;
$result5rxx = $conn->query($sql5);
if ($result5rxx->num_rows > 0) {
    while ($row5 = $result5rxx->fetch_assoc()) {
        $finValArr[] = $row5;
    }
}



$ind_fin_value = 0;

$sql55 = "SELECT * FROM financesetupind where sessionyear LIKE '%$sy%' and sccode='$sccode' and stid='$stid' order by id";
// echo $sql5; 
$result5rxx = $conn->query($sql55);
if ($result5rxx->num_rows > 0) {
    $ind_fin_value = 1;
    $fill = '';
}
?>






<style>
    .dues-row {
        cursor: pointer;
    }
</style>




<div class="card  mb-3 border border-primary border-0 studentInfo">
    <div class="card-body p-3">

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1 fw-bold">
                    <?= htmlspecialchars($eee) ?>
                </h5>
                <small class="text-muted"><?= htmlspecialchars($bbb) ?></small>

                <p class="mb-1 mt-2">
                    Class : <span id="fin-cls" class="fw-bold"><?= htmlspecialchars($ccc) ?></span>
                    (<span id="fin-sec" class="fw-bold"><?= htmlspecialchars($sss) ?></span>) |
                    Roll : <b><span id="cur-roll"><?= $rrr ?></span></b>
                </p>

                <small class="text-info">
                    ID: <span id="fin-stid" class="fw-bold"><?= $stid ?></span> | <i class="bi bi-telephone-fill"></i>
                    <?= $mmm ?>
                </small>
            </div>

            <div class="text-end">
                <button class="btn btn-outline-secondary btn-sm mb-1" onclick="openFineModal('<?= $stid ?>')">
                    <i class="bi bi-currency-exchange icon-18px me-3"></i> Fine
                </button>

                <button class="btn btn-outline-info btn-sm mb-1" onclick="history('<?= $stid ?>')">
                    <i class="bi bi-clock-history me-3"></i> History
                </button>

                <button class="btn btn-danger btn-sm fw-bold pb-1 fs-6"
                    onclick="preloads('stid','','','<?= $stid ?>','','',1);">
                    <?= number_format($tdues ?? 0, 2) ?>
                </button>

                <div class="text-danger text-small me-1" style="font-size:12px;">Total Dues</div>
            </div>
        </div>

    </div>
</div>

<div id="history-end"></div>

<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row g-2">

            <div class="col-md-3">
                <input class="form-control form-control-sm fw-bold px-0 text-center" id="prno" value="<?= $prno ?>"
                    disabled>
            </div>

            <div class="col-md-3">
                <input type="date" class="form-control form-control-sm" id="prdate" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-md-3">
                <input class="form-control form-control-sm text-end fw-bold" id="amt" value="0.00" disabled>
            </div>

            <div class="col-md-3">
                <button class="btn btn-success btn-sm w-100 fs-6  fw-bold h-100" id="bbttnn"
                    onclick="save(<?= $stid ?>, <?= $sy ?>);" disabled>
                    <i class="bi bi-coin p-0 icon-18px me-5"></i> Pay Now
                </button>
            </div>

        </div>
    </div>
</div>


<div class="table-responsive" id="item-list-table">
    <table class="table table-sm table-hover align-middle">
        <tbody>

            <?php
            $upd = $ccc . '_update';
            $sql5 = "SELECT id, $ccc, $upd, itemcode, splitable, modifieddate FROM financesetup where sessionyear LIKE '%$sy%' and sccode='$sccode'  order by id";
            // echo $sql5; 
            $result5r = $conn->query($sql5);
            if ($result5r->num_rows > 0) {
                while ($row5 = $result5r->fetch_assoc()) {
                    $finset[] = $row5;
                }
            }

            $cnt = 0;
            $stfinance = [];
            $sql5 = "SELECT * FROM stfinance where sessionyear LIKE '%$sy%' and sccode='$sccode' and stid='$stid' and dues > 0 and month<='$month' order by partid, splitid, id";
            $result5 = $conn->query($sql5);
            if ($result5->num_rows > 0) {
                while ($row5 = $result5->fetch_assoc()) {
                    $stfinance[] = $row5;
                }
            }

            foreach ($stfinance as $row):
                $fid = $row["id"];
                $partid = $row["partid"];
                $particulareng = $row["particulareng"];
                $dues = $row["dues"];
                $icode = $row["itemcode"];
                $splitid = $row["splitid"];

                $finModify = $row["modifieddate"];


                $src = array_search($icode, array_column($finset, 'itemcode'));
                $upddate = $finset[$src][$upd];
                $itemModify = $finset[$src]['modifieddate'];
                $updtaka = $finset[$src][$ccc];
                $icode2 = $finset[$src]['itemcode'];
                $splt = 0;

                $indx = array_search($icode, array_column($finValArr, 'itemcode'));
                $valModify = $cur;
                if ($indx !== false && $indx > -1) {
                    $valModify = $finValArr[$indx]['modifieddate'] ?? $cur;
                }

                if ($icode == $icode2) {
                    $splt = $finset[$src]['splitable'];
                }
                ?>
                <tr class="dues-row" onclick="toggleRow(<?= $cnt ?>)">
                    <td width="40">
                        <input type="checkbox" id="rex<?= $cnt ?>" class="form-check-input"
                            onclick="event.stopPropagation(); sel(<?= $cnt ?>);">
                        <div id="fid<?= $cnt ?>" hidden><?= $fid ?></div>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['particulareng']) ?>
                    </td>

                    <td width="40" class="p-0 text-end">
                        <?php
                        if ($finModify < $valModify) {
                            $needSync++;

                            ?>
                            <span class="ms-2 text-danger" title="Need Update"
                                onclick="event.stopPropagation(); preloads('stid','','','<?= $stid ?>','','',1);">
                                <i class="bi bi-arrow-repeat  icon-22px fw-bold"></i>
                            </span>
                            <?php
                        }
                        ?>
                    </td>

                    <td width="40" class="p-0 text-end">
                        <?php if ($splt):
                            if ($splitid > 0) {
                                ?>
                                <span class="ms-2 text-info fw-bold"
                                    onclick="event.stopPropagation(); mergerow(<?php echo $fid; ?>, <?php echo $splitid; ?>, 2);"
                                    title="This item is already split. Click to rollback.">
                                    <i class="bi bi-union icon-22px fw-bold"></i>
                                </span>
                                <?php
                            } else {
                                ?>
                                <span class="ms-2 text-warning"
                                    onclick="event.stopPropagation(); splitable0(<?= $fid ?>, <?= $dues ?>)">
                                    <i class="bi bi-subtract  icon-22px fw-bold"></i>
                                </span>
                                <?php
                            }

                        endif; ?>


                        <?php if (str_contains($row['particulareng'], 'Fine') && $userlevel !== 'User'): ?>
                            <i class="bi bi-x-circle-fill text-danger fw-bold icon-22px"
                                onclick="event.stopPropagation(); delitem(<?= $fid ?>);"></i>
                        <?php endif; ?>
                    </td>

                    <td class="text-end fw-bold">
                        <span id="amt<?= $cnt ?>"><?= number_format($dues, 2) ?></span>
                    </td>
                </tr>
                <?php $cnt++; endforeach; ?>

        </tbody>
    </table>

    <input type="hidden" id="cntp" value="<?= $cnt ?>">
    <input type="hidden" id="chk" value="0">
</div>





<script>document.getElementById("total_due").innerHTML = '<?php echo $tamt; ?>.00';</script>

<script>
    function diss() {
        var aaa = document.getElementById("amt").value;
        var bb = document.getElementById("bbttnn");

        if (aaa > 0) {
            bb.disabled = false;
            // alert("Yes");
        } else {
            bb.disabled = true;
            // alert("NO");
        }

    }
</script>

<script>
    function preloads(type, part, icode, stid, cls, sec, tail) {
        ind = icode + cls + sec;
        var infor = "type=" + type + "&part=" + part + "&icode=" + icode + "&stid=" + stid + "&cls=" + cls + "&sec=" + sec + "&tail=" + tail;
        // alert(infor);
        $("#total_due").html("");
        $.ajax({
            type: "POST",
            url: "payments/check-student-finance-pre.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#total_due").html('<i class="bi bi-check"></i>');
            },
            success: function (html) {
                $("#total_due").html(html);
                // alert(type);
                window.location.href = 'sync-payments.php?' + infor;
            }
        });
    }
</script>




<script>
    function history(stid) {

        let infor = { stid: stid };

        // modal open
        let modal = new bootstrap.Modal(document.getElementById('historyModal'));
        modal.show();

        $('#historyContent').html(
            '<div class="text-center text-muted">' +
            '<i class="bi bi-arrow-repeat"></i> Loading history...' +
            '</div>'
        );

        $.ajax({
            type: "POST",
            url: "payments/check-student-payment-history.php",
            data: infor,
            cache: false,
            success: function (html) {
                $('#historyContent').html(html);
            },
            error: function () {
                $('#historyContent').html(
                    '<div class="alert alert-danger">Failed to load history</div>'
                );
            }
        });
    }


    function delitem(fid) {
        event.stopPropagation();
        var infor = "fid=" + fid;
        // alert(infor);

        $("#history-end").html("");
        $.ajax({
            type: "POST",
            url: "payments/del-stfinance-item.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#history-end").html('<i class="bi bi-arrow-repeat"></i>');
            },
            success: function (html) {
                $("#history-end").html(html);
                let stid = $('#fin-stid').text().trim();
                // alert(stid);
                setCookie('payment-stid', stid);
                showToast('danger', 'Item Deleted', 'Delete Item');
                setTimeout(function () { window.location.reload(); }, 500);

            }
        });
    }


    function splitable0(fid, amt) {
        event.stopPropagation();
        document.getElementById("spltid").value = fid;
        document.getElementById("spltamt").value = amt;
        document.getElementById("spltamtpre").value = amt;
        var myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {
            keyboard: false
        });
        myModal.show();
        const input = document.getElementById("spltamt");
        input.focus();
        input.select();
    }



    function splitable() {

        var fid = document.getElementById("spltid").value;
        var amtpre = document.getElementById("spltamtpre").value * 1;
        var amt = document.getElementById("spltamt").value * 1;


        if (amt >= amtpre || amt <= 0 || amt == '') {
            alert('Invalid Amount');
            const input = document.getElementById("spltamt");
            input.focus();
            input.select();
            return;
        }

        var infor = "fid=" + fid + "&amt=" + amt + "&tail=1";
        // alert(infor);

        $("#history-end").html("");
        $.ajax({
            type: "POST",
            url: "payments/stfinance-item-split.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#history-end").html('<i class="bi bi-arrow-repeat"></i> Spliting...');
            },
            success: function (html) {
                $("#history-end").html(html);
                var modalEl = document.getElementById('exampleModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                let stid = $('#fin-stid').text().trim();
                // alert(stid);
                setCookie('payment-stid', stid);
                showToast('info', 'Amount Split into two indivisual record.' + stid, 'Split Amount');
                setTimeout(function () { window.location.reload(); }, 500);


            }
        });
    }



    function mergerow(id, amt, tail) {
        event.stopPropagation();
        var infor = "fid=" + id + "&amt=" + amt + "&tail=2";
        // alert(infor);
        $("#history-end").html("");
        $.ajax({
            type: "POST",
            url: "payments/stfinance-item-split.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#history-end").html('<i class="bi bi-arrow-repeat"></i> Merging...');
            },
            success: function (html) {
                $("#history-end").html(html);
                let stid = $('#fin-stid-merge').text().trim();
                // alert('stid=' + stid);
                setCookie('payment-stid', stid);
                showToast('info', 'Amount Merged into Main Item' + stid, 'Merged');
                setTimeout(function () { window.location.reload(); }, 500);
            }
        });
    }

</script>

<script>
    function rollback(id, taka, tail) {
        var infor = "id=" + id + "&taka=" + taka + "&tail=" + tail;
        // alert(infor);
        $("#bbttnn" + id).html("");
        $.ajax({
            type: "POST",
            url: "payments/roll-back-st-finance-item.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#bbttnn" + id).html('<i class="bi bi-arrow-repeat"></i>');
            },
            success: function (html) {
                $("#bbttnn" + id).html(html);
            }
        });
    }
</script>

<script>
    if (<?= $needSync ?> > 0) {
        $('.studentInfo').css('background-color', '#ffc107');
    }
</script>

<script>
    const button = document.getElementById("mybtn");
    const input = document.getElementById("spltamt");
    input.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            button.click();
        }
    });
</script>