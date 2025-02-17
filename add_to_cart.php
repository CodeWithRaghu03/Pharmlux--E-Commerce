<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if product_id is set and is a valid number
if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Ensure the cart is initialized as an array if it does not exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        // If the product is already in the cart, increase the quantity by 1
        $_SESSION['cart'][$product_id]++;
    } else {
        // If the product is not in the cart, add it with quantity 1
        $_SESSION['cart'][$product_id] = 1;
    }

    // Redirect to the homepage or cart page (you can change this as needed)
    header("Location: homepage.php"); // Or change this to redirect to cart.php if preferred
    exit();
} else {
    // If product_id is not set or invalid, redirect to homepage with an error message
    header("Location: homepage.php?error=invalid_product_id");
    exit();
}
?>
