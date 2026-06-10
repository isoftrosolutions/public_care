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
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_zip VARCHAR(20),
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

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES ('groq_api_key', '');

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

-- ========================================
-- Feature 1: AI Body Analysis (Dosha Assessment)
-- ========================================
CREATE TABLE dosha_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    category ENUM('vata','pitta','kapha') NOT NULL,
    weight INT DEFAULT 1,
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dosha_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vata_score INT DEFAULT 0,
    pitta_score INT DEFAULT 0,
    kapha_score INT DEFAULT 0,
    dominant_dosha VARCHAR(20),
    recommendations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE dosha_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value INT NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES dosha_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES dosha_questions(id) ON DELETE CASCADE
);

-- ========================================
-- Feature 2: Email Coach (Health Reminders)
-- ========================================
CREATE TABLE health_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','water','yoga','diet') NOT NULL,
    reminder_time TIME NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reminder_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','water','yoga','diet'),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent','failed') DEFAULT 'sent',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- Feature 3: 90-Day Dashboard (Patient Metrics)
-- ========================================
CREATE TABLE patient_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    record_date DATE NOT NULL,
    weight DECIMAL(5,2),
    sleep_hours DECIMAL(3,1),
    pain_score INT,
    bp_systolic INT,
    bp_diastolic INT,
    blood_sugar INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, record_date)
);

-- ========================================
-- Feature 4: Family Account
-- ========================================
CREATE TABLE family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    relationship ENUM('self','spouse','son','daughter','father','mother','other') DEFAULT 'other',
    age INT,
    gender ENUM('male','female','other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- Feature 5: Video Consult (Consultations & Prescriptions)
-- ========================================
CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    type ENUM('video','audio','chat') DEFAULT 'video',
    status ENUM('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
    meeting_link VARCHAR(500),
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
);

CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultation_id INT NOT NULL,
    doctor_id INT NOT NULL,
    user_id INT NOT NULL,
    diagnosis TEXT,
    medicines TEXT,
    advice TEXT,
    follow_up_date DATE,
    pdf_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- Feature 6: Language Preferences
-- ========================================
CREATE TABLE user_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    language_name VARCHAR(50) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lang (user_id, language_code)
);

-- ========================================
-- ALTER existing tables
-- ========================================
ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'hi' AFTER role;
ALTER TABLE users ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE AFTER preferred_language;
ALTER TABLE appointments ADD COLUMN consultation_id INT DEFAULT NULL AFTER notes;
ALTER TABLE appointments ADD COLUMN meeting_link VARCHAR(500) DEFAULT NULL AFTER consultation_id;

-- ========================================
-- Seed data: dosha_questions
-- ========================================
INSERT INTO dosha_questions (question_text, category, weight, display_order) VALUES
('मेरा शरीर आमतौर पर ठंडा रहता है, खासकर हाथ-पैर', 'vata', 2, 1),
('मेरी नींद हल्की और बेचैन होती है', 'vata', 2, 2),
('मेरा पाचन अनियमित रहता है - कभी ठीक, कभी नहीं', 'vata', 2, 3),
('मेरा शरीर पतला है और वजन बढ़ाना मुश्किल है', 'vata', 2, 4),
('मेरी त्वचा रूखी और खुरदरी है', 'vata', 1, 5),
('मुझे जल्दी याद आता है लेकिन जल्दी भूल भी जाता हूँ', 'vata', 1, 6),
('मेरा शरीर गर्म रहता है और मुझे पसीना जल्दी आता है', 'pitta', 2, 7),
('मेरी भूख तेज होती है और मैं अधिक खा सकता हूँ', 'pitta', 2, 8),
('मुझे गर्मी सहन नहीं होती और मैं ठंडी चीज़ें पसंद करता हूँ', 'pitta', 2, 9),
('मेरा पाचन तेज है - खाना जल्दी पच जाता है', 'pitta', 2, 10),
('मेरी त्वचा संवेदनशील है और जल्दी लाल हो जाती है', 'pitta', 1, 11),
('मैं निर्णय जल्दी लेता हूँ और कभी-कभी चिड़चिड़ा हो जाता हूँ', 'pitta', 1, 12),
('मेरा शरीर भारी और गठीला है', 'kapha', 2, 13),
('मुझे देर तक सोना पसंद है और सुबह उठना मुश्किल होता है', 'kapha', 2, 14),
('मेरा पाचन धीमा है और भोजन के बाद भारीपन रहता है', 'kapha', 2, 15),
('मेरा वजन आसानी से बढ़ जाता है और घटाना मुश्किल है', 'kapha', 2, 16),
('मेरी त्वचा तैलीय और चिकनी है', 'kapha', 1, 17),
('मैं शांत स्वभाव का हूँ और गुस्सा जल्दी नहीं करता', 'kapha', 1, 18);
