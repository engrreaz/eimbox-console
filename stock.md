# EIMBox Stock, Inventory, Assets & Sales Management System (Master Blueprint)

**সিস্টেমের নাম:** EIMBox Store, Inventory, Fixed Assets & Student Sales POS  
**টার্গেট প্ল্যাটফর্ম:** `eimbox-materio` (Web ERP Dashboard)  
**ডাটাবেস আর্কিটেকচার:** MySQL / MariaDB (Multi-Tenant with strict `sccode` filtering)  
**ইউজার ইন্টারফেস স্ট্যান্ডার্ড:** Bootstrap 5 (Existing Theme CSS Only — No Custom/Separate CSS)  
**আইকন স্ট্যান্ডার্ড:** Bootstrap Icons (`bi bi-*`)  
**অ্যালার্ট ও নোটিফিকেশন:** SweetAlert2 (`Swal.fire` / `Swal.mixin` Toasts)  
**লেআউট ফ্রেমওয়ার্ক:** `header.php` & `footer.php` সমন্বিত রেসপনসিভ গ্রিড  

---

## ১. ভূমিকা ও কৌশলগত লক্ষ্য (System Purpose & Strategy)

শিক্ষা প্রতিষ্ঠানের আর্থিক ও পরিচালনা ব্যবস্থাপনায় নগদ অর্থ লেনদেনের পাশাপাশি সম্পদের হিসাব ও বিক্রয় ব্যবস্থাপনা অত্যন্ত গুরুত্বপূর্ণ। বিদ্যমান `cashbook.php` শুধুমাত্র ক্যাশ-ইন ও ক্যাশ-আউট (নগদ প্রবাহ) নিয়ন্ত্রণ করে। 

এই ব্লুপ্রিন্টের মাধ্যমে প্রতিষ্ঠানটির জন্য একটি ত্রি-মাত্রিক সম্পদ ও স্টোর ম্যানেজমেন্ট সিস্টেম নিশ্চিত করা হবে:
1. **Student Store & Sales POS:** শিক্ষার্থীদের শিক্ষা উপকরণ (ডায়েরি, খাতা, কলম, বই, স্কুলব্যাগ, ইউনিফর্ম, টাই/ব্যাজ ইত্যাদি) বিক্রয় ও তাৎক্ষণিক থার্মাল মেমো প্রিন্টিং।
2. **Internal Consumable Stock:** প্রতিষ্ঠানের অভ্যন্তরীণ কনজিউমেবল মালামাল (মার্কার, পেপার, চক, ক্লিনিং ইত্যাদি) স্টক-ইন এবং বিভিন্ন শিক্ষক/বিভাগে ইস্যু ট্র্যাকিং।
3. **Fixed Assets & Equipment Register:** প্রতিষ্ঠানের স্থায়ী সম্পদ (কম্পিউটার, প্রজেক্টর, এসি, ল্যাব সরঞ্জাম, বেঞ্চ-ফার্নিচার) রুম ও লোকেশন অনুযায়ী ট্যাগিং এবং মেরামত/অবস্থা তদারকি।

---

## ২. ডাটাবেস টেবিলসমূহ ও এদের ব্যবহারিক প্রয়োগ (Database Tables & Practical Implementation)

প্রতিটি টেবিল মাল্টি-টেন্যান্সি সাপোর্ট করে এবং বাধ্যতামূলকভাবে `sccode` কলাম দ্বারা সুরক্ষিত।

```
                                  [ sccode ] (Tenant Isolation)
                                       │
         ┌─────────────────────────────┼─────────────────────────────┐
         ▼                             ▼                             ▼
┌──────────────────┐          ┌──────────────────┐          ┌──────────────────┐
│ 1. Master Config │          │ 2. Inventory/POS │          │ 3. Fixed Assets  │
├──────────────────┤          ├──────────────────┤          ├──────────────────┤
│ inv_categories   │          │ inv_suppliers    │          │ fixed_assets     │
│ inv_units        │          │ inv_purchases    │          │ asset_logs       │
│ inv_items        │          │ inv_purchase_items          └──────────────────┘
└──────────────────┘          │ inv_sales        │
                              │ inv_sale_items   │
                              │ inv_issues       │
                              │ inv_issue_items  │
                              └────────┬─────────┘
                                       │
                                       ▼ (Auto Voucher Post)
                              ┌──────────────────┐
                              │     cashbook     │
                              └──────────────────┘
```

