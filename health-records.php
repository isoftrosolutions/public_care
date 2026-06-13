<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Health Records';
$user = current_user($db);
$records = [];
if (table_exists($db, 'health_records')) {
    $stmt = $db->prepare('SELECT * FROM health_records WHERE user_id = ? ORDER BY record_date DESC, created_at DESC');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$metrics = [];
if (table_exists($db, 'patient_metrics')) {
    $stmt = $db->prepare('SELECT * FROM patient_metrics WHERE user_id = ? ORDER BY record_date DESC LIMIT 8');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $metrics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4"><div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-fixed text-primary"><span class="material-symbols-outlined text-4xl">account_circle</span></div><div><h1 class="font-display-lg text-display-lg text-primary"><?= h($user['full_name'] ?? 'My Health') ?></h1><p class="text-on-surface-variant">Blood group B+ · Last updated <?= date('d M Y') ?></p></div></div>
        <div class="flex flex-wrap gap-3"><a href="<?= BASE_URL ?>/my-family.php" class="rounded-lg border border-outline-variant px-5 py-3 text-primary">Switch Profile</a><a href="export-health-pdf.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary inline-block">Export All Records</a></div>
    </div>
</div>
<div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <?= stat_card('monitor_heart', 'Vitals', $metrics ? (($metrics[0]['bp_systolic'] ?? 120) . '/' . ($metrics[0]['bp_diastolic'] ?? 80)) : '120/80', 'HR 72') ?>
    <?= stat_card('science', 'Lab Reports', (string)count(array_filter($records, fn($r) => ($r['record_type'] ?? '') === 'lab_report')), 'Latest normal') ?>
    <?= stat_card('medication', 'Current Medications', '5 active', '3 due today') ?>
    <?= stat_card('event', 'Upcoming Appointments', date('15 M Y'), 'Dr. Sharma') ?>
</div>
<div class="mt-10 rounded-xl border border-outline-variant bg-surface-container-lowest">
    <div class="flex flex-wrap gap-2 border-b border-outline-variant p-5"><?php foreach (['Vitals History','Lab Reports','Medications','Allergies','Conditions','Family History'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?></div>
    <div class="grid gap-6 p-6 lg:grid-cols-[1fr_340px]">
        <div>
            <h2 class="font-headline-md text-headline-md text-primary">Vitals History</h2>
            <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[620px] text-left"><thead class="bg-surface-container text-label-sm uppercase text-on-surface-variant"><tr><th class="p-3">Date</th><th class="p-3">BP</th><th class="p-3">Weight</th><th class="p-3">Sugar</th><th class="p-3">Notes</th></tr></thead><tbody class="divide-y divide-outline-variant">
            <?php foreach ($metrics ?: [['record_date'=>date('Y-m-d'),'bp_systolic'=>120,'bp_diastolic'=>80,'weight'=>68,'blood_sugar'=>92,'notes'=>'Baseline check-in']] as $m): ?>
            <tr><td class="p-3"><?= date('d M Y', strtotime($m['record_date'])) ?></td><td class="p-3"><?= h(($m['bp_systolic'] ?? '-') . '/' . ($m['bp_diastolic'] ?? '-')) ?></td><td class="p-3"><?= h($m['weight'] ?? '-') ?> kg</td><td class="p-3"><?= h($m['blood_sugar'] ?? '-') ?></td><td class="p-3"><?= h($m['notes'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
            <h2 class="mt-8 font-headline-md text-headline-md text-primary">Health Files</h2>
            <div class="mt-4 grid gap-3">
            <?php foreach ($records as $record): ?><a class="rounded-lg border border-outline-variant p-4 hover:bg-surface-container" href="<?= h($record['file_path']) ?>"><strong><?= h($record['title'] ?: $record['file_name']) ?></strong><br><span class="text-label-sm text-on-surface-variant"><?= h($record['record_type']) ?> · <?= h($record['record_date']) ?></span></a><?php endforeach; ?>
            <?php if (!$records): ?><div><?= empty_state('folder_open', 'No health files yet', 'Upload lab reports, scans, prescriptions and other records to build your health passport.', 'Open Prescriptions', BASE_URL . '/prescriptions.php') ?></div><?php endif; ?>
            </div>
        </div>
        <aside class="space-y-4">
            <button class="w-full rounded-lg bg-primary px-5 py-3 text-on-primary" onclick="document.getElementById('record-menu').classList.toggle('hidden')">+ Add Record</button>
            <div id="record-menu" class="hidden rounded-xl border border-outline-variant bg-surface-container p-4 space-y-2"><a class="block rounded-lg bg-white p-3" href="<?= BASE_URL ?>/my-health.php">Add Vitals</a><a class="block rounded-lg bg-white p-3" href="<?= BASE_URL ?>/lab-tests.php">Upload Lab Report</a><button class="w-full rounded-lg bg-white p-3 text-left">Add Allergy</button><button class="w-full rounded-lg bg-white p-3 text-left">Add Medication</button></div>
            <div class="rounded-xl bg-primary-fixed p-5 text-primary"><strong>Shareable Summary</strong><p class="mt-2 text-sm">Generate a printable PDF summary for your doctor or family member.</p><a href="export-health-pdf.php" class="mt-4 rounded-lg bg-primary px-4 py-2 text-on-primary inline-block">Generate PDF</a></div>
        </aside>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
