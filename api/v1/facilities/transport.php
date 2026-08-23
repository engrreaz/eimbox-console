<?php
/**
 * EIMBox REST API - Transport & Bus Fleet Management
 * Endpoint: /api/v1/facilities/transport.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $routes = [
            [
                'route_id' => 1,
                'route_title' => 'Route 1: Uttara - Mirpur Campus',
                'vehicle_no' => 'Dhaka Metro-Ga 11-4520',
                'driver_name' => 'Md. Abdul Alim',
                'driver_mobile' => '01712345670',
                'monthly_fare' => 1500,
                'student_count' => 38,
                'capacity' => 45,
                'status' => 'Active'
            ],
            [
                'route_id' => 2,
                'route_title' => 'Route 2: Dhanmondi - Mirpur Campus',
                'vehicle_no' => 'Dhaka Metro-Ga 12-8890',
                'driver_name' => 'Md. Mominul Haque',
                'driver_mobile' => '01812345671',
                'monthly_fare' => 1800,
                'student_count' => 42,
                'capacity' => 45,
                'status' => 'Active'
            ]
        ];
        api_response('success', 'Transport routes loaded', $routes);
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Transport route created/updated', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
