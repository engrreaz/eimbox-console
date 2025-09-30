<?php
 $mail->Body = '
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width">
        </head>
        <body style="font-family: Arial, Helvetica, sans-serif; margin:0; padding:20px; background:#f6f6f6;">
          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
              <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border-radius:6px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                  <tr>
                    <td style="padding:20px; text-align:left;">
                      <h2 style="margin:0 0 10px 0; font-size:20px; color:#333333;">স্বাগতম!</h2>
                      <p style="margin:0 0 16px 0; color:#555555; line-height:1.5;">
                        এই ইমেইলটি একটি সিম্পল টেবিল ও একটি লিংক দেখানোর উদাহরণ।
                      </p>

                      <!-- Table -->
                      <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse; border-color:#e1e1e1; margin-bottom:16px;">
                        <thead>
                          <tr style="background:#f2f2f2;">
                            <th align="left" style="font-weight:600; font-size:14px;">Name</th>
                            <th align="left" style="font-weight:600; font-size:14px;">Email</th>
                            <th align="left" style="font-weight:600; font-size:14px;">Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td style="font-size:14px; color:#333333;">রিয়াজ</td>
                            <td style="font-size:14px; color:#333333;">reaz@example.com</td>
                            <td style="font-size:14px; color:#333333;">Active</td>
                          </tr>
                          <tr>
                            <td style="font-size:14px; color:#333333;"> মিম </td>
                            <td style="font-size:14px; color:#333333;">mim@example.com</td>
                            <td style="font-size:14px; color:#333333;">Pending</td>
                          </tr>
                        </tbody>
                      </table>

                      <!-- Link / CTA -->
                      <p style="margin:0 0 8px 0;">
                        আরও বিস্তারিত জানতে নিচের লিংকে যান:
                      </p>
                      <p style="margin:0;">
                        <a href="https://example.com" style="display:inline-block; padding:10px 16px; background:#1a73e8; color:#ffffff; text-decoration:none; border-radius:4px; font-weight:600;">
                          Visit Example.com
                        </a>
                      </p>

                      <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">

                      <p style="font-size:12px; color:#888888; margin:0;">
                        যদি আপনি এই মেইলটি পেতে না চান, আমাদের জানান।
                      </p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </body>
      </html>
      ';