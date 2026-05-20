<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
$stmt = $conn->prepare("
    SELECT orders.id, users.username, users.email, orders.created_at
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders</title>
    <style>
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border:1px solid #ccc; }
        th { background:#333; color:#fff; }
    </style>
</head>
<body>

<h2>All Orders</h2>

<table>
<tr>
    <th>Order ID</th>
    <th>User</th>
    <th>Email</th>
    <th>Date</th>
    <th>View</th>
</tr>

<?php foreach ($orders as $order): ?>
<tr>
    <td><?= $order['id'] ?></td>
    <td><?= htmlspecialchars($order['username']) ?></td>
    <td><?= htmlspecialchars($order['email']) ?></td>
    <td><?= $order['created_at'] ?></td>
    <td>
        <a href="order_details.php?id=<?= $order['id'] ?>">View</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
