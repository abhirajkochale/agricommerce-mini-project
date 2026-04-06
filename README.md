# AgroConnect – AgriCommerce Portal

A web-based agricultural marketplace built as a college mini project. It lets farmers list crops and buyers browse/order them — all through a simple, role-based portal.

---

## What it does

- Farmers can log in, manage their crop listings, and track orders
- Buyers (users) can browse products, add to cart, checkout, and see their order history
- Admin gets a dashboard to manage all users and orders
- Dark mode toggle that actually works and remembers your preference

---

## Pages & Files

**Frontend**
- `index.html` – Landing page
- `product.html` – Product listing with search/filter
- `about-product.html` – Price comparison table
- `registration-form.html` – Demo registration form
- `media.html` – Gallery + video section

**Styles & Scripts**
- `portal.css` – Main stylesheet (includes dark mode styles)
- `script.js` – JS stuff: theme toggle, cart counter, filters, DOM updates
- `validation.js` – Checkout form validation logic

**PHP (CRUD + Auth)**
- `login.php` / `logout.php` / `register.php` – Auth flow
- `index.php` – Crop orders CRUD (create + read)
- `insert.php`, `edit.php`, `update.php`, `delete.php` – Basic CRUD ops
- `cart.php`, `add_to_cart.php`, `get_cart_count.php` – Cart system
- `checkout.php`, `process_checkout.php`, `order_success.php` – Order flow
- `my_orders.php` – User order history
- `admin_dashboard.php` – Admin: manage all orders + users
- `farmer_dashboard.php` – Farmer: view their listed crops + orders
- `user_dashboard.php` – Buyer: quick overview
- `update_order_status.php`, `update_all_orders_status.php` – Order status updates
- `db.php` – MySQL connection

**Includes**
- `includes/header.php` – Shared nav header
- `includes/footer.php` – Shared footer

---

## Tech Stack

- HTML, CSS, JavaScript (vanilla, no frameworks)
- PHP (procedural, mysqli)
- MySQL via XAMPP / phpMyAdmin

---

## Database Setup

1. Open XAMPP Control Panel and start **Apache** and **MySQL**
2. Go to `http://localhost/phpmyadmin`
3. Run this to get started:

```sql
CREATE DATABASE IF NOT EXISTS agroconnect;
```

4. Select the `agroconnect` database and create the orders table:

```sql
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  farmer_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  crop_name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL,
  quantity INT NOT NULL,
  price INT NOT NULL,
  location VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

> You'll also need `users` and `cart` tables for the auth + cart features. Check `db.php` or any attached SQL dump for the full schema.

---

## Running Locally

1. Drop the project folder into:
   ```
   C:\xampp\htdocs\agroconnect
   ```

2. Start Apache and MySQL from XAMPP

3. Set up the database (steps above)

4. Visit in your browser:
   ```
   http://localhost/agroconnect
   ```

5. Login or register, then explore the portal based on your role (admin / farmer / user)

> If you changed MySQL root password, update it in `db.php`.

---

## Lab Experiments Covered

| Experiment | What's covered |
|---|---|
| Exp 2 – CSS3 Web Portal | Multi-page layout, shared header/footer, crop cards, responsive design |
| Exp 3 – JavaScript | DOM manipulation, arrays, discount logic, search/filter, cart counter, dark mode toggle |
| Exp 4 – Form Validation | Real-time validation on checkout: email, phone, password strength, card details |
| Exp 5 – PHP MySQL CRUD | Full Create/Read/Update/Delete on orders table using mysqli |

---

Made for the Web Programming Lab mini project.
