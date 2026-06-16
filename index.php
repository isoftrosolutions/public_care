<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Home';
require_once __DIR__ . '/includes/header.php';

$conn = getDB();

$featured_products = [];
$product_result = $conn->query("SELECT * FROM products WHERE is_bestseller = TRUE ORDER BY created_at DESC LIMIT 5");
if (!$product_result || $product_result->num_rows === 0) {
    $product_result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
}
if ($product_result) {
    while ($row = $product_result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

$featured_doctors = [];
$doctor_result = $conn->query("SELECT * FROM doctors WHERE available = 1 AND reviews_count > 0 AND name NOT LIKE '%StatusDoc%' AND name NOT LIKE '%BookingDoc%' ORDER BY rating DESC, reviews_count DESC LIMIT 5");
if ($doctor_result) {
    while ($row = $doctor_result->fetch_assoc()) {
        $featured_doctors[] = $row;
    }
}

$blog_posts = [];
$blog_result = $conn->query("SELECT * FROM blog_posts ORDER BY published_at DESC LIMIT 3");
if ($blog_result) {
    while ($row = $blog_result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
}

$hero_image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBo11Xq4d49gQxM0_vENu4Qofi6nZ3iRK1tr--Pdm22dy9TiiDv7FYFz_MlDipFOceI4NAmcK4eohhVsVjx6WfD59jYTP9Tr_CDF3zS1oFCS-QG8L4BZEfoSnUi35osHRACMq1HJlWMrfoj9h84U-fkT-VXFC6Ih9H-kSzgP6mAn_sDA5ai6r6c9rSkZRehbsZEYxeTrxBF5yRpUf4OBK1mpfg-IdYRGDHG2Sh0ho53ntdBRKHWo0HFk8rbj9w5qJ9FYeQ6EJhQJbw';
$default_doctor_image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBcT8CfNWtCA5inN3DIFJMb1fuYm7-48sYjLPHzvXUhWJYVpQW218o3aMqTpMRF7cXGXY4VB6UycYMCiX8I8djKXySQpfl_sgFgt5OjQU2YCV3rsuNGpIQqLvU58fm45EhVr9EaJ3jW3nP5_sDCKo-UQcPPlTX7HXTS65aL4S1hRRZQIStc3FSFcDoMuueneSTJk2NlYUa2cPjmFg-Rd99DPbYyBcqdFbt2pW_uwA-t8lbCqhMou7dR9lgIDpLtxLdIdL8mAsuqlB4';
$default_product_image = BASE_URL . '/assets/uploads/logo.jpeg';

$services = [
    ['icon' => 'science', 'title' => t('service_blood_test'), 'text' => t('service_blood_test_desc'), 'url' => 'lab-tests.php'],
    ['icon' => 'upload_file', 'title' => t('service_upload_report'), 'text' => t('service_upload_report_desc'), 'url' => 'upload-report.php'],
    ['icon' => 'videocam', 'title' => t('service_talk_to_doctor'), 'text' => t('service_talk_to_doctor_desc'), 'url' => 'doctor-listing.php'],
    ['icon' => 'smart_toy', 'title' => t('service_ai_assistant'), 'text' => t('service_ai_assistant_desc'), 'url' => 'ai-assistant.php'],
    ['icon' => 'emergency_home', 'title' => t('service_emergency'), 'text' => t('service_emergency_desc'), 'url' => 'contact-us.php#emergency-help'],
    ['icon' => 'local_shipping', 'title' => t('service_delivery'), 'text' => t('service_delivery_desc'), 'url' => 'shop.php'],
    ['icon' => 'event_available', 'title' => t('service_appointments'), 'text' => t('service_appointments_desc'), 'url' => 'appointment-booking.php'],
    ['icon' => 'description', 'title' => t('service_prescriptions'), 'text' => t('service_prescriptions_desc'), 'url' => 'prescriptions.php'],
    ['icon' => 'shopping_bag', 'title' => t('service_health_products'), 'text' => t('service_health_products_desc'), 'url' => 'shop.php'],
    ['icon' => 'family_restroom', 'title' => t('service_family_care'), 'text' => t('service_family_care_desc'), 'url' => 'my-family.php'],
];

$why_cards = [
    ['icon' => 'verified', 'title' => t('why_ai_title'), 'text' => t('why_ai_desc')],
    ['icon' => 'stethoscope', 'title' => t('hero_verified'), 'text' => t('why_doctors_desc')],
    ['icon' => 'medication', 'title' => t('why_systems_title'), 'text' => t('why_systems_desc')],
    ['icon' => 'bolt', 'title' => t('why_fast_title'), 'text' => t('why_fast_desc')],
    ['icon' => 'shield_lock', 'title' => t('why_secure_title'), 'text' => t('why_secure_desc')],
    ['icon' => 'support_agent', 'title' => t('why_affordable_title'), 'text' => t('why_affordable_desc')],
];

$steps = [
    ['icon' => 'science', 'title' => t('step_book_title'), 'text' => t('step_book_desc')],
    ['icon' => 'upload_file', 'title' => t('step_upload_title'), 'text' => t('step_upload_desc')],
    ['icon' => 'smart_toy', 'title' => t('step_understand_title'), 'text' => t('step_understand_desc')],
    ['icon' => 'stethoscope', 'title' => t('step_consult_title'), 'text' => t('step_consult_desc')],
];

$fallback_testimonials = [
    ['name' => 'Priya Sharma', 'role' => 'Verified Patient', 'text' => 'Doctor consultation and Ayurvedic medicines were easy to book from one place.'],
    ['name' => 'Rahul Verma', 'role' => 'Customer', 'text' => 'The AI assistant helped me understand what care route to take before booking.'],
    ['name' => 'Ananya Patel', 'role' => 'Verified Patient', 'text' => 'Fast medicine delivery and simple prescription storage made follow-up easier.'],
];
?>

<style>
.home-soft-grid {
  background-image:
    radial-gradient(circle at 12% 18%, rgba(166, 245, 175, 0.34), transparent 24%),
    radial-gradient(circle at 88% 8%, rgba(223, 228, 220, 0.65), transparent 20%),
    linear-gradient(180deg, #fbfdf8 0%, #fbf9f8 48%, #ffffff 100%);
}
.home-card { box-shadow: 0 14px 40px rgba(0, 82, 33, 0.08); }
.home-phone { box-shadow: 0 22px 60px rgba(0, 82, 33, 0.2); }
.home-scroll-snap { scroll-snap-type: x mandatory; }
.home-scroll-snap > * { scroll-snap-align: start; }
</style>

<div class="pt-20 bg-background text-on-surface">
<section class="home-soft-grid overflow-hidden border-b border-outline-variant/20">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 lg:py-10">
<div class="grid lg:grid-cols-12 gap-8 items-center">
<div class="lg:col-span-5 space-y-5">
<div class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-white/80 px-3 py-1.5 text-[11px] font-bold text-primary shadow-sm">
<span class="material-symbols-outlined text-base">health_and_safety</span>
<?= t('hero_badge_text') ?>
</div>
<div>
<p class="font-label-lg text-label-lg text-on-surface-variant mb-2"><?= t('hero_system_text') ?></p>
<h1 class="font-display-lg text-[36px] leading-[42px] md:text-[50px] md:leading-[56px] text-primary tracking-tight max-w-xl"><?= t('hero_title_text') ?></h1>
</div>
<p class="text-body-lg text-on-surface-variant max-w-xl"><?= t('hero_subtitle_text') ?></p>
<div class="flex flex-col sm:flex-row flex-wrap gap-3">
<a href="<?= BASE_URL ?>/lab-tests.php" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-on-primary font-label-lg shadow-md hover:bg-primary-container transition-all">
<span class="material-symbols-outlined text-lg">science</span>
<?= t('book_blood_test') ?>
</a>
<a href="<?= BASE_URL ?>/doctor-listing.php" class="inline-flex items-center justify-center gap-2 rounded-full border border-primary/30 bg-white px-6 py-3 text-primary font-label-lg hover:bg-primary/5 transition-all">
<span class="material-symbols-outlined text-lg">videocam</span>
<?= t('talk_to_doctor') ?>
</a>
<a href="<?= BASE_URL ?>/upload-report.php" class="inline-flex items-center justify-center gap-2 rounded-full border border-primary/30 bg-white px-6 py-3 text-primary font-label-lg hover:bg-primary/5 transition-all">
<span class="material-symbols-outlined text-lg">upload_file</span>
<?= t('upload_report') ?>
</a>
</div>
<div class="grid grid-cols-3 gap-3 pt-2 text-[12px] text-on-surface-variant">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">verified</span><?= t('hero_secure') ?></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">workspace_premium</span><?= t('hero_verified') ?></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">support_agent</span><?= t('hero_support') ?></div>
</div>
</div>

<div class="lg:col-span-7 relative min-h-[420px]">
<div class="absolute right-0 top-3 hidden md:block w-72 h-72 rounded-full overflow-hidden border-[18px] border-white/70 home-card">
<img loading="lazy" src="<?= htmlspecialchars($hero_image) ?>" alt="Ayurvedic herbs and wellness products" class="w-full h-full object-cover">
</div>
<div class="absolute -right-10 bottom-6 hidden lg:block w-48 h-48 rounded-full bg-primary-fixed/45 blur-2xl"></div>
<div class="relative mx-auto w-[260px] sm:w-[300px] rounded-[34px] border-[10px] border-on-primary-fixed bg-white p-3 home-phone">
<div class="rounded-[24px] overflow-hidden border border-outline-variant/30 bg-surface-container-lowest">
<div class="bg-primary text-on-primary p-4">
<div class="flex items-center justify-between text-[11px] opacity-90">
<span>9:41</span>
<span class="material-symbols-outlined text-sm">battery_full</span>
</div>
<div class="mt-4 flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center">
<span class="material-symbols-outlined">waving_hand</span>
</div>
<div>
<p class="text-[12px] opacity-80"><?= t('hero_morning') ?></p>
<p class="font-bold"><?= t('hero_help') ?></p>
</div>
</div>
</div>
<div class="p-4 space-y-3">
<div class="rounded-2xl bg-primary/8 p-3 border border-primary/10">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
<span class="material-symbols-outlined">smart_toy</span>
</div>
<div>
<p class="text-sm font-bold text-primary"><?= t('hero_ai_title') ?></p>
<p class="text-[11px] text-on-surface-variant"><?= t('hero_ai_desc') ?></p>
</div>
</div>
</div>
<div class="grid grid-cols-4 gap-2 text-center">
<?php foreach (array_slice($services, 0, 4) as $service): ?>
<div class="rounded-xl bg-surface-container-low p-2">
<span class="material-symbols-outlined text-primary text-xl"><?= htmlspecialchars($service['icon']) ?></span>
<p class="text-[9px] leading-tight mt-1"><?= htmlspecialchars(explode(' ', $service['title'])[0]) ?></p>
</div>
<?php endforeach; ?>
</div>
<div class="rounded-2xl border border-outline-variant/30 p-3">
<div class="flex items-center justify-between mb-2">
<p class="text-xs font-bold"><?= t('hero_upcoming') ?></p>
<span class="text-[10px] text-primary font-bold"><?= t('hero_today') ?></span>
</div>
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center">
<span class="material-symbols-outlined text-primary text-lg">stethoscope</span>
</div>
<div>
<p class="text-xs font-bold"><?= t('nav_consult') ?></p>
<p class="text-[10px] text-on-surface-variant">10:30 AM, video call</p>
</div>
</div>
</div>
<div class="grid grid-cols-2 gap-2">
<a href="<?= BASE_URL ?>/shop.php" class="rounded-xl bg-primary text-on-primary text-center py-2 text-xs font-bold"><?= t('hero_buy_medicine') ?></a>
<a href="<?= BASE_URL ?>/lab-tests.php" class="rounded-xl bg-surface-container text-primary text-center py-2 text-xs font-bold"><?= t('hero_book_test') ?></a>
</div>
</div>
</div>
</div>
</div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-8">
<div class="home-card rounded-xl bg-white border border-outline-variant/20 p-4 flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">groups</span>
<div><p class="font-headline-md text-xl text-primary">50,000+</p><p class="text-xs text-on-surface-variant"><?= t('stat_patients') ?></p></div>
</div>
<div class="home-card rounded-xl bg-white border border-outline-variant/20 p-4 flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">stethoscope</span>
<div><p class="font-headline-md text-xl text-primary">5,000+</p><p class="text-xs text-on-surface-variant"><?= t('stat_experts') ?></p></div>
</div>
<div class="home-card rounded-xl bg-white border border-outline-variant/20 p-4 flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">support_agent</span>
<div><p class="font-headline-md text-xl text-primary">24x7</p><p class="text-xs text-on-surface-variant"><?= t('stat_support') ?></p></div>
</div>
<div class="home-card rounded-xl bg-white border border-outline-variant/20 p-4 flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-3xl">medical_services</span>
<div><p class="font-headline-md text-xl text-primary">100+</p><p class="text-xs text-on-surface-variant"><?= t('stat_services') ?></p></div>
</div>
</div>
</div>
</section>

<div class="space-y-12 md:space-y-14 py-10 md:py-14">

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="rounded-2xl bg-white border border-outline-variant/25 p-5 md:p-7 home-card">
<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-5">
<div>
<p class="text-label-md font-label-md text-primary uppercase tracking-wider"><?= t('launch_services_label') ?></p>
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('launch_services_title') ?></h2>
</div>
<p class="text-sm text-on-surface-variant max-w-xl"><?= t('launch_services_desc') ?></p>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
<?php foreach ([
    ['science', t('launch_test_title'), t('launch_test_desc')],
    ['upload_file', t('launch_ai_title'), t('launch_ai_desc')],
    ['videocam', t('launch_doctor_title'), t('launch_doctor_desc')],
    ['article', t('launch_articles_title'), t('launch_articles_desc')],
    ['emergency_home', t('launch_emergency_title'), t('launch_emergency_desc')],
] as $item): ?>
<div class="rounded-xl bg-surface-container-low border border-outline-variant/25 p-4">
<span class="material-symbols-outlined text-primary text-3xl"><?= htmlspecialchars($item[0]) ?></span>
<h3 class="mt-3 font-bold text-on-surface"><?= htmlspecialchars($item[1]) ?></h3>
<p class="mt-1 text-xs text-on-surface-variant leading-5"><?= htmlspecialchars($item[2]) ?></p>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="text-center mb-6">
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('services_title') ?></h2>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-9 gap-3">
<?php foreach ($services as $service): ?>
<a href="<?= BASE_URL ?>/<?= htmlspecialchars($service['url']) ?>" class="group min-h-[148px] rounded-xl border border-outline-variant/25 bg-white p-3 text-center home-card hover:-translate-y-1 hover:border-primary/30 transition-all">
<div class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-primary/8 flex items-center justify-center group-hover:bg-primary group-hover:text-on-primary text-primary transition-colors">
<span class="material-symbols-outlined"><?= htmlspecialchars($service['icon']) ?></span>
</div>
<h3 class="text-sm font-bold text-on-surface leading-tight"><?= htmlspecialchars($service['title']) ?></h3>
<p class="mt-2 text-[11px] leading-4 text-on-surface-variant"><?= htmlspecialchars($service['text']) ?></p>
</a>
<?php endforeach; ?>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="grid lg:grid-cols-12 gap-6 items-center">
<div class="lg:col-span-4 rounded-2xl overflow-hidden bg-primary/8 min-h-[280px]">
<img loading="lazy" src="<?= htmlspecialchars($hero_image) ?>" alt="<?= t('family_care_alt') ?>" class="w-full h-full min-h-[280px] object-cover">
</div>
<div class="lg:col-span-8">
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-5"><?= t('why_choose_title') ?></h2>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
<?php foreach ($why_cards as $card): ?>
<div class="rounded-xl bg-white border border-outline-variant/25 p-4 home-card">
<div class="w-10 h-10 rounded-xl bg-primary/8 text-primary flex items-center justify-center mb-3">
<span class="material-symbols-outlined"><?= htmlspecialchars($card['icon']) ?></span>
</div>
<h3 class="font-bold text-sm text-on-surface"><?= htmlspecialchars($card['title']) ?></h3>
<p class="text-xs text-on-surface-variant mt-1"><?= htmlspecialchars($card['text']) ?></p>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="flex items-end justify-between gap-4 mb-5">
<h2 class="font-headline-lg text-headline-lg text-primary"><?= t('how_it_works_title') ?></h2>
<a href="<?= BASE_URL ?>/ai-assistant.php" class="hidden sm:inline-flex items-center gap-1 text-primary font-label-lg"><?= t('btn_learn_more') ?> <span class="material-symbols-outlined text-base">arrow_forward</span></a>
</div>
<div class="grid md:grid-cols-4 gap-3">
<?php foreach ($steps as $index => $step): ?>
<div class="relative rounded-xl bg-white border border-outline-variant/25 p-4 home-card">
<div class="flex items-center gap-3">
<div class="w-11 h-11 rounded-full bg-primary text-on-primary flex items-center justify-center">
<span class="material-symbols-outlined"><?= htmlspecialchars($step['icon']) ?></span>
</div>
<span class="absolute right-4 top-4 text-xs font-bold text-primary/35">0<?= $index + 1 ?></span>
</div>
<h3 class="font-bold text-on-surface mt-4"><?= htmlspecialchars($step['title']) ?></h3>
<p class="text-xs text-on-surface-variant mt-1"><?= htmlspecialchars($step['text']) ?></p>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="flex items-center justify-between mb-5">
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('featured_doctors_title') ?></h2>
<a class="text-primary font-label-lg text-label-lg flex items-center gap-1" href="<?= BASE_URL ?>/doctor-listing.php"><?= t('view_all') ?> <span class="material-symbols-outlined text-base">arrow_forward</span></a>
</div>
<div class="flex gap-4 overflow-x-auto hide-scrollbar pb-2 home-scroll-snap">
<?php if (count($featured_doctors) > 0): ?>
<?php foreach ($featured_doctors as $doctor): ?>
<a href="<?= BASE_URL ?>/doctor-profile.php?id=<?= (int)$doctor['id'] ?>" class="min-w-[220px] rounded-xl bg-white border border-outline-variant/25 p-4 home-card hover:-translate-y-1 transition-all">
<img loading="lazy" src="<?= htmlspecialchars($doctor['image_url'] ?: $default_doctor_image) ?>" alt="<?= htmlspecialchars($doctor['name']) ?>" class="w-full h-36 object-cover rounded-lg bg-surface-container mb-3" onerror="this.onerror=null;this.src='<?= htmlspecialchars($default_doctor_image) ?>';">
<h3 class="font-bold text-sm text-on-surface"><?= htmlspecialchars($doctor['name']) ?></h3>
<p class="text-xs text-on-surface-variant mt-1"><?= htmlspecialchars($doctor['specialty'] ?: t('fallback_specialty')) ?></p>
<p class="text-[11px] text-on-surface-variant mt-1"><?= htmlspecialchars($doctor['qualifications'] ?: t('fallback_qual')) ?> &bull; <?= (int)($doctor['experience_years'] ?? 5) ?>+ yrs</p>
<div class="flex items-center justify-between mt-3">
<span class="inline-flex items-center gap-1 text-xs font-bold text-tertiary"><span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span><?= number_format((float)$doctor['rating'], 1) ?></span>
<span class="rounded-full bg-primary px-3 py-1 text-[11px] font-bold text-on-primary"><?= t('btn_book_now') ?></span>
</div>
</a>
<?php endforeach; ?>
<?php else: ?>
<?php foreach ([['Dr. Ajay Vaidya','Ayurvedic Doctor'], ['Dr. Bhavna Mishra','General Physician'], ['Dr. Kavita Sharma','Women Health Expert'], ['Dr. Amit Singh','Child Specialist'], ['Dr. Neha Kapoor','Homeopathy Expert']] as $doctor): ?>
<a href="<?= BASE_URL ?>/doctor-listing.php" class="min-w-[220px] rounded-xl bg-white border border-outline-variant/25 p-4 home-card hover:-translate-y-1 transition-all">
<img loading="lazy" src="<?= htmlspecialchars($default_doctor_image) ?>" alt="<?= htmlspecialchars($doctor[0]) ?>" class="w-full h-36 object-cover rounded-lg bg-surface-container mb-3">
<h3 class="font-bold text-sm text-on-surface"><?= htmlspecialchars($doctor[0]) ?></h3>
<p class="text-xs text-on-surface-variant mt-1"><?= htmlspecialchars($doctor[1]) ?></p>
<p class="text-[11px] text-on-surface-variant mt-1">BAMS &bull; 8+ yrs</p>
<div class="flex items-center justify-between mt-3">
<span class="inline-flex items-center gap-1 text-xs font-bold text-tertiary"><span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span>4.8</span>
<span class="rounded-full bg-primary px-3 py-1 text-[11px] font-bold text-on-primary"><?= t('btn_book_now') ?></span>
</div>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="rounded-2xl bg-primary text-on-primary overflow-hidden home-card">
<div class="grid lg:grid-cols-12 gap-6 p-5 md:p-7 items-center">
<div class="lg:col-span-4 flex items-center gap-4">
<div class="w-16 h-16 rounded-2xl bg-white text-primary flex items-center justify-center">
<span class="material-symbols-outlined text-4xl">smart_toy</span>
</div>
<div>
<h2 class="font-headline-md text-headline-md"><?= t('hero_ai_title') ?></h2>
<p class="text-sm text-on-primary/75"><?= t('hero_ai_desc') ?></p>
</div>
</div>
<div class="lg:col-span-6 grid sm:grid-cols-4 gap-2">
<?php foreach (['What should I eat?', 'Tips to control diabetes?', 'Ayurveda for immunity?', 'Is homeopathy effective?'] as $question): ?>
<a href="<?= BASE_URL ?>/ai-assistant.php" class="rounded-full bg-white/10 border border-white/15 px-3 py-2 text-center text-xs hover:bg-white/20 transition-colors"><?= htmlspecialchars($question) ?></a>
<?php endforeach; ?>
</div>
<div class="lg:col-span-2 lg:text-right">
<a href="<?= BASE_URL ?>/ai-assistant.php" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-primary font-bold text-sm"><?= t('btn_learn_more') ?> <span class="material-symbols-outlined text-base">arrow_forward</span></a>
</div>
</div>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="flex items-center justify-between mb-5">
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('popular_medicines_title') ?></h2>
<a class="text-primary font-label-lg text-label-lg flex items-center gap-1" href="<?= BASE_URL ?>/shop.php"><?= t('view_all_products') ?> <span class="material-symbols-outlined text-base">arrow_forward</span></a>
</div>
<div class="flex gap-4 overflow-x-auto hide-scrollbar pb-2 home-scroll-snap">
<?php foreach ($featured_products as $product): ?>
<?php $discount = ((float)($product['compare_price'] ?? 0) > 0) ? round((1 - (float)$product['price'] / (float)$product['compare_price']) * 100) : 0; ?>
<?php $product_image = trim((string)($product['image_url'] ?? '')) !== '' ? $product['image_url'] : $default_product_image; ?>
<article class="min-w-[230px] rounded-xl bg-white border border-outline-variant/25 p-4 home-card">
<a href="<?= BASE_URL ?>/product-details.php?id=<?= (int)$product['id'] ?>" class="block">
<div class="relative h-36 rounded-lg bg-surface-container-low overflow-hidden mb-3 flex items-center justify-center">
<?php if (!empty($product['is_bestseller'])): ?><span class="absolute left-2 top-2 rounded-full bg-tertiary-fixed px-2 py-1 text-[10px] font-bold text-on-tertiary-fixed">Bestseller</span><?php endif; ?>
<img src="<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-contain p-2" onerror="this.onerror=null;this.src='<?= htmlspecialchars($default_product_image) ?>';">
</div>
<h3 class="font-bold text-sm text-on-surface line-clamp-2 min-h-[40px]"><?= htmlspecialchars($product['name']) ?></h3>
<div class="flex items-center gap-1 mt-2 text-tertiary">
<span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="text-xs font-bold"><?= number_format((float)$product['rating'], 1) ?></span>
<span class="text-xs text-on-surface-variant">(<?= number_format((int)$product['reviews_count']) ?>)</span>
</div>
<div class="flex items-end gap-2 mt-2">
<span class="font-bold text-primary">&#8377;<?= number_format((float)$product['price'], 2) ?></span>
<?php if ((float)($product['compare_price'] ?? 0) > 0): ?>
<span class="text-xs text-on-surface-variant line-through">&#8377;<?= number_format((float)$product['compare_price'], 2) ?></span>
<span class="text-[10px] font-bold text-error"><?= $discount ?>% OFF</span>
<?php endif; ?>
</div>
</a>
<button class="mt-3 w-full rounded-full border border-primary px-4 py-2 text-sm font-bold text-primary hover:bg-primary hover:text-on-primary transition-all add-to-cart" data-product-id="<?= (int)$product['id'] ?>">
<span class="material-symbols-outlined text-base align-[-3px] mr-1">add_shopping_cart</span><?= t('btn_add_to_cart') ?>
</button>
</article>
<?php endforeach; ?>
<?php if (count($featured_products) === 0): ?>
<div class="rounded-xl bg-white border border-outline-variant/25 p-6 text-on-surface-variant">Products will appear here after medicines are added.</div>
<?php endif; ?>
</div>
</section>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="flex items-center justify-between mb-5">
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('testimonials_title') ?></h2>
<a class="text-primary font-label-lg text-label-lg" href="<?= BASE_URL ?>/wellness-blog.php"><?= t('view_all') ?></a>
</div>
<div class="grid md:grid-cols-3 gap-4">
<?php foreach ($fallback_testimonials as $item): ?>
<article class="rounded-xl bg-white border border-outline-variant/25 p-5 home-card">
<div class="flex text-tertiary-fixed-dim mb-3">
<?php for ($i = 0; $i < 5; $i++): ?><span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span><?php endfor; ?>
</div>
<p class="text-sm text-on-surface-variant">"<?= htmlspecialchars($item['text']) ?>"</p>
<div class="flex items-center gap-3 mt-4">
<div class="w-9 h-9 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-bold"><?= htmlspecialchars(substr($item['name'], 0, 1)) ?></div>
<div>
<p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($item['name']) ?></p>
<p class="text-xs text-primary"><?= htmlspecialchars($item['role']) ?></p>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</section>

<?php if (count($blog_posts) > 0): ?>
<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="flex items-end justify-between mb-5">
<h2 class="font-headline-lg text-headline-lg text-on-surface"><?= t('wellness_insights_title') ?></h2>
<a class="text-primary font-label-lg text-label-lg flex items-center gap-1" href="<?= BASE_URL ?>/wellness-blog.php"><?= t('btn_read_more') ?> <span class="material-symbols-outlined text-base">arrow_forward</span></a>
</div>
<div class="grid md:grid-cols-3 gap-4">
<?php foreach ($blog_posts as $post): ?>
<article class="rounded-xl bg-white border border-outline-variant/25 overflow-hidden home-card group">
<img class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" onerror="this.onerror=null;this.src='<?= htmlspecialchars($hero_image) ?>';">
<div class="p-4">
<span class="text-[11px] font-bold uppercase tracking-wide text-primary"><?= htmlspecialchars($post['category']) ?></span>
<h3 class="font-bold text-on-surface mt-2"><?= htmlspecialchars($post['title']) ?></h3>
<p class="text-sm text-on-surface-variant mt-2 line-clamp-2"><?= htmlspecialchars($post['excerpt']) ?></p>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="rounded-2xl bg-surface-container-high p-6 md:p-10 text-center relative overflow-hidden">
<div class="relative z-10 max-w-2xl mx-auto">
<h2 class="font-headline-lg text-headline-lg text-primary mb-3"><?= t('join_community_title') ?></h2>
<p class="text-on-surface-variant mb-6"><?= t('join_community_desc') ?></p>
<form class="flex flex-col md:flex-row gap-3 max-w-md mx-auto" id="newsletter-form" method="POST" action="<?= BASE_URL ?>/subscribe.php">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input class="flex-grow rounded-full border-outline-variant bg-white px-5 py-3 focus:ring-primary focus:border-primary" placeholder="<?= t('email_placeholder') ?>" required name="email" type="email">
<button class="bg-primary text-on-primary px-7 py-3 rounded-full font-label-lg text-label-lg hover:bg-primary-container transition-all" type="submit"><?= t('subscribe_btn') ?></button>
</form>
<div id="newsletter-msg" class="hidden mt-4 font-label-lg text-label-lg"></div>
</div>
</div>
</section>

</div>
</div>

<script>
(function() {
  const params = new URLSearchParams(window.location.search);
  const msg = params.get('newsletter');
  const el = document.getElementById('newsletter-msg');
  if (msg && el) {
    const map = {
      success: ['Welcome to the family! Check your inbox.', 'text-primary'],
      invalid: ['Invalid email address.', 'text-error'],
      exists: ['You are already subscribed!', 'text-tertiary'],
      error: ['Something went wrong. Please try again.', 'text-error']
    };
    const selected = map[msg] || ['', ''];
    if (selected[0]) {
      el.textContent = selected[0];
      el.className = 'mt-4 font-label-lg text-label-lg ' + selected[1] + ' block';
    }
    if (msg === 'success') {
      document.getElementById('newsletter-form')?.classList.add('hidden');
    }
    setTimeout(() => { if (el) el.classList.add('hidden'); }, 5000);
  }
})();

document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const id = this.dataset.productId;
    if (!id) return;
    const icon = this.querySelector('.material-symbols-outlined');
    const originalIcon = icon ? icon.textContent : '';
    const originalText = this.textContent;
    if (icon) icon.textContent = 'sync';
    this.disabled = true;
    fetch('<?= BASE_URL ?>/cart-update.php?action=add&id=' + id)
      .then(() => {
        if (icon) icon.textContent = 'check';
        this.classList.add('bg-primary', 'text-on-primary');
        const badge = document.querySelector('#cart-count-badge');
        if (badge) {
          const current = parseInt(badge.textContent || '0', 10) || 0;
          badge.textContent = current + 1;
        }
      })
      .catch(() => {
        this.textContent = 'Try Again';
      })
      .finally(() => {
        setTimeout(() => {
          this.disabled = false;
          if (icon) icon.textContent = originalIcon;
          if (!icon) this.textContent = originalText;
          this.classList.remove('bg-primary', 'text-on-primary');
        }, 1400);
      });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