---

### ২.১ `inv_categories` (আইটেম ও সম্পদ ক্যাটাগরি)
* **ব্যবহারিক প্রয়োগ:** সমস্ত আইটেমকে তিনটি প্রধান ক্যাটাগরি টাইপে ভাগ করে (`SaleItem`, `Consumable`, `Asset`)।
* **ফিল্ডসমূহ:** `id`, `sccode`, `category_name`, `category_type`, `status`, `created_at`
* **উদাহরণ:** 
  * `Stationery & Books` (Type: `SaleItem`)
  * `Office Supplies` (Type: `Consumable`)
  * `ICT & Electronics` (Type: `Asset`)

---

### ২.২ `inv_units` (পরিমাপের একক মাস্টার)
* **ব্যবহারিক প্রয়োগ:** আইটেম পরিমাপের সঠিক একক নির্ধারণ করা।
* **ফিল্ডসমূহ:** `id`, `sccode`, `unit_name`, `unit_symbol`, `status`
* **উদাহরণ:** `Piece (Pcs)`, `Packet (Pkt)`, `Dozen (Dzn)`, `Set (Set)`, `Kilogram (Kg)`।

---

### ২.৩ `inv_items` (পণ্য ও স্টক মাস্টার টেবিল)
* **ব্যবহারিক প্রয়োগ:** প্রতিটি পণ্যের নাম, বারকোড/এসকেইউ, ক্রয়মূল্য, বিক্রয়মূল্য, বর্তমান স্টক ব্যালেন্স এবং রি-অর্ডার লেভেল সংরক্ষণ।
* **ফিল্ডসমূহ:** 
  * `id`, `sccode`, `item_code`, `barcode`, `item_name`, `category_id`, `unit_id`, `item_type`, `purchase_price`, `sale_price`, `current_stock`, `reorder_level`, `location_rack`, `status`, `created_by`, `created_at`, `updated_at`
* **অটোমেশন রুল:** 
  * কোনো সেলস ইনভয়েস হলে `current_stock = current_stock - qty`।
  * পারচেস রিসিভ হলে `current_stock = current_stock + qty`।
  * `current_stock <= reorder_level` হলে সিস্টেমে স্বয়ংক্রিয় লো-স্টক এলার্ট ট্রিগার হবে।

---

### ২.৪ `inv_suppliers` (সরবরাহকারী / ভেন্ডর টেবিল)
* **ব্যবহারিক প্রয়োগ:** যেসকল কোম্পানি বা পাইকারি দোকান থেকে প্রতিষ্ঠান পণ্য বা স্টেশনারি ক্রয় করে তাদের তথ্য ও বকেয়া লেজার রাখা।
* **ফিল্ডসমূহ:** `id`, `sccode`, `supplier_name`, `company_name`, `phone`, `email`, `address`, `opening_balance`, `status`

---

### ২.৫ `inv_purchases` ও `inv_purchase_items` (পারচেস ও স্টক-ইন)
* **ব্যবহারিক প্রয়োগ:** 
  * `inv_purchases`: পারচেস ভাউচার হেডার (চালান নং, তারিখ, ভেন্ডর আইডি, মোট টাকা, ডিসকাউন্ট, পরিশোধিত টাকা, ক্যাশবুক লিংক)।
  * `inv_purchase_items`: চালানের অন্তর্গত কোন কোন আইটেম কত রেটে কত পিস কেনা হলো তার লাইন-বাই-লাইন রেকর্ড।
* **ক্যাশ বুক লিঙ্ক:** যদি নগদ টাকায় কেনা হয়, তবে `cashbook` টেবিলে স্বয়ংক্রিয়ভাবে `type = 'Expenditure'`, `amount = paid_amount`, `account_head = 'Store Purchase'` হিসেবে এন্ট্রি তৈরি হয়ে আইডিটি `cashbook_entry_id`-তে সংরক্ষিত হবে।

---

