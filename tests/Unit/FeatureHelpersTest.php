<?php

namespace Ayurviro\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/feature_helpers.php';

class FeatureHelpersTest extends TestCase
{
    public function test_h_encodes_special_chars(): void
    {
        $this->assertSame('&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;', h("<script>alert('xss')</script>"));
    }

    public function test_h_encodes_ampersands(): void
    {
        $this->assertSame('AT&amp;T &amp; Sony', h('AT&T & Sony'));
    }

    public function test_h_returns_empty_string_for_null(): void
    {
        $this->assertSame('', h(null));
    }

    public function test_h_passes_plain_text_through(): void
    {
        $this->assertSame('Hello World', h('Hello World'));
    }

    public function test_money_formats_inr(): void
    {
        $this->assertSame('₹1,234.00', money(1234));
    }

    public function test_money_formats_decimals(): void
    {
        $this->assertSame('₹49.99', money(49.99));
    }

    public function test_money_formats_zero(): void
    {
        $this->assertSame('₹0.00', money(0));
    }

    public function test_money_formats_large_number(): void
    {
        $this->assertSame('₹100,000.00', money(100000));
    }

    public function test_money_rounds_to_two_decimals(): void
    {
        $this->assertSame('₹10.33', money(10.3333));
    }

    public function test_money_formats_string_number(): void
    {
        $this->assertSame('₹50.00', money('50'));
    }

    public function test_empty_state_contains_icon(): void
    {
        $html = empty_state('inbox', 'Empty', 'Nothing here');
        $this->assertStringContainsString('inbox', $html);
    }

    public function test_empty_state_contains_title_and_body(): void
    {
        $html = empty_state('search', 'No Results', 'Try a different search');
        $this->assertStringContainsString('No Results', $html);
        $this->assertStringContainsString('Try a different search', $html);
    }

    public function test_empty_state_with_cta_has_link(): void
    {
        $html = empty_state('cart', 'Cart Empty', 'Add items', 'Shop Now', '/shop.php');
        $this->assertStringContainsString('Shop Now', $html);
        $this->assertStringContainsString('/shop.php', $html);
    }

    public function test_empty_state_without_cta_has_no_link(): void
    {
        $html = empty_state('info', 'Title', 'Body');
        $this->assertStringNotContainsString('inline-flex', $html);
    }

    public function test_empty_state_escapes_xss(): void
    {
        $html = empty_state('warning', '<script>alert(1)</script>', 'body', 'CTA', '/url');
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_stat_card_contains_label_and_value(): void
    {
        $html = stat_card('people', 'Users', '1,234', '+12%');
        $this->assertStringContainsString('Users', $html);
        $this->assertStringContainsString('1,234', $html);
        $this->assertStringContainsString('+12%', $html);
        $this->assertStringContainsString('people', $html);
    }

    public function test_stat_card_without_hint(): void
    {
        $html = stat_card('star', 'Rating', '4.8');
        $this->assertStringContainsString('Rating', $html);
        $this->assertStringContainsString('4.8', $html);
    }

    public function test_stat_card_escapes_xss(): void
    {
        $html = stat_card('<img>', '<b>Label</b>', '<i>Value</i>', '<a>Hint</a>');
        $this->assertStringNotContainsString('<img>', $html);
        $this->assertStringNotContainsString('<b>Label</b>', $html);
        $this->assertStringNotContainsString('<i>Value</i>', $html);
        $this->assertStringNotContainsString('<a>Hint</a>', $html);
    }
}
