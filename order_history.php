<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle order delivery confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered', updated_at = NOW() WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch order history
$stmt = $conn->prepare("
    SELECT 
        o.order_id, 
        o.order_date, 
        o.total, 
        o.status, 
        o.created_at, 
        o.updated_at
    FROM 
        orders o
    WHERE 
        o.user_id = ?
    ORDER BY 
        o.order_date DESC
");

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_results = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Order History</h1>
            <nav class="nav">
                <a href="homepage.php">Home</a>
                <a href="cart.php">My Cart</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        
        <section class="order-history">
            <h2>Your Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($order_results && $order_results->num_rows > 0): ?>
                        <?php while ($row = $order_results->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_id']) ?></td>
                                <td><?= htmlspecialchars($row['order_date']) ?></td>
                                <td>$<?= htmlspecialchars($row['total']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                                            <button type="submit">Mark as Delivered</button>
                                        </form>
                                    <?php else: ?>
                                        Delivered
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        
        <footer class="footer">
            <p>&copy; 2024 PharmLux. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