### ২.৬ `inv_sales` ও `inv_sale_items` (স্টুডেন্ট সেলস ও পিওএস বিলিং)
* **ব্যবহারিক প্রয়োগ:**
  * `inv_sales`: বিক্রয় মেমোর মূল তথ্য (ইনভয়েস নং, তারিখ, শিক্ষার্থীর আইডি/রোল, ক্রেতার নাম, পেমেন্ট মোড যেমন Cash/bKash, ডিসকাউন্ট, ক্যাশ কালেকশন)।
  * `inv_sale_items`: বিক্রিত প্রতিটি আইটেমের নাম, বিক্রয়মূল্য, ক্রয়মূল্য (গ্রস প্রফিট নির্ণয়ের জন্য) এবং পরিমাণ।
* **ক্যাশ বুক লিঙ্ক:** পিওএস থেকে সংগৃহীত টাকা দিনশেষে বা তাৎক্ষণিকভাবে `cashbook` টেবিলে `type = 'Income'`, `account_head = 'Stationery & Store Sales'` হিসেবে জমা হবে।

---

### ২.৭ `inv_issues` ও `inv_issue_items` (অভ্যন্তরীণ বিভাগীয় বিতরণ)
* **ব্যবহারিক প্রয়োগ:** ক্লাসরুমের জন্য হোয়াইটবোর্ড মার্কার, ডাস্টার, প্রিন্টারের জন্য এ-ফোর পেপার, পরীক্ষার খাতা বা দাপ্তরিক ফাইল বিভিন্ন শিক্ষক/কর্মকর্তার নামে ইস্যু করার হিসাব রাখা।
* **ফিল্ডসমূহ:** `id`, `sccode`, `issue_no`, `issue_date`, `issued_to_type` (Teacher/Staff/Classroom), `issued_to_name`, `issued_by`, `purpose`

---

### ২.৮ `fixed_assets` (স্থায়ী সম্পদ রেজিস্টার)
* **ব্যবহারিক প্রয়োগ:** প্রতিষ্ঠানের জমি, ভবন, ল্যাব সরঞ্জাম, কম্পিউটার, প্রজেক্টর, ফ্যান, এসি, সাউন্ড সিস্টেম ইত্যাদির রেকর্ড।
* **ফিল্ডসমূহ:** 
  * `id`, `sccode`, `asset_tag_code` (ইউনিক ট্যাগ/বারকোড যেমন AST-2026-0045), `asset_name`, `category_id`, `purchase_date`, `purchase_cost`, `location_room` (উদাঃ Computer Lab 2), `custodian_name` (দায়িত্বপ্রাপ্ত ব্যক্তি), `warranty_expiry`, `asset_condition` (Good / Under Repair / Damaged / Disposed), `depreciation_rate_pct`, `current_valuation`, `remarks`

---

## ৩. স্ক্রিপ্টসমূহের বিস্তারিত তালিকা ও কাজের বিবরণ (Scripts & Operational Workflow)

সমস্ত স্ক্রিপ্ট রুট ফোল্ডার `d:\XAMPP\htdocs\eimbox-dashboard\eimbox-materio\` তে সংরক্ষিত থাকবে এবং বিদ্যমান লেআউট ফ্রেমওয়ার্ক অনুযায়ী চালিত হবে।

```
eimbox-materio/
├── inventory-dashboard.php    ─── [কমান্ড সেন্টার ও কেপিআই ওভারভিউ]
├── inventory-pos.php          ─── [শিক্ষার্থী/কাউন্টার পিওএস দ্রুত বিলিং টার্মিনাল]
├── inventory-items.php        ─── [পণ্য তালিকা, বারকোড ও স্টক মাস্টার]
├── inventory-purchases.php    ─── [স্টক-ইন ও ভেন্ডর পারচেস এন্ট্রি]
├── inventory-suppliers.php    ─── [সাপ্লায়ার ডিরেক্টরি ও লেজার]
├── inventory-issues.php       ─── [অভ্যন্তরীণ মালামাল বিতরণ রেজিস্টার]
├── fixed-assets.php           ─── [স্থায়ী সম্পদ তদারকি ও রুমভিত্তিক ট্র্যাকিং]
├── inventory-reports.php      ─── [স্টক লেজার, সেলস ও প্রফিট অ্যানালিটিক্স]
└── api/
    └── inventory-action.php   ─── [AJAX ব্যাকএন্ড প্রসেসিং ও অটোমেশন ইঞ্জিন]
