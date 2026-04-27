<?php
require_once '../core/config.php';
require_once '../core/db.php';

$slot = $_POST['slot'] ?? '';
$date = $_POST['date'] ?? '';

?>

<div class="card">
  <div class="card-body">

    <?php include 'events.php'; ?>
    <?php include 'teacher_attendance.php'; ?>
    <?php include 'student_attendance.php'; ?>
    <?php include 'student_performance.php'; ?>
    <?php include 'collection_summary.php'; ?>
    <?php include 'student_collection.php'; ?>
    <?php include 'payment_gateway.php'; ?>
    <?php include 'bank_transaction.php'; ?>
    <?php include 'sms_report.php'; ?>

  </div>
</div>