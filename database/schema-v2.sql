CREATE TABLE IF NOT EXISTS products (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    compare_cents INT UNSIGNED NULL,
    category VARCHAR(60) NOT NULL,
    image VARCHAR(160) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    portion VARCHAR(80) NOT NULL,
    INDEX idx_products_active_featured (active, featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    delivery_json JSON NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id CHAR(36) PRIMARY KEY,
    number VARCHAR(32) NOT NULL UNIQUE,
    user_id CHAR(36) NOT NULL,
    customer_json JSON NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    status ENUM('received','preparing','shipping','delivered','cancelled') NOT NULL DEFAULT 'received',
    payment_status ENUM('pending','approved','rejected','cancelled','refunded','expired') NOT NULL DEFAULT 'pending',
    payment_id VARCHAR(80) NULL UNIQUE,
    pix_code TEXT NULL,
    pix_qr_base64 MEDIUMTEXT NULL,
    payment_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_status_created (status, created_at),
    INDEX idx_orders_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id CHAR(36) PRIMARY KEY,
    order_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    name VARCHAR(120) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_cents INT UNSIGNED NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
