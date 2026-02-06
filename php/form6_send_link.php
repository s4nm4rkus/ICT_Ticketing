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


    $mail->Subject = 'Form 6 Application – Processed';
    $mail->Body = "
Good day {$data['first_name']} {$data['last_name']},

Your Application for Leave (Form 6) has been processed.

You may view and print your form using the link below:
$printLink

This link is personal. Please do not share it.

Thank you.
";

    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("MAIL ERROR: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}