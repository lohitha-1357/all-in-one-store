<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Fetch user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
<h2>Welcome, <?= htmlspecialchars($user['email']); ?>!</h2>
<ul>
    <li><a href="cart.php">My Cart</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
</body>
</html>
