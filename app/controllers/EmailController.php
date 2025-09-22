<?php
require "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = 'Avishka Shehan';
$recipientEmail = 'jspshehan@gmail.com';
$subject = 'Email Testing in Instrument Care';
$clientName = $name; // Dynamic client name
$logoPath = "../../Assets/national-logo.png"; // Use absolute path

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

    // Embed logo
    $mail->addEmbeddedImage($logoPath, 'logo_cid');

    // HTML email body
    $mailBody = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>

        <!-- Header -->
        <div style='background-color: #f4f4f4; padding: 20px; text-align: center;'>
            <img src='cid:logo_cid' alt='Instrument Care' style='height: 60px;'><br>
            <h2>Instrument Care</h2>
        </div>

        <!-- Body -->
        <div style='padding: 20px;'>
            <p>You have a new service request from <strong>{$clientName}</strong>.</p>
            <p>View full details by logging in to your <a href='https://yourwebsite.com/login'>Instrument Care account</a>.</p>
        </div>

        <!-- Footer -->
        <div style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #555;'>
            <p>Instrument Care Support<br>
            Phone: +94 123 456 789<br>
            Email: support@instrumentcare.com</p>
            <p>123, Main Street, Colombo, Sri Lanka</p>
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
