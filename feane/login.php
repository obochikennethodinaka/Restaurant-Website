<?php
// ==========================================
// FILE: login.php
// Admins → dashboard.php, Regular users → user_dashboard.php
// ==========================================
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, firstName, lastName, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: user_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
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
    <title>Login | Feane Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255,255,255,0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .login-card:hover { transform: translateY(-5px); }
        .card-header {
            background: linear-gradient(135deg, #ff6b00, #e55a00);
            padding: 50px 30px;
            text-align: center;
            color: white;
        }
        .card-header h1 { font-size: 2.8rem; font-weight: 700; margin-bottom: 10px; }
        .card-header p { opacity: 0.9; }
        .card-body { padding: 45px 40px; }
        .input-group-custom {
            position: relative;
            margin-bottom: 30px;
        }
        .input-group-custom i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff6b00;
            font-size: 1.2rem;
            z-index: 2;
        }
        .input-group-custom input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
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
        .btn-login {
            background: linear-gradient(135deg, #ff6b00, #e55a00);
            border: none;
            padding: 15px;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255,107,0,0.3);
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        .register-link a {
            color: #ff6b00;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover { text-decoration: underline; }
        .alert-custom {
            border-radius: 16px;
            border: none;
            padding: 12px 20px;
            margin-bottom: 30px;
        }
        .forgot-link {
            text-align: right;
            margin-top: -20px;
            margin-bottom: 20px;
        }
        .forgot-link a {
            color: #888;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .forgot-link a:hover { color: #ff6b00; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <h1>Welcome Back</h1>
            <p>Login to access your account</p>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="input-group-custom">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="input-group-custom">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="forgot-link">
                    <a href="#"><i class="fas fa-question-circle me-1"></i>Forgot password?</a>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                <div class="register-link">
                    Don't have an account? <a href="register.php">Create one now</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>