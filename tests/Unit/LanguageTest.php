<?php

namespace Ayurviro\Tests\Unit;

use PHPUnit\Framework\TestCase;

// Define BASE_URL if needed by includes
if (!defined('BASE_URL')) define('BASE_URL', '');

require_once __DIR__ . '/../../includes/language.php';

class LanguageTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['lang'] = 'hi';
    }

    public function test_loadTranslations_returns_array_for_valid_lang(): void
    {
        $result = loadTranslations('hi');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('nav_home', $result);
        $this->assertSame('होम', $result['nav_home']);
    }

    public function test_loadTranslations_returns_empty_array_for_invalid_lang(): void
    {
        $this->assertSame([], loadTranslations('xx'));
    }

    public function test_loadTranslations_returns_english(): void
    {
        $result = loadTranslations('en');
        $this->assertSame('Home', $result['nav_home']);
    }

    public function test_loadTranslations_returns_punjabi(): void
    {
        $result = loadTranslations('pa');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('nav_home', $result);
        $this->assertSame('ਮੁੱਖ ਪੰਨਾ', $result['nav_home']);
    }

    public function test_loadTranslations_returns_haryanvi(): void
    {
        $result = loadTranslations('har');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('nav_home', $result);
        $this->assertSame('मुख पन्ना', $result['nav_home']);
    }

    public function test_loadTranslations_returns_bhojpuri(): void
    {
        $result = loadTranslations('bho');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('nav_home', $result);
        $this->assertSame('मुखपन्ना', $result['nav_home']);
    }

    public function test_t_returns_translation_for_known_key(): void
    {
        // We need to trigger the merge logic which happens on file include
        // Since it's already included, we can't easily re-run the global merge
        // but we can test the fallback to English if HI is missing keys.
        // Actually, the 't' function uses global $translations.
        global $translations;
        $translations = array_merge(loadTranslations('en'), loadTranslations('hi'));
        $this->assertSame('होम', t('nav_home'));
    }

    public function test_t_returns_english_when_lang_is_en(): void
    {
        global $translations;
        $translations = loadTranslations('en');
        $this->assertSame('Home', t('nav_home'));
    }

    public function test_t_returns_key_as_fallback_for_unknown_key(): void
    {
        $this->assertSame('nonexistent_key_xyz', t('nonexistent_key_xyz'));
    }

    public function test_t_supports_template_params(): void
    {
        global $translations;
        $translations['test_param'] = 'Hello {{name}}';
        $result = t('test_param', ['name' => 'World']);
        $this->assertSame('Hello World', $result);
    }

    public function test_getAvailableLanguages_returns_all_five(): void
    {
        $langs = getAvailableLanguages();
        $codes = array_keys($langs);
        sort($codes);
        $this->assertSame(['bho', 'en', 'har', 'hi', 'pa'], $codes);
    }

    public function test_getAvailableLanguages_contains_expected_structure(): void
    {
        $langs = getAvailableLanguages();
        $this->assertArrayHasKey('name', $langs['hi']);
        $this->assertArrayHasKey('native', $langs['hi']);
        $this->assertArrayHasKey('flag', $langs['hi']);
        $this->assertSame('Hindi', $langs['hi']['name']); // In metadata it's 'Hindi'
        $this->assertSame('हिन्दी', $langs['hi']['native']);
        $this->assertSame('English', $langs['en']['name']);
    }
}
