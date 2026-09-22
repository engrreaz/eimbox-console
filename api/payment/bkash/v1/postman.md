# EIMBox - bKash Pay Bill Postman Testing Guide

This guide outlines how to use Postman to test and verify the **bKash Pay Bill Module**.

---

## 1. Importing the Collection

1. Open **Postman**.
2. Click **Import** (top left).
3. Select [`postman_collection.json`](file:///d:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/api/payment/bkash/v1/postman_collection.json) located at:
   ```
   d:\XAMPP\htdocs\eimbox-dashboard\eimbox-materio\api\payment\bkash\v1\postman_collection.json
   ```
4. Click **Import**.

---

## 2. Environment Variables Configuration

The collection includes pre-configured variables:

| Variable | Staging / Local Value | Production Value | Description |
|---|---|---|---|
| `base_url` | `http://localhost/eimbox-dashboard/eimbox-materio/api/payment/bkash/v1` | `https://console.eimbox.com/api/payment/bkash/v1` | API Base Endpoint |
| `bkash_user` | `bKash00` | Assigned by bKash | Partner Biller Username |
| `bkash_pass` | `bKash12321` | Assigned by bKash | Partner Biller Password |
| `student_ref_id` | `1031870001` | Live Student ID | Test Student Reference ID |
| `bkash_trxid` | `BKASH_TEST_12345` | Real bKash TrxID | Transaction ID to verify |

---

## 3. Included Requests

1. **`1. CheckBill (POST JSON)`**: Submits a JSON body to fetch student name, class, roll, due date, and payable dues.
2. **`2. CheckBill (GET Query String)`**: Submits query parameters in the URL to test bKash GET dispatchers.
3. **`3. BillPayment (Confirm Payment)`**: Settle dues, generate sequential receipt number (`prno`), and update `stfinance` and `stpr`.
4. **`4. RecheckPayment (Inquire Status)`**: Inquires settlement status by `trxid`.
