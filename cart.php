<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details from cart
$cart_items = $_SESSION['cart'];
$product_ids = array_keys($cart_items);

// Handle form submission for saving delivery location
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];
    $user_id = $_SESSION['user_id'];

    // Save the address in the database
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, address_line1, address_line2, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $address_line1, $address_line2, $city, $state, $zip_code, $country);
    $stmt->execute();
    $stmt->close();

    // Redirect to the cart page
    header("Location: cart.php");
    exit();
}

// Fetch saved addresses for the user
$saved_addresses = [];
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $saved_addresses[] = $row;
}
$stmt->close();

// Fetch product details for products in the cart
$products = [];
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $conn->prepare("SELECT product_id, product_name, price, product_image FROM products WHERE product_id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[$row['product_id']] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - PharmLux</title>
    <link rel="stylesheet" type="text/css" href="style/cartstyle.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function () {
                    const productId = this.dataset.productId;
                    const quantity = this.value;

                    if (quantity > 0) {
                        fetch('update_cart.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `product_id=${productId}&quantity=${quantity}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                document.querySelector(`#subtotal-${productId}`).innerText = `$${data.subtotal.toFixed(2)}`;
                                document.querySelector('#total').innerText = `$${data.total.toFixed(2)}`;
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    } else {
                        alert('Invalid quantity.');
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>My Cart</h1>
        <?php if (!empty($cart_items) && !empty($products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($cart_items as $product_id => $quantity):
                        $product = $products[$product_id];
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><img src="get_image.php?product_id=<?= htmlspecialchars($product['product_id']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" width="100"></td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td>
                                <input type="number" class="quantity-input" data-product-id="<?= $product_id ?>" value="<?= $quantity ?>" min="1">
                            </td>
                            <td id="subtotal-<?= $product_id ?>">$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <form action="remove_from_cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">
                <strong>Total: <span id="total">$<?= number_format($total, 2) ?></span></strong>
            </div>
            <div class="actions">
                <a href="Fake_Payment_Demo.php" class="btn btn-primary">Proceed to Checkout</a>
                <a href="homepage.php" class="btn btn-danger">Continue Shopping</a>
            </div>
        <?php else: ?>
            <p>Your cart is empty. <a href="homepage.php" style="color: #1de9b6;">Start shopping now!</a></p>
        <?php endif; ?>

        <div class="address-section">
            <h2>Delivery Location</h2>
            <form method="post" action="cart.php" class="address-form">
                <label for="address_line1">Street Address Line 1:</label>
                <input type="text" id="address_line1" name="address_line1" required>
                <label for="address_line2">Street Address Line 2 (Optional):</label>
                <input type="text" id="address_line2" name="address_line2">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
                <label for="state">State:</label>
                <input type="text" id="state" name="state" required>
                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code" required>
                <label for="country">Country:</label>
                <input type="text" id="country" name="country" required>
                <input type="submit" value="Save Delivery Location">
            </form>

            <!-- Display existing addresses -->
            <div class="saved-addresses">
                <h3>Your Saved Addresses</h3>
                <?php if (!empty($saved_addresses)): ?>
                    <ul>
                        <?php foreach ($saved_addresses as $address): ?>
                            <li>
                                <p><?= htmlspecialchars($address['address_line1']) ?></p>
                                <?php if (!empty($address['address_line2'])): ?>
                                    <p><?= htmlspecialchars($address['address_line2']) ?></p>
                                <?php endif; ?>
                                <p><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['zip_code']) ?></p>
                                <p><?= htmlspecialchars($address['country']) ?></p>
                                <form method="post" action="remove_address.php">
                                    <input type="hidden" name="address_id" value="<?= $address['address_id'] ?>">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No saved addresses.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
