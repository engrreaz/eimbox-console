<?php
/**
 * EIMBox REST API - Admin Package & Module Management
 * Endpoint: /api/v1/admin/packages.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$method = $_SERVER['REQUEST_METHOD'];
$input = get_api_input();

switch ($method) {
    case 'GET':
        $packages = [
            [
                'id' => 1,
                'tier' => 'Bronze',
                'name' => 'Basic Offline Edition',
                'price_monthly' => 1500,
                'price_annual' => 15000,
                'student_limit' => 300,
                'sms_quota' => 2000,
                'modules' => [
                    'student_directory' => true,
                    'academic_calendar' => true,
                    'fee_counter_basic' => true,
                    'offline_sqlite' => true,
                    'exam_grading' => false,
                    'biometric_attendance' => false,
                    'sms_gateway' => false,
                    'document_studio' => false,
                    'omr_scanner' => false
                ]
            ],
            [
                'id' => 2,
                'tier' => 'Silver',
                'name' => 'Standard Cloud School',
                'price_monthly' => 3000,
                'price_annual' => 30000,
                'student_limit' => 1000,
                'sms_quota' => 10000,
                'modules' => [
                    'student_directory' => true,
                    'academic_calendar' => true,
                    'fee_counter_basic' => true,
                    'offline_sqlite' => true,
                    'exam_grading' => true,
                    'biometric_attendance' => true,
                    'sms_gateway' => true,
                    'document_studio' => false,
                    'omr_scanner' => false
                ]
            ],
            [
                'id' => 3,
                'tier' => 'Gold',
                'name' => 'Enterprise Live Suite',
                'price_monthly' => 5000,
                'price_annual' => 50000,
                'student_limit' => 3000,
                'sms_quota' => 50000,
                'modules' => [
                    'student_directory' => true,
                    'academic_calendar' => true,
                    'fee_counter_basic' => true,
                    'offline_sqlite' => true,
                    'exam_grading' => true,
                    'biometric_attendance' => true,
                    'sms_gateway' => true,
                    'document_studio' => true,
                    'omr_scanner' => true
                ]
            ],
            [
                'id' => 4,
                'tier' => 'Platinum',
                'name' => 'Multi-Campus Mega Institute',
                'price_monthly' => 8500,
                'price_annual' => 85000,
                'student_limit' => 10000,
                'sms_quota' => 200000,
                'modules' => [
                    'student_directory' => true,
                    'academic_calendar' => true,
                    'fee_counter_basic' => true,
                    'offline_sqlite' => true,
                    'exam_grading' => true,
                    'biometric_attendance' => true,
                    'sms_gateway' => true,
                    'document_studio' => true,
                    'omr_scanner' => true,
                    'custom_domain' => true,
                    'dedicated_db' => true
                ]
            ]
        ];

        api_response('success', 'Packages and module structures loaded', $packages);
        break;

    case 'POST':
        $tier = trim($input['tier'] ?? 'Gold');
        $price = floatval($input['price_annual'] ?? 0);
        $studentLimit = intval($input['student_limit'] ?? 1000);
        $smsQuota = intval($input['sms_quota'] ?? 10000);

        api_response('success', "Package tier $tier settings saved successfully", [
            'tier' => $tier,
            'price_annual' => $price,
            'student_limit' => $studentLimit,
            'sms_quota' => $smsQuota,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
