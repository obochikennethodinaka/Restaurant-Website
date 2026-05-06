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
