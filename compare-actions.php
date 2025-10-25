<?php
// compare-actions.php
require_once 'header.php';
// NOTE: header.php already includes your DB config (DB_HOST, DB_USER, DB_PASS, DB_NAME)
// But for actions we want to connect to the REMOTE DB. Configure REMOTE_* constants here or in header.php


// === CONFIGURE REMOTE CONNECTION HERE ===
// If you want to use the same DB as DB_* in header.php, you can set them equal.
// Otherwise replace these with actual remote connection details.
$remoteHost = defined('REMOTE_DB_HOST') ? DB_HOST : (defined('DB_HOST') ? DB_HOST : 'localhost');
$remoteUser = defined('REMOTE_DB_USER') ? DB_USER : (defined('DB_USER') ? DB_USER : 'root');
$remotePass = defined('REMOTE_DB_PASS') ? DB_PASS : (defined('DB_PASS') ? DB_PASS : '');
$remoteDb = defined('REMOTE_DB_NAME') ? 'eimbox' : (defined('DB_NAME') ? DB_NAME : '');

// Create remote mysqli connection
$remoteConn = new mysqli($remoteHost, $remoteUser, $remotePass, $remoteDb);
if ($remoteConn->connect_error) {
    http_response_code(500);
    echo "Remote DB connection failed: " . $remoteConn->connect_error;
    exit;
}

// helpers
function decode_line($s)
{
    // input arrives URL-encoded via fetch; php will decode automatically in $_POST
    return trim($s);
}

$action = $_POST['action'] ?? '';
$table = $_POST['table'] ?? '';
$line = $_POST['line'] ?? '';

if (!$action) {
    echo "No action specified.";
    exit;
}

