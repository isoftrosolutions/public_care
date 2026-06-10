<?php
require_once __DIR__ . '/includes/config.php';

$success = '';
$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['subject'] = trim($_POST['subject'] ?? '');
    $form_data['message'] = trim($_POST['message'] ?? '');

    if (!$form_data['name']) {
        $errors['name'] = 'Name is required.';
    }
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email is required.';
    }
    if (!$form_data['message']) {
        $errors['message'] = 'Message is required.';
    }

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $form_data['name'], $form_data['email'], $form_data['subject'], $form_data['message']);
        if ($stmt->execute()) {
            $success = 'Thank you for reaching out. Our team will respond within 24 hours.';
            $form_data = [];
        } else {
            $errors['general'] = 'Failed to send message. Please try again later.';
        }
    }
    }
}

$site_title = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
?>
<main class="max-w-container-max mx-auto px-margin-desktop">
<header class="py-section-gap text-center max-w-3xl mx-auto">
<h1 class="font-display-lg text-display-lg text-primary mb-6">Get in Touch with Our Experts</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">
Whether you're beginning your Ayurvedic journey or require clinical assistance, our team of botanical heritage specialists is here to guide your path to wellness.
</p>
</header>
<section class="grid grid-cols-1 lg:grid-cols-12 gap-gutter mb-section-gap">
<div class="lg:col-span-8 bg-surface-container-lowest p-10 rounded-[16px] shadow-sm border border-outline-variant">
<h2 class="font-headline-lg text-headline-lg text-primary mb-8">Send an Inquiry</h2>
<?php if ($success): ?>
<div class="mb-6 p-5 rounded-lg bg-tertiary-container border border-tertiary text-on-tertiary-container font-label-md flex items-center gap-3">
<span class="material-symbols-outlined">check_circle</span>
<?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if (!empty($errors['general'])): ?>
<div class="mb-6 p-4 rounded-lg bg-error-container border border-error text-on-error-container font-label-md"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>
<form class="space-y-6" method="POST" action="">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="font-label-md text-on-surface-variant block" for="name">Full Name</label>
<input class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-3 <?= isset($errors['name']) ? 'border-error' : '' ?>" id="name" name="name" placeholder="e.g. Dr. Kavita Sharma" type="text" value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" required/>
<?php if (isset($errors['name'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>
</div>
<div class="space-y-2">
<label class="font-label-md text-on-surface-variant block" for="email">Email Address</label>
<input class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-3 <?= isset($errors['email']) ? 'border-error' : '' ?>" id="email" name="email" placeholder="kavita@heritage.care" type="email" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required/>
<?php if (isset($errors['email'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
</div>
</div>
<div class="space-y-2">
<label class="font-label-md text-on-surface-variant block" for="subject">Topic of Discussion</label>
<select class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-3" id="subject" name="subject">
<option value="Clinical Consultation" <?= ($form_data['subject'] ?? '') === 'Clinical Consultation' ? 'selected' : '' ?>>Clinical Consultation</option>
<option value="Product Inquiry" <?= ($form_data['subject'] ?? '') === 'Product Inquiry' ? 'selected' : '' ?>>Product Inquiry</option>
<option value="Order Tracking" <?= ($form_data['subject'] ?? '') === 'Order Tracking' ? 'selected' : '' ?>>Order Tracking</option>
<option value="Botanical Sourcing" <?= ($form_data['subject'] ?? '') === 'Botanical Sourcing' ? 'selected' : '' ?>>Botanical Sourcing</option>
</select>
</div>
<div class="space-y-2">
<label class="font-label-md text-on-surface-variant block" for="message">Your Message</label>
<textarea class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-3 <?= isset($errors['message']) ? 'border-error' : '' ?>" id="message" name="message" placeholder="How can our clinical experts assist you today?" rows="5" required><?= htmlspecialchars($form_data['message'] ?? '') ?></textarea>
<?php if (isset($errors['message'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['message']) ?></p><?php endif; ?>
</div>
<button class="bg-primary text-on-primary px-8 py-4 rounded-full font-label-md hover:opacity-90 transition-all shadow-md flex items-center gap-2" type="submit">
<span>Submit Inquiry</span>
<span class="material-symbols-outlined">send</span>
</button>
</form>
</div>
<aside class="lg:col-span-4 space-y-gutter">
<div class="bg-primary text-on-primary p-8 rounded-[16px] shadow-lg">
<h3 class="font-headline-md text-headline-md mb-6">Immediate Support</h3>
<div class="space-y-6">
<a class="flex items-center gap-4 group" href="tel:+1800AYURCARE">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">call</span>
</div>
<div>
<p class="text-label-sm opacity-70">Phone Support</p>
<p class="font-headline-md text-headline-md">+1 (800) AYUR-CARE</p>
</div>
</a>
<a class="flex items-center gap-4 group" href="mailto:expert@publiccare.com">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">mail</span>
</div>
<div>
<p class="text-label-sm opacity-70">Email Us</p>
<p class="font-headline-md text-headline-md">expert@ayur.care</p>
</div>
</a>
<a class="flex items-center gap-4 group" href="<?= BASE_URL ?>/index.php">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">chat</span>
</div>
<div>
<p class="text-label-sm opacity-70">WhatsApp Concierge</p>
<p class="font-headline-md text-headline-md">Live Chat Active</p>
</div>
</a>
</div>
</div>
<div class="bg-surface-container-high p-8 rounded-[16px] border border-outline-variant">
<h4 class="font-label-md text-primary mb-4 uppercase tracking-wider">Flagship Clinic</h4>
<p class="font-headline-md text-headline-md text-on-surface mb-2">The Heritage House</p>
<p class="font-body-md text-on-surface-variant mb-6">
42 Clinical Gardens, Wellness District<br/>
New Delhi, India 110001
</p>
<div class="pt-4 border-t border-outline-variant">
<p class="font-label-md text-on-surface">Mon – Sat: 08:00 AM – 08:00 PM</p>
<p class="font-label-md text-on-surface">Sun: Closed for Botanical Harvest</p>
</div>
</div>
</aside>
</section>
<section class="mb-section-gap">
<div class="rounded-[24px] overflow-hidden shadow-sm h-[400px] relative border border-outline-variant">
<div class="absolute inset-0 bg-surface-dim flex items-center justify-center map-container">
<img alt="Clinic Location Map" class="w-full h-full object-cover opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCFIZo49uitXwv-Anxp3BnrsNgHD7miee-yj_HTZVvcAfT6fZK8bFV8rOwlf4x4EPep7IHfsKGNmqmO9WQimz8JYYriiffsrYOdyEnBZaF7ZPF3KXR0QrEHzRGC5p8ZuoGLX8--6seIsp6f368UKydEVpTzgLj6AjgnbiwpstWgU3VrfIllSO-k8pit-wbjTPr2SKmWalOI6pLV4OTvqXaRbGCS23C9X3kuWfZEiD_NVI8bJbMZe0bS3Lr5wWP4NKRrrA-cWRUIPNM"/>
<div class="absolute bg-surface-container-lowest p-6 rounded-xl shadow-xl border border-outline-variant max-w-sm left-10 bottom-10">
<div class="flex gap-4 items-start">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">location_on</span>
<div>
<h4 class="font-headline-md text-headline-md text-primary">Public Care Flagship</h4>
<p class="font-body-md text-on-surface-variant">The heart of our Ayurvedic research and clinical practice.</p>
<button class="mt-4 text-primary font-bold font-label-md flex items-center gap-1">Get Directions <span class="material-symbols-outlined text-sm">arrow_forward</span></button>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="mb-section-gap max-w-4xl mx-auto">
<div class="text-center mb-12">
<h2 class="font-display-lg text-display-lg text-primary mb-4">Frequently Asked Questions</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant">Common inquiries regarding our clinical standards and patient care.</p>
</div>
<div class="space-y-4">
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">local_shipping</span>
<span class="font-headline-md text-on-surface">Global Shipping &amp; Delivery</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
We offer climate-controlled international shipping to over 40 countries to ensure the potency of our botanical oils and herbs remains intact. Standard delivery within India is 3-5 business days, while international orders typically arrive within 7-14 days depending on customs.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">video_chat</span>
<span class="font-headline-md text-on-surface">How do Digital Consultations work?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
Our digital consultations are conducted via a secure, HIPAA-compliant telehealth platform. Once you book, you'll receive a pre-consultation Dosha assessment form. Your expert physician will then review your history and conduct a 45-minute live visual diagnosis.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">prescriptions</span>
<span class="font-headline-md text-on-surface">Safety and Clinical Guidelines</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
All treatments prescribed at Ayurwellness undergo rigorous quality control. Our herbs are sourced from organic heritage farms and tested for purity, heavy metals, and potency. We strictly follow clinical guidelines that bridge traditional Ayurvedic texts with modern safety standards.
</div>
</details>
</div>
</section>
</main>
<style>
.faq-accordion[open] .faq-icon { transform: rotate(180deg); }
.map-container { filter: grayscale(0.2) contrast(1.1) brightness(0.95); }
</style>
<script>
const faqs = document.querySelectorAll('.faq-accordion');
faqs.forEach(faq => {
    faq.addEventListener('toggle', function() {
        if (this.open) {
            faqs.forEach(otherFaq => {
                if (otherFaq !== this) otherFaq.removeAttribute('open');
            });
        }
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
