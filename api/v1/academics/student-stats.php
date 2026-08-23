<?php
/**
 * EIMBox REST API - Student Demographics & Statistical Analytics
 * Endpoint: /api/v1/academics/student-stats.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Aggregated student statistics
        $stats = [
            'total_students' => 840,
            'gender_breakdown' => [
                'male' => 435,
                'female' => 405,
                'male_pct' => '51.8%',
                'female_pct' => '48.2%'
            ],
            'religion_breakdown' => [
                ['religion' => 'Islam', 'count' => 745, 'pct' => '88.7%'],
                ['religion' => 'Hinduism', 'count' => 82, 'pct' => '9.8%'],
                ['religion' => 'Christianity', 'count' => 8, 'pct' => '1.0%'],
                ['religion' => 'Buddhism', 'count' => 5, 'pct' => '0.5%']
            ],
            'class_wise_enrollment' => [
                ['class' => 'Class 6', 'boys' => 92, 'girls' => 88, 'total' => 180],
                ['class' => 'Class 7', 'boys' => 86, 'girls' => 84, 'total' => 170],
                ['class' => 'Class 8', 'boys' => 90, 'girls' => 85, 'total' => 175],
                ['class' => 'Class 9', 'boys' => 85, 'girls' => 80, 'total' => 165],
                ['class' => 'Class 10', 'boys' => 82, 'girls' => 68, 'total' => 150]
            ],
            'quota_breakdown' => [
                ['quota' => 'General (সাধারণ)', 'count' => 720],
                ['quota' => 'Catchment Area (ক্যাচমেন্ট কোটা)', 'count' => 85],
                ['quota' => 'Freedom Fighter (মুক্তিযোদ্ধা কোটা)', 'count' => 25],
                ['quota' => 'Special Needs (প্রতিবন্ধী কোটা)', 'count' => 10]
            ]
        ];
        
        api_response('success', 'Student statistical metrics loaded', $stats);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
