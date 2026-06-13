<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';

$site_title = 'Language';
$languages = [
    'en' => ['English', 'English'], 'hi' => ['हिन्दी', 'Hindi'], 'te' => ['తెలుగు', 'Telugu'], 'ta' => ['தமிழ்', 'Tamil'], 'ml' => ['മലയാളം', 'Malayalam'], 'kn' => ['ಕನ್ನಡ', 'Kannada'],
    'bn' => ['বাংলা', 'Bengali'], 'mr' => ['मराठी', 'Marathi'], 'gu' => ['ગુજરાતી', 'Gujarati'], 'pa' => ['ਪੰਜਾਬੀ', 'Punjabi'], 'or' => ['ଓଡ଼ିଆ', 'Odia'], 'sa' => ['संस्कृत', 'Sanskrit'],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $lang = $_POST['language'] ?? 'en';
    $_SESSION['lang'] = $lang;
    if (isset($_SESSION['user_id']) && column_exists(getDB(), 'users', 'preferred_language')) {
        $stmt = getDB()->prepare('UPDATE users SET preferred_language = ? WHERE id = ?');
        $uid = (int)$_SESSION['user_id'];
        $stmt->bind_param('si', $lang, $uid);
        $stmt->execute();
    }
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mx-auto max-w-5xl">
    <div class="mb-8 flex items-center gap-4"><a href="<?= BASE_URL ?>/profile.php" class="material-symbols-outlined text-primary">arrow_back</a><div><h1 class="font-display-lg text-display-lg text-primary">Language</h1><p class="text-on-surface-variant">Choose your preferred language · अपनी पसंदीदा भाषा चुनें</p></div></div>
    <input id="language-search" class="mb-6 w-full rounded-xl border-outline-variant p-4 focus:border-primary focus:ring-primary" placeholder="Search language...">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <h2 class="mb-3 text-label-lg uppercase tracking-wider text-on-surface-variant">Popular</h2>
        <div class="mb-8 flex flex-wrap gap-3"><?php foreach (['en','hi','te','ta','bn'] as $code): ?><button type="button" data-pick="<?= $code ?>" class="rounded-full border border-outline-variant px-5 py-2 hover:bg-primary-fixed"><?= h($languages[$code][0]) ?></button><?php endforeach; ?></div>
        <div id="language-grid" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($languages as $code => $lang): ?>
            <label data-language-card class="flex cursor-pointer items-center justify-between rounded-xl border border-outline-variant bg-surface-container-lowest p-5 transition hover:bg-primary-fixed/20 has-[:checked]:border-primary has-[:checked]:ring-2 has-[:checked]:ring-primary">
                <span><span class="block font-title-lg text-title-lg"><?= h($lang[0]) ?></span><span class="text-on-surface-variant"><?= h($lang[1]) ?></span></span>
                <input type="radio" name="language" value="<?= h($code) ?>" <?= ($_SESSION['lang'] ?? 'en') === $code ? 'checked' : '' ?> class="text-primary focus:ring-primary">
            </label>
            <?php endforeach; ?>
        </div>
        <div id="language-empty" class="mt-6 hidden"><?= empty_state('search_off', 'No languages matching your search', 'Try searching in English or your native script.') ?></div>
        <button class="mt-8 w-full rounded-lg bg-primary py-4 font-label-lg text-on-primary">Continue in selected language</button>
        <p class="mt-3 text-center text-on-surface-variant">You can change language anytime from Settings.</p>
    </form>
</div>
</section>
<script>
document.querySelectorAll('[data-pick]').forEach(button => button.addEventListener('click', () => document.querySelector(`input[value="${button.dataset.pick}"]`)?.click()));
document.getElementById('language-search')?.addEventListener('input', function(){let shown=0;document.querySelectorAll('[data-language-card]').forEach(card=>{const ok=card.textContent.toLowerCase().includes(this.value.toLowerCase());card.classList.toggle('hidden',!ok);if(ok)shown++;});document.getElementById('language-empty').classList.toggle('hidden',shown>0);});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
