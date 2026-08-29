# EIMBox Multi-Platform Feature & Issue Tracker Plan

## ১. প্ল্যাটফর্ম সংজ্ঞাসমূহ (Platforms)
1. **Dashboard** (`dashboard`) — Web Old Version
2. **Console** (`console`) — Web New Version
3. **Android Lite** (`android_lite`) — Android Lite Mobile Version
4. **Premium** (`premium`) — Offline App Version
5. **Desktop** (`desktop`) — Windows 10/11 Desktop Version

---

## ২. ২-টেবিল রিলেশনাল আর্কিটেকচার (Database Schema)

### টেবিল ১: `eimbox_features_master`
* `id` (INT PK)
* `module` (VARCHAR) — যেমন: Attendance, Accounts, Exam, Analytics
* `feature_name` (VARCHAR) — যেমন: Daily Attendance, OMR Scanner
* `description` (TEXT) — বিস্তারিত কাজের পরিধি
* `category` (VARCHAR)
* `display_order` (INT)
* `created_at`, `updated_at` (DATETIME)

### টেবিল ২: `eimbox_platform_tracker`
* `id` (INT PK)
* `feature_id` (INT FK -> `eimbox_features_master.id`)
* `platform` (ENUM: `dashboard`, `console`, `android_lite`, `premium`, `desktop`)
* `script_path` (VARCHAR) — সংশ্লিষ্ট ফাইল বা কন্ট্রোলার/ডার্ট ফাইল পাথ
* `status` (ENUM: `Not Implemented`, `Planned`, `Ongoing`, `Testing`, `Completed`, `Issue`, `Need Update`, `Customization`, `On Hold`)
* `priority` (ENUM: `Critical`, `High`, `Medium`, `Low`)
* `progress_percent` (TINYINT: 0 to 100)
* `issue_notes` (TEXT) — বাগ/সমস্যা, পেন্ডিং কাজ, ইউজার কাস্টমাইজেশন রিকোয়েস্ট
* `dev_response` (TEXT) — সমাধান, অগ্রগতি বা ডেভেলপার নোট
* `estimated_deadline` (DATE) — সম্ভাব্য সমাপ্তির তারিখ
* `assigned_to` (VARCHAR) — দায়িত্বপ্রাপ্ত ডেভেলপার/টিম
* `created_at`, `updated_at` (DATETIME)
* `UNIQUE KEY` (`feature_id`, `platform`)

---

## ৩. বাস্তবায়িত সুবিধাসমূহ (Implemented in `feature-tracker.php`)

- [x] **Modulelist Table Integration**: মডিউল ড্রপডাউন, ফিল্টার এবং অ্যাড/এডিট মোডালে সরাসরি সিস্টেমের `modulelist` টেবিল থেকে মডিউল তালিকা (Academic/Public এবং Backend গ্রুপ অনুযায়ী) লোড করা হচ্ছে।
- [x] **Platform Matrix View**: এক নজরে প্রতি সারিতে একটি ফিচার এবং পাশাপাশি ৫টি প্ল্যাটফর্মের লাইভ স্ট্যাটাস, প্রোগ্রেস %, স্ক্রিপ্ট ও ইস্যু ব্যাজ।
- [x] **Interactive Cell Click**: যেকোনো প্ল্যাটফর্মের সেলে ক্লিক করলে ঐ প্ল্যাটফর্মের স্ক্রিপ্ট, স্ট্যাটাস, ইস্যু নোট ও ডেভেলপার রেসপন্স আপডেট করার মোডাল ওপেন হয়।
- [x] **Master Feature CRUD**: নতুন ফিচার যুক্ত করা, এডিট করা এবং ডিলিট (ক্যাসকেড সহ) করার পূর্ণাঙ্গ ব্যবস্থা।
- [x] **Bulk 5-Platform Config**: এক ক্লিকে ৫টি প্ল্যাটফর্মের ডাটা একসাথে সেটআপ/এডিট করার মোডাল।
- [x] **Filter & Search Bar**: মডিউল, প্ল্যাটফর্ম, স্ট্যাটাস, প্রায়োরিটি ও লাইভ টেক্সট সার্চ (Debounced)।
- [x] **One-Click Issues Filter**: "শুধু ইস্যু ও সমস্যাসমূহ" বাটনে ক্লিক করে সব প্ল্যাটফর্মের একটিভ ইস্যুগুলো ফিল্টার করা।
- [x] **1-Click Demo Data Seeder**: ডাটাবেজ ফাঁকা থাকলে ১-ক্লিকেই বাস্তবধর্মী ডেমো ডাটা লোড করার সুবিধা।
- [x] **Zero Page Reloads**: সকল অপারেশন সম্পূর্ণ AJAX চালিত।



প্রতি অপারেশনে বার বার পেজ রিলোড হয়। অ্যাজাক্সের মাধ্যমে ব্যাকইন্ড অপারেশন করো।
