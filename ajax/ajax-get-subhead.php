<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$type = $_POST['type'] ?? 'Expenditure';

if ($type === 'Income') {
    $where_type = "s.income = 1";
} else {
    $where_type = "s.expenditure = 1";
}

$sql = "SELECT s.id, s.sub_head, s.account_head_id, COALESCE(h.account_head, 'Unassigned') AS account_head
        FROM account_sub_head s
        LEFT JOIN account_head h ON s.account_head_id = h.id
        WHERE s.sccode = '$sccode' AND $where_type
        ORDER BY h.account_head ASC, s.sub_head ASC";

$res = $conn->query($sql);

echo '<option value="">Select Account Sector (Sub-Head)</option>';

if ($res && $res->num_rows > 0) {
    $current_head = '';
    while ($sh = $res->fetch_assoc()) {
        if ($current_head !== $sh['account_head']) {
            if ($current_head !== '') {
                echo '</optgroup>';
            }
            echo '<optgroup label="' . htmlspecialchars($sh['account_head']) . '">';
            $current_head = $sh['account_head'];
        }
        echo '<option value="' . $sh['id'] . '" data-head="' . $sh['account_head_id'] . '">' . htmlspecialchars($sh['sub_head']) . '</option>';
    }
    if ($current_head !== '') {
        echo '</optgroup>';
    }
}
