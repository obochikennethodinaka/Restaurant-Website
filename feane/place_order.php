<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $quantity = intval($_POST['quantity']);
    $total_price = floatval($_POST['total_price']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $payment_method = $_POST['payment_method']; // 'bank_transfer' or 'cod'

    $user_id = isLoggedIn() ? $_SESSION['user_id'] : NULL;
    $order_status = 'pending';          // delivery status
    $payment_status = ($payment_method === 'bank_transfer') ? 'pending' : 'pending';
    // For bank transfer, admin will verify payment manually.

    // Insert order
    $checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_method'");
    $hasPaymentCols = mysqli_num_rows($checkColumns) > 0;

    if ($hasPaymentCols) {
        $insert = "INSERT INTO orders (user_id, product_id, product_name, quantity, total_price, 
                    customer_name, customer_email, customer_phone, status, payment_method, payment_status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "iisidssssss", 
            $user_id, $product_id, $product_name, $quantity, $total_price,
            $customer_name, $customer_email, $customer_phone, $order_status, $payment_method, $payment_status
        );
    } else {
        // Fallback if payment columns don't exist (older table)
        $insert = "INSERT INTO orders (user_id, product_id, product_name, quantity, total_price, 
                    customer_name, customer_email, customer_phone, status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "iisidssss", 
            $user_id, $product_id, $product_name, $quantity, $total_price,
            $customer_name, $customer_email, $customer_phone, $order_status
        );
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);
        if ($payment_method === 'bank_transfer') {
            $message = "✅ Order placed! Please transfer the total amount to the bank account shown. Your order will be processed after payment confirmation.";
        } else {
            $message = "✅ Order placed successfully! You will pay on delivery.";
        }
        $messageType = "success";
    } else {
        $message = "❌ Order failed: " . mysqli_error($conn);
        $messageType = "danger";
    }
    mysqli_stmt_close($stmt);
    
    // Show result page (no auto-redirect, user stays until they click button)
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
        <title>Order Status</title>
        <style>
            body { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; }
            .card { background: white; border-radius: 20px; padding: 30px; max-width: 500px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
            .btn-go-home { background: #ff6b00; color: white; border: none; padding: 12px 25px; border-radius: 30px; font-weight: bold; text-decoration: none; display: inline-block; margin-top: 20px; }
            .btn-go-home:hover { background: #e55a00; color: white; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='alert alert-$messageType' style='border-radius: 15px;'>
                <h4>" . ($messageType == 'success' ? '✅ Order Placed!' : '❌ Error') . "</h4>
                <p>$message</p>
            </div>
            " . ($messageType == 'success' && $payment_method == 'bank_transfer' ? "
            <div class='alert alert-info mt-3'>
                <strong>Bank Transfer Details</strong><br>
                Bank: First Bank of Nigeria<br>
                Account Name: Feane Restaurant Ltd<br>
                Account Number: 1234567890<br>
                Sort Code: 011234567<br>
                <small>Use Order ID #$order_id as reference.</small>
            </div>" : "") . "
            <a href='index.php' class='btn-go-home'>← Back to Homepage</a>
        </div>
    </body>
    </html>";
    exit();
}
?>