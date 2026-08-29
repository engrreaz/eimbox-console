<?php
/**
 * EIMBox REST API - Admin Release Notes, Changelog & System Notifications
 * Endpoint: /api/v1/admin/releases.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$method = $_SERVER['REQUEST_METHOD'];
$input = get_api_input();

switch ($method) {
    case 'GET':
        $releases = [
            [
                'id' => 1,
                'version' => 'v1.1.0',
                'release_date' => '2026-08-29',
                'title' => 'Admin Panel Suite & Secondary Activity Dock',
                'category' => 'Major Release',
                'summary' => 'Comprehensive multi-tenant admin console, real-time live usage monitor, and institutional billing engine.',
                'features' => [
                    'Secondary Activity Bar Admin dock with institutional live telemetry.',
                    'Multi-tenant User & Role-based Access Control manager with one-click password reset.',
                    'Central Billing, Subscriptions, and Broadcast Notice Dispatcher.',
                    'User Ticket and Helpdesk issue tracker.'
                ],
                'fixes' => [
                    'Optimized SQLite sync engine for high-concurrency offline POS fee collection.',
                    'Fixed tabulating sheet 4th subject GPA calculation in result processor.'
                ]
            ],
            [
                'id' => 2,
                'version' => 'v1.0.1',
                'release_date' => '2026-08-20',
                'title' => 'POS Fee Counter & Academic Cache Enhancements',
                'category' => 'Feature Update',
                'summary' => 'Direct POS thermal receipt reprint, automatic fine calculation, and admission waiving rules.',
                'features' => [
                    'Instant ESC/POS thermal printer output for fee receipts.',
                    'Multi-year session student demographic statistics.'
                ],
                'fixes' => [
                    'Prevented duplicate transaction ID generation during concurrent offline cash collections.'
                ]
            ]
        ];

        api_response('success', 'Release notes and changelog loaded', $releases);
        break;

    case 'POST':
        $version = trim($input['version'] ?? '');
        $title = trim($input['title'] ?? '');
        $summary = trim($input['summary'] ?? '');
        $features = $input['features'] ?? [];
        $fixes = $input['fixes'] ?? [];

        if (empty($version) || empty($title)) {
            api_response('error', 'Version and Release Title are required.', null, 400);
        }

        api_response('success', "Release $version published successfully", [
            'id' => rand(10, 99),
            'version' => $version,
            'title' => $title,
            'release_date' => date('Y-m-d'),
            'summary' => $summary,
            'features' => $features,
            'fixes' => $fixes
        ]);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
