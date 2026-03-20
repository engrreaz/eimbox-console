<?php 
session_start();
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';
require_once dirname(__DIR__) . '/core/functions.php';


$id = $_POST['id'] ?? '';

if($id){
    echo student_profile_image_path($id);
}