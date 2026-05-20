<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['product_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

/* Check if product exists */
$stmt = $conn->prepare(
    "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?"
);
$stmt->execute([$user_id, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmt = $conn->prepare(
        "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?"
    );
    $stmt->execute([$user_id, $product_id]);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)"
    );
    $stmt->execute([$user_id, $product_id]);
}

header("Location: cart.php");
exit();
