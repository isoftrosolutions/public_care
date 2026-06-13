<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Delete Doctor';
$active_page = 'doctors';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM doctors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    header('Location: doctors.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $stmt = $db->prepare('DELETE FROM doctors WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        admin_delete_local_upload($doctor['image_url'] ?? null);
    }
    header('Location: doctors.php');
    exit;
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="mb-8">
<h2 class="text-display-lg font-display-lg text-primary">Delete Doctor</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Confirm before removing this doctor profile.</p>
</header>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 max-w-2xl">
<div class="flex items-start gap-4">
<?php if (!empty($doctor['image_url'])): ?>
<img src="<?= htmlspecialchars($doctor['image_url']) ?>" alt="" class="w-20 h-20 rounded-full object-cover bg-surface-container-high">
<?php else: ?>
<div class="w-20 h-20 rounded-full bg-surface-container-high flex items-center justify-center"><span class="material-symbols-outlined text-on-surface-variant">person</span></div>
<?php endif; ?>
<div>
<h3 class="text-headline-md text-on-surface"><?= htmlspecialchars($doctor['name']) ?></h3>
<p class="text-body-md text-on-surface-variant mt-1"><?= htmlspecialchars($doctor['specialty'] ?? 'General') ?></p>
<p class="text-label-sm text-error mt-4">This action cannot be undone.</p>
</div>
</div>
<form method="POST" class="flex gap-3 mt-8">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<button type="submit" class="bg-error text-on-error px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity">Delete Doctor</button>
<a href="doctors.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
</form>
</section>
</main>
</body>
</html>
