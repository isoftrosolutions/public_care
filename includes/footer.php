</main>

<footer class="bg-primary text-on-primary w-full py-section-gap px-gutter">
<div class="max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
<div class="col-span-1">
<img alt="Public Care Ayurveda Logo" class="h-12 w-auto object-contain brightness-0 invert mb-6" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAo879HCds3w21ve37kcaDk6TDBD6jGLmvKyZY044-2j4TI7_Fo3jg5MGIn2vS_Uy3jg523kaixaI9ewUNDrQnspvl92wCOxeXw3qX_NNldczaZIT3RTuZZl-ERSsjdCWSpLmC_9EVaIHo1ut2kqTZDdm2Ighvwpvul1Cg1nfmqPr1xgZydE72HjEN6ISVD-7gJT1zBWpbOG5yyRaloF-sBOHDbzme6CNKxf-SZ6ElHJDdS2ew6k7nkVl2Ul0YZYjPaefVIy8Wakas"/>
<p class="text-sm opacity-80 leading-relaxed">Bridging ancient holistic wisdom with clinical precision for the modern family.</p>
</div>
<div>
<h4 class="font-headline-md text-headline-md mb-6">Quick Links</h4>
<ul class="space-y-3">
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="<?= BASE_URL ?>/shop.php">Shop All</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="<?= BASE_URL ?>/doctor-listing.php">Our Doctors</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="<?= BASE_URL ?>/about-us.php">About Ayurveda</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="#">Bulk Orders</a></li>
</ul>
</div>
<div>
<h4 class="font-headline-md text-headline-md mb-6">Customer Care</h4>
<ul class="space-y-3">
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="#">Privacy Policy</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="#">Terms of Service</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="#">Shipping Info</a></li>
<li><a class="text-primary-fixed-dim hover:text-on-primary text-sm" href="#">Returns</a></li>
</ul>
</div>
<div>
<h4 class="font-headline-md text-headline-md mb-6">Contact Us</h4>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm opacity-80"><span class="material-symbols-outlined text-sm">mail</span> support@publiccareayurveda.com</li>
<li class="flex items-center gap-3 text-sm opacity-80"><span class="material-symbols-outlined text-sm">phone</span> +1 (800) AYURVEDA</li>
<li class="flex items-center gap-3 text-sm opacity-80"><span class="material-symbols-outlined text-sm">location_on</span> 123 Wellness Blvd, Heritage City</li>
</ul>
</div>
</div>
<div class="border-t border-white/10 pt-10 text-center">
<p class="font-label-sm text-label-sm opacity-60">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Holistic wellness through ancient wisdom.</p>
</div>
</footer>

<script>
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
if (mobileMenuBtn) {
mobileMenuBtn.addEventListener('click', () => {
const nav = document.querySelector('nav');
if (nav) {
nav.classList.toggle('mobile-open');
}
});
}
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
</body>
</html>
