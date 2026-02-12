-- =====================================================
-- DATABASE VILLA SALON LUMAJANG
-- =====================================================
-- Drop database jika sudah ada
DROP DATABASE IF EXISTS villa_salon;

-- Create database
CREATE DATABASE villa_salon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE villa_salon;

-- =====================================================
-- 1. TABEL USERS (Admin, Kasir, Terapis)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir', 'terapis') NOT NULL DEFAULT 'kasir',
    email VARCHAR(100),
    phone VARCHAR(15),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. TABEL SERVICES (Layanan dengan Fee)
-- =====================================================
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    fee DECIMAL(10, 2) NOT NULL,
    duration_minutes INT DEFAULT 30,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 3. TABEL THERAPIST_STATUS (Status Terapis)
-- =====================================================
CREATE TABLE therapist_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    status ENUM('available', 'busy', 'off') DEFAULT 'available',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 4. TABEL MEMBERS (Pelanggan)
-- =====================================================
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 5. TABEL TRANSACTIONS (Transaksi Harian)
-- =====================================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_date DATE NOT NULL,
    member_id INT,
    service_id INT NOT NULL,
    therapist_id INT NOT NULL,
    kasir_id INT NOT NULL,
    fee DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'qris') NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (kasir_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_date (transaction_date),
    INDEX idx_therapist (therapist_id),
    INDEX idx_status (status)
);

-- =====================================================
-- 6. TABEL THERAPIST_COMMISSION (Komisi Terapis)
-- =====================================================
CREATE TABLE therapist_commission (
    id INT PRIMARY KEY AUTO_INCREMENT,
    therapist_id INT NOT NULL,
    transaction_id INT NOT NULL UNIQUE,
    commission_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('accumulated', 'withdrawn') DEFAULT 'accumulated',
    accumulated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    withdrawal_date TIMESTAMP NULL,
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    INDEX idx_therapist (therapist_id),
    INDEX idx_status (status)
);

-- =====================================================
-- 7. TABEL THERAPIST_WITHDRAWAL (Pencairan Komisi)
-- =====================================================
CREATE TABLE therapist_withdrawal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    therapist_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    withdrawal_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    proof_file VARCHAR(255),
    notes TEXT,
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_date (withdrawal_date),
    INDEX idx_therapist (therapist_id)
);

-- =====================================================
-- 8. TABEL CASH_REGISTER (Kas Salon)
-- =====================================================
CREATE TABLE cash_register (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cash_date DATE NOT NULL UNIQUE,
    opening_balance DECIMAL(10, 2) DEFAULT 0,
    total_cash_income DECIMAL(10, 2) DEFAULT 0,
    total_qris_income DECIMAL(10, 2) DEFAULT 0,
    total_expenses DECIMAL(10, 2) DEFAULT 0,
    closing_balance DECIMAL(10, 2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 9. TABEL EXPENSES (Pengeluaran)
-- =====================================================
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_date DATE NOT NULL,
    description VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    recorded_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    INDEX idx_date (expense_date)
);

-- =====================================================
-- 10. TABEL SETTINGS (Pengaturan Umum)
-- =====================================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- INSERT DATA AWAL
-- =====================================================

-- Insert Admin User
INSERT INTO users (name, username, password, role, email, phone, status) VALUES
('Administrator', 'admin', MD5('1234'), 'admin', 'admin@villasalon.local', '081234567890', 'active'),
('Kasir Utama', 'kasir1', MD5('kasir123'), 'kasir', 'kasir1@villasalon.local', '081234567891', 'active'),
('Terapis Siti', 'siti', MD5('terapis123'), 'terapis', 'siti@villasalon.local', '081234567892', 'active'),
('Terapis Rina', 'rina', MD5('terapis123'), 'terapis', 'rina@villasalon.local', '081234567893', 'active'),
('Terapis Dewi', 'dewi', MD5('terapis123'), 'terapis', 'dewi@villasalon.local', '081234567894', 'active');

-- Insert Therapist Status
INSERT INTO therapist_status (user_id, status) VALUES
(3, 'available'),
(4, 'available'),
(5, 'available');

-- Insert Services
INSERT INTO services (name, description, fee, duration_minutes, status) VALUES
('Creambath', 'Perawatan rambut dengan krim khusus', 25000, 30, 'active'),
('Pijat Tradisional', 'Pijat terapi tradisional', 30000, 60, 'active'),
('Pijat Refleksi', 'Pijat telapak kaki', 20000, 45, 'active'),
('Facial', 'Perawatan wajah lengkap', 35000, 60, 'active'),
('Manicure', 'Perawatan kuku tangan', 15000, 30, 'active'),
('Pedicure', 'Perawatan kuku kaki', 15000, 30, 'active');

-- Insert Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('salon_name', 'Villa Salon Lumajang'),
('salon_phone', '(0334) 123456'),
('salon_address', 'Jl. Merdeka No. 123, Lumajang'),
('salon_city', 'Lumajang'),
('admin_password', '1234');

-- =====================================================
-- CREATE INDEXES untuk performa
-- =====================================================
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_user_status ON users(status);
CREATE INDEX idx_service_status ON services(status);
CREATE INDEX idx_transaction_date ON transactions(transaction_date);
CREATE INDEX idx_expense_date ON expenses(expense_date);

-- =====================================================
-- END OF SCRIPT
-- =====================================================
