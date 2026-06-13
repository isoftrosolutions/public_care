<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="pt-24 space-y-16 pb-24">

<!-- Hero Section -->
<section class="max-w-container-max mx-auto px-margin-desktop">
<div class="relative w-full rounded-xl overflow-hidden shadow-lg h-[640px]">
<img class="absolute inset-0 w-full h-full object-cover" data-alt="A premium wide shot of a modern Ayurvedic wellness setup featuring a wooden mortar and pestle surrounded by fresh green herbs, turmeric roots, and sleek dark glass apothecary bottles. The background is a clean, bright, off-white minimalist space with soft natural morning light creating gentle shadows. The overall aesthetic is professional, corporate modern, and deeply rooted in nature with a palette of forest greens and warm earthy tones." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBo11Xq4d49gQxM0_vENu4Qofi6nZ3iRK1tr--Pdm22dy9TiiDv7FYFz_MlDipFOceI4NAmcK4eohhVsVjx6WfD59jYTP9Tr_CDF3zS1oFCS-QG8L4BZEfoSnUi35osHRACMq1HJlWMrfoj9h84U-fkT-VXFC6Ih9H-kSzgP6mAn_sDA5ai6r6c9rSkZRehbsZEYxeTrxBF5yRpUf4OBK1mpfg-IdYRGDHG2Sh0ho53ntdBRKHWo0HFk8rbj9w5qJ9FYeQ6EJhQJbw">
<div class="absolute inset-0 bg-gradient-to-r from-on-primary-fixed/60 to-transparent flex items-center bg-black/20">
<div class="px-16 space-y-6 max-w-2xl">
<h1 class="font-display-lg text-on-primary leading-tight text-6xl">Natural Care,<br>Ayurvedic Cure</h1>
<p class="font-headline-md text-headline-md text-primary-fixed">Up to 30% OFF</p>
<p class="font-body-lg text-body-lg text-on-primary opacity-90 max-w-md">Experience the fusion of ancient herbal wisdom and modern science for a healthier, vibrant tomorrow.</p>
<div class="pt-4">
<a class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary px-10 py-4 rounded-lg font-label-lg text-label-lg transition-all active:scale-95 shadow-md inline-block" href="<?= BASE_URL ?>/shop.php">Shop Now</a>
</div>
</div>
</div>
</div>
</section>

<!-- Category Quick-Links -->
<section class="max-w-container-max mx-auto px-margin-desktop overflow-hidden">
<div class="flex justify-between items-center mb-8">
<h2 class="font-headline-lg text-headline-lg text-on-surface">Shop by Category</h2>
<a class="text-primary font-label-lg text-label-lg hover:underline" href="<?= BASE_URL ?>/shop.php">View All</a>
</div>
<div class="flex gap-12 overflow-x-auto pb-4 hide-scrollbar">
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">oil_barrel</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Oil</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">medication</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Capsules</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">nutrition</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Churna</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">vaccines</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Syrup</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">water_drop</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Juice</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">grain</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Powder</span>
</div>
<div class="flex flex-col items-center gap-3 group cursor-pointer shrink-0">
<div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center border-2 border-transparent group-hover:border-primary transition-all duration-300">
<span class="material-symbols-outlined text-primary text-3xl">emoji_food_beverage</span>
</div>
<span class="font-label-lg text-label-lg group-hover:text-primary transition-colors">Herbal Tea</span>
</div>
</div>
</section>

<!-- Trust Signals Bar -->
<section class="w-full bg-secondary-container/30 py-8">
<div class="max-w-container-max mx-auto px-margin-desktop flex flex-wrap justify-between gap-8">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary text-3xl">verified</span>
<div>
<p class="font-label-lg text-label-lg text-on-surface">100% Ayurvedic &amp; Natural</p>
<p class="text-label-sm text-on-surface-variant">Pure, potent formulations</p>
</div>
</div>
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary text-3xl">shield_lock</span>
<div>
<p class="font-label-lg text-label-lg text-on-surface">No Side Effects</p>
<p class="text-label-sm text-on-surface-variant">Gentle on your body</p>
</div>
</div>
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary text-3xl">groups</span>
<div>
<p class="font-label-lg text-label-lg text-on-surface">Trusted by Thousands</p>
<p class="text-label-sm text-on-surface-variant">Over 50,000+ happy lives</p>
</div>
</div>
</div>
</section>

