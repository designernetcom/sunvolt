<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===============================
   1. reCAPTCHA v3 Verification
================================ */
$secretKey = "6Lc201csAAAAAKJFiaXL1C6fB0cwYLF0M610zoEX"; // SECRET KEY (server-side)

if (empty($_POST['recaptcha_token'])) {
    echo "reCAPTCHA token missing";
    exit;
}

$token = $_POST['recaptcha_token'];

$verifyURL = "https://www.google.com/recaptcha/api/siteverify";
$verifyResponse = file_get_contents($verifyURL . "?secret=" . $secretKey . "&response=" . $token);
$responseData = json_decode($verifyResponse);

if (!$responseData->success || $responseData->score < 0.5) {
    echo "reCAPTCHA verification failed";
    exit;
}

/* ===============================
   2. Form Validation
================================ */
$error = "";

// Name
if (empty($_POST['fname'])) {
    $error .= "Name is required\n";
} elseif (!preg_match("/^[a-zA-Z\s]+$/", $_POST['fname'])) {
    $error .= "Enter valid First Name\n";
}
if (empty($_POST['lname'])) {
    $error .= "Name is required\n";
} elseif (!preg_match("/^[a-zA-Z\s]+$/", $_POST['lname'])) {
    $error .= "Enter valid Last Name\n";
}
// Email
if (empty($_POST['email'])) {
    $error .= "Email is required\n";
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $error .= "Enter valid Email\n";
}

if ($phone === '' || !preg_match("/^[0-9+\-\s]+$/", $phone)) {
    echo "Enter valid Phone number";
    exit;
}

// Message
if (empty($_POST['message'])) {
    $error .= "Message is required\n";
}

// Stop if errors exist
if (!empty($error)) {
    echo trim($error);
    exit;
}

/* ===============================
   3. Send Email using PHPMailer
================================ */
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

try {
    $mail = new PHPMailer(true);

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "designernetcom@gmail.com";
    $mail->Password = "zorekeuwtjyuqhdg"; // App Password
    $mail->SMTPSecure = "ssl";
    $mail->Port = 465;

    // Email Content
    $mail->setFrom("sunvolt24@gmail.com", "Sunvolt Enterprises");
    $mail->addAddress("sunvolt24@gmail.com");

    $mail->isHTML(true);
    $mail->Subject = 'Sunvolt Enterprises';

    $message_body = "
    <html>
    <body>
        <table border='1' cellpadding='10' cellspacing='0'>
            <tr>
                <td colspan='2' style='color:#C50B33;font-size:18px;'>
                    <strong>Contact Form Enquiry</strong>
                </td>
            </tr>
            <tr>
                <td>First Name</td>
                <td>" . htmlspecialchars($_POST['fname']) . "</td>
            </tr>
             <tr>
                <td>Last Name</td>
                <td>" . htmlspecialchars($_POST['lname']) . "</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>" . htmlspecialchars($_POST['email']) . "</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>" . htmlspecialchars($_POST['phone']) . "</td>
            </tr>
          
            <tr>
                <td>Message</td>
                <td>" . nl2br(htmlspecialchars($_POST['message'])) . "</td>
            </tr>
        </table>
    </body>
    </html>";

    $mail->Body = $message_body;

    if ($mail->send()) {

        // Auto-reply to user
        $mail->clearAddresses();
        $mail->addAddress($_POST['email']);
        $mail->Subject = 'Sunvolt Enterprises';
        $mail->Body = "Thank you for contacting us. We will get back to you shortly.";

        $mail->send();

        echo "sent";
    } else {
        echo "Mail sending failed";
    }

} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>