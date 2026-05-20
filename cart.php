<?php
session_start();
require_once '../includes/db.php';

/* User must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   ADD TO CART (MISSING PART)
   ========================= */
if (isset($_POST['add_to_cart'])) {

    $product_id = $_POST['product_id'];

    // Check if product already in cart
    $stmt = $conn->prepare(
        "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?"
    );
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Increase quantity
        $stmt = $conn->prepare(
            "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?"
        );
        $stmt->execute([$user_id, $product_id]);
    } else {
        // Insert new product
        $stmt = $conn->prepare(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)"
        );
        $stmt->execute([$user_id, $product_id]);
    }

    header("Location: cart.php");
    exit();
}

/* =========================
   UPDATE QUANTITY
   ========================= */
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);

    $stmt = $conn->prepare(
        "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?"
    );
    $stmt->execute([$quantity, $user_id, $product_id]);
}

/* =========================
   REMOVE FROM CART
   ========================= */
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare(
        "DELETE FROM cart WHERE user_id = ? AND product_id = ?"
    );
    $stmt->execute([$user_id, $product_id]);
}

/* =========================
   FETCH CART ITEMS
   ========================= */
$stmt = $conn->prepare(
    "SELECT products.id AS product_id, products.name, products.price, cart.quantity
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ?"
);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Calculate total */
$total_cost = 0;
foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Your Cart</h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <?php if (empty($cart_items)) : ?>
            <p>Your cart is empty.</p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td>$<?= number_format($item['price'], 2); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1" style="width:60px;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                <button type="submit" name="remove_from_cart">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total:</strong></td>
                        <td colspan="2"><strong>$<?= number_format($total_cost, 2); ?></strong></td>
                    </tr>
                </tfoot>
                <tr>
    <td colspan="5" style="text-align:right;">
        <form action="../place_order.php" method="POST">
            <button type="submit" class="checkout-btn">
                Place Order
            </button>
        </form>
    </td>
</tr>


            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
