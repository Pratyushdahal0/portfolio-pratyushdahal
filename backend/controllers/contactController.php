<?php
header("Content-Type: application/json");

require_once "../config/db.php";
require_once "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all fields."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);
    exit;
}

try {
    $sql = "INSERT INTO contact_messages (name, email, subject, message)
            VALUES (:name, :email, :subject, :message)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":subject" => $subject,
        ":message" => $message
    ]);

    $mailConfig = require "../config/mail.php";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mailConfig["host"];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig["username"];
        $mail->Password = $mailConfig["password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mailConfig["port"];

        $mail->setFrom($mailConfig["from_email"], $mailConfig["from_name"]);
        $mail->addAddress($mailConfig["to_email"], $mailConfig["to_name"]);
        $mail->addReplyTo($email, $name);

        $safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, "UTF-8"));

        $mail->isHTML(true);
        $mail->Subject = "New Portfolio Message: " . $safeSubject;

        $mail->Body = "
            <h2>New Portfolio Contact Message</h2>
            <p><strong>Name:</strong> {$safeName}</p>
            <p><strong>Email:</strong> {$safeEmail}</p>
            <p><strong>Subject:</strong> {$safeSubject}</p>
            <hr>
            <p><strong>Message:</strong></p>
            <p>{$safeMessage}</p>
        ";

        $mail->AltBody =
            "New Portfolio Contact Message\n\n" .
            "Name: {$name}\n" .
            "Email: {$email}\n" .
            "Subject: {$subject}\n\n" .
            "Message:\n{$message}";

        $mail->send();

        echo json_encode([
            "success" => true,
            "message" => "Thank you! Your message has been sent successfully."
        ]);
    } catch (Exception $emailError) {
    echo json_encode([
        "success" => true,
        "message" => "Saved to database, but email failed: " . $mail->ErrorInfo
    ]);
}
} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Something went wrong while saving your message."
    ]);
}