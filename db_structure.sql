-- =====================================================
-- Coral Nest Villa, Hikkaduwa - Database Structure
-- IT1208 Web Technologies - CA2
-- =====================================================

DROP DATABASE IF EXISTS coral_nest;
CREATE DATABASE coral_nest CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE coral_nest;

-- -----------------------------------------------------
-- 1. users (guests and admin)
-- -----------------------------------------------------
CREATE TABLE users (
    user_id        INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    contact_number VARCHAR(20)  NOT NULL,
    password       VARCHAR(255) NOT NULL,          -- password_hash() value
    role           ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 2. rooms
-- -----------------------------------------------------
CREATE TABLE rooms (
    room_id         INT AUTO_INCREMENT PRIMARY KEY,
    room_name       VARCHAR(100) NOT NULL,
    room_type       VARCHAR(50)  NOT NULL,
    description     TEXT,
    facilities      VARCHAR(255),
    price_per_night DECIMAL(10,2) NOT NULL CHECK (price_per_night > 0),
    max_guests      INT NOT NULL CHECK (max_guests > 0),
    image           VARCHAR(255) DEFAULT 'default.jpg',
    room_status     ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 3. bookings
-- -----------------------------------------------------
CREATE TABLE bookings (
    booking_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    room_id        INT NOT NULL,
    check_in       DATE NOT NULL,
    check_out      DATE NOT NULL,
    guests         INT NOT NULL DEFAULT 1,
    nights         INT NOT NULL,
    total_amount   DECIMAL(10,2) NOT NULL,
    booking_status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
    booking_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    CONSTRAINT chk_dates CHECK (check_out > check_in)
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE DATA (all fictional)
-- =====================================================

-- Passwords:  admin -> Admin@123   |  guests -> Guest@123
INSERT INTO users (name, email, contact_number, password, role) VALUES
('Villa Admin',    'admin@coralnest.lk',  '0771234567', '$2b$12$XxuJ120sI0z988xU.SVjuevsyG4DjQ8yZn7h8m7jLPqzvVBXia6Lm', 'admin'),
('Nimal Perera',   'nimal@example.com',   '0712345678', '$2b$12$Xe1cpjSvgWwYQVjZcFa2W.18Oq6xHXaQX1h/sGjCf3MxbdHKZxQ3i', 'guest'),
('Sarah Johnson',  'sarah@example.com',   '0723456789', '$2b$12$Xe1cpjSvgWwYQVjZcFa2W.18Oq6xHXaQX1h/sGjCf3MxbdHKZxQ3i', 'guest');

INSERT INTO rooms (room_name, room_type, description, facilities, price_per_night, max_guests, image, room_status) VALUES
('Turtle Bay Deluxe',  'Deluxe',  'Spacious sea view room with a private balcony facing the Hikkaduwa coast.', 'AC, WiFi, Balcony, Breakfast, Sea View', 18000.00, 2, 'turtle_bay.jpg',  'active'),
('Coral Garden Room',  'Standard','Quiet room surrounded by a tropical garden, a short walk to the beach.',    'AC, WiFi, Garden View, Breakfast',    12000.00, 2, 'coral_garden.jpg','active'),
('Reef Family Suite',  'Suite',   'Two-bedroom family suite with a living area and ocean breeze.',             'AC, WiFi, Kitchenette, Breakfast, Parking', 25000.00, 4, 'reef_family.jpg', 'active'),
('Shell Budget Double','Budget',  'Simple and clean double room for budget travellers.',                       'Fan, WiFi, Breakfast',                 8000.00, 2, 'shell_budget.jpg','active');

INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, nights, total_amount, booking_status) VALUES
(2, 1, '2026-12-10', '2026-12-12', 2, 2, 36000.00, 'Confirmed'),
(2, 2, '2026-12-20', '2026-12-23', 2, 3, 32400.00, 'Pending'),   -- 3 nights: 10% discount applied
(3, 3, '2026-12-15', '2026-12-18', 4, 3, 67500.00, 'Cancelled');
