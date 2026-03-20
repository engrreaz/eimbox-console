<?php
// log-api.php
// Handles AJAX actions: fetch, stats, remove_by_index, get_raw_by_index, download, clear, archive
// Security: simple CSRF using session; adjust clear password below

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

// CONFIG - change as needed
$LOG_FILE = __DIR__ . "/php-error.log";
$ARCHIVE_DIR = __DIR__ . "/logs";
$TRUNCATE_KEEP_LINES = 500;         // when archiving keep last N lines
$MAX_SIZE_BYTES = 5 * 1024 * 1024;  // 5MB trigger auto-archive
// Set clear password: replace 'change_me' with secure password OR integrate with your auth system
define('LOG_CLEAR_PASSWORD', 'CLRPSW');

// CSRF check helper
function csrf_ok($val)
{
    return isset($_SESSION['csrf_token']) && $val === $_SESSION['csrf_token'];
}

// helper to read file lines (returns array of lines)
function read_lines($path)
{
    if (!file_exists($path))
        return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    return $lines ?: [];
}

// parse a line into components (time, msg, type, file)
function parse_line($line)
{
    $res = ['raw' => $line, 'time' => '', 'msg' => $line, 'type' => 'UNKNOWN', 'file' => 'Unknown'];
    if (preg_match('/^\[(.*?)\]\s*(.*)$/', $line, $m)) {
        $res['time'] = $m[1];
        $res['msg'] = $m[2];
    } else {
        $res['msg'] = $line;
    }
    if (preg_match('/PHP\s+([A-Z]+)/', $res['msg'], $t))
        $res['type'] = $t[1];
    if (preg_match('/in\s+(.*?)\s+on\s+line/', $line, $f))
        $res['file'] = basename($f[1]);
    return $res;
}

// read raw input (json or form)
$raw = file_get_contents('php://input');
$isJson = false;
$params = [];
if (!empty($raw) && ($j = json_decode($raw, true)) !== null) {
    $params = $j;
    $isJson = true;
} else {
    $params = $_POST;
}

// route actions
$action = $params['action'] ?? ($_GET['action'] ?? 'fetch');

