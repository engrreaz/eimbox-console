# EIMBox - DBBL Rocket Bill Payment API Integration & Testing Plan

> **Official Specification:** Dutch-Bangla Bank Limited (DBBL) Bill Payment Reconciliation API Document V1.1  
> **Target Production Base URL:** `https://console.eimbox.com/api/payment/v1/`  
> **Local Development URL:** `http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/`  
> **Supported Platform:** Rocket Mobile App, USSD (*322#), Rocket Agent & Core Banking Bill Pay  

---

## ১. গুরুত্বপূর্ণ স্থাপত্য ও নিরাপত্তা নীতিমালা (Architecture & Security Review)

### ১.১ প্রতিষ্ঠানভিত্তিক (per `sccode`) `api_key` ও `secret_key` এর প্রয়োজনীয়তা ও সমাধান
* **প্রশ্ন:** সিকিউরিটির জন্য আমরা প্রতিটি `sccode` এর বিপরীতে কি `api_key`, `secret_key` সেট করা প্রয়োজন হবে?
* **বাস্তবায়ন সিদ্ধান্ত:** 
  1. **গ্লোবাল DBBL পার্টনারশিপ ক্রেডেনশিয়াল (Default Aggregator Mode):**
     * ডিবিবিএল রকেট যখন একটি সেন্ট্রাল গেটওয়ে বা এগ্রিগেটর হিসেবে EIMBox-এর সাথে সংযুক্ত হয়, তখন তারা সাধারণত **একটি নির্দিষ্ট Basic Auth ক্রেডেনশিয়াল** (`ROCKET_DEFAULT_USER`, `ROCKET_DEFAULT_PASS`) ব্যবহার করে রিকোয়েস্ট পাঠায়।
  2. **প্রতিষ্ঠানভিত্তিক Biller Key (Independent School Mode):**
     * যদি কোনো শিক্ষাপ্রতিষ্ঠানের নিজস্ব স্বতন্ত্র মার্চেন্ট একাউন্ট থাকে, তবে তাদের জন্য ডেটাবেজে `payment_gateway_keys` টেবিলে প্রতিটি `sccode`-এর বিপরীতে আলাদা `biller_id`, `api_key` এবং `secret_key` কনফিগার করে রাখা হয়েছে।
  3. **অটো-ফলব্যাক সিকিউরিটি:**
     * আমাদের তৈরি `bootstrap.php` স্বয়ংক্রিয়ভাবে প্রথমে রিকোয়েস্টের ক্রেডেনশিয়াল গ্লোবাল পার্টনার হিসেবে যাচাই করে, অথবা সংশ্লিষ্ট `sccode`-এর `payment_gateway_keys` টেবিল থেকে নির্দিষ্ট কি (key) যাচাই করে। ফলে উভয় পরিস্থিতিতেই ১০০% নিরাপদ যোগাযোগ বজায় থাকে।

---

### ১.২ ডেটাবেজ ম্যাপিং ও সেশন ডাটার সুনির্দিষ্ট বিভাজন
* **`students` টেবিল:** 
  * এখানে শিক্ষার্থীদের স্থায়ী ডেমোগ্রাফিক প্রোফাইল সংরক্ষিত থাকে (যেমন: `sccode`, `stid`, `stnameeng`, `stnameben`, `guarmobile`, পিতা/মাতার নাম ইত্যাদি)।
  * রকেটের রেসপন্সে **`customerName`** ফিল্ডে সরাসরি `students.stnameeng` প্রদান করা হয়।
* **`sessioninfo` টেবিল:** 
  * শিক্ষার্থীর শিক্ষাবর্ষভিত্তিক শ্রেণির তথ্য (যেমন: `sccode`, `stid`, `sessionyear`, `classname`, `sectionname`, `rollno`) এখানে থাকে।
  * রকেটের রেসপন্সে **`optionalInfo1`** ও **`optionalInfo2`**-তে শ্রেণি, শাখা ও রোল প্রদর্শনের জন্য `sessioninfo` থেকে ডেটা আনা হয়।
* **`stfinance` টেবিল (বকেয়া লেজার):**
  * শিক্ষার্থীর প্রতিটি ফি আইটেমের বিস্তারিত হিসাব।
  * **শর্তাবলী:** `WHERE sccode = ? AND sessionyear LIKE ? AND stid = ? AND month <= ? AND dues > 0`
  * **বকেয়া সূত্র:** `SUM(dues)` — চলতি মাস বা তার আগের মাসের যে সকল আইটেমের বকেয়া অবশিষ্ট রয়েছে সেগুলোর যোগফল। (অক্টোবর বা তার বেশি মাস হলে পুরো ১২ মাসের বকেয়া ধরা হয়)।

---

## ২. রকেট পে-বিল এপিআই স্পেসিফিকেশন (API Endpoints)

### ২.১ Validation API (বিল যাচাই ও বকেয়া অনুসন্ধান)

* **URL:** `https://console.eimbox.com/api/payment/v1/paymentValidation` (অথবা `paymentValidation.php`)
* **HTTP Method:** `POST`
* **Headers:**
  * `Content-Type: application/json`
  * `Authorization: Basic Um9ja2V0OlJvY2tldDEyMw==` (Base64 of `Rocket:Rocket123`)

#### রকেট থেকে আসা রিকোয়েস্ট (Request Schema):
```json
{
  "usrid": "Rocket",
  "pswrd": "Rocket123",
  "refNo1": "1001-10502",
  "refNo2": "",
  "refNo3": "2026"
}
```
> **নোট:** `refNo1` এ `sccode-stid` ফরম্যাটে (যেমন: `1001-10502`) অথবা `refNo1` এ `stid` ও `refNo2` এ `sccode` দিলে সিস্টেম স্বয়ংক্রিয়ভাবে দুটিকেই সাপোর্ট করে।

#### EIMBox সফল রেসপন্স (Success Response - Amount Provided by Partner):
```json
{
  "errCode": "00",
  "errMsg": "Successful",
  "customerName": "RAKIBUL HASAN",
  "optionalInfo1": "Class: Nine (A)",
  "optionalInfo2": "Roll: 05 | Year: 2026",
  "optionalInfo3": "Institute SC: 1001",
  "amount": "1500"
}
```

#### এরর রেসপন্স (Error Response Examples):
* শিক্ষার্থী পাওয়া না গেলে:
  ```json
  {
    "errCode": "01",
    "errMsg": "Student Not Found for given ID and Institution"
  }
  ```
* অথেন্টিকেশন ব্যর্থ হলে:
  ```json
  {
    "errCode": "01",
    "errMsg": "Invalid Authentication"
  }
  ```

---

### ২.২ Confirmation API (পেমেন্ট নিশ্চিতকরণ ও লেজার আপডেট)

* **URL:** `https://console.eimbox.com/api/payment/v1/paymentConfirmation` (অথবা `paymentConfirmation.php`)
* **HTTP Method:** `POST`
* **Headers:** `Content-Type: application/json`

#### রকেট থেকে আসা রিকোয়েস্ট (Request Schema):
```json
{
  "userid": "Rocket",
  "password": "Rocket123",
  "txnid": "DBBL202609110001",
  "txndate": "11-Sep-2026 04:50:00 AM",
  "refno1": "1001-10502",
  "refno2": "",
  "refno3": "2026",
  "amount": "1500"
}
```

#### EIMBox ব্যাকএন্ড কার্যপ্রণালী:
1. **Idempotency Guard:** `rocket_transactions` টেবিলে `txnid` পূর্বে প্রসেস করা থাকলে তাৎক্ষণিক `00` সাকসেস রিটার্ন করে (ডাবল পোস্টিং প্রতিরোধ)।
2. **`stfinance` আপডেট:** শিক্ষার্থীর বকেয়া থাকা আইটেমগুলোতে ক্রমানুসারে `paid = paid + amt`, `dues = dues - amt`, এবং রসিদ নম্বর হিসেবে `pr1no = txnid`, `pr1by = 'Rocket'` সেট করে।
3. **`stpr` রসিদ তৈরি:** রসিদ টেবিলে পেমেন্টের ভাউচার তৈরি হয়।
4. **অডিট লগ:** `rocket_transactions` টেবিলে ট্রানজেকশনের সম্পূর্ণ রিকোয়েস্ট ও স্ট্যাটাস সংরক্ষিত হয়।

#### সফল রেসপন্স (Success Response):
```json
{
  "errCode": "00",
  "errMsg": "Payment Information Updated Successfully"
}
```

---

### ২.৩ Inquiry API (রিকনসিলিয়েশন ও স্ট্যাটাস যাচাই)

* **URL:** `https://console.eimbox.com/api/payment/v1/getPaymentStatus` (অথবা `getPaymentStatus.php`)
* **HTTP Method:** `POST`
* **Headers:** `Content-Type: application/json`

#### রকেট থেকে আসা রিকোয়েস্ট (Request Schema):
```json
{
  "userid": "Rocket",
  "password": "Rocket123",
  "txnid": "DBBL202609110001",
  "refno1": "1001-10502"
}
```

#### সফল রেসপন্স (Transaction Found & Processed):
```json
{
  "errCode": "00",
  "errMsg": "Payment Information Updated Successfully"
}
```

#### ব্যর্থ রেসপন্স (Transaction Not Found):
```json
{
  "errCode": "01",
  "errMsg": "Transaction Not Found"
}
```

---

## ৩. এপিআই টেস্টিং নির্দেশিকা (Testing Guide)

আপনি **Postman**, **cURL (Terminal)** অথবা **PowerShell** দিয়ে নিচের কমান্ডগুলো চালিয়ে এখনই পরীক্ষা করতে পারবেন:

### টেস্ট ১: Validation API টেস্ট (PowerShell / cURL)

```powershell
# PowerShell:
$headers = @{
    "Content-Type" = "application/json"
    "Authorization" = "Basic Um9ja2V0OlJvY2tldDEyMw=="
}
$body = @{
    "usrid" = "Rocket"
    "pswrd" = "Rocket123"
    "refNo1" = "1001-10502"
    "refNo2" = ""
    "refNo3" = "2026"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/paymentValidation.php" -Method Post -Headers $headers -Body $body
```

```bash
# cURL (Linux / macOS / Git Bash):
curl -X POST "https://console.eimbox.com/api/payment/v1/paymentValidation" \
  -H "Content-Type: application/json" \
  -u "Rocket:Rocket123" \
  -d '{
    "usrid": "Rocket",
    "pswrd": "Rocket123",
    "refNo1": "1001-10502",
    "refNo2": "",
    "refNo3": "2026"
  }'
```

---

### টেস্ট ২: Confirmation API টেস্ট

```powershell
# PowerShell:
$body = @{
    "userid" = "Rocket"
    "password" = "Rocket123"
    "txnid" = "TXN" + (Get-Random -Minimum 100000 -Maximum 999999)
    "txndate" = (Get-Date).ToString("dd-MMM-yyyy hh:mm:ss tt")
    "refno1" = "1001-10502"
    "amount" = 500
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/paymentConfirmation.php" -Method Post -Headers @{"Content-Type"="application/json"} -Body $body
```

---

### টেস্ট ৩: Inquiry API টেস্ট

```powershell
# PowerShell:
$body = @{
    "userid" = "Rocket"
    "password" = "Rocket123"
    "txnid" = "TXN123456"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/getPaymentStatus.php" -Method Post -Headers @{"Content-Type"="application/json"} -Body $body
```

---

## ৪. সার্ভার কনফিগারেশন ও প্রোডাকশন চেকলিস্ট

1. **সার্ভার পাথ ও URL:**
   * সার্ভারে ফাইলগুলো `https://console.eimbox.com/api/payment/v1/` ফোল্ডারে থাকবে।
   * `.htaccess` ফাইলে `RewriteRule` কনফিগার করা হয়েছে যাতে রকেট সিস্টেম `.php` ছাড়া (যেমন `/paymentValidation`) অথবা `.php` সহ দুটোতেই কল করতে পারে।
2. **লগ ডিরেক্টরি:**
   * প্রতিটি লেনদেন, ইনকামিং রিকোয়েস্ট এবং আউটগোয়িং রেসপন্স স্বয়ংক্রিয়ভাবে `/api/payment/v1/logs/rocket-YYYY-MM-DD.log` ফাইলে অডিট ও সমস্যা বিশ্লেষণের জন্য জমা হবে।
3. **ডিবিবিএল রকেটকে সরবরাহ করার তথ্য:**
   * **Validation URL:** `https://console.eimbox.com/api/payment/v1/paymentValidation`
   * **Confirmation URL:** `https://console.eimbox.com/api/payment/v1/paymentConfirmation`
   * **Inquiry URL:** `https://console.eimbox.com/api/payment/v1/getPaymentStatus`
   * **HTTP Method:** `POST`
   * **Request Format:** `JSON`
   * **Authentication:** `Basic Authentication` ও Payload Credentials
