<?php
echo "=== TESTING EIMBOX CMS API v1 ENDPOINTS ===\n\n";

function testFile($file, $method = 'GET', $get = [], $post = []) {
    echo ">> Testing: " . basename($file) . " [$method]\n";
    $script = '<?php ' .
        '$_SERVER["REQUEST_METHOD"] = "' . $method . '"; ' .
        '$_SERVER["HTTP_HOST"] = "console.eimbox.com"; ' .
        '$_SERVER["HTTP_AUTHORIZATION"] = "Bearer EBX-SECURE-SYNC-2026"; ' .
        '$_SERVER["HTTP_X_EIMBOX_EIIN"] = "105135"; ' .
        '$_GET = ' . var_export($get, true) . '; ' .
        '$_POST = ' . var_export($post, true) . '; ' .
        'require "' . str_replace('\\', '/', $file) . '";';

    $tempFile = __DIR__ . '/temp_runner.php';
    file_put_contents($tempFile, $script);
    $out = shell_exec('php -d display_errors=0 ' . escapeshellarg($tempFile));
    @unlink($tempFile);

    $json = json_decode($out, true);
    if ($json && isset($json['status'])) {
        echo "[PASS] Status: " . $json['status'] . "\n";
        echo "Message: " . $json['message'] . "\n";
        echo "Latency: " . ($json['latency'] ?? 'N/A') . "\n";
        echo "Data preview: " . json_encode(array_slice($json['data'] ?? [], 0, 3), JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "[WARN] Output:\n" . substr($out, 0, 400) . "\n\n";
    }
}

testFile(__DIR__ . '/../api/cms/v1/health.php', 'GET', ['eiin' => '105135']);
testFile(__DIR__ . '/../api/cms/v1/faculty.php', 'GET', ['eiin' => '105135']);
testFile(__DIR__ . '/../api/cms/v1/pull.php', 'POST', ['eiin' => '105135'], ['school_eiin' => '105135', 'module' => 'all']);
testFile(__DIR__ . '/../api/cms/v1/push.php', 'POST', ['eiin' => '105135'], [
    'school_eiin' => '105135',
    'data' => [
        'staff' => [['staff_id' => 'T-1', 'name_bn' => 'পরীক্ষা শিক্ষক']]
    ]
]);
testFile(__DIR__ . '/../api/cms/v1/index.php', 'GET', ['eiin' => '105135']);
