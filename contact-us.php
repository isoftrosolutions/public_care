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
Whether you need blood test booking help, report upload support, doctor consultation guidance or emergency helpline information, the AyurViora team is here to help.
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
<option value="Blood Test Booking" <?= ($form_data['subject'] ?? '') === 'Blood Test Booking' ? 'selected' : '' ?>>Blood Test Booking</option>
<option value="Report Upload Help" <?= ($form_data['subject'] ?? '') === 'Report Upload Help' ? 'selected' : '' ?>>Report Upload Help</option>
<option value="Doctor Consultation" <?= ($form_data['subject'] ?? '') === 'Doctor Consultation' ? 'selected' : '' ?>>Doctor Consultation</option>
<option value="Payment Support" <?= ($form_data['subject'] ?? '') === 'Payment Support' ? 'selected' : '' ?>>Payment Support</option>
<option value="Emergency Information" <?= ($form_data['subject'] ?? '') === 'Emergency Information' ? 'selected' : '' ?>>Emergency Information</option>
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
<a class="flex items-center gap-4 group" href="tel:+919999999999">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">call</span>
</div>
<div>
<p class="text-label-sm opacity-70">Phone Support</p>
<p class="font-headline-md text-headline-md">+91 99999 99999</p>
</div>
</a>
<a class="flex items-center gap-4 group" href="mailto:support@ayurviora.com">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">mail</span>
</div>
<div>
<p class="text-label-sm opacity-70">Email Us</p>
<p class="font-headline-md text-headline-md">support@ayurviora.com</p>
</div>
</a>
<a class="flex items-center gap-4 group" href="https://wa.me/919999999999">
<div class="bg-primary-container p-3 rounded-full group-hover:bg-tertiary-container transition-colors">
<span class="material-symbols-outlined">chat</span>
</div>
<div>
<p class="text-label-sm opacity-70">WhatsApp Concierge</p>
<p class="font-headline-md text-headline-md">+91 99999 99999</p>
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
<p class="font-label-md text-on-surface">Mon - Sat: 08:00 AM - 08:00 PM</p>
<p class="font-label-md text-on-surface">Sun: Emergency helpline info only</p>
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
<h4 class="font-headline-md text-headline-md text-primary">AyurViora Support Center</h4>
<p class="font-body-md text-on-surface-variant">Help for tests, consultations, report uploads and patient support.</p>
<button class="mt-4 text-primary font-bold font-label-md flex items-center gap-1">Get Directions <span class="material-symbols-outlined text-sm">arrow_forward</span></button>
</div>
</div>
</div>
</div>
</div>
</section>
<section id="emergency-help" class="mb-section-gap">
<div class="rounded-[24px] bg-red-50 border border-red-200 p-8">
<div class="flex flex-col lg:flex-row gap-6 lg:items-center lg:justify-between">
<div>
<p class="text-label-md font-label-md text-red-700 uppercase tracking-wider mb-2">Emergency Help Numbers</p>
<h2 class="font-headline-lg text-headline-lg text-red-900 mb-3">For urgent symptoms, call emergency services immediately</h2>
<p class="text-red-800 max-w-3xl">AyurViora is not an ambulance or emergency response provider. These helplines are shared for quick access during urgent situations.</p>
</div>
<div class="grid sm:grid-cols-3 gap-3 min-w-full lg:min-w-[520px]">
<a href="tel:112" class="rounded-xl bg-white border border-red-200 p-4 text-center">
<span class="material-symbols-outlined text-red-700 text-3xl">emergency_home</span>
<p class="font-bold text-red-900">112</p>
<p class="text-xs text-red-700">National Emergency</p>
</a>
<a href="tel:108" class="rounded-xl bg-white border border-red-200 p-4 text-center">
<span class="material-symbols-outlined text-red-700 text-3xl">ambulance</span>
<p class="font-bold text-red-900">108</p>
<p class="text-xs text-red-700">Ambulance</p>
</a>
<a href="tel:102" class="rounded-xl bg-white border border-red-200 p-4 text-center">
<span class="material-symbols-outlined text-red-700 text-3xl">pregnant_woman</span>
<p class="font-bold text-red-900">102</p>
<p class="text-xs text-red-700">Mother & Child</p>
</a>
</div>
</div>
</div>
</section>
<section class="mb-section-gap max-w-4xl mx-auto">
<div class="text-center mb-12">
<h2 class="font-display-lg text-display-lg text-primary mb-4">Frequently Asked Questions</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant">Common launch questions about tests, reports, consultations and payments.</p>
</div>
<div class="space-y-4">
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">local_shipping</span>
<span class="font-headline-md text-on-surface">Blood sample ghar se liya jayega?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
Yes. For supported service areas, a trained phlebotomist collects the blood sample from your home at the selected date and time slot.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">quickreply</span>
<span class="font-headline-md text-on-surface">Report kitne time mein milegi?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
Most routine reports are delivered digitally within 24-48 hours. Some specialized tests may take longer and the expected time is shown on the lab test card.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">video_chat</span>
<span class="font-headline-md text-on-surface">Doctor video call kaise karega?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
After booking, you receive appointment details and a secure video consultation link. Join from your phone or computer at the scheduled time.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">payments</span>
<span class="font-headline-md text-on-surface">Payment kaise hogi?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
You can pay online during booking where available. The team can also guide you for UPI/payment-link support if manual confirmation is needed.
</div>
</details>
<details class="faq-accordion group bg-surface-container-low border border-outline-variant rounded-[16px] overflow-hidden">
<summary class="flex justify-between items-center p-6 cursor-pointer list-none">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary">upload_file</span>
<span class="font-headline-md text-on-surface">Report upload ke baad AI explanation kya karega?</span>
</div>
<span class="faq-icon material-symbols-outlined transition-transform duration-300">expand_more</span>
</summary>
<div class="p-6 pt-0 font-body-md text-on-surface-variant leading-relaxed">
AI explanation gives a simple educational summary of common report values and suggests what to discuss with a doctor. It does not replace diagnosis or treatment.
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