if ($action === 'sync_table') {
    if (!$table) {
        echo "Table name missing.";
        exit;
    }

    // Read local schema to find CREATE TABLE for this table
    $localFile = __DIR__ . '/schema/localhost.sql';
    if (!file_exists($localFile)) {
        echo "Local schema file missing.";
        exit;
    }
    $sql = file_get_contents($localFile);

    if (!preg_match('/CREATE TABLE\s+`' . preg_quote($table, '/') . '`\s*\((.*?)\)\s*([^;]*);/is', $sql, $m)) {
        echo "CREATE TABLE for '$table' not found in local schema.";
        exit;
    }
    $body = $m[1];
    $trailer = $m[2];

    // Reconstruct CREATE TABLE SQL (safe)
    $createSQL = "CREATE TABLE `$table` (" . $body . ") " . $trailer . ";";

    // If table does not exist in remote, create it
    $res = $remoteConn->query("SHOW TABLES LIKE '" . $remoteConn->real_escape_string($table) . "'");
    if ($res && $res->num_rows === 0) {
        if ($remoteConn->query($createSQL) === TRUE) {
            echo "Created table '$table' on remote successfully.";
        } else {
            echo "Failed to create table '$table': " . $remoteConn->error;
        }
        exit;
    }

    // If table exists, attempt to sync missing columns and keys from local
    // Parse local columns list
    $parts = preg_split('/,(?![^(]*\))/m', $body);
    $messages = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if (!$part)
            continue;
        if (preg_match('/^`([^`]+)`\s+(.*)$/s', $part, $cm)) {
            $col = $cm[1];
            $def = $cm[2];
            // Check if column exists in remote
            $colEsc = $remoteConn->real_escape_string($col);
            $chk = $remoteConn->query("SHOW COLUMNS FROM `" . $remoteConn->real_escape_string($table) . "` LIKE '" . $colEsc . "'");
            if ($chk && $chk->num_rows === 0) {
                // add column
                $sqlAdd = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD COLUMN $def";
                if ($remoteConn->query($sqlAdd) === TRUE) {
                    $messages[] = "Added column `$col`.";
                } else {
                    $messages[] = "Failed to add `$col`: " . $remoteConn->error;
                }
            } else {
                // column exists; attempt modify to match local definition
                // Use MODIFY COLUMN to update type/default/nullable/extra
                $sqlMod = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` MODIFY COLUMN $def";
                if ($remoteConn->query($sqlMod) === TRUE) {
                    $messages[] = "Modified column `$col` to match local.";
                } else {
                    $messages[] = "Modify failed for `$col` (may be identical or incompatible): " . $remoteConn->error;
                }
            }
        } else {
            // key/constraint lines: try to apply basic ones (PRIMARY KEY, UNIQUE KEY, KEY)
            $keyLine = $part;
            // PRIMARY KEY
            if (preg_match('/PRIMARY KEY\s*\((.*?)\)/i', $keyLine, $pm)) {
                $colsList = $pm[1];
                // Check if table already has primary key (SHOW KEYS)
                $havePK = false;
                $resk = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = 'PRIMARY'");
                if ($resk && $resk->num_rows > 0)
                    $havePK = true;
                if (!$havePK) {
                    $sqlPk = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD PRIMARY KEY ($colsList)";
                    if ($remoteConn->query($sqlPk) === TRUE) {
                        $messages[] = "Added PRIMARY KEY ($colsList).";
                    } else {
                        $messages[] = "Failed to add PRIMARY KEY: " . $remoteConn->error;
                    }
                } else {
                    $messages[] = "Primary key exists; skipped.";
                }
            }
            // UNIQUE / KEY : basic handling
            if (preg_match('/UNIQUE KEY\s+`([^`]+)`\s*\((.*?)\)/i', $keyLine, $um)) {
                $idxName = $um[1];
                $colsList = $um[2];
                // check existing
                $chkIdx = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = '" . $remoteConn->real_escape_string($idxName) . "'");
                if ($chkIdx && $chkIdx->num_rows === 0) {
                    $sqlIdx = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD UNIQUE `$idxName` ($colsList)";
                    if ($remoteConn->query($sqlIdx) === TRUE) {
                        $messages[] = "Added UNIQUE index `$idxName`.";
                    } else {
                        $messages[] = "Failed to add UNIQUE `$idxName`: " . $remoteConn->error;
                    }
                } else {
                    $messages[] = "Unique index `$idxName` exists; skipped.";
                }
            }
            if (preg_match('/KEY\s+`([^`]+)`\s*\((.*?)\)/i', $keyLine, $km)) {
                $idxName = $km[1];
                $colsList = $km[2];
                $chkIdx = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = '" . $remoteConn->real_escape_string($idxName) . "'");
                if ($chkIdx && $chkIdx->num_rows === 0) {
                    $sqlIdx = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD INDEX `$idxName` ($colsList)";
                    if ($remoteConn->query($sqlIdx) === TRUE) {
                        $messages[] = "Added INDEX `$idxName`.";
                    } else {
                        $messages[] = "Failed to add INDEX `$idxName`: " . $remoteConn->error;
                    }
                } else {
                    $messages[] = "Index `$idxName` exists; skipped.";
                }
            }
            // Note: FOREIGN KEY and complex constraints are not fully handled here (requires careful order).
        }
    } // foreach parts

    echo implode("\n", $messages);
    exit;
} elseif ($action === 'sync_column') {
    if (!$table || !$line) {
        echo "Missing params.";
        exit;
    }
    $line = decode_line($line);
    // Extract column name
    if (preg_match('/^`([^`]+)`\s+(.*)$/s', $line, $cm)) {
        $col = $cm[1];
        $def = $cm[2];
        // check exists
        $chk = $remoteConn->query("SHOW COLUMNS FROM `" . $remoteConn->real_escape_string($table) . "` LIKE '" . $remoteConn->real_escape_string($col) . "'");
        if ($chk && $chk->num_rows === 0) {
            // add column
            $sqlAdd = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD COLUMN $def";
            if ($remoteConn->query($sqlAdd) === TRUE) {
                echo "Added column `$col` to `$table`.";
            } else {
                echo "Failed to add `$col`: " . $remoteConn->error;
            }
        } else {
            // modify column
            $sqlMod = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` MODIFY COLUMN $def";
            if ($remoteConn->query($sqlMod) === TRUE) {
                echo "Modified column `$col` on `$table`.";
            } else {
                echo "Failed to modify `$col`: " . $remoteConn->error;
            }
        }
    } elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY|KEY|CONSTRAINT)/i', $line)) {
        // handle key/constraint add
        $part = $line;
        // Primary
        if (preg_match('/PRIMARY KEY\s*\((.*?)\)/i', $part, $pm)) {
            $colsList = $pm[1];
            $resk = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = 'PRIMARY'");
            if ($resk && $resk->num_rows === 0) {
                $sqlPk = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD PRIMARY KEY ($colsList)";
                if ($remoteConn->query($sqlPk) === TRUE) {
                    echo "Added PRIMARY KEY ($colsList).";
                } else {
                    echo "Failed to add PK: " . $remoteConn->error;
                }
            } else {
                echo "Primary key exists; skipped.";
            }
        } else {
            // for UNIQUE KEY / KEY
            if (preg_match('/UNIQUE KEY\s+`([^`]+)`\s*\((.*?)\)/i', $part, $um)) {
                $idxName = $um[1];
                $colsList = $um[2];
                $chkIdx = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = '" . $remoteConn->real_escape_string($idxName) . "'");
                if ($chkIdx && $chkIdx->num_rows === 0) {
                    $sqlIdx = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD UNIQUE `$idxName` ($colsList)";
                    if ($remoteConn->query($sqlIdx) === TRUE)
                        echo "Added UNIQUE `$idxName`.";
                    else
                        echo "Failed to add UNIQUE: " . $remoteConn->error;
                } else
                    echo "Unique index exists; skipped.";
            } elseif (preg_match('/KEY\s+`([^`]+)`\s*\((.*?)\)/i', $part, $km)) {
                $idxName = $km[1];
                $colsList = $km[2];
                $chkIdx = $remoteConn->query("SHOW INDEX FROM `" . $remoteConn->real_escape_string($table) . "` WHERE Key_name = '" . $remoteConn->real_escape_string($idxName) . "'");
                if ($chkIdx && $chkIdx->num_rows === 0) {
                    $sqlIdx = "ALTER TABLE `" . $remoteConn->real_escape_string($table) . "` ADD INDEX `$idxName` ($colsList)";
                    if ($remoteConn->query($sqlIdx) === TRUE)
                        echo "Added INDEX `$idxName`.";
                    else
                        echo "Failed to add INDEX: " . $remoteConn->error;
                } else
                    echo "Index exists; skipped.";
            } else {
                echo "Constraint type not supported via sync_column (or complex): $part";
            }
        }
    } else {
        echo "Unsupported line format.";
    }
    exit;
} else {
    echo "Unknown action.";
    exit;
}