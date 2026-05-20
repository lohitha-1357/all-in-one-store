<?php
session_start();
include 'includes/db.php';

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$user_email = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_email = $user['email'];
    }
}

// Fetch products
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin:0; padding:0; background:#f4f4f9; }
        header { background:#333; color:white; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; }
        header a { color:white; margin-left:10px; text-decoration:none; }
        .main-container { width:90%; max-width:1200px; margin:20px auto; }
        .product-list { display:flex; flex-wrap:wrap; gap:20px; }
        .product { background:#fff; padding:15px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); width:200px; }
        .product img { width:100%; height:auto; }
        .add-to-cart-button { background:#28a745; color:white; border:none; padding:8px 10px; margin-top:10px; cursor:pointer; border-radius:4px; width:100%; }
        .add-to-cart-button:hover { background:#218838; }
        .cart-link { display:flex; align-items:center; gap:5px; }
        .logout-button { background:#f44336; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; color:white; }
        .logout-button:hover { background:#e53935; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Online Store</h1>
        </div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Hello, <?= htmlspecialchars($user_email); ?></span>
                <a href="pages/cart.php" class="cart-link">Cart</a>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
            <?php else: ?>
                <a href="pages/login.php">Login</a>
                <a href="pages/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="main-container">
        <h2>Products</h2>
        <div class="product-list">
            <?php if (empty($products)): ?>
                <p>No products available.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <h3><?= htmlspecialchars($product['name']); ?></h3>
                        <p>Price: $<?= number_format($product['price'], 2); ?></p>
                        <p><?= htmlspecialchars($product['description']); ?></p>
                        <?php if (!empty($product['image'])): ?>
                            <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                        <?php endif; ?>
                        <form method="POST" action="pages/add_to_cart.php">

                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer style="text-align:center; margin:20px 0;">
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
