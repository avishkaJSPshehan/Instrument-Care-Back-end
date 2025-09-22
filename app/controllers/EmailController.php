<?php
require "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = 'Avishka Shehan';
$recipientEmail = 'jspshehan@gmail.com';
$subject = 'Email Testing in Instrument Care';
$message = 'This is a Test Message';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'avishka.test.ii@gmail.com';     // Your Gmail account
    $mail->Password   = 'xsnefikqmjrqtfck';  // Use Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('avishka.test.ii@gmail.com', 'Instrument Care'); // Must match Gmail account
    $mail->addAddress($recipientEmail, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
