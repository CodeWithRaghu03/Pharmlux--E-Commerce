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
    $search_results = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmLux - Your Premium Pharmacy</title>
    <link rel="stylesheet" type="text/css" href="style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        /* Header Styles */
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s;
        }

        .nav a:hover {
            color: #ffcc00;
        }

        /* Hero Section */
        .hero {
            background: url('your-hero-image.jpg') no-repeat center center/cover;
            color: white;
            text-align: center;
            padding: 50px 20px;
        }

        /* Search Bar */
        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            border: none;
            border-radius: 5px;
        }

        .search-bar button {
            padding: 10px 15px;
            background-color: #ffcc00;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #e6b800;
        }

        /* Product Section */
        .products {
            padding: 20px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .product-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 15px;
            width: calc(33% - 30px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .product-item:hover {
            transform: scale(1.02);
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100 %;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Footer Styles */
        .footer {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Back to Top Button */
        #back-to-top {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 100;
            border: none;
            outline: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            display: none; /* Hidden by default */
        }

        .notification-info {
            background-color: #17a2b8;
        }

        .notification-success {
            background-color: #28a745;
        }

        .notification-error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>PharmLux</h1>
            <p>Welcome, <?= htmlspecialchars($username); ?>. Experience the Pinnacle of Healthcare.</p>
            <nav class="nav">
                <a href="#products" class="scroll-link">Products</a>
                <a href="#about" class="scroll-link">About Us</a>
                <a href="#contact" class="scroll-link">Contact</a>
                <a href="cart.php">My Cart</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        
        <section class="hero">
            <h2>Your Premium Pharmacy</h2>
            <p>Discover premium products for a healthier tomorrow.</p>
            <div class="search-bar">
                <form action="" method="get">
                    <input type="text" name="search" placeholder="Search for products..." aria-label="Search for products">
                    <button type="submit">Search</button>
                </form>
            </div>
        </section>
        
        <section class="products" id="products">
            <h2>Our Products</h2>
            <div class="product-list">
                <?php if ($search_results && $search_results->num_rows > 0): ?>
                    <?php while ($row = $search_results->fetch_assoc()): ?>
                        <div class="product-item card-flip" data-product-id="<?= htmlspecialchars($row['product_id']) ?>" data-product-name="<?= htmlspecialchars($row['product_name']) ?>" data-product-description="<?= htmlspecialchars($row['product_description']) ?>" data-product-price="<?= htmlspecialchars($row['price']) ?>" data-product-image="get_image.php?product_id=<?= htmlspecialchars($row['product_id']) ?>">
                            <div class="card-flip-inner">
                                <div class="card-flip-front">
                                    <img src="get_image.php?product_id=<?= htmlspecialchars($row['product_id']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?> Image">
                                    <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                    <p><strong>Price:</strong> $<?= htmlspecialchars($row['price']) ?></p>
                                    <div class="rating">
                                        <span>★</span><span>★</span><span>★</span><span>☆</span><span>☆</span>
                                    </div>
                                </div>
                                <div class="card-flip-back">
                                    <h2>Product Details</h2>
                                    <p>More details about <?= htmlspecialchars($row['product_name']) ?>.</p>
                                    <p><?= htmlspecialchars($row['product_description']) ?></p>
                                    <button class="btn-primary view-details">View Details</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="testimonials">
            <h2>What Our Customers Say</h2>
            <div class="testimonial">
                <p>"PharmLux has the best products and customer service. Highly recommend!"</p>
                <p><strong>- Jane Doe</strong></p>
            </div>
            <div class="testimonial">
                <p>"I've never been disappointed with my purchases from PharmLux."</p>
                <p><strong>- John Smith</strong></p>
            </div>
        </section>

        <section class="newsletter">
            <h2>Stay Updated!</h2>
            <form action="subscribe.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required aria-label="Email for subscription">
                <button type="submit" class="btn-primary">Subscribe</button>
            </form>
        </section>

        <section class="about" id="about">
            <h2>About Us</h2>
            <p>At PharmLux, we believe in delivering excellence. Our premium products are curated to ensure you lead a healthier and more fulfilling life. Trust PharmLux for all your healthcare needs.</p>
        </section>
        
        <section class="contact" id="contact">
            <h2>Contact Us</h2>
            <p><strong>Email:</strong> info@pharmlux.com</p>
            <p><strong>Phone:</strong> +1 (555) 123-4567</p>
            <p>We're here to assist you. Reach out to us with any questions or concerns!</p>
        </section>
        
        <footer class="footer">
            <p>&copy; 2024 PharmLux. All rights reserved. Designed with care and innovation.</p>
        </footer>
        
        <button id="back-to-top" title="Back to Top">&uarr;</button>
        
        <div class="notification notification-info">
            <p>Info: This is an informational notification!</p>
            <button class="close-notification">×</button>
        </div>

        <div class="modal" id="product-modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2 id="modal-product-name"></h2>
                <img id="modal-product-image" src="" alt="Product Image" />
                <p id="modal-product-description"></p>
                <p><strong>Price:</strong> <span id="modal-product-price"></span></p>
                <form action="add_to_cart.php" method="POST" id="modal-add-to-cart-form">
                    <input type="hidden" name="product_id" id="modal-product-id">
                    <input type="hidden" name="product_name" id="modal-product-name-hidden">
                    <input type="hidden" name="price" id="modal-product-price-hidden">
                    <button type="submit" class="btn-primary">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('.scroll-link').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top
                }, 800);
            });

            var backToTop = $('#back-to-top');
            $(window).scroll(function() {
                if ($(window).scrollTop() > 300) {
                    backToTop.fadeIn();
                } else {
                    backToTop.fadeOut();
                }
            });

            backToTop.click(function() {
                $('html, body').animate({scrollTop: 0}, 800);
                return false;
            });

            // Modal logic
            const modal = $('#product-modal');
            const closeModal = $('.close-modal');

            // Show modal with product details
            $('.view-details').click(function() {
                const productItem = $(this).closest('.product-item');
                $('#modal-product-name').text(productItem.data('product-name'));
                $('#modal-product-image').attr('src', productItem.data('product-image'));
                $('#modal-product-description').text(productItem.data('product-description'));
                $('#modal-product-price').text('$' + productItem.data('product-price'));
                $('#modal-product-id').val(productItem.data('product-id'));
 $('#modal-product-name-hidden').val(productItem.data('product-name'));
                $('#modal-product-price-hidden').val(productItem.data('product-price'));
                modal.show();
            });

            closeModal.click(function() {
                modal.hide();
            });

            $(window).click(function(event) {
                if ($(event.target).is(modal)) {
                    modal.hide();
                }
            });

            // Notification dismissal
            $('.close-notification').click(function() {
                $(this).parent('.notification').fadeOut();
            });

            // Show notification on page load (for demonstration)
            $('.notification').fadeIn().delay(3000).fadeOut();
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>