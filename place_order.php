<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email']; // set during login

/* =========================
   FAST2SMS FUNCTION
   ========================= */
function sendOrderSMS($mobile, $order_id, $amount) {

    $apiKey = "FSiYOUe8noDrHkGRb3067xIcBK5NM1zfvXuEQpgZh2VLACwy9spUyQjgMmfT7oYIA2ikCqDrK8R4sVXL"; // 🔴 paste your API key

    $message = "Your order #$order_id placed successfully. Amount Rs.$amount. Thank you for shopping with us.";

    $data = [
        "route" => "p",
        "message" => $message,
        "language" => "english",
        "numbers" => $mobile,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "authorization: $apiKey",
            "content-type: application/json"
        ],
    ]);

    curl_exec($ch);
    curl_close($ch);
}

/* =========================
   CREATE ORDER
   ========================= */
$stmt = $conn->prepare("INSERT INTO orders (user_id, created_at) VALUES (?, NOW())");
$stmt->execute([$user_id]);
$order_id = $conn->lastInsertId();

/* =========================
   FETCH CART ITEMS + TOTAL
   ========================= */
$stmt = $conn->prepare("
    SELECT c.product_id, c.quantity, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_amount = 0;

foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];

    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$order_id, $item['product_id'], $item['quantity']]);
}

/* =========================
   SEND EMAIL
   ========================= */
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

$mail->setFrom('yourgmail@gmail.com', 'Ecommerce Store');
$mail->addAddress($user_email);
$mail->isHTML(true);
$mail->Subject = 'Order Confirmed';
$mail->Body = "<h3>Your order #$order_id has been placed successfully.</h3>
               <p>Total Amount: Rs.$total_amount</p>";
$mail->send();

/* =========================
   SEND SMS
   ========================= */
$stmt = $conn->prepare("SELECT mobile FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($user['mobile'])) {
    sendOrderSMS($user['mobile'], $order_id, $total_amount);
}

/* =========================
   CLEAR CART
   ========================= */
$conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

header("Location: order_success.php");
exit();
/* =========================
   SEND SMS USING FAST2SMS
   ========================= */

$apiKey = FAST2SMS_API_KEY;

$user_mobile = $_SESSION['user_mobile']; // must be set during login

$message = "Your order #$order_id has been placed successfully. Thank you for shopping with us.";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "route" => "q",
        "message" => $message,
        "language" => "english",
        "numbers" => $user_mobile,
    ]),
    CURLOPT_HTTPHEADER => [
        "authorization: $apiKey",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

