# Database-Online-Pharmacy-Ordering-System-PHP-MySQL
# 💊 Online Pharmacy Ordering System (PHP + MySQL)

A web-based **online pharmacy ordering platform** built with **PHP** and **MySQL**, supporting both **customer ordering** and **pharmacy-side prescription approval & inventory management**.

This system simulates a real-world pharmacy workflow including:

- Prescription-required medicine handling  
- Inventory control  
- Order lifecycle management  
- Role-based login (User / Pharmacy)

---

## 📌 Features

### 👤 User Functions
- User registration & login
- Browse medicines by pharmacy
- Search products with pagination
- Add/remove items in shopping cart
- Checkout and place orders
- Upload prescriptions for restricted medicines
- View order history
- Confirm delivery completion

### 🏥 Pharmacy Functions
- Pharmacy login & dashboard
- Add and manage medicine products
- Maintain inventory stock
- View customer orders
- Approving or rejecting prescriptions

### ⚙️ System Logic
- Prescription validation before checkout
- Stock consistency enforced via MySQL triggers
- Inventory automatically reduced after order update
- Secure authentication with bcrypt password hashing

---

## 🧱 Tech Stack

| Layer       | Technology |
|------------|------------|
| Backend     | PHP (PDO) |
| Database    | MySQL |
| Frontend    | HTML + CSS |
| Auth        | Sessions + bcrypt |
| Uploads     | PHP File Upload |
| DB Logic    | SQL Triggers |

---

## 📂 Project Structure

```bash
php/
├── connectdb.php                 # Database connection
│
├── login.php                     # Login page (user & pharmacy)
├── register.php                  # Registration page
├── logincheck_combine.php        # Login validation
├── new_register.php              # Registration logic
├── logout.php                    # Logout
│
├── user_home.php                 # User dashboard
├── menu.php                      # Product browsing + search
├── add_cart.php                  # Add items to cart
├── cart.php                      # Shopping cart display
├── remove_cart_item.php          # Remove cart items
├── checkout.php                  # Checkout processing
├── confirm_order.php             # Confirm delivery
├── my_order.php                  # User order history
│
├── submit_prescription.php       # Upload prescription file
├── pending_prescription.php      # Pharmacy prescription review
├── approve_prescription.php      # Approve/reject logic
│
├── pharmacy_home.php             # Pharmacy dashboard
├── pharmacy_products.php         # Inventory/product management
├── pharmacy_order.php            # Pharmacy order management
│
├── database_structure.sql        # Database schema
├── database_data.sql             # Sample data (⚠ sensitive)
│
├── trg_check_inventory_before_order_update.sql
├── trg_reduce_inventory_after_order_update.sql
│
├── uploads/                      # Uploaded prescription images
├── qiao_logo.svg                 # Logo asset
└── back_picture.png              # Background image
