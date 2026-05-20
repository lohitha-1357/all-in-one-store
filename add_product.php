<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../includes/db.php';

/* Protect admin access */
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit();
}

$message = '';

if (isset($_POST['add_product'])) {

    $name = trim($_POST['name']);
    $price = $_POST['price'];
    $description = trim($_POST['description']);

    /* Image upload handling */
    if (!empty($_FILES['image']['name'])) {

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = '../images/' . $imageName;

        $imageType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($imageType, $allowedTypes)) {
            $message = "Only JPG, PNG, JPEG, WEBP images allowed.";
        } else {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
        }

    } else {
        $imageName = '';
    }

    /* Insert product */
    if (empty($message)) {
        $stmt = $conn->prepare(
            "INSERT INTO products (name, price, description, image) 
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([$name, $price, $description, $imageName]);

        header("Location: manage_products.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 45%;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
            color: #555;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Product</h2>

    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Price</label>
        <input type="number" step="0.01" name="price" required>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Product Image</label>
        <input type="file" name="image" required>

        <button type="submit" name="add_product">Add Product</button>
    </form>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>