<!-- Best Sellers -->
<?php
$conn = getDB();
$featured_query = "SELECT * FROM products WHERE is_bestseller = TRUE ORDER BY created_at DESC LIMIT 4";
$featured_result = $conn->query($featured_query);
$featured_products = [];
if ($featured_result && $featured_result->num_rows > 0) {
    while ($row = $featured_result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}
?>
<section class="max-w-container-max mx-auto px-margin-desktop">
<div class="flex justify-between items-center mb-8">
<h2 class="font-headline-lg text-headline-lg text-on-surface">Our Bestsellers</h2>
<a class="text-primary font-label-lg text-label-lg hover:underline" href="<?= BASE_URL ?>/shop.php">View All Products</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
<?php if (count($featured_products) > 0): ?>
<?php foreach ($featured_products as $product): ?>
<?php $discount = ($product['compare_price'] > 0) ? round((1 - $product['price'] / $product['compare_price']) * 100) : 0; ?>
<div class="bg-surface-container-lowest rounded-xl p-4 hover-lift group border border-outline-variant/30 relative">
<?php if ($product['is_bestseller']): ?>
<span class="absolute top-4 left-4 bg-tertiary-fixed text-on-tertiary-fixed text-label-sm px-2 py-1 rounded-full z-10">Bestseller</span>
<?php endif; ?>
<div class="h-64 rounded-lg overflow-hidden mb-4 bg-surface flex items-center justify-center">
<img class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
</div>
<h3 class="font-title-lg text-title-lg text-on-surface mb-1"><?= htmlspecialchars($product['name']) ?></h3>
<div class="flex items-center gap-2 mb-3">
<div class="flex text-tertiary-fixed-dim">
<?php $full_stars = round($product['rating']); for ($i = 1; $i <= 5; $i++): ?>
<span class="material-symbols-outlined text-lg" style="<?= $i <= $full_stars ? "font-variation-settings: 'FILL' 1;" : '' ?>"><?= $i <= $full_stars ? 'star' : ($i - 0.5 <= $product['rating'] ? 'star_half' : 'star') ?></span>
<?php endfor; ?>
</div>
<span class="text-label-sm text-on-surface-variant">(<?= number_format((int)$product['reviews_count']) ?>)</span>
</div>
<div class="flex items-end gap-2 mb-6">
<span class="font-headline-md text-headline-md text-primary">₹<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-label-lg text-on-surface-variant line-through mb-1">₹<?= number_format($product['compare_price'], 2) ?></span>
<span class="text-label-sm font-bold text-error mb-1"><?= $discount ?>% OFF</span>
<?php endif; ?>
</div>
<button class="w-full py-3 border border-primary text-primary font-label-lg rounded-lg hover:bg-primary hover:text-on-primary transition-all add-to-cart" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
</div>
<?php endforeach; ?>
<?php else: ?>
<?php
$fallback_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$fallback_result = $conn->query($fallback_query);
if ($fallback_result && $fallback_result->num_rows > 0) {
    while ($row = $fallback_result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}
?>
<?php if (count($featured_products) > 0): ?>
<?php foreach ($featured_products as $product): ?>
<?php $discount = ($product['compare_price'] > 0) ? round((1 - $product['price'] / $product['compare_price']) * 100) : 0; ?>
<div class="bg-surface-container-lowest rounded-xl p-4 hover-lift group border border-outline-variant/30 relative">
<?php if ($product['is_bestseller']): ?>
<span class="absolute top-4 left-4 bg-tertiary-fixed text-on-tertiary-fixed text-label-sm px-2 py-1 rounded-full z-10">Bestseller</span>
<?php endif; ?>
<div class="h-64 rounded-lg overflow-hidden mb-4 bg-surface flex items-center justify-center">
<img class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
</div>
<h3 class="font-title-lg text-title-lg text-on-surface mb-1"><?= htmlspecialchars($product['name']) ?></h3>
<div class="flex items-center gap-2 mb-3">
<div class="flex text-tertiary-fixed-dim">
<?php $full_stars = round($product['rating']); for ($i = 1; $i <= 5; $i++): ?>
<span class="material-symbols-outlined text-lg" style="<?= $i <= $full_stars ? "font-variation-settings: 'FILL' 1;" : '' ?>"><?= $i <= $full_stars ? 'star' : ($i - 0.5 <= $product['rating'] ? 'star_half' : 'star') ?></span>
<?php endfor; ?>
</div>
<span class="text-label-sm text-on-surface-variant">(<?= number_format((int)$product['reviews_count']) ?>)</span>
</div>
<div class="flex items-end gap-2 mb-6">
<span class="font-headline-md text-headline-md text-primary">₹<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-label-lg text-on-surface-variant line-through mb-1">₹<?= number_format($product['compare_price'], 2) ?></span>
<span class="text-label-sm font-bold text-error mb-1"><?= $discount ?>% OFF</span>
<?php endif; ?>
</div>
<button class="w-full py-3 border border-primary text-primary font-label-lg rounded-lg hover:bg-primary hover:text-on-primary transition-all add-to-cart" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
</div>
</section>

<!-- AI Assistant & Experts (Bento Grid Style) -->
<section class="max-w-container-max mx-auto px-margin-desktop grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<div class="lg:col-span-7 bg-primary text-on-primary rounded-xl p-8 flex flex-col justify-between relative overflow-hidden">
<div class="absolute -right-20 -top-20 w-80 h-80 bg-primary-container rounded-full opacity-20 blur-3xl"></div>
<div class="relative z-10">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 bg-on-primary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-primary">smart_toy</span>
</div>
<h2 class="font-headline-lg text-headline-lg">Ayurwellness AI Assistant</h2>
</div>
<p class="font-body-lg text-body-lg opacity-90 max-w-lg mb-8">Namaste! I'm your dedicated Ayurvedic companion. I can help you find the right herbs for your unique Prakriti. How can I assist you today?</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<a href="<?= BASE_URL ?>/dosha-quiz.php" class="bg-on-primary/10 hover:bg-on-primary/20 p-4 rounded-lg text-left transition-colors border border-on-primary/20 group block">
<p class="text-label-sm opacity-60 mb-1">Try asking:</p>
<p class="font-label-lg group-hover:translate-x-1 transition-transform">Suggest medicine for immunity</p>
</a>
<a href="<?= BASE_URL ?>/dosha-quiz.php" class="bg-on-primary/10 hover:bg-on-primary/20 p-4 rounded-lg text-left transition-colors border border-on-primary/20 group block">
<p class="text-label-sm opacity-60 mb-1">Try asking:</p>
<p class="font-label-lg group-hover:translate-x-1 transition-transform">Home remedy for headache</p>
</a>
</div>
</div>
<div class="mt-8 relative z-10">
<a class="bg-on-primary text-primary px-8 py-3 rounded-full font-label-lg hover:bg-primary-fixed transition-colors inline-block" href="<?= BASE_URL ?>/dosha-quiz.php">Start Chat Now</a>
</div>
</div>
<div class="lg:col-span-5 flex flex-col">
<div class="flex justify-between items-center mb-6">
<h2 class="font-headline-md text-headline-md text-on-surface">Consult Experts</h2>
<a class="text-primary font-label-lg" href="<?= BASE_URL ?>/doctor-listing.php">View All</a>
</div>
<div class="space-y-4 flex-grow">
<a href="<?= BASE_URL ?>/doctor-listing.php" class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow cursor-pointer block">
<div class="w-20 h-20 rounded-full overflow-hidden shrink-0 border-2 border-secondary-container">
<img class="w-full h-full object-cover" data-alt="Professional headshot of a friendly Indian Ayurvedic doctor in a white coat with a stethoscope, warm confident smile in a well-lit modern clinic with medicinal plants in the background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBcT8CfNWtCA5inN3DIFJMb1fuYm7-48sYjLPHzvXUhWJYVpQW218o3aMqTpMRF7cXGXY4VB6UycYMCiX8I8djKXySQpfl_sgFgt5OjQU2YCV3rsuNGpIQqLvU58fm45EhVr9EaJ3jW3nP5_sDCKo-UQcPPlTX7HXTS65aL4S1hRRZQIStc3FSFcDoMuueneSTJk2NlYUa2cPjmFg-Rd99DPbYyBcqdFbt2pW_uwA-t8lbCqhMou7dR9lgIDpLtxLdIdL8mAsuqlB4">
</div>
<div class="flex-grow">
<h4 class="font-title-lg text-title-lg text-on-surface">Dr. Vaidya Anand</h4>
<p class="text-label-sm text-on-surface-variant">BAMS, MD (Ayurveda) • 15+ Yrs</p>
<div class="flex items-center gap-1 mt-1 text-tertiary">
<span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-label-sm font-bold">4.8</span>
</div>
</div>
<span class="material-symbols-outlined text-outline">chevron_right</span>
</a>
<a href="<?= BASE_URL ?>/doctor-listing.php" class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex items-center gap-4 hover:shadow-md transition-shadow cursor-pointer block">
<div class="w-20 h-20 rounded-full overflow-hidden shrink-0 border-2 border-secondary-container">
<img class="w-full h-full object-cover" data-alt="Professional portrait of a female Ayurvedic specialist with calm welcoming expression, professional attire in a minimalist modern office with soft lighting." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDeQVJlfX4LRnPCR-cUmYKZHJKWvUz1wE3yACjnBBvdjSGNWct2YDBLF_mzKznvlVgR25A56L1emsEvlDNKSbNpelz9m52ySgA2GuUL5E19rWx-bDmOOLPynGqCs2sNLgR1prL7fO3ac2CszsIbseg5s8mS6kieT6eqejuJBGchZUF5YMRnecAkjKIvvE4_KBPJt9xVDvA9DOoWcNPxC0JG92L52vCMHRwCaOvDciOOuXQ23URx7dt4RJehW6iIfdCUJUj7ru_c9dY">
</div>
<div class="flex-grow">
<h4 class="font-title-lg text-title-lg text-on-surface">Dr. Priyanka Sharma</h4>
<p class="text-label-sm text-on-surface-variant">BAMS, MD (Ayurveda) • 8+ Yrs</p>
<div class="flex items-center gap-1 mt-1 text-tertiary">
<span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-label-sm font-bold">4.7</span>
</div>
</div>
<span class="material-symbols-outlined text-outline">chevron_right</span>
</a>
<a class="w-full block py-4 bg-primary-container text-on-primary-container rounded-xl font-label-lg text-center hover:opacity-90 transition-opacity" href="<?= BASE_URL ?>/appointment-booking.php">Book a Consultation</a>
</div>
</div>
</section>

<!-- Wellness Plans -->


<!-- Testimonials -->
<section class="bg-surface-container-lowest py-section-gap overflow-hidden">
<div class="max-w-container-max mx-auto px-margin-desktop">
<div class="grid lg:grid-cols-3 gap-12 items-center">
<div class="lg:col-span-1">
<h2 class="font-display-lg text-display-lg text-primary mb-6">Healing Stories</h2>
<p class="text-on-surface-variant mb-8 font-body-lg">Real results from our global community of wellness seekers.</p>
<div class="flex gap-4">
<button class="p-4 rounded-full border border-outline hover:bg-surface-container transition-colors material-symbols-outlined">arrow_back</button>
<button class="p-4 rounded-full bg-primary text-on-primary hover:bg-primary-container transition-colors material-symbols-outlined">arrow_forward</button>
</div>
</div>
<div class="lg:col-span-2 flex gap-8 overflow-x-auto pb-8 snap-x no-scrollbar">
<div class="min-w-[400px] snap-center bg-surface-container-low p-10 rounded-3xl relative">
<span class="material-symbols-outlined text-6xl text-primary opacity-10 absolute top-4 right-8">format_quote</span>
<div class="flex text-secondary-container mb-6">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-body-lg italic text-primary mb-8 font-serif">"The Triphala Digestive Care plan changed my life. After years of struggling with bloating, I finally feel light and energetic. The doctor consultation was so eye-opening!"</p>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-primary-fixed"></div>
<div>
<div class="font-bold text-primary">Sarah Jenkins</div>
<div class="text-xs text-on-surface-variant">Verified Patient</div>
</div>
</div>
</div>
<div class="min-w-[400px] snap-center bg-surface-container-low p-10 rounded-3xl relative border-l-4 border-secondary">
<span class="material-symbols-outlined text-6xl text-primary opacity-10 absolute top-4 right-8">format_quote</span>
<div class="flex text-secondary-container mb-6">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<p class="text-body-lg italic text-primary mb-8 font-serif">"I was skeptical about Ayurveda, but the Ashwagandha formulation has noticeably reduced my daily stress levels. Professional quality products."</p>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-primary-fixed"></div>
<div>
<div class="font-bold text-primary">Mark Roberts</div>
<div class="text-xs text-on-surface-variant">Verified Patient</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- Blog Preview -->
<?php
$blog_query = "SELECT * FROM blog_posts ORDER BY published_at DESC LIMIT 3";
$blog_result = $conn->query($blog_query);
$blog_posts = [];
if ($blog_result && $blog_result->num_rows > 0) {
    while ($row = $blog_result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
}
?>
<section class="py-section-gap max-w-container-max mx-auto px-margin-desktop">
<div class="flex justify-between items-end mb-12">
<h2 class="font-headline-lg text-headline-lg text-on-surface">Wellness Insights</h2>
<a class="text-primary font-label-lg text-label-lg flex items-center gap-2 group" href="<?= BASE_URL ?>/wellness-blog.php">Read Our Blog <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span></a>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<?php if (count($blog_posts) > 0): ?>
<?php foreach ($blog_posts as $post): ?>
<article class="group">
<div class="aspect-[16/10] overflow-hidden rounded-2xl mb-6">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"/>
</div>
<span class="text-xs font-bold text-secondary tracking-widest uppercase mb-3 block"><?= htmlspecialchars($post['category']) ?></span>
<h3 class="font-headline-md text-headline-md text-on-surface mb-4 group-hover:text-primary-container"><?= htmlspecialchars($post['title']) ?></h3>
<p class="text-on-surface-variant line-clamp-2"><?= htmlspecialchars($post['excerpt']) ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>

<!-- Newsletter Signup -->
<section class="py-section-gap">
<div class="max-w-container-max mx-auto px-margin-desktop">
<div class="bg-surface-container-high rounded-[40px] p-12 lg:p-20 text-center relative overflow-hidden">
<div class="absolute top-0 left-0 w-32 h-32 bg-primary/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
<div class="absolute bottom-0 right-0 w-48 h-48 bg-secondary/5 rounded-full translate-x-1/4 translate-y-1/4"></div>
<div class="relative z-10 max-w-2xl mx-auto">
<h2 class="font-display-lg text-display-lg text-primary mb-4">Join Our Wellness Community</h2>
<p class="text-on-surface-variant mb-10">Get exclusive Ayurvedic tips, product launches, and 10% off your first order.</p>
<form class="flex flex-col md:flex-row gap-4 max-w-md mx-auto" id="newsletter-form" method="POST" action="<?= BASE_URL ?>/subscribe.php">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input class="flex-grow rounded-full border-outline-variant bg-surface px-6 py-4 focus:ring-primary focus:border-primary" placeholder="Your email address" required name="email" type="email"/>
<button class="bg-primary text-on-primary px-8 py-4 rounded-full font-label-lg text-label-lg hover:bg-primary-container transition-all" type="submit">Subscribe</button>
</form>
<div id="newsletter-msg" class="hidden mt-4 font-label-lg text-label-lg"></div>
</div>
</div>
</div>
</section>

</div>

<script>
(function() {
  const params = new URLSearchParams(window.location.search);
  const msg = params.get('newsletter');
  const el = document.getElementById('newsletter-msg');
  if (msg && el) {
    const map = { success: ['Welcome to the family! Check your inbox.', 'text-primary'], invalid: ['Invalid email address.', 'text-error'], exists: ['You are already subscribed!', 'text-tertiary'], error: ['Something went wrong. Please try again.', 'text-error'] };
    const [text, cls] = map[msg] || ['', ''];
    if (text) { el.textContent = text; el.className = 'mt-4 font-label-lg text-label-lg ' + cls + ' block'; }
    if (msg === 'success') { document.getElementById('newsletter-form')?.querySelector('input[type="email"]')?.closest('form')?.classList.add('hidden'); }
    setTimeout(() => { if (el) el.classList.add('hidden'); }, 5000);
  }
})();
document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const id = this.dataset.productId;
    if (!id) return;
    const btnInner = this.querySelector('.material-symbols-outlined');
    const orig = btnInner ? btnInner.textContent : '';
    if (btnInner) btnInner.textContent = 'sync';
    fetch('<?= BASE_URL ?>/cart-update.php?action=add&id=' + id)
      .then(() => {
        if (btnInner) btnInner.textContent = 'check';
        this.classList.add('bg-secondary', 'text-on-secondary');
        const badge = document.querySelector('#cart-count-badge');
        if (badge) {
          const c = parseInt(badge.textContent) + 1;
          badge.textContent = c;
        }
        setTimeout(() => {
          if (btnInner) btnInner.textContent = orig;
          this.classList.remove('bg-secondary', 'text-on-secondary');
          this.classList.add('bg-primary', 'text-on-primary');
        }, 1500);
      });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
