<!-- HTML EMAIL: replace %OTP% with actual 6-digit code before sending -->
<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <style>
        table,
        td {
            border: 0;
        }

        .num {
            height: 45px;
            width: 40px;
            text-align: center;
            border: 1px solid indigo;
            border-radius: 0px;
            vertical-align: middle;
            font-size: 22px;
            font-weight: 600;
            background: #981fb6ff;
            color: white;
        }

        .gap {
            width: 10px;
            border-top: none;
            ;
        }
    </style>

</head>

<body style="margin:0;padding:0;background-color:#f6f8fa;font-family:Arial,Helvetica,sans-serif;">

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
        style="background-color:#f6f8fa;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600"
                    style="background:#ffffff;border-radius:8px;padding:24px;box-shadow:0 2px 4px rgba(0,0,0,0.08);">
                    <tr>
                        <td>

                            <table>
                                <tr>
                                    <td>
                                        <img src="https://dashboard.eimbox.com/assets/imgs/logo.png"
                                            style="height:35px; padding-right:10px;" />
                                    </td>
                                    <td>
                                        <span style="color:darkviolet;"><b>EIMBox</b></span><br>
                                        <small>Eduational Institute Management System</small>
                                    </td>
                                </tr>
                            </table>



                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:16px;">
                            <p>Dear <b>%NAME%</b>,</p>
                            <h2 style="margin:0 0 8px 0;font-size:20px;color:#111;">MFA/OTP কোড</h2>
                            <p style="margin:0;color:#555;font-size:14px;line-height:1.4;">
                                নিচের ৬-ডিজিট কোটটি ব্যবহার করে আপনার লগইন/ভেরিফিকেশন সম্পন্ন করুন। কোডটি <strong>5
                                    মিনিট</strong> পর নিস্ক্রিয় হয়ে যাবে।
                            </p>
                        </td>
                    </tr>

                    <!-- OTP BOXES -->
                    <tr>
                        <td align="center" style="padding:15px 0;">
                            <!-- Use a table for best email-client rendering -->
                            <table role="presentation" cellpadding="0" cellspacing="6"
                                style="border-collapse:collapse;">
                                <tr>
                                    <!-- We'll output one <td> per digit -->
                                    <!-- Replace each %D1% ... %D6% with the corresponding digit from the OTP -->
                                    <td class="num">
                                        %D1%
                                    </td>
                                    <td class="gap"></td>
                                    <td class="num">
                                        %D2%
                                    </td>
                                    <td class="gap"></td>
                                    <td class="num"> %D3%
                                    </td>
                                    <td class="gap"></td>
                                    <td class="num"> %D4%
                                    </td>
                                    <td class="gap"></td>
                                    <td class="num"> %D5%
                                    </td>
                                    <td class="gap"></td>
                                    <td class="num"> %D6%
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Fallback text + support -->
                    <tr>
                        <td style="padding:12px 0;color:#666;font-size:13px;line-height:1.4;">
                            আপনার ওটিপি কোডটি: <strong style="color:darkviolet;">%OTP%</strong><br />
                            আপনি যদি এই অনুরোধ না করে থাকেন, অনুগ্রহ করে আমাদের সাপোর্ট টিমকে জানান।
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top:18px;border-top:1px solid #eef0f2;color:#999;font-size:12px;">
                            © <span id="year">2025</span> EIMBox
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>



</body>

</html>