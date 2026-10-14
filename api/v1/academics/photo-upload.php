<?php
/**
 * EIMBox REST API — Student & Teacher Photo and Signature Upload Endpoint
 * Route: POST /api/v1/academics/photo-upload.php
 */
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Optional Token Extraction (Non-terminating)
$user = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$token = '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} elseif (!empty($authHeader)) {
    $token = trim($authHeader);
}

if (!empty($token)) {
    $parts = explode('.', $token);
    if (count($parts) === 2) {
        $payloadJson = base64_decode($parts[0]);
        $signature = $parts[1];
        $expectedSig = hash_hmac('sha256', $payloadJson, 'EIMBox_Secret_Key_2026_Studio');
        if (hash_equals($expectedSig, $signature)) {
            $payload = json_decode($payloadJson, true);
            if ($payload && (isset($payload['uid']) || isset($payload['sccode']))) {
                $uid = intval($payload['uid'] ?? 0);
                $payloadSccode = intval($payload['sccode'] ?? 0);
                if ($uid > 0) {
                    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE id = ? LIMIT 1");
                    if ($stmt) {
                        $stmt->bind_param('i', $uid);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $user = $res->fetch_assoc();
                        $stmt->close();
                    }
                }
                if (!$user && $payloadSccode > 0) {
                    $user = ['sccode' => $payloadSccode];
                }
            }
        }
    }
}

