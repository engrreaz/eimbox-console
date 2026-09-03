<?php
echo "=== TESTING EIMBOX CMS API v1 ENDPOINTS ===\n\n";

function runEndpointTest($endpointFile, $method, $params = [], $body = []) {
    echo ">> Testing: " . basename($endpointFile) . " [$method]\n";
    $cmd = 'php -d display_errors=0 -r "' .
        '\$_SERVER[\'REQUEST_METHOD\'] = \'' . $method . '\'; ' .
        '\$_SERVER[\'HTTP_HOST\'] = \'console.eimbox.com\'; ' .
        '\$_SERVER[\'HTTP_AUTHORIZATION\'] = \'Bearer EBX-SECURE-SYNC-2026\'; ' .
        '\$_SERVER[\'HTTP_X_EIMBOX_EIIN\'] = \'105135\'; ' .
        '\$_GET = ' . var_export($params, true) . '; ' .
        '\$_POST = ' . var_export($body, true) . '; ' .
        'require \'' . addslashes(str_replace('\\', '/', $endpointFile)) . '\';' .
        '"';

    $output = shell_exec($cmd);
    $json = json_decode($output, true);
    if ($json && isset($json['status'])) {
        echo "[PASS] Response Status: " . $json['status'] . "\n";
        echo "Message: " . $json['message'] . "\n";
        echo "Latency: " . ($json['latency'] ?? 'N/A') . "\n";
        echo "Data preview: " . json_encode(array_slice($json['data'] ?? [], 0, 3), JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "[WARN] Output:\n" . substr($output, 0, 300) . "\n\n";
    }
}

// 1. Test Health
runEndpointTest(__DIR__ . '/../api/cms/v1/health.php', 'GET', ['eiin' => '105135']);

// 2. Test Faculty
runEndpointTest(__DIR__ . '/../api/cms/v1/faculty.php', 'GET', ['eiin' => '105135']);

// 3. Test Pull
runEndpointTest(__DIR__ . '/../api/cms/v1/pull.php', 'POST', ['eiin' => '105135'], ['school_eiin' => '105135', 'module' => 'all']);

// 4. Test Push
runEndpointTest(__DIR__ . '/../api/cms/v1/push.php', 'POST', ['eiin' => '105135'], [
    'school_eiin' => '105135',
    'data' => [
        'staff' => [['staff_id' => 'T-1', 'name_bn' => 'পরীক্ষা শিক্ষক']]
    ]
]);

// 5. Test Router Gateway
runEndpointTest(__DIR__ . '/../api/cms/v1/index.php', 'GET', ['eiin' => '105135']);
