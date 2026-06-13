-- Feature 1: AI Body Analysis (Dosha Assessment)
CREATE TABLE IF NOT EXISTS dosha_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    category ENUM('vata','pitta','kapha') NOT NULL,
    weight INT DEFAULT 1,
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dosha_assessments (
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

CREATE TABLE IF NOT EXISTS dosha_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value INT NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES dosha_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES dosha_questions(id) ON DELETE CASCADE
);

-- Feature 2: Email Coach (Health Reminders)
CREATE TABLE IF NOT EXISTS health_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','water','yoga','diet') NOT NULL,
    reminder_time TIME NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reminder_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','water','yoga','diet'),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent','failed') DEFAULT 'sent',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feature 3: 90-Day Dashboard (Patient Metrics)
CREATE TABLE IF NOT EXISTS patient_metrics (
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

-- Feature 4: Family Account
CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    relationship ENUM('self','spouse','son','daughter','father','mother','other') DEFAULT 'other',
    age INT,
    gender ENUM('male','female','other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feature 5: Video Consult (Consultations & Prescriptions)
CREATE TABLE IF NOT EXISTS consultations (
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

CREATE TABLE IF NOT EXISTS prescriptions (
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

-- Feature 6: Language Preferences
CREATE TABLE IF NOT EXISTS user_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    language_name VARCHAR(50) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lang (user_id, language_code)
);

-- ALTER existing tables (safe with IF NOT EXISTS check)
SET @precol := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='public_care_ayurveda' AND TABLE_NAME='users' AND COLUMN_NAME='preferred_language');
SET @precol2 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='public_care_ayurveda' AND TABLE_NAME='appointments' AND COLUMN_NAME='consultation_id');

ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'hi' AFTER role;
ALTER TABLE users ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE AFTER preferred_language;
ALTER TABLE appointments ADD COLUMN consultation_id INT DEFAULT NULL AFTER amount;
ALTER TABLE appointments ADD COLUMN meeting_link VARCHAR(500) DEFAULT NULL AFTER consultation_id;

-- Feature 7: Settings (API keys, config stored in DB)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('openai_api_key', ''),
('openai_model', 'gpt-5.2');

-- Seed data: dosha_questions (only if table is empty)
INSERT INTO dosha_questions (question_text, category, weight, display_order) 
SELECT * FROM (
    SELECT 'मेरा शरीर आमतौर पर ठंडा रहता है, खासकर हाथ-पैर' AS q, 'vata' AS c, 2 AS w, 1 AS o UNION ALL
    SELECT 'मेरी नींद हल्की और बेचैन होती है', 'vata', 2, 2 UNION ALL
    SELECT 'मेरा पाचन अनियमित रहता है - कभी ठीक, कभी नहीं', 'vata', 2, 3 UNION ALL
    SELECT 'मेरा शरीर पतला है और वजन बढ़ाना मुश्किल है', 'vata', 2, 4 UNION ALL
    SELECT 'मेरी त्वचा रूखी और खुरदरी है', 'vata', 1, 5 UNION ALL
    SELECT 'मुझे जल्दी याद आता है लेकिन जल्दी भूल भी जाता हूँ', 'vata', 1, 6 UNION ALL
    SELECT 'मेरा शरीर गर्म रहता है और मुझे पसीना जल्दी आता है', 'pitta', 2, 7 UNION ALL
    SELECT 'मेरी भूख तेज होती है और मैं अधिक खा सकता हूँ', 'pitta', 2, 8 UNION ALL
    SELECT 'मुझे गर्मी सहन नहीं होती और मैं ठंडी चीज़ें पसंद करता हूँ', 'pitta', 2, 9 UNION ALL
    SELECT 'मेरा पाचन तेज है - खाना जल्दी पच जाता है', 'pitta', 2, 10 UNION ALL
    SELECT 'मेरी त्वचा संवेदनशील है और जल्दी लाल हो जाती है', 'pitta', 1, 11 UNION ALL
    SELECT 'मैं निर्णय जल्दी लेता हूँ और कभी-कभी चिड़चिड़ा हो जाता हूँ', 'pitta', 1, 12 UNION ALL
    SELECT 'मेरा शरीर भारी और गठीला है', 'kapha', 2, 13 UNION ALL
    SELECT 'मुझे देर तक सोना पसंद है और सुबह उठना मुश्किल होता है', 'kapha', 2, 14 UNION ALL
    SELECT 'मेरा पाचन धीमा है और भोजन के बाद भारीपन रहता है', 'kapha', 2, 15 UNION ALL
    SELECT 'मेरा वजन आसानी से बढ़ जाता है और घटाना मुश्किल है', 'kapha', 2, 16 UNION ALL
    SELECT 'मेरी त्वचा तैलीय और चिकनी है', 'kapha', 1, 17 UNION ALL
    SELECT 'मैं शांत स्वभाव का हूँ और गुस्सा जल्दी नहीं करता', 'kapha', 1, 18
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM dosha_questions LIMIT 1);

-- Checkout/order address columns. Some older local databases have the enhanced
-- payment columns but are missing the canonical shipping fields used by checkout,
-- payment, orders, returns, and tracking pages.
SET @orders_shipping_name := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_name');
SET @sql := IF(@orders_shipping_name = 0, 'ALTER TABLE orders ADD COLUMN shipping_name VARCHAR(100) NULL AFTER shipping', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @orders_shipping_phone := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_phone');
SET @sql := IF(@orders_shipping_phone = 0, 'ALTER TABLE orders ADD COLUMN shipping_phone VARCHAR(20) NULL AFTER shipping_name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @orders_shipping_address := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_address');
SET @sql := IF(@orders_shipping_address = 0, 'ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER shipping_phone', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @orders_shipping_city := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_city');
SET @sql := IF(@orders_shipping_city = 0, 'ALTER TABLE orders ADD COLUMN shipping_city VARCHAR(100) NULL AFTER shipping_address', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @orders_shipping_zip := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_zip');
SET @sql := IF(@orders_shipping_zip = 0, 'ALTER TABLE orders ADD COLUMN shipping_zip VARCHAR(20) NULL AFTER shipping_city', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    order_punch_id INT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    pdf_path VARCHAR(255) NULL,
    email_status ENUM('pending','sent','failed') DEFAULT 'pending',
    email_error TEXT NULL,
    emailed_at TIMESTAMP NULL,
    gst_number VARCHAR(50) NULL,
    sub_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    taxable_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    cgst DECIMAL(10,2) DEFAULT 0.00,
    sgst DECIMAL(10,2) DEFAULT 0.00,
    igst DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    amount_in_words VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

SET @invoices_pdf_path := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'pdf_path');
SET @sql := IF(@invoices_pdf_path = 0, 'ALTER TABLE invoices ADD COLUMN pdf_path VARCHAR(255) NULL AFTER invoice_number', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @invoices_email_status := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'email_status');
SET @sql := IF(@invoices_email_status = 0, 'ALTER TABLE invoices ADD COLUMN email_status ENUM(''pending'',''sent'',''failed'') DEFAULT ''pending'' AFTER pdf_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @invoices_email_error := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'email_error');
SET @sql := IF(@invoices_email_error = 0, 'ALTER TABLE invoices ADD COLUMN email_error TEXT NULL AFTER email_status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @invoices_emailed_at := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'emailed_at');
SET @sql := IF(@invoices_emailed_at = 0, 'ALTER TABLE invoices ADD COLUMN emailed_at TIMESTAMP NULL AFTER email_error', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Feature 8: Email Log (tracks all outgoing emails from PHPMailer)
CREATE TABLE IF NOT EXISTS email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT DEFAULT NULL,
    status ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    error_message TEXT DEFAULT NULL,
    email_type VARCHAR(100) DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    reference_id INT DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (email_type),
    INDEX idx_sent_at (sent_at)
);
