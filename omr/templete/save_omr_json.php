<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Data'
    ]);
    exit;
}

$omrName = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['omrName']);

$jsonData = $data['jsonData'];

$directory = dirname(dirname(dirname(__DIR__))) . "/playconsole/omr/templete/";

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$filePath = $directory . $omrName . ".json";

$jsonContent = json_encode(
    $jsonData,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if (file_put_contents($filePath, $jsonContent)) {

    echo json_encode([
        'status' => 'success' 
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'File Save Failed'
    ]);
}
?>