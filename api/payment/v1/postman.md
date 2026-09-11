Postman দিয়ে এই তিনটি API টেস্ট করার জন্য নিচে ধাপে ধাপে বিস্তারিত গাইড দেওয়া হলো।

প্রাথমিক প্রস্তুতি:
লোকালহোস্ট বেস ইউআরএল: http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/
Common Header:
Key: Content-Type
Value: application/json
ধাপ ১: Validation API টেস্ট (বিল যাচাই ও নাম/বকেয়া খোঁজা)
অভিভাবক যখন রকেটে স্টুডেন্ট আইডি ও স্কুল কোড প্রদান করেন, তখন এই এপিআই-তে রিকোয়েস্ট আসে।

Method: POST

URL:
http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/paymentValidation.php
(লাইভ সার্ভারে: https://console.eimbox.com/api/payment/v1/paymentValidation)

Headers ট্যাব:

Content-Type : application/json
Body ট্যাব:

raw রেডিও বাটনে ক্লিক করে ড্রপডাউন থেকে JSON নির্বাচন করুন।
নিচের JSON কোডটি পেস্ট করুন:
json
{
  "usrid": "Rocket",
  "pswrd": "Rocket123",
  "refNo1": "1000000001",
  "refNo2": "1001",
  "refNo3": ""
}
(নোট: refNo1-এ আপনার ডাটাবেজের যেকোনো শিক্ষার্থীর ১০ ডিজিট stid এবং refNo2-এ তার প্রতিষ্ঠানের ৬ ডিজিট sccode/eiin বসাবেন)

Send বাটনে ক্লিক করুন।

প্রত্যাশিত সফল রেসপন্স (HTTP 200 OK):
json
{
  "errCode": "00",
  "errMsg": "Successful",
  "customerName": "MD. RAHIM AHMED",
  "optionalInfo1": "Class: Ten (A)",
  "optionalInfo2": "Roll: 01 | Year: 2026",
  "optionalInfo3": "Institute SC: 1001",
  "amount": "1200"
}
ধাপ ২: Confirmation API টেস্ট (টাকা পরিশোধ নিশ্চিতকরণ)
গ্রাহক যখন পিন দিয়ে পেমেন্ট সফল করেন, তখন রকেট ব্যাংক সার্ভার থেকে এই এপিআই কল করে।

Method: POST

URL:
http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/paymentConfirmation.php

Headers ট্যাব:

Content-Type : application/json
Body ট্যাব (raw -> JSON):

json
{
  "userid": "Rocket",
  "password": "Rocket123",
  "txnid": "DBBLTESTTXN001",
  "txndate": "2026-09-11 13:45:00",
  "refno1": "1000000001",
  "refno2": "1001",
  "refno3": "",
  "amount": "1200"
}
(নোট: প্রতিবার নতুন টেস্টের সময় txnid কিছুটা পরিবর্তন করে নিতে পারেন, যেমন DBBLTESTTXN002, কারণ ডাবল পেমেন্ট প্রতিরোধের জন্য একই txnid দুইবার গ্রহণ করা হয় না)

Send বাটনে ক্লিক করুন।

প্রত্যাশিত সফল রেসপন্স (HTTP 200 OK):
json
{
  "errCode": "00",
  "errMsg": "Payment Information Updated Successfully"
}
(এই কলের পর ডাটাবেজে stfinance বকেয়া শূন্য হবে, stpr টেবিলে ভাউচার যুক্ত হবে এবং rocket_transactions টেবিলে ট্রানজেকশন সেভ হবে)

ধাপ ৩: Status Inquiry API টেস্ট (পেমেন্ট স্ট্যাটাস পুনঃযাচাই)
দিনশেষে অডিট বা কোনো কারণে নেটওয়ার্ক ড্রপ হলে রকেট এই এপিআই কল করে পেমেন্ট সফল হয়েছিল কিনা নিশ্চিত হয়।

Method: POST
URL:
http://localhost/eimbox-dashboard/eimbox-materio/api/payment/v1/getPaymentStatus.php
Headers ট্যাব:
Content-Type : application/json
Body ট্যাব (raw -> JSON):
json
{
  "userid": "Rocket",
  "password": "Rocket123",
  "txnid": "DBBLTESTTXN001",
  "refno1": "1000000001",
  "refno2": "1001"
}
Send বাটনে ক্লিক করুন।
প্রত্যাশিত সফল রেসপন্স (HTTP 200 OK):
json
{
  "errCode": "00",
  "errMsg": "Payment Information Updated Successfully"
}
(যদি ট্রানজেকশন আইডি ডাটাবেজে না থাকে, তবে আসবে {"errCode": "01", "errMsg": "Transaction Not Found"})
সংক্ষেপে চেকলিস্ট:
টেস্ট নং	API নাম	মেথড	মূল প্যারামিটার	কাঙ্ক্ষিত আউটপুট
১	paymentValidation.php	POST	refNo1 (stid), refNo2 (sccode)	ছাত্রের নাম, ক্লাস, রোল ও বকেয়া ফি (amount)
২	paymentConfirmation.php	POST	txnid, amount, refno1, refno2	errCode: 00 (পেমেন্ট সেটেলমেন্ট সম্পন্ন)
৩	getPaymentStatus.php	POST	txnid	errCode: 00 (ট্রানজেকশন সফল ছিল)
1:44 PM

