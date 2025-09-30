<?php
$otp = '824395';
$to_name = "Brindabon Das";





$otp = str_pad(preg_replace('/\D/', '', $otp), 6, '0', STR_PAD_LEFT);
$digits = str_split($otp);
$htmlTemplate = file_get_contents('mailer/mail-templete-otp.php'); // অথবা সরাসরি স্ট্রিং হিসেবে.
$html = str_replace('%OTP%', htmlspecialchars($otp, ENT_QUOTES, 'UTF-8'), $htmlTemplate);
for ($i = 0; $i < 6; $i++) {
  $html = str_replace('%D' . ($i + 1) . '%', htmlspecialchars($digits[$i], ENT_QUOTES, 'UTF-8'), $html);
}
  $html = str_replace('%NAME%', htmlspecialchars($to_name, ENT_QUOTES, 'UTF-8'), $html);


echo $html;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Send Mail with PHPMailer (AJAX)</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body style="font-family: Arial;">

  <h2>Send Email</h2>

  <form id="mailForm" enctype="multipart/form-data">
    <label>Recipient Email:</label><br>
    <input type="email" name="to_mail" required value="engrreaz@gmail.com"><br><br>

    <label>Recipient Name:</label><br>
    <input type="text" name="to_name" required value="Engr. Reazul Hoque"><br><br>

    <label>Subject:</label><br>
    <input type="text" name="mail_subject" required value="Test Email with PHPMailer"><br><br>

    <label>HTML Body:</label><br>
    <textarea name="mail_body" rows="5" cols="50"><?php echo $html; ?></textarea><br><br>

    <label>Plain Text Body:</label><br>
    <input type="text" name="mail_alt" value="This is the plain text version of the email"><br><br>

    <label>Attachment:</label><br>
    <input type="text" name="mail_doc" value="../docs/doc.txt"><br><br>

    <label>Success:</label><br>
    <input type="text" name="mail_success" value="OPT has been sent to your mail also"><br><br>

    <button type="submit">Send Mail</button>
  </form>

  <div id="result" style="margin-top:20px; font-weight:bold;"></div>

  <script>
    $(document).ready(function () {
      $("#mailForm").on("submit", function (e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
          url: "mailer/php-mailer.php",
          type: "POST",
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            $("#result").html(response);
          },
          error: function () {
            $("#result").html("Error sending mail (AJAX failed)");
          }
        });
      });
    });
  </script>


  <?php
  // echo $html;
  ?>
</body>

</html>