<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<section class="relative min-h-[85vh] flex items-center overflow-hidden">
<div class="absolute inset-0 z-0">
<img class="w-full h-full object-cover" data-alt="A serene wide-angle lifestyle photograph featuring a healthy, smiling family practicing mindful meditation in a lush, green sunlit garden. The composition includes premium glass jars of Ayurvedic supplements in the soft-focus foreground, illuminated by warm, golden-hour lighting. The overall aesthetic is clinical yet grounded in nature, using deep forest greens and soft sage tones to evoke professional wellness and ancient botanical wisdom." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAQdELDR0Pm1B_DXVpYpAXgL_bV4U3mjfRxGB0lf2E-fxailJkJw4E2JDlwgMhIWQaCsQ9Un6j0hEvm_u96aCxxwaXhlimp2goqL2gpk6Ff4yOFP0IpJ1yP6aAWDOYZa9wNY8ObAhhA-l6jMDiXpWXX_dUk-gMBqlR3sZOpOtifW145kNTl2ffJDWaM4g1K9Z7kVlLvje7PitgymvTWu27RhWtpfxHOOQn3o40HEAaFPum2jV6hNfWr2RX7zJ4amoXbqxPqJRHbTFA"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface/95 via-surface/60 to-transparent"></div>
</div>
<div class="relative z-10 w-full max-w-container-max mx-auto px-gutter">
<div class="max-w-2xl animate-fade-in">
<span class="text-primary-container font-label-md text-label-md tracking-widest block mb-4">TRADITION MEETS CLINICAL SCIENCE</span>
<h1 class="font-display-lg text-display-lg lg:text-[64px] lg:leading-[72px] text-primary mb-6">Ancient Wisdom for Modern Living</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-xl">
Experience the healing power of authentic Ayurveda. Trusted by families, recommended by doctors, and formulated with 5,000 years of botanical heritage.
</p>
<div class="flex flex-wrap gap-4">
<a class="bg-primary text-on-primary px-8 py-4 rounded-full font-label-md text-label-md hover:bg-primary-container shadow-lg transition-all hover:-translate-y-1 inline-block" href="<?= BASE_URL ?>/shop.php">Start Your Journey</a>
<a class="border-2 border-primary text-primary px-8 py-4 rounded-full font-label-md text-label-md hover:bg-surface-container-low transition-all inline-block" href="<?= BASE_URL ?>/doctor-listing.php">Consult a Doctor</a>
</div>
</div>
</div>
</section>

<!-- Trust Indicators Bar -->
<section class="bg-surface-container-low py-8">
<div class="max-w-container-max mx-auto px-gutter flex flex-wrap justify-between items-center gap-8">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
<span class="font-label-md text-label-md text-primary">GMP CERTIFIED</span>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">nature</span>
<span class="font-label-md text-label-md text-primary">100% AYURVEDIC</span>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">medical_services</span>
<span class="font-label-md text-label-md text-primary">DOCTOR RECOMMENDED</span>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">lock</span>
<span class="font-label-md text-label-md text-primary">SECURE PAYMENTS</span>
</div>
</div>
</section>

