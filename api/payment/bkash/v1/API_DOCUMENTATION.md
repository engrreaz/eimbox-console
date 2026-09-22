# EIMBox - bKash Pay Bill Integration Specification

**Document Version:** 2.0  
**Target Audience:** bKash Pay Bill Technical Integration Team, Aggregator Developers, MFS Solution Architects, System Administrators  
**Protocol:** HTTPS / REST (JSON & URL-Encoded Query Parameters)  
**Production Base URL:** `https://console.eimbox.com/api/payment/bkash/v1/`  
**Staging / Test Base URL:** `http://localhost/eimbox-dashboard/eimbox-materio/api/payment/bkash/v1/`  
**Standard Compliance:** bKash Pay Bill Module Technical Specification v2.0 (`PayBill_API_For_BKash_v2.docx`)  

---

## Table of Contents

1. [System Architecture & Sequence Flow](#1-system-architecture--sequence-flow)
2. [Security, Whitelisting & Authentication](#2-security-whitelisting--authentication)
3. [Reference & Parameter Resolution Standard](#3-reference--parameter-resolution-standard)
4. [API Endpoints Specification](#4-api-endpoints-specification)
   - [4.1 Check Bill API (`/CheckBill`)](#41-check-bill-api-checkbill)
   - [4.2 Bill Payment API (`/BillPayment`)](#42-bill-payment-api-billpayment)
   - [4.3 Recheck Bill Payment API (`/RecheckPayment`)](#43-recheck-bill-payment-api-recheckpayment)
5. [Standard Error Codes & Handling](#5-standard-error-codes--handling)
6. [Financial Ledger Settlement & Receipt Numbering](#6-financial-ledger-settlement--receipt-numbering)
7. [Database Schema & Multi-Tenancy](#7-database-schema--multi-tenancy)
8. [Idempotency & Concurrency Protection](#8-idempotency--concurrency-protection)
9. [Integration Testing & Sample Code](#9-integration-testing--sample-code)
   - [9.1 cURL Request Examples](#91-curl-request-examples)
   - [9.2 PowerShell Automated Test Script](#92-powershell-automated-test-script)
   - [9.3 Postman Collection Setup](#93-postman-collection-setup)
10. [Audit Logging & Production Checklist](#10-audit-logging--production-checklist)

---

## 1. System Architecture & Sequence Flow

The **EIMBox Educational Institution Management System** provides automated real-time student fee settlement and billing integration for **bKash Mobile Financial Services (Pay Bill Module)**.

### 1.1 Technical Interaction Flow

```
┌─────────────────┐             ┌─────────────────────┐             ┌─────────────────────────┐
│                 │             │                     │             │                         │
│   bKash App /   │             │    bKash Gateway    │             │   EIMBox Cloud Core     │
│   USSD (*247#)  │             │   (bKash Servers)   │             │  (console.eimbox.com)   │
│                 │             │                     │             │                         │
└────────┬────────┘             └──────────┬──────────┘             └────────────┬────────────┘
         │                                 │                                     │
         │ 1. Selects School Biller        │                                     │
         │    Enters Student ID (`ref_id`) │                                     │
         │────────────────────────────────▶│                                     │
         │                                 │ 2. CheckBill API Call               │
         │                                 │    (HTTP GET / POST)                │
         │                                 │────────────────────────────────────▶│
         │                                 │                                     │ 3. Fetch Student &
         │                                 │                                     │    Calculate Unpaid Dues
         │                                 │ 4. Return Dues & Student Details    │    from `stfinance`
         │                                 │◀────────────────────────────────────│
         │ 5. Displays Student Name,       │                                     │
         │    Class, Roll, & Payable Dues  │                                     │
         │◀────────────────────────────────│                                     │
         │                                 │                                     │
         │ 6. Enters PIN & Confirms Pay    │                                     │
         │────────────────────────────────▶│                                     │
         │                                 │ 7. BillPayment API Call             │
         │                                 │    (HTTP POST with trxid, amount)   │
         │                                 │────────────────────────────────────▶│
         │                                 │                                     │ 8. Idempotency Check
         │                                 │                                     │ 9. Settle `stfinance`
         │                                 │                                     │ 10. Generate PR No (`stpr`)
         │                                 │                                     │ 11. Log `bkash_transactions`
         │                                 │ 12. Return 200 Success Confirmation │
         │                                 │◀────────────────────────────────────│
         │ 13. Displays Success Voucher    │                                     │
         │     & Sends Confirmation SMS    │                                     │
         │◀────────────────────────────────│                                     │
         │                                 │                                     │
         │                                 │ [If Network Timeout / Reconcile]    │
         │                                 │ 14. RecheckPayment API Call (trxid) │
         │                                 │────────────────────────────────────▶│
         │                                 │                                     │ 15. Verify Transaction
         │                                 │ 16. Return Transaction Status       │     Status in Database
         │                                 │◀────────────────────────────────────│
```

---

## 2. Security, Whitelisting & Authentication

All API communications between bKash and EIMBox must be transmitted over secure **TLS 1.2+ (HTTPS)**.

### 2.1 Supported Authentication Methods

EIMBox supports both HTTP Header Authentication and Payload/Query Parameter credentials for full backward and forward compatibility with bKash gateway dispatchers:

#### A. HTTP Basic Authentication (RFC 7617)
```http
Authorization: Basic <base64_encode(username:password)>
Content-Type: application/json; charset=UTF-8
```

#### B. Direct Payload / Query Parameter Credentials
Credentials supplied inside the JSON payload or URL query parameters:
* `username`: Biller Partner User ID
* `password`: Biller Partner Password

### 2.2 Authentication Hierarchy

1. **Global Aggregator Model (Default):** Central bKash Biller partner credentials (`bKash00` / `bKash12321` or `demo3` / `aaa`).
2. **Multi-Tenant / Per-Institute Model:** Gateway dynamically matches credentials against the `payment_gateway_keys` table for institutions holding independent merchant agreements with bKash (`gateway_name = 'bKash'`, `biller_id`, `api_key`, `secret_key`, `sccode`).

---

## 3. Reference & Parameter Resolution Standard

bKash passes the student reference via `ref_id`. EIMBox parses and identifies the student and institution using the following priority order:

| Parameter | Data Type | Requirement | Description | Accepted Formats |
|---|---|---|---|---|
| `ref_id` | String (6-30) | **Mandatory** | Student Identifier (`stid`) or Combined Reference | • **10-digit Student ID:** `1031871631` (1st 6 digits resolve institute `sccode`, remaining digits resolve student)<br>• **Combined Delimited Reference:** `103187-1031871631` or `103187_1031871631` |
| `bill_month` | String (6) | Optional | Billing Month (`MMYYYY` or `na`) | e.g. `092026` (September 2026) or `na` (calculates cumulative dues till date). |
| `sccode` | Numeric (6) | Optional | 6-digit Institution EIIN | Explicit school code if not embedded within `ref_id`. |

---

## 4. API Endpoints Specification

### 4.1 Check Bill API (`/CheckBill`)

Validates student existence, queries real-time dues from `stfinance`, and returns the student name, academic details, payable amount, and due date.

* **Endpoint URL:** `https://console.eimbox.com/api/payment/bkash/v1/CheckBill` (or `CheckBill.php`)
* **HTTP Methods:** `GET`, `POST` (JSON / Form Data / URL Query Parameters)
* **bKash Doc Reference:** Check Bill Section

#### Request Fields:
| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
| `username` | String | Yes | Biller Username | `bKash00` |
| `password` | String | Yes | Biller Password | `bKash12321` |
| `ref_id` | String | Yes | Student Reference ID | `1031870001` or `103187-1031870001` |
| `bill_month` | String | No | Month (`MMYYYY` or `na`) | `na` |
| `amount` | String | No | Optional amount parameter | `1000` |

#### Sample Request 1: GET with URL Query Parameters
```http
GET https://console.eimbox.com/api/payment/bkash/v1/CheckBill?username=bKash00&password=bKash12321&ref_id=1031870001&bill_month=na HTTP/1.1
Host: console.eimbox.com
```

#### Sample Request 2: POST with JSON Payload
```http
POST https://console.eimbox.com/api/payment/bkash/v1/CheckBill HTTP/1.1
Host: console.eimbox.com
Content-Type: application/json; charset=UTF-8

{
  "username": "bKash00",
  "password": "bKash12321",
  "ref_id": "1031870001",
  "bill_month": "na"
}
```

#### Success Response (`200 OK`):
```json
{
  "ErrorCode": "200",
  "ErrorMsg": "Success",
  "Consumer_Name": "Afruja Akter",
  "Bill_month": "na",
  "Bill_amount": "1283",
  "Bill_due_date": "2026-09-30",
  "Trxid": "CHK20260922065637288",
  "Querytime": "20260922065637",
  "Amount_Breakdown": "Class: Six (Tagor) | Roll: 1 | SC: 103187"
}
```

#### Success Response when Zero Dues (`200 OK`):
```json
{
  "ErrorCode": "204",
  "ErrorMsg": "Already paid: Student has no outstanding dues",
  "Consumer_Name": "Afruja Akter",
  "Bill_month": "na",
  "Bill_amount": "0",
  "Bill_due_date": "2026-09-30",
  "Trxid": "CHK20260922065637300",
  "Querytime": "20260922065637",
  "Amount_Breakdown": "Class: Six (Tagor) | Roll: 1"
}
```

#### Error Response (Student Not Found):
```json
{
  "ErrorCode": "205",
  "ErrorMsg": "Data not found: Student record not found for given ID and Institution"
}
```

---

### 4.2 Bill Payment API (`/BillPayment`)

Dispatched by bKash immediately after the customer confirms payment. Settles student finance ledgers (`stfinance`), generates a receipt voucher (`stpr`) with an 8-digit receipt number (`prno`), and logs transaction audit trails.

* **Endpoint URL:** `https://console.eimbox.com/api/payment/bkash/v1/BillPayment` (or `BillPayment.php`)  
* **HTTP Methods:** `POST`, `GET`
* **bKash Doc Reference:** Bill Payment Section

#### Request Fields:
| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
| `username` | String | Yes | Biller Username | `bKash00` |
| `password` | String | Yes | Biller Password | `bKash12321` |
| `ref_id` | String | Yes | Student Reference ID | `1031870001` |
| `bill_month` | String | No | Month (`MMYYYY` or `na`) | `na` |
| `amount` | Numeric | Yes | Paid Amount in BDT | `1283` |
| `user_mobile_number` | String | No | Payer's bKash Mobile Number | `01711000000` |
| `trxid` | String (15-60) | Yes | bKash Unique Transaction ID | `BKASH_TXN_987654` |
| `paytime` | String | Yes | Timestamp (`YYYYMMDD` or `YYYYMMDDhhmmss`) | `20260922` |

#### Sample Request (POST JSON):
```http
POST https://console.eimbox.com/api/payment/bkash/v1/BillPayment HTTP/1.1
Host: console.eimbox.com
Content-Type: application/json; charset=UTF-8

{
  "username": "bKash00",
  "password": "bKash12321",
  "ref_id": "1031870001",
  "bill_month": "na",
  "amount": "1283",
  "user_mobile_number": "01711000000",
  "trxid": "BKASH_TXN_987654",
  "paytime": "20260922"
}
```

#### Success Response (`200 OK`):
```json
{
  "ErrorCode": "200",
  "ErrorMsg": "Success",
  "Consumer_Name": "Afruja Akter",
  "Total_amount": "1283",
  "Trxid": "BKASH_TXN_987654",
  "Paytime": "20260922",
  "Amount_Breakdown": "Class: Six | Roll: 1 | PR: 25000101"
}
```

#### Idempotency & Duplicate Protection Response:
```json
{
  "ErrorCode": "200",
  "ErrorMsg": "Success",
  "Consumer_Name": "Student #1031870001",
  "Total_amount": "1283",
  "Trxid": "BKASH_TXN_987654",
  "Paytime": "20260922",
  "Amount_Breakdown": "Already Processed | PR: 25000101"
}
```

---

### 4.3 Recheck Bill Payment API (`/RecheckPayment`)

Used by the bKash automated reconciliation system to verify transaction settlement status during network drops, timeouts, or batch audit reconciliations.

* **Endpoint URL:** `https://console.eimbox.com/api/payment/bkash/v1/RecheckPayment` (or `/bkash/RecheckPayment`)  
* **HTTP Methods:** `POST`, `GET`
* **bKash Doc Reference:** Recheck Bill Payment Section

#### Request Fields:
| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
| `username` | String | Yes | Biller Username | `bKash00` |
| `password` | String | Yes | Biller Password | `bKash12321` |
| `trxid` | String | Yes | bKash Transaction ID to verify | `BKASH_TXN_987654` |

#### Sample Request (POST JSON):
```http
POST https://console.eimbox.com/api/payment/bkash/v1/RecheckPayment HTTP/1.1
Host: console.eimbox.com
Content-Type: application/json; charset=UTF-8

{
  "username": "bKash00",
  "password": "bKash12321",
  "trxid": "BKASH_TXN_987654"
}
```

#### Sample Success Response (Transaction Found):
```json
{
  "ErrorCode": "200",
  "ErrorMsg": "Success",
  "PreviousMessage": "Payment Information Updated Successfully. PR: 25000101 | Amount: 1283 BDT"
}
```

#### Sample Error Response (Transaction Not Found):
```json
{
  "ErrorCode": "205",
  "ErrorMsg": "Data not found: Transaction not found in EIMBox records",
  "PreviousMessage": "Transaction not found"
}
```

---

## 5. Standard Error Codes & Handling

EIMBox strictly conforms to the official bKash Pay Bill Error Code Specification:

| Error Code | Error Message | Details & Trigger Conditions | Remediation |
|---|---|---|---|
| `200` | `Success` | Transaction or inquiry completed successfully. | No action required. |
| `201` | `Authentication failed` | Username or password invalid in Header / Payload. | Verify API credentials. |
| `202` | `Mandatory Field missing` | Required field (`ref_id`, `username`, `password`, `trxid`, `amount`) is missing. | Provide all mandatory parameters. |
| `203` | `Data Mismatch` | Requested parameters do not match institution records. | Verify student academic session. |
| `204` | `Already paid` | Student has 0 outstanding unpaid dues for the queried period. | No further payment needed. |
| `205` | `Data not found` | Student record or transaction ID not found in database. | Verify `ref_id` or `trxid`. |
| `206` | `Due date over` | Bill payment deadline has expired (if configured). | Contact institution admin. |
| `207` | `Minimum amount not paid` | Paid amount is less than the minimum acceptable fee. | Adjust payment amount. |
| `208` | `Pay amount and biller amount not match` | Amount paid does not match calculated total outstanding dues. | Query `/CheckBill` for exact dues. |
| `209` | `Internal error` | Database exception or unhandled server error. | Check server error logs. |
| `210` | `School not found` | Institution EIIN / School Code cannot be resolved. | Pass valid 6-digit EIIN. |

---

## 6. Financial Ledger Settlement & Receipt Numbering

### 6.1 Sequential Receipt Number (`prno`) Generation Standard

When a payment is processed via `/BillPayment`:
1. EIMBox queries the student's latest receipt in the active session from `stpr`.
2. If a previous receipt exists, it increments: `prno = last_prno + 1`.
3. If it is the student's first receipt of the academic year, an **8-digit number** is generated:
   - **Digits 1–2:** Last 2 digits of the academic session year (e.g., `25` for `2025` or `26` for `2026`).
   - **Digits 3–6:** Last 4 digits of the student ID (`stid`, e.g., `0001` from `1031870001`).
   - **Digits 7–8:** Sequence counter starting with `01`.
   - *Example:* For student `1031870001` in year `2025`, the first receipt is `25000101`.

### 6.2 Ledger Adjustments in Database

1. **`stfinance` (Fee Items Ledger):**
   - Dues are adjusted chronologically by `month ASC, id ASC`.
   - `paid = paid + item_amount`
   - `dues = dues - item_amount`
   - PR slot allocated: `pr1` (or `pr2` if `pr1` already filled).
   - `pr1no = [new_prno]`, `pr1date = [current_date]`, `pr1by = 'bKash'`.
2. **`stpr` (Receipts Ledger):**
   - Inserts receipt row with `entryby = 'bKash'`, `amount = [amount]`, `statusvalue = 'TxnID: [trxid] | bKash PayBill'`.
3. **`sessioninfo` (Student Profile):**
   - Updates `lastpr = [new_prno]`.

---

## 7. Database Schema & Multi-Tenancy

### 7.1 `bkash_transactions` Table
Stores all bKash transactions for audit, verification, and idempotency:

```sql
CREATE TABLE IF NOT EXISTS `bkash_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL,
    `stid` INT NOT NULL,
    `sessionyear` VARCHAR(20) NOT NULL,
    `prno` BIGINT NULL DEFAULT NULL,
    `trxid` VARCHAR(60) NOT NULL UNIQUE,
    `paytime` VARCHAR(40) NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `ref_id` VARCHAR(50) NULL,
    `bill_month` VARCHAR(20) NULL,
    `user_mobile` VARCHAR(30) NULL,
    `status` VARCHAR(20) DEFAULT 'Success',
    `error_code` VARCHAR(20) DEFAULT '200',
    `error_msg` VARCHAR(100) DEFAULT 'Success',
    `raw_request` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_trx` (`trxid`),
    INDEX `idx_prno` (`prno`),
    INDEX `idx_student` (`sccode`, `stid`, `sessionyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7.2 `payment_gateway_keys` Table (Multi-Tenant Configuration)
Allows individual institutions to configure their own independent bKash Biller credentials:

```sql
CREATE TABLE IF NOT EXISTS `payment_gateway_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL,
    `gateway_name` VARCHAR(50) NOT NULL DEFAULT 'bKash',
    `biller_id` VARCHAR(50) NOT NULL,
    `api_key` VARCHAR(100) NOT NULL,
    `secret_key` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sccode` (`sccode`),
    INDEX `idx_gateway` (`gateway_name`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 8. Idempotency & Concurrency Protection

* **Database Level Constraint:** The `trxid` column in `bkash_transactions` is defined as `UNIQUE`.
* **Application Level Check:** When a `/BillPayment` request arrives, EIMBox queries `bkash_transactions` before opening a transaction:
  - If `trxid` already exists, the API immediately returns `ErrorCode: "200"` with the previously issued receipt number and details.
  - No duplicate ledger deductions or multiple receipts can occur under any circumstance.
* **ACID Transactions:** Dues deduction, `stpr` insertion, and transaction logging are executed inside an atomic database transaction (`$conn->begin_transaction()`). If any step fails, the entire transaction is rolled back.

---

## 9. Integration Testing & Sample Code

### 9.1 cURL Request Examples

#### A. Check Bill:
```bash
curl -i -X POST "https://console.eimbox.com/api/payment/bkash/v1/CheckBill" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "bKash00",
    "password": "bKash12321",
    "ref_id": "1031870001",
    "bill_month": "na"
  }'
```

#### B. Bill Payment:
```bash
curl -i -X POST "https://console.eimbox.com/api/payment/bkash/v1/BillPayment" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "bKash00",
    "password": "bKash12321",
    "ref_id": "1031870001",
    "bill_month": "na",
    "amount": "1283",
    "user_mobile_number": "01711000000",
    "trxid": "BKASH_TXN_987654",
    "paytime": "20260922"
  }'
```

#### C. Recheck Bill Payment:
```bash
curl -i -X POST "https://console.eimbox.com/api/payment/bkash/v1/RecheckPayment" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "bKash00",
    "password": "bKash12321",
    "trxid": "BKASH_TXN_987654"
  }'
```

---

### 9.2 PowerShell Automated Test Script

```powershell
$baseUrl = "http://localhost/eimbox-dashboard/eimbox-materio/api/payment/bkash/v1"
$headers = @{ "Content-Type" = "application/json" }

# Step 1: Check Bill
Write-Host "--- 1. Querying Student Dues (CheckBill) ---" -ForegroundColor Cyan
$chkBody = @{
    username = "bKash00"
    password = "bKash12321"
    ref_id = "1031870001"
    bill_month = "na"
} | ConvertTo-Json

$chkRes = Invoke-RestMethod -Uri "$baseUrl/CheckBill.php" -Method Post -Headers $headers -Body $chkBody
$chkRes | ConvertTo-Json -Depth 5

$payableAmount = $chkRes.Bill_amount

# Step 2: Execute Bill Payment
Write-Host "--- 2. Executing Payment (BillPayment) ---" -ForegroundColor Cyan
$payTxnId = "BKASH_" + (Get-Random -Minimum 100000 -Maximum 999999)
$payBody = @{
    username = "bKash00"
    password = "bKash12321"
    ref_id = "1031870001"
    bill_month = "na"
    amount = $payableAmount
    user_mobile_number = "01711000000"
    trxid = $payTxnId
    paytime = (Get-Date).ToString("yyyyMMdd")
} | ConvertTo-Json

$payRes = Invoke-RestMethod -Uri "$baseUrl/BillPayment.php" -Method Post -Headers $headers -Body $payBody
$payRes | ConvertTo-Json -Depth 5

# Step 3: Recheck Payment Status
Write-Host "--- 3. Verifying Settlement (RecheckPayment) ---" -ForegroundColor Cyan
$recheckBody = @{
    username = "bKash00"
    password = "bKash12321"
    trxid = $payTxnId
} | ConvertTo-Json

$recheckRes = Invoke-RestMethod -Uri "$baseUrl/RecheckPayment.php" -Method Post -Headers $headers -Body $recheckBody
$recheckRes | ConvertTo-Json -Depth 5
```

---

### 9.3 Postman Collection Setup

Download or import [`postman_collection.json`](file:///d:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/api/payment/bkash/v1/postman_collection.json) directly into Postman to run pre-configured test cases.

---

## 10. Audit Logging & Production Checklist

### 10.1 Daily Activity Log File
All incoming requests and outgoing responses are recorded in:
```
/api/payment/bkash/v1/logs/bkash-YYYY-MM-DD.log
```
*Format:*
```
[2026-09-22 06:56:37] [CHECK_BILL_REQUEST] REQ: {"username":"bKash00",...} | RES: null
[2026-09-22 06:56:37] [RESPONSE] REQ: "send_bkash_response" | RES: {"ErrorCode":"200","ErrorMsg":"Success",...}
```

### 10.2 Production Launch Checklist

- [x] SSL / TLS 1.2+ certificate active on `https://console.eimbox.com`.
- [x] `bkash_transactions` table indexed on `trxid` and `(sccode, stid, sessionyear)`.
- [x] Idempotency tested against retries.
- [x] Daily log directory permissions set to `0777` with automated rotation.
- [x] MySQL connection timezone set to `+06:00` (Asia/Dhaka).
- [x] Multi-tenant gateway credentials verified in `payment_gateway_keys`.

---

For technical assistance or sandbox whitelisting, contact the **EIMBox Engineering Team** at `support@eimbox.com`.
