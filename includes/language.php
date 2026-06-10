<?php
/**
 * Lightweight translation system
 * Usage: __('nav_home') — returns translated string or key itself as fallback
 */

function loadTranslations($lang) {
    $file = __DIR__ . '/translations/' . $lang . '.json';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function __($key, $params = []) {
    static $translations = null;
    static $currentLang = null;
    
    $lang = $_SESSION['lang'] ?? 'hi';
    
    if ($translations === null || $currentLang !== $lang) {
        $translations = loadTranslations($lang);
        $currentLang = $lang;
    }
    
    $text = $translations[$key] ?? $key;
    
    if (!empty($params)) {
        foreach ($params as $k => $v) {
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }
    }
    
    return $text;
}

function getAvailableLanguages() {
    return [
        'hi' => ['name' => 'हिन्दी', 'native' => 'हिन्दी', 'flag' => '🇮🇳'],
        'bg' => ['name' => 'हरियाणवी', 'native' => 'हरियाणवी', 'flag' => '🇮🇳'],
        'pa' => ['name' => 'ਪੰਜਾਬੀ', 'native' => 'ਪੰਜਾਬੀ', 'flag' => '🇮🇳'],
        'bho' => ['name' => 'भोजपुरी', 'native' => 'भोजपुरी', 'flag' => '🇮🇳'],
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇬🇧'],
    ];
}
