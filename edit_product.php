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

/* Fetch product */
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found";
    exit();
}

/* Update product */
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    /* Image handling */
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $imageName);
    } else {
        $imageName = $product['image'];
    }

    $stmt = $conn->prepare(
        "UPDATE products SET name=?, price=?, description=?, image=? WHERE id=?"
    );
    $stmt->execute([$name, $price, $description, $imageName, $id]);

    header("Location: manage_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
</head>
<body>

<h2>Edit Product</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Name</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required><br><br>

    <label>Price</label><br>
    <input type="number" name="price" step="0.01" value="<?= $product['price']; ?>" required><br><br>

    <label>Description</label><br>
    <textarea name="description" required><?= htmlspecialchars($product['description']); ?></textarea><br><br>

    <label>Image</label><br>
    <input type="file" name="image"><br>
    <small>Current Image:</small><br>
    <img src="../images/<?= $product['image']; ?>" width="80"><br><br>

    <button type="submit" name="update">Update Product</button>
</form>

</body>
</html>
