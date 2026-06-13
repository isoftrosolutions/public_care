<?php

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = BASE_URL . '/' . basename($_SERVER['SCRIPT_NAME']);
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function table_exists(mysqli $db, string $table): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

function column_exists(mysqli $db, string $table, string $column): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return '₹' . number_format((float)$amount, 2);
}

function current_user(mysqli $db): array
{
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $uid = (int)$_SESSION['user_id'];
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();
    return $user;
}

function fetch_products(mysqli $db, int $limit = 8): array
{
    if (!table_exists($db, 'products')) {
        return [];
    }
    $limit = max(1, min(24, $limit));
    $result = $db->query("SELECT * FROM products ORDER BY is_bestseller DESC, created_at DESC LIMIT $limit");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_cart_items(mysqli $db, int $uid): array
{
    if (!table_exists($db, 'cart') || !table_exists($db, 'products')) {
        return [];
    }
    $stmt = $db->prepare('SELECT c.*, p.name, p.price, p.compare_price, p.image_url, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

function empty_state(string $icon, string $title, string $body, string $cta = '', string $href = '#'): string
{
    $button = $cta ? '<a href="' . h($href) . '" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-on-primary font-label-lg">' . h($cta) . '<span class="material-symbols-outlined text-lg">arrow_forward</span></a>' : '';
    return '<div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-10 text-center">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary-fixed text-primary"><span class="material-symbols-outlined text-3xl">' . h($icon) . '</span></div>
        <h3 class="font-headline-md text-headline-md text-primary">' . h($title) . '</h3>
        <p class="mx-auto mt-2 mb-6 max-w-xl text-on-surface-variant">' . h($body) . '</p>' . $button . '
    </div>';
}

function stat_card(string $icon, string $label, string $value, string $hint = ''): string
{
    return '<div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5">
        <div class="mb-4 flex items-center justify-between"><span class="material-symbols-outlined rounded-lg bg-primary-fixed p-2 text-primary">' . h($icon) . '</span><span class="text-label-sm text-primary">' . h($hint) . '</span></div>
        <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">' . h($label) . '</p>
        <p class="mt-1 font-headline-lg text-headline-lg text-on-surface">' . h($value) . '</p>
    </div>';
}
