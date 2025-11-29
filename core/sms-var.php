<?php
$sms_hint = [
    '[[STUDENT_NAME_ENG]]',
    '[[STUDENT_NAME_BEN]]',
    '[[GUARDIAN_NAME]]',
    '[[CLASS_NAME]]',
    '[[SECTION_NAME]]',
    '[[DUE_AMOUNT]]',
    '[[PAYMENT_AMOUNT]]',
    '[[PAYMENT_DATE]]',
    '[[IN_TIME]]',
    '[[OUT_TIME]]',
    '[[MONTH]]',
    '[[CUR]]'
];


$sms_var = [
    '$stnameeng',
    '$stnameben',
    '$guarname',
    '$classname',
    '$sectionname',
    '$dueamount',
    '$paymentamount',
    '$paymentdate',
    '$intime',
    '$outtime',
    '$month',
    '$cur'
];

$sms_sample = [
    'Labib Shahriar',
    'লাবিব শাহরিয়ার',
    'Absus Sattar',
    'Nine',
    'Science',
    '120.00',
    '650.00',
    $td,
    $cur,
    $cur,
    date('m'),
    $cur
];