<!-- Product Categories -->
<section class="py-section-gap max-w-container-max mx-auto px-gutter">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg text-primary mb-2">Targeted Care Solutions</h2>
<div class="w-24 h-1 bg-secondary mx-auto mb-6"></div>
<p class="text-on-surface-variant max-w-lg mx-auto">Discover holistic treatments tailored to your unique biological constitution (Dosha) and health goals.</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Close-up artistic photography of vibrant citrus and ginger on a matte white ceramic surface, symbolizing natural immunity. The lighting is bright and editorial, reflecting high-end clinical hygiene and botanical purity. Soft sage green tones and forest green shadows create a premium Ayurvedic healthcare aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBkyDg0HnVV4EP5dWjPpwv37e9ZTNqA8uwnQmspn9DivMTrM9Ck3rAQR_sug5uWGKlJMe-0VbvD7JAfZ5LBPDYpgLIPT3zZC_9GDzy57rLHQsRSb29R4_OZ-AJ6Bzj3DkIBsIQEeciVclaLnmWppRsLPOBogyAdsjGfT43NTtTsowVUHXyt0wurSvxbqC6fQa-TY145FpcchbcVUpZRUjGRDEw0Y4Xs_sBtL34xUMZSDuEmfPo2JANljtADgfkqYB9A0gKyoZVxgPs"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Immunity</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="A minimalist arrangement of raw cinnamon sticks and bitter melon on a light stone background, focused on metabolic health and diabetes care. The visual style is modern traditionalism with generous whitespace and high-contrast, soft textures. Deep earthy botanical tones align with the Public Care Ayurveda brand identity." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBoGVCoC7RwEoIYFqIS9dv6ov9M_rYwTSm-zKqf66NjkcjDdk7xOpLw5QsZ17cjud_fNb2mqmWuQV4wymien5WbXPEuOQAMnlJLpyJ1qF1eOhCgefwbLByX6lm0-16znC3bY13v7i_8QA7Mw7slHEP5L1ypV0SuLfeS2mxsOMlBdq2ohyM2_KsenTpwEjPFTZe-N1M0ovU9u30wmQ9_UrrZNYsAs5E3jkDpupvwjKzSPNwrZUlNuu06-zUk-uZQTXMbApX10P5jQH0"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Diabetes</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Overhead shot of green tea leaves and healthy superfoods arranged in an asymmetric bento-style composition for weight loss. The lighting is soft and airy, emphasizing professional care and wellness through order. The color palette features Forest Green accents and clean off-white surfaces." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCHBIzhbMGKexbonM1ND7XIhjHT9X4m0lZj2JIK-LVa44r-o4um6bGZGHxB3lRIcF1CpKBWTL76PjWfJ53JF6at9hxcg4uQdmgQO0Zmh7QVvZZA2KccjshsDo2OzU1oU8ZchkmzN7vwOBvTsBGc4Q4dIvqOczp_7N2fyic-CM6CM0zSWKs4r0d-I_8IznFiciUs2XrAJcDHm1K6gHzFdb-u-1D99d3GmoPv_UMykc-TpWqlxmxDkkkWq8dAeTFP7MHDav1bT_nsRvs"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Weight Loss</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Luxurious macro photography of herbal hair oil infused with jasmine and amla, shown on a matte medical equipment texture for hair care. The composition is clean and editorial, using ambient shadows and Forest Green tints to create depth and a premium healthcare identity." src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8HqhJCe7QqmKMPMeUYGLso0ayAqMgpaEVBNkgCDf_TczhP2h-pKE5tYrGR0M1GDX25WhMAvISYjolh4VYcMkQ5Pr7fb_yplWz52paYaF1lZSQQg_z0PMLG88744k96DHOHPSHsZOVSk24SVauXBK3f81jzqLwGvheA0HkdxsJu3YWoomr_ZClJqUS03uRv8Ri1BgLedhQYSdAtB0oo93zq_FtqQj_KgqkP8q90IMpEry_9BRAVETqiYHkc-IQJ5aSkLa9uk7Sl3E"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Hair Care</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Soft, high-key photography of organic aloe vera and sandalwood paste on a white paper surface for skin care. The style reflects clinical precision and ancient holistic wisdom, featuring a timeless, authoritative presence with breathable layout and botanical accents." src="https://lh3.googleusercontent.com/aida-public/AB6AXuD_zXPHqsV2MxuDnF6AhnMGBQ8jZl4Un9lfXs7ZxqDgeA9aZMpoeY9s6HUCbqMOB1kutV4MM9ILr1SVbkhfTpCbXmeU-r8x4sqXCquMRSqaXw5WjKq8BYkYAXcXHyvzoke_nytbN1yoVCiGcaZpv8bYX-K-DowjSu8ZA3F2vi5OOV5-6TlUsHzQaP9_ZUABDjtKylt7L86XQN8p4IAXvxB9fBruI88rFDASfJ6duGjsPBb7AKAOI-QY0vXfs3a-CSD2pJZjWSXrDa0"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Skin Care</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="A tranquil scene showing a woman drinking herbal tea by a window, representing women's health. The mood is serene and technologically advanced in its clarity, utilizing a palette of deep blacks, whites, and Forest Green. High-contrast, soft lighting creates a premium medical-paper feel." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAtWWV7wJFWU9LUWYdbmCowtSHu2v1ZP7y4ju2-_jDmcktUq59Dz11RWFe-SNZKLgsSkZonO2LQMhUbjW-Rxu9vhHQgJnxAVispleXFju6Ejkcd2tzNgZ9m1A4j_XZLg17_dUSv26RlwXJfday7FOQQlKSi7VLS00wKSttqlzpHq2J_xkb3k9396ILqV4jlkYElRWihjhObssjTvtvthMTDxhsc83YSmiXFv-dCL0yBk0q-Nez_q2IOxzm0R09riKfXkJudrJJrm2o"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Women's Health</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Minimalist clinical shot of herbal supplements for men's vitality on a matte surface. The aesthetic is modern traditionalism, focusing on longevity and professional care. Deep earthy botanical tones and 8px baseline grid movement ensure a breathable and premium layout." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMwXWrzfAuvncQGQvSXRUQ81pFMJNoE8RDrQu6al3wN0W1ApwdOOuvmxoX7wX-evBqRtFvHfrcxxLALjexcNhrTGJQVZTpFebbQyfpGYu9eM5rgBH_kda4tIJ4iFb7ufTKVmcPTElWNedrzNt5_gweVhSeXof9aJObAoytA9M5p3YgcWEoIjiHglG6af_xGDAGZAFGGemxX8WxCg0GnXN5ud-JAcH7gNjM1p5ivshWGJxLA7L0mWEA_xadwtjoJkd8zjmL7LrAR18"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Men's Health</span>
</div>
</a>
<a class="group relative aspect-square overflow-hidden rounded-xl bg-surface-container-highest shadow-sm hover:shadow-md transition-all" href="<?= BASE_URL ?>/shop.php">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Artistic macro detail of fennel seeds and cardamom on a premium textured paper, symbolizing digestive care. The lighting is high-key and professional, using a Forest Green and Sage Green palette. The mood is calm, signifying wellness through order." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBd0NS1CekJufnkArOUwSpMKPE-j-N5YdtlL4X0Xf40FypV9HwpfzkV1is7Qj9OjucD9LMSR6s7zZdExz3lxgfBtIPNR99ALDv7QolGJ_fdqaL1cFb0LNvEyNUKwCi2JDD_KKULe38cjwGaIvmzpve4AtJ-HbDOPVI2TYc5QuI6lz3kVoAITF1VeLbFu1eZ3jPlMjB5rRoIDLj_QNiMgVHRyMAb6g_PGwiatfAfAmgmiNqaoo1NgF5b8lVD4imBB44zYRsE2HGlKbQ"/>
<div class="absolute inset-0 bg-primary/40 group-hover:bg-primary/20 transition-colors flex items-center justify-center">
<span class="text-on-primary font-headline-md text-headline-md tracking-wide">Digestive Care</span>
</div>
</a>
</div>
</section>

