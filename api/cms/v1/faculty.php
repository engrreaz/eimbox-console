<?php
/**
 * Route: /api/cms/v1/faculty or /api/cms/v1/faculty.php
 * Method: GET
 * Purpose: Fetch Live Faculty & Teachers List from EIMBOX Central Console Database
 */

require_once __DIR__ . '/bootstrap.php';

$auth = authenticate_cms_request($conn ?? null);
$eiin = $auth['eiin'];
$input = $auth['input'];

$staffType = trim((string)($input['type'] ?? 'teacher'));
$dept = trim((string)($input['dept'] ?? ''));

$teachers = [];

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $where = "sccode = ? AND (status = '1' OR status = 'YES' OR status = 'Active' OR status = '' OR status IS NULL)";
    $params = [$eiin];
    $types = "s";

    if (!empty($dept)) {
        $where .= " AND (subjects LIKE ? OR position LIKE ?)";
        $params[] = "%{$dept}%";
        $params[] = "%{$dept}%";
        $types .= "ss";
    }

    $sql = "SELECT id, sl, tid, tname, tnameb, position, mobile, email, ranks, subjects, gender, dob, jdate, mpoindex, status 
            FROM teacher 
            WHERE $where 
            ORDER BY sl ASC, ranks ASC, id ASC";

    $stmt = @$conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $tid = (string)$row['tid'];
            $photoUrl = '';
            if (file_exists(__DIR__ . '/../../../teacher/' . $tid . '.jpg')) {
                $photoUrl = 'https://console.eimbox.com/teacher/' . $tid . '.jpg';
            }

            $teachers[] = [
                'staff_id'       => $tid,
                'name_bn'        => !empty($row['tnameb']) ? $row['tnameb'] : $row['tname'],
                'name_en'        => $row['tname'],
                'designation_bn' => !empty($row['position']) ? $row['position'] : 'সহকারী শিক্ষক',
                'staff_type'     => (str_contains(mb_strtolower($row['position'] ?? ''), 'কর্মচারী') || str_contains(mb_strtolower($row['position'] ?? ''), 'অফিস')) ? 'staff' : 'teacher',
                'phone'          => $row['mobile'] ?? '',
                'email'          => $row['email'] ?? '',
                'department'     => !empty($row['subjects']) ? $row['subjects'] : 'সাধারণ',
                'order_priority' => intval($row['sl'] ?? $row['ranks'] ?? 99),
                'photo_url'      => $photoUrl,
                'status'         => 'active',
                'join_date'      => $row['jdate'] ?? '',
                'mpo_index'      => $row['mpoindex'] ?? '',
            ];
        }
        $stmt->close();
    }
}

$msg = empty($teachers) 
    ? "সেন্ট্রাল ডেটাবেজে এই প্রতিষ্ঠানের (EIIN: {$eiin}) কোনো শিক্ষক নিবন্ধিত পাওয়া যায়নি।"
    : "সেন্ট্রাল ডাটাবেজ থেকে শিক্ষক তালিকা সফলভাবে লোড করা হয়েছে।";

cms_api_response('success', $msg, [
    'eiin'        => $eiin,
    'total_count' => count($teachers),
    'teachers'    => $teachers
], 200);
