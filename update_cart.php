<?php
session_start();
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

$response = [];

// Check if product_id and quantity are provided
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // Validate the quantity
    if ($quantity > 0) {
        // Update the cart
        $_SESSION['cart'][$product_id] = $quantity;

        // Example: Calculate subtotal (replace with your pricing logic)
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            $price = 10; // Replace with your product price lookup
            $subtotal += $qty * $price;
        }

        $response['subtotal'] = $subtotal;
        $response['message'] = "Cart updated successfully.";
    } else {
        $response['error'] = "Invalid quantity.";
    }
} else {
    $response['error'] = "Invalid request.";
}

echo json_encode($response);
exit();
?>
