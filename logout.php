<?php
require_once 'core/init.php';
session_destroy();
setcookie('remember_me','',time()-3600,'/','',true,true);
header('Location: login.php');
exit;
