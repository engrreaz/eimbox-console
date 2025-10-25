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
        4 => "#FFD700",  // Yellow
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
        4 => "#FFD700",  // Yellow
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
        4 => "#FFD700",  // Yellow
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
        4 => "#FFD700",  // Yellow
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
    0 => "রিলিজটি এখনও নির্ধারিত বা পরিকল্পিত হয়নি।",
    1 => "প্রাথমিক ডেভেলপমেন্ট ধাপ, খুবই অস্থিতিশীল, মূলত অভ্যন্তরীণ পরীক্ষার জন্য।",
    2 => "প্রথম কার্যকরী সংস্করণ, সম্ভবত অস্থিতিশীল, অভ্যন্তরীণ বা সীমিত পরীক্ষার জন্য ব্যবহৃত।",
    3 => "ফিচার-সম্পূর্ণ কিন্তু বাগ থাকতে পারে, বিস্তৃত পরীক্ষার জন্য রিলিজ করা হয়।",
    4 => "স্থিতিশীলতার কাছাকাছি প্রি-রিলিজ সংস্করণ, সীমিত বাইরের পরীক্ষা।",
    5 => "(রিলিজ ক্যান্ডিডেট): চূড়ান্ত রিলিজের জন্য প্রার্থী, বেশিরভাগ স্থিতিশীল, অফিসিয়াল লঞ্চের আগে চূড়ান্ত পরীক্ষা।",
    6 => "অফিসিয়াল স্থিতিশীল রিলিজ, সাধারণ ব্যবহারের জন্য নিরাপদ।",
    7 => "স্থিতিশীল রিলিজের ছোট আপডেট বা বাগ ফিক্স।",
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

$fullname = $_SESSION['fullname'] ?? $usr;

$sccode = $_SESSION['sccode'] ?? '';

$sctype = $_SESSION['sccategory'] ?? '';
$scname = $_SESSION['scname'] ?? '';





$admin_data = json_decode($_SESSION['admin_data'] ?? '', true);
$sccode_current_package = $admin_data['package']['id'] ?? 2;



$cur = date('Y-m-d H:i:s');
$td = date('Y-m-d');

// var_dump($_SESSION);
// echo $usr;





// $_SESSION['first_name'] . '/ ' .
// $_SESSION['last_name'] . '/ ' .
// $_SESSION['phone'] . '/ ' .
// $_SESSION['address'] . '/ ' .
// $_SESSION['dob'];