<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db'); // Ensure this matches your database settings
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle address removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];
    $user_id = $_SESSION['user_id'];

    // Remove the address from the database
    $stmt = $conn->prepare("DELETE FROM addresses WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to the cart page
    header("Location: cart.php");
    exit();
}

$conn->close();
?>
