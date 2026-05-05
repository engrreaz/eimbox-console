<?php
if (!isset($_SESSION)) {
    session_start();
}


$theme = isset($_COOKIE['site_theme']) ? $_COOKIE['site_theme'] : 'light';

// এখন থিম অনুযায়ী রঙ সেট করো
if ($theme === 'dark') {

    $page_status_colors = [
        0 => "#f1e6e6ff",  // white
        1 => "#808080",  // Gray
        2 => "#FF0000",  // Red
        3 => "#FFA500",  // Orange
        4 => "#B027F5",  // Yellow
        5 => "#1E90FF",  // Blue
        6 => "#57e657ff",  // Green
        7 => "#24b611ff",  // Purple
        8 => "#cc56f0ff"   // Dark Green
    ];


    $release_colors = [
        0 => "#fafafaff",  // white
        1 => "#808080",  // Gray
        2 => "#FF0000",  // Red
        3 => "#FFA500",  // Orange
        4 => "#B027F5",  // Yellow
        5 => "#1E90FF",  // Blue
        6 => "#07af07ff",  // Green
        7 => "#800080",  // Purple
        8 => "#20c420ff"   // Dark Green
    ];

    $release_text = [
        0 => "#d33a3aff",  // white
        1 => "#af8b28ff",    // Gray
        2 => "#f5f5f5ff",    // Red
        3 => "#332306ff",    // Orange
        4 => "#1a180dff",    // Yellow
        5 => "#072746ff",    // Blue
        6 => "#ffffffff",  // Green
        7 => "#e6d8e6ff",    // Purple
        8 => "#edf0edff"   // Dark Green
    ];

} else {
    // Page Name Color : Page Status Color:
    $page_status_colors = [
        0 => "#0a0000ff",  // white
        1 => "#808080",  // Gray
        2 => "#FF0000",  // Red
        3 => "#FFA500",  // Orange
        4 => "#B027F5",  // Yellow
        5 => "#1E90FF",  // Blue
        6 => "#07af07ff",  // Green
        7 => "#800080",  // Purple
        8 => "#0d5f0dff"   // Dark Green
    ];


    $release_colors = [
        0 => "#0a0000ff",  // white
        1 => "#808080",  // Gray
        2 => "#FF0000",  // Red
        3 => "#FFA500",  // Orange
        4 => "#B027F5",  // Yellow
        5 => "#1E90FF",  // Blue
        6 => "#07af07ff",  // Green
        7 => "#800080",  // Purple
        8 => "#176817ff"   // Dark Green
    ];

    $release_text = [
        0 => "#140202ff",  // white
        1 => "#1a0a0aff",    // Gray
        2 => "#f5f5f5ff",    // Red
        3 => "#332306ff",    // Orange
        4 => "#1a180dff",    // Yellow
        5 => "#072746ff",    // Blue
        6 => "#ffffffff",  // Green
        7 => "#e6d8e6ff",    // Purple
        8 => "#edf0edff"   // Dark Green
    ];

}




$page_status_names = [
    0 => "Not Define Yet",  // white
    1 => "Pre-Alpha",  // Gray
    2 => "Alpha",  // Red
    3 => "Beta",  // Orange
    4 => "Gamma",  // Yellow
    5 => "RC",  // Blue
    6 => "Stable",  // Green
    7 => "Patch",  // Purple
    8 => "LTS"   // Dark Green
];



$status_desc_en = [
    0 => "The release is not defined or planned yet.",
    1 => "Early development stage, very unstable, mainly internal testing.",
    2 => "First functional version, likely unstable, used for internal or limited testing.",
    3 => "Feature-complete but may contain bugs, released for wider testing.",
    4 => "Pre-release version nearing stability, limited external testing.",
    5 => "(Release Candidate): Candidate for final release, mostly stable, final testing before official launch.",
    6 => "Official stable release, safe for general use.",
    7 => "Minor update or bug fix applied to stable release.",
    8 => "(Long-Term Support): Stable release with extended support and maintenance for longer period."
];

$status_desc_bn = [
    0 => "রিলিজটি এখনো পরিকল্পিত নয়। স্ট্যাটাস নির্ধারণ করা হয়নি।",
    1 => "এটি এখনো ডেভেলপমেন্টের প্রাথমিক পর্যায়ে আছে , খুবই অস্থিতিশীল, মূলত অভ্যন্তরীণ পরীক্ষার জন্য বিবেচ্য।",
    2 => "এই সংস্করণটি কার্যকর হলেও সম্ভবত অস্থিতিশীল। অভ্যন্তরীণ বা সীমিত পরীক্ষার জন্য ব্যবহৃত হচ্ছে। সম্বব হলে বাগ রিপোর্ট করুন।",
    3 => "ফিচার-সম্পূর্ণ কিন্তু বাগ থাকতে পারে, বিস্তৃত পরিসরে পরীক্ষা চলমান আছে।",
    4 => "স্থিতিশীলতার কাছাকাছি প্রি-রিলিজ সংস্করণ, চুড়ান্ত পরিক্ষা-নিরীক্ষা চলমান।",
    5 => "(রিলিজ ক্যান্ডিডেট): এটি চূড়ান্ত রিলিজ সংস্করণ। বেশিরভাগ স্থিতিশীল। নিরাপদে ব্যবহার করা যেতে পারে।",
    6 => "অফিসিয়াল স্থিতিশীল রিলিজ, সাধারণ ব্যবহারের জন্য সম্পুর্ণ নিরাপদ।",
    7 => "আপডেটেড ভার্সন। স্থিতিশীল রিলিজের ছোট আপডেট বা বাগ ফিক্স। নিরাপদ ও কার্যকর।",
    8 => "(দীর্ঘমেয়াদী সমর্থন): স্থিতিশীল রিলিজ যা দীর্ঘ সময়ের জন্য সমর্থন ও রক্ষণাবেক্ষণ পাবে।"
];



