<?php
session_start();

/* Protect admin access */
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php"); // ✅ FIXED
    exit();
}

require_once '../includes/db.php';

/* Fetch products */
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #28a745;
            color: white;
        }

        td img {
            width: 50px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .actions a {
            margin-right: 8px;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #007bff;
            color: #007bff;
            transition: 0.3s;
        }

        .actions a:hover {
            background-color: #007bff;
            color: white;
        }

        .btn-back {
            display: block;
            width: 180px;
            margin: 30px auto;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Manage Products</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['id']; ?></td>
                <td><?= htmlspecialchars($product['name']); ?></td>
                <td>$<?= number_format($product['price'], 2); ?></td>
                <td><?= htmlspecialchars($product['description']); ?></td>
                <td>
                    <img src="../images/<?= htmlspecialchars($product['image']); ?>" alt="Product">
                </td>
                <td class="actions">
                    <a href="edit_product.php?id=<?= $product['id']; ?>">Edit</a>
                    <a href="delete_product.php?id=<?= $product['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this product?');">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
</div>

</body>
</html>
