<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Write a Review';
include __DIR__ . '/includes/header.php';

$db = getDB();
$error = '';
$success = '';

// Get products and doctors for selection
$products = $db->query("SELECT id, name FROM products WHERE stock > 0 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$doctors = $db->query("SELECT id, name FROM doctors WHERE available = 1 AND reviews_count > 0 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = t('login_required');
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $rating = isset($_POST['rating']) ? min(5, max(1, (int)$_POST['rating'])) : 0;
        $comment = trim($_POST['comment'] ?? '');
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $doctor_id = !empty($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : null;

        if ($rating < 1) {
            $error = t('review_rating_required');
        } elseif (empty($comment)) {
            $error = t('review_comment_required');
        } elseif (!$product_id && !$doctor_id) {
            $error = t('review_target_required');
        } else {
            $stmt = $db->prepare("INSERT INTO reviews (product_id, doctor_id, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('iiiis', $product_id, $doctor_id, $_SESSION['user_id'], $rating, $comment);
            if ($stmt->execute()) {
                $success = t('review_submitted');
            } else {
                $error = 'Failed to submit review. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<section class="pt-[100px] pb-section-gap max-w-[1200px] mx-auto px-gutter">
<div class="max-w-2xl mx-auto">
<h1 class="font-headline-lg text-headline-lg text-primary mb-2"><?= t('write_review') ?></h1>
<p class="text-body-md text-on-surface-variant mb-8"><?= t('review_subtitle') ?></p>

<?php if ($error): ?>
<div class="bg-error-container text-on-error-container p-4 rounded-xl mb-6 flex items-center gap-3">
<span class="material-symbols-outlined">error</span>
<span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-primary-fixed text-on-primary-fixed p-6 rounded-xl mb-6 text-center">
<span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
<p class="font-bold text-lg"><?= htmlspecialchars($success) ?></p>
<p class="text-sm mt-1"><?= t('review_thanks') ?></p>
<a href="<?= BASE_URL ?>/index.php" class="inline-block mt-4 bg-primary text-on-primary px-6 py-2.5 rounded-full font-label-lg"><?= t('nav_home') ?></a>
</div>
<?php else: ?>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="bg-surface-container-low rounded-xl p-8 text-center">
<span class="material-symbols-outlined text-5xl text-outline mb-4">person</span>
<h2 class="font-headline-md text-headline-md text-primary mb-2"><?= t('login_required') ?></h2>
<p class="text-body-md text-on-surface-variant mb-6"><?= t('review_login_prompt') ?></p>
<a href="<?= BASE_URL ?>/login.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-full font-label-lg"><?= t('nav_login') ?></a>
</div>
<?php else: ?>

<form method="POST" class="space-y-6">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="bg-white rounded-2xl border border-outline-variant/30 p-6 space-y-6">
<div>
<label class="font-label-lg text-on-surface block mb-2"><?= t('review_for') ?></label>
<div class="grid grid-cols-2 gap-4">
<div>
<p class="text-label-sm text-outline mb-1"><?= t('nav_medicine') ?></p>
<select name="product_id" class="w-full border border-outline-variant rounded-xl px-4 py-3 text-body-md focus:ring-2 focus:ring-primary outline-none bg-surface">
<option value="">— <?= t('select_product') ?> —</option>
<?php foreach ($products as $p): ?>
<option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
<?php endforeach; ?>
</select>
</div>
<div>
<p class="text-label-sm text-outline mb-1"><?= t('nav_consult') ?></p>
<select name="doctor_id" class="w-full border border-outline-variant rounded-xl px-4 py-3 text-body-md focus:ring-2 focus:ring-primary outline-none bg-surface">
<option value="">— <?= t('select_doctor') ?> —</option>
<?php foreach ($doctors as $d): ?>
<option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
</div>

<div>
<label class="font-label-lg text-on-surface block mb-2"><?= t('review_rating') ?></label>
<div class="flex gap-2" id="star-rating">
<?php for ($i = 1; $i <= 5; $i++): ?>
<button type="button" class="star-btn w-12 h-12 rounded-xl border border-outline-variant flex items-center justify-center text-2xl hover:bg-tertiary-fixed/30 transition-all" data-value="<?= $i ?>">
<span class="material-symbols-outlined text-tertiary-fixed-dim" style="font-variation-settings: 'FILL' 0;">star</span>
</button>
<?php endfor; ?>
</div>
<input type="hidden" name="rating" id="rating-input" value="0">
</div>

<div>
<label class="font-label-lg text-on-surface block mb-2" for="comment"><?= t('review_comment') ?></label>
<textarea id="comment" name="comment" rows="5" class="w-full border border-outline-variant rounded-xl px-4 py-3 text-body-md focus:ring-2 focus:ring-primary outline-none bg-surface resize-none" placeholder="<?= htmlspecialchars(t('review_placeholder')) ?>"></textarea>
</div>

<button type="submit" class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-label-lg hover:bg-primary-container transition-all active:scale-[0.98]"><?= t('submit_review') ?></button>
</div>
</form>
<?php endif; ?>
<?php endif; ?>
</div>
</section>

<script>
document.querySelectorAll('.star-btn').forEach(btn => {
btn.addEventListener('click', function() {
const val = parseInt(this.dataset.value);
document.getElementById('rating-input').value = val;
document.querySelectorAll('.star-btn').forEach((s, i) => {
const icon = s.querySelector('.material-symbols-outlined');
if (i < val) {
icon.style.setProperty('font-variation-settings', "'FILL' 1");
s.classList.add('bg-tertiary-fixed/20');
} else {
icon.style.setProperty('font-variation-settings', "'FILL' 0");
s.classList.remove('bg-tertiary-fixed/20');
}
});
});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
