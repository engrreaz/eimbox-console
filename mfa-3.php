<?php 
ob_start(); // Output buffering শুরু
require_once 'header.php';

if($_SESSION['suspicious'] !== true && $is_admin == 0 ) {
    header('Location: index.php');
    exit;
}
ob_end_flush(); // Output send করা হবে
?>

<pre>
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





</pre>

<hr style="width:10px;" />

<pre>
  🧩 ১️⃣ লগইন ও অথেনটিকেশন সম্পর্কিত সন্দেহজনক আচরণ
#	আচরণ
1	একাধিক ব্যর্থ লগইন (short time window-এ)
2	অচেনা বা নতুন IP থেকে হঠাৎ লগইন
3	একই ইউজার একসাথে একাধিক সেশনে লগইন
4	ভিন্ন দেশ/অঞ্চল থেকে এক ঘন্টার মধ্যে একাধিক লগইন
5	একই IP থেকে বহু ভিন্ন ইউজার লগইন প্রচেষ্টা
6	“Remember me” token reuse বা expired session reuse
7	লগইনের পর তৎক্ষণাৎ logout (automation indicator)
8	Invalid user-agent সহ লগইন
9	Login without referrer header (direct script access)
10	VPN, Proxy, TOR নেটওয়ার্ক থেকে লগইন
🧠 ২️⃣ অ্যাকাউন্ট সেটিংস / প্রোফাইল আচরণ
#	আচরণ
11	ঘন ঘন পাসওয়ার্ড পরিবর্তন (e.g., মিনিটে কয়েকবার)
12	একাধিকবার পাসওয়ার্ড রিসেট ইমেইল রিকোয়েস্ট
13	ইমেইল/ফোন নাম্বার পরিবর্তন করে পুনরায় আগেরটিতে ফিরে যাওয়া
14	Two-factor authentication নিষ্ক্রিয় করা
15	অজানা ডিভাইস থেকে security settings পরিবর্তন
16	প্রোফাইল ইনফো-তে অস্বাভাবিক বা গার্বেজ ডেটা (e.g., “asdf”, “123”)
17	“Session hijacking” — ভিন্ন IP + একই session cookie detected
💾 ৩️⃣ ফাইল, ইনপুট ও আপলোড সম্পর্কিত সন্দেহজনক আচরণ
#	আচরণ
18	executable ফাইল (.php, .exe, .js) আপলোড করার চেষ্টা
19	বড় ফাইল বারবার আপলোড করা (resource abuse)
20	ইনপুটে SQL/XSS প্যাটার্ন (e.g., ' OR 1=1 --)
21	Base64 encoded / obfuscated script ফর্মে আপলোড
22	ফাইল নাম অদ্ভুত (e.g., .php.jpg, double extension)
23	ফাইল আপলোডের পর সাথে সাথে access চেষ্টা (execution intent)
24	Cross-site request forgery (forged origin header)
25	Repeated invalid MIME type submissions
🌍 ৪️⃣ ট্রাফিক ও ইউজার বিহেভিয়ার
#	আচরণ
26	প্রতি মিনিটে অনেক বেশি রিকোয়েস্ট (automation / bot)
27	খুব দ্রুত পেজ নেভিগেশন (impossible human speed)
28	একাধিক পেজ একই timestamp-এ hit (multi-threaded bot)
29	Suspicious referrer — অন্য domain থেকে redirect attempt
30	একই অ্যাকাউন্টে ভিন্ন timezone-এর থেকে একসাথে কাজ
31	পেজ access sequence অস্বাভাবিক (e.g., সরাসরি settings-এ jump)
32	Session cookie পরিবর্তন / inject করা
33	Invalid / missing headers (X-CSRF, Origin, UA)
34	High frequency GET + POST mix (data scraping indicator)
35	User idle অনেকক্ষণ, তারপর burst activity
36	Frequent reload বা auto-refresh (DDOS বা crawling intent)
🧾 ৫️⃣ ডেটা অ্যাক্সেস ও এক্সপোর্ট আচরণ
#	আচরণ
37	Short time-এ অনেক ডেটা ডাউনলোড / এক্সপোর্ট
38	unauthorized resource access (ID tampering: ?id=5 instead of ?id=3)
39	sensitive API endpoint hit (without permission)
40	ডেটা filter-less export (fetch all records attempts)
41	mass download of files / reports
42	একই রিকোয়েস্ট পুনরায় পাঠানো (replay attack)
43	অনেক সময় একই ইউজার ভিন্ন role দিয়ে একসাথে ডেটা দেখছে
44	অন্য ইউজারের রেকর্ড দেখার চেষ্টা (Privilege escalation attempt)
💳 ৬️⃣ ফাইনান্স / পেমেন্ট / ট্রানজাকশন
#	আচরণ
45	একই কার্ড দিয়ে বারবার ছোট ট্রানজাকশন (test payment)
46	refund abuse — frequent refunds
47	ভিন্ন একাউন্ট থেকে একই কার্ড ব্যবহার
48	কারেন্সি mismatch (geo vs transaction)
49	high-value transaction without history
50	payment gateway থেকে mismatch response
51	repeated failed payment attempts
52	coupon abuse (same coupon multiple times)
⚙️ ৭️⃣ সিস্টেম এক্সপ্লয়ট / ইনজেকশন প্রচেষ্টা
#	আচরণ
53	URL-এ script/tag injection (e.g., script)
54	Admin panel বা hidden endpoint-এ direct access চেষ্টা
55	Directory traversal (../../etc/passwd)
56	Query parameter-এ encoded shell command (%3B ls -la)
57	Form input-এ HTML injection
58	Suspicious API payload (malformed JSON, hex blobs)
59	Error-triggering input (SQL error forcing attempt)
60	Suspicious automation tools user-agent (curl, python-requests)
61	Cookie manipulation / JWT tampering
62	Suspicious Referer Spoofing
🕹 ৮️⃣ বট বা স্ক্রিপ্ট-ভিত্তিক আচরণ
#	আচরণ
63	same IP-থেকে মিনিটে শতাধিক রিকোয়েস্ট
64	non-browser UA (e.g., “python”, “curl”, “wget”)
65	no JS execution detected (JS-disabled bots)
66	একই IP থেকে অনেক account তৈরি
67	একই প্যাটার্নে ইনপুট (auto form filler)
68	অনিয়মিত mouse movement / keyboard events
69	headless browser (puppeteer, selenium UA)
70	API key brute force attempt
71	token reuse after expiry
🧮 ৯️⃣ অভ্যন্তরীণ (insider / privilege abuse)
#	আচরণ
72	Admin user বারবার অন্যদের role পরিবর্তন করছে
73	অপ্রয়োজনীয় bulk data export
74	নিজস্ব record modify করার চেষ্টা
75	privileged action without justification
76	off-hours (midnight) এ data access
77	বারবার sensitive টেবিল access (users, payments ইত্যাদি)
78	config বা setting ফাইল ডাউনলোড
79	admin action failure-এর পরে retry
80	অন্য অ্যাডমিনের সেশন থেকে unauthorized task
🧰 🔟 সিস্টেম বা নেটওয়ার্ক স্তরে সন্দেহজনক আচরণ
#	আচরণ
81	high latency burst / unusual bandwidth usage
82	malformed request / 400 spam flood
83	repeated 404 scanning (probing for hidden files)
84	repeated 500/403 responses (probing for exploit)
85	Login flood from same subnet
86	POST request-এ invalid Content-Length
87	unusual API call sequence
88	JSON বা XML bomb attempt
89	frequent CORS preflight anomalies
90	WebSocket flood or continuous reconnect attempts
⚡ ১১️⃣ অন্য গুরুত্বপূর্ণ Miscellaneous আচরণ
#	আচরণ
91	User repeatedly disables email notifications
92	Multiple account creation from same device/IP
93	Suspicious referral chain (self-referral loops)
94	Rapid switching between multiple roles
95	Device time manipulation (for session expiry bypass)
96	Fake geolocation info
97	Repeated login using temporary/disposable emails
98	Abnormal timezone mismatch (login UTC+6, logout UTC-8)
99	Automated “forgot password” spam
100	abnormal high error 403/401 patterns per user

✅ মোট: প্রায় ১০০টি ভিন্ন “Suspicious Activity”
(এগুলোর মধ্যে থেকেই আপনি আপনার সিস্টেম অনুযায়ী detection rule বানাতে পারেন।)
</pre>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>
</html>
