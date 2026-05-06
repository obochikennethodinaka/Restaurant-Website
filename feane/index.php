<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'includes/auth.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$products = [];
$tableExists = mysqli_query($conn, "SHOW TABLES LIKE 'product'");
if (mysqli_num_rows($tableExists) > 0) {
    $productsQuery = mysqli_query($conn, "SELECT * FROM product ORDER BY id ASC");
    if ($productsQuery) {
        while ($row = mysqli_fetch_assoc($productsQuery)) {
            $products[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Feane | Fast Food Restaurant</title>
    <style>
        body { overflow-x: hidden; background-color: #fff; }
        .bg-dark-custom { background-color: #1f1f1f; }
        .text-orange { color: #ff6b00; }
        .btn-orange { background-color: #ff6b00; color: white; border-radius: 30px; padding: 10px 25px; font-weight: bold; border: none; transition: 0.2s; }
        .btn-orange:hover { background-color: #e55a00; color: white; }
        .hero-section { background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1600') center/cover no-repeat; min-height: 650px; display: flex; align-items: center; }
        .nav-link-feane { color: white !important; font-weight: 500; text-transform: uppercase; }
        .nav-link-feane:hover, .nav-link-feane.active { color: #ff6b00 !important; }
        .offer-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: 0.3s; text-align: center; padding: 20px; }
        .offer-card img { max-width: 180px; height: auto; }
        .menu-filter { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; list-style: none; padding: 0; }
        .menu-filter li { background: #f0f0f0; padding: 8px 25px; border-radius: 40px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .menu-filter li.active, .menu-filter li:hover { background: #ff6b00; color: white; }
        .product-card { background: #f9f9f9; border-radius: 20px; padding: 20px; text-align: center; height: 100%; transition: 0.2s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .product-card img { max-width: 150px; margin-bottom: 15px; border-radius: 15px; }
        .product-price { font-size: 1.3rem; font-weight: bold; color: #ff6b00; margin: 10px 0; }
        .testimonial-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; flex-wrap: wrap; align-items: center; gap: 20px; }
        .testimonial-card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
        .footer { background: #111; color: #ccc; padding: 50px 0 30px; }
        .footer h1, .footer h5 { color: #ff6b00; font-size: 1.8rem; }
        .btn-book { background: #ff6b00; color: white; border-radius: 40px; padding: 12px 30px; font-weight: bold; border: none; }
        .btn-book:hover { background: #e55a00; }
        .map-img { width: 100%; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .user-welcome { color: white; background: #ff6b00; padding: 5px 15px; border-radius: 30px; margin-left: 10px; font-size: 14px; }
        .btn-primary-custom { background: #ff6b00; border: none; padding: 10px 20px; border-radius: 30px; color: white; font-weight: bold; }
        .btn-primary-custom:hover { background: #e55a00; }
        .bank-details {
            background: #f8f9fa;
            border-left: 4px solid #ff6b00;
            padding: 12px;
            margin-top: 10px;
            border-radius: 12px;
            display: none;
        }
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #25D366;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        .whatsapp-float:hover {
            background-color: #20b859;
            transform: scale(1.05);
        }
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background-color: #ff6b00;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            border: none;
        }
        .scroll-top-btn.show {
            opacity: 1;
            visibility: visible;
        }
        .scroll-top-btn:hover {
            background-color: #e55a00;
            transform: scale(1.05);
        }
        .chat-modal .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }
        .chat-modal .modal-header {
            background: #075E54;
            color: white;
            border-bottom: none;
        }
        .chat-modal .modal-header .btn-close {
            filter: invert(1);
        }
        .chat-modal textarea {
            border-radius: 15px;
            resize: none;
        }
        @media (max-width: 768px) {
            .whatsapp-float {
                width: 50px;
                height: 50px;
                font-size: 26px;
                bottom: 20px;
                right: 20px;
            }
            .scroll-top-btn {
                width: 45px;
                height: 45px;
                font-size: 20px;
                bottom: 20px;
                left: 20px;
            }
            .hero-section h1 { font-size: 2rem; }
            .hero-section p { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-dark-custom py-3">
    <div class="container">
        <a class="navbar-brand text-white fw-bold fs-2" href="#">feane</a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-3">
                <li class="nav-item"><a class="nav-link nav-link-feane active" href="#">home</a></li>
                <li class="nav-item"><a class="nav-link nav-link-feane" href="#menu">menu</a></li>
                <li class="nav-item"><a class="nav-link nav-link-feane" href="#about">about</a></li>
                <li class="nav-item"><a class="nav-link nav-link-feane" href="#book">book-table</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <span class="user-welcome">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    </span>
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                        <a href="dashboard.php" class="btn-orange text-decoration-none">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="user_dashboard.php" class="btn-orange text-decoration-none">My Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-orange text-decoration-none">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-orange text-decoration-none">Login</a>
                    <a href="register.php" class="btn-orange text-decoration-none">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <h1 class="text-orange display-3 fw-bold">fast food restaurant</h1>
                <p class="text-white mt-3 fs-5">Delicious meals prepared with fresh ingredients, served with love.<br> Experience the best burgers, pizzas, and more at Feane.</p>
                <button class="btn-orange mt-3">order now</button>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="offer-card">
                <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?w=200" alt="tasty thursday">
                <div class="mt-3">
                    <p class="fw-bold fs-3 mb-1">tasty thursday</p>
                    <p class="fs-2 text-orange">15% <small class="fs-6">off</small></p>
                    <button class="btn-orange">order now</button>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="offer-card">
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=200" alt="pizza days">
                <div class="mt-3">
                    <p class="fw-bold fs-3 mb-1">pizza days</p>
                    <p class="fs-2 text-orange">20% <small class="fs-6">off</small></p>
                    <button class="btn-orange">order now</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container text-center my-5" id="menu">
    <h3 class="display-5 fw-bold">our menu</h3>
    <ul class="menu-filter mt-4" id="filter-list">
        <li data-category="all" class="active">all</li>
        <li data-category="burger">burger</li>
        <li data-category="pizza">pizza</li>
        <li data-category="pasta">pasta</li>
        <li data-category="fries">fries</li>
    </ul>
</div>

<div class="container" id="menu-container">
    <div class="row g-4" id="product-grid">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center">
                <p class="text-muted">No food items available yet. Please check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4 product-item" data-category="<?= htmlspecialchars($product['category'] ?? 'burger') ?>">
                <div class="product-card">
                    <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['header']) ?>" onerror="this.src='https://via.placeholder.com/150'">
                    <h4><?= htmlspecialchars($product['header']) ?></h4>
                    <p><?= htmlspecialchars($product['content']) ?></p>
                    <div class="product-price">₦<?= number_format($product['price'], 2) ?></div>
                    <button class="btn-orange order-btn" 
                        data-id="<?= $product['id'] ?>" 
                        data-name="<?= htmlspecialchars($product['header']) ?>" 
                        data-price="<?= $product['price'] ?>">
                        Order Now
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="text-center mt-5">
        <button class="btn-orange px-4 py-2">View More</button>
    </div>
</div>

<div class="bg-dark-custom text-white py-5 my-5" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1552566626-52f8b828add9?w=500" alt="about feane" class="img-fluid rounded-4">
            </div>
            <div class="col-md-6">
                <h2 class="text-orange display-4 fw-bold">We Are Feane</h2>
                <p class="mt-3 fs-5">We serve the finest quality fast food made from fresh ingredients.<br>
                   Our passion for great taste and customer satisfaction drives everything we do.<br>
                   Join us for an unforgettable dining experience.</p>
                <button class="btn-orange mt-2">Read More</button>
            </div>
        </div>
    </div>
</div>

<div class="container my-5" id="book">
    <div class="text-center mb-5">
        <h3 class="display-5 fw-bold">Book A Table</h3>
    </div>
    <div class="row g-5 align-items-start">
        <div class="col-lg-6">
            <form>
                <div class="mb-4">
                    <input type="text" class="form-control form-control-lg" placeholder="Your name.." style="border-radius: 15px;">
                </div>
                <div class="mb-4">
                    <input type="tel" class="form-control form-control-lg" placeholder="Phone Number.." style="border-radius: 15px;">
                </div>
                <div class="mb-4">
                    <input type="email" class="form-control form-control-lg" placeholder="Your Email.." style="border-radius: 15px;">
                </div>
                <div class="mb-4">
                    <select class="form-select form-control-lg" style="border-radius: 15px;">
                        <option>How Many Persons</option>
                        <option>1 Person</option>
                        <option>2 Persons</option>
                        <option>3 Persons</option>
                        <option>4+ Persons</option>
                    </select>
                </div>
                <div class="mb-4">
                    <input type="date" class="form-control form-control-lg" style="border-radius: 15px;">
                </div>
                <button type="submit" class="btn-book w-100">Try It Yourself</button>
            </form>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1590846406792-0adc7f938f1d?w=600" alt="Location Map" class="map-img">
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="text-center mb-5">
        <h3 class="display-5 fw-bold">What Says Our Customers</h3>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="testimonial-card">
                <div class="flex-grow-1">
                    <p>"Amazing food and great service! The burgers are absolutely delicious. Highly recommend!"</p>
                    <h5 class="fw-bold mt-2">John Doe</h5>
                    <p class="text-muted">Regular Customer</p>
                </div>
                <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="client">
            </div>
        </div>
        <div class="col-md-6">
            <div class="testimonial-card">
                <div class="flex-grow-1">
                    <p>"Best pizza in town! The atmosphere is cozy and the staff is super friendly. Will come again."</p>
                    <h5 class="fw-bold mt-2">Jane Smith</h5>
                    <p class="text-muted">Food Blogger</p>
                </div>
                <img src="https://randomuser.me/api/portraits/women/1.jpg" alt="client">
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row gy-5">
            <div class="col-md-4">
                <h1>Contact Us</h1>
                <p><i class="fas fa-map-marker-alt me-2"></i>123 Food Street, Lagos<br>
                <i class="fas fa-phone me-2"></i>+01 1234567890<br>
                <i class="fas fa-envelope me-2"></i>hello@feane.com</p>
            </div>
            <div class="col-md-4">
                <h1>Feane</h1>
                <p>Your favorite fast food destination. Quality ingredients, amazing taste, and fast service.</p>
                <p class="mt-4">© 2025 Feane Restaurant. All Rights Reserved</p>
            </div>
            <div class="col-md-4">
                <h1>Opening Hours</h1>
                <p><i class="fas fa-calendar-day me-2"></i>Everyday</p>
                <p><i class="fas fa-clock me-2"></i>10:00 Am - 10:00 Pm</p>
            </div>
        </div>
    </div>
</footer>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Place Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="place_order.php" method="POST" id="orderForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="order_product_id">
                    <input type="hidden" name="product_name" id="order_product_name">
                    <input type="hidden" name="total_price" id="order_total_price">
                    
                    <div class="mb-3">
                        <label class="form-label">Food Item</label>
                        <input type="text" class="form-control" id="order_product_display" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₦)</label>
                        <input type="text" class="form-control" id="order_price_display" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="order_quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control" value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="customer_phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university text-primary"></i> Bank Transfer (Online Payment)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cod">
                                <label class="form-check-label" for="cash_on_delivery">
                                    <i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="bankDetailsBox" class="bank-details">
                        <strong><i class="fas fa-info-circle"></i> Bank Transfer Details</strong><br>
                        After placing your order, please transfer the exact amount to:<br><br>
                        <strong>Bank Name:</strong> First Bank of Nigeria<br>
                        <strong>Account Name:</strong> Feane Restaurant Ltd<br>
                        <strong>Account Number:</strong> 1234567890<br>
                        <strong>Sort Code:</strong> 011234567<br><br>
                        <span class="text-muted small">Use your Order ID as payment reference. Send your payment proof via WhatsApp to 08012345678.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WhatsApp Chat Modal -->
<button class="whatsapp-float" data-bs-toggle="modal" data-bs-target="#whatsappChatModal">
    <i class="fab fa-whatsapp"></i>
</button>

<div class="modal fade chat-modal" id="whatsappChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Chat with us on WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="whatsappForm">
                    <div class="mb-3">
                        <label for="whatsappName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="whatsappName" placeholder="e.g., John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label for="whatsappMessage" class="form-label">Your Message</label>
                        <textarea class="form-control" id="whatsappMessage" rows="4" placeholder="Ask a question, make a complaint, or give feedback..." required></textarea>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> Your message will be sent via WhatsApp. A new tab will open.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="sendWhatsappBtn">
                    <i class="fab fa-whatsapp"></i> Send Message
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scroll Up Button -->
<button class="scroll-top-btn" id="scrollTopBtn" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Category filter
    const filterItems = document.querySelectorAll('#filter-list li');
    const productItems = document.querySelectorAll('.product-item');
    if (filterItems.length && productItems.length) {
        filterItems.forEach(filter => {
            filter.addEventListener('click', function() {
                filterItems.forEach(f => f.classList.remove('active'));
                this.classList.add('active');
                const category = this.getAttribute('data-category');
                productItems.forEach(item => {
                    if (category === 'all' || item.getAttribute('data-category') === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // Order modal: populate product data
    document.querySelectorAll('.order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const productPrice = this.dataset.price;
            
            document.getElementById('order_product_id').value = productId;
            document.getElementById('order_product_name').value = productName;
            document.getElementById('order_product_display').value = productName;
            document.getElementById('order_price_display').value = '₦' + parseFloat(productPrice).toLocaleString();
            document.getElementById('order_total_price').value = productPrice;
            
            const qtyInput = document.getElementById('order_quantity');
            const updateTotal = () => {
                const newTotal = parseFloat(productPrice) * parseInt(qtyInput.value);
                document.getElementById('order_total_price').value = newTotal.toFixed(2);
            };
            qtyInput.removeEventListener('input', updateTotal);
            qtyInput.addEventListener('input', updateTotal);
            
            new bootstrap.Modal(document.getElementById('orderModal')).show();
        });
    });

    // Toggle bank details
    const bankTransferRadio = document.getElementById('bank_transfer');
    const codRadio = document.getElementById('cash_on_delivery');
    const bankDetailsDiv = document.getElementById('bankDetailsBox');
    function toggleBankDetails() {
        if (bankTransferRadio && bankTransferRadio.checked) {
            bankDetailsDiv.style.display = 'block';
        } else {
            bankDetailsDiv.style.display = 'none';
        }
    }
    if (bankTransferRadio && codRadio) {
        bankTransferRadio.addEventListener('change', toggleBankDetails);
        codRadio.addEventListener('change', toggleBankDetails);
        toggleBankDetails();
    }

    // WhatsApp send
    const sendBtn = document.getElementById('sendWhatsappBtn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            const name = document.getElementById('whatsappName').value.trim();
            let message = document.getElementById('whatsappMessage').value.trim();
            if (name === '') { alert('Please enter your name.'); return; }
            if (message === '') { alert('Please enter a message.'); return; }
            const fullMessage = `Name: ${name}\n\nMessage: ${message}`;
            const encodedMessage = encodeURIComponent(fullMessage);
            const phoneNumber = '2348012345678'; // Change to your WhatsApp number
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
            window.open(whatsappUrl, '_blank');
            document.getElementById('whatsappName').value = '';
            document.getElementById('whatsappMessage').value = '';
            const modal = bootstrap.Modal.getInstance(document.getElementById('whatsappChatModal'));
            if (modal) modal.hide();
        });
    }

    // Scroll up button
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
</script>
</body>
</html>