-- Clean and Earn India Database Schema

-- Drop existing tables if they exist
DROP TABLE IF EXISTS points_transactions;
DROP TABLE IF EXISTS waste_purchases;
DROP TABLE IF EXISTS storage_inventory;
DROP TABLE IF EXISTS waste_collections;
DROP TABLE IF EXISTS waste_listings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS waste_types;
DROP TABLE IF EXISTS waste_subtypes;

-- Create waste_types table
CREATE TABLE waste_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    rate_per_kg DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create waste_subtypes table
CREATE TABLE waste_subtypes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waste_type_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    rate_per_kg DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (waste_type_id) REFERENCES waste_types(id) ON DELETE CASCADE
);

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    role ENUM('household', 'collector', 'storage', 'buyer', 'admin', 'super_admin') NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    profile_image VARCHAR(255),
    total_points INT DEFAULT 0,
    reset_token VARCHAR(64),
    reset_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create waste_listings table
CREATE TABLE waste_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    waste_type_id INT NOT NULL,
    waste_subtype_id INT,
    weight DECIMAL(10, 2) NOT NULL,
    quantity INT,
    description TEXT,
    pickup_date DATE NOT NULL,
    pickup_time_slot VARCHAR(50) NOT NULL,
    pickup_address TEXT NOT NULL,
    status ENUM('pending', 'assigned', 'collected', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (waste_type_id) REFERENCES waste_types(id),
    FOREIGN KEY (waste_subtype_id) REFERENCES waste_subtypes(id)
);

-- Create waste_collections table
CREATE TABLE waste_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    collector_id INT NOT NULL,
    actual_weight DECIMAL(10, 2),
    collection_date TIMESTAMP,
    status ENUM('assigned', 'in_progress', 'collected', 'delivered', 'cancelled') DEFAULT 'assigned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES waste_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (collector_id) REFERENCES users(id)
);

-- Create storage_inventory table
CREATE TABLE storage_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    storage_id INT NOT NULL,
    waste_type_id INT NOT NULL,
    waste_subtype_id INT,
    weight DECIMAL(10, 2) NOT NULL,
    collection_id INT,
    status ENUM('available', 'reserved', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (storage_id) REFERENCES users(id),
    FOREIGN KEY (waste_type_id) REFERENCES waste_types(id),
    FOREIGN KEY (waste_subtype_id) REFERENCES waste_subtypes(id),
    FOREIGN KEY (collection_id) REFERENCES waste_collections(id)
);

-- Create waste_purchases table
CREATE TABLE waste_purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    storage_id INT NOT NULL,
    inventory_id INT NOT NULL,
    weight DECIMAL(10, 2) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    pickup_date DATE,
    pickup_time VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (storage_id) REFERENCES users(id),
    FOREIGN KEY (inventory_id) REFERENCES storage_inventory(id)
);

-- Create points_transactions table
CREATE TABLE points_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    transaction_type ENUM('earned', 'redeemed', 'expired', 'adjusted') NOT NULL,
    reference_id INT,
    reference_type ENUM('listing', 'collection', 'purchase', 'manual'),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default waste types
INSERT INTO waste_types (name, description, rate_per_kg) VALUES
('Paper', 'Paper waste including newspapers, magazines, and cardboard', 22.00),
('Plastic', 'Plastic waste including bottles, containers, and packaging', 15.00),
('Metal', 'Metal waste including aluminum, iron, and steel', 90.00),
('Electronic', 'Electronic waste including computers, phones, and appliances', 200.00),
('Glass', 'Glass waste including bottles and containers', 10.00),
('Organic', 'Organic waste including food scraps and garden waste', 5.00);

-- Insert default waste subtypes
INSERT INTO waste_subtypes (waste_type_id, name, description, rate_per_kg) VALUES
(1, 'Newspaper', 'Old newspapers and print media', 22.00),
(1, 'White Printed Paper', 'Office paper and documents', 30.00),
(1, 'Brown Kraft Paper', 'Packaging material', 35.00),
(1, 'Plain Paper Scrap', 'Clean white paper', 6.00),
(1, 'Cardboard', 'Corrugated boxes and packaging', 9.00),
(2, 'PET Bottles', 'Plastic beverage bottles', 15.00),
(2, 'HDPE Plastic', 'High-density polyethylene containers', 20.00),
(2, 'PVC Plastic', 'Polyvinyl chloride materials', 10.00),
(3, 'Aluminum', 'Cans and other aluminum items', 90.00),
(3, 'Copper', 'Wires and copper items', 450.00),
(3, 'Iron/Steel', 'Various iron and steel items', 22.00),
(4, 'Computers', 'Desktop and laptop computers', 200.00),
(4, 'Mobile Phones', 'Smartphones and feature phones', 150.00),
(4, 'Household Appliances', 'Refrigerators, washing machines, etc.', 100.00);

-- Create admin user
INSERT INTO users (name, email, password, phone, role, status) VALUES
('Admin User', 'admin@cleanearnindia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'admin', 'active');