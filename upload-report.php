<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';

require_login();

$site_title = 'Upload Report';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (empty($_FILES['report_file']['name']) || ($_FILES['report_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Please choose a PDF, JPG, PNG or WEBP report file.';
    } else {
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $originalName = basename($_FILES['report_file']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $error = 'Only PDF, JPG, PNG and WEBP reports are supported.';
        } elseif ((int)$_FILES['report_file']['size'] > 8 * 1024 * 1024) {
            $error = 'Report file must be smaller than 8 MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/reports';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = 'report-' . (int)$_SESSION['user_id'] . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target = $uploadDir . '/' . $safeName;

            if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target)) {
                $db = getDB();
                if (table_exists($db, 'health_records')) {
                    $title = trim($_POST['report_title'] ?? '') ?: 'Uploaded Lab Report';
                    $recordType = 'lab_report';
                    $recordDate = date('Y-m-d');
                    $filePath = 'uploads/reports/' . $safeName;
                    $stmt = $db->prepare("INSERT INTO health_records (user_id, record_type, title, file_name, file_path, record_date) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param('isssss', $_SESSION['user_id'], $recordType, $title, $originalName, $filePath, $recordDate);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                $success = 'Report uploaded successfully. Our AI explanation will summarize key values in simple language, and you can share the report with a doctor for medical advice.';
            } else {
                $error = 'Could not save the uploaded report. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="pt-32 pb-section-gap bg-background">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5">
                <p class="text-label-md font-label-md text-primary uppercase tracking-wider mb-3">Report Upload & AI Explanation</p>
                <h1 class="font-display-lg text-display-lg text-primary mb-4">Understand your health report in simple language</h1>
                <p class="text-body-lg text-body-lg text-on-surface-variant leading-relaxed">Upload blood test reports, prescriptions or scanned health files. AyurViora helps explain common values, flags what to discuss with a doctor, and keeps your records organized.</p>
                <div class="mt-6 grid gap-3">
                    <div class="flex items-start gap-3 rounded-xl bg-white border border-outline-variant/30 p-4">
                        <span class="material-symbols-outlined text-primary">lock</span>
                        <p class="text-sm text-on-surface-variant"><strong class="text-on-surface">Private storage:</strong> reports are linked to your logged-in account.</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl bg-white border border-outline-variant/30 p-4">
                        <span class="material-symbols-outlined text-primary">psychology</span>
                        <p class="text-sm text-on-surface-variant"><strong class="text-on-surface">AI explanation:</strong> educational summary only, not a diagnosis.</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl bg-white border border-outline-variant/30 p-4">
                        <span class="material-symbols-outlined text-primary">stethoscope</span>
                        <p class="text-sm text-on-surface-variant"><strong class="text-on-surface">Doctor review:</strong> book a video consultation for treatment decisions.</p>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-7">
                <div class="rounded-2xl bg-white border border-outline-variant/30 shadow-sm p-6 md:p-8">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-5">Upload Report</h2>
                    <?php if ($success): ?>
                        <div class="mb-5 rounded-xl bg-green-50 border border-green-200 text-green-800 p-4 flex gap-3">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-800 p-4"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2" for="report_title">Report title</label>
                            <input id="report_title" name="report_title" class="w-full rounded-xl border border-outline-variant bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-primary/25 focus:border-primary outline-none" placeholder="e.g. CBC report, Thyroid profile">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2" for="report_file">Report file</label>
                            <input id="report_file" name="report_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-dashed border-outline-variant bg-surface-container-low px-4 py-6">
                            <p class="mt-2 text-xs text-on-surface-variant">Supported: PDF, JPG, PNG, WEBP. Max size: 8 MB.</p>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-on-primary font-bold hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined">upload_file</span>
                            Upload & Request Explanation
                        </button>
                    </form>
                    <div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                        <strong>Medical disclaimer:</strong> AI explanations are for education only. Always consult a qualified doctor for diagnosis, treatment or emergency symptoms.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
