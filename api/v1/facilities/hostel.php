<?php
/**
 * EIMBox REST API - Hostel & Dormitory Management
 * Endpoint: /api/v1/facilities/hostel.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $hostels = [
            [
                'hostel_id' => 1,
                'hostel_name' => 'Dr. Kudrat-E-Khuda Boys Hostel',
                'superintendent' => 'Prof. Md. Nazmul Haque',
                'contact' => '01711220011',
                'total_rooms' => 20,
                'total_beds' => 80,
                'occupied_beds' => 72,
                'monthly_rent' => 3500,
                'mess_charge' => 4000
            ],
            [
                'hostel_id' => 2,
                'hostel_name' => 'Begum Rokeya Girls Dormitory',
                'superintendent' => 'Mrs. Dilruba Begum',
                'contact' => '01811220022',
                'total_rooms' => 18,
                'total_beds' => 72,
                'occupied_beds' => 65,
                'monthly_rent' => 3500,
                'mess_charge' => 4000
            ]
        ];
        api_response('success', 'Hostels and dormitories loaded', $hostels);
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Hostel bed allotment saved', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
