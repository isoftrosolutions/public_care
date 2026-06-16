-- ============================================================
-- Multilingual Support Patch
-- Only adds what's missing from the live DB (ektamultp_ayurviora)
-- Already present in live DB: products.*_hi/*_har/*_pa/*_bho, users.preferred_language
-- ============================================================

-- 1. Lab Tests Translation Fields (MISSING in live DB)
ALTER TABLE lab_tests
ADD COLUMN name_hi VARCHAR(200) AFTER name,
ADD COLUMN name_har VARCHAR(200) AFTER name_hi,
ADD COLUMN name_pa VARCHAR(200) AFTER name_har,
ADD COLUMN name_bho VARCHAR(200) AFTER name_pa,
ADD COLUMN description_hi TEXT AFTER description,
ADD COLUMN description_har TEXT AFTER description_hi,
ADD COLUMN description_pa TEXT AFTER description_har,
ADD COLUMN description_bho TEXT AFTER description_pa;

-- 2. Categories Translation Fields (MISSING in live DB)
ALTER TABLE categories
ADD COLUMN name_hi VARCHAR(100) AFTER name,
ADD COLUMN name_har VARCHAR(100) AFTER name_hi,
ADD COLUMN name_pa VARCHAR(100) AFTER name_har,
ADD COLUMN name_bho VARCHAR(100) AFTER name_pa;
