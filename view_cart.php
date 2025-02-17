<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT products.name, cart.quantity, products.price, products.image FROM cart 
        JOIN products ON cart.product_id = products.id WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }
        ul li img {
            max-width: 100px;
            height: auto;
        }
        .product-info {
            flex: 1;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1>Your Cart</h1>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $subtotal = $row['quantity'] * $row['price']; ?>
            <li>
                <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="product-info">
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                    Quantity: <?php echo htmlspecialchars($row['quantity']); ?><br>
                    Price per item: ₹<?php echo htmlspecialchars($row['price']); ?><br>
                    Subtotal: ₹<?php echo $subtotal; ?>
                </div>
            </li>
            <?php $total += $subtotal; ?>
        <?php endwhile; ?>
    </ul>
    <h2>Total: ₹<?php echo $total; ?></h2>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
