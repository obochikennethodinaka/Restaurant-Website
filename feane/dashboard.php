<?php
// ==========================================
// FILE: dashboard.php (Admin Dashboard)
// Shows ALL orders, allows order status update, payment confirmation, and manage comments
// ==========================================
require_once 'config/database.php';
require_once 'includes/auth.php';

// Must be logged in AND have admin role
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$msg = '';

// ========== ADD NEW PRODUCT ==========
if (isset($_POST['save_product'])) {
    $header = mysqli_real_escape_string($conn, $_POST['header']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $footer = mysqli_real_escape_string($conn, $_POST['footer']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        $target = "images/" . $image;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $insert = "INSERT INTO product (image, header, title, content, footer, price, category) 
                       VALUES ('$image', '$header', '$title', '$content', '$footer', '$price', '$category')";
            if (mysqli_query($conn, $insert)) {
                $msg = "<div class='alert alert-success'>✅ Product added successfully!</div>";
            } else {
                $msg = "<div class='alert alert-danger'>DB Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Image upload failed.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Please select an image.</div>";
    }
}

// ========== UPDATE PRODUCT ==========
if (isset($_POST['update_product'])) {
    $id = intval($_POST['id']);
    $header = mysqli_real_escape_string($conn, $_POST['header']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $footer = mysqli_real_escape_string($conn, $_POST['footer']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        $target = "images/" . $image;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $update = "UPDATE product SET image='$image', header='$header', title='$title', 
                       content='$content', footer='$footer', price='$price', category='$category' 
                       WHERE id='$id'";
        } else {
            $msg = "<div class='alert alert-danger'>Image upload failed.</div>";
        }
    } else {
        $update = "UPDATE product SET header='$header', title='$title', content='$content', 
                   footer='$footer', price='$price', category='$category' WHERE id='$id'";
    }
    
    if (isset($update) && mysqli_query($conn, $update)) {
        $msg = "<div class='alert alert-success'>✅ Product updated!</div>";
    } elseif (!isset($update)) {
        // Already handled
    } else {
        $msg = "<div class='alert alert-danger'>Update failed.</div>";
    }
}

// ========== DELETE PRODUCT ==========
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    if (mysqli_query($conn, "DELETE FROM product WHERE id='$id'")) {
        $msg = "<div class='alert alert-success'>✅ Product deleted!</div>";
    }
}

// ========== DELETE USER ==========
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    if ($id != $_SESSION['user_id']) {
        if (mysqli_query($conn, "DELETE FROM users WHERE id='$id'")) {
            $msg = "<div class='alert alert-success'>✅ User deleted!</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ You cannot delete your own account.</div>";
    }
}

// ========== MAKE ADMIN ==========
if (isset($_GET['make_admin'])) {
    $id = intval($_GET['make_admin']);
    $updateRole = "UPDATE users SET role = 'admin' WHERE id = $id";
    if (mysqli_query($conn, $updateRole)) {
        $msg = "<div class='alert alert-success'>✅ User promoted to Admin!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update role.</div>";
    }
}

// ========== DELETE ORDER ==========
if (isset($_GET['delete_order'])) {
    $id = intval($_GET['delete_order']);
    if (mysqli_query($conn, "DELETE FROM orders WHERE id='$id'")) {
        $msg = "<div class='alert alert-success'>✅ Order deleted!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Delete failed.</div>";
    }
}

// ========== DELETE COMMENT ==========
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    if (mysqli_query($conn, "DELETE FROM comments WHERE id='$id'")) {
        $msg = "<div class='alert alert-success'>✅ Comment deleted!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Delete failed.</div>";
    }
}

