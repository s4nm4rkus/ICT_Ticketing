<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Composer autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';   // For Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sanmarkusmorcoso@gmail.com'; // 🔹 Replace with your Gmail
        $mail->Password   = 'onvs ctzn stcj isrj';   // 🔹 Replace with Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress("ict.tayabas@deped.gov.ph", "Admin"); // 🔹 Where the form should send
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New message: $subject";
        $mail->Body    = "
            <h3>ICT Ticketing Message</h3>
            <p><b>From:</b> $name</p>
            <p><b>Email:</b> $email</p>
            <p><b>Subject:</b> $subject</p>
            <p><b>Message:</b><br/>" . nl2br($message) . "</p>
        ";

        $mail->send();
        echo "✅ Message sent successfully!";
    } catch (Exception $e) {
        echo "❌ Message could not be sent. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}