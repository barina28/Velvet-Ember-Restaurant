# Burgundy Table - Restaurant QR Ordering System
PHP 8.1+ / MySQL / HTML / CSS / Bootstrap 5 / JavaScript. Burgundy + black theme with 3D effects.

## Setup (XAMPP / WAMP / any PHP host)
1. Copy this folder into `htdocs/restaurant` (or your web root).
2. Open phpMyAdmin -> Import -> choose `database.sql` (it creates the `restaurant_db` database, tables and demo data).
3. Edit DB user/password in `includes/config.php` if yours differ from root / empty.
4. Open `http://localhost/restaurant/`.
5. Make sure `assets/img/picture/` is writable (needed for menu photo uploads from the admin panel).

Needs an internet connection in the browser (Bootstrap, fonts and the QR library load from CDNs).

## Demo logins (change them in Admin -> Staff accounts!)
| Role     | Username | Password   | Goes to        |
|----------|----------|------------|----------------|
| Admin    | admin    | admin123   | Admin panel    |
| Manager  | manager  | manager123 | Admin panel    |
| Waiter   | waiter   | waiter123  | Live board     |
| Kitchen  | kitchen  | kitchen123 | Live board     |
| Cashier  | cashier  | cashier123 | Live board     |

Login page: `/login.php`

## Customer flow
1. Customer scans the table QR (Admin -> Tables & QR -> print) -> `scan.php?t=TOKEN` opens the site for that table.
2. Browses the menu, adds items, enters name + mobile number once, places the order.
3. Tracks status live on **My Orders**, can tap **Order again** at any time.
4. **Bill** shows items, tax and total; **Pay** supports JazzCash / Easypaisa / Card / Cash at table.
5. **Call waiter** button (bottom right) notifies staff if there is a problem; orders always go through the website.
6. **WiFi** page shows the network name, password (copy button) and a join-by-scan QR code.

## Staff flow
Live board (`/staff/`) refreshes every 5 seconds: new orders -> kitchen -> ready -> served, plus waiter calls and cash to collect.

## Folders
- `assets/css/main.css`, `assets/js/main.js`, `assets/img/picture/` (all images)
- `api/` JSON endpoints, `admin/` admin panel, `staff/` live board, `includes/` shared code

## Before going live
- Replace the demo payment in `includes/payment.php` with a real gateway (JazzCash, Easypaisa, PayFast, Stripe...). Never store card numbers yourself.
- Change all demo passwords, host on HTTPS, and regenerate table QR codes from the live domain.
- Replace the placeholder dish images in `assets/img/picture/` with real photos (or upload via Admin -> Menu).
