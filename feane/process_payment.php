<?php
// process_payment.php - Initialize Paystack payment using session data
require_once 'config/database.php';
require_once 'includes/auth.php';

// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retrieve pending order from session
if (!isset($_SESSION['pending_order'])) {
    die("No pending order found. Please try again.");
}

$orderData = $_SESSION['pending_order'];
$amount = $orderData['total_price'] * 100; // Paystack uses kobo (multiply by 100)
$email = $orderData['customer_email'];

// Generate a unique reference for this transaction
$reference = 'FEANE_' . time() . '_' . rand(1000, 9999);

$callback_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_payment.php";

// ==== REPLACE WITH YOUR PAYSTACK SECRET KEY (TEST MODE) ====
$secret_key = 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; // Get from dashboard.paystack.co

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $amount,
        'email' => $email,
        'reference' => $reference,
        'callback_url' => $callback_url,
        'metadata' => json_encode(['order_data' => $orderData])
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json",
    ],
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("CURL Error: " . $err);
}

$result = json_decode($response, true);
if ($result['status']) {
    // Save reference in session to verify later
    $_SESSION['paystack_ref'] = $result['data']['reference'];
    header("Location: " . $result['data']['authorization_url']);
    exit();
} else {
    die("Paystack error: " . $result['message']);
}
?>