<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];

    $conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    if ($stmt->execute()) {
        echo "Added to wishlist!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add to Wishlist</title>
</head>
<body>
<form method="post" action="wishlist.php">
    Product ID: <input type="text" name="product_id" required><br>
    <input type="submit" value="Add to Wishlist">
</form>
</body>
</html>
