-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'buyer', 'transport', 'admin') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCTS TABLE (crops listed by farmers)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100),
    price_per_unit DECIMAL(10,2) NOT NULL,
    unit VARCHAR(30) DEFAULT 'kg',
    quantity_available DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('available', 'sold_out') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id)
);

-- ORDERS TABLE (buyer purchases)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('bkash', 'cash') DEFAULT 'cash',
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    bkash_number VARCHAR(20),
    bkash_transaction_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- TRANSPORT REQUESTS TABLE
CREATE TABLE transport_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    order_id INT,
    transport_id INT,
    pickup_address TEXT NOT NULL,
    delivery_address TEXT NOT NULL,
    product_details TEXT,
    status ENUM('pending', 'accepted', 'in_transit', 'delivered') DEFAULT 'pending',
    requested_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id),
    FOREIGN KEY (transport_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- SALES TRACKING TABLE (farmer sales summary)
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_sold DECIMAL(10,2) NOT NULL,
    amount_earned DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- DEFAULT ADMIN USER (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@agroconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');