<?php
namespace App\Controllers;

require_once __DIR__ . "/../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a service request email with dynamic details
 *
 * @param string $recipientEmail Recipient email address
 * @param string $clientName Recipient name
 * @param string $requestId Service Request ID
 * @param string $customer Customer name
 * @param string $serviceType Type of service
 * @param string $scheduledDate Scheduled date
 * @param string $location Location
 */
function sendServiceRequestEmail($recipientEmail, $clientName, $requestId, $customer, $serviceType, $scheduledDate, $location) {
    $subject = 'Instrument Care - You Have a New Service Request';
    $logoPath = "C:/xampp/htdocs/Instrument-Care-Back-end/Assets/Email Header III.png"; // Header image path

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'avishka.test.ii@gmail.com';
        $mail->Password   = 'xsnefikqmjrqtfck';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('avishka.test.ii@gmail.com', 'Instrument Care');
        $mail->addAddress($recipientEmail, $clientName);

        // Embed header image
        $mail->addEmbeddedImage($logoPath, 'logo_cid');

        // HTML Email Body
        $mailBody = "
        <html>
        <body style='margin:0; padding:0; font-family:Segoe UI, Roboto, Arial, sans-serif; background-color:#ffffff; color:#333333;'>

            <!-- Header -->
            <div style='margin:0; padding:0;'>
            <img src='cid:logo_cid' alt='Instrument Care' style='display:block; width:100%; height:auto; object-fit:cover;'>
            </div>

            <!-- Body -->
            <div style='padding:40px 30px;'>
            <h1 style='color:#ff6600; margin-top:0; margin-bottom:20px; font-size:24px; font-weight:600;'>
                New Service Request Assigned
            </h1>

            <p style='font-size:15px; margin:0 0 12px;'>
                Dear <strong style='font-weight:600;'>{$clientName}</strong>,
            </p>

            <p style='font-size:15px; margin:0 0 20px;'>
                You have been assigned a <strong style='color:#000;'>new service request</strong>. Please find the details below:
            </p>

            <!-- Details Section -->
            <div style='background:#f9f9f9; padding:18px 20px; border-left:4px solid #ff6600; margin:20px 0; font-size:14px;'>
                <p style='margin:8px 0;'><strong>Request ID:</strong> {$requestId}</p>
                <p style='margin:8px 0;'><strong>Customer:</strong> {$customer}</p>
                <p style='margin:8px 0;'><strong>Service Type:</strong> <strong style='color:#000;'>{$serviceType}</strong></p>
                <p style='margin:8px 0;'><strong>Scheduled Date:</strong> {$scheduledDate}</p>
                <p style='margin:8px 0;'><strong>Location:</strong> {$location}</p>
            </div>

            <p style='font-size:15px; margin:0 0 25px;'>
                Kindly review the request and <strong>proceed with the necessary actions</strong>.
            </p>

            <!-- CTA Button -->
            <a href='https://yourwebsite.com/login' style='display:inline-block; padding:14px 28px; background:#ff6600; color:#ffffff; text-decoration:none; font-weight:600; border-radius:6px; font-size:15px;'>
                View in System
            </a>
            </div>

            <!-- Footer -->
            <div style='background:linear-gradient(135deg, #f4f4f4 0%, #e9e9e9 100%); padding:30px 20px; text-align:center; font-family:Segoe UI, Roboto, Arial, sans-serif; font-size:13px; color:#555555;'>

            <h2 style='margin:0; font-weight:700; font-size:15px; color:#333333;'>
                Instrument Care
            </h2>

            <p style='margin:6px 0 18px; font-size:18px; color:#777777;'>
                Reliable Service • Quality Care • Customer First
            </p>

            <!-- Links -->
            <p style='margin:0 0 14px;'>
                <a href='https://yourwebsite.com/privacy' style='color:#ff6600; text-decoration:none; margin:0 10px;'>Privacy Policy</a> | 
                <a href='https://yourwebsite.com/terms' style='color:#ff6600; text-decoration:none; margin:0 10px;'>Terms of Service</a> | 
                <a href='https://yourwebsite.com/contact' style='color:#ff6600; text-decoration:none; margin:0 10px;'>Contact Us</a>
            </p>

            <!-- Social (text fallback for email) -->
            <p style='margin:0; font-size:12px;'>
                Follow us:
                <a href='https://facebook.com' style='color:#555555; text-decoration:none; margin:0 6px;'>Facebook</a> ·
                <a href='https://twitter.com' style='color:#555555; text-decoration:none; margin:0 6px;'>Twitter</a> ·
                <a href='https://linkedin.com' style='color:#555555; text-decoration:none; margin:0 6px;'>LinkedIn</a>
            </p>

            <!-- Copyright -->
            <p style='margin:20px 0 0; font-size:11px; color:#999999;'>
                © " . date('Y') . " Instrument Care — This is an automated email. Please do not reply.
            </p>
            </div>

        </body>
        </html>
        ";

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $mailBody;
        $mail->AltBody = "You have a new service request from {$clientName}. Request ID: {$requestId}, Service: {$serviceType}, Date: {$scheduledDate}, Location: {$location}.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Example usage
// sendServiceRequestEmail(
//     'jspshehan@gmail.com',
//     'Avishka Shehan',
//     'SR-2025-001',
//     'John Doe',
//     'AC Repair',
//     '25 Sept 2025',
//     'Colombo, Sri Lanka'
// );
