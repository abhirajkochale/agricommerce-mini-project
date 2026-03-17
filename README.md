# AgroConnect / AgriCommerce Mini Project

A simple agricultural marketplace portal built as a Web Programming Laboratory mini project.

The portal demonstrates:

- HTML, CSS, and a multi-page layout
- JavaScript DOM manipulation, events, arrays, and calculations
- Client-side form validation
- PHP + MySQL CRUD operations using XAMPP

## Project Structure

- Frontend portal pages
  - `index.html` – Home page
  - `product.html` – Products listing + checkout form with validation
  - `about-product.html` – Price comparison page with styled table
  - `registration-form.html` – Farmer registration demo form
  - `media.html` – Media gallery and embedded video
- Styles and scripts
  - `portal.css` – Main portal styling and dark mode
  - `script.js` – JavaScript interactions, theme toggle, cart, filters
  - `validation.js` – Client-side checkout form validation
- PHP CRUD (Experiment 5)
  - `db.php` – MySQL connection using mysqli
  - `index.php` – Crop orders CRUD interface (Create + Read)
  - `insert.php` – Insert new order
  - `edit.php` – Load existing order for editing
  - `update.php` – Update order
  - `delete.php` – Delete order
  - `style.css` – Styling used by the PHP CRUD pages

## Technology Stack

- HTML, CSS, JavaScript (no frameworks)
- PHP (procedural, mysqli)
- MySQL via XAMPP / phpMyAdmin

## Database Setup

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Open phpMyAdmin: `http://localhost/phpmyadmin`.
3. Create the database:

   ```sql
   CREATE DATABASE IF NOT EXISTS agroconnect;
   ```

4. Select the `agroconnect` database and create the `orders` table:

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

## PHP Database Connection

`db.php` connects to MySQL using mysqli:

```php
$host = "localhost";
$user = "root";
$password = ""; // default XAMPP root password
$database = "agroconnect";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
```

Update `$password` only if you have changed the MySQL root password.

## Running the Project on XAMPP

1. Copy the project folder into:

   ```
   C:\xampp\htdocs\agroconnect
   ```

2. Ensure Apache and MySQL are running in XAMPP.
3. (Optional but recommended) In Apache `httpd.conf`, make `index.html` the first entry in `DirectoryIndex` so the portal opens by default:

   ```apache
   DirectoryIndex index.html index.php ...
   ```

4. Open the portal in your browser:

   ```
   http://localhost/agroconnect
   ```

5. Use the **Manage Orders** navigation link to open the PHP CRUD interface (index.php), where you can:
   - Add crop orders
   - View all orders
   - Edit and delete existing orders

## Experiments Covered

- **Experiment 2 – CSS3 Web Portal**
  - Multi-page portal with shared header, footer, sidebar, and responsive layout
  - Styled crop cards, tables, and forms using a single external stylesheet
- **Experiment 3 – JavaScript Concepts**
  - DOM updates (welcome message, dynamic product cards)
  - Variables, arrays, operators, discount logic (if–else, switch)
  - Search/filter, theme toggle, event handling, cart counter
- **Experiment 4 – Form Validation**
  - Checkout form with real-time validation and error messages
  - Email, phone, strong password, confirm password, zip, card, expiry, CVV
  - Disabled submit button until the form is fully valid
- **Experiment 5 – PHP MySQL CRUD**
  - Create, Read, Update, Delete operations on `orders` table
  - Uses mysqli and a shared `db.php` connection

This structure keeps the project beginner-friendly while demonstrating all required concepts for the Web Programming Laboratory mini project.