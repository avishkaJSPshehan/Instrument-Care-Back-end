<?php
require "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = 'Avishka Shehan';
$recipientEmail = 'jspshehan@gmail.com';
$subject = 'Instrument Care - You Have a New Service Request';
$clientName = $name; // Dynamic client/technician name
$logoPath = "../../Assets/Email Header III.png"; // Absolute/relative path
$footerPath = "../../Assets/Email Footer IV.png";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'avishka.test.ii@gmail.com'; // Your Gmail
    $mail->Password   = 'xsnefikqmjrqtfck';         // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('avishka.test.ii@gmail.com', 'Instrument Care');
    $mail->addAddress($recipientEmail, $name);

    // Embed header & footer images
    $mail->addEmbeddedImage($logoPath, 'logo_cid');
    $mail->addEmbeddedImage($footerPath, 'footer_cid');

    // Elegant HTML Email Body
    $mailBody = "
    <html>
    <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;'>

      <!-- Header -->
      <div style='text-align:center;'>
        <img src='cid:logo_cid' alt='Instrument Care' style='width:100%; max-height:250px; object-fit:cover;'>
      </div>

      <!-- Body -->
      <div style='background:#ffffff; margin:20px auto; max-width:auto; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:hidden;'>
        <div style='padding:25px; color:#000000;'>
          <h2 style='color:#ff6600; margin-top:0;'>🔧 New Service Request Assigned</h2>
          <p>Dear <strong>{$clientName}</strong>,</p>
          <p>You have been assigned a new service request. Please find the details below:</p>

          <div style='background:#f4f4f4; padding:15px; border-radius:6px; margin:20px 0;'>
            <p><strong>Request ID:</strong> SR-2025-001</p>
            <p><strong>Customer:</strong> John Doe</p>
            <p><strong>Service Type:</strong> AC Repair</p>
            <p><strong>Scheduled Date:</strong> 25 Sept 2025</p>
            <p><strong>Location:</strong> Colombo, Sri Lanka</p>
          </div>

          <p>Kindly review the request and proceed with the necessary actions.</p>
          <a href='https://yourwebsite.com/login' style='display:inline-block; padding:12px 20px; background:#ff6600; color:#ffffff; text-decoration:none; font-weight:bold; border-radius:6px;'>View in System</a>
        </div>
      </div>

      <!-- Footer -->
      <div style='text-align:center; margin-top:10px;'>
        <img src='cid:footer_cid' alt='Instrument Care' style='width:100%; max-height:300px; object-fit:cover;'>
      </div>
      <div style='text-align:center; font-size:12px; color:#888888; padding:10px;'>
        © " . date('Y') . " Instrument Care | This is an automated email. Please do not reply.
      </div>

    </body>
    </html>
    ";

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $mailBody;
    $mail->AltBody = "You have a new service request from {$clientName}. Visit your Instrument Care account to view details.";

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
