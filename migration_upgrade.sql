-- Use this only when upgrading an older GlobeTrek database created by the previous ZIP.
-- Recommended for a clean deployment: create a fresh database with database.sql.
-- Existing data should be backed up before running ALTER statements.
ALTER TABLE bookings
ADD COLUMN booking_reference VARCHAR(30) NULL;
UPDATE bookings
SET booking_reference = CONCAT(
        'GT-',
        DATE_FORMAT(created_at, '%y%m'),
        '-',
        LPAD(id, 6, '0')
    )
WHERE booking_reference IS NULL
    OR booking_reference = '';
ALTER TABLE bookings
MODIFY booking_reference VARCHAR(30) NOT NULL;
ALTER TABLE bookings
ADD UNIQUE KEY uq_booking_reference (booking_reference);
ALTER TABLE bookings
ADD COLUMN assigned_staff_id INT UNSIGNED NULL;
ALTER TABLE bookings
ADD COLUMN internal_notes TEXT NULL;
ALTER TABLE bookings
ADD COLUMN confirmed_at DATETIME NULL;
ALTER TABLE bookings
ADD INDEX idx_bookings_staff (assigned_staff_id);
ALTER TABLE booking_services
ADD COLUMN accommodation_status ENUM('pending', 'requested', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending';
ALTER TABLE booking_services
ADD COLUMN transportation_status ENUM('pending', 'requested', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending';
ALTER TABLE booking_services
ADD COLUMN supplier_notes TEXT NULL;
ALTER TABLE inquiries
ADD COLUMN responded_by INT UNSIGNED NULL;
ALTER TABLE inquiries
ADD COLUMN responded_at DATETIME NULL;
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
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id VARCHAR(80) NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
-- Email OTP password-reset support. Safe to run more than once.
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