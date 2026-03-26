<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/php_error.log');

header('Content-Type: application/json');

require_once __DIR__ . '/libs/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/phpmailer/src/SMTP.php';
require_once __DIR__ . '/libs/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* =======================
   VALIDATE INPUT
======================= */
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$formId = (int) $_POST['id'];

/* =======================
   DATABASE
======================= */
$conn = new mysqli("localhost", "root", "", "ticket");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

/* =======================
   FETCH RECORD
======================= */
$stmt = $conn->prepare("
    SELECT first_name, last_name, email, print_token
    FROM form6_applicationforleave
    WHERE id = ?
");
$stmt->bind_param("i", $formId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}

$data = $result->fetch_assoc();

/* =======================
   ENSURE TOKEN EXISTS
======================= */
if (empty($data['print_token'])) {
    $token = bin2hex(random_bytes(32));

    $update = $conn->prepare("
        UPDATE form6_applicationforleave
        SET print_token = ?
        WHERE id = ?
    ");
    $update->bind_param("si", $token, $formId);
    $update->execute();
} else {
    $token = $data['print_token'];
}

/* =======================
   BUILD PRINT LINK
======================= */
$printLink = "http://localhost/Ticketing/form6/print.php?token=" . $token;

/* =======================
   SEND EMAIL
======================= */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $_SERVER['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_SERVER['SMTP_USER'];
    $mail->Password   = $_SERVER['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_SERVER['SMTP_PORT'];

    $mail->setFrom($_SERVER['SMTP_USER'], 'SDO Tayabas');
    $mail->addAddress($data['email']);

    $mail->isHTML(true);

    $mail->Subject = 'Form 6 Application Processed';
    $mail->Body = "
    <div style='font-family:Arial,sans-serif;font-size:14px;color:#eeeee;'>
        <div style='width: 500px; height: color:#333'>
            <p>Good day <strong>{$data['first_name']} {$data['last_name']}</strong>,</p>

            <p>Your <strong>Application for Leave (Form 6)</strong> has been processed.</p>

            <p>You can securely view and print your form using the button below:</p>

            <p style='margin:25px 0'>
            <a href='{$printLink}' 
            style='
            background:#2e6cff;
            color:#fff;
            padding:12px 22px;
            border-radius:6px;
            text-decoration:none;
            font-weight:bold;
            display:inline-block;
            '>
            View Form
            </a>
            </p>

            <p><b>Important:</b> This link is private and intended only for you. Do not share it with others.</p>

            <p>Thank you,<br>
            Leave Management System</p>

            <hr>
            <p style='font-size:12px;color:#777'>
            This is an automated system email. Replies are not monitored.
            </p>

        </div>
    </div>
    ";


    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("MAIL ERROR: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}