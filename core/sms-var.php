<?php
// EIMBox SMS Variable Mapping & Dynamic Tag Engine

$td = $td ?? date('Y-m-d');
$cur = $cur ?? date('Y-m-d H:i:s');
$insname = $insname ?? 'EIMBox Model School & College';

$sms_hint = [
    '[[INSTITUTE_NAME]]',
    '[[STUDENT_NAME]]',
    '[[STUDENT_NAME_ENG]]',
    '[[STUDENT_NAME_BEN]]',
    '[[GUARDIAN_NAME]]',
    '[[CLASS_NAME]]',
    '[[SECTION_NAME]]',
    '[[ROLL_NO]]',
    '[[DUE_AMOUNT]]',
    '[[PAYMENT_AMOUNT]]',
    '[[PAID_AMOUNT]]',
    '[[RECEIPT_NO]]',
    '[[PAYMENT_DATE]]',
    '[[IN_TIME]]',
    '[[OUT_TIME]]',
    '[[DATE]]',
    '[[TIME]]',
    '[[MONTH]]',
    '[[MONTH_NAME]]',
    '[[EXAM_NAME]]',
    '[[GPA_GRADE]]',
    '[[TEACHER_NAME]]',
    '[[MEETING_TIME]]',
    '[[CUR]]'
];

$sms_var = [
    '$insname',
    '$stnameeng',
    '$stnameeng',
    '$stnameben',
    '$guarname',
    '$classname',
    '$sectionname',
    '$rollno',
    '$dueamount',
    '$paymentamount',
    '$paymentamount',
    '$receiptno',
    '$paymentdate',
    '$intime',
    '$outtime',
    '$td',
    '$time_now',
    '$month',
    '$monthname',
    '$examname',
    '$gpagrade',
    '$teachername',
    '$meetingtime',
    '$cur'
];

$sms_desc = [
    'Institue Name',
    'Student Name (English)',
    'Student Name (English)',
    'Student Name (Bengali)',
    'Guardian Name',
    'Class Name',
    'Section Name',
    'Roll Number',
    'Total Due Amount',
    'Payment Amount',
    'Paid Amount',
    'Money Receipt No',
    'Payment Date',
    'In Time',
    'Out Time',
    'Current / Relevant Date',
    'Current Time',
    'Month (Numeric)',
    'Month Name (e.g. September)',
    'Exam Name',
    'GPA & Grade (e.g. 5.00 / A+)',
    'Teacher / Staff Name',
    'Meeting Time & Venue',
    'Current Date & Time'
];

$sms_sample = [
    'EIMBox Model School & College',
    'Labib Shahriar',
    'Labib Shahriar',
    'লাবিব শাহরিয়ার',
    'Md. Abdus Sattar',
    'Nine',
    'Padma',
    '12',
    '1,250.00',
    '650.00',
    '650.00',
    'MR-2026-0891',
    $td,
    '08:15 AM',
    '02:00 PM',
    $td,
    date('h:i A'),
    date('m'),
    date('F'),
    'Annual Examination',
    'GPA: 5.00 (A+)',
    'Mr. Rafiqul Islam',
    'সকাল ১০:০০ ঘটিকায় (শিক্ষক মিলনায়তন)',
    $cur
];