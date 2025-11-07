<?php 
require_once 'vendor/autoload.php'; // mPDF autoload

use Mpdf\Mpdf;
use Mpdf\Config\FontVariables;

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new Mpdf([
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/fonts']),
    'fontdata' => $fontData + [
        'solaimanlipi' => [
            'R' => 'solaimanlipi.ttf',
            'useOTL' => 0xFF, // optional, বাংলা ঠিক দেখানোর জন্য
        ]
    ],
    'default_font' => 'solaimanlipi',
    'format' => 'A4'
]);
