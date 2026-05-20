<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$id = $_GET['id'];

/* Optional: delete image file */
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product && file_exists("../images/" . $product['image'])) {
    unlink("../images/" . $product['image']);
}

/* Delete product */
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header("Location: manage_products.php");
exit();
