<?php
// simple header for every page

// boot up php session for this user if it’s not already started
// lets us use $_SESSION to track logged in user, cart, etc
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// get db config and helper functions
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// how many items are in this users cart
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartCount = getCartCount($pdo, (int)$_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Books</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<header>
    <h1>We Sell Books!</h1>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="cart.php">Cart (<?php echo $cartCount; ?>)</a>
            <a href="orders.php">Order History</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>