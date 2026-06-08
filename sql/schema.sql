CREATE DATABASE IF NOT EXISTS public_care_ayurveda;
USE public_care_ayurveda;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin','doctor') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2),
    image_url TEXT,
    stock INT DEFAULT 0,
    is_bestseller BOOLEAN DEFAULT FALSE,
    rating DECIMAL(2,1) DEFAULT 0.0,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    qualifications VARCHAR(200),
    specialty VARCHAR(200),
    experience_years INT,
    languages VARCHAR(200),
    fee DECIMAL(10,2),
    bio TEXT,
    image_url TEXT,
    rating DECIMAL(2,1) DEFAULT 0.0,
    reviews_count INT DEFAULT 0,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0,
    shipping DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    doctor_id INT,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    category VARCHAR(100),
    excerpt TEXT,
    content TEXT,
    image_url TEXT,
    author VARCHAR(100),
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed data
INSERT INTO categories (name, slug, image_url) VALUES
('Immunity', 'immunity', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2'),
('Diabetes', 'diabetes', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2'),
('Weight Loss', 'weight-loss', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2'),
('Hair Care', 'hair-care', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2'),
('Skin Care', 'skin-care', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2');

INSERT INTO products (category_id, name, slug, description, price, image_url, is_bestseller, rating, reviews_count) VALUES
(1, 'Ashwagandha Vitality', 'ashwagandha-vitality', '500mg KSM-66 Organic Ashwagandha for stress relief and vitality.', 24.99, 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', TRUE, 5.0, 420),
(2, 'Triphala Digestive', 'triphala-digestive', 'Traditional Triphala blend for digestive health and detox.', 18.50, 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', FALSE, 4.0, 185),
(3, 'Golden Milk Blend', 'golden-milk-blend', 'Turmeric and spice blend for immunity and inflammation.', 22.00, 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', TRUE, 5.0, 312),
(4, 'Herbal Hair Oil', 'herbal-hair-oil', 'Amla and Brahmi infused hair oil for strength and growth.', 15.99, 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', TRUE, 5.0, 540);

INSERT INTO doctors (name, slug, qualifications, specialty, experience_years, languages, fee, bio, rating, reviews_count) VALUES
('Dr. Ananya Sharma', 'dr-ananya-sharma', 'BAMS, MD (Ayurveda Pharmacology)', 'Internal Medicine', 12, 'English, Hindi', 750, 'Dr. Ananya Sharma is a distinguished practitioner of Ayurvedic medicine focusing on Kayachikitsa.', 4.9, 240),
('Dr. Vikram Malhotra', 'dr-vikram-malhotra', 'BAMS, PhD', 'Panchakarma Specialist', 15, 'Hindi, Marathi', 1000, 'Expert in Panchakarma detoxification and Ayurvedic surgery.', 4.8, 115),
('Dr. Priya Nair', 'dr-priya-nair', 'BAMS', "Women's Health", 8, 'English, Malayalam', 500, 'Specializing in women''s health, lifestyle consultation, and hormonal balance.', 5.0, 88);

INSERT INTO blog_posts (title, slug, category, excerpt, image_url, author) VALUES
("The Benefits of Brahmi for Cognitive Clarity", "benefits-of-brahmi", "HERBAL GUIDE", "Discover how this ancient 'herb of grace' supports memory, focus, and long-term brain health.", 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', 'Dr. Ananya Sharma'),
("Ayurvedic Diet Tips for Staying Cool This Summer", "ayurvedic-diet-summer", "DIET & LIFESTYLE", "Learn how to balance your Pitta dosha during hot months with cooling foods.", 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', 'Public Care Team'),
("Essential Yoga Poses for Digestive Health", "yoga-poses-digestive-health", "YOGA & MINDFULNESS", "Uncover the connection between movement and internal balance with simple asanas.", 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2', 'Wellness Editor');