// ========== UPDATE PAYMENT STATUS (AJAX) ==========
if (isset($_GET['update_payment'])) {
    // Only admin can do this
    if (!isAdmin()) {
        exit('unauthorized');
    }
    $order_id = intval($_GET['order_id']);
    $payment_status = mysqli_real_escape_string($conn, $_GET['status']);
    if (!in_array($payment_status, ['pending', 'paid'])) {
        exit('invalid');
    }
    $update = "UPDATE orders SET payment_status = '$payment_status' WHERE id = $order_id";
    if (mysqli_query($conn, $update)) {
        echo 'ok';
    } else {
        echo 'error';
    }
    exit();
}

// Get statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM product) AS total_products,
    (SELECT COUNT(*) FROM orders) AS total_orders
"));

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
$products = mysqli_query($conn, "SELECT * FROM product ORDER BY id ASC");
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id ASC");
$comments = mysqli_query($conn, "SELECT * FROM comments ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Feane</title>
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
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
        }
        .btn-primary-custom {
            background: var(--primary);
            border: none;
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark);
        }
        .table-responsive {
            border-radius: 16px;
            overflow-x: auto;
        }
        .content-wrapper {
            padding: 30px;
        }
        .section-title {
            border-left: 4px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 25px;
        }
        .order-status-badge {
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
        /* Fixed column widths for orders table */
        .orders-table th, .orders-table td {
            vertical-align: middle;
            white-space: nowrap;
        }
        .orders-table .customer-info {
            min-width: 180px;
            white-space: normal;
        }
        .orders-table .product-col {
            min-width: 150px;
        }
        .orders-table .status-col {
            min-width: 160px;
        }
        .orders-table .actions-col {
            min-width: 100px;
        }
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
                    <h3 class="text-white mb-4">Feane<span class="text-primary">Admin</span></h3>
                </div>
                <nav class="nav flex-column px-3">
                    <a class="nav-link active" href="#" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="#" data-section="users">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="#" data-section="products">
                        <i class="fas fa-hamburger"></i> Food Items
                    </a>
                    <a class="nav-link" href="#" data-section="add-product">
                        <i class="fas fa-plus-circle"></i> Add Food
                    </a>
                    <a class="nav-link" href="#" data-section="orders">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    <a class="nav-link" href="#" data-section="comments">
                        <i class="fas fa-comments"></i> Comments
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
                    <?= $msg ?>
                    
                    <!-- Dashboard Section -->
                    <div id="dashboard-section">
                        <h4 class="section-title">Dashboard Overview</h4>
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                    <h3><?= number_format($stats['total_users']) ?></h3>
                                    <p class="text-muted mb-0">Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-hamburger fa-3x text-success mb-3"></i>
                                    <h3><?= number_format($stats['total_products']) ?></h3>
                                    <p class="text-muted mb-0">Food Items</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-shopping-cart fa-3x text-warning mb-3"></i>
                                    <h3><?= number_format($stats['total_orders']) ?></h3>
                                    <p class="text-muted mb-0">Total Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Section -->
                    <div id="users-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="section-title mb-0">Registered Users</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover bg-white">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['role'] == 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                                <?= $user['role'] ?>
                                            </span>
                                        </td>
                                        <td><?= $user['date_created'] ?></td>
                                        <td>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <a href="?make_admin=<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning" 
                                                   onclick="return confirm('Make this user an admin?')">
                                                    <i class="fas fa-user-shield"></i> Make Admin
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete_user=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div id="products-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="section-title mb-0">Food Items</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover bg-white">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Header</th>
                                        <th>Title</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($products, 0);
                                    while ($product = mysqli_fetch_assoc($products)): 
                                    ?>
                                    <tr>
                                        <td><?= $product['id'] ?></td>
                                        <td><img src="images/<?= $product['image'] ?>" class="product-thumb" onerror="this.src='https://via.placeholder.com/50'"></td>
                                        <td><?= htmlspecialchars($product['header']) ?></td>
                                        <td><?= htmlspecialchars($product['title']) ?></td>
                                        <td><span class="badge bg-primary">₦<?= number_format($product['price'], 2) ?></span></td>
                                        <td><?= htmlspecialchars($product['category'] ?? 'General') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $product['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_product=<?= $product['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $product['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title">Edit Food Item</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                        <div class="mb-3">
                                                            <label>Image (leave empty to keep)</label>
                                                            <input type="file" name="image" class="form-control">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Header</label>
                                                            <input type="text" name="header" class="form-control" value="<?= htmlspecialchars($product['header']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Title</label>
                                                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Content</label>
                                                            <input type="text" name="content" class="form-control" value="<?= htmlspecialchars($product['content']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Footer</label>
                                                            <input type="text" name="footer" class="form-control" value="<?= htmlspecialchars($product['footer']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Price (₦)</label>
                                                            <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" step="0.01" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Category</label>
                                                            <select name="category" class="form-control">
                                                                <option value="burger" <?= $product['category'] == 'burger' ? 'selected' : '' ?>>Burger</option>
                                                                <option value="pizza" <?= $product['category'] == 'pizza' ? 'selected' : '' ?>>Pizza</option>
                                                                <option value="pasta" <?= $product['category'] == 'pasta' ? 'selected' : '' ?>>Pasta</option>
                                                                <option value="fries" <?= $product['category'] == 'fries' ? 'selected' : '' ?>>Fries</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_product" class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Product Section -->
                    <div id="add-product-section" style="display: none;">
                        <h4 class="section-title">Add New Food Item</h4>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Food Image</label>
                                            <input type="file" name="image" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Category</label>
                                            <select name="category" class="form-control" required>
                                                <option value="burger">Burger</option>
                                                <option value="pizza">Pizza</option>
                                                <option value="pasta">Pasta</option>
                                                <option value="fries">Fries</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Header / Name</label>
                                            <input type="text" name="header" class="form-control" placeholder="e.g., Deluxe Burger" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Title / Subtitle</label>
                                            <input type="text" name="title" class="form-control" placeholder="e.g., Grilled to perfection" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Description / Content</label>
                                            <textarea name="content" class="form-control" rows="3" placeholder="Food description..." required></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Footer / Extra Info</label>
                                            <input type="text" name="footer" class="form-control" placeholder="e.g., Served with fries">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Price (₦)</label>
                                            <input type="number" name="price" class="form-control" placeholder="e.g., 5000" step="0.01" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="save_product" class="btn btn-primary-custom px-4">
                                                <i class="fas fa-save me-2"></i>Add Food Item
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Section (ALL orders, clean and arranged) -->
                    <div id="orders-section" style="display: none;">
                        <h4 class="section-title">All Customer Orders</h4>
                        <?php if (mysqli_num_rows($orders) == 0): ?>
                            <div class="alert alert-info">No orders placed yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover bg-white orders-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th>Payment</th>
                                            <th>Pay Status</th>
                                            <th>Order Status</th>
                                            <th>Order Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        mysqli_data_seek($orders, 0);
                                        while ($order = mysqli_fetch_assoc($orders)): 
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold">#<?= $order['id'] ?></td>
                                            <td class="customer-info">
                                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small><br>
                                                <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                                            </td>
                                            <td class="product-col"><?= htmlspecialchars($order['product_name']) ?></td>
                                            <td class="text-center"><?= $order['quantity'] ?></td>
                                            <td class="text-nowrap">₦<?= number_format($order['total_price'], 2) ?></td>
                                            <td><span class="badge bg-secondary"><?= strtoupper($order['payment_method'] ?? 'COD') ?></span></td>
                                            <td class="payment-status-col">
                                                <select class="form-select form-select-sm payment-status-dropdown d-inline-block w-auto" data-id="<?= $order['id'] ?>">
                                                    <option value="pending" <?= ($order['payment_status'] ?? 'pending') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="paid" <?= ($order['payment_status'] ?? 'pending') == 'paid' ? 'selected' : '' ?>>Paid</option>
                                                </select>
                                                <span class="badge <?= ($order['payment_status'] ?? 'pending') == 'paid' ? 'bg-success' : 'bg-warning' ?> d-block mt-1">
                                                    <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                                </span>
                                            </td>
                                            <td class="status-col">
                                                <select class="form-select form-select-sm order-status-dropdown d-inline-block w-auto" data-id="<?= $order['id'] ?>">
                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                    <option value="out_for_delivery" <?= $order['status'] == 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <span class="order-status-badge status-<?= $order['status'] ?> d-block mt-1">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-nowrap"><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></td>
                                            <td class="actions-col">
                                                <a href="?delete_order=<?= $order['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Comments Section (Admin can delete comments) -->
                    <div id="comments-section" style="display: none;">
                        <h4 class="section-title">Manage Customer Reviews</h4>
                        <div class="table-responsive">
                            <table class="table table-hover bg-white">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Comment</th>
                                        <th>Rating</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($comments, 0);
                                    while ($com = mysqli_fetch_assoc($comments)): 
                                    ?>
                                    <tr>
                                        <td><?= $com['id'] ?></td>
                                        <td><?= htmlspecialchars($com['user_name']) ?><br><small><?= htmlspecialchars($com['user_email']) ?></small></td>
                                        <td><?= nl2br(htmlspecialchars($com['comment'])) ?></td>
                                        <td><?= $com['rating'] ?> ★</td>
                                        <td><?= $com['created_at'] ?></td>
                                        <td>
                                            <a href="?delete_comment=<?= $com['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this comment?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Section navigation
        const sections = ['dashboard', 'users', 'products', 'add-product', 'orders', 'comments'];
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
        
        showSection('dashboard');

        // Real-time order status update via AJAX (original)
        document.querySelectorAll('.order-status-dropdown').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.id;
                const newStatus = this.value;
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId + '&status=' + newStatus
                })
                .then(res => res.text())
                .then(data => {
                    if (data === 'ok') {
                        const row = this.closest('tr');
                        const badgeSpan = row.querySelector('.order-status-badge');
                        if (badgeSpan) {
                            let displayStatus = newStatus.replace(/_/g, ' ');
                            displayStatus = displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1);
                            badgeSpan.textContent = displayStatus;
                            badgeSpan.className = `order-status-badge status-${newStatus}`;
                        }
                        const tempMsg = document.createElement('small');
                        tempMsg.className = 'text-success ms-2';
                        tempMsg.innerText = '✓ Updated';
                        this.parentNode.appendChild(tempMsg);
                        setTimeout(() => tempMsg.remove(), 1500);
                    } else {
                        alert('Error updating order status: ' + data);
                    }
                })
                .catch(err => alert('Request failed: ' + err));
            });
        });

        // Payment status update via AJAX (new)
        document.querySelectorAll('.payment-status-dropdown').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.id;
                const newPaymentStatus = this.value;
                fetch('dashboard.php?update_payment=1&order_id=' + orderId + '&status=' + newPaymentStatus)
                .then(res => res.text())
                .then(data => {
                    if (data === 'ok') {
                        const row = this.closest('tr');
                        const badgeSpan = row.querySelector('.payment-status-col .badge');
                        if (badgeSpan) {
                            badgeSpan.textContent = newPaymentStatus === 'paid' ? 'Paid' : 'Pending';
                            badgeSpan.className = `badge ${newPaymentStatus === 'paid' ? 'bg-success' : 'bg-warning'} d-block mt-1`;
                        }
                        const tempMsg = document.createElement('small');
                        tempMsg.className = 'text-success ms-2';
                        tempMsg.innerText = '✓ Payment updated';
                        this.parentNode.appendChild(tempMsg);
                        setTimeout(() => tempMsg.remove(), 1500);
                    } else {
                        alert('Error updating payment status: ' + data);
                    }
                })
                .catch(err => alert('Request failed: ' + err));
            });
        });
    </script>
</body>
</html>