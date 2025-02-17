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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - PharmLux</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            background: #101010;
            color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #202020;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }
        h1 {
            text-align: center;
            color: #ffd700;
        }
        .address-form input, .address-form select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #404040;
            background: #202020;
            color: #f5f5f5;
        }
        .address-form label {
            font-weight: bold;
        }
        .address-form input[type="submit"] {
            background: #1de9b6;
            color: #101010;
            cursor: pointer;
            border: none;
            transition: background 0.3s;
        }
        .address-form input[type="submit"]:hover {
            background: #ffd700;
        }
        .saved-addresses {
            margin-top: 20px;
        }
        .saved-addresses ul {
            list-style: none;
            padding: 0;
        }
        .saved-addresses li {
            padding: 10px;
            background: #303030;
            border-radius: 10px;
            margin-bottom: 10px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Cart</h1>
        
        <div class="address-section">
            <h2>Delivery Location</h2>
            <form method="post" action="address.php" class="address-form">
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
