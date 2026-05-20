<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Create order
$conn->prepare("INSERT INTO orders (user_id, created_at) VALUES (?, NOW())")
     ->execute([$user_id]);

$order_id = $conn->lastInsertId();

// Order items
$stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);

foreach ($stmt as $row) {
    $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity)
        VALUES (?, ?, ?)
    ")->execute([$order_id, $row['product_id'], $row['quantity']]);
}

// Email
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'yourgmail@gmail.com';
$mail->Password = 'APP_PASSWORD';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('yourgmail@gmail.com', 'Ecommerce Store');
$mail->addAddress($user_email);
$mail->isHTML(true);
$mail->Subject = 'Order Placed';
$mail->Body = "<p>Order ID: <b>$order_id</b></p>";
$mail->send();

// Clear cart
$conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

header("Location: order_success.php");
exit();
