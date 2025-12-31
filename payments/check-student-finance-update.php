<?php
/* ================================
   PAYABLE AMOUNT (individual setup)
================================ */
$paya = $amt;

$iix = array_search($itemcode, array_column($finsetupind, 'itemcode'));
if ($iix !== false) {
    $paya = $finsetupind[$iix]['amount'];
}

// new-only rule
if (!empty($new_only) && $new_only == 1 && $new_admi == 0) {
    $paya = 0;
}

/* ================================
   INSERT / UPDATE
================================ */
$idmon = '';

if ($stfinid == 0) {

    $sql = "INSERT INTO stfinance 
        (sccode, sessionyear, classname, sectionname, stid, rollno, partid,
         itemcode, particulareng, particularben, amount, month, idmon,
         setupdate, setupby, payableamt, modifieddate, paid, dues,
         last_update, validate, validationtime)
        VALUES
        ('$sccode','$syear','$cls','$sec','$stid','$roll','$partid',
         '$itemcode','$partex','$partbx','$amt','$z','$idmon',
         '$cur','$usr','$paya','$cur',0,'$paya',
         '$cur',1,'$cur')";

    $conn->query($sql);
    $new++;

} else {

    if ($paid > 0) {

        $conn->query("UPDATE stfinance SET validate=1 WHERE id='$stfinid'");
        $noneed++;

    } else {

        $sql = "UPDATE stfinance SET
                    validate=1,
                    amount='$amt',
                    payableamt='$paya',
                    dues=payableamt - paid,
                    modifieddate='$cur',
                    modifiedby='$usr'
                WHERE id='$stfinid'";

        $conn->query($sql);
        $update++;
    }
}
