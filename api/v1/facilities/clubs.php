<?php
/**
 * EIMBox REST API - Clubs & Co-Curricular Activities
 * Endpoint: /api/v1/facilities/clubs.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $clubs = [
            [
                'club_id' => 1,
                'club_name' => 'EIMBox Science & Robotics Club',
                'mentor_teacher' => 'Md. Farhad Hossain (Physics Dept)',
                'member_count' => 64,
                'category' => 'Science & Tech',
                'meeting_day' => 'Every Thursday 03:30 PM'
            ],
            [
                'club_id' => 2,
                'club_name' => 'National Scout & Girl Guides Troop',
                'mentor_teacher' => 'Kamrul Hasan (Physical Education)',
                'member_count' => 120,
                'category' => 'Scouting & Discipline',
                'meeting_day' => 'Every Saturday 08:00 AM'
            ],
            [
                'club_id' => 3,
                'club_name' => 'English & Bengali Debating Society',
                'mentor_teacher' => 'Mrs. Nazmun Nahar (English)',
                'member_count' => 45,
                'category' => 'Literary & Public Speaking',
                'meeting_day' => 'Every Tuesday 03:30 PM'
            ]
        ];
        api_response('success', 'Co-curricular clubs loaded', $clubs);
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Club registration updated', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
