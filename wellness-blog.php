<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Wellness Blog';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$posts = $db->query("SELECT * FROM blog_posts ORDER BY published_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<section class="max-w-container-max mx-auto px-margin-desktop py-10">
<div class="flex flex-wrap items-center gap-3 mb-12">
<span class="text-label-md text-outline uppercase mr-2">Explore:</span>
<a href="<?= BASE_URL ?>/wellness-blog.php" class="px-5 py-2 rounded-full bg-primary text-on-primary font-label-md transition-all">All Articles</a>
<a class="px-5 py-2 rounded-full bg-surface-container-high text-on-surface-variant font-label-md hover:bg-primary-fixed transition-all" href="#">Herbal Remedies</a>
<a class="px-5 py-2 rounded-full bg-surface-container-high text-on-surface-variant font-label-md hover:bg-primary-fixed transition-all" href="#">Mental Wellness</a>
<a class="px-5 py-2 rounded-full bg-surface-container-high text-on-surface-variant font-label-md hover:bg-primary-fixed transition-all" href="#">Nutrition</a>
</div>

<?php if (!empty($posts)): ?>
<?php $featured = $posts[0]; ?>
<section class="relative rounded-3xl overflow-hidden mb-section-gap group">
<div class="aspect-[21/9] w-full overflow-hidden">
<img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= htmlspecialchars($featured['image_url']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
</div>
<div class="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/20 to-transparent flex flex-col justify-end p-12">
<div class="max-w-2xl">
<span class="inline-block px-3 py-1 bg-secondary-container text-on-secondary-container rounded text-label-sm font-bold mb-4 uppercase tracking-widest">Featured Story</span>
<h1 class="font-display-lg text-display-lg text-white mb-6"><?= htmlspecialchars($featured['title']) ?></h1>
<p class="text-white/90 text-body-lg mb-8 line-clamp-2"><?= htmlspecialchars($featured['excerpt']) ?></p>
<span class="inline-block bg-secondary-container text-on-secondary-container px-8 py-4 rounded-lg font-label-md hover:bg-secondary-fixed transition-all cursor-pointer">Read Full Article <span class="material-symbols-outlined align-middle">arrow_forward</span></span>
</div>
</div>
</section>
<?php endif; ?>

<div class="flex flex-col lg:flex-row gap-gutter">
<div class="lg:w-2/3">
<h2 class="font-headline-lg text-headline-lg mb-8 flex items-center gap-3">Recent Insights <div class="h-px flex-grow bg-outline-variant"></div></h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
<?php foreach ($posts as $i => $post): if ($i === 0) continue; ?>
<article class="flex flex-col gap-4 group">
<div class="aspect-[4/3] rounded-2xl overflow-hidden bg-surface-container">
<img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
</div>
<div class="space-y-3">
<div class="flex items-center gap-3 text-label-sm text-outline uppercase font-bold">
<span><?= htmlspecialchars($post['category'] ?? 'General') ?></span>
<span class="w-1 h-1 rounded-full bg-outline-variant"></span>
<span><?= date('M d, Y', strtotime($post['published_at'])) ?></span>
</div>
<h3 class="font-headline-md text-headline-md group-hover:text-primary transition-colors"><?= htmlspecialchars($post['title']) ?></h3>
<p class="text-on-surface-variant line-clamp-2"><?= htmlspecialchars($post['excerpt']) ?></p>
</div>
</article>
<?php endforeach; ?>
</div>
</div>

<aside class="lg:w-1/3 space-y-12">
<div class="bg-surface-container-low rounded-3xl p-8 border border-outline-variant">
<h4 class="font-headline-md text-headline-md mb-6 flex items-center gap-2"><span class="material-symbols-outlined text-primary">trending_up</span> Trending Now</h4>
<ul class="space-y-6">
<li class="group cursor-pointer"><span class="text-display-lg text-primary-fixed-dim font-bold opacity-30 float-left mr-4 leading-none">01</span><div><h5 class="font-label-md text-on-surface group-hover:text-primary mb-1">Understanding Vata Imbalance During Travel</h5><p class="text-label-sm text-outline">12.5k Reads</p></div></li>
<li class="group cursor-pointer"><span class="text-display-lg text-primary-fixed-dim font-bold opacity-30 float-left mr-4 leading-none">02</span><div><h5 class="font-label-md text-on-surface group-hover:text-primary mb-1">Top 5 Herbs for Immune Resilience</h5><p class="text-label-sm text-outline">9.2k Reads</p></div></li>
<li class="group cursor-pointer"><span class="text-display-lg text-primary-fixed-dim font-bold opacity-30 float-left mr-4 leading-none">03</span><div><h5 class="font-label-md text-on-surface group-hover:text-primary mb-1">The Microbiome and Ayurvedic Digestion</h5><p class="text-label-sm text-outline">8.8k Reads</p></div></li>
</ul>
</div>
<div class="bg-primary text-on-primary rounded-3xl p-8 shadow-xl relative overflow-hidden">
<div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-container rounded-full opacity-50 blur-3xl"></div>
<div class="relative z-10">
<span class="material-symbols-outlined text-secondary-fixed mb-4 text-4xl">eco</span>
<h4 class="font-headline-md text-headline-md mb-3 text-white">Join our Wellness Community</h4>
<p class="text-on-primary-container mb-6 font-body-md opacity-90">Receive curated Ayurvedic wisdom directly in your inbox twice a month.</p>
<form class="space-y-3">
<input class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:ring-2 focus:ring-secondary-fixed outline-none" placeholder="Your email address" type="email"/>
<button class="w-full py-3 bg-secondary-container text-on-secondary-container font-label-md rounded-lg hover:bg-secondary-fixed transition-all" type="submit">Subscribe Now</button>
</form>
</div>
</div>
</aside>
</div>
</section>

<script>
const observer = new IntersectionObserver((entries) => {
entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('opacity-100', 'translate-y-0'); entry.target.classList.remove('opacity-0', 'translate-y-8'); } });
}, { threshold: 0.1 });
document.querySelectorAll('article').forEach(a => { a.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-8'); observer.observe(a); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
