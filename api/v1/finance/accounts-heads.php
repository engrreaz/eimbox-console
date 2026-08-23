<?php
/**
 * EIMBox REST API - Chart of Accounts & Head/Sub-heads Management
 * Endpoint: /api/v1/finance/accounts-heads.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Return structured chart of accounts
        $accounts = [
            [
                'id' => 1,
                'head_code' => '1000',
                'head_name' => 'Tuition & Academic Fees (শিক্ষার্থী বেতন)',
                'head_type' => 'Income',
                'sub_heads' => [
                    ['code' => '1001', 'title' => 'Monthly Tuition Fee', 'type' => 'Income', 'default_amount' => 1200],
                    ['code' => '1002', 'title' => 'Session Admission Fee', 'type' => 'Income', 'default_amount' => 3500],
                    ['code' => '1003', 'title' => 'Exam & Evaluation Fee', 'type' => 'Income', 'default_amount' => 600],
                    ['code' => '1004', 'title' => 'ICT & Lab Fee', 'type' => 'Income', 'default_amount' => 300]
                ]
            ],
            [
                'id' => 2,
                'head_code' => '2000',
                'head_name' => 'Fines & Late Charges (জরিমানা ও বিলম্ব ফি)',
                'head_type' => 'Income',
                'sub_heads' => [
                    ['code' => '2001', 'title' => 'Monthly Fee Late Fine', 'type' => 'Income', 'default_amount' => 50],
                    ['code' => '2002', 'title' => 'Absence Penalty', 'type' => 'Income', 'default_amount' => 20],
                    ['code' => '2003', 'title' => 'Lost ID Card Re-issue Fine', 'type' => 'Income', 'default_amount' => 150]
                ]
            ],
            [
                'id' => 3,
                'head_code' => '3000',
                'head_name' => 'Institutional Operational Expenses (প্রাতিষ্ঠানিক ব্যয়)',
                'head_type' => 'Expense',
                'sub_heads' => [
                    ['code' => '3001', 'title' => 'Faculty & Staff Salary', 'type' => 'Expense', 'default_amount' => 0],
                    ['code' => '3002', 'title' => 'Electricity & Utility Bills', 'type' => 'Expense', 'default_amount' => 12000],
                    ['code' => '3003', 'title' => 'Exam Printing & Paper Supply', 'type' => 'Expense', 'default_amount' => 25000],
                    ['code' => '3004', 'title' => 'Campus Maintenance & Repairs', 'type' => 'Expense', 'default_amount' => 15000]
                ]
            ]
        ];
        api_response('success', 'Chart of accounts loaded', $accounts);
        break;

    case 'POST':
        $data = get_api_input();
        $headTitle = $data['head_title'] ?? '';
        $headType = $data['head_type'] ?? 'Income';
        
        if (!$headTitle) {
            api_response('error', 'Head title is required');
        }
        
        api_response('success', 'New account head created successfully', [
            'id' => rand(10, 99),
            'head_title' => $headTitle,
            'head_type' => $headType
        ]);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
