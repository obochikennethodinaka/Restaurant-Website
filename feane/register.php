<?php
// ==========================================
// FILE: register.php
// World-Class Registration Page
// ==========================================
require_once 'config/database.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validation
    if (strlen($firstName) < 2) {
        $errors[] = "First name must be at least 2 characters.";
    }
    if (strlen($lastName) < 2) {
        $errors[] = "Last name must be at least 2 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Phone number must be 10-15 digits.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $checkEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkEmail, "s", $email);
        mysqli_stmt_execute($checkEmail);
        mysqli_stmt_store_result($checkEmail);
        
        if (mysqli_stmt_num_rows($checkEmail) > 0) {
            $errors[] = "Email already registered. Please login.";
        }
        mysqli_stmt_close($checkEmail);
    }

    // Insert user if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role
        
        $stmt = mysqli_prepare($conn, "INSERT INTO users (firstName, lastName, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $firstName, $lastName, $email, $phone, $hashedPassword, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Registration successful! You can now login.";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Feane Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: rgba(255,255,255,0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .register-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, #ff6b00, #e55a00);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .card-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .card-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        .card-body {
            padding: 40px 35px;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 25px;
        }
        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff6b00;
            font-size: 1.1rem;
            z-index: 2;
        }
        .input-group-custom input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        .input-group-custom input:focus {
            border-color: #ff6b00;
            outline: none;
            box-shadow: 0 0 0 4px rgba(255,107,0,0.1);
            background: white;
        }
        .btn-register {
            background: linear-gradient(135deg, #ff6b00, #e55a00);
            border: none;
            padding: 14px;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255,107,0,0.3);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: #ff6b00;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .alert-custom {
            border-radius: 16px;
            border: none;
            padding: 12px 20px;
            margin-bottom: 25px;
        }
        .name-row {
            display: flex;
            gap: 15px;
        }
        .name-row .input-group-custom {
            flex: 1;
        }
        @media (max-width: 480px) {
            .card-body { padding: 30px 20px; }
            .name-row { flex-direction: column; gap: 0; }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="card-header">
            <h1>Join Us</h1>
            <p>Create your account to enjoy exclusive offers</p>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-custom">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-custom">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                    <div class="mt-2"><a href="login.php" class="fw-bold">Click here to login</a></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="name-row">
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" name="firstName" placeholder="First Name" value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>" required>
                    </div>
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" name="lastName" placeholder="Last Name" value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="input-group-custom">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="input-group-custom">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                </div>
                <div class="input-group-custom">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-group-custom">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirmPassword" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>