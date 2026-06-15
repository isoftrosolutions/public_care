-- AyurViora production launch patch
-- Safe to run on an existing production database.
-- It does not drop or truncate tables.
--
-- Recommended:
--   1. Take a production DB backup first.
--   2. Run against the `public_care_ayurveda` database.
--   3. Deploy the matching PHP code after this patch.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- ---------------------------------------------------------------------
-- Brand and launch support settings
-- ---------------------------------------------------------------------

INSERT INTO settings (setting_key, setting_value)
VALUES
    ('site_name', 'AyurViora'),
    ('site_tagline', 'Blood tests, doctor consultation and report explanation in one care flow'),
    ('support_email', 'support@ayurviora.com'),
    ('support_phone', '+91 99999 99999'),
    ('support_whatsapp', '+91 99999 99999'),
    ('support_hours', 'Mon - Sat: 08:00 AM - 08:00 PM'),
    ('emergency_disclaimer', 'AyurViora is not an ambulance or emergency response provider. Call emergency services immediately for urgent symptoms.'),
    ('emergency_national', '112'),
    ('emergency_ambulance', '108'),
    ('emergency_mother_child', '102')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    updated_at = CURRENT_TIMESTAMP;

-- ---------------------------------------------------------------------
-- Report Upload & AI Explanation support
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS health_records (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    record_type ENUM('prescription','lab_report','scan','other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    file_size INT DEFAULT NULL,
    title VARCHAR(200) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    record_date DATE DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT health_records_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE health_records
    ADD COLUMN IF NOT EXISTS file_name VARCHAR(255) DEFAULT NULL AFTER file_path,
    ADD COLUMN IF NOT EXISTS file_size INT DEFAULT NULL AFTER file_name,
    ADD COLUMN IF NOT EXISTS title VARCHAR(200) DEFAULT NULL AFTER file_size,
    ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL AFTER title,
    ADD COLUMN IF NOT EXISTS record_date DATE DEFAULT NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER record_date;

-- ---------------------------------------------------------------------
-- Lab test launch catalogue and fasting/preparation guidance
-- ---------------------------------------------------------------------

ALTER TABLE lab_tests
    ADD COLUMN IF NOT EXISTS includes TEXT DEFAULT NULL COMMENT 'What tests are included' AFTER description,
    ADD COLUMN IF NOT EXISTS preparation_instructions TEXT DEFAULT NULL AFTER includes,
    ADD COLUMN IF NOT EXISTS home_collection TINYINT(1) DEFAULT 1 AFTER discount_price,
    ADD COLUMN IF NOT EXISTS report_time_hours INT DEFAULT 24 AFTER home_collection,
    ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1 AFTER image_url;

INSERT INTO lab_tests
    (name, slug, category, description, includes, preparation_instructions, price, discount_price, home_collection, report_time_hours, image_url, active)
VALUES
    ('Complete Blood Count (CBC)', 'cbc', 'Hematology',
     'Measures red cells, white cells, hemoglobin, hematocrit and platelets.',
     'Hemoglobin, RBC, WBC, platelet count and differential counts.',
     'Fasting is not required. Drink water normally before sample collection.',
     499.00, 399.00, 1, 24, NULL, 1),
    ('Blood Sugar (Fasting & PP)', 'blood-sugar', 'Diabetes',
     'Checks fasting and post-prandial blood glucose levels.',
     'Fasting blood sugar and post-prandial blood sugar.',
     'Fasting sample needs 8-12 hours fasting. Post-prandial sample is collected 2 hours after meal.',
     299.00, 199.00, 1, 24, NULL, 1),
    ('Thyroid Profile (T3, T4, TSH)', 'thyroid-profile', 'Hormones',
     'Assesses thyroid hormone balance and thyroid stimulating hormone.',
     'T3, T4 and TSH.',
     'Fasting is not required. Inform the team if you take thyroid medicine.',
     699.00, 599.00, 1, 48, NULL, 1),
    ('Lipid Profile', 'lipid-profile', 'Cardiology',
     'Checks cholesterol, HDL, LDL, VLDL and triglycerides.',
     'Total cholesterol, HDL, LDL, VLDL and triglycerides.',
     'Fasting for 8-12 hours is recommended unless your doctor advises otherwise.',
     599.00, 499.00, 1, 24, NULL, 1),
    ('Liver Function Test', 'lft', 'Hepatology',
     'Checks common liver enzymes, bilirubin and protein markers.',
     'SGOT, SGPT, bilirubin, albumin, alkaline phosphatase and related markers.',
     'Fasting for 8-12 hours is recommended for best sample quality.',
     799.00, 649.00, 1, 48, NULL, 1),
    ('Kidney Function Test', 'kft', 'Nephrology',
     'Checks kidney health using creatinine, urea, uric acid and electrolytes.',
     'Creatinine, BUN/urea, uric acid and electrolytes.',
     'Fasting for 8-12 hours is recommended. Drink water normally unless restricted by your doctor.',
     699.00, 549.00, 1, 48, NULL, 1),
    ('Vitamin D Total', 'vitamin-d', 'Vitamins',
     'Measures 25-Hydroxy Vitamin D level.',
     '25-Hydroxy Vitamin D.',
     'Fasting is not required.',
     1200.00, 899.00, 1, 72, NULL, 1),
    ('Vitamin B12', 'vitamin-b12', 'Vitamins',
     'Measures Vitamin B12 level for deficiency screening.',
     'Vitamin B12.',
     'Fasting is not required.',
     999.00, 799.00, 1, 48, NULL, 1),
    ('HbA1c (Diabetes Control)', 'hba1c', 'Diabetes',
     'Shows average blood sugar control over the last 2-3 months.',
     'HbA1c.',
     'Fasting is not required.',
     599.00, 499.00, 1, 24, NULL, 1),
    ('Full Body Checkup', 'full-body-checkup', 'Wellness',
     'Comprehensive wellness screening package for routine health review.',
     'CBC, LFT, KFT, Lipid Profile, Thyroid Profile, Vitamin D, Vitamin B12 and Urine Routine.',
     'Fasting for 8-12 hours is recommended because this package includes lipid and sugar markers.',
     4999.00, 2999.00, 1, 72, NULL, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    description = VALUES(description),
    includes = VALUES(includes),
    preparation_instructions = VALUES(preparation_instructions),
    price = VALUES(price),
    discount_price = VALUES(discount_price),
    home_collection = VALUES(home_collection),
    report_time_hours = VALUES(report_time_hours),
    active = VALUES(active);

-- ---------------------------------------------------------------------
-- Launch health articles
-- ---------------------------------------------------------------------

INSERT INTO blog_posts
    (title, slug, category, excerpt, content, image_url, author, published_at)
VALUES
    ('Diabetes se bachav: daily habits that protect blood sugar',
     'diabetes-se-bachav',
     'Diabetes Care',
     'Simple diet, walking, sleep and regular testing habits that help reduce diabetes risk.',
     'Diabetes prevention starts with daily habits: balanced meals, regular walking, good sleep, weight control and periodic blood sugar testing. People with family history, high weight, frequent thirst, fatigue or slow wound healing should book testing and consult a doctor.',
     'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=900&q=80',
     'AyurViora Care Team',
     CURRENT_TIMESTAMP),
    ('High BP kya hai? Symptoms, risks and when to see a doctor',
     'high-bp-kya-hai',
     'Heart Health',
     'Understand blood pressure readings, warning signs and lifestyle steps for safer BP control.',
     'High blood pressure often has no obvious symptoms, but it increases risk for heart, kidney and stroke problems. Regular BP checks, lower salt intake, exercise, stress control and doctor follow-up are important. Seek urgent care for chest pain, severe headache, breathlessness or weakness.',
     'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=900&q=80',
     'AyurViora Care Team',
     CURRENT_TIMESTAMP + INTERVAL 1 SECOND),
    ('Pregnancy diet: safe nutrition tips for expecting mothers',
     'pregnancy-diet-safe-nutrition-tips',
     'Pregnancy Care',
     'Balanced meal ideas, hydration, supplements and foods to discuss with your doctor.',
     'Pregnancy nutrition should focus on balanced home meals, hydration, protein, iron, folate, calcium and safe snacks. Avoid self-medication and discuss supplements, vomiting, swelling, bleeding, severe pain or reduced baby movement with a qualified doctor immediately.',
     'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=900&q=80',
     'AyurViora Care Team',
     CURRENT_TIMESTAMP + INTERVAL 2 SECOND),
    ('Dengue ke lakshan: fever warning signs you should not ignore',
     'dengue-ke-lakshan',
     'Seasonal Health',
     'Common dengue symptoms, hydration guidance and when urgent medical care is needed.',
     'Dengue may cause high fever, body pain, headache, rash, nausea or weakness. Warning signs include bleeding, severe abdominal pain, persistent vomiting, drowsiness or breathlessness. Do not self-medicate with painkillers; consult a doctor and test as advised.',
     'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=900&q=80',
     'AyurViora Care Team',
     CURRENT_TIMESTAMP + INTERVAL 3 SECOND),
    ('Monsoon health tips for immunity, digestion and infection prevention',
     'monsoon-health-tips',
     'Monsoon Care',
     'Practical rainy-season precautions for food, water, mosquitoes and everyday wellness.',
     'During monsoon, use safe drinking water, avoid stale food, protect against mosquitoes, keep feet dry and seek care for fever or stomach infection symptoms. Seasonal wellness is about prevention, hygiene and timely testing.',
     'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=900&q=80',
     'AyurViora Care Team',
     CURRENT_TIMESTAMP + INTERVAL 4 SECOND)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    category = VALUES(category),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    image_url = VALUES(image_url),
    author = VALUES(author),
    published_at = VALUES(published_at);

-- If older test placeholder posts exist, hide the blank placeholder feel by filling them with real launch content.
UPDATE blog_posts
SET
    category = 'General Health',
    excerpt = COALESCE(NULLIF(excerpt, ''), 'AyurViora health article for practical everyday care.'),
    author = COALESCE(NULLIF(author, ''), 'AyurViora Care Team')
WHERE title LIKE 'first-%';

-- ---------------------------------------------------------------------
-- Optional operational notes:
-- - Ensure the web server can write to: uploads/reports
-- - Deploy upload-report.php and matching navigation/footer code.
-- ---------------------------------------------------------------------

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
