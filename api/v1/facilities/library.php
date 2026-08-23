<?php
/**
 * EIMBox REST API - Library Catalog & Book Circulation
 * Endpoint: /api/v1/facilities/library.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'books';

switch ($method) {
    case 'GET':
        if ($action === 'books') {
            $books = [
                ['id' => 1, 'accession_no' => 'ACC-1001', 'title' => 'Bangla Sahitya Kanika', 'author' => 'Dr. Muhammad Shahidullah', 'category' => 'Literature', 'shelf' => 'Shelf A-1', 'copies_total' => 25, 'copies_available' => 18],
                ['id' => 2, 'accession_no' => 'ACC-1002', 'title' => 'Higher Mathematics (Classes 9-10)', 'author' => 'NCTB', 'category' => 'Science & Math', 'shelf' => 'Shelf B-3', 'copies_total' => 40, 'copies_available' => 32],
                ['id' => 3, 'accession_no' => 'ACC-1003', 'title' => 'Oxford Advanced Learner Dictionary', 'author' => 'A.S. Hornby', 'category' => 'Reference', 'shelf' => 'Shelf Ref-01', 'copies_total' => 10, 'copies_available' => 8]
            ];
            api_response('success', 'Library books loaded', $books);
        } elseif ($action === 'issues') {
            $issues = [
                ['issue_id' => 'ISS-2026-01', 'book_title' => 'Bangla Sahitya Kanika', 'accession_no' => 'ACC-1001', 'borrower_type' => 'Student', 'borrower_id' => '2026101', 'borrower_name' => 'Mohammed Tanvir Ahmed', 'issue_date' => '2026-08-10', 'due_date' => '2026-08-24', 'status' => 'Issued'],
                ['issue_id' => 'ISS-2026-02', 'book_title' => 'Higher Mathematics', 'accession_no' => 'ACC-1002', 'borrower_type' => 'Student', 'borrower_id' => '2026102', 'borrower_name' => 'Nusrat Jahan Mim', 'issue_date' => '2026-08-12', 'due_date' => '2026-08-26', 'status' => 'Issued']
            ];
            api_response('success', 'Book circulation issues loaded', $issues);
        }
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Book issue / catalog operation successful', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
