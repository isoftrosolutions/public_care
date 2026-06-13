</main>

<footer class="bg-surface-container-high dark:bg-surface-dim w-full">
<div class="w-full px-base md:px-margin-desktop py-16 grid grid-cols-1 md:grid-cols-4 gap-gutter max-w-container-max mx-auto">
<div class="space-y-4">
<h3 class="font-headline-md text-headline-md font-bold text-primary dark:text-inverse-primary"><?= SITE_NAME ?></h3>
<p class="font-body-md text-body-md text-on-surface-variant">Your trusted partner in holistic wellness and traditional Ayurvedic care for a modern lifestyle.</p>
<div class="flex gap-3 pt-2">
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="#"><span class="material-symbols-outlined">public</span></a>
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="#"><span class="material-symbols-outlined">alternate_email</span></a>
<a class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-full text-primary hover:bg-primary hover:text-on-primary transition-all" href="#"><span class="material-symbols-outlined">call</span></a>
</div>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold">Quick Links</h4>
<ul class="space-y-2">
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/shop.php">Shop All</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/doctor-listing.php">Consult Doctor</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/about-us.php">About Us</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="<?= BASE_URL ?>/wellness-blog.php">Wellness Blog</a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold">Support</h4>
<ul class="space-y-2">
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="#">Contact Us</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="#">Shipping &amp; Returns</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="#">Privacy Policy</a></li>
<li><a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-all" href="#">Terms of Service</a></li>
</ul>
</div>
<div class="space-y-4">
<h4 class="font-label-lg text-label-lg text-on-surface font-bold">Newsletter</h4>
<p class="font-body-md text-body-md text-on-surface-variant">Subscribe to get wellness tips and exclusive offers.</p>
<div class="flex gap-2">
<input class="bg-surface-container-lowest border border-outline-variant rounded-lg flex-grow px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md text-body-md" placeholder="Email address" type="email"/>
<button class="bg-primary text-on-primary p-2 rounded-lg hover:bg-primary-container transition-all"><span class="material-symbols-outlined">send</span></button>
</div>
</div>
</div>
<div class="border-t border-outline-variant/30 py-8 px-base md:px-margin-desktop text-center">
<p class="font-body-md text-body-md text-on-surface-variant">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Holistic wellness through ancient wisdom.</p>
</div>
</footer>

<script>
document.querySelectorAll('section > div, .animate-on-scroll').forEach(el => {
el.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-10');
const observer = new IntersectionObserver((entries) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
entry.target.classList.add('opacity-100', 'translate-y-0');
entry.target.classList.remove('opacity-0', 'translate-y-10');
}
});
}, { threshold: 0.1 });
observer.observe(el);
});
</script>
<?php require_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
