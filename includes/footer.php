</main>

<?php
$bottom_nav_items = [
    [
        'label' => t('nav_home'),
        'icon' => 'home',
        'url' => BASE_URL . '/index.php',
        'active' => $current_page === 'index.php',
    ],
    [
        'label' => t('nav_medicine'),
        'icon' => 'medication',
        'url' => BASE_URL . '/shop.php',
        'active' => in_array($current_page, ['shop.php', 'product-details.php', 'shopping-cart.php', 'checkout.php', 'payment.php'], true),
    ],
    [
        'label' => t('nav_consult'),
        'icon' => 'stethoscope',
        'url' => BASE_URL . '/doctor-listing.php',
        'active' => in_array($current_page, ['doctor-listing.php', 'doctor-profile.php', 'appointment-booking.php', 'video-consult.php'], true),
    ],
    [
        'label' => t('nav_ai_health'),
        'icon' => 'psychology',
        'url' => BASE_URL . '/ai-assistant.php',
        'active' => in_array($current_page, ['ai-assistant.php', 'dosha-quiz.php', 'dosha-result.php', 'health-coach.php'], true),
    ],
    [
        'label' => t('nav_cart'),
        'icon' => 'shopping_cart',
        'url' => BASE_URL . '/shopping-cart.php',
        'active' => $current_page === 'shopping-cart.php',
        'badge' => $_SESSION['cart_count'] ?? 0,
    ],
];
?>

<nav class="mobile-bottom-nav md:hidden fixed left-3 right-3 bottom-3 z-[80] rounded-[1.35rem] border border-outline-variant/40 bg-white/94 backdrop-blur-xl shadow-[0_16px_48px_rgba(0,82,33,0.18)] px-2 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]" aria-label="Mobile primary navigation">
<div class="grid grid-cols-5 gap-1">
<?php foreach ($bottom_nav_items as $item): ?>
<a href="<?= htmlspecialchars($item['url']) ?>" class="relative flex min-h-[58px] flex-col items-center justify-center gap-1 rounded-2xl px-1 text-[10px] font-bold transition-all <?= $item['active'] ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' ?>" aria-current="<?= $item['active'] ? 'page' : 'false' ?>">
<span class="material-symbols-outlined text-[23px]" style="<?= $item['active'] ? "font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;" : '' ?>"><?= htmlspecialchars($item['icon']) ?></span>
<span class="leading-none"><?= htmlspecialchars($item['label']) ?></span>
<?php if (isset($item['badge'])): ?>
<span id="mobile-cart-count-badge" class="cart-count-badge absolute right-2 top-1 min-w-[17px] h-[17px] rounded-full bg-tertiary-fixed px-1 text-[10px] font-bold leading-[17px] text-on-tertiary-fixed <?= ((int)$item['badge'] > 0) ? '' : 'hidden' ?>"><?= htmlspecialchars($item['badge']) ?></span>
<?php endif; ?>
</a>
<?php endforeach; ?>
</div>
</nav>

<footer class="hidden md:block bg-surface-container-high dark:bg-surface-dim w-full">
<div class="w-full px-base md:px-margin-desktop py-16 grid grid-cols-1 md:grid-cols-4 gap-gutter max-w-container-max mx-auto">
<div class="space-y-4">
<h3 class="font-headline-md text-headline-md font-bold text-primary dark:text-inverse-primary"><?= SITE_NAME ?></h3>
<p class="font-body-md text-body-md text-on-surface-variant"><?= t('footer_partner_text') ?></p>
<div class="flex gap-3 pt-2">
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="<?= BASE_URL ?>/"><span class="material-symbols-outlined">public</span></a>
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="mailto:sharmasumolishyam@gmail.com"><span class="material-symbols-outlined">alternate_email</span></a>
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="tel:+919899784504"><span class="material-symbols-outlined">call</span></a>
</div>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold"><?= t('footer_quick_links') ?></h4>
<ul class="space-y-2">
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/shop.php"><?= t('footer_shop_all') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/order-punch.php"><?= t('footer_order_punch') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/lab-tests.php"><?= t('book_blood_test') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/doctor-listing.php"><?= t('nav_consult') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/about-us.php"><?= t('footer_about_us') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/wellness-blog.php"><?= t('footer_wellness_blog') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/write-review.php"><?= t('write_review') ?></a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold"><?= t('footer_support') ?></h4>
<ul class="space-y-2">
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/contact-us.php"><?= t('btn_contact_us') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/contact-us.php#emergency-help"><?= t('footer_emergency_help') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/returns.php"><?= t('footer_shipping_returns') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/privacy-policy.php"><?= t('footer_privacy_policy') ?></a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/terms-of-service.php"><?= t('footer_terms_service') ?></a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold"><?= t('footer_newsletter') ?></h4>
<p class="font-body-md text-body-md text-on-surface-variant"><?= t('footer_subscribe_text') ?></p>
<div class="flex gap-2">
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg flex-grow px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md text-body-md" placeholder="<?= t('email_placeholder') ?>" type="email"/>
<button class="bg-primary text-on-primary p-2 rounded-lg hover:bg-primary-container transition-all"><span class="material-symbols-outlined">send</span></button>
</div>
</div>
</div>
<div class="border-t border-outline-variant/30 py-8 px-base md:px-margin-desktop text-center">
<p class="font-body-md text-body-md text-on-surface-variant"><?= t('footer_copyright') ?> <?= t('footer_tagline') ?></p>
</div>
</footer>

<script>
function syncCartBadges(value) {
    const count = parseInt(value || '0', 10) || 0;
    document.querySelectorAll('.cart-count-badge').forEach((badge) => {
        badge.textContent = count;
        badge.classList.toggle('hidden', count <= 0 && badge.id === 'mobile-cart-count-badge');
    });
}

const headerCartBadge = document.getElementById('cart-count-badge');
if (headerCartBadge) {
    syncCartBadges(headerCartBadge.textContent);
    new MutationObserver(() => syncCartBadges(headerCartBadge.textContent)).observe(headerCartBadge, {
        childList: true,
        characterData: true,
        subtree: true
    });
}

document.querySelectorAll('section > div:not(.no-scroll-animation), .animate-on-scroll').forEach(el => {
el.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-10');
const observer = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
entry.target.classList.add('opacity-100', 'translate-y-0');
entry.target.classList.remove('opacity-0', 'translate-y-10');
observer.unobserve(entry.target);
}
});
}, { threshold: 0.01, rootMargin: '0px 0px -24px 0px' });
observer.observe(el);
window.setTimeout(() => {
    if (el.classList.contains('opacity-0')) {
        el.classList.add('opacity-100', 'translate-y-0');
        el.classList.remove('opacity-0', 'translate-y-10');
    }
}, 900);
});
</script>
<?php require_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
