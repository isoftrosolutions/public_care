<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Prescriptions';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '') && isset($_FILES['prescription_file']) && table_exists($db, 'uploaded_prescriptions')) {
    $file = $_FILES['prescription_file'];
    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= 5 * 1024 * 1024) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
            $dir = __DIR__ . '/uploads/prescriptions';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = 'rx_' . $uid . '_' . time() . '.' . $ext;
            $path = 'uploads/prescriptions/' . $name;
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $path)) {
                $stmt = $db->prepare('INSERT INTO uploaded_prescriptions (user_id, file_path, file_type, notes) VALUES (?, ?, ?, ?)');
                $notes = trim($_POST['notes'] ?? '');
                $stmt->bind_param('isss', $uid, $path, $ext, $notes);
                $stmt->execute();
                $notice = 'Prescription uploaded successfully.';
            }
        }
    }
}
$prescriptions = [];
if (table_exists($db, 'prescriptions')) {
    $stmt = $db->prepare('SELECT p.*, d.name AS doctor_name, d.specialty FROM prescriptions p LEFT JOIN doctors d ON d.id = p.doctor_id WHERE p.user_id = ? ORDER BY p.created_at DESC');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $prescriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
if (!$prescriptions) {
    $prescriptions = [[
        'doctor_name' => 'Dr. Priya Sharma', 'specialty' => 'Ayurvedacharya', 'created_at' => date('Y-m-d'), 'follow_up_date' => date('Y-m-d', strtotime('+90 days')),
        'diagnosis' => 'Digestive imbalance with stress-related sleep disturbance.',
        'medicines' => "Triphala Churna|1 tsp|Bedtime|30 days|Take with warm water\nAshwagandha 500mg|1-0-1|After breakfast and dinner|60 days|Take with milk",
        'advice' => 'Warm meals, early dinner, 20 minutes of pranayama daily.'
    ]];
}
$selected = $prescriptions[0] ?? null;
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="flex items-center gap-3"><a href="<?= BASE_URL ?>/my-health.php" class="material-symbols-outlined text-primary">arrow_back</a><div><h1 class="font-display-lg text-display-lg text-primary">Prescriptions <span class="rounded-full bg-primary-fixed px-3 py-1 text-label-lg text-primary"><?= count($prescriptions) ?></span></h1><p class="text-on-surface-variant">Doctor-signed prescriptions, uploads and pharmacy reorder.</p></div></div>
    <div class="flex flex-wrap gap-3"><button data-upload class="rounded-lg border border-primary px-5 py-3 text-primary">Upload Prescription</button><a href="<?= BASE_URL ?>/order-punch.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary">Order on Prescription</a></div>
</div>
<?php if ($notice): ?><div class="mb-6 rounded-lg bg-primary-fixed p-4 text-primary"><?= h($notice) ?></div><?php endif; ?>
<div class="mb-6 flex flex-wrap gap-2"><?php foreach (['All','Pending','Active','Expired'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?></div>
<div class="grid gap-8 lg:grid-cols-[360px_1fr]">
    <aside class="space-y-4">
        <?php foreach ($prescriptions as $index => $rx): ?>
        <button class="w-full rounded-xl border <?= $index === 0 ? 'border-primary bg-primary-fixed/20' : 'border-outline-variant bg-surface-container-lowest' ?> p-5 text-left">
            <div class="flex items-start justify-between gap-3"><div><h2 class="font-title-lg text-title-lg"><?= h($rx['doctor_name'] ?? 'Uploaded Prescription') ?></h2><p class="text-label-sm text-on-surface-variant"><?= h($rx['specialty'] ?? 'Ayurveda') ?></p></div><span class="rounded-full bg-primary-fixed px-3 py-1 text-label-sm text-primary">Active</span></div>
            <p class="mt-3 text-label-sm text-on-surface-variant">Issued: <?= date('d M Y', strtotime($rx['created_at'] ?? 'now')) ?></p>
            <p class="text-label-sm text-primary">Valid until: <?= !empty($rx['follow_up_date']) ? date('d M Y', strtotime($rx['follow_up_date'])) : date('d M Y', strtotime('+90 days')) ?></p>
            <p class="mt-2 text-label-sm"><?= substr_count((string)($rx['medicines'] ?? ''), "\n") + 1 ?> medicines</p>
        </button>
        <?php endforeach; ?>
    </aside>
    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
        <?php if ($selected): ?>
        <div class="flex flex-col gap-4 border-b border-outline-variant pb-5 md:flex-row md:items-start md:justify-between">
            <div><h2 class="font-headline-md text-headline-md text-primary"><?= h($selected['doctor_name'] ?? 'Doctor') ?></h2><p class="text-on-surface-variant">AyurViora Clinic · Registration AYU-2026-118</p></div>
            <span class="rounded-full bg-primary-fixed px-3 py-1 text-label-sm text-primary">Digital Signature Verified</span>
        </div>
        <div class="mt-5 grid gap-4 rounded-lg bg-surface-container p-4 md:grid-cols-4"><span>Patient: <?= h($_SESSION['user_name'] ?? 'Patient') ?></span><span>Age: 34</span><span>Gender: Not set</span><span>Weight: 68 kg</span></div>
        <h3 class="mt-6 font-title-lg text-title-lg">Diagnosis</h3><p class="mt-2 text-on-surface-variant"><?= h($selected['diagnosis'] ?? 'General wellness consultation') ?></p>
        <div class="mt-6 overflow-x-auto"><table class="w-full min-w-[720px] text-left"><thead class="bg-surface-container text-label-sm uppercase text-on-surface-variant"><tr><th class="p-3">S.No</th><th class="p-3">Medicine</th><th class="p-3">Dosage</th><th class="p-3">Frequency</th><th class="p-3">Duration</th><th class="p-3">Instructions</th></tr></thead><tbody class="divide-y divide-outline-variant">
        <?php foreach (explode("\n", (string)($selected['medicines'] ?? '')) as $i => $line): $cols = array_pad(explode('|', $line), 5, 'As directed'); ?>
            <tr><td class="p-3"><?= $i + 1 ?></td><td class="p-3 font-medium"><?= h($cols[0]) ?></td><td class="p-3"><?= h($cols[1]) ?></td><td class="p-3"><?= h($cols[2]) ?></td><td class="p-3"><?= h($cols[3]) ?></td><td class="p-3"><?= h($cols[4]) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <p class="mt-6 text-on-surface-variant"><?= h($selected['advice'] ?? '') ?></p>
        <div class="mt-8 flex flex-col gap-3 border-t border-outline-variant pt-5 sm:flex-row sm:items-center sm:justify-between"><div class="font-title-lg text-primary">Dr. Priya Sharma<br><span class="text-label-sm text-on-surface-variant">(Signed digitally)</span></div><div class="flex flex-wrap gap-2"><button onclick="window.print()" class="rounded-lg border border-outline-variant px-4 py-2">Print</button><button class="rounded-lg border border-outline-variant px-4 py-2">Share</button><a href="<?= BASE_URL ?>/order-punch.php" class="rounded-lg bg-primary px-4 py-2 text-on-primary">Order All Medicines</a></div></div>
        <?php endif; ?>
    </article>
</div>
</section>
<div id="upload-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <form method="post" enctype="multipart/form-data" class="w-full max-w-lg rounded-xl bg-surface-container-lowest p-6">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <div class="flex justify-between"><h3 class="font-headline-md text-headline-md text-primary">Upload Prescription</h3><button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"><span class="material-symbols-outlined">close</span></button></div>
        <label class="mt-5 flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-outline-variant p-6 text-center"><span class="material-symbols-outlined text-4xl text-primary">upload_file</span><span>Drop prescription image or click to browse</span><input type="file" name="prescription_file" accept=".jpg,.jpeg,.png,.pdf" class="mt-4"></label>
        <textarea name="notes" class="mt-4 w-full rounded-lg border-outline-variant" placeholder="Optional notes"></textarea>
        <button class="mt-4 w-full rounded-lg bg-primary py-3 text-on-primary">Upload</button>
    </form>
</div>
<script>document.querySelector('[data-upload]')?.addEventListener('click', () => { const modal = document.getElementById('upload-modal'); modal.classList.remove('hidden'); modal.classList.add('flex'); });</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

