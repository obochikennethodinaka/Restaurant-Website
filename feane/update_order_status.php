<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Only admin can change order status
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // List of allowed statuses
    $allowed_statuses = ['pending', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
    
    if ($order_id <= 0 || !in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo 'invalid_input';
        exit();
    }
    
    // Optional: Verify order exists before updating
    $check = mysqli_query($conn, "SELECT id FROM orders WHERE id = $order_id");
    if (mysqli_num_rows($check) == 0) {
        http_response_code(404);
        echo 'order_not_found';
        exit();
    }
    
    // Update order status
    $update = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if (mysqli_query($conn, $update)) {
        echo 'ok';
    } else {
        http_response_code(500);
        echo 'db_error';
    }
} else {
    http_response_code(405);
    echo 'method_not_allowed';
}
?>