<?php
/**
 * EIMBox REST API - Certificate Templates, Testimonials & TC Generator
 * Endpoint: /api/v1/documents/template-manage.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'templates';

switch ($method) {
    case 'GET':
        if ($action === 'templates') {
            $templates = [
                ['id' => 1, 'title' => 'Official Academic Testimonial (প্রশংসাপত্র)', 'type' => 'Testimonial', 'paper_size' => 'A4 Landscape', 'updated_at' => '2026-08-01'],
                ['id' => 2, 'title' => 'Transfer Certificate (ছাড়পত্র / টিসি)', 'type' => 'TC', 'paper_size' => 'A4 Portrait', 'updated_at' => '2026-08-01'],
                ['id' => 3, 'title' => 'Student Character Certificate (চারিত্রিক সনদ)', 'type' => 'Certificate', 'paper_size' => 'A4 Landscape', 'updated_at' => '2026-07-15'],
                ['id' => 4, 'title' => 'Official Dispatch / Reference Letter (স্মারক পত্র)', 'type' => 'Dispatch', 'paper_size' => 'A4 Portrait', 'updated_at' => '2026-07-20']
            ];
            api_response('success', 'Document templates loaded', $templates);
        } elseif ($action === 'generate_testimonial') {
            $stid = $_GET['stid'] ?? '';
            $stmt = $conn->prepare("SELECT s.stid, s.stname, s.fathername, s.mothername, s.dob, s.gpa, s.class, s.section
                FROM students s WHERE s.stid = ? AND s.sccode = ? LIMIT 1");
            $stmt->bind_param("ss", $stid, $sccode);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            
            api_response('success', 'Testimonial data generated', [
                'sl_no' => 'TEST-2026-' . rand(100, 999),
                'session' => '2025-2026',
                'student' => $student,
                'conduct' => 'Satisfactory and Moral Character is Good',
                'passed_exam' => 'Secondary School Certificate (SSC)',
                'board' => 'Dhaka'
            ]);
        }
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Template layout saved successfully', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
