<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';
$exam = $_POST['exam'] ?? '';
$classname = $_POST['classname'] ?? '';
$sectionname = $_POST['sectionname'] ?? '';

$siSlot = $_POST['sessioninfoSlot'] ?? '';
$siSession = $_POST['sessioninfoSession'] ?? '';

$where = [];

/* ---- tabulatingsheet filters (ts) ---- */
if ($slot)
    $where[] = "ts.slot='$slot'";
if ($session)
    $where[] = "ts.sessionyear='$session'";
if ($exam)
    $where[] = "ts.exam='$exam'";
if ($classname)
    $where[] = "ts.classname='$classname'";
if ($sectionname)
    $where[] = "ts.sectionname='$sectionname'";

/* ---- sessioninfo filters (si) ---- */
if ($siSlot)
    $where[] = "si.slot='$siSlot'";
if ($siSession)
    $where[] = "si.sessionyear='$siSession'";

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$whereSQL .= " AND ts.sccode='$sccode'";
$sql = "
    SELECT 
        ts.meritnumcomb,
        ts.meritnum,
        ts.stid,
        ts.rollno AS ts_roll,
        si.classname AS si_class,
        si.sectionname AS si_section,
        si.rollno AS si_roll
    FROM tabulatingsheet ts
    LEFT JOIN sessioninfo si 
        ON ts.stid = si.stid
    $whereSQL
    ORDER BY ts.meritnumcomb ASC
";
echo $sql;
$res = mysqli_query($conn, $sql);

if (!$res) {
    echo '<tr><td colspan="6" class="text-danger">' . mysqli_error($conn) . '</td></tr>';
    exit;
}

while ($row = mysqli_fetch_assoc($res)) {

    $rollno = $row['si_roll'] ?: $row['ts_roll'];

    // check conditions
    $meritCombCheck = ($rollno == $row['meritnumcomb'])
        ? ' <span class="text-success fw-bold">✔</span>'
        : '';

    $meritNumCheck = ($rollno == $row['meritnum'])
        ? ' <span class="text-success fw-bold">✔</span>'
        : '';

    if ($meritCombCheck == '' && $meritNumCheck == '') {
        $errorIcon = ' <span class="text-danger fw-bold">✖</span>';
    } else {
        $errorIcon = '';
    }

    echo "<tr>
        <td>{$row['meritnumcomb']}{$meritCombCheck}</td>
        <td>{$row['meritnum']}{$meritNumCheck}</td>
        <td>{$row['stid']}</td>
        <td>{$rollno}{$errorIcon}</td>
        <td>{$row['si_class']}</td>
        <td>{$row['si_section']}</td>
    </tr>";
}

