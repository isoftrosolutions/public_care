<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Consult Our Doctors';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$doctors = $db->query("SELECT * FROM doctors WHERE available = 1 ORDER BY rating DESC")->fetch_all(MYSQLI_ASSOC);
?>

<section class="pt-[100px] pb-section-gap max-w-[1200px] mx-auto px-gutter">
<div class="mb-10 space-y-6">
<h1 class="font-headline-lg text-headline-lg text-primary">Find Your Ayurvedic Expert</h1>
<div class="flex flex-col md:flex-row gap-4 items-center">
<div class="relative w-full md:max-w-md">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
<input class="w-full pl-12 pr-4 py-3 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Search by name, specialty or health concern..." type="text"/>
</div>
<div class="flex flex-wrap gap-3 overflow-x-auto pb-2 scrollbar-hide">
<button class="px-4 py-2 bg-primary text-on-primary rounded-full font-label-md text-label-md flex items-center gap-2">Specialty <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span></button>
<button class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-full font-label-md text-label-md hover:bg-surface-container transition-colors flex items-center gap-2">Availability <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span></button>
<button class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-full font-label-md text-label-md hover:bg-surface-container transition-colors flex items-center gap-2">Language <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span></button>
<button class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-full font-label-md text-label-md hover:bg-surface-container transition-colors flex items-center gap-2">Experience <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span></button>
</div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-12 gap-gutter items-start">
<aside class="md:col-span-3 space-y-8 sticky top-[120px] hidden md:block">
<div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/30">
<div class="flex justify-between items-center mb-6">
<h3 class="font-headline-md text-[18px] text-primary">Advanced Filters</h3>
<a href="<?= BASE_URL ?>/doctor-listing.php" class="text-primary font-label-sm text-label-sm hover:underline">Clear all</a>
</div>
<div class="space-y-8">
<div class="space-y-4">
<h4 class="font-label-md text-label-md text-on-surface">Consultation Fee</h4>
<div class="space-y-2">
<label class="flex items-center gap-3 cursor-pointer group"><input class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/><span class="text-body-md text-on-surface-variant group-hover:text-primary">Under ₹500</span></label>
<label class="flex items-center gap-3 cursor-pointer group"><input class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/><span class="text-body-md text-on-surface-variant group-hover:text-primary">₹500 - ₹1000</span></label>
<label class="flex items-center gap-3 cursor-pointer group"><input class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/><span class="text-body-md text-on-surface-variant group-hover:text-primary">₹1000+</span></label>
</div>
</div>
<div class="space-y-4">
<h4 class="font-label-md text-label-md text-on-surface">Doctor Rating</h4>
<div class="space-y-2">
<label class="flex items-center gap-3 cursor-pointer group"><input class="w-4 h-4 border-outline-variant text-primary focus:ring-primary" name="rating" type="radio"/><span class="flex items-center text-body-md text-on-surface-variant">4.5 &amp; Above <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim ml-1" style="font-variation-settings: 'FILL' 1;">star</span></span></label>
<label class="flex items-center gap-3 cursor-pointer group"><input class="w-4 h-4 border-outline-variant text-primary focus:ring-primary" name="rating" type="radio"/><span class="flex items-center text-body-md text-on-surface-variant">4.0 &amp; Above <span class="material-symbols-outlined text-[18px] text-secondary-fixed-dim ml-1" style="font-variation-settings: 'FILL' 1;">star</span></span></label>
</div>
</div>
<div class="space-y-4">
<h4 class="font-label-md text-label-md text-on-surface">Clinic Location</h4>
<select class="w-full bg-surface-container border-none rounded-lg p-3 text-body-md text-on-surface outline-none">
<option>Select City</option>
<option>Bengaluru</option>
<option>Mumbai</option>
<option>New Delhi</option>
<option>Kerala (Wayanad)</option>
</select>
</div>
</div>
</div>
<div class="bg-primary-container p-6 rounded-2xl text-on-primary-container relative overflow-hidden">
<div class="relative z-10">
<p class="font-label-md text-label-md mb-2 opacity-80">Nadi Pariksha</p>
<h4 class="font-headline-md text-[20px] mb-4 text-white">Traditional Pulse Diagnosis at Home</h4>
<button class="bg-secondary-fixed text-on-secondary-fixed px-4 py-2 rounded-lg font-label-md text-label-md">Learn More</button>
</div>
<div class="absolute -right-4 -bottom-4 opacity-20"><span class="material-symbols-outlined text-[100px]">spa</span></div>
</div>
</aside>
<div class="md:col-span-9 space-y-6">
<div class="flex justify-between items-center px-2">
<p class="text-body-md text-on-surface-variant">Showing <span class="font-bold text-on-surface"><?= count($doctors) ?> doctors</span></p>
<div class="flex items-center gap-2">
<span class="text-label-md font-label-md text-outline">Sort by:</span>
<select class="bg-transparent border-none text-primary font-bold focus:ring-0 cursor-pointer">
<option>Relevance</option>
<option>Experience: High to Low</option>
<option>Consultation Fee: Low to High</option>
<option>Rating: High to Low</option>
</select>
</div>
</div>
<div class="space-y-4">
<?php foreach ($doctors as $d): ?>
<div class="bg-white rounded-2xl p-6 border border-outline-variant/30 shadow-sm hover:shadow-md transition-shadow flex flex-col lg:flex-row gap-6">
<div class="flex-shrink-0 relative">
<img class="w-32 h-32 lg:w-40 lg:h-40 object-cover rounded-2xl" src="<?= htmlspecialchars($d['image_url']) ?>" alt="<?= htmlspecialchars($d['name']) ?>">
<div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-full whitespace-nowrap border-2 border-white">AVAILABLE TODAY</div>
</div>
<div class="flex-grow flex flex-col justify-between">
<div class="flex flex-col md:flex-row md:justify-between items-start gap-2">
<div>
<h2 class="font-headline-md text-headline-md text-primary"><?= htmlspecialchars($d['name']) ?></h2>
<p class="text-on-surface-variant font-label-md"><?= htmlspecialchars($d['qualifications']) ?> | <?= htmlspecialchars($d['specialty']) ?></p>
</div>
<div class="bg-tertiary-fixed text-on-tertiary-fixed-variant px-3 py-1 rounded-lg font-label-md text-label-md flex items-center gap-1">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<?= number_format($d['rating'], 1) ?> (<?= (int)$d['reviews_count'] ?> Reviews)
</div>
</div>
<div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-6 mt-4">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline">history</span><span class="text-body-md"><?= (int)$d['experience_years'] ?>+ Years Exp.</span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline">translate</span><span class="text-body-md"><?= htmlspecialchars($d['languages']) ?></span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline">payments</span><span class="text-body-md font-bold">₹<?= number_format($d['fee']) ?></span></div>
</div>
<div class="mt-6 flex flex-wrap gap-4 items-center">
<a class="bg-primary text-on-primary px-8 py-3 rounded-full font-label-md text-label-md hover:opacity-90 transition-all flex items-center gap-2" href="<?= BASE_URL ?>/appointment-booking.php?doctor_id=<?= $d['id'] ?>">Book Consultation <span class="material-symbols-outlined text-[18px]">calendar_today</span></a>
<a class="text-primary font-label-md text-label-md border-b border-primary pb-0.5 hover:text-primary-container transition-colors" href="<?= BASE_URL ?>/doctor-profile.php?id=<?= $d['id'] ?>">View Profile</a>
</div>
</div>
</div>
<?php endforeach; ?>
<?php if (empty($doctors)): ?>
<div class="text-center py-20">
<span class="material-symbols-outlined text-6xl text-outline mb-4">medical_services</span>
<h3 class="font-headline-md text-headline-md text-primary mb-2">No doctors available right now</h3>
<p class="text-body-md text-on-surface-variant">Please check back later for available consultations.</p>
</div>
<?php endif; ?>
</div>
<div class="mt-12 flex items-center justify-center gap-4">
<button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-outline hover:border-primary hover:text-primary transition-colors"><span class="material-symbols-outlined">chevron_left</span></button>
<button class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-on-primary font-label-md">1</button>
<button class="w-10 h-10 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container font-label-md transition-colors">2</button>
<button class="w-10 h-10 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container font-label-md transition-colors">3</button>
<button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant text-outline hover:border-primary hover:text-primary transition-colors"><span class="material-symbols-outlined">chevron_right</span></button>
</div>
</div>
</div>
</section>

<script>
document.querySelectorAll('button.border-outline-variant').forEach(chip => {
chip.addEventListener('click', function() {
this.classList.toggle('bg-primary-container');
this.classList.toggle('text-white');
this.classList.toggle('border-primary-container');
});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
