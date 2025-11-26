<?php
header("Access-Control-Allow-Origin: https://console.eimbox.com");
header("Content-Type: image/jpeg");
// readfile("students/1000000001.jpg");

include_once('core-val.php');
$folder = dirname(dirname(__DIR__)) . "/students";

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$limit = 3;
$offset = $page * $limit;

$searchName = strtolower($_GET['name'] ?? "");
$searchSize = intval($_GET['size'] ?? 0);

$files = glob($folder . "/*.{jpg,jpeg,png,gif}", GLOB_BRACE);

$filtered = [];
foreach($files as $f){
    $filename = basename($f);
    $sizeKB = round(filesize($f)/1024);

    if($searchName !== "" && strpos(strtolower($filename), $searchName) === false){
        continue;
    }
    if($sizeKB < $searchSize){
        continue;
    }

    $filtered[] = $f;
}

$chunk = array_slice($filtered, $offset, $limit);

$baseURL = BASE_PATH . "students/";

foreach($chunk as $img){
    $filename = basename($img);
    $sizeKB = round(filesize($img)/1024);

    // get image resolution
    $imgSize = getimagesize($img); // returns [width, height, type, attr]
    $width = $imgSize[0];
    $height = $imgSize[1];

    $url = $baseURL . $filename;

    echo "<tr data-filename='$filename'>
        <td><img class='lazy' data-src='$url' style='height:50px; border:1px solid #ccc;'></td>
        <td>$filename</td>  <td>{$width}x{$height}</td> 
        <td>$sizeKB KB</td>
      
        <td><button class='btn btn-sm btn-success editBtn'>Edit</button></td>
    </tr>";
}
