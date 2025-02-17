<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $_GET['query'] : '';
$stmt = $conn->prepare("SELECT product_id, product_name, product_description, price, product_image FROM products WHERE product_name LIKE ?");
$search_term = "%{$query}%";
$stmt->bind_param("s", $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - PharmLux</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #f7f9fc, #e0e0e0);
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            background: linear-gradient(45deg, #2c3e50, #16a085);
            padding: 20px;
            color: white;
            border-bottom: 3px solid #d4af37;
        }
        .header h1 {
            margin: 0;
            font-size: 2em;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 1.8em;
            color: #16a085;
            text-align: center;
            margin-bottom: 20px;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-item {
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transform-style: preserve-3d;
            transition: transform 0.5s, box-shadow 0.5s;
        }
        .product-item:hover {
            transform: translateY(-10px) rotateX(10deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }
        .product-item:hover img {
            transform: scale(1.1);
        }
        .product-details {
            padding: 15px;
            text-align: center;
        }
        .product-details h3 {
            margin: 0;
            font-size: 1.2em;
            color: #2c3e50;
        }
        .product-details p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .price {
            font-size: 1.2em;
            color: #16a085;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin: 20px auto;
            padding: 15px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            font-size: 1em;
            font-weight: bold;
            border-radius: 8px;
            transition: background 0.3s, transform 0.3s;
            text-align: center;
        }
        .back-link:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PharmLux</h1>
        <p>Search Results for "<strong><?= htmlspecialchars($query) ?></strong>"</p>
    </div>
    <div class="container">
        <h2>Products Found</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="product-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-item">
                        <img src="<?= htmlspecialchars($row['product_image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                        <div class="product-details">
                            <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                            <p><?= htmlspecialchars($row['product_description']) ?></p>
                            <p class="price">$<?= htmlspecialchars($row['price']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #7f8c8d;">No results found for "<strong><?= htmlspecialchars($query) ?></strong>".</p>
        <?php endif; ?>
        <a href="homepage.php" class="back-link">Back to Home</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