<!-- Featured Products -->
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
<section class="bg-surface-container py-section-gap">
<div class="max-w-container-max mx-auto px-gutter">
<div class="flex justify-between items-end mb-12">
<div>
<h2 class="font-headline-lg text-headline-lg text-primary mb-2">Our Healing Staples</h2>
<p class="text-on-surface-variant">Best-selling formulations loved by thousands.</p>
</div>
<a class="text-primary font-label-md text-label-md border-b-2 border-primary pb-1" href="<?= BASE_URL ?>/shop.php">VIEW ALL PRODUCTS</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
<?php if (count($featured_products) > 0): ?>
<?php foreach ($featured_products as $product): ?>
<div class="bg-surface rounded-2xl p-6 border border-outline-variant shadow-sm hover:shadow-lg transition-all group">
<div class="relative overflow-hidden rounded-xl mb-6 aspect-square bg-surface-container-low">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="<?= htmlspecialchars($product['image_url']) ?>"/>
<?php if ($product['is_bestseller']): ?>
<span class="absolute top-3 left-3 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-xs font-bold">BEST SELLER</span>
<?php endif; ?>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2"><?= htmlspecialchars($product['name']) ?></h3>
<div class="flex items-center gap-1 mb-3">
<?php $full_stars = round($product['rating']); for ($i = 1; $i <= 5; $i++): ?>
<span class="material-symbols-outlined <?= $i <= $full_stars ? 'text-secondary' : 'text-outline-variant' ?> text-sm" style="<?= $i <= $full_stars ? "font-variation-settings: 'FILL' 1;" : '' ?>">star</span>
<?php endfor; ?>
<span class="text-xs text-on-surface-variant ml-1">(<?= (int)$product['reviews_count'] ?>)</span>
</div>
<div class="flex items-center justify-between">
<span class="text-xl font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-sm text-outline-variant line-through ml-2">$<?= number_format($product['compare_price'], 2) ?></span>
<?php endif; ?>
<button class="bg-primary text-on-primary p-2 rounded-full hover:bg-primary-container transition-all add-to-cart" data-product-id="<?= $product['id'] ?>">
<span class="material-symbols-outlined">add_shopping_cart</span>
</button>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<?php
// Fallback: fetch any 4 products
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
<div class="bg-surface rounded-2xl p-6 border border-outline-variant shadow-sm hover:shadow-lg transition-all group">
<div class="relative overflow-hidden rounded-xl mb-6 aspect-square bg-surface-container-low">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="<?= htmlspecialchars($product['image_url']) ?>"/>
<?php if ($product['is_bestseller']): ?>
<span class="absolute top-3 left-3 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-xs font-bold">BEST SELLER</span>
<?php endif; ?>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2"><?= htmlspecialchars($product['name']) ?></h3>
<div class="flex items-center gap-1 mb-3">
<?php $full_stars = round($product['rating']); for ($i = 1; $i <= 5; $i++): ?>
<span class="material-symbols-outlined <?= $i <= $full_stars ? 'text-secondary' : 'text-outline-variant' ?> text-sm" style="<?= $i <= $full_stars ? "font-variation-settings: 'FILL' 1;" : '' ?>">star</span>
<?php endfor; ?>
<span class="text-xs text-on-surface-variant ml-1">(<?= (int)$product['reviews_count'] ?>)</span>
</div>
<div class="flex items-center justify-between">
<span class="text-xl font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-sm text-outline-variant line-through ml-2">$<?= number_format($product['compare_price'], 2) ?></span>
<?php endif; ?>
<button class="bg-primary text-on-primary p-2 rounded-full hover:bg-primary-container transition-all add-to-cart" data-product-id="<?= $product['id'] ?>">
<span class="material-symbols-outlined">add_shopping_cart</span>
</button>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
</div>
</div>
</section>

