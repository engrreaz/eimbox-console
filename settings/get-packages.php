<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$current = $_POST['current'] ?? '';

$q = mysqli_query($conn, "SELECT id, package_name, description FROM packages WHERE status='active' ORDER BY id");

while ($row = mysqli_fetch_assoc($q)) {
    $checked = ($row['id'] == $current) ? 'checked' : '';
    echo '
    <label class="list-group-item d-flex">
        <input class="form-check-input me-2" type="radio" name="package" value="'.$row['id'].'" '.$checked.'>
        <div><span class="fs-6 fw-bold">'.$row['package_name'].'</span><br><span class"fs-small text-secondary">'.$row['description'].'</span>
    </div></label>';
}