<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "mark_delivered") {
    $conn = new mysqli("localhost", "root", "", "ecommerce_db");
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database connection failed"]);
        exit();
    }

    $user_id = $_SESSION["user_id"];

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered', updated_at = NOW() WHERE user_id = ? AND status = 'Confirmed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Orders updated to 'Delivered'"]);
    } else {
        echo json_encode(["status" => "error", "message" => "No orders to update"]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
