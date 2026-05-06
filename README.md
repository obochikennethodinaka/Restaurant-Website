Here is a complete `README.md` file you can copy and paste directly into your GitHub repository. It assumes your database is already set up and configured, so it focuses on project overview, features, installation, usage, and credits.

---

```markdown
# 🍔 Feane Restaurant Management System

A full-featured restaurant web application built with PHP, MySQL, Bootstrap, and JavaScript.  
It allows customers to browse the menu, place orders (Bank Transfer / Cash on Delivery), track order & payment status, leave reviews, and contact the restaurant via WhatsApp.  
The admin dashboard provides complete control over products, orders, users, comments, and payment confirmation.

## ✨ Key Features

### For Customers (Public)
- **Age verification** before login (alert + prompt)
- **User registration & login** with validation
- **Dynamic menu** – products loaded from the database
- **Order placement** with two payment methods:
  - **Bank Transfer** – account details shown; payment confirmed manually by admin.
  - **Cash on Delivery** – pay when order arrives.
- **User dashboard**:
  - View order history with delivery status (`Pending`, `Processing`, `Out for Delivery`, `Delivered`, `Cancelled`)
  - View payment status (`Pending` / `Paid`)
  - Edit profile (name, email, phone, password, profile picture)
- **Leave reviews** – star rating & comment (only logged‑in users can post)
- **WhatsApp chat modal** – customers can send messages directly from the website (opens WhatsApp with pre‑filled info)
- **Smooth scroll‑up button** – appears when scrolling down

### For Admin
- **Admin dashboard** (role‑based access)
- **Product management** – add, edit, delete food items (with image upload)
- **User management** – view all users, promote to admin, delete users
- **Order management**:
  - See all customer orders in a well‑arranged table
  - Change **order status** (delivery progress) – dropdown updates instantly via AJAX
  - Change **payment status** (`Pending` → `Paid`) – also AJAX, no page reload
  - Delete orders
- **Comment management** – delete inappropriate customer reviews
- **Real‑time updates** – status changes are saved immediately and shown to customers

## 🛠 Tech Stack

- **Backend:** PHP 7.4+ (MySQLi, procedural style)
- **Frontend:** HTML5, CSS3, Bootstrap 5, Font Awesome 6
- **Database:** MySQL
- **JavaScript:** Vanilla JS (AJAX, DOM manipulation)
- **No external payment APIs** – bank details are displayed and payments are confirmed manually by admin

## 📁 Project Structure

```
feane/
├── admin/
│   └── dashboard.php          # Admin dashboard (products, orders, users, comments)
├── config/
│   └── database.php           # Database connection settings
├── includes/
│   └── auth.php               # Session & authentication functions
├── images/                    # Product images & user profile pictures
│   └── users/                 # User profile pictures
├── index.php                  # Restaurant homepage (menu, order modal, reviews, WhatsApp chat)
├── login.php                  # Login page with age verification
├── register.php               # Registration page
├── user_dashboard.php         # Customer dashboard (orders, profile)
├── place_order.php            # Handles order placement (Bank Transfer / COD)
├── post_comment.php           # Handles comment submission
├── update_order_status.php    # AJAX endpoint for order status updates (admin)
├── logout.php                 # Logout script
└── README.md                  # This file
```

## 🚀 Installation Guide

### 1. Prerequisites
- A local or live server with **PHP ≥7.4** and **MySQL** (e.g., XAMPP, WAMP, LAMP)
- Git (optional, for cloning)

### 2. Clone or download the project
```bash
git clone https://github.com/yourusername/feane-restaurant.git
```
Place the folder in your web server root (e.g., `htdocs/` for XAMPP).

### 3. Database setup
- You have already created the database and tables – ensure they match the expected schema (users, product, orders, comments).
- Update the database credentials in `config/database.php`:
  ```php
  $db_host = 'localhost';
  $db_user = 'root';
  $db_pass = '';
  $db_name = 'restaurant_db';
  ```

### 4. Create required folders (if not exists)
- `images/` – for product images
- `images/users/` – for user profile pictures  
Make sure both folders are writable.

### 5. (Optional) Configure WhatsApp number
In `index.php`, find the JavaScript section with `phoneNumber` and replace `2348012345678` with your actual WhatsApp number (country code without `+`).

### 6. Start your server
- Apache & MySQL must be running.
- Access the application at:  
  `http://localhost/feane/` (or your custom folder name)

## 🔐 Default Admin Login (if you inserted the admin user)
| Role  | Email               | Password  |
|-------|---------------------|-----------|
| Admin | admin@feane.com     | admin123  |

> You can change the password after login via the admin dashboard (edit user) or directly in the database.

## 📸 Usage Examples

### Customer flow
1. Register or log in.
2. Browse the menu and click **Order Now** on any product.
3. Choose payment method:
   - **Bank Transfer** – see account details, place order → order saved with `payment_status = pending`. Admin will confirm payment later.
   - **Cash on Delivery** – order placed immediately.
4. Go to **My Dashboard** → view orders, track delivery status, and see payment status.
5. Edit your profile (upload picture, change password).
6. Scroll down to the **Customer Reviews** section to leave a rating and comment.

### Admin flow
1. Log in with admin credentials.
2. **Dashboard** shows statistics (total users, products, orders).
3. **Orders** section:
   - Change **order status** (delivery progress) using the dropdown – updates instantly.
   - Change **payment status** (Pending → Paid) – also real‑time.
   - Delete orders if needed.
4. **Food Items** – add new products or edit/delete existing ones.
5. **Users** – promote regular users to admin, delete users.
6. **Comments** – delete any inappropriate review.

## 🧪 Troubleshooting

- **Blank page or error** – enable PHP error reporting by adding at the top of the problematic file:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- **Image upload fails** – ensure the `images/` folder exists and has write permissions.
- **WhatsApp link not working** – double‑check the phone number format (only digits, country code first, no `+`).
- **Session start warning** – all files already check `session_status()` before calling `session_start()`.

## 🤝 Contributing
Contributions, issues, and feature requests are welcome!  
Feel free to check the [issues page](https://github.com/yourusername/feane-restaurant/issues).

## 📄 License
This project is open‑source and available under the [MIT License](LICENSE).
