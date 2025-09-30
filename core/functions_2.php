<?php
require_once 'config.php';
require_once 'db.php';

// Risk decision constants
define('RISK_ALLOW', 0);     // শুধু পাসওয়ার্ডেই চলবে
define('RISK_STEPUP', 1);    // 2FA / OTP দরকার
define('RISK_BLOCK', 2);     // ব্লক বা ম্যানুয়াল ভেরিফিকেশন
define('OTP_TTL', 600);

function haversine_distance_km($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

function normalize_device_fp($fp_raw) {
    return substr(hash('sha256', $fp_raw), 0, 64);
}

// Placeholder GeoIP - replace with MaxMind or ipinfo
function geoip_lookup($ip) {
    // trivial local fallback: return nulls
    return [
        'country' => null,
        'region' => null,
        'city' => null,
        'lat' => null,
        'lon' => null,
        'asn' => null
    ];
}

// Placeholder IP reputation
function ip_reputation_lookup($ip) {
    return [
        'is_proxy' => false,
        'is_tor' => false,
        'asn' => null,
        'score' => 0
    ];
}

function record_login_attempt($mysqli, $user_id, $event_type, $ip, $user_agent, $device_fp_raw, $reason = null) {
    $device_fp = $device_fp_raw ? normalize_device_fp($device_fp_raw) : null;
    $geo = geoip_lookup($ip);
    $iprep = ip_reputation_lookup($ip);

    $stmt = $mysqli->prepare("INSERT INTO users_login_tracks
        (user_id, event_type, ip, user_agent, device_fp, geo_country, geo_region, geo_city, lat, lon, asn, ip_risk_score, reason)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('issssssddsis', $user_id, $event_type, $ip, $user_agent, $device_fp,
        $geo['country'], $geo['region'], $geo['city'], $geo['lat'], $geo['lon'], $iprep['asn'], $iprep['score'], $reason);
    // Note: bind_param requires variables by reference; to keep this template simple we'll build a safe insert below instead
    $stmt->close();

    // fallback simple insert (escaping) to avoid complexities in template
    $sql = sprintf("INSERT INTO users_login_tracks (user_id,event_type,ip,user_agent,device_fp,geo_country,geo_region,geo_city,lat,lon,asn,ip_risk_score,reason)
        VALUES (%d,'%s','%s','%s','%s','%s','%s','%s',%s,%s,'%s',%d,'%s')",
        intval($user_id), $mysqli->real_escape_string($event_type), $mysqli->real_escape_string($ip), $mysqli->real_escape_string($user_agent), $mysqli->real_escape_string($device_fp),
        $mysqli->real_escape_string($geo['country']), $mysqli->real_escape_string($geo['region']), $mysqli->real_escape_string($geo['city']),
        is_null($geo['lat']) ? 'NULL' : floatval($geo['lat']), is_null($geo['lon']) ? 'NULL' : floatval($geo['lon']), $mysqli->real_escape_string($iprep['asn']), intval($iprep['score']), $mysqli->real_escape_string($reason));
    $mysqli->query($sql);

    return compute_risk_score($mysqli, $user_id, $ip, $device_fp, $geo, $iprep);
}

function compute_risk_score($mysqli, $user_id, $ip, $device_fp, $geo, $iprep) {
    $risk = 0;
    if (!empty($iprep['is_tor']) || !empty($iprep['is_proxy'])) $risk += 40;
    $risk += intval($iprep['score'] ?? 0);

    if ($device_fp) {
        $sql = sprintf("SELECT id FROM remembered_devices WHERE user_id=%d AND device_fp='%s' LIMIT 1", intval($user_id), $mysqli->real_escape_string($device_fp));
        $res = $mysqli->query($sql);
        if (!$res || $res->num_rows === 0) $risk += 25;
    }

    // last successful login
    $sql = sprintf("SELECT lat, lon, created_at FROM users_login_tracks WHERE user_id=%d AND event_type='login_success' ORDER BY created_at DESC LIMIT 1", intval($user_id));
    $res = $mysqli->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        if ($geo['lat'] && $geo['lon'] && $row['lat'] && $row['lon']) {
            $hours = (time() - strtotime($row['created_at'])) / 3600.0;
            $dist = haversine_distance_km($row['lat'], $row['lon'], $geo['lat'], $geo['lon']);
            if ($dist > 1000 && $hours > 0 && $hours < ($dist/800)) $risk += 60;
            elseif ($dist > 1000) $risk += 20;
        }
    }

    // time-of-day heuristic
    $sql = sprintf("SELECT HOUR(created_at) AS hr, COUNT(*) AS c FROM users_login_tracks WHERE user_id=%d AND event_type='login_success' AND created_at > (NOW() - INTERVAL 90 DAY) GROUP BY hr ORDER BY c DESC LIMIT 3", intval($user_id));
    $res = $mysqli->query($sql);
    $topHours = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $topHours[] = intval($r['hr']);
    }
    $currentHour = intval(gmdate('G'));
    if ($topHours && !in_array($currentHour, $topHours)) $risk += 10;

    // recent failures
    $sql = sprintf("SELECT COUNT(*) as c FROM users_login_tracks WHERE user_id=%d AND event_type='login_failure' AND created_at > (NOW() - INTERVAL 10 MINUTE)", intval($user_id));
    $res = $mysqli->query($sql);
    $fails = 0;
    if ($res && $r = $res->fetch_assoc()) $fails = intval($r['c']);
    if ($fails >=5) $risk += 50;
    elseif ($fails >=3) $risk += 20;

    if ($risk > 100) $risk = 100;
    return $risk;
}

