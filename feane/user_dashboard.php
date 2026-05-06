<?php
// ==========================================
// FILE: user_dashboard.php
// Shows logged-in user their order history and status
// ==========================================
require_once 'config/database.php';
require_once 'includes/auth.php';

// Must be logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ---------- PROFILE UPDATE ----------
if (isset($_POST['update_profile'])) {
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $update_fields = "firstName='$firstName', lastName='$lastName', phone='$phone', email='$email'";
    
    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields .= ", password='$hashed'";
        } else {
            $error = "Passwords do not match.";
        }
    }

    // Profile image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $image = basename($_FILES['profile_image']['name']);
        $target = "images/users/" . $image;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        
        if (!is_dir("images/users")) {
            mkdir("images/users", 0777, true);
        }
        
        if (in_array($ext, $allowed) && move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
            $update_fields .= ", image='$image'";
        } else {
            $error = "Image upload failed. Allowed: JPG, PNG, GIF, WEBP.";
        }
    }

    if (empty($error)) {
        $update_query = "UPDATE users SET $update_fields WHERE id = $user_id";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email;
            $message = "Profile updated successfully!";
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
}

// ---------- GET USER DATA ----------
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// ---------- GET USER ORDERS ----------
$orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
$total_orders = mysqli_num_rows($orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="refresh" content="30">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Feane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b00;
            --primary-dark: #e55a00;
            --dark: #1a1a1a;
        }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: var(--dark);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: #aaa;
            padding: 12px 20px;
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
        }
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        .content-wrapper {
            padding: 30px;
        }
        .section-title {
            border-left: 4px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 25px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary);
            margin-bottom: 15px;
        }
        .avatar-initials {
            width: 120px;
            height: 120px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-processing { background: #17a2b8; color: white; }
        .status-out_for_delivery { background: #fd7e14; color: white; }
        .status-delivered { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .badge-payment-pending { background: #ffc107; color: #000; }
        .badge-payment-paid { background: #28a745; color: white; }
        @media (max-width: 768px) {
            .sidebar { min-height: auto; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-4">
                    <h3 class="text-white mb-4">Feane<span class="text-primary">User</span></h3>
                </div>
                <nav class="nav flex-column px-3">
                    <a class="nav-link active" href="#" data-section="overview">
                        <i class="fas fa-tachometer-alt"></i> Overview
                    </a>
                    <a class="nav-link" href="#" data-section="profile">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                    <a class="nav-link" href="#" data-section="orders">
                        <i class="fas fa-shopping-cart"></i> My Orders
                    </a>
                    <hr class="bg-secondary">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-0">
                <div class="content-wrapper">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <!-- Overview Section -->
                    <div id="overview-section">
                        <h4 class="section-title">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <?php if (!empty($user['image']) && file_exists("images/users/" . $user['image'])): ?>
                                            <img src="images/users/<?= $user['image'] ?>" class="profile-img" alt="Profile">
                                        <?php else: ?>
                                            <div class="avatar-initials mx-auto">
                                                <?= strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <h5><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                                        <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($user['phone']) ?></p>
                                        <a href="#" class="btn btn-primary-custom" data-section-trigger="profile">Edit Profile</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shopping-bag fa-3x text-primary mb-3"></i>
                                        <h3><?= $total_orders ?></h3>
                                        <p class="text-muted">Total Orders Placed</p>
                                        <a href="#" class="btn btn-primary-custom" data-section-trigger="orders">View Orders</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Section -->
                    <div id="profile-section" style="display: none;">
                        <h4 class="section-title">My Profile</h4>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="text-center mb-4">
                                        <?php if (!empty($user['image']) && file_exists("images/users/" . $user['image'])): ?>
                                            <img src="images/users/<?= $user['image'] ?>" class="profile-img" id="profilePreview">
                                        <?php else: ?>
                                            <div class="avatar-initials mx-auto" id="profilePreviewInitials">
                                                <?= strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)) ?>
                                            </div>
                                            <img src="" id="profilePreviewImg" class="profile-img" style="display:none;">
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <input type="file" name="profile_image" class="form-control w-50 mx-auto" accept="image/*" onchange="previewImage(this)">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>First Name</label>
                                            <input type="text" name="firstName" class="form-control" value="<?= htmlspecialchars($user['firstName']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Last Name</label>
                                            <input type="text" name="lastName" class="form-control" value="<?= htmlspecialchars($user['lastName']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Phone</label>
                                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>New Password (leave blank to keep current)</label>
                                            <input type="password" name="new_password" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control">
                                        </div>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary-custom">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Section -->
                    <div id="orders-section" style="display: none;">
                        <h4 class="section-title">My Orders</h4>
                        <?php if ($total_orders == 0): ?>
                            <div class="alert alert-info">You haven't placed any orders yet. <a href="index.php#menu">Order now!</a></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover bg-white">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Payment Method</th>
                                            <th>Payment Status</th>
                                            <th>Order Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $orders_query2 = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
                                        while ($order = mysqli_fetch_assoc($orders_query2)): 
                                        ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                                            <td><?= $order['quantity'] ?></td>
                                            <td>₦<?= number_format($order['total_price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= strtoupper($order['payment_method'] ?? 'COD') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($order['payment_status'] ?? 'pending') == 'paid' ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="order-status status-<?= $order['status'] ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= $order['order_date'] ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sections = ['overview', 'profile', 'orders'];
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        function showSection(sectionId) {
            sections.forEach(section => {
                const el = document.getElementById(`${section}-section`);
                if (el) el.style.display = section === sectionId ? 'block' : 'none';
            });
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('active');
                }
            });
        }
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const section = link.getAttribute('data-section');
                if (section) {
                    e.preventDefault();
                    showSection(section);
                }
            });
        });
        
        document.querySelectorAll('[data-section-trigger]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const section = btn.getAttribute('data-section-trigger');
                if (section) showSection(section);
            });
        });
        
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('profilePreviewImg');
                    const previewInitials = document.getElementById('profilePreviewInitials');
                    const existingImg = document.getElementById('profilePreview');
                    if (existingImg) existingImg.style.display = 'none';
                    if (previewInitials) previewInitials.style.display = 'none';
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'profile-img';
                        img.id = 'profilePreview';
                        input.parentNode.parentNode.querySelector('.text-center').appendChild(img);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        showSection('overview');
    </script>
    <style>
        .btn-primary-custom {
            background: #ff6b00;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            color: white;
            font-weight: bold;
        }
        .btn-primary-custom:hover {
            background: #e55a00;
        }
    </style>
</body>
</html>