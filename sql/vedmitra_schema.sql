-- ============================================================
-- Vedmitra Complete Database Schema v1.0
-- Healthcare + Medicine + Consultation + B2B + Ayurveda
-- ============================================================

-- ============================================================
-- 1. LAB TESTS
-- ============================================================
CREATE TABLE lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    category VARCHAR(100),
    description TEXT,
    includes TEXT COMMENT 'What tests are included',
    preparation_instructions TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    home_collection BOOLEAN DEFAULT TRUE,
    report_time_hours INT DEFAULT 24,
    image_url TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lab_test_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME,
    collection_address TEXT,
    collection_type ENUM('home','center') DEFAULT 'home',
    status ENUM('pending','confirmed','sample_collected','processing','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    amount DECIMAL(10,2),
    report_url TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES lab_tests(id) ON DELETE CASCADE
);

-- ============================================================
-- 2. B2B ORDER PUNCH
-- ============================================================
CREATE TABLE order_punch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    retailer_id INT,
    order_number VARCHAR(30) UNIQUE NOT NULL,
    order_type ENUM('retail','wholesale','distributor') DEFAULT 'retail',
    source ENUM('manual','barcode','prescription','excel') DEFAULT 'manual',
    total_amount DECIMAL(12,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    gst_amount DECIMAL(10,2) DEFAULT 0,
    net_amount DECIMAL(12,2) NOT NULL,
    status ENUM('draft','pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'draft',
    payment_status ENUM('pending','paid','partial','refunded') DEFAULT 'pending',
    prescription_file VARCHAR(500),
    excel_file VARCHAR(500),
    notes TEXT,
    delivery_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_punch_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_punch_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(300),
    barcode VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2),
    gst_percent DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(12,2),
    FOREIGN KEY (order_punch_id) REFERENCES order_punch(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ============================================================
-- 3. RETURNS & REPLACEMENTS
-- ============================================================
CREATE TABLE return_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    return_number VARCHAR(30) UNIQUE NOT NULL,
    return_type ENUM('return','replacement') DEFAULT 'return',
    reason ENUM('wrong_product','damaged','expired','defective','other') NOT NULL,
    reason_detail TEXT,
    status ENUM('pending','approved','picked_up','refunded','replaced','rejected') DEFAULT 'pending',
    refund_amount DECIMAL(10,2),
    pickup_address TEXT,
    pickup_date DATE,
    pickup_slot TIME,
    tracking_id VARCHAR(100),
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_id INT NOT NULL,
    order_item_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    refund_amount DECIMAL(10,2),
    FOREIGN KEY (return_id) REFERENCES return_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 4. WALLET
-- ============================================================
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    balance DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    source ENUM('payment','refund','withdrawal','cashback','referral','admin') NOT NULL,
    reference_id INT,
    reference_type VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
);

-- ============================================================
-- 5. COUPONS & OFFERS
-- ============================================================
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('percent','fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2),
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    applies_to ENUM('all','category','product') DEFAULT 'all',
    applies_value INT,
    valid_from DATETIME,
    valid_to DATETIME,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coupon_id INT NOT NULL,
    order_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
);

-- ============================================================
-- 6. WISHLIST & SUBSCRIPTIONS
-- ============================================================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT,
    plan_name VARCHAR(200),
    frequency ENUM('weekly','biweekly','monthly','quarterly') DEFAULT 'monthly',
    status ENUM('active','paused','cancelled','expired') DEFAULT 'active',
    next_delivery_date DATE,
    total_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE subscription_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 7. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT,
    type ENUM('order','consultation','payment','promo','reminder','system') DEFAULT 'system',
    reference_type VARCHAR(50),
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 8. AI HEALTH ASSISTANT
-- ============================================================
CREATE TABLE ai_chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(200),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ai_chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    role ENUM('user','assistant','system') NOT NULL,
    message TEXT NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES ai_chat_sessions(id) ON DELETE CASCADE
);

-- ============================================================
-- 9. MEDICINE INFORMATION & DRUG INTERACTIONS
-- ============================================================
CREATE TABLE drug_information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(300) NOT NULL,
    generic_name VARCHAR(300),
    category VARCHAR(100),
    description TEXT,
    uses TEXT,
    side_effects TEXT,
    precautions TEXT,
    dosage TEXT,
    brand_names TEXT,
    image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE drug_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drug1_id INT NOT NULL,
    drug2_id INT NOT NULL,
    severity ENUM('minor','moderate','severe','contraindicated') NOT NULL,
    description TEXT,
    mechanism TEXT,
    FOREIGN KEY (drug1_id) REFERENCES drug_information(id) ON DELETE CASCADE,
    FOREIGN KEY (drug2_id) REFERENCES drug_information(id) ON DELETE CASCADE
);

