<!-- HTML EMAIL: replace %RESET_LINK% with actual reset URL before sending -->
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

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: darkviolet;
            color: #fff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }

        .btn:hover {
            background-color: indigo;
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
                                        <small>Educational Institute Management System</small>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:16px;">
                            <p>Dear <b>%NAME%</b>,</p>
                            <h2 style="margin:0 0 8px 0;font-size:20px;color:#111;">Forgot Your Password?</h2>
                            <p style="margin:0;color:#555;font-size:14px;line-height:1.4;">
                                আমরা আপনার জন্য একটি পাসওয়ার্ড রিসেট লিঙ্ক তৈরি করেছি। নিচের বাটনে ক্লিক করে আপনার
                                নতুন পাসওয়ার্ড সেট করুন। এই লিঙ্কটি <strong>৫ মিনিট</strong> পর মেয়াদোত্তীর্ণ হবে।
                            </p>
                        </td>
                    </tr>

                    <!-- RESET PASSWORD BUTTON -->
                    <tr>
                        <td align="center" style="padding:20px 0;">
                            <a href="%RESET_LINK%" class="btn" target="_blank">Reset Password</a>
                        </td>
                    </tr>

                    <!-- Fallback text + support -->
                    <tr>
                        <td style="padding:12px 0;color:#666;font-size:13px;line-height:1.4;">
                            যদি বাটনে ক্লিক করতে সমস্যা হয়, নিচের লিঙ্কটি ব্রাউজারে কপি-পেস্ট করুন:<br />
                            <a href="%RESET_LINK%" style="color:darkviolet;">%RESET_LINK%</a><br />
                            আপনি যদি এই অনুরোধটি না করে থাকেন, দয়া করে আমাদের সাপোর্ট টিমকে জানান।
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
