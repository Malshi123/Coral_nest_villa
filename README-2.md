# Coral Nest Villa, Hikkaduwa - Villa & Room Booking System

**Module:** IT1208 - Web Technologies (Continuous Assessment 2)
**Student:** W.D.M. Nethsarani
**Technologies:** HTML5, CSS3 (Bootstrap 5), JavaScript, PHP (PDO), MySQL

## 1. Project Overview

Coral Nest Villa is a fictional eco-friendly beachside villa in Hikkaduwa, Sri Lanka.
This web application lets tourists explore the villa, search room availability, and send booking requests.
The villa administrator can manage rooms and confirm or cancel bookings.

## 2. Implemented Features

### Guest side
- Home page with villa introduction, facilities, room preview and "Book Now" buttons
- Rooms page showing all active rooms (image, description, facilities, max guests, price per night)
- Availability search by check-in date, check-out date and number of guests
  (rooms with an overlapping **confirmed** booking are hidden)
- Guest registration and login (PHP sessions, `password_hash()` / `password_verify()`), logout
- Booking form with live nights and total cost calculation (JavaScript) and final price calculation on the server (PHP)
- 10% long-stay discount for bookings of 3 or more nights
- Room filtering by type and maximum price, and sorting by price or name
- Photo gallery (villa, rooms and facilities) with category filter and lightbox viewer
- Home page photo slideshow, guest comment slider and an interactive Google map of the location
- My Bookings page: shows only the logged-in guest's bookings with status (Pending / Confirmed / Cancelled); guests can cancel their pending bookings

### Administrator side
- Admin dashboard with booking statistics (total, pending, confirmed, cancelled, revenue, active rooms, guests) and recent bookings
- Room management: add, view, edit (details and pricing), activate / deactivate, delete (rooms that have bookings cannot be deleted)
- Booking management: view all bookings, filter by status, confirm or cancel
- Overlapping confirmed bookings for the same room are prevented (checked inside a database transaction)

### Responsive design
- Bootstrap 5 grid and custom CSS media behaviour for desktop, tablet and mobile

## 3. Security and Good Practices
- PDO prepared statements for all database queries (protects against SQL injection)
- Passwords stored using PHP password hashing
- `session_regenerate_id()` after login
- Output escaped with `htmlspecialchars()` (protects against XSS)
- Role-based access control: admin pages are blocked for guests (`includes/admin_check.php`)
- Guests can only view and cancel their own bookings (`WHERE user_id = ?`)
- Client-side (JavaScript) and server-side (PHP) validation for registration and booking

## 4. Software Requirements
- XAMPP (Apache + MySQL + PHP 8.x) or WAMP / MAMP / equivalent
- A web browser (Chrome, Safari, Firefox)
- Internet connection (Bootstrap, Bootstrap Icons and Google Fonts are loaded from CDNs)

## 5. Installation and Configuration

1. Install XAMPP and start **Apache** and **MySQL** from the XAMPP control panel.
2. Copy the project folder `coral_nest` into the web root:
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/coral_nest`
   - Windows: `C:\xampp\htdocs\coral_nest`
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Click **Import**, choose `db_structure.sql` and click **Import**.
   This creates the `coral_nest` database with tables and sample data.
5. Check the database settings in `includes/db.php` (default XAMPP: user `root`, empty password).
6. If the project folder name is not `coral_nest`, change `BASE_URL` in `includes/header.php`
   and the redirect path in `includes/admin_check.php`.
7. Open the website: `http://localhost/coral_nest/`

## 6. Sample Login Credentials (fictional)

| Role  | Email                | Password  |
|-------|----------------------|-----------|
| Admin | admin@coralnest.lk   | Admin@123 |
| Guest | nimal@example.com    | Guest@123 |
| Guest | sarah@example.com    | Guest@123 |

## 7. Database Design

Tables: `users`, `rooms`, `bookings`

- `bookings.user_id` is a foreign key to `users.user_id`
- `bookings.room_id` is a foreign key to `rooms.room_id`
- `booking_status` values: Pending, Confirmed, Cancelled

## 8. Folder Structure

```
coral_nest/
├── index.php            Home page
├── rooms.php            Rooms, availability search, filters and sorting
├── gallery.php          Photo gallery with filter and lightbox
├── booking.php          Booking form
├── my_bookings.php      Guest bookings
├── register.php, login.php, logout.php
├── db_structure.sql     Database structure and sample data
├── admin/               dashboard.php, rooms.php, room_form.php, bookings.php
├── includes/            db.php, header.php, footer.php, functions.php, admin_check.php, admin_nav.php
├── css/                 style.css, home.css
├── js/                  auth.js, booking.js, gallery.js
└── images/              villa and room photos (facility photos in images/facilities/)
```

## 9. External Resources and Acknowledgements
- Bootstrap 5.3.3 and Bootstrap Icons 1.11.3 (CDN)
- Google Fonts: Poppins, Playfair Display, Dancing Script
- Google Maps embed (iframe) for the location map
- Photographs: [add the source and photographer of each image here, e.g. Unsplash / Pexels]
- All villa details, rooms, guest records and prices are fictional and created for this assignment.

## 10. Generative AI Declaration
Generative AI (Claude by Anthropic) was used during this project for brainstorming the project idea, planning the work, generating starter code and helping with debugging. I tested the system, reviewed the code and I am able to explain how it works.
