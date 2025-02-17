<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if product_id is provided
if (isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    // Remove the product from the cart if it exists
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = "Product removed from cart.";
    } else {
        $_SESSION['message'] = "Product not found in cart.";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect back to the cart page
header("Location: cart.php");
exit();
?>
