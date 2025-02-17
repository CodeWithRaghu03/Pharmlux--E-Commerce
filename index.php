<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if ($user_id && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            header("Location: homepage.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    } elseif (isset($_POST['signup'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $email = $_POST['email'];
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            $success = "Signup successful. You can now login.";
        } else {
            $error = "Signup failed. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Signup - PharmLux</title>
    <link rel="stylesheet" type="text/css" href="style/index.css">
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <!-- Login Form -->
        <h2>Login</h2>
        <form method="post" action="index.php">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <button type="submit" name="login">Login</button>
            </div>
        </form>
        <p class="toggle-link"><a href="forgot_password.php">Forgot Password?</a></p>
        <p class="toggle-link">Don't have an account? <a href="index.php?signup">Sign up here</a></p>

        <!-- Signup Form -->
        <?php if (isset($_GET['signup'])): ?>
            <h2>Sign Up</h2>
            <form method="post" action="index.php">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="signup">Sign Up</button>
                </div>
            </form>
            <p class="toggle-link">Already have an account? <a href="index.php">Login here</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
