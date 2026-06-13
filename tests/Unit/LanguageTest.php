<?php

namespace Ayurviro\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/language.php';

class LanguageTest extends TestCase
{
    protected function setUp(): void
    {
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
    }

    public function test_loadTranslations_returns_haryanvi(): void
    {
        $result = loadTranslations('bg');
        $this->assertIsArray($result);
    }

    public function test_loadTranslations_returns_bhojpuri(): void
    {
        $result = loadTranslations('bho');
        $this->assertIsArray($result);
    }

    public function test___returns_translation_for_known_key(): void
    {
        $_SESSION['lang'] = 'hi';
        $this->assertSame('होम', __('nav_home'));
    }

    public function test___returns_english_when_lang_is_en(): void
    {
        $_SESSION['lang'] = 'en';
        $this->assertSame('Home', __('nav_home'));
    }

    public function test___returns_key_as_fallback_for_unknown_key(): void
    {
        $this->assertSame('nonexistent_key_xyz', __('nonexistent_key_xyz'));
    }

    public function test___defaults_to_hindi_when_no_lang_set(): void
    {
        unset($_SESSION['lang']);
        $this->assertSame('होम', __('nav_home'));
        $_SESSION['lang'] = 'hi';
    }

    public function test___supports_template_params(): void
    {
        $result = __('auth_login_title');
        $this->assertSame('वापसी पर स्वागत है', $result);
    }

    public function test_getAvailableLanguages_returns_all_five(): void
    {
        $langs = getAvailableLanguages();
        $codes = array_keys($langs);
        sort($codes);
        $this->assertSame(['bg', 'bho', 'en', 'hi', 'pa'], $codes);
    }

    public function test_getAvailableLanguages_contains_expected_structure(): void
    {
        $langs = getAvailableLanguages();
        $this->assertArrayHasKey('name', $langs['hi']);
        $this->assertArrayHasKey('native', $langs['hi']);
        $this->assertArrayHasKey('flag', $langs['hi']);
        $this->assertSame('हिन्दी', $langs['hi']['name']);
        $this->assertSame('English', $langs['en']['name']);
    }

    public function test_getAvailableLanguages_hindi_is_first(): void
    {
        $langs = getAvailableLanguages();
        $this->assertSame('hi', array_key_first($langs));
    }
}
