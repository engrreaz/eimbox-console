<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: text/html; charset=utf-8');

// Input
$mode = intval($_POST['mode'] ?? 0);
$id = intval($_POST['id'] ?? 0);
$accno = intval($_POST['accno'] ?? 0);
$date = $_POST['date'] ?? '';
$type = trim($_POST['type'] ?? '');
$chq = trim($_POST['chq'] ?? '');
$amt = floatval($_POST['amt'] ?? 0);

if (!in_array($mode, [1, 2, 3])) {
    echo "<div class='alert alert-danger'>Invalid mode</div>";
    exit;
}

/* ------------------------------------------------------
   UTIL: Get last transaction (slno + balance)
--------------------------------------------------------*/
function getLastBalance($conn, $sccode, $accno)
{
    $q = $conn->prepare("
        SELECT slno, balance 
        FROM banktrans 
        WHERE sccode=? AND accno=? AND verified=1
        ORDER BY slno DESC LIMIT 1
    ");
    $q->bind_param("si", $sccode, $accno);
    $q->execute();
    $r = $q->get_result();

    if ($r->num_rows > 0)
        return $r->fetch_assoc(); // ['slno'=>xx, 'balance'=>yy]

    return ['slno' => 0, 'balance' => 0];
}

/* ------------------------------------------------------
   MODE 1: ADD NEW TRANSACTION
--------------------------------------------------------*/
/* ------------------------------------------------------
   MODE 1: ADD OR UPDATE TRANSACTION
--------------------------------------------------------*/
if ($mode === 1) {

    if (empty($accno) || empty($date) || $amt <= 0) {
        echo "<div class='alert alert-warning'>Provide account, date & positive amount.</div>";
        exit;
    }

    // last balance + slno
    $last = getLastBalance($conn, $sccode, $accno);
    $opening = floatval($last['balance']);
    $nextSlno = intval($last['slno']) + 1;

    // New balance calculation
    if (in_array($type, ['Deposit', 'Interest']))
        $balance = $opening + $amt;
    else
        $balance = $opening - $amt;

    /* ---------------------------------------------
       CASE A: id > 0   →   UPDATE existing record
    --------------------------------------------- */
    if ($id > 0) {

        // check record exists
        $chk = $conn->prepare("SELECT id FROM banktrans WHERE id=? AND sccode=?");
        $chk->bind_param("is", $id, $sccode);
        $chk->execute();
        $rs = $chk->get_result();

        if ($rs->num_rows == 0) {
            echo "<div class='alert alert-danger'>Record not found for update.</div>";
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE banktrans
            SET 
                slno=?, 
                date=?, 
                transtype=?, 
                chqno=?, 
                amount=?, 
                transopening=?, 
                balance=?, 
                entryby=?, 
                entrytime=NOW()
            WHERE id=? AND sccode=?
        ");
        $stmt->bind_param(
            "isssdddiss",
            $nextSlno,
            $date,
            $type,
            $chq,
            $amt,
            $opening,
            $balance,
            $entryby,
            $id,
            $sccode
        );

        $ok = $stmt->execute();

        if ($ok)
            echo "<div class='alert alert-success'>Transaction updated successfully.</div>";
        else
            echo "<div class='alert alert-danger'>Update error: " . htmlspecialchars($stmt->error) . "</div>";

        exit;
    }

    /* ---------------------------------------------
       CASE B: id == 0   →   INSERT new record
    --------------------------------------------- */
    $stmt = $conn->prepare("
        INSERT INTO banktrans 
        (sccode, accno, slno, date, transtype, chqno, amount, transopening, balance, entryby, entrytime)
        VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param(
        "siisssddds",
        $sccode,
        $accno,
        $nextSlno,
        $date,
        $type,
        $chq,
        $amt,
        $opening,
        $balance,
        $entryby
    );
    $ok = $stmt->execute();

    if ($ok)
        echo "<div class='alert alert-success'>Transaction added.</div>";
    else
        echo "<div class='alert alert-danger'>Insert error: " . htmlspecialchars($stmt->error) . "</div>";

    exit;
}


/* ------------------------------------------------------
   MODE 2: UPDATE + VERIFY
--------------------------------------------------------*/
if ($mode === 2) {

    if ($id <= 0) {
        echo "<div class='alert alert-danger'>Invalid transaction ID.</div>";
        exit;
    }

    // Get the original transaction
    $g = $conn->prepare("SELECT * FROM banktrans WHERE id=? AND sccode=?");
    $g->bind_param("is", $id, $sccode);
    $g->execute();
    $gr = $g->get_result();

    if ($gr->num_rows == 0) {
        echo "<div class='alert alert-warning'>Transaction not found.</div>";
        exit;
    }

    $orig = $gr->fetch_assoc();

    // Last slno + balance for this ACC
    $last = getLastBalance($conn, $sccode, $accno);
    $opening = floatval($last['balance']);
    $nextSlno = intval($last['slno']) + 1;

    // New balance after verify
    if (in_array($type, ['Deposit', 'Interest']))
        $balance = $opening + $amt;
    else
        $balance = $opening - $amt;

    // Update record
    $stmt = $conn->prepare("
        UPDATE banktrans 
        SET 
            slno=?, 
            date=?, 
            transtype=?, 
            chqno=?, 
            amount=?, 
            transopening=?, 
            balance=?, 
            verified=1, 
            verifytime=NOW(), 
            entryby=?
        WHERE id=? AND sccode=?
    ");

    $stmt->bind_param(
        "isssdddsii",
        $nextSlno,
        $date,
        $type,
        $chq,
        $amt,
        $opening,
        $balance,
        $entryby,
        $id,
        $sccode
    );

    $ok = $stmt->execute();

    if ($ok)
        echo "<div class='alert alert-success'>Transaction updated & verified.</div>";
    else
        echo "<div class='alert alert-danger'>Update failed: " . htmlspecialchars($stmt->error) . "</div>";

    exit;
}

/* ------------------------------------------------------
   MODE 3: DELETE (if unverified)
--------------------------------------------------------*/
if ($mode === 3) {

    if ($id <= 0) {
        echo "<div class='alert alert-danger'>Invalid ID for delete.</div>";
        exit;
    }

    $chk = $conn->prepare("SELECT verified FROM banktrans WHERE id=? AND sccode=?");
    $chk->bind_param("is", $id, $sccode);
    $chk->execute();
    $cres = $chk->get_result();

    if ($cres->num_rows == 0) {
        echo "<div class='alert alert-warning'>Transaction not found.</div>";
        exit;
    }

    $verified = intval($cres->fetch_assoc()['verified']);

    if ($verified === 1) {
        echo "<div class='alert alert-danger'>Cannot delete a verified transaction.</div>";
        exit;
    }

    $d = $conn->prepare("DELETE FROM banktrans WHERE id=? AND sccode=?");
    $d->bind_param("is", $id, $sccode);
    $ok = $d->execute();

    if ($ok)
        echo "<div class='alert alert-success'>Transaction deleted.</div>";
    else
        echo "<div class='alert alert-danger'>Delete failed: " . htmlspecialchars($d->error) . "</div>";

    exit;
}
?>