if ($action === 'fetch') {
    // require csrf
    if (empty($params['csrf']) || !csrf_ok($params['csrf'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }
    
    $page = max(1, intval($params['page'] ?? 1));
    $per_page = max(1, intval($params['per_page'] ?? 20));
    $filter_date = trim($params['date'] ?? '');
    $filter_type = strtoupper(trim($params['type'] ?? ''));
    $filter_file = trim($params['file'] ?? '');
    $filter_text = trim($params['text'] ?? '');

    $lines = array_reverse(read_lines($LOG_FILE)); // newest first

    // build items with parsed data and apply filter
    $items = [];
    foreach ($lines as $i => $ln) {
        $p = parse_line($ln);
        $p['global_index'] = $i;
        if ($filter_date && strpos($p['time'], $filter_date) === false)
            continue;
        if ($filter_type && $filter_type !== $p['type'])
            continue;
        if ($filter_file && stripos($p['file'], $filter_file) === false)
            continue;
        if ($filter_text && stripos($p['msg'], $filter_text) === false)
            continue;
        $items[] = $p;
    }

    $total = count($items);
    $start = ($page - 1) * $per_page;
    $page_items = array_slice($items, $start, $per_page);

    // return items and meta. note: we include page_start_index as global index in reversed array
    // to allow unique removal by index relative to reversed(all_lines)
    echo json_encode([
        'ok' => true,
        'items' => $page_items,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'page_start_index' => $start
    ]);
    exit;
}

// stats
if ($action === 'stats') {
    if (empty($params['csrf']) || !csrf_ok($params['csrf'])) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }
    $lines = array_reverse(read_lines($LOG_FILE));
    $counts = ['ERROR' => 0, 'WARNING' => 0, 'NOTICE' => 0];
    $total = 0;
    $files = [];
    foreach ($lines as $ln) {
        $p = parse_line($ln);
        $total++;
        if (isset($counts[$p['type']]))
            $counts[$p['type']]++;
        $files[$p['file']] = ($files[$p['file']] ?? 0) + 1;
    }
    arsort($files);
    $top_files = array_slice($files, 0, 8, true);
    // small html for UI
    $top_html = [];
    foreach ($top_files as $fn => $c)
        $top_html[] = "<span class='badge bg-light text-dark me-1 mb-1 file-badge' data-file='{$fn}' style='cursor:pointer;'>{$fn} <small>({$c})</small></span>";
    echo json_encode(['ok' => true, 'total' => $total, 'counts' => $counts, 'top_files_html' => implode(' ', $top_html)]);
    exit;
}

// remove_by_index (index relative to reversed total file)
if ($action === 'remove_by_index') {
    $idx = isset($params['index']) ? intval($params['index']) : null;
    $csrf = $params['csrf'] ?? '';
    if ($idx === null || !csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid request']);
        exit;
    }

    $lines = array_reverse(read_lines($LOG_FILE)); // newest first
    if (!isset($lines[$idx])) {
        echo json_encode(['ok' => false, 'msg' => 'Index not found']);
        exit;
    }

    // remove that index and write file back (reverse back to original order)
    unset($lines[$idx]);
    $new = array_reverse(array_values($lines));
    file_put_contents($LOG_FILE, implode("\n", $new));
    echo json_encode(['ok' => true]);
    exit;
}


// Remove all 
if ($action === 'remove_all_by_file') {
    $csrf = $params['csrf'] ?? '';
    $file = trim($params['file'] ?? '');

    if (!csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }
    if ($file === '') {
        echo json_encode(['ok' => false, 'msg' => 'File empty']);
        exit;
    }

    $lines = read_lines($LOG_FILE);
    $new = [];

    foreach ($lines as $ln) {
        if (preg_match('/in\s+(.*?)\s+on\s+line/', $ln, $m)) {
            if (basename($m[1]) === $file)
                continue;  // remove
        }
        $new[] = $ln; // keep
    }

    file_put_contents($LOG_FILE, implode("\n", $new));

    echo json_encode(['ok' => true, 'removed' => true]);
    exit;
}


// get raw by index
if ($action === 'get_raw_by_index') {
    $idx = isset($params['index']) ? intval($params['index']) : null;
    $csrf = $params['csrf'] ?? '';
    if ($idx === null || !csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid request']);
        exit;
    }
    $lines = array_reverse(read_lines($LOG_FILE));
    if (!isset($lines[$idx])) {
        echo json_encode(['ok' => false, 'msg' => 'Index not found']);
        exit;
    }
    echo json_encode(['ok' => true, 'raw' => $lines[$idx]]);
    exit;
}

// download (GET)
if ($action === 'download') {
    $csrf = $_GET['csrf'] ?? $_POST['csrf'] ?? '';
    if (!csrf_ok($csrf)) {
        http_response_code(403);
        echo "Invalid CSRF";
        exit;
    }
    if (!file_exists($LOG_FILE)) {
        http_response_code(404);
        echo "No log";
        exit;
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="php-error.log"');
    readfile($LOG_FILE);
    exit;
}

// remove (non-ajax fallback by raw line match)
if ($action === 'remove') {
    $csrf = $params['csrf'] ?? '';
    if (!csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }
    $line = $params['line'] ?? '';
    if ($line === '') {
        echo json_encode(['ok' => false, 'msg' => 'Empty line']);
        exit;
    }
    $lines = read_lines($LOG_FILE);
    $new = [];
    foreach ($lines as $l)
        if (trim($l) !== trim($line))
            $new[] = $l;
    file_put_contents($LOG_FILE, implode("\n", $new));
    echo json_encode(['ok' => true]);
    exit;
}

// clear (form POST; requires password or integrate auth)
if ($action === 'clear') {
    // if JSON, params come from JSON; else from POST
    $csrf = $params['csrf'] ?? ($_POST['csrf'] ?? '');
    if (!csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }

    $given = $params['clear_password'] ?? ($_POST['clear_password'] ?? '');
    // compare to LOG_CLEAR_PASSWORD constant
    if (!defined('LOG_CLEAR_PASSWORD') || LOG_CLEAR_PASSWORD === 'change_me') {
        echo json_encode(['ok' => false, 'msg' => 'Clear password not configured. Update log-api.php LOG_CLEAR_PASSWORD.']);
        exit;
    }
    if ($given !== LOG_CLEAR_PASSWORD) {
        echo json_encode(['ok' => false, 'msg' => 'Wrong password']);
        exit;
    }
    // rotate / archive current file before clearing
    if (file_exists($LOG_FILE) && filesize($LOG_FILE) > 0) {
        if (!is_dir($ARCHIVE_DIR))
            mkdir($ARCHIVE_DIR, 0755, true);
        $arc = $ARCHIVE_DIR . '/php-error-' . date('Ymd-His') . '.log';
        rename($LOG_FILE, $arc);
        // create empty file
        file_put_contents($LOG_FILE, '');
        echo json_encode(['ok' => true, 'archive' => $arc]);
    } else {
        echo json_encode(['ok' => true, 'archive' => null]);
    }
    exit;
}

// archive (manual)
if ($action === 'archive') {
    $csrf = $params['csrf'] ?? '';
    if (!csrf_ok($csrf)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid CSRF']);
        exit;
    }
    if (!is_dir($ARCHIVE_DIR))
        mkdir($ARCHIVE_DIR, 0755, true);
    $ts = date('Ymd-His');
    $archivePath = $ARCHIVE_DIR . '/php-error-' . $ts . '.log';
    // move file if exists
    if (file_exists($LOG_FILE)) {
        // keep last N lines to new log file
        $lines = read_lines($LOG_FILE);
        $keep = array_slice($lines, -$TRUNCATE_KEEP_LINES);
        // write archive as full old content
        file_put_contents($archivePath, implode("\n", $lines));
        // write truncated back to active log (last N lines)
        file_put_contents($LOG_FILE, implode("\n", $keep));
        echo json_encode(['ok' => true, 'archive' => $archivePath]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Log file not found']);
    }
    exit;
}

// automatic archive trigger on GET request (can be invoked by cron or admin)
if ($action === 'auto_archive') {
    if (!is_dir($ARCHIVE_DIR))
        mkdir($ARCHIVE_DIR, 0755, true);
    if (!file_exists($LOG_FILE)) {
        echo json_encode(['ok' => false, 'msg' => 'No log']);
        exit;
    }
    $size = filesize($LOG_FILE);
    if ($size > $MAX_SIZE_BYTES) {
        $lines = read_lines($LOG_FILE);
        $archivePath = $ARCHIVE_DIR . '/php-error-' . date('Ymd-His') . '.log';
        file_put_contents($archivePath, implode("\n", $lines));
        $keep = array_slice($lines, -$TRUNCATE_KEEP_LINES);
        file_put_contents($LOG_FILE, implode("\n", $keep));
        echo json_encode(['ok' => true, 'archive' => $archivePath, 'old_size' => $size]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Not large enough']);
    }
    exit;
}

// default - unknown action
echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
exit;