function decide_action_by_risk($risk) {
    if ($risk < RISK_ALLOW) return ['action' => 'allow_password_only', 'level' => 0];
    if ($risk < RISK_STEPUP) return ['action' => 'require_stepup', 'level' => 1];
    return ['action' => 'block_or_require_strong', 'level' => 2];
}

function generate_and_store_otp($mysqli, $user_id, $channel = 'email', $ttl_seconds = OTP_TTL) {
    $otp = random_int(0, 999999);
    $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);
    $hash = password_hash($otp, PASSWORD_DEFAULT);
    $expires_at = date('Y-m-d H:i:s', time() + $ttl_seconds);
    $sql = sprintf("INSERT INTO otp_store (user_id, otp_hash, channel, expires_at) VALUES (%d,'%s','%s','%s')",
        intval($user_id), $mysqli->real_escape_string($hash), $mysqli->real_escape_string($channel), $mysqli->real_escape_string($expires_at));
    $mysqli->query($sql);
    return $otp;
}

function verify_otp($mysqli, $user_id, $otp_plain) {
    $sql = sprintf("SELECT id, otp_hash, expires_at, consumed FROM otp_store WHERE user_id=%d AND consumed=0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1", intval($user_id));
    $res = $mysqli->query($sql);
    if (!$res || $res->num_rows === 0) return false;
    $row = $res->fetch_assoc();
    if (password_verify($otp_plain, $row['otp_hash'])) {
        // mark consumed
        $mysqli->query(sprintf("UPDATE otp_store SET consumed=1 WHERE id=%d", intval($row['id'])));
        return true;
    }
    return false;
}

// TOTP (RFC6238) implementation
function base32_decode($s) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $s = strtoupper($s);
    $l = strlen($s);
    $bits = '';
    for ($i = 0; $i < $l; $i++) {
        $val = strpos($alphabet, $s[$i]);
        if ($val === false) continue;
        $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
    }
    $bytes = '';
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $bytes .= chr(bindec(substr($bits, $i, 8)));
    }
    return $bytes;
}

function totp_now($secret, $digits = 6, $period = 30, $algo = 'sha1') {
    $secretKey = base32_decode($secret);
    $time = floor(time() / $period);
    $timeBytes = pack('N*', 0) . pack('N*', $time); // 64-bit
    $hash = hash_hmac($algo, $timeBytes, $secretKey, true);
    $offset = ord(substr($hash, -1)) & 0x0F;
    $binary = (ord($hash[$offset]) & 0x7f) << 24 |
              (ord($hash[$offset+1]) & 0xff) << 16 |
              (ord($hash[$offset+2]) & 0xff) << 8 |
              (ord($hash[$offset+3]) & 0xff);
    $otp = $binary % pow(10, $digits);
    return str_pad($otp, $digits, '0', STR_PAD_LEFT);
}

function verify_totp($secret, $code, $window = 1) {
    for ($i = -$window; $i <= $window; $i++) {
        $t = floor((time() + ($i * 30)) / 30);
        $timeBytes = pack('N*', 0) . pack('N*', $t);
        $hash = hash_hmac('sha1', $timeBytes, base32_decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7f) << 24 |
                  (ord($hash[$offset+1]) & 0xff) << 16 |
                  (ord($hash[$offset+2]) & 0xff) << 8 |
                  (ord($hash[$offset+3]) & 0xff);
        $otp = str_pad($binary % 1000000, 6, '0', STR_PAD_LEFT);
        if (hash_equals($otp, $code)) return true;
    }
    return false;
}

