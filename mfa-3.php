
১) অস্বাভাবিক লোকেশন বা IP থেকে লগইন

কি দেখবেন (Signals):

ক্লায়েন্টের IP → GeoIP (country / region / city)।

পূর্বের টপ-নর্মাল লোকেশন রেঞ্জ (user’s usual country/cities)।

IP reputation (malicious / proxy / TOR / VPN)।

Geo-distance / impossible travel (এক ঘণ্টার মধ্যে ২টি ভিন্ন দেশের লগইন)।

কোন ডাটা রাখতে হবে:

user_id, timestamp, ip, geo_country, geo_city, user_agent, device_id, risk_score (login history টেবিলে)।

IP reputation API ফলাফল ক্যাশ করা।

সনাক্তকরণ নিয়ম (heuristic):

যদি geo_country পুরোনো তালিকায় না থাকে → risk += 30

যদি distance(previous_location, current_location) > 1000 km এবং time_diff < plausible_travel_time → impossible-traveler flag।

যদি IP reputation == bad বা known-proxy → risk += 40

উদাহরণ (পিএইচপি/পসুডো):

$geo = geoip_lookup($ip);
$last = get_last_known_location($user_id); // country, timestamp
$hours = (time() - $last['timestamp'])/3600;
$distance = haversine($geo['lat'],$geo['lon'],$last['lat'],$last['lon']);
if ($geo['country'] !== $last['country']) $risk += 30;
if ($distance > 1000 && $hours < ($distance/800)) $risk += 60; // impossible traveler heuristic
if (ip_is_proxy($ip)) $risk += 40;


Action (mitigation):

রিস্ক স্কোর > থ্রেশহোল্ড → step-up MFA (authenticator app / hardware key) বা ব্লক ও নোটিফাই। 
Palo Alto Networks
+1

২) অচেনা বা নতুন ডিভাইস/ব্রাউজার

Signals:

New device fingerprint: user_agent string, screen resolution, installed fonts, timezone, canvas fingerprint ইত্যাদি (হালকা ফিঙ্গারপ্রিন্টিং)।

First-time device: device_id বা cookie নেই।

Browser fingerprint hash mismatch vs previous enrolled devices।

Data to store:

device_id, user_agent, device_fingerprint_hash, enrolled_at, last_seen (devices টেবিলে)।

persistent cookie / localStorage token (with consent)।

Heuristic / rule:

যদি device_fingerprint_hash not in enrolled_devices AND (not within recent remembered devices window) → risk += 25

দুইবার বা ত্রৈমাসিকে নতুন ডিভাইস হলে user notification

Example flow (JS + server):

// on login success, send minimal fingerprint to server
fetch('/auth/record-device', {
  method:'POST',
  body: JSON.stringify({
    ua: navigator.userAgent, tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
    screen: `${screen.width}x${screen.height}`, fp: simpleFp()
  })
});

-- SQL: check if device exists
SELECT id FROM devices WHERE user_id=? AND device_fp_hash = ? LIMIT 1;


Mitigation:

নতুন ডিভাইস হলে ইমেইল নোটিফিকেশন + 2FA step-up বা require confirmation link from registered email. 
OWASP Cheat Sheet Series

ক্যারিফুলি: শক্তিশালী ফিঙ্গারপ্রিন্টিং প্রাইভেসি/false-positive বাড়ায় — হালকা/consented token-based approach ভালো। 
NSF Public Access Repository

৩) একসঙ্গে একাধিক জায়গা থেকে (simultaneous) লগইন চেষ্টা — “Impossible / Concurrent logins”

Signals:

একই user_id-এর জন্য overlapping active sessions from geographically distant IPs।

Session tokens used from different IPs within short time window.

Suspicious session concurrency pattern (same credentials used simultaneously repeatedly)।

Data to store:

Active sessions table: session_id, user_id, ip, ua, started_at, last_seen_at, device_id।

Heuristic / rule (immediate detection):

যদি session A last_seen_at within 5 minutes AND new login from location_distance(A.ip, new.ip) > 500 km → flag simultaneous / impossible.

Rate of simultaneous new sessions per hour > N → high risk.

簡単なSQL:

SELECT s.* FROM sessions s
WHERE s.user_id = :user_id AND s.last_seen_at >= NOW() - INTERVAL '10 minutes';


তারপর প্রতিটা session-এর IP নিয়ে distance হিসাব করে concurrency চেক করুন।

Mitigation:

Invalidate older session(s) OR prompt both sessions for reauth/notification.

Notify user: “Your account was just accessed from another device — was this you?” এবং temporary lock যদি suspicious। 
Griaule Documentation
+1

