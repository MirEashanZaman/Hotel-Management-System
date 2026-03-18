CREATE DATABASE IF NOT EXISTS hotel_management;
USE hotel_management;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    room_type ENUM('Single','Double','Suite','Deluxe') NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    description TEXT,
    amenities TEXT,
    status ENUM('available','occupied','maintenance') NOT NULL DEFAULT 'available',
    floor INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','checked_in','checked_out','cancelled') DEFAULT 'pending',
    special_requests TEXT,
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card','online') DEFAULT 'cash',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    paid_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('food','spa','laundry','transport','other') DEFAULT 'other',
    available TINYINT(1) DEFAULT 1
);

CREATE TABLE service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
    notes TEXT,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(50),
    logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, email, password, role, phone, address) VALUES
('Eshan Admin',     'eshan@admin.com',  'sha256:ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'admin',    '+8801700000001', 'Dhaka, Bangladesh'),
('Milton Staff',    'milton@gmail.com', 'sha256:ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'staff',    '+8801700000002', 'Dhaka, Bangladesh'),
('Tanjim Customer', 'tanjim@gmail.com', 'sha256:ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'customer', '+8801700000003', 'Dhaka, Bangladesh');

INSERT INTO rooms (room_number, room_type, price_per_night, capacity, description, amenities, status, floor) VALUES
('101', 'Single', 2500.00, 1, 'Cozy single room with city view', 'WiFi, TV, AC, Mini Fridge', 'available', 1),
('102', 'Single', 2500.00, 1, 'Comfortable single room near garden', 'WiFi, TV, AC', 'available', 1),
('201', 'Double', 4500.00, 2, 'Spacious double room with balcony', 'WiFi, TV, AC, Mini Bar, Balcony', 'available', 2),
('202', 'Double', 4500.00, 2, 'Double room with pool view', 'WiFi, TV, AC, Mini Bar, Pool View', 'occupied', 2),
('203', 'Double', 4800.00, 2, 'Premium double room with city skyline', 'WiFi, TV, AC, Mini Bar, City View', 'available', 2),
('301', 'Deluxe', 7500.00, 3, 'Deluxe room with king bed and lounge area', 'WiFi, TV, AC, Jacuzzi, Mini Bar, Lounge', 'available', 3),
('302', 'Deluxe', 7500.00, 3, 'Deluxe corner room with panoramic view', 'WiFi, TV, AC, Jacuzzi, Mini Bar, Panoramic View', 'maintenance', 3),
('401', 'Suite',  15000.00, 4, 'Presidential suite with full living room', 'WiFi, TV, AC, Jacuzzi, Kitchen, Living Room, Butler Service', 'available', 4),
('402', 'Suite',  12000.00, 3, 'Executive suite with home office setup', 'WiFi, TV, AC, Jacuzzi, Office Setup, Mini Kitchen', 'available', 4);

INSERT INTO services (name, description, price, category) VALUES
('Breakfast Buffet', 'Full continental breakfast buffet', 500.00,  'food'),
('Room Service',     '24/7 in-room dining',               200.00,  'food'),
('Spa Package',      'Full body massage and relaxation',  2000.00, 'spa'),
('Laundry Service',  'Per kg laundry and dry cleaning',   150.00,  'laundry'),
('Airport Transfer', 'Pickup and drop to airport',        1200.00, 'transport'),
('City Tour',        'Guided half-day city tour',         1500.00, 'transport');

INSERT INTO bookings (customer_id, room_id, check_in, check_out, total_price, status, special_requests) VALUES
(3, 1, '2026-03-10', '2026-03-13', 7500.00,  'checked_out', 'Late check-out if possible'),
(3, 3, '2026-03-20', '2026-03-22', 9000.00,  'confirmed',   'Need extra pillows');

INSERT INTO payments (booking_id, amount, payment_method, payment_status, paid_at) VALUES
(1, 7500.00, 'card', 'paid',    '2026-03-10 14:00:00'),
(2, 9000.00, 'cash', 'pending', NULL);

INSERT INTO activity_logs (user_id, action, details) VALUES
(1, 'System Initialized', 'Hotel Management System database seeded'),
(3, 'Booking Created', 'Booking #1 for Room 101'),
(3, 'Booking Created', 'Booking #2 for Room 201');
