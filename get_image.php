<?php
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    $conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($product_image);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        header("Content-Type: image/jpeg");
        echo $product_image;
    } else {
        echo "Image not found.";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid product ID.";
}
?>
