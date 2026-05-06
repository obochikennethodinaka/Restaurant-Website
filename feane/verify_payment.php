<?php
// verify_payment.php - Confirm Paystack payment and save order
require_once 'config/database.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reference = $_GET['reference'] ?? '';
if (!$reference) {
    die("No payment reference found.");
}

// ==== REPLACE WITH YOUR PAYSTACK SECRET KEY (SAME AS ABOVE) ====
$secret_key = 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json",
    ],
));

$response = curl_exec($curl);
curl_close($curl);
$result = json_decode($response, true);

if ($result['status'] && $result['data']['status'] == 'success') {
    // Payment successful – retrieve pending order from session
    $orderData = $_SESSION['pending_order'] ?? null;
    if (!$orderData) {
        die("Order data missing. Please contact support.");
    }

    $user_id = isLoggedIn() ? $_SESSION['user_id'] : NULL;
    $status = 'pending';
    $payment_status = 'paid';
    $payment_method = 'paystack';
    $transaction_ref = $reference;

    $insert = "INSERT INTO orders (user_id, product_id, product_name, quantity, total_price, 
                customer_name, customer_email, customer_phone, status, payment_method, payment_status, transaction_ref)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, "iisidsssssss", 
        $user_id, 
        $orderData['product_id'], 
        $orderData['product_name'], 
        $orderData['quantity'], 
        $orderData['total_price'],
        $orderData['customer_name'],
        $orderData['customer_email'],
        $orderData['customer_phone'],
        $status,
        $payment_method,
        $payment_status,
        $transaction_ref
    );

    if (mysqli_stmt_execute($stmt)) {
        unset($_SESSION['pending_order']);
        unset($_SESSION['paystack_ref']);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
            <meta http-equiv='refresh' content='3;url=index.php'>
            <title>Payment Successful</title>
        </head>
        <body class='d-flex justify-content-center align-items-center vh-100'>
            <div class='container text-center'>
                <div class='alert alert-success'>
                    <h4>✅ Payment successful!</h4>
                    <p>Your order has been placed. You will be redirected to the homepage.</p>
                </div>
            </div>
        </body>
        </html>";
    } else {
        die("Database error: " . mysqli_error($conn));
    }
} else {
    // Payment failed
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
        <meta http-equiv='refresh' content='5;url=index.php'>
        <title>Payment Failed</title>
    </head>
    <body class='d-flex justify-content-center align-items-center vh-100'>
        <div class='container text-center'>
            <div class='alert alert-danger'>
                <h4>❌ Payment failed</h4>
                <p>Please try again or choose Cash on Delivery.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>