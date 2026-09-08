<?php
/* ================================
   PAYABLE AMOUNT & WAIVERS
================================ */
$paya = $amt;

$month_eng = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$month_ben = ['', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

// Individual Student Custom Setup Override
if (!empty($finsetupind)) {
    $iix = array_search($itemcode, array_column($finsetupind, 'itemcode'));
    if ($iix !== false) {
        $paya = floatval($finsetupind[$iix]['amount']);
    }
}

// New Admission Only Rule (if item is only for new students, skip for existing students)
if (!empty($new_only) && $new_only == 1 && $new_admi == 0) {
    return; // Don't generate dues for old/continuing students
}

/* ================================
   INSERT / UPDATE LOGIC
================================ */
$idmon = $stid . '-' . $partid . '-' . $z;

if ($stfinid == 0) {
    // Determine item title with month suffix if multi-month frequency
    if ($item_repeat > 1 && isset($month_eng[$z])) {
        $partexx = $partex . ' | ' . $month_eng[$z];
        $partbxx = $partbx . ' | ' . $month_ben[$z];
    } else {
        $partexx = $partex;
        $partbxx = $partbx;
    }

    $partexx_esc = $conn->real_escape_string($partexx);
    $partbxx_esc = $conn->real_escape_string($partbxx);

    $sql = "INSERT INTO stfinance 
        (sccode, sessionyear, classname, sectionname, stid, rollno, partid,
         itemcode, sub_head, particulareng, particularben, amount, month, idmon,
         setupdate, setupby, payableamt, modifieddate, paid, dues,
         last_update, validate, validationtime)
        VALUES
        ('$sccode','$syear','$cls','$sec','$stid','$roll','$partid',
         '$itemcode','$sub_head','$partexx_esc','$partbxx_esc','$amt','$z','$idmon',
         '$cur','$usr','$paya','$cur',0,'$paya',
         '$cur',1,'$cur')";

    if ($conn->query($sql)) {
        $new++;
    }

} else {

    // If money has already been paid or split, NEVER overwrite amount or paid values
    if ($paid > 0 || $splitid_check > 0 || $splitid_check_2 > 0) {
        $conn->query("UPDATE stfinance 
                      SET validate=1, sub_head='$sub_head' 
                      WHERE id='$stfinid'");
        $noneed++;
    } else {
        // Safe to recalculate unpaid dues
        $sql = "UPDATE stfinance SET
                    validate=1,
                    sub_head='$sub_head',
                    amount='$amt',
                    payableamt='$paya',
                    dues=payableamt - paid,
                    modifieddate='$cur',
                    modifiedby='$usr'
                WHERE id='$stfinid'";

        if ($conn->query($sql)) {
            $update++;
        }
    }
}