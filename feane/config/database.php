<?php
// ==========================================
// FILE: config/database.php
// Database configuration - adjust as needed
// ==========================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'restaurant_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset for proper encoding
mysqli_set_charset($conn, "utf8mb4");
?>