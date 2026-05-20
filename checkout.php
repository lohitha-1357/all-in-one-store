<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Create order
$conn->prepare("INSERT INTO orders (user_id, created_at) VALUES (?, NOW())")
     ->execute([$user_id]);

$order_id = $conn->lastInsertId();

// Move cart → order_items
$stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);

foreach ($stmt as $item) {
    $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity)
        VALUES (?, ?, ?)
    ")->execute([$order_id, $item['product_id'], $item['quantity']]);
}

// Email
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'yourgmail@gmail.com';
$mail->Password = 'APP_PASSWORD';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('yourgmail@gmail.com', 'Ecommerce');
$mail->addAddress($user_email);
$mail->Subject = 'Order Successful';
$mail->Body = "Your order ID is $order_id";
$mail->send();

// Clear cart
$conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

header("Location: order_success.php");
exit();
