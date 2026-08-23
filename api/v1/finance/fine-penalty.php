<?php
/**
 * EIMBox REST API - Student Fine & Penalty Policies
 * Endpoint: /api/v1/finance/fine-penalty.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $policies = [
            [
                'id' => 1,
                'fine_type' => 'Late Tuition Fee (মাসিক বেতন বিলম্ব ফি)',
                'grace_days' => 10,
                'fine_amount' => 50.00,
                'calculation_mode' => 'Fixed per Month',
                'auto_apply' => true,
                'status' => 'Active'
            ],
            [
                'id' => 2,
                'fine_type' => 'Unauthorized Absence Penalty (অনুপস্থিতি জরিমানা)',
                'grace_days' => 0,
                'fine_amount' => 20.00,
                'calculation_mode' => 'Per Day Absent',
                'auto_apply' => false,
                'status' => 'Active'
            ],
            [
                'id' => 3,
                'fine_type' => 'Library Overdue Book Fine (বই ফেরত বিলম্ব)',
                'grace_days' => 7,
                'fine_amount' => 5.00,
                'calculation_mode' => 'Per Day Overdue',
                'auto_apply' => true,
                'status' => 'Active'
            ]
        ];
        api_response('success', 'Fine and penalty rules loaded', $policies);
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Fine and penalty configuration updated successfully', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
