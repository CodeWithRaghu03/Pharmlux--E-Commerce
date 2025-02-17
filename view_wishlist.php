<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT products.name, products.description, products.price, products.image FROM wishlist 
        JOIN products ON wishlist.product_id = products.id 
        WHERE wishlist.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Wishlist</title>
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
    <h1>Your Wishlist</h1>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="product-info">
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                    <?php echo htmlspecialchars($row['description']); ?><br>
                    Price: ₹<?php echo htmlspecialchars($row['price']); ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