<!-- Doctor Consultation Banner -->
<section class="py-section-gap max-w-container-max mx-auto px-gutter">
<div class="flex flex-col lg:flex-row items-center bg-primary text-on-primary rounded-[32px] overflow-hidden shadow-2xl">
<div class="w-full lg:w-1/2 h-[500px]">
<img class="w-full h-full object-cover" data-alt="A professional Ayurvedic doctor in a clean, white consultation room, smiling warmly while reviewing health charts. The setting is modern and high-end with subtle botanical elements like a small desk plant. The lighting is bright and inviting, reinforcing a trust-based clinical environment. The composition focuses on expert care and professional authority." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAr7Qz_qGS3_L5dvUwMGBvCCIBAcyQE9hZy4sMLG7YnieFe8crtX9kmDZfJ8TJU1PIkWwqt0_D3kHrkUmqRm_YrIqQYFUQ3ZHQEqX83dpiBaqXOrDMa9tyeY85aeWKd-DVWR21HeiLcgrFgrxC9qC1sP3p2BAMf1lAjTwG9QGzcwg6zNy5KohIpXKjmoUeNp55b-EORH9GVhrXoYOmqIg_zeiSqSlrADvHj9bTb4kuOzCG0Ma5EEU2oz262jU1KMwOiWhU52tTjjXs"/>
</div>
<div class="w-full lg:w-1/2 p-12 lg:p-20">
<h2 class="font-display-lg text-display-lg mb-6 leading-tight">Expert Guidance at Your Fingertips</h2>
<p class="font-body-lg text-body-lg mb-10 opacity-90 leading-relaxed">
Navigate your wellness journey with personalized advice. Speak with our certified Ayurvedic practitioners for a deep-dive into your health history and a customized roadmap to recovery.
</p>
<ul class="space-y-4 mb-10">
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-secondary-fixed">check_circle</span>
<span>Personalized Dosha Analysis</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-secondary-fixed">check_circle</span>
<span>Diet &amp; Lifestyle Roadmap</span>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-secondary-fixed">check_circle</span>
<span>15-Minute Free Initial Consult</span>
</li>
</ul>
<a class="bg-secondary-container text-on-secondary-container px-10 py-5 rounded-full font-label-md text-label-md hover:scale-105 transition-all shadow-lg inline-block" href="<?= BASE_URL ?>/appointment-booking.php">Book Free Consultation</a>
</div>
</div>
</section>

