<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Privacy Policy';
include __DIR__ . '/includes/header.php';
?>
<section class="pt-[100px] pb-section-gap max-w-[1200px] mx-auto px-gutter">
<div class="max-w-3xl mx-auto">
<h1 class="font-headline-lg text-headline-lg text-primary mb-8">Privacy Policy</h1>
<div class="prose prose-green max-w-none space-y-6 text-on-surface-variant">
<p><strong>Last Updated:</strong> June 2026</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">1. Information We Collect</h2>
<p>AyurViora collects information you provide when creating an account, placing orders, booking appointments, uploading health reports, or contacting us. This includes your name, email, phone number, address, health information, and payment details.</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">2. How We Use Your Information</h2>
<p>We use your information to process orders, schedule appointments, provide AI-powered health report analysis, communicate with you about your care, and improve our services.</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">3. Data Protection</h2>
<p>Your health data is highly sensitive. We implement industry-standard encryption and access controls. We never share your health reports with third parties without your explicit consent.</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">4. Cookies</h2>
<p>We use essential cookies for session management and authentication. Analytics cookies help us improve our platform. You can control cookie preferences through your browser settings.</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">5. Your Rights</h2>
<p>You may request access to, correction of, or deletion of your personal data at any time by contacting us. You can manage notification preferences in your account settings.</p>

<h2 class="font-headline-md text-headline-md text-primary mt-8">6. Contact</h2>
<p>For privacy-related inquiries, please contact us through our <a href="<?= BASE_URL ?>/contact-us.php" class="text-primary underline">Contact page</a>.</p>
</div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
