<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment']);
    $rating = intval($_POST['rating']);
    
    if (empty($comment)) {
        $_SESSION['comment_error'] = "Please write a comment.";
        header("Location: index.php#comment-section");
        exit();
    }
    
    // If logged in, use user info; otherwise use guest input (if you allow guests)
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_email = $_SESSION['user_email'];
    } else {
        // Optionally allow guests: you would need extra form fields
        $user_id = NULL;
        $user_name = mysqli_real_escape_string($conn, $_POST['guest_name'] ?? 'Guest');
        $user_email = mysqli_real_escape_string($conn, $_POST['guest_email'] ?? '');
    }
    
    $user_name = mysqli_real_escape_string($conn, $user_name);
    $user_email = mysqli_real_escape_string($conn, $user_email);
    $comment = mysqli_real_escape_string($conn, $comment);
    
    $insert = "INSERT INTO comments (user_id, user_name, user_email, comment, rating) 
               VALUES ('$user_id', '$user_name', '$user_email', '$comment', '$rating')";
    
    if (mysqli_query($conn, $insert)) {
        $_SESSION['comment_success'] = "Thank you for your review!";
    } else {
        $_SESSION['comment_error'] = "Failed to post comment: " . mysqli_error($conn);
    }
    
    header("Location: index.php#comment-section");
    exit();
}
?>