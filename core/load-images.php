<?php
// CORS (allow image usage inside Cropper from console.eimbox.com)
header("Access-Control-Allow-Origin: https://console.eimbox.com");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html; charset=UTF-8");

// Required config
include_once('core-val.php');

// Folder path
$folder = dirname(dirname(__DIR__)) . "/students";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$limit = 5;
$offset = $page * $limit;

// Filters
$searchName = strtolower($_GET['name'] ?? "");
$searchSize = intval($_GET['size'] ?? 0);
$minWidth = intval($_GET['w'] ?? 0);
$minHeight = intval($_GET['h'] ?? 0);

// Fetch image files
$files = glob($folder . "/*.{jpg,jpeg,png,gif}", GLOB_BRACE);

$filtered = [];

foreach ($files as $filePath) {

    $filename = basename($filePath);
    $sizeKB = round(filesize($filePath) / 1024);

    // Filename filter
    if ($searchName !== "" && strpos(strtolower($filename), $searchName) === false) {
        continue;
    }

    // File size filter
    if ($searchSize > 0 && $sizeKB < $searchSize) {
        continue;
    }

    // Resolution filter
    $info = @getimagesize($filePath);
    if (!$info)
        continue;

    $width = $info[0];
    $height = $info[1];

    if ($minWidth > 0 && $width < $minWidth)
        continue;
    if ($minHeight > 0 && $height < $minHeight)
        continue;

    $filtered[] = [
        "path" => $filePath,
        "name" => $filename,
        "sizeKB" => $sizeKB,
        "width" => $width,
        "height" => $height
    ];
}

// Pagination result
$chunk = array_slice($filtered, $offset, $limit);

// Base URL
$baseURL = BASE_PATH . "students/";

// Output rows
foreach ($chunk as $img) {

    $filename = $img['name'];
    $sizeKB = $img['sizeKB'];
    $width = $img['width'];
    $height = $img['height'];

    $url = $baseURL . $filename;

    echo "<tr 
            data-filename='$filename' 
            data-fullsrc='$url' 
            data-width='$width' 
            data-height='$height' 
            data-size='$sizeKB'
        >
        <td><img class='lazy' data-src='$url' style='height:50px; border:1px solid #ccc;'></td>
        <td>$filename</td>  
        <td>{$width}×{$height}</td> 
        <td>{$sizeKB} KB</td>

        <td>
            <button class='btn btn-sm btn-info viewBtn'>View</button>
            <button class='btn btn-sm btn-success editBtn'>Edit</button>
        </td>
    </tr>";
}