$input = get_api_input();
$sccode = intval($_POST['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);
$rawType = strtolower(trim($_POST['type'] ?? $input['type'] ?? 'student')); // 'student', 'teacher', 'sign', 'signature', 'teacher_sign'
$id = trim($_POST['stid'] ?? $_POST['tid'] ?? $_POST['id'] ?? $input['stid'] ?? $input['tid'] ?? $input['id'] ?? '');

$isSign = in_array($rawType, ['sign', 'signature', 'teacher_sign', 'teachersign']);
$type = $isSign ? 'sign' : ($rawType === 'teacher' ? 'teacher' : 'student');

if (empty($id)) {
    api_response('error', 'Valid ID (stid / tid) is required.', null, 400);
}

// Fallback sccode resolution if 0
if ($sccode <= 0) {
    if ($type === 'teacher' || $type === 'sign') {
        $sRes = $conn->query("SELECT sccode FROM teacher WHERE tid = '" . $conn->real_escape_string($id) . "' OR id = '" . $conn->real_escape_string($id) . "' LIMIT 1");
        if ($sRes && $sRow = $sRes->fetch_assoc()) $sccode = intval($sRow['sccode']);
    } else {
        $sRes = $conn->query("SELECT sccode FROM students WHERE stid = '" . $conn->real_escape_string($id) . "' LIMIT 1");
        if ($sRes && $sRow = $sRes->fetch_assoc()) $sccode = intval($sRow['sccode']);
    }
    if ($sccode <= 0) {
        $scRes = $conn->query("SELECT sccode FROM scinfo LIMIT 1");
        if ($scRes && $scRow = $scRes->fetch_assoc()) $sccode = intval($scRow['sccode']);
    }
}

$binaryData = null;

// 1. Check if multipart $_FILES is provided
if (isset($_FILES['file']) && !empty($_FILES['file']['tmp_name'])) {
    $tmp = $_FILES['file']['tmp_name'];
    $binaryData = file_get_contents($tmp);
} elseif (isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
    $tmp = $_FILES['photo']['tmp_name'];
    $binaryData = file_get_contents($tmp);
} elseif (isset($_FILES['sign']) && !empty($_FILES['sign']['tmp_name'])) {
    $tmp = $_FILES['sign']['tmp_name'];
    $binaryData = file_get_contents($tmp);
} else {
    // 2. Check for Base64 Data URL or raw base64 string
    $rawBase64 = $_POST['sign_base64'] ?? $_POST['sign_data'] ?? $_POST['sign_path'] ?? $_POST['photo_base64'] ?? $_POST['photo_data'] ?? $_POST['photo_path'] ?? $_POST['base64'] ?? $_POST['image'] ?? $input['sign_base64'] ?? $input['sign_data'] ?? $input['sign_path'] ?? $input['photo_base64'] ?? $input['photo_data'] ?? $input['photo_path'] ?? $input['base64'] ?? $input['image'] ?? '';
    if (!empty($rawBase64)) {
        if (str_contains($rawBase64, ';base64,')) {
            $parts = explode(';base64,', $rawBase64);
            $cleanStr = str_replace(' ', '+', trim($parts[1]));
            $binaryData = base64_decode($cleanStr);
        } else {
            $cleanStr = str_replace(' ', '+', trim($rawBase64));
            $binaryData = base64_decode($cleanStr);
        }
    }
}

if (!$binaryData || strlen($binaryData) < 10) {
    api_response('error', 'No valid image data or file was provided.', null, 400);
}

// Project root directory
$rootDir = dirname(dirname(dirname(__DIR__))); // eimbox-materio

$targetRelativePath = '';
$publicUrl = '';

// 3. Process and Save Image File
$srcImg = @imagecreatefromstring($binaryData);

if ($isSign) {
    // Teacher Signature single canonical upload folder: sign/[sccode]/[tid].png
    $targetDir = $rootDir . '/sign/' . $sccode;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);

    if ($srcImg) {
        $width = imagesx($srcImg);
        $height = imagesy($srcImg);
        $pngImg = imagecreatetruecolor($width, $height);
        imagealphablending($pngImg, false);
        imagesavealpha($pngImg, true);
        $transparent = imagecolorallocatealpha($pngImg, 255, 255, 255, 127);
        imagefilledrectangle($pngImg, 0, 0, $width, $height, $transparent);
        imagecopy($pngImg, $srcImg, 0, 0, 0, 0, $width, $height);
        imagedestroy($srcImg);

        imagepng($pngImg, $targetDir . '/' . $id . '.png');
        imagedestroy($pngImg);
    } else {
        @file_put_contents($targetDir . '/' . $id . '.png', $binaryData);
    }

    $targetRelativePath = "sign/{$sccode}/{$id}.png";
    $publicUrl = "https://eimbox.com/sign/{$sccode}/{$id}.png";

    // Safely update teacher modifieddate
    try {
        @$conn->query("UPDATE teacher SET modifieddate = NOW() WHERE (tid = '" . $conn->real_escape_string($id) . "' OR id = '" . $conn->real_escape_string($id) . "') AND (sccode = $sccode OR sccode = 0)");
    } catch (Throwable $e) {}

    api_response('success', 'Teacher signature uploaded and saved successfully as transparent PNG.', [
        'sccode' => $sccode,
        'id' => $id,
        'type' => 'sign',
        'sign_id' => "{$id}.png",
        'sign_path' => $targetRelativePath,
        'sign_url' => $publicUrl,
        'saved_at' => date('Y-m-d H:i:s')
    ]);
}

if ($type === 'teacher') {
    // Teacher photo single canonical upload folder: teacher/[sccode]/[tid].webp
    $targetDir = $rootDir . '/teacher/' . $sccode;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
    
    if ($srcImg) {
        $width = imagesx($srcImg);
        $height = imagesy($srcImg);
        $webpImg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($webpImg, 255, 255, 255);
        imagefilledrectangle($webpImg, 0, 0, $width, $height, $white);
        imagecopy($webpImg, $srcImg, 0, 0, 0, 0, $width, $height);
        imagedestroy($srcImg);

        if (function_exists('imagewebp')) {
            imagewebp($webpImg, $targetDir . '/' . $id . '.webp', 85);
        } else {
            @file_put_contents($targetDir . '/' . $id . '.webp', $binaryData);
        }
        imagedestroy($webpImg);
    } else {
        @file_put_contents($targetDir . '/' . $id . '.webp', $binaryData);
    }

    $targetRelativePath = "teacher/{$sccode}/{$id}.webp";
    $publicUrl = "https://eimbox.com/teacher/{$sccode}/{$id}.webp";

    // Safely update teacher modifieddate
    try {
        @$conn->query("UPDATE teacher SET modifieddate = NOW() WHERE (tid = '" . $conn->real_escape_string($id) . "' OR id = '" . $conn->real_escape_string($id) . "') AND (sccode = $sccode OR sccode = 0)");
    } catch (Throwable $e) {}
} else {
    // Student photo single canonical upload folder: students/[sccode]/[stid].webp
    $targetDir = $rootDir . '/students/' . $sccode;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
    
    if ($srcImg) {
        $width = imagesx($srcImg);
        $height = imagesy($srcImg);
        $webpImg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($webpImg, 255, 255, 255);
        imagefilledrectangle($webpImg, 0, 0, $width, $height, $white);
        imagecopy($webpImg, $srcImg, 0, 0, 0, 0, $width, $height);
        imagedestroy($srcImg);

        if (function_exists('imagewebp')) {
            imagewebp($webpImg, $targetDir . '/' . $id . '.webp', 85);
        } else {
            @file_put_contents($targetDir . '/' . $id . '.webp', $binaryData);
        }
        imagedestroy($webpImg);
    } else {
        @file_put_contents($targetDir . '/' . $id . '.webp', $binaryData);
    }

    $targetRelativePath = "students/{$sccode}/{$id}.webp";
    $publicUrl = "https://eimbox.com/students/{$sccode}/{$id}.webp";

    // Safely update students modifieddate
    try {
        @$conn->query("UPDATE students SET modifieddate = NOW() WHERE stid = '" . $conn->real_escape_string($id) . "' AND (sccode = $sccode OR sccode = 0)");
    } catch (Throwable $e) {}
}

api_response('success', 'Photo uploaded and saved successfully.', [
    'sccode' => $sccode,
    'id' => $id,
    'type' => $type,
    'photo_id' => "{$id}.webp",
    'photo_path' => $targetRelativePath,
    'photo_url' => $publicUrl,
    'saved_at' => date('Y-m-d H:i:s')
]);



