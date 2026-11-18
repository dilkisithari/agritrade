
# 🌱 AgriTrade

AgriTrade is a PHP and MySQL web platform for buying and selling agricultural products such as vegetables, fertilizers, seeds and insecticides. It connects suppliers, buyers, logistics partners and administrators in one system.

## Features

**Buyer**
- Browse and search products
- Shopping cart and checkout
- Upload payment slips
- Track orders and manage profile

**Supplier**
- Add, edit and delete products
- Approve orders and payments
- View orders and payments

**Logistics**
- View available delivery orders
- Accept orders and update delivery status
- Delivery history

**Admin**
- Manage users, products and orders
- Manage market prices
- Reports and platform settings

## Tech Stack

- PHP 8
- MySQL / MariaDB
- HTML, CSS, JavaScript
- XAMPP (Apache + MySQL)

## Installation

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Clone or download this repository into `C:\xampp\htdocs\agritrade`.
3. Open `http://localhost/agritrade/setup-database.php` and click **Import database now**.
   This imports `docs/agritrade.sql`.
4. Open `http://localhost/agritrade/`.

Default admin login: `admin` / `admin123` (change it after the first login).

## Database Configuration

Edit `config/database.php` if your MySQL uses different credentials. The app tries port 3307 first, then 3306.

## Project Structure

```
agritrade/
├── admin/        Admin pages
├── auth/         Login, register, logout
├── buyer/        Buyer pages
├── supplier/     Supplier pages
├── logistics/    Logistics pages
├── includes/     Shared header and footer
├── config/       Database connection
├── assets/       CSS, JS and images
├── docs/         Database SQL file
└── uploads/      Product images and payment slips
```

## User Roles

| Role | Purpose |
|------|---------|
| Admin | Manages the whole platform |
| Supplier | Sells products and approves orders |
| Buyer | Orders products and uploads payment |
| Logistics | Delivers orders |

## License

This project is for educational purposes.