-- ============================================================
-- 10. MEDICINE REMINDERS
-- ============================================================
CREATE TABLE medicine_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_name VARCHAR(200) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    times JSON COMMENT 'Array of reminder times',
    start_date DATE,
    end_date DATE,
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 11. SAVED ADDRESSES
-- ============================================================
CREATE TABLE saved_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) COMMENT 'Home, Office, Other',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    landmark VARCHAR(200),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    pincode VARCHAR(20) NOT NULL,
    address_type ENUM('home','work','other') DEFAULT 'home',
    is_default BOOLEAN DEFAULT FALSE,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 12. BARCODE & UPLOADED PRESCRIPTIONS
-- ============================================================
CREATE TABLE barcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(100) UNIQUE NOT NULL,
    product_id INT,
    product_name VARCHAR(300),
    manufacturer VARCHAR(200),
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE uploaded_prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    notes TEXT,
    status ENUM('pending','processed','failed') DEFAULT 'pending',
    order_punch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 13. RETAILER MANAGEMENT
-- ============================================================
CREATE TABLE retailers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    business_name VARCHAR(200) NOT NULL,
    owner_name VARCHAR(100),
    gst_number VARCHAR(50),
    license_number VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(20),
    subscription_plan ENUM('basic','premium','enterprise') DEFAULT 'basic',
    subscription_expiry DATE,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    commission_percent DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE retailer_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    retailer_id INT NOT NULL,
    product_id INT NOT NULL,
    stock INT DEFAULT 0,
    price DECIMAL(10,2),
    is_available BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (retailer_id) REFERENCES retailers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_retailer_product (retailer_id, product_id)
);

-- ============================================================
-- 14. DISTRIBUTOR MANAGEMENT
-- ============================================================
CREATE TABLE distributors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    business_name VARCHAR(200) NOT NULL,
    owner_name VARCHAR(100),
    gst_number VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    service_area TEXT COMMENT 'JSON array of pincodes/cities',
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    commission_percent DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE distributor_retailers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    distributor_id INT NOT NULL,
    retailer_id INT NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (distributor_id) REFERENCES distributors(id) ON DELETE CASCADE,
    FOREIGN KEY (retailer_id) REFERENCES retailers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dist_ret (distributor_id, retailer_id)
);

-- ============================================================
-- 15. INVOICES
-- ============================================================
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    order_punch_id INT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    gst_number VARCHAR(50),
    sub_total DECIMAL(12,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    taxable_amount DECIMAL(12,2) NOT NULL,
    cgst DECIMAL(10,2) DEFAULT 0,
    sgst DECIMAL(10,2) DEFAULT 0,
    igst DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_in_words VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 16. SERVICEABLE PINCODES
-- ============================================================
CREATE TABLE serviceable_pincodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pincode VARCHAR(20) NOT NULL,
    city VARCHAR(100),
    state VARCHAR(100),
    delivery_days INT DEFAULT 3,
    cod_available BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_pincode (pincode)
);

