<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$data = $_POST;
unset($data['sccode']);

$json = mysqli_real_escape_string($conn, json_encode($data));

mysqli_query($conn,"
UPDATE scinfo
SET admin_data='$json'
WHERE sccode='$sccode'
");

echo "OK";