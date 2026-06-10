# 6 Differentiation Features — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add 6 market-differentiating features to Public Care Ayurveda: AI dosha analysis, email health coach, 90-day dashboard, family accounts, video consultations, and multi-language support.

**Architecture:** New DB tables for each feature; new PHP pages under `/` (public) and `/admin/`; reusable components in `/includes/`. All features use existing session auth, CSRF, and prepared statement patterns.

**Tech Stack:** PHP 8.x, MySQL, Tailwind CSS (CDN), PHPMailer for email, simple JS for interactive elements.

---

## DB Migration: New Tables

**Files:**
- Modify: `sql/schema.sql` (append new tables)

```sql
-- 1. DOSHA ASSESSMENT
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

-- 2. HEALTH REMINDERS (Email Coach)
CREATE TABLE health_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','water','yoga','diet') NOT NULL,
    time TIME NOT NULL,
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

-- 3. PATIENT METRICS (90-Day Dashboard)
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

-- 4. FAMILY MEMBERS
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

-- 5. CONSULTATIONS & PRESCRIPTIONS
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

-- 6. LANGUAGE PREFERENCES
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

ALTER TABLE appointments ADD COLUMN consultation_id INT DEFAULT NULL AFTER notes;
ALTER TABLE appointments ADD COLUMN meeting_link VARCHAR(500) DEFAULT NULL AFTER consultation_id;
ALTER TABLE users ADD COLUMN language VARCHAR(10) DEFAULT 'hi' AFTER role;
ALTER TABLE users ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE AFTER language;
```

---

## Feature 1: AI + Ayurveda Body Analysis

**Files:**
- Create: `dosha-quiz.php` (public quiz page)
- Create: `dosha-result.php` (assessment result page)
- Create: `includes/dosha-questions.php` (question bank + scoring logic)
- Create: `admin/dosha-questions.php` (manage questions)
- Modify: `includes/header.php` (add nav link)
- Modify: `admin/includes/sidebar.php` (add admin nav link)

**Quiz Questions (Vata/Pitta/Kapha):**

Sleep questions → Kapha (oversleep), Vata (light/restless), Pitta (sound/moderate)
Digestion questions → Pitta (strong), Vata (irregular), Kapha (slow)
Body temperature → Pitta (hot), Vata (cold hands/feet), Kapha (normal/cool)
Body frame → Vata (thin), Pitta (medium), Kapha (large/sturdy)
Skin type → Vata (dry), Pitta (sensitive/oily), Kapha (oily/smooth)
Memory → Vata (quick forget), Pitta (sharp), Kapha (slow/steady)

---

## Feature 2: Email Health Coach

**Files:**
- Create: `health-coach.php` (manage reminders page)
- Create: `includes/send-reminders.php` (cron-triggered email sender)
- Create: `includes/PHPMailer/` (PHPMailer library)
- Modify: `includes/header.php` (nav link)

**Reminder Types:**
- Medicine: Daily at user-set times
- Water: Every 2 hours (9AM-9PM)
- Yoga: Morning 6AM or evening 6PM
- Diet: Morning (breakfast), afternoon (lunch), evening (dinner)

---

## Feature 3: 90 Day Transformation Dashboard

**Files:**
- Create: `my-health.php` (patient dashboard)
- Create: `includes/charts.php` (chart rendering helper)
- Create: `api/metrics.php` (CRUD API for metrics)
- Modify: `includes/header.php` (nav link)

**Dashboard sections:**
- Weight tracker (line chart)
- Sleep tracker (bar chart)
- Pain score tracker
- BP/Sugar tracker
- Daily log form
- 7-day / 30-day / 90-day view toggle

---

## Feature 4: Family Health Account

**Files:**
- Create: `my-family.php` (manage family members)
- Create: `includes/family-header.php` (family member switcher)
- Modify: `my-health.php` (show family member data)
- Modify: `includes/header.php` (nav link)

---

## Feature 5: Video Doctor Call + Digital Prescriptions

**Files:**
- Create: `video-consult.php` (video call room / booking)
- Create: `admin/prescriptions.php` (manage prescriptions)
- Create: `admin/consultations.php` (manage consultations)
- Modify: `appointment-booking.php` (add video call option)
- Modify: `doctor-profile.php` (add video consult button)
- Modify: `admin/includes/sidebar.php` (admin nav links)

---

## Feature 6: Language-based Service

**Files:**
- Create: `includes/language.php` (translation loader)
- Create: `includes/translations/` (JSON translation files)
- Modify: `includes/config.php` (language initialization)
- Modify: `includes/header.php` (language switcher)

**Languages:**
- Hindi (hi)
- Haryanvi (bg)
- Punjabi (pa)
- Bhojpuri (bho)
- English (en) — fallback