৪) উচ্চ-ঝুঁকিপূর্ণ অ্যাকশন (sensitive actions)

Actions considered high-risk:

Password reset, email/phone change, adding/removing MFA, financial transfer, deleting account, exporting data।

Signals:

Action type, request origin (same device?), recent successful MFA, session_age, user role/privilege।

Data to store:

Audit log of sensitive actions: actor_id, action, target, timestamp, ip, device_id, prev_value, new_value.

Heuristic:

If action == password_change AND last_auth_time > 15 minutes → require reauthentication.

If changing recovery phone/email and device != enrolled_device → require hardware token or biometric.

Example policy pseudocode:

if action in HIGH_RISK:
  if session_age > 15min or recent_mfa != TRUE:
    require_step_up(MFA_HARD)


Mitigation:

Force step-up MFA (hardware key / authenticator app), send confirmation email (with link to cancel) and delay critical changes (e.g., 24–48h delay for contact change with cancel link) per risk appetite and UX trade-off. 
NIST Pages
+1

৫) লগইন প্যাটার্নের ভিন্নতা (behavioral anomalies)

Signals:

Unusual time-of-day (e.g., normally 9–18, now 03:00)।

Sudden change in login frequency (spike of attempts).

Failed-attempt patterns (many failures then success).

Behavioral biometrics (typing rhythm, mouse patterns) if available.

Data to store:

Historical login histogram: hour_of_day, day_of_week, avg_location, avg_device per user.

Recent failed login attempts count and timestamps.

Heuristic / rule:

timeDeviation = is_outside_user_hourly_window(current_hour, user_histogram) → risk += 15

if failed_attempts_in_last_10min > 5 → risk += 40 and cooldown/lockout

Example pseudo-SQL (time anomaly):

-- count logins in user's typical hour window last 90 days
SELECT COUNT(*) FROM logins WHERE user_id=? AND HOUR(ts) IN (user_top_hours) AND ts > NOW() - INTERVAL '90 days';


Mitigation:

For moderate risk → prompt step-up (OTP + push); for high risk → block and notify + require password reset. Behavioral signals should be used conservatively to avoid false positives. 
OWASP Cheat Sheet Series
+1

৬) সন্দেহজনক নেটওয়ার্ক/পরিবেশ (public Wi-Fi, VPN, Tor)

Signals:

IP belongs to known VPN / Tor exit node list.

ASN or Hosting provider indicates datacenter instead of ISP (e.g., Amazon AWS / DigitalOcean).

Use of HTTP or other insecure channel, or missing TLS client cert when expected.

Data to store:

IP reputation & ASN mapping cache.

Flags for previous use from same ASN.

Detection pseudocode:

$asn = lookup_asn($ip);
if (in_array($asn, $datacenter_asns)) $risk += 20;
if (is_tor_exit($ip) || ip_is_vpn($ip)) $risk += 40;


Mitigation:

Step-up auth OR temporarily restrict certain actions (no financial tx from Tor) and notify user. Optionally allow read-only sessions from high-risk networks. 
OWASP Cheat Sheet Series
+1

মোট মিলিয়ে — Risk Scoring & Decisioning

প্রতিটি সিগন্যালকে পয়েন্ট দিন (weight)। সব সিগন্যাল যোগ করে risk_score পান।

Define thresholds:

risk_score < 20 → normal (2FA ok)

20 ≤ risk_score < 60 → step-up (Authenticator app / push)

risk_score ≥ 60 → block + require strong step-up (hardware key) or locked after human review

লগ ও টেলিমেট্রি সংগ্রহ করুন, ML ব্যবহার করলে false-positive কমে এবং per-user baseline শিখে। 
Risk-Based Authentication
+1

Simple risk calculator example (pseudo):

risk = 0
if new_country: risk += 30
if ip_proxy: risk += 40
if new_device: risk += 25
if odd_time: risk += 10
if many_failed_attempts: risk += 50
# then act by thresholds

বাস্তবায়ন টিপস ও নজরদারি

Centralize signals — একটি রিয়াল-টাইম decision service যেখানে সমস্ত লগইন/অ্যাকশন ইভেন্ট পাঠানো হবে এবং risk decision আউটপুট দিয়ে হবে।

Cache geo/ip lookups to reduce latency and cost.

Rate-limit & backoff failed attempts and OTP requests.

Telemetry & alerting — suspicious patterns automated alerts to security ops.

A/B testing ও gradual rollout — নতুন heuristics small % users-এ চালাবেন false positive কমাতে। 
OWASP Cheat Sheet Series
+1