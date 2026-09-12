CREATE DATABASE IF NOT EXISTS globetrek_adventures CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE globetrek_adventures;
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    account_scope VARCHAR(10) GENERATED ALWAYS AS (
        CASE
            WHEN role = 'customer' THEN 'customer'
            ELSE 'management'
        END
    ) STORED,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email_scope(email, account_scope),
    INDEX idx_users_role_status(role, status)
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS tour_packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    destination VARCHAR(180) NOT NULL,
    duration_days INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    short_description VARCHAR(500) NULL,
    description TEXT NULL,
    activities VARCHAR(1000) NULL,
    inclusions TEXT NULL,
    exclusions TEXT NULL,
    status ENUM('active', 'inactive', 'draft') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_packages_status(status),
    CONSTRAINT fk_package_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE
    SET NULL
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS accommodations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    location VARCHAR(180) NOT NULL,
    price_per_night DECIMAL(12, 2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS transportation_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(180) NOT NULL,
    service_type VARCHAR(120) NOT NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(30) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    package_id INT UNSIGNED NOT NULL,
    travel_date DATE NOT NULL,
    travelers INT UNSIGNED NOT NULL DEFAULT 1,
    custom_plan TEXT NULL,
    total_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid', 'pending', 'paid', 'refunded', 'failed') NOT NULL DEFAULT 'unpaid',
    assigned_staff_id INT UNSIGNED NULL,
    internal_notes TEXT NULL,
    confirmed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_user(user_id),
    INDEX idx_bookings_status(status),
    INDEX idx_bookings_staff(assigned_staff_id),
    CONSTRAINT fk_booking_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_booking_package FOREIGN KEY(package_id) REFERENCES tour_packages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_booking_staff FOREIGN KEY(assigned_staff_id) REFERENCES users(id) ON DELETE
    SET NULL
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS booking_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    accommodation_id INT UNSIGNED NULL,
    transportation_id INT UNSIGNED NULL,
    accommodation_status ENUM('pending', 'requested', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    transportation_status ENUM('pending', 'requested', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    supplier_notes TEXT NULL,
    notes TEXT NULL,
    CONSTRAINT fk_bs_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_bs_accommodation FOREIGN KEY(accommodation_id) REFERENCES accommodations(id) ON DELETE
    SET NULL,
        CONSTRAINT fk_bs_transport FOREIGN KEY(transportation_id) REFERENCES transportation_services(id) ON DELETE
    SET NULL
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NULL,
    status ENUM('open', 'answered', 'closed') NOT NULL DEFAULT 'open',
    responded_by INT UNSIGNED NULL,
    responded_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_inquiries_status(status),
    CONSTRAINT fk_inquiry_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_inquiry_staff FOREIGN KEY(responded_by) REFERENCES users(id) ON DELETE
    SET NULL
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    order_id VARCHAR(80) NOT NULL UNIQUE,
    gateway VARCHAR(40) NOT NULL DEFAULT 'payhere',
    gateway_payment_id VARCHAR(100) NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'LKR',
    status ENUM(
        'pending',
        'success',
        'cancelled',
        'failed',
        'refunded'
    ) NOT NULL DEFAULT 'pending',
    method VARCHAR(40) NULL,
    raw_status VARCHAR(100) NULL,
    gateway_payload JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_booking(booking_id),
    INDEX idx_payments_status(status),
    CONSTRAINT fk_payment_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE RESTRICT
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id VARCHAR(80) NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity(entity_type, entity_id),
    CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE
    SET NULL
) ENGINE = InnoDB;
-- Demo credentials: passwords are generated by the application installer/seed script.
CREATE TABLE IF NOT EXISTS password_reset_otps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    purpose ENUM('customer', 'management') NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    used_at DATETIME NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    request_ip VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_user_purpose(user_id, purpose, created_at),
    INDEX idx_password_reset_expiry(expires_at),
    CONSTRAINT fk_password_reset_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB;