<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'My Wishlist';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'cart' && $product_id > 0) {
        $stmt = $db->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
        if ($stmt) {
            $stmt->bind_param('ii', $uid, $product_id);
            $stmt->execute();
            $_SESSION['cart_count'] = (int)($_SESSION['cart_count'] ?? 0) + 1;
            $notice = 'Item moved to cart.';
        }
    } elseif (($_POST['action'] ?? '') === 'remove' && table_exists($db, 'wishlist')) {
        $stmt = $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
        $stmt->bind_param('ii', $uid, $product_id);
        $stmt->execute();
        $notice = 'Item removed from wishlist.';
    } elseif (($_POST['action'] ?? '') === 'clear' && table_exists($db, 'wishlist')) {
        $stmt = $db->prepare('DELETE FROM wishlist WHERE user_id = ?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $notice = 'Wishlist cleared.';
    }
}

$items = [];
if (table_exists($db, 'wishlist')) {
    $stmt = $db->prepare('SELECT p.*, w.created_at AS wished_at FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.created_at DESC');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
if (!$items) {
    $items = array_map(function ($product) {
        $product['wished_at'] = date('Y-m-d');
        return $product;
    }, array_slice(fetch_products($db, 6), 0, 3));
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
        <h1 class="font-display-lg text-display-lg text-primary">My Wishlist</h1>
        <p class="mt-2 text-on-surface-variant"><?= count($items) ?> saved items ready for later purchase.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <select id="wishlist-sort" class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary"><option>Newest</option><option>Price: Low-High</option><option>Price: High-Low</option><option>Discount</option></select>
        <button id="view-toggle" class="rounded-lg border border-outline-variant px-4 py-2 text-primary"><span class="material-symbols-outlined align-middle">view_list</span></button>
        <button type="button" onclick="navigator.clipboard?.writeText(location.href); this.textContent='Link Copied'" class="rounded-lg border border-outline-variant px-4 py-2 text-primary">Share Wishlist</button>
    </div>
</div>
<?php if ($notice): ?><div class="mb-6 rounded-lg bg-primary-fixed p-4 text-primary"><?= h($notice) ?></div><?php endif; ?>
<?php if (!$items): ?>
<?= empty_state('favorite', 'Your wishlist is empty', 'Save your favourite products here and buy them later.', 'Start Shopping', BASE_URL . '/shop.php') ?>
<?php else: ?>
<div id="wishlist-grid" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
<?php foreach ($items as $item): $discount = !empty($item['compare_price']) && $item['compare_price'] > $item['price'] ? round((($item['compare_price'] - $item['price']) / $item['compare_price']) * 100) : 0; ?>
    <article class="wishlist-card overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest transition hover:-translate-y-1 hover:shadow-lg">
        <div class="relative aspect-square bg-surface-container">
            <img class="h-full w-full object-cover" src="<?= h($item['image_url'] ?: 'assets/uploads/logo.jpeg') ?>" alt="<?= h($item['name']) ?>">
            <?php if ($discount): ?><span class="absolute left-3 top-3 rounded-full bg-error-container px-3 py-1 text-label-sm text-on-error-container"><?= $discount ?>% off</span><?php endif; ?>
            <?php if ((int)($item['stock'] ?? 0) < 5): ?><span class="absolute right-3 top-3 rounded-full bg-tertiary-fixed px-3 py-1 text-label-sm text-on-tertiary-fixed">Low Stock</span><?php endif; ?>
        </div>
        <div class="p-5">
            <h2 class="line-clamp-2 min-h-14 font-title-lg text-title-lg"><?= h($item['name']) ?></h2>
            <div class="mt-3 flex items-center gap-2">
                <span class="font-headline-md text-headline-md text-primary"><?= money($item['price']) ?></span>
                <?php if (!empty($item['compare_price'])): ?><span class="text-on-surface-variant line-through"><?= money($item['compare_price']) ?></span><?php endif; ?>
            </div>
            <p class="mt-2 text-label-sm text-on-surface-variant"><span class="text-tertiary-fixed-dim">★★★★★</span> <?= h($item['rating'] ?? '4.8') ?> (<?= (int)($item['reviews_count'] ?? 0) ?>)</p>
            <p class="mt-2 text-label-sm <?= (int)($item['stock'] ?? 0) > 0 ? 'text-primary' : 'text-error' ?>"><?= (int)($item['stock'] ?? 0) > 0 ? '● In Stock' : '● Out of Stock' ?></p>
            <form method="post" class="mt-5 flex gap-2">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                <button name="action" value="cart" <?= (int)($item['stock'] ?? 0) <= 0 ? 'disabled' : '' ?> class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-on-primary disabled:opacity-50">Add to Cart</button>
                <button name="action" value="remove" class="rounded-lg border border-outline-variant px-3 text-error"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">favorite</span></button>
                <button type="button" data-alert class="rounded-lg border border-outline-variant px-3 text-primary"><span class="material-symbols-outlined">notifications</span></button>
            </form>
        </div>
    </article>
<?php endforeach; ?>
</div>
<form method="post" class="mt-8 flex flex-wrap justify-between gap-3">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
    <button name="action" value="clear" class="rounded-lg border border-error px-5 py-3 text-error">Clear All</button>
    <a href="<?= BASE_URL ?>/shopping-cart.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary">View Cart</a>
</form>
<?php endif; ?>
</section>
<div id="alert-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-xl bg-surface-container-lowest p-6">
        <h3 class="font-headline-md text-headline-md text-primary">Get notified when price drops</h3>
        <div class="my-5 flex flex-wrap gap-2"><button class="rounded-full bg-primary px-4 py-2 text-on-primary">10%</button><button class="rounded-full bg-surface-container px-4 py-2">20%</button><button class="rounded-full bg-surface-container px-4 py-2">30%</button><button class="rounded-full bg-surface-container px-4 py-2">50%</button></div>
        <button onclick="document.getElementById('alert-modal').classList.add('hidden')" class="w-full rounded-lg bg-primary py-3 text-on-primary">Set Alert</button>
    </div>
</div>
<script>
document.querySelectorAll('[data-alert]').forEach(button => button.addEventListener('click', () => document.getElementById('alert-modal').classList.remove('hidden')));
document.getElementById('view-toggle')?.addEventListener('click', () => document.getElementById('wishlist-grid').classList.toggle('lg:grid-cols-1'));
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