<!-- Wellness Plans -->
<section class="bg-surface py-section-gap max-w-container-max mx-auto px-gutter">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg text-primary mb-2">Curated Wellness Plans</h2>
<p class="text-on-surface-variant">Step-by-step guidance for lasting health transformations.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="flex flex-col p-8 rounded-3xl border border-outline-variant bg-surface hover:border-primary transition-all group">
<div class="mb-8">
<div class="w-16 h-16 rounded-2xl bg-surface-container-low flex items-center justify-center mb-6 text-primary">
<span class="material-symbols-outlined text-4xl">clean_hands</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2">Body Detox</h3>
<p class="text-on-surface-variant mb-6">A 21-day program to eliminate toxins and reboot metabolism.</p>
<div class="text-3xl font-bold text-primary mb-6">$129 <span class="text-base font-normal text-on-surface-variant">/ plan</span></div>
</div>
<ul class="space-y-4 mb-12 flex-grow">
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">spa</span> Herbal Supplement Kit</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">restaurant_menu</span> Sattvic Meal Planner</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">support_agent</span> Weekly Expert Check-ins</li>
</ul>
<button class="w-full py-4 rounded-full border-2 border-primary text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-all">Select Plan</button>
</div>
<div class="flex flex-col p-8 rounded-3xl border-2 border-primary bg-surface shadow-xl relative z-10 lg:-mt-4 lg:-mb-4">
<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-6 py-1 rounded-full text-xs font-bold">MOST POPULAR</div>
<div class="mb-8">
<div class="w-16 h-16 rounded-2xl bg-primary-container flex items-center justify-center mb-6 text-on-primary">
<span class="material-symbols-outlined text-4xl">shield</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2">Immunity Boost</h3>
<p class="text-on-surface-variant mb-6">Strengthen your natural defenses with seasonal herbs and yoga.</p>
<div class="text-3xl font-bold text-primary mb-6">$159 <span class="text-base font-normal text-on-surface-variant">/ plan</span></div>
</div>
<ul class="space-y-4 mb-12 flex-grow">
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">spa</span> Premium Ojas Elixirs</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">fitness_center</span> Video Yoga Library</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">notifications_active</span> Daily Habit Tracking</li>
</ul>
<button class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-all">Select Plan</button>
</div>
<div class="flex flex-col p-8 rounded-3xl border border-outline-variant bg-surface hover:border-primary transition-all group">
<div class="mb-8">
<div class="w-16 h-16 rounded-2xl bg-surface-container-low flex items-center justify-center mb-6 text-primary">
<span class="material-symbols-outlined text-4xl">self_improvement</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2">Stress Care</h3>
<p class="text-on-surface-variant mb-6">Calm the Vata energy and achieve mental clarity and restful sleep.</p>
<div class="text-3xl font-bold text-primary mb-6">$99 <span class="text-base font-normal text-on-surface-variant">/ plan</span></div>
</div>
<ul class="space-y-4 mb-12 flex-grow">
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">spa</span> Brahmi &amp; Jatamansi Kit</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">psychology</span> Guided Audio Meditations</li>
<li class="flex items-start gap-3 text-sm"><span class="material-symbols-outlined text-primary text-lg">menu_book</span> Sleep Hygiene Guide</li>
</ul>
<button class="w-full py-4 rounded-full border-2 border-primary text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-all">Select Plan</button>
</div>
</div>
</section>