```

---

### ৩.১ `inventory-dashboard.php` (ইনভেন্টরি কমান্ড সেন্টার)
* **মূল উদ্দেশ্য:** স্টোর ও সম্পদ ব্যবস্থাপনার সার্বিক চিত্র এক নজরে উপস্থাপন করা।
* **প্রধান সেকশনসমূহ:**
  1. **টপ কেপিআই কার্ডস:**
     * **মোট আইটেম সংখ্যা:** `<i class="bi bi-boxes text-primary"></i>`
     * **বর্তমান স্টক মূল্যায়ন মূল্য:** `<i class="bi bi-cash-stack text-success"></i>`
     * **আজকের মোট বিক্রয়:** `<i class="bi bi-cart-check-fill text-info"></i>`
     * **লো-স্টক সতর্কতা কাউন্টার:** `<i class="bi bi-exclamation-triangle-fill text-danger"></i>`
     * **মোট স্থায়ী সম্পদের মূল্য:** `<i class="bi bi-buildings-fill text-warning"></i>`
  2. **দ্রুত অ্যাকশন বাটন:**
     * New Sale (POS) `<i class="bi bi-cart-plus"></i>`
     * Stock In / Purchase `<i class="bi bi-bag-plus"></i>`
     * Issue Item `<i class="bi bi-box-arrow-right"></i>`
     * Add Asset `<i class="bi bi-plus-circle"></i>`
  3. **রিয়েল-টাইম টেবিল ও চার্ট:**
     * **Critical Stock Alert Table:** যেসকল পণ্যের স্টক `reorder_level` এর নিচে নেমে গেছে (পাশে 'Re-Order' বাটন সহ)।
     * **Top Fast-Moving Items:** চলতি মাসের সর্বোচ্চ বিক্রিত ৫টি আইটেম।
     * **Recent Transactions:** সর্বশেষ ১০টি সেলস ও ইস্যু ট্রানজ্যাকশন।

---

### ৩.২ `inventory-pos.php` (দ্রুত বিক্রয় টার্মিনাল / POS Terminal)
* **মূল উদ্দেশ্য:** কাউন্টারে দাঁড়ানো শিক্ষার্থীদের তাৎক্ষণিক বিলিং ও রসিদ প্রদান।
* **ইন্টারফেস লেআউট (২-কলাম গ্রিড):**
  * **বাম কলাম (পণ্য নির্বাচন ও সার্চ):**
    * **বারকোড স্ক্যানার ইনপুট:** বারকোড রিডার দিয়ে স্ক্যান করলেই আইটেম সরাসরি কার্টে যুক্ত হবে।
    * **লাইভ সার্চ বার:** পণ্যের নাম বা কোড লিখতেই ড্রপডাউনে ফিল্টার হবে।
    * **ক্যাটাগরি পিলস বাটন:** (All, Diary, Notebook, Bags, Pens, Uniforms)।
    * **আইটেম কার্ড গ্রিড:** ছবির আইকন, পণ্যের নাম, স্টক সংখ্যা এবং মূল্য সহ ক্লিকেবল কার্ড।
  * **ডান কলাম (কার্ট ও চেকআউট রসিদ):**
    * **শিক্ষার্থী তথ্য:** Student ID (STID) বা Roll দিয়ে সার্চ করলে নাম, ক্লাস ও সেকশন অটো-লোড হবে (বা কাউন্টার কাস্টমার হিসেবে রাখা যাবে)।
    * **ডায়নামিক কার্ট টেবিল:** আইটেম নাম, ইউনিট রেট, কোয়ান্টিটি ইনক্রিমেন্ট/ডিক্রিমেন্ট (+/-), সাব-টোটাল, ডিলিট বাটন।
    * **বিলিং ক্যালকুলেশন:** মোট মূল্য, ছাড় (Discount), নেট দেয় টাকা, প্রাপ্ত টাকা (Paid Amount), ফেরত টাকা (Change Return)।
    * **পেমেন্ট মোড সিলেক্টর:** Cash, bKash, Nagad, Card।
    * **চেকআউট বাটন:** `<button class="btn btn-success btn-lg w-100"><i class="bi bi-printer-fill me-2"></i>Pay & Print Receipt</button>`
* **অ্যাকশন ও লজিক:**
  * চেকআউট বাটনে ক্লিক করলে `SweetAlert2` কনফার্মেশন প্রম্পট আসবে।
  * নিশ্চিত করলে AJAX এর মাধ্যমে `api/inventory-action.php`-তে ডাটা যাবে:
    1. `inv_sales` ও `inv_sale_items` টেবিলে ডাটা ইনসার্ট।
    2. `inv_items` টেবিলে `current_stock` বিয়োগ হবে।
    3. `cashbook` টেবিলে Income ভাউচার এন্ট্রি সম্পন্ন হবে।
    4. একটি ক্লাসিকাল ৮০মিমি থার্মাল পিওএস প্রিন্ট উইন্ডো ওপেন হবে।

---

### ৩.৩ `inventory-items.php` (আইটেম ও স্টক মাস্টার কন্ট্রোল)
* **মূল উদ্দেশ্য:** স্টোরের প্রতিটি পণ্যের ডাটাবেস তৈরি, মূল্য নির্ধারণ ও স্টক লেভেল আপডেট।
* **ফাংশনালিটি:**
  1. **আইটেম টেবিল ভিউ:** DataTables ইন্টিগ্রেশন সহ আইটেম কোড, নাম, ক্যাটাগরি, ইউনিট, ক্রয়মূল্য, বিক্রয়মূল্য, বর্তমান স্টক, স্ট্যাটাস ব্যাজ (In Stock / Low Stock / Out of Stock)।
  2. **Add / Edit Item Modal:**
     * আইটেম টাইপ (`SaleItem` / `Consumable` / `Asset`)
     * বারকোড (অটো-জেনারেট বাটন সহ)
     * ক্রয়মূল্য ও বিক্রয়মূল্য ইনপুট
     * রি-অর্ডার এলার্ট লেভেল সংখ্যা
     * র‍্যাক/সেল্ফ লোকেশন
  3. **Stock Adjustment Modal:** কোনো পণ্য ক্ষতিগ্রস্ত বা নষ্ট হলে স্টক অ্যাডজাস্ট করার অপশন (Damaged / Lost / Expired)।
  4. **বারকোড লেবেল জেনারেটর:** নির্বাচিত পণ্যের বারকোড স্টিকার প্রিন্ট করার প্রিভিউ।

---

### ৩.৪ `inventory-purchases.php` (পারচেস ও স্টক-ইন)
* **মূল উদ্দেশ্য:** পাইকারি বাজার বা প্রকাশনী/সাপ্লায়ার থেকে পণ্য কিনে স্টকে যুক্ত করা।
* **ফাংশনালিটি:**
  1. চালান নম্বর, ক্রয়ের তারিখ এবং সাপ্লায়ার নির্বাচন।
  2. মাল্টি-আইটেম অ্যাড রো (আইটেম সিলেক্ট -> কোয়ান্টিটি -> ক্রয় রেট -> সাব-টোটাল)।
  3. ডিসকাউন্ট ও ভ্যাট সমন্বয়।
  4. পেমেন্ট মোড (Cash / Bank / Cheque / Due)।
  5. **Auto Cashbook Sync Checkbox:** সক্রিয় থাকলে স্বয়ংক্রিয়ভাবে ক্যাশবুকে Expenditure হেড "Store Purchase" এর অধীনে খরচ ভাউচার তৈরি হবে।

---

### ৩.৫ `inventory-suppliers.php` (সাপ্লায়ার ও ভেন্ডর লেজার)
* **মূল উদ্দেশ্য:** সরবরাহকারীদের যোগাযোগের তথ্য ও লেনদেন হিস্ট্রি সংরক্ষণ।
* **ফাংশনালিটি:**
  * সাপ্লায়ার তালিকা ও নতুন সাপ্লায়ার যোগ/সম্পাদনা।
  * সাপ্লায়ার ভিত্তিক মোট ক্রয়, মোট পরিশোধ ও বকেয়া (Due Balance) সামারি।
  * সাপ্লায়ার পেমেন্ট ভাউচার প্রদান ও ক্যাশবুক সিঙ্ক।

---

### ৩.৬ `inventory-issues.php` (অভ্যন্তরীণ মালামাল বিতরণ)
* **মূল উদ্দেশ্য:** প্রতিষ্ঠানের শিক্ষক, কর্মচারী বা শ্রেণিকক্ষে ব্যবহারের জন্য স্টেশনারি বিতরণ করা।
* **ফাংশনালিটি:**
  * ইস্যু ভাউচার তৈরি (কার নামে ইস্যু করা হচ্ছে, কোন বিভাগ/ক্লাসরুম, কী উদ্দেশ্যে)।
  * কনজিউমেবল আইটেম নির্বাচন ও পরিমাণ প্রদান।
  * সেভ করার সাথে সাথে স্টক থেকে উক্ত পরিমাণ আইটেম কমে যাবে এবং ইস্যু হিস্ট্রি লগে যুক্ত হবে।

---

### ৩.৭ `fixed-assets.php` (স্থায়ী সম্পদ রেজিস্টার ও রুম অডিট)
* **মূল উদ্দেশ্য:** প্রতিষ্ঠানের দীর্ঘমেয়াদী সম্পদসমূহ নিখুঁতভাবে ট্র্যাকিং করা।
* **ফাংশনালিটি:**
  1. **অ্যাসেট ট্যাগিং:** প্রতিটি সম্পদের জন্য ইউনিক ট্যাগ তৈরি (যেমন: `AST-LAB1-PC-01`)।
  2. **রুম/লোকেশন ভিত্তিক ফিল্টার:** যেমন Principal Room, Computer Lab 1, Science Lab, Teachers Lounge ইত্যাদি রুমে কোন কোন সম্পদ আছে তা দেখা।
  3. **কন্ডিশন স্ট্যাটাস ট্র্যাকিং:** Good (`<span class="badge bg-success">Good</span>`), Under Repair (`<span class="badge bg-warning">Repair</span>`), Damaged (`<span class="badge bg-danger">Damaged</span>`)।
  4. **অবচয় ও বর্তমান মূল্যায়ন:** বার্ষিক অবচয় হার অনুযায়ী বর্তমান সম্পদের আনুমানিক মূল্য নির্ধারণ।

---

### ৩.৮ `inventory-reports.php` (রিপোর্ট ও অ্যানালিটিক্স)
* **মূল উদ্দেশ্য:** প্রতিষ্ঠানের অডিট, স্টক নিরীক্ষা ও লাভের হিসাব যাচাই।
* **রিপোর্টের ধরনসমূহ:**
  1. **Daily Sales & Cash Summary:** তারিখ অনুযায়ী মোট বিক্রয়, সংগৃহীত ক্যাশ ও বিক্রয়ভিত্তিক গ্রস প্রফিট।
  2. **Stock Valuation Report:** প্রতিটি পণ্যের বর্তমান মজুদের মোট ক্রয়মূল্য ও বিক্রয়মূল্যের পরিসংখ্যান।
  3. **Low Stock & Re-Order Report:** অবিলম্বে রি-অর্ডার করার মতো পণ্যের তালিকা।
  4. **Departmental Issue Report:** কোন বিভাগে কত টাকার কনজিউমেবল মালামাল ব্যবহার হয়েছে তার রিপোর্ট।
  5. **Fixed Assets Audit Sheet:** রুম অনুযায়ী সম্পদের তালিকা প্রিন্ট করে সরেজমিনে অডিট করার রিপোর্ট।

---

### ৩.৯ `api/inventory-action.php` (নিরাপদ AJAX হ্যান্ডলার)
* **মূল উদ্দেশ্য:** ফ্রন্টএন্ডের সমস্ত রিকোয়েস্ট নিরাপদ ও দ্রুতগতিতে প্রসেস করা।
* **অ্যাকশন এন্ডপয়েন্টসমূহ:**
  * `action=search_items_barcode` : বারকোড দিয়ে ইনস্ট্যান্ট আইটেম ফেচ।
  * `action=search_student` : রোল বা আইডি দিয়ে শিক্ষার্থী তথ্য লোড।
  * `action=process_pos_sale` : পিওএস বিক্রয় এক্সিকিউট, স্টক মাইনাস ও ক্যাশবুক সিঙ্ক।
  * `action=save_purchase` : পারচেস সেভ, স্টক প্লাস ও ক্যাশবুক এক্সপেন্স পোস্টিং।
  * `action=save_item` : আইটেম তৈরি ও এডিট।
  * `action=adjust_stock` : ড্যামেজ বা অডিট অ্যাডজাস্টমেন্ট।
  * `action=save_asset` : স্থায়ী সম্পদ এন্ট্রি ও লোকেশন আপডেট।

---

## ৪. ক্যাশবুকের সাথে অটোমেটিক ইন্টিগ্রেশন মেকানিজম (Cashbook Integration Architecture)

ক্যাশবুকে যাতে ম্যানুয়ালি ডাবল এন্ট্রি না করতে হয়, সেজন্য পিএইচপি ব্যাকএন্ডে স্বয়ংক্রিয় প্রিপেয়ার্ড স্টেটমেন্ট ব্যবহার করা হবে:

```php
// =========================================================================
// POS SALES -> CASHBOOK INCOME POSTING SNIPPET (In api/inventory-action.php)
// =========================================================================
if ($payment_mode === 'Cash' && $paid_amount > 0) {
    // ১. সেলস এন্ট্রি সম্পন্ন হওয়া
    $memo_desc = "Store POS Sale - Invoice #$invoice_no ($customer_name)";
    $target_head = 12; // Example: Stationery/Store Income Head ID
    $target_subhead = 45; // Example: Student Item Sales Subhead ID
    $cur_month = intval(date('n'));
    $cur_year = intval(date('Y'));
    $session_val = $sessionyear ?? date('Y');

    $cb_stmt = $conn->prepare("INSERT INTO cashbook 
        (sccode, sessionyear, month, year, date, slots, account_head, account_sub_head, partid, particulars, amount, income, expenditure, type, memono, entryby, entrytime, status) 
        VALUES (?, ?, ?, ?, CURDATE(), 'General', ?, ?, ?, ?, ?, ?, 0.00, 'Income', ?, ?, NOW(), 1)");
    
    $cb_stmt->bind_param("isiisiiisdsis", 
        $sccode, $session_val, $cur_month, $cur_year, 
        $target_head, $target_subhead, $target_subhead, 
        $memo_desc, $paid_amount, $paid_amount, 
        $sale_id, $usr
    );
    $cb_stmt->execute();
    $cashbook_id = $conn->insert_id;

    // সেলস রেকর্ডে cashbook_entry_id আপডেট
    $up_sale = $conn->prepare("UPDATE inv_sales SET cashbook_entry_id = ? WHERE id = ? AND sccode = ?");
    $up_sale->bind_param("iii", $cashbook_id, $sale_id, $sccode);
    $up_sale->execute();
}
```

---

## ৫. ইউজার ইন্টারফেস ও ফ্রন্টএন্ড স্ট্যান্ডার্ড নির্দেশিকা (UI/UX Strict Guidelines)

1. **সিএসএস স্ট্যান্ডার্ড:**
   * কোনো নতুন `.css` ফাইল যোগ করা হবে না।
   * সমস্ত স্টাইলিং Bootstrap 5 ইউটিলিটি ক্লাস (`card`, `table-responsive`, `badge`, `d-flex`, `align-items-center`, `gap-2`, `btn-outline-*`) দিয়ে করা হবে।
2. **আইকন স্ট্যান্ডার্ড:**
   * সমস্ত আইকন Bootstrap Icons (`<i class="bi bi-..."></i>`) হতে হবে। ইমোজি সম্পূর্ণ নিষিদ্ধ।
   * উদাহরণ:
     * ড্যাশবোর্ড: `<i class="bi bi-speedometer2"></i>`
     * পিওএস: `<i class="bi bi-cart4"></i>`
     * আইটেম মাস্টার: `<i class="bi bi-box-seam"></i>`
     * পারচেস: `<i class="bi bi-bag-check"></i>`
     * সম্পদ: `<i class="bi bi-buildings"></i>`
     * প্রিন্ট: `<i class="bi bi-printer"></i>`
     * ডিলিট: `<i class="bi bi-trash3"></i>`
     * এডিট: `<i class="bi bi-pencil-square"></i>`
3. **সুইটঅ্যালার্ট২ স্ট্যান্ডার্ড (SweetAlert2):**
   * ডিলিট বা ক্যাশ কালেকশন কনফার্মেশন:
     ```javascript
     Swal.fire({
         title: 'Are you sure?',
         text: "This item will be permanently removed from inventory!",
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#d33',
         cancelButtonColor: '#6c757d',
         confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, delete it!'
     }).then((result) => {
         if (result.isConfirmed) {
             // AJAX call
         }
     });
     ```
   * সফল অপারেশন টোস্ট:
     ```javascript
     const Toast = Swal.mixin({
         toast: true,
         position: 'top-end',
         showConfirmButton: false,
         timer: 2500,
         timerProgressBar: true
     });
     Toast.fire({
         icon: 'success',
         title: 'Transaction completed successfully!'
     });
     ```

---

## ৬. রোল-বেসড অ্যাক্সেস কন্ট্রোল (RBAC Permissions)

| ভূমিকা (Role) | ড্যাশবোর্ড | পিওএস সেলস | আইটেম ও স্টক ম্যানেজ | পারচেস ও ভেন্ডর | স্থায়ী সম্পদ | রিপোর্টস |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Super Admin / Principal** | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস |
| **Store Manager / Accountant** | ভিউ | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস | ফুল এক্সেস |
| **POS Cashier / Sales Staff** | সীমিত ভিউ | শুধুমাত্র বিক্রয় | শুধুমাত্র ভিউ | নো এক্সেস | নো এক্সেস | দৈনিক সেলস |
| **Teacher / Department Head** | নো এক্সেস | নো এক্সেস | নো এক্সেস | নো এক্সেস | রিকুইজিশন ভিউ | নো এক্সেস |

---

## ৭. বাস্তবায়ন ও ডেপ্লয়মেন্ট চেকভেক্টর (Implementation Checklist)

- [x] **ধাপ ১:** MySQL ডাটাবেসে টেবিল তৈরির জন্য `core/inventory_db.php` স্ক্রিপ্ট তৈরি সম্পন্ন (`inv_categories`, `inv_units`, `inv_items`, `inv_suppliers`, `inv_purchases`, `inv_purchase_items`, `inv_sales`, `inv_sale_items`, `inv_issues`, `inv_issue_items`, `fixed_assets`, `inv_stock_adjustments`)।
- [x] **ধাপ ২:** `api/inventory-action.php` তৈরি করে CRUD, বারকোড সার্চ, শিক্ষার্থী অনুসন্ধান এবং পিওএস ব্যাকএন্ড লজিক প্রিপেয়ার্ড স্টেটমেন্ট সহ কার্যকর করা হয়েছে।
- [x] **ধাপ ৩:** `inventory-items.php` তৈরি করে আইটেম তালিকা, স্টক ও ক্যাটাগরি এবং স্টক অ্যাডজাস্টমেন্ট সম্পন্ন করা হয়েছে।
- [x] **ধাপ ৪:** `inventory-pos.php` তৈরি করে বারকোড রিডার, ডায়নামিক কার্ট ও থার্মাল স্লিপ প্রিন্টসহ ক্যাশবুক অটো-পোস্টিং কার্যকর করা হয়েছে।
- [x] **ধাপ ৫:** `inventory-purchases.php`, `inventory-suppliers.php` ও `inventory-issues.php` তৈরি করে সরবরাহকারী ও অভ্যন্তরীণ বিতরণ সম্পন্ন করা হয়েছে।
- [x] **ধাপ ৬:** `fixed-assets.php` সম্পদ রেজিস্টার ও রুম ফিল্টারিং কার্যকর করা হয়েছে।
- [x] **ধাপ ৭:** `inventory-dashboard.php` ও `inventory-reports.php` তৈরি করে সার্বিক কেপিআই ও অডিট রিপোর্ট কার্যকর করা হয়েছে।
- [x] **ধাপ ৮:** `inventory-reports.php`-তে `templete/letter-head-01.php` ইন্টিগ্রেশন, A4 Portrait ফরম্যাট এবং প্রাতিষ্ঠানিক সিগনেচার ব্লকসহ ফুল প্রিন্ট সুবিধা যুক্ত করা হয়েছে।

