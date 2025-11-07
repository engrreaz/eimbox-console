<?php
// Fixed SQL file
$sqlFile = 'dump.sql';

if(!isset($_POST['searchText'])) exit('Invalid request');

$search = $_POST['searchText'];

if(!file_exists($sqlFile)) exit('SQL file not found');

$handle = fopen($sqlFile, "r");
if(!$handle) exit('Cannot open SQL file');

$count = 0;
echo '<table class="table table-sm table-bordered">';
echo '<tr><th>Line</th><th>Text</th><th>Table (if SQL)</th></tr>';

$lineNumber = 0;
while(($line = fgets($handle)) !== false){
    $lineNumber++;
    if(stripos($line, $search) !== false){
        $count++;
        $table = '';
        if(preg_match('/\b(INTO|TABLE|FROM)\s+([a-zA-Z0-9_]+)/i', $line, $matches)){
            $table = $matches[2];
        }
        echo '<tr><td>'.$lineNumber.'</td><td>'.htmlspecialchars($line).'</td><td>'.$table.'</td></tr>';
    }
}
fclose($handle);

echo '</table>';
echo "<p>Total occurrences: $count</p>";
