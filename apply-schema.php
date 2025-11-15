<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

require_once 'core/config.php';

$rev = $_SESSION['reverse'] ?? 0;
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
$dbname = ($rev == 0) ? (defined('DB_SYNC') ? DB_SYNC : DB_NAME) : DB_NAME;

$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo '<div class="alert alert-danger">❌ Connection failed: ' . htmlspecialchars($conn_sync->connect_error) . '</div>';
    exit;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Read JSON from php://input
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['action'])) {
    echo '<div class="alert alert-danger">❌ Invalid JSON data</div>';
    $conn_sync->close();
    exit;
}

$action = $data['action'];

// ------------------ APPLY TABLE ----------------------
if ($action === 'apply-table') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $createSQL = html_entity_decode(trim($data['sql'] ?? ''), ENT_QUOTES);

    if (!$table || stripos($createSQL,'CREATE TABLE')!==0){
        echo '<div class="alert alert-danger">❌ Invalid table or SQL</div>';
        $conn_sync->close(); exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
    if ($checkTable && $checkTable->num_rows>0){
        echo "<div class='alert alert-warning'>⚠️ Table '".h($table)."' already exists</div>";
        $conn_sync->close(); exit;
    }

    if ($conn_sync->query($createSQL)){
        echo "<div class='alert alert-success'>✅ Table '".h($table)."' created successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ MySQL Error: ".h($conn_sync->error)."</div>";
    }

    $conn_sync->close(); exit;
}

// ------------------ APPLY SINGLE COLUMN ----------------
if ($action === 'apply-column') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $columnDef = html_entity_decode(trim($data['column'] ?? ''), ENT_QUOTES);

    if (!$table || !$columnDef){
        echo '<div class="alert alert-danger">❌ Missing table or column</div>';
        $conn_sync->close(); exit;
    }

    preg_match('/^`([^`]+)`/',$columnDef,$m);
    $colName = $m[1] ?? '';
    if (!$colName){ echo '<div class="alert alert-danger">❌ Invalid column definition</div>'; $conn_sync->close(); exit; }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
    if (!$checkTable || $checkTable->num_rows===0){ echo "<div class='alert alert-danger'>❌ Table '".h($table)."' not found</div>"; $conn_sync->close(); exit; }

    $checkCol = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '".$conn_sync->real_escape_string($colName)."'");
    $colExists = ($checkCol && $checkCol->num_rows>0);

    if ($colExists){
        $current = $checkCol->fetch_assoc();
        $currentType = $current['Type'];
        $currentNull = $current['Null'];
        $currentDefault = $current['Default'];

        // Backup
        $bakCol = $colName.'_bak_'.date('Ymd_His');
        $bakCheck = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '".$conn_sync->real_escape_string($bakCol)."'");
        if ($bakCheck && $bakCheck->num_rows>0) $bakCol.='_'.substr(md5(rand()),0,6);

        $bakDefParts = [$currentType];
        $bakDefParts[]=($currentNull==='NO')?'NOT NULL':'NULL';
        if($currentDefault!==null) $bakDefParts[]="DEFAULT ".$conn_sync->real_escape_string($currentDefault);
        $bakDef = implode(' ',$bakDefParts);

        if(!$conn_sync->query("ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}")){
            echo "<div class='alert alert-danger'>❌ Failed to create backup '{$bakCol}': ".h($conn_sync->error)."</div>";
            $conn_sync->close(); exit;
        }

        if(!$conn_sync->query("UPDATE `{$table}` SET `{$bakCol}`=`{$colName}`")){
            echo "<div class='alert alert-danger'>❌ Failed to copy data to backup '{$bakCol}'</div>";
            $conn_sync->close(); exit;
        }

        $modifySQL="ALTER TABLE `{$table}` MODIFY {$columnDef}";
        if($conn_sync->query($modifySQL)){
            echo "<div class='alert alert-success'>🔄 Column '".h($table).".".h($colName)."' modified. Backup: '".h($bakCol)."'</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error modifying '".h($table).".".h($colName)."': ".h($conn_sync->error)."</div>";
            echo "<div class='alert alert-warning'>⚠️ Old data preserved in backup column '".h($bakCol)."'</div>";
        }

    } else {
        $addSQL="ALTER TABLE `{$table}` ADD {$columnDef}";
        if($conn_sync->query($addSQL)){
            echo "<div class='alert alert-success'>🆕 Column '".h($table).".".h($colName)."' added</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error adding '".h($table).".".h($colName)."': ".h($conn_sync->error)."</div>";
        }
    }

    $conn_sync->close(); exit;
}

// ------------------ APPLY SELECTED COLUMNS ----------------
if ($action==='apply-selected'){
    $items=$data['items'] ?? [];
    if(!is_array($items)||count($items)===0){ echo "<div class='alert alert-danger'>❌ No columns selected</div>"; $conn_sync->close(); exit; }

    foreach($items as $item){
        $table=$conn_sync->real_escape_string($item['table'] ?? '');
        $columnDef=html_entity_decode(trim($item['column'] ?? ''),ENT_QUOTES);
        if(!$table || !$columnDef) continue;

        preg_match('/^`([^`]+)`/',$columnDef,$m);
        $colName=$m[1]??'';
        if(!$colName) continue;

        $checkTable=$conn_sync->query("SHOW TABLES LIKE '{$table}'");
        if(!$checkTable||$checkTable->num_rows===0) continue;

        $checkCol=$conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '".$conn_sync->real_escape_string($colName)."'");
        $colExists=($checkCol && $checkCol->num_rows>0);

        if($colExists){
            $current=$checkCol->fetch_assoc();
            $currentType=$current['Type'];
            $newDefClean=strtoupper($columnDef);

            if(strpos($newDefClean,strtoupper($currentType))!==false){ echo "<div class='alert alert-info'>ℹ️ No change for '".h($table).".".h($colName)."'</div>"; continue; }

            $bakCol=$colName.'_bak_'.date('Ymd_His');
            $bakCheck=$conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '".$conn_sync->real_escape_string($bakCol)."'");
            if($bakCheck && $bakCheck->num_rows>0) $bakCol.='_'.substr(md5(rand()),0,6);

            $currentNull=$current['Null'];
            $currentDefault=$current['Default'];
            $bakDefParts=[$currentType];
            $bakDefParts[]=($currentNull==='NO')?'NOT NULL':'NULL';
            if($currentDefault!==null) $bakDefParts[]="DEFAULT ".$conn_sync->real_escape_string($currentDefault);
            $bakDef=implode(' ',$bakDefParts);

            if(!$conn_sync->query("ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}")) continue;
            if(!$conn_sync->query("UPDATE `{$table}` SET `{$bakCol}`=`{$colName}`")) continue;

            $modifySQL="ALTER TABLE `{$table}` MODIFY {$columnDef}";
            if($conn_sync->query($modifySQL)){
                echo "<div class='alert alert-success'>🔄 Updated '".h($table).".".h($colName)."' (MODIFY). Backup: '".h($bakCol)."'</div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error updating '".h($table).".".h($colName)."': ".h($conn_sync->error)."</div>";
                echo "<div class='alert alert-warning'>⚠️ Old data preserved in backup '".h($bakCol)."'</div>";
            }

        } else {
            $addSQL="ALTER TABLE `{$table}` ADD {$columnDef}";
            $conn_sync->query($addSQL);
        }
    }

    $conn_sync->close(); exit;
}

ob_end_clean();
echo '<div class="alert alert-danger">❌ Invalid action</div>';
$conn_sync->close();
exit;