<!-- Testimonials -->
<section class="bg-surface-container-lowest py-section-gap overflow-hidden">
<div class="max-w-container-max mx-auto px-gutter">
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
<section class="py-section-gap max-w-container-max mx-auto px-gutter">
<div class="flex justify-between items-end mb-12">
<h2 class="font-headline-lg text-headline-lg text-primary">Wellness Insights</h2>
<a class="text-primary font-label-md text-label-md flex items-center gap-2 group" href="<?= BASE_URL ?>/wellness-blog.php">Read Our Blog <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span></a>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<?php if (count($blog_posts) > 0): ?>
<?php foreach ($blog_posts as $post): ?>
<article class="group">
<div class="aspect-[16/10] overflow-hidden rounded-2xl mb-6">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"/>
</div>
<span class="text-xs font-bold text-secondary tracking-widest uppercase mb-3 block"><?= htmlspecialchars($post['category']) ?></span>
<h3 class="font-headline-md text-headline-md text-primary mb-4 group-hover:text-primary-container"><?= htmlspecialchars($post['title']) ?></h3>
<p class="text-on-surface-variant line-clamp-2"><?= htmlspecialchars($post['excerpt']) ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>

<!-- Newsletter Signup -->
<section class="py-section-gap">
<div class="max-w-container-max mx-auto px-gutter">
<div class="bg-surface-container-high rounded-[40px] p-12 lg:p-20 text-center relative overflow-hidden">
<div class="absolute top-0 left-0 w-32 h-32 bg-primary/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
<div class="absolute bottom-0 right-0 w-48 h-48 bg-secondary/5 rounded-full translate-x-1/4 translate-y-1/4"></div>
<div class="relative z-10 max-w-2xl mx-auto">
<h2 class="font-display-lg text-display-lg text-primary mb-4">Join Our Wellness Community</h2>
<p class="text-on-surface-variant mb-10">Get exclusive Ayurvedic tips, product launches, and 10% off your first order.</p>
<form class="flex flex-col md:flex-row gap-4 max-w-md mx-auto" id="newsletter-form" method="POST" action="<?= BASE_URL ?>/subscribe.php">
<input class="flex-grow rounded-full border-outline-variant bg-surface px-6 py-4 focus:ring-primary focus:border-primary" placeholder="Your email address" required name="email" type="email"/>
<button class="bg-primary text-on-primary px-8 py-4 rounded-full font-label-md text-label-md hover:bg-primary-container transition-all" type="submit">Subscribe</button>
</form>
<p class="hidden text-primary font-bold mt-4 animate-bounce" id="success-msg">Welcome to the family! Check your inbox.</p>
</div>
</div>
</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
