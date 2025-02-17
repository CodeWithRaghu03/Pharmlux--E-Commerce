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

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    $stmt->fetch();
    $stmt->close();
} else {
    $username = "Guest"; // Default value if the username is not fetched
}

$search_query = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : '%';
$sql = "SELECT product_id, product_name, product_description, price, product_image FROM products WHERE product_name LIKE ? OR product_description LIKE ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $search_results = $stmt->get_result();
    $stmt->close();
} else {
    $search_results = null;  // Initialize $search_results to null if the query fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmLux - Your Premium Pharmacy</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Fixed icons */
        .fixed-icon, .order-history-icon {
            position: fixed;
            z-index: 1000;
            width: 60px;
            height: 60px;
        }

        .fixed-icon {
            bottom: 20px;
            right: 20px;
        }

        .order-history-icon {
            bottom: 90px;
            right: 20px;
        }

        .fixed-icon img, .order-history-icon img {
            width: 100%;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .fixed-icon img:hover, .order-history-icon img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        /* Header */
        .header {
            text-align: center;
            background: rgba(0, 31, 63, 0.9);
            border-radius: 15px;
            padding: 20px 10px;
            box-shadow: 0 5px 20px rgba(255, 65, 54, 0.5);
            position: fixed; /* Fixed position */
            top: 0; /* Stick to the top */
            left: 0; /* Align to the left */
            width: 100%; /* Full width */
            z-index: 1000; /* Ensure it's on top */
        }

        body {
            padding-top: 120px; /* Create space for the fixed header */
        }

        /* Product Description Styling (on back side) */
        .product-description {
            font-size: 0.9rem;
            line-height: 1.4;
            color: #ddd;
            margin-top: 15px;
            text-transform: lowercase;
            height: 150px;
            overflow-y: auto;
            padding-right: 10px;
            display: none;
            transition: max-height 0.3s ease;
        }

        .product-item:hover .card-flip-inner .product-description {
            display: block;
            max-height: 150px;
        }

        .product-description::-webkit-scrollbar {
            width: 8px;
        }

        .product-description::-webkit-scrollbar-thumb {
            background-color: #0074d9;
            border-radius: 5px;
        }

        .product-description::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>PharmLux</h1>
            <p>Welcome, <?= htmlspecialchars($username); ?>. Experience the Pinnacle of Healthcare.</p>
            <nav class="nav">
                <a href="#products">Products</a>
                <a href="#" class="open-modal" data-modal="about-modal">About Us</a>
                <a href="#" class="open-modal" data-modal="contact-modal">Contact</a>
                <a href="cart.php">My Cart</a>
                <a href="order_history.php">Order History</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <section>
            <video autoplay muted loop class="video-background">
                <source src="12.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </section>

        <section>
            <h2>Your Premium Pharmacy</h2>
            <p>Discover premium products for a healthier tomorrow.</p>
            <div class="search-bar">
                <form action="" method="get">
                    <input type="text" name="search" placeholder="Search for products...">
                    <button type="submit">Search</button>
                </form>
            </div>
        </section>

        <!-- Fixed icons -->
        <div class="fixed-icon">
            <a href="https://share.chatling.ai/s/9LQs33B95hENnl3" target="_blank">
                <img src="b48c8274-61df-480f-9cd9-47d697ef03e9.jpg" alt="Icon">
            </a>
        </div>
        <div class="order-history-icon">
            <a href="order_history.php" title="View Order History">
                
            </a>
        </div>

        <section class="products" id="products">
            <h2>Our Products</h2>
            <div class="product-list">
                <?php if ($search_results && $search_results->num_rows > 0): ?>
                    <?php while ($row = $search_results->fetch_assoc()): ?>
                        <div class="product-item">
                            <div class="card-flip-inner">
                                <div class="card-flip-front">
                                    <img src="get_image.php?product_id=<?= htmlspecialchars($row['product_id']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                                    <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                    <p><strong>Price:</strong> $<?= htmlspecialchars($row['price']) ?></p>
                                </div>
                                <div class="card-flip-back">
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['product_id']) ?>">
                                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>">
                                        <input type="hidden" name="price" value="<?= htmlspecialchars($row['price']) ?>">
                                        <button type="submit" class="btn-primary">Add to Cart</button>
                                    </form>
                                    <p class="product-description"><?= htmlspecialchars($row['product_description']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2024 PharmLux. All rights reserved.</p>
        </footer>
    </div>

    <!-- About Us Modal -->
    <div id="about-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>About Us</h2>
            <p>At PharmLux, we believe in delivering excellence. Our premium products are curated to ensure you lead a healthier and more fulfilling life. Trust PharmLux for all your healthcare needs.</p>
        </div>
    </div>

    <!-- Contact Us Modal -->
    <div id="contact-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Contact Us</h2>
            <p><strong>Email:</strong> Mohak@pharmlux.com</p>
            <p><strong>Phone:</strong> 7367943838</p>
            <p>We're here to assist you. Reach out to us with any questions or concerns!</p>
        </div>
    </div>

    <script>
        // Modal functionality
        $(document).ready(function() {
            $('.open-modal').click(function(e) {
                e.preventDefault();
                var modalId = $(this).data('modal');
                $('#' + modalId).fadeIn();
            });

            $('.close-modal').click(function() {
                $(this).closest('.modal').fadeOut();
            });

            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    $('.modal').fadeOut();
                }
            });
        });
    </script>
</body>
</html>
