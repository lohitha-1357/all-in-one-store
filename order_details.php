<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT products.name, products.price, order_items.quantity
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
</head>
<body>

<h2>Order #<?= $order_id ?></h2>

<table border="1" cellpadding="10">
<tr>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Total</th>
</tr>

<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td>₹<?= $item['price'] ?></td>
    <td><?= $item['quantity'] ?></td>
    <td>₹<?= $item['price'] * $item['quantity'] ?></td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
