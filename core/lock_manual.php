<?php
session_start();
$_SESSION['locked'] = true;

http_response_code(200);