$currentFile = $_SESSION['current_page'] ?? '';

$error_message = '';
// echo 'User ID is set: ' . $_SESSION['user_id'];
$user_id_no = $_SESSION['user_id'] ?? '';
$usr = $_SESSION['user_email'] ?? '';
$username = $_SESSION['user_name'] ?? '';
$pth = $_SESSION['photourl'] ?? '';
$userlevel = $_SESSION['userlevel'] ?? '';
$is_admin = $_SESSION['isadmin'] ?? 0;
$is_chief = $_SESSION['is_chief'] ?? 0;



$page_status_grant = $_SESSION['page_status_grant'] ?? 6;

$fullname = $_SESSION['fullname'] ?? $usr;

$sccode = $_SESSION['sccode'] ?? '';

$sctype = $_SESSION['sccategory'] ?? '';
$scname = $_SESSION['scname'] ?? '';
$scaddress = $_SESSION['scaddress_top_full'] ?? '';
$scmobile = $_SESSION['scmobile'] ?? '';
$scmail = $_SESSION['scmail'] ?? '';
$scweb = $_SESSION['scweb'] ?? '';


$raw_json = $_SESSION['admin_data'] ?? '';
$raw_json = trim($raw_json);
$admin_data = json_decode($raw_json, true);


$json = $_SESSION['admin_data'] ?? '';
$json = trim($raw_json, "\x00..\x1F");
$json = mb_convert_encoding($json, 'UTF-8', 'UTF-8');
$admin_data = json_decode($json, true);



$sccode_current_package = $_SESSION['package_id'] ?? 2;
$sccode_current_package_name = $_SESSION['package_name'] ?? '&mdash;';
$sccode_current_package_tier = $_SESSION['tier'] ?? 'A';

$rootuser = $_SESSION['rootuser'] ?? '';
$headname = $_SESSION['headname'] ?? '';
$headtitle = $_SESSION['headtitle'] ?? '';

$sms_gateway = isset($_SESSION['sms_gateway']) ? explode(' | ', $_SESSION['sms_gateway']) : [];

$valid_module = isset($_SESSION['valid_module']) ? explode(' | ', $_SESSION['valid_module']) : [];
$active_module = isset($_SESSION['active_module']) ? explode(' | ', $_SESSION['active_module']) : [];

// active check: যদি 0 index খালি না থাকে
$sms_active = !empty($sms_gateway[0]) ? 1 : 0;

// array index defaults
$sms_api_key = $sms_gateway[1] ?? '';
$sms_secret_key = $sms_gateway[2] ?? '';
$sms_username = $sms_gateway[3] ?? '';
$sms_password = $sms_gateway[4] ?? '';
$sms_url = $sms_gateway[5] ?? '';
$sms_provider = $sms_gateway[6] ?? 'eimbox';
$sms_price = $sms_gateway[7] ?? '0.50';



$cur = date('Y-m-d H:i:s');
$td = date('Y-m-d');
$y_v2 = date('y');
$y_v4 = date('Y');
$chain = ''; // Slot -> Session -> Class -> Section chain holder

// echo $usr;

$eimbox_panels = ['Admin', 'Teacher', 'Guardian', 'Student', 'Guest', 'SMC', 'Guest'];



// $_SESSION['first_name'] . '/ ' .
// $_SESSION['last_name'] . '/ ' .
// $_SESSION['phone'] . '/ ' .
// $_SESSION['address'] . '/ ' .
// $_SESSION['dob'];


$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$sql = "SELECT 
            a.classteacher AS cteacherid,
            t.tname AS cteachername
        FROM areas a
        LEFT JOIN teacher t 
            ON a.classteacher = t.tid
        WHERE a.slot = '$slot'
        AND a.sessionyear = '$sessionyear'
        AND a.areaname = '$classname'
        AND a.subarea = '$sectionname'
        AND a.sccode = '$sccode'
        LIMIT 1";

$res = mysqli_query($conn, $sql);

$cteacherid = null;
$cteachername = null;

if ($row = mysqli_fetch_assoc($res)) {
    $cteacherid = $row['cteacherid'] ?? '';
    $cteachername = $row['cteachername'] ?? '-';
}


