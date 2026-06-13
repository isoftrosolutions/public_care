<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Edit Doctor';
$active_page = 'doctors';
$error = '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM doctors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$form_doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$form_doctor) {
    header('Location: doctors.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        try {
            $name = trim($_POST['name'] ?? '');
            $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($name)));
            $qualifications = trim($_POST['qualifications'] ?? '');
            $specialty = trim($_POST['specialty'] ?? '');
            $experience_years = (int)($_POST['experience_years'] ?? 0);
            $languages = trim($_POST['languages'] ?? '');
            $fee = (float)($_POST['fee'] ?? 0);
            $bio = trim($_POST['bio'] ?? '');
            $available = isset($_POST['available']) ? 1 : 0;
            $image_url = admin_upload_image('image_file', 'doctors', $form_doctor['image_url'] ?? null);

            if ($name === '') {
                throw new RuntimeException('Doctor name is required.');
            }

            $stmt = $db->prepare("UPDATE doctors SET name=?, slug=?, qualifications=?, specialty=?, experience_years=?, languages=?, fee=?, bio=?, image_url=?, available=? WHERE id=?");
            $stmt->bind_param('sssssisssii', $name, $slug, $qualifications, $specialty, $experience_years, $languages, $fee, $bio, $image_url, $available, $id);
            $stmt->execute();
            header('Location: doctors.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            $form_doctor = array_merge($form_doctor, $_POST);
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Edit Doctor</h2>
<p class="text-body-lg text-on-surface-variant mt-2"><?= htmlspecialchars($form_doctor['name']) ?></p>
</div>
<a href="doctors.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">arrow_back</span> Doctors</a>
</header>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<?php $submit_label = 'Update Doctor'; require __DIR__ . '/includes/doctor-form.php'; ?>
</section>
</main>
</body>
</html>
