<?php
/**
 * EIMBox REST API - Admin Support & User Ticket Management
 * Endpoint: /api/v1/admin/tickets.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$method = $_SERVER['REQUEST_METHOD'];
$input = get_api_input();

switch ($method) {
    case 'GET':
        $status = trim($_GET['status'] ?? '');
        $sccode = trim($_GET['sccode'] ?? '');

        $tickets = [
            [
                'id' => 'TCK-2026-108',
                'sccode' => '108742',
                'scname' => 'EIMBOX MODEL HIGH SCHOOL',
                'subject' => 'Issue in Tabulating Sheet Grade Auto-Calculation',
                'category' => 'Examination',
                'priority' => 'High',
                'status' => 'In-Progress',
                'created_by' => 'admin@eimboxschool.edu.bd',
                'created_at' => '2026-08-29 09:30:12',
                'last_reply' => 'Our support engineers are reviewing the 4th subject continuous assessment rule.'
            ],
            [
                'id' => 'TCK-2026-109',
                'sccode' => '108743',
                'scname' => 'UTTARA RESIDENTIAL COLLEGE',
                'subject' => 'Request for Additional 50,000 SMS Masking Credit',
                'category' => 'Billing / SMS',
                'priority' => 'Medium',
                'status' => 'Open',
                'created_by' => 'principal@uttaracollege.edu.bd',
                'created_at' => '2026-08-29 11:20:00',
                'last_reply' => 'Awaiting invoice generation & payment verification.'
            ],
            [
                'id' => 'TCK-2026-107',
                'sccode' => '108744',
                'scname' => 'CHITTAGONG IDEAL HIGH SCHOOL',
                'subject' => 'Biometric Fingerprint Device Port Connection Timeout',
                'category' => 'Hardware / Attendance',
                'priority' => 'Normal',
                'status' => 'Resolved',
                'created_by' => 'headmaster@ctgideal.edu.bd',
                'created_at' => '2026-08-27 15:45:00',
                'last_reply' => 'ZKTeco USB driver was re-configured and verified operational.'
            ]
        ];

        if (!empty($status)) {
            $tickets = array_values(array_filter($tickets, fn($t) => strtolower($t['status']) === strtolower($status)));
        }
        if (!empty($sccode)) {
            $tickets = array_values(array_filter($tickets, fn($t) => $t['sccode'] === $sccode));
        }

        api_response('success', 'Support tickets loaded', $tickets);
        break;

    case 'POST':
        $ticketId = trim($input['ticket_id'] ?? '');
        $status = trim($input['status'] ?? 'Resolved');
        $reply = trim($input['reply_text'] ?? '');

        if (empty($ticketId)) {
            api_response('error', 'Ticket ID is required.', null, 400);
        }

        api_response('success', "Ticket $ticketId updated to $status", [
            'ticket_id' => $ticketId,
            'status' => $status,
            'reply' => $reply,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
