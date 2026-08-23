<?php
/**
 * EIMBox REST API - Super Admin Panel & Central Tenant Management
 * Endpoint: /api/v1/settings/super-admin.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'institutions';

switch ($method) {
    case 'GET':
        if ($action === 'institutions') {
            $institutions = [
                ['id' => 1, 'sccode' => '108742', 'scname' => 'EIMBOX MODEL HIGH SCHOOL', 'eiin' => '108742', 'admin_email' => 'admin@eimboxschool.edu.bd', 'mobile' => '01711000000', 'plan' => 'Enterprise Live', 'status' => 'Active', 'expiry' => '2027-12-31', 'dues' => 0],
                ['id' => 2, 'sccode' => '108743', 'scname' => 'UTTARA RESIDENTIAL COLLEGE', 'eiin' => '108743', 'admin_email' => 'principal@uttaracollege.edu.bd', 'mobile' => '01811000001', 'plan' => 'Standard Cloud', 'status' => 'Active', 'expiry' => '2026-11-30', 'dues' => 5000],
                ['id' => 3, 'sccode' => '108744', 'scname' => 'CHITTAGONG IDEAL HIGH SCHOOL', 'eiin' => '108744', 'admin_email' => 'headmaster@ctgideal.edu.bd', 'mobile' => '01911000002', 'plan' => 'Basic Offline', 'status' => 'Renewal Due', 'expiry' => '2026-08-31', 'dues' => 12000]
            ];
            api_response('success', 'Central institutions loaded', $institutions);
        } elseif ($action === 'invoices') {
            $invoices = [
                ['inv_no' => 'INV-2026-001', 'sccode' => '108742', 'scname' => 'EIMBOX MODEL HIGH SCHOOL', 'amount' => 36000, 'plan' => 'Annual SaaS Renewal', 'status' => 'Paid', 'date' => '2026-01-05'],
                ['inv_no' => 'INV-2026-002', 'sccode' => '108743', 'scname' => 'UTTARA RESIDENTIAL COLLEGE', 'amount' => 24000, 'plan' => 'SMS Gateway Pack (50k SMS)', 'status' => 'Paid', 'date' => '2026-03-12']
            ];
            api_response('success', 'Subscription invoices loaded', $invoices);
        }
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Institution onboarding / plan update successful', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
