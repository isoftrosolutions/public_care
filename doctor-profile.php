<?php
require_once __DIR__ . '/includes/config.php';

$db = getDB();
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$doctor = $db->query("SELECT * FROM doctors WHERE id = $doctor_id")->fetch_assoc();

if (!$doctor) {
    http_response_code(404);
    $site_title = 'Doctor Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="max-w-container-max mx-auto px-margin-desktop py-24 text-center"><div class="w-24 h-24 bg-error-container rounded-full flex items-center justify-center mx-auto mb-8"><span class="material-symbols-outlined text-5xl text-error">person_off</span></div>';
    echo '<h1 class="font-headline-lg text-headline-lg text-primary mb-4">Doctor Not Found</h1>';
    echo '<p class="text-body-lg text-on-surface-variant mb-8">The doctor you are looking for does not exist or may have been removed.</p>';
    echo '<a href="' . BASE_URL . '/doctor-listing.php" class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-md">Browse All Doctors</a>';
    echo '</section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$site_title = htmlspecialchars($doctor['name']);
require_once __DIR__ . '/includes/header.php';
?>

<section class="max-w-container-max mx-auto px-margin-desktop py-12">
<section class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-end mb-section-gap">
<div class="lg:col-span-4">
<div class="aspect-[4/5] rounded-3xl overflow-hidden shadow-sm border border-outline-variant bg-surface-container">
<img alt="<?= htmlspecialchars($doctor['name']) ?>" class="w-full h-full object-cover" src="<?= htmlspecialchars($doctor['image_url']) ?>">
</div>
</div>
<div class="lg:col-span-8 pb-4">
<div class="flex items-center gap-3 mb-2">
<span class="bg-primary-fixed text-on-primary-fixed px-3 py-1 rounded-full text-label-sm flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">verified</span> Verified Practitioner
</span>
<span class="text-secondary font-label-md flex items-center gap-1">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<?= number_format($doctor['rating'], 1) ?> (<?= (int)$doctor['reviews_count'] ?>+ Reviews)
</span>
</div>
<h1 class="text-display-lg font-display-lg text-primary mb-2"><?= htmlspecialchars($doctor['name']) ?></h1>
<p class="text-headline-md font-headline-md text-on-surface-variant mb-6"><?= htmlspecialchars($doctor['qualifications']) ?></p>
<div class="flex flex-wrap gap-4">
<div class="flex items-center gap-3 bg-surface-container-low p-4 rounded-2xl border border-outline-variant min-w-[180px]">
<span class="material-symbols-outlined text-primary text-3xl">workspace_premium</span>
<div><p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Experience</p><p class="text-body-md font-bold text-on-surface"><?= (int)$doctor['experience_years'] ?>+ Years</p></div>
</div>
<div class="flex items-center gap-3 bg-surface-container-low p-4 rounded-2xl border border-outline-variant min-w-[180px]">
<span class="material-symbols-outlined text-primary text-3xl">ecg_heart</span>
<div><p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Specialty</p><p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($doctor['specialty']) ?></p></div>
</div>
<div class="flex items-center gap-3 bg-surface-container-low p-4 rounded-2xl border border-outline-variant min-w-[180px]">
<span class="material-symbols-outlined text-primary text-3xl">language</span>
<div><p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Languages</p><p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($doctor['languages']) ?></p></div>
</div>
</div>
</div>
</section>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
<div class="lg:col-span-2 space-y-section-gap">
<article>
<h2 class="text-headline-lg font-headline-lg text-primary mb-6 flex items-center gap-3"><span class="w-8 h-px bg-primary"></span> Professional Profile</h2>
<div class="text-body-lg text-on-surface-variant space-y-4 leading-relaxed">
<?= $doctor['bio'] ? '<p>' . nl2br(htmlspecialchars($doctor['bio'])) . '</p>' : '<p>No biography available yet.</p>' ?>
</div>
</article>
<?php if (!empty($doctor['experience'])): ?>
<section>
<h2 class="text-headline-lg font-headline-lg text-primary mb-8 flex items-center gap-3"><span class="w-8 h-px bg-primary"></span> Professional Journey</h2>
<div class="space-y-8 relative before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-px before:bg-outline-variant">
<?php
$exp_items = explode("\n", $doctor['experience']);
foreach ($exp_items as $i => $item):
$item = trim($item);
if (empty($item)) continue;
$parts = explode('|', $item);
$period = $parts[0] ?? '';
$title = $parts[1] ?? '';
$org = $parts[2] ?? '';
$desc = $parts[3] ?? '';
$is_first = $i === 0;
?>
<div class="relative pl-10">
<div class="absolute left-0 top-1.5 w-[22px] h-[22px] rounded-full <?= $is_first ? 'bg-primary border-4 border-surface' : 'bg-outline-variant border-4 border-surface' ?>"></div>
<p class="text-label-md <?= $is_first ? 'text-primary' : 'text-on-surface-variant' ?> font-bold"><?= htmlspecialchars($period) ?></p>
<h3 class="font-bold text-body-lg text-on-surface"><?= htmlspecialchars($title) ?></h3>
<p class="text-on-surface-variant"><?= htmlspecialchars($org) ?></p>
<?php if ($desc): ?><p class="text-body-md text-on-surface-variant mt-2"><?= htmlspecialchars($desc) ?></p><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<?php if (!empty($doctor['education'])): ?>
<section>
<h2 class="text-headline-lg font-headline-lg text-primary mb-8 flex items-center gap-3"><span class="w-8 h-px bg-primary"></span> Education &amp; Certifications</h2>
<div class="space-y-6">
<?php
$edu_items = explode("\n", $doctor['education']);
foreach ($edu_items as $item):
$item = trim($item);
if (empty($item)) continue;
$parts = explode('|', $item);
$degree = $parts[0] ?? '';
$institution = $parts[1] ?? '';
$year = $parts[2] ?? '';
?>
<div class="flex items-start gap-4 p-4 bg-surface-container-low rounded-xl border border-outline-variant">
<div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center flex-shrink-0 mt-1">
<span class="material-symbols-outlined text-primary">school</span>
</div>
<div>
<p class="font-bold text-on-surface"><?= htmlspecialchars($degree) ?></p>
<p class="text-body-md text-on-surface-variant"><?= htmlspecialchars($institution) ?></p>
<?php if ($year): ?><p class="text-label-sm text-outline mt-1"><?= htmlspecialchars($year) ?></p><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<section>
<div class="flex justify-between items-end mb-8">
<h2 class="text-headline-lg font-headline-lg text-primary flex items-center gap-3"><span class="w-8 h-px bg-primary"></span> Patient Testimonials</h2>
<button class="text-primary font-label-md hover:underline">See all reviews</button>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="bg-surface-container-low p-6 rounded-3xl border border-outline-variant shadow-sm">
<div class="flex justify-between items-start mb-4">
<div class="flex text-secondary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<span class="text-label-sm text-on-surface-variant">2 weeks ago</span>
</div>
<p class="text-body-md italic text-on-surface-variant mb-4">"An exceptional consultation. The doctor's deep understanding of Ayurvedic principles made a significant difference in my treatment plan."</p>
<div class="flex items-center gap-2">
<span class="font-bold text-on-surface text-label-md">Verified Patient</span>
<span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">verified</span>
<span class="text-label-sm text-on-surface-variant">Verified Patient</span>
</div>
</div>
<div class="bg-surface-container-low p-6 rounded-3xl border border-outline-variant shadow-sm">
<div class="flex justify-between items-start mb-4">
<div class="flex text-secondary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<span class="text-label-sm text-on-surface-variant">1 month ago</span>
</div>
<p class="text-body-md italic text-on-surface-variant mb-4">"Professional, empathetic, and extremely knowledgeable. Took the time to explain the 'why' behind the treatment, which is so rare these days."</p>
<div class="flex items-center gap-2">
<span class="font-bold text-on-surface text-label-md">Satisfied Patient</span>
<span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">verified</span>
<span class="text-label-sm text-on-surface-variant">Verified Patient</span>
</div>
</div>
</div>
</section>
</div>
<aside class="lg:col-span-1">
<div class="sticky top-28 bg-white p-8 rounded-[32px] border border-outline-variant shadow-xl ring-1 ring-black/5 overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none"><span class="material-symbols-outlined text-9xl">eco</span></div>
<h3 class="text-headline-md font-headline-md text-primary mb-6">Schedule Consultation</h3>
<div class="space-y-3">
<div class="flex justify-between items-center text-body-md mb-2">
<span class="text-on-surface-variant">Consultation Fee</span>
<span class="font-bold text-primary">₹<?= number_format($doctor['fee'], 2) ?></span>
</div>
<a href="<?= BASE_URL ?>/appointment-booking.php?doctor_id=<?= $doctor['id'] ?>" class="block w-full bg-secondary text-on-primary py-4 rounded-2xl font-bold hover:brightness-110 transition-all shadow-lg shadow-secondary/20 flex items-center justify-center gap-2">Proceed to Payment <span class="material-symbols-outlined">arrow_forward</span></a>
<a href="<?= BASE_URL ?>/appointment-booking.php?doctor_id=<?= $doctor['id'] ?>&type=video" class="block w-full bg-primary text-on-primary py-4 rounded-2xl font-bold hover:brightness-110 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 mt-3"><span class="material-symbols-outlined">videocam</span> Video Consult</a>
<p class="text-center text-label-sm text-on-surface-variant mt-4">Free cancellation up to 24 hours before</p>
</div>
</div>
</aside>
</div>
</section>

<div class="fixed bottom-0 left-0 w-full bg-surface/80 backdrop-blur-md p-4 border-t border-outline-variant z-[60] md:hidden">
<div class="flex items-center justify-between gap-4 max-w-container-max mx-auto">
<div>
<p class="text-label-sm text-on-surface-variant">Consultation Fee</p>
<p class="text-headline-md font-bold text-primary">₹<?= number_format($doctor['fee'], 2) ?></p>
</div>
<div class="flex gap-2">
<a href="<?= BASE_URL ?>/appointment-booking.php?doctor_id=<?= $doctor['id'] ?>" class="bg-primary text-on-primary py-3 px-6 rounded-xl font-bold shadow-lg shadow-primary/20 text-center text-sm">Book Now</a>
<a href="<?= BASE_URL ?>/appointment-booking.php?doctor_id=<?= $doctor['id'] ?>&type=video" class="bg-primary-container text-on-primary-container py-3 px-6 rounded-xl font-bold text-center text-sm flex items-center gap-1"><span class="material-symbols-outlined text-sm">videocam</span> Video</a>
</div>
</div>
</div>

<script>
document.querySelectorAll('button.text-label-sm:not(.opacity-50)').forEach(btn => {
btn.addEventListener('click', function() {
const parent = this.closest('.grid');
if (parent) {
parent.querySelectorAll('button').forEach(b => {
b.classList.remove('border-primary', 'bg-primary-fixed', 'font-bold');
b.classList.add('border-outline-variant');
});
}
this.classList.remove('border-outline-variant');
this.classList.add('border-primary', 'bg-primary-fixed', 'font-bold');
});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
