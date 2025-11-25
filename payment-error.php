<?php 

        $errorMessages = [
            2001 => "Invalid App Key",
            2002 => "Invalid Payment ID",
            2003 => "Process failed",
            2004 => "Invalid firstPaymentDate",
            2005 => "Invalid frequency",
            2006 => "Invalid amount",
            2007 => "Invalid currency",
            2008 => "Invalid intent",
            2009 => "Invalid Wallet",
            2010 => "Invalid OTP",
            2011 => "Invalid PIN",
            2012 => "Invalid Receiver MSISDN",
            2013 => "Resend Limit Exceeded",
            2014 => "Wrong PIN",
            2015 => "Wrong PIN count exceeded",
            2016 => "Wrong verification code",
            2017 => "Wrong verification limit exceeded",
            2018 => "OTP verification time expired",
            2019 => "PIN verification time expired",
            2020 => "Exception Occurred",
            2021 => "Invalid Mandate ID",
            2022 => "The mandate does not exist",
            2023 => "Insufficient Balance",
            2024 => "Exception occurred",
            2025 => "Invalid request body",
            2026 => "The reversal amount cannot be greater than the original transaction amount",
            2027 => "Mandate already exists for this payer reference number",
            2028 => "Reverse failed because the transaction serial number does not exist",
            2029 => "Duplicate for all transactions",
            2030 => "Invalid mandate request type",
            2031 => "Invalid merchant invoice number",
            2032 => "Invalid transfer type",
            2033 => "Transaction not found",
            2034 => "Original transaction already reversed",
            2035 => "Initiator has no permission to reverse this transaction",
            2036 => "Mandate not in Active state",
            2037 => "Debit party account prohibits execution",
            2038 => "Debit party identity prohibits execution",
            2039 => "Credit party account prohibits execution",
            2040 => "Credit party identity prohibits execution",
            2041 => "Credit party identity does not support current service",
            2042 => "Initiator has no permission to reverse this transaction",
            2043 => "Incorrect security credential",
            2044 => "Identity not subscribed or inactive",
            2045 => "Customer MSISDN does not exist",
            2046 => "Identity not subscribed to requested service",
            2047 => "TLV Data Format Error",
            2048 => "Invalid Payer Reference",
            2049 => "Invalid Merchant Callback URL",
            2050 => "Agreement already exists",
            2051 => "Invalid Agreement ID",
            2052 => "Agreement is incomplete",
            2053 => "Agreement already cancelled",
            2054 => "Prerequisite not met for agreement execution",
            2055 => "Invalid Agreement State",
            2056 => "Invalid Payment State",
            2057 => "Not a bKash Account",
            2058 => "Not a Customer Wallet",
            2059 => "Multiple OTP requests denied",
            2060 => "Prerequisite not met for payment execution",
            2061 => "Only initiator can perform this action",
            2062 => "Payment already completed",
            2063 => "Invalid mode",
            2064 => "Product mode unavailable",
            2065 => "Mandatory field missing",
            2066 => "Agreement not shared with merchant",
            2067 => "Invalid permission",
            2068 => "Transaction already completed",
            2069 => "Transaction already cancelled",
            2116 => "Agreement execution already completed",
            2117 => "Payment execution already completed",
            2118 => "Invalid Platform value",
            2119 => "Authorized payment already processed",
            503 => "System is undergoing maintenance. Try again later"
        ];

        $code = $statusCode ?? 0; // API response status code

        if (array_key_exists($code, $errorMessages)) {
            $error = $errorMessages[$code];
        } else {
            $error = "Unknown Error (Code: $code)";
        }

        ?>


        <div class="payment-wrapper">
            <div class="card payment-card p-3">

                <div class="row">
                    <div class="col-md-4 d-flex h-100 justify-content-center align-items-center">
                        <?php
                        echo '<div id="showhide" style="display:none;">';
                        echo '<pre>';
                        print_r($obj);
                        echo '</pre>' . '</div>';

                        $image = 'error.png';
                        if ($statusCode == 2056) {
                            $image = 'cancel.png';
                        } else if ($statusCode == 2029) {
                            $image = 'duplicate.png';
                        } else if ($statusCode == 2062) {
                            $image = 'cancel.png';
                        }

                        echo '<img class="status-img img-fluid" src="assets/images/pgw/' . $image . '" onclick="showhide();"/>';
                        ?>
                    </div>

                    <div class="col-md-8 h-100">
                        <div class="card-header pb-0 text-center text-dark fw-bold">
                            Transaction Info
                        </div>
                        <hr class="pb-0 mb-0">

                        <div class="card-body">
                            <div class="alert alert-danger text-center fs-5 fw-bold"><?= $error ?></div>



                            <div class="text-center">
                                <button class="btn btn-sm btn-dark mt-3 px-5"
                                    onclick="window.location.href='student-payable.php';">
                                    Go Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>