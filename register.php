<?php
session_start();
include('../includes/db.php'); // Database connection

if (isset($_POST['register'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = 'user'; // Default role for users

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $error_message = "Email is already registered!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hashed_password, $role]);

        // Log in user immediately
        $_SESSION['user_id'] = $conn->lastInsertId();
        header("Location: ../index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registration</title>
<style>
    body { font-family: Arial; background: #f4f4f9; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
    .container { background:#fff; padding:30px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:100%; max-width:400px; }
    input, button { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; font-size:1em; }
    button { background:#28a745; color:#fff; border:none; cursor:pointer; }
    button:hover { background:#218838; }
    .error { color:red; text-align:center; margin-top:10px; }
</style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <?php if (isset($error_message)) echo "<p class='error'>".$error_message."</p>"; ?>
</div>
</body>
</html>