-- ============================================================
-- 17. DOCTOR SCHEDULES
-- ============================================================
CREATE TABLE doctor_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration INT DEFAULT 15 COMMENT 'Minutes per slot',
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- ============================================================
-- 18. HEALTH RECORDS (File-based)
-- ============================================================
CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    record_type ENUM('prescription','lab_report','scan','other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255),
    file_size INT,
    title VARCHAR(200),
    notes TEXT,
    record_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 19. OFFERS & BANNERS
-- ============================================================
CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    banner_image TEXT,
    offer_type ENUM('percent','fixed','buy_get','free_shipping') DEFAULT 'percent',
    offer_value DECIMAL(10,2),
    coupon_code VARCHAR(50),
    applies_to ENUM('all','category','product') DEFAULT 'all',
    applies_value INT,
    valid_from DATETIME,
    valid_to DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 20. USER SESSIONS & ANALYTICS
-- ============================================================
CREATE TABLE page_views (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(200),
    referrer_url VARCHAR(500),
    device_type ENUM('desktop','tablet','mobile') DEFAULT 'desktop',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE search_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    query VARCHAR(300) NOT NULL,
    results_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 21. CONSULTATION TYPES & SPECIALTIES
-- ============================================================
CREATE TABLE consultation_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    price DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0
);

INSERT INTO consultation_types (name, slug, description, icon, price, display_order) VALUES
('General Physician', 'general-physician', 'Common health issues, fever, cold, infections', 'stethoscope', 299, 1),
('Ayurvedic Doctor', 'ayurvedic-doctor', 'Holistic wellness, dosha balancing, herbal treatment', 'spa', 499, 2),
('Specialist Doctor', 'specialist-doctor', 'Cardiology, neurology, dermatology & more', 'biotech', 799, 3),
('Child Specialist', 'child-specialist', 'Pediatric care for children up to 16 years', 'child_care', 399, 4),
('Skin & Hair', 'skin-hair', 'Dermatology, hair care, skin treatment', 'skin', 349, 5);

-- ============================================================
-- 22. ALTER EXISTING TABLES (Enhancements)
-- ============================================================
ALTER TABLE users ADD COLUMN avatar_url TEXT AFTER email_notifications;
ALTER TABLE users ADD COLUMN dob DATE AFTER avatar_url;
ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(12,2) DEFAULT 0 AFTER dob;
ALTER TABLE users ADD COLUMN referral_code VARCHAR(20) UNIQUE AFTER wallet_balance;
ALTER TABLE users ADD COLUMN referred_by INT AFTER referral_code;

ALTER TABLE products ADD COLUMN hsn_code VARCHAR(20) AFTER stock;
ALTER TABLE products ADD COLUMN gst_percent DECIMAL(5,2) DEFAULT 0 AFTER hsn_code;
ALTER TABLE products ADD COLUMN prescription_required BOOLEAN DEFAULT FALSE AFTER gst_percent;
ALTER TABLE products ADD COLUMN manufacturer VARCHAR(200) AFTER prescription_required;
ALTER TABLE products ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER manufacturer;
ALTER TABLE products ADD COLUMN weight DECIMAL(10,3) AFTER is_featured;
ALTER TABLE products ADD COLUMN weight_unit VARCHAR(20) DEFAULT 'g' AFTER weight;

ALTER TABLE doctors ADD COLUMN consultation_types VARCHAR(200) AFTER available;
ALTER TABLE doctors ADD COLUMN clinic_address TEXT AFTER consultation_types;
ALTER TABLE doctors ADD COLUMN clinic_latitude DECIMAL(10,8) AFTER clinic_address;
ALTER TABLE doctors ADD COLUMN clinic_longitude DECIMAL(11,8) AFTER clinic_latitude;

ALTER TABLE orders ADD COLUMN order_type ENUM('retail','wholesale','subscription') DEFAULT 'retail' AFTER status;
ALTER TABLE orders ADD COLUMN delivery_notes TEXT AFTER order_type;
ALTER TABLE orders ADD COLUMN coupon_id INT AFTER delivery_notes;
ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER coupon_id;
ALTER TABLE orders ADD COLUMN wallet_used DECIMAL(10,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE orders ADD COLUMN gst_amount DECIMAL(10,2) DEFAULT 0 AFTER wallet_used;

-- ============================================================
-- 23. INDEXES for Performance
-- ============================================================
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_bestseller ON products(is_bestseller);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX idx_lab_bookings_user ON lab_test_bookings(user_id, status);
CREATE INDEX idx_order_punch_user ON order_punch(user_id, status);
CREATE INDEX idx_return_requests_user ON return_requests(user_id, status);
CREATE INDEX idx_drug_name ON drug_information(name);
CREATE INDEX idx_barcode ON barcodes(barcode);
CREATE INDEX idx_pincode ON serviceable_pincodes(pincode);
CREATE INDEX idx_subscriptions_user ON subscriptions(user_id, status);
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_coupon_code ON coupons(code);
CREATE INDEX idx_ai_sessions_user ON ai_chat_sessions(user_id);
CREATE INDEX idx_page_views_date ON page_views(created_at);

-- ============================================================
-- 24. SEED DATA: Sample Lab Tests
-- ============================================================
INSERT INTO lab_tests (name, slug, category, description, price, discount_price, home_collection, report_time_hours) VALUES
('Complete Blood Count (CBC)', 'cbc', 'Hematology', 'Measures red cells, white cells, hemoglobin, platelets', 499, 399, TRUE, 24),
('Thyroid Profile (T3, T4, TSH)', 'thyroid-profile', 'Hormones', 'Complete thyroid function assessment', 699, 599, TRUE, 48),
('Lipid Profile', 'lipid-profile', 'Cardiology', 'Cholesterol, HDL, LDL, triglycerides', 599, 499, TRUE, 24),
('Blood Sugar (Fasting & PP)', 'blood-sugar', 'Diabetes', 'Fasting and post-prandial blood glucose', 299, 199, TRUE, 24),
('Liver Function Test', 'lft', 'Hepatology', 'SGOT, SGPT, bilirubin, albumin, alkaline phosphatase', 799, 649, TRUE, 48),
('Kidney Function Test', 'kft', 'Nephrology', 'Creatinine, BUN, uric acid, electrolytes', 699, 549, TRUE, 48),
('Vitamin D Total', 'vitamin-d', 'Vitamins', '25-Hydroxy Vitamin D measurement', 1200, 899, TRUE, 72),
('Vitamin B12', 'vitamin-b12', 'Vitamins', 'Active B12 level assessment', 999, 799, TRUE, 48),
('HbA1c (Diabetes Control)', 'hba1c', 'Diabetes', '3-month average blood sugar level', 599, 499, TRUE, 24),
('Full Body Checkup', 'full-body-checkup', 'Wellness', 'Includes CBC, LFT, KFT, Lipid, Thyroid, Vitamin D, B12, Urine', 4999, 2999, TRUE, 72);

-- ============================================================
-- 25. SEED DATA: Sample Drug Information
-- ============================================================
INSERT INTO drug_information (name, generic_name, category, uses, side_effects, precautions, dosage, brand_names) VALUES
('Paracetamol', 'Acetaminophen', 'Pain Relief', 'Fever, mild to moderate pain, headache', 'Nausea, rash (rare), liver damage with overdose', 'Avoid alcohol, max 4g per day for adults', '500-1000mg every 4-6 hours', 'Crocin, Calpol, Dolo'),
('Amoxicillin', 'Amoxicillin', 'Antibiotic', 'Bacterial infections: respiratory, UTI, ear infections', 'Diarrhea, rash, nausea, allergic reactions', 'Complete full course, inform if allergic to penicillin', '250-500mg every 8 hours', 'Amoxil, Mox, Novamox'),
('Omeprazole', 'Omeprazole', 'Gastric', 'Acidity, GERD, gastric ulcers, heartburn', 'Headache, diarrhea, stomach pain', 'Long-term use may cause vitamin B12 deficiency', '20mg once daily before meal', 'Omez, Losec, Prilosec'),
('Cetirizine', 'Cetirizine', 'Allergy', 'Allergic rhinitis, hives, skin allergies', 'Drowsiness, dry mouth, fatigue', 'Avoid driving if drowsy, caution with alcohol', '10mg once daily', 'Cetzine, Zyrtec, Allerid'),
('Metformin', 'Metformin', 'Diabetes', 'Type 2 diabetes mellitus, PCOS', 'Nausea, diarrhea, metallic taste', 'Monitor kidney function, avoid alcohol', '500-1000mg twice daily with meals', 'Glycomet, Metpure, Riomet');

INSERT INTO drug_interactions (drug1_id, drug2_id, severity, description) VALUES
(1, 3, 'minor', 'No significant interaction, but both may cause gastrointestinal discomfort'),
(2, 3, 'moderate', 'Omeprazole may reduce absorption of Amoxicillin'),
(4, 1, 'minor', 'Increased drowsiness possible when combined'),
(5, 1, 'moderate', 'Metformin may reduce effectiveness of Paracetamol metabolism');

-- ============================================================
-- 26. SEED DATA: Offers & Coupons
-- ============================================================
INSERT INTO offers (title, description, offer_type, offer_value, coupon_code, applies_to, display_order) VALUES
('Flat 20% Off on First Order', 'Get 20% discount on your first medicine order', 'percent', 20, 'FIRST20', 'all', 1),
('Free Shipping on Orders ₹500+', 'No delivery charges on orders above ₹500', 'free_shipping', 0, 'FREESHIP', 'all', 2),
('Lab Test Combo Offer', 'Book 2+ lab tests and get 30% off on each', 'percent', 30, 'LAB30', 'category', 3),
('Diabetic Care Pack', '15% off on all diabetes-related products', 'percent', 15, 'DIABETES15', 'category', 4);

INSERT INTO coupons (code, type, value, min_order_amount, max_discount, usage_limit, applies_to, valid_from, valid_to) VALUES
('WELCOME10', 'percent', 10, 299, 100, 10000, 'all', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('SAVE50', 'fixed', 50, 499, 50, 5000, 'all', NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('HEALTHY100', 'fixed', 100, 999, 100, 2000, 'all', NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH)),
('SUMMER20', 'percent', 20, 599, 200, 1000, 'all', NOW(), DATE_ADD(NOW(), INTERVAL 2 MONTH));

INSERT INTO settings (setting_key, setting_value) VALUES
('openai_api_key', ''),
('openai_model', 'gpt-5.2')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
