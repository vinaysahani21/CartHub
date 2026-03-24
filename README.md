# 🛒 CartHub - Premium Multi-Vendor E-Commerce Platform

CartHub is a modern, fully responsive, multi-role e-commerce platform built with PHP and MySQL. It connects buyers with independent sellers, featuring a secure checkout process, an intuitive seller dashboard, and a powerful master admin control panel.

![CartHub Banner](https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1200&q=80)

## ✨ Key Features

### 🛍️ Customer Experience
* **Modern UI/UX:** Glassmorphism elements, smooth AOS scroll animations, and a mobile-first responsive design.
* **Smart Search:** AJAX-powered live search suggestions and multi-parameter filtering (price, category, sort).
* **Shopping Cart:** Real-time cart calculations including 5% tax and dynamic stock validation.
* **Secure Checkout:** Integrated **Razorpay** payment gateway for seamless UPI, Card, and NetBanking transactions.
* **Account Management:** Order tracking, profile editing, and secure password recovery via email.

### 🏪 Seller Dashboard
* **Store Management:** Add, edit, and manage product listings and inventory.
* **Financial Overview:** Track lifetime earnings, pending balances, and total sales.
* **Automated Payouts:** Request bank/UPI withdrawals with an automated **10% platform commission** deduction.
* **Real-time Metrics:** View recent payout history and withdrawal statuses.

### 🛡️ Master Admin Panel
* **Platform Intelligence:** Visual analytics dashboards using **Chart.js** to track user acquisition and platform revenue.
* **User Directory:** Manage all roles (Admin, Seller, Customer), suspend accounts, and export user data to **CSV**.
* **Global Inventory:** Monitor all seller products, track low-stock alerts, and hide/remove inappropriate listings.
* **Hero Slider CMS:** Upload and manage homepage promotional banners dynamically.
* **Financial Audits:** Track gross merchandise value (GMV) and net platform commission profits.

---

## 🛠️ Technology Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3
* **Backend:** PHP 8+ (Core/Vanilla)
* **Database:** MySQL (MariaDB)
* **Libraries & APIs:**
  * [Razorpay Checkout](https://razorpay.com/) (Payment Processing)
  * [Chart.js](https://www.chartjs.org/) (Data Visualization)
  * [AOS Library](https://michalsnik.github.io/aos/) (Scroll Animations)
* **Typography & Icons:** Plus Jakarta Sans, FontAwesome 6

---

## 🚀 Installation & Setup

Follow these steps to run the project locally on XAMPP/WAMP/MAMP or an Ubuntu LAMP server.

### 1. Clone the Repository
```bash
git clone [https://github.com/vinaysahani21/CartHub.git](https://github.com/vinaysahani21/CartHub.git)
cd carthub

3. Database Configuration
Open phpMyAdmin (http://localhost/phpmyadmin).

Create a new database named carthub.

Import the provided SQL file (e.g., database.sql if exported) into this database.

Navigate to config/db.php and update your credentials:

PHP
$host = "localhost";
$user = "root"; // your db username
$pass = "";     // your db password
$dbname = "carthub";


4. API Key Configuration
Razorpay (Payments):
Navigate to user/checkout.php and replace the test key with your own Razorpay Key ID.

JavaScript
var options = {
    "key": "YOUR_RAZORPAY_KEY_ID_HERE",
    // ...


Bash
sudo mkdir -p assets/uploads
sudo chown -R www-data:www-data assets/uploads/
sudo chmod -R 775 assets/uploads/

📂 Directory Structure
Plaintext
carthub/
├── admin/          # Master control panel & analytics
├── assets/         # Static images, CSS, JS, and user uploads
├── auth/           # Login, Register, Logout, and Password Reset
├── config/         # Database connection scripts
├── seller/         # Vendor dashboard and product management
├── user/           # Customer frontend, cart, and checkout
├── vendor/         # Composer dependencies (PHPMailer)
└── index.php       # Main landing page


# 