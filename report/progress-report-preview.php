<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if(isset($_GET['preview'])){
    $settings = $_GET;
}else{
    $sccode = mysqli_real_escape_string($conn,$_GET['sccode']);
    $q = mysqli_query($conn,"SELECT admin_data FROM scinfo WHERE sccode='$sccode'");
    $row = mysqli_fetch_assoc($q);
    $settings = json_decode($row['admin_data'] ?? '', true);
}

function g($k,$d=''){ global $settings; return $settings[$k] ?? $d; }
?>

<style>
body{background:#eee}
.report{
    width:210mm;
    min-height:297mm;
    margin:auto;
    background:#fff;
    padding:20px;
    position:relative;
}
.watermark{
    position:absolute;
    opacity:.08;
    font-size:120px;
    transform:rotate(-30deg);
    top:35%;
    left:10%;
}
</style>

<div class="report">

<?php if(g('watermark')=='1'): ?>
<div class="watermark">SCHOOL</div>
<?php endif; ?>

<!-- Header -->
<div style="transform:scale(<?= g('header_scale','1') ?>); transform-origin:top;">
<?php
$hf = g('report_header');
if($hf && file_exists($hf)) include $hf;
?>
</div>

<!-- Text Image -->
<?php if($img=g('report_text_image')): ?>
<img src="<?= $img ?>" style="width:100%;margin:10px 0">
<?php endif; ?>

<hr>

<h3>Student Name:
<?php if(g('student_name_en')) echo 'John Doe '; ?>
<?php if(g('student_name_bn')) echo '(জন ডো)'; ?>
</h3>

<?php if(g('student_photo')): ?>
<img src="demo-student.jpg" width="120">
<?php endif; ?>

<?php if(g('attendance_info')): ?>
<p>Attendance: 92%</p>
<?php endif; ?>

<?php if(g('highest_mark')): ?>
<p>Highest Mark: 98</p>
<?php endif; ?>

</div>