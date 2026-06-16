<?php
/**
 * Real Multilingual Language Toggle System
 * Handles language detection, session persistence, and translations.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Load translation file for a specific language
 * @param string $lang Language code
 * @return array
 */
function loadTranslations($lang) {
    $lang = preg_replace('/[^a-z0-9_-]/', '', $lang); // Basic security
    $file = __DIR__ . '/translations/' . $lang . '.php';
    if (file_exists($file)) {
        return require $file;
    }
    return [];
}

$allowedLangs = ['en', 'hi', 'har', 'pa', 'bho'];

// Detect language from URL query parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs)) {
    $_SESSION['lang'] = $_GET['lang'];
    
    // Optional: Update user preference in database if logged in
    if (isset($_SESSION['user_id'])) {
        try {
            $langDb = getDB();
            $stmt = $langDb->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
            $stmt->bind_param("si", $_GET['lang'], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // Silently fail if DB is not available or query fails
        }
    }
}

// Set current language, defaulting to English
$currentLang = $_SESSION['lang'] ?? 'en';

// Load translations
$defaultTranslations = loadTranslations('en');
$currentTranslations = ($currentLang === 'en') ? [] : loadTranslations($currentLang);

// Merge translations, with English as fallback for missing keys
$translations = array_merge($defaultTranslations, $currentTranslations);

/**
 * Translation helper function
 * @param string $key The translation key
 * @param array $params Optional parameters to replace in the string (e.g. {{name}})
 * @return string The translated text or the key if not found
 */
function t($key, $params = []) {
    global $translations;
    $text = $translations[$key] ?? $key;
    
    if (!empty($params)) {
        foreach ($params as $k => $v) {
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }
    }
    
    return $text;
}

/**
 * Database Translation helper function
 * @param array $row The database row
 * @param string $field The base field name (e.g. 'name')
 * @return string The translated text or the base field text if not found
 */
function db_t($row, $field) {
    global $currentLang;
    
    if ($currentLang === 'en') {
        return $row[$field] ?? '';
    }
    
    $langField = $field . '_' . $currentLang;
    if (isset($row[$langField]) && trim((string)$row[$langField]) !== '') {
        return $row[$langField];
    }
    
    return $row[$field] ?? '';
}

/**
 * Backward compatibility alias for t()
 */
if (!function_exists('__')) {
    function __($key, $params = []) {
        return t($key, $params);
    }
}

/**
 * Get available languages with metadata
 */
function getAvailableLanguages() {
    return [
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇬🇧', 'html_lang' => 'en'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳', 'html_lang' => 'hi'],
        'har' => ['name' => 'Haryanvi', 'native' => 'हरियाणवी', 'flag' => '🇮🇳', 'html_lang' => 'hi'],
        'pa' => ['name' => 'Punjabi', 'native' => 'ਪੰਜਾਬੀ', 'flag' => '🇮🇳', 'html_lang' => 'pa'],
        'bho' => ['name' => 'Bhojpuri', 'native' => 'भोजपुरी', 'flag' => '🇮🇳', 'html_lang' => 'hi'],
    ];
}

/**
 * Generate URL with language parameter, preserving other query params
 */
function langUrl($lang) {
    $params = $_GET;
    $params['lang'] = $lang;
    $path = strtok($_SERVER["REQUEST_URI"], '?');
    return $path . '?' . http_build_query($params);
}

/**
 * Get CSS class for active language in dropdown
 */
function activeLangClass($lang, $currentLang) {
    return $lang === $currentLang ? 'bg-secondary-container font-bold' : '';
}
