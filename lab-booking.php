<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$site_title = 'Book Lab Test';
$db = getDB();

$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$test = null;
if ($test_id) {
    $stmt = $db->prepare("SELECT * FROM lab_tests WHERE id = ? AND active = 1");
    $stmt->bind_param('i', $test_id);
    $stmt->execute();
    $test = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$test) {
    header('Location: ' . BASE_URL . '/lab-tests.php');
    exit;
}

$user_stmt = $db->prepare("SELECT full_name, mobile FROM users WHERE id = ?");
$user_stmt->bind_param('i', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$success = false;
$booking_id = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_test'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $patient_name = trim($_POST['patient_name'] ?? '');
        $patient_phone = trim($_POST['patient_phone'] ?? '');
        $booking_date = trim($_POST['booking_date'] ?? '');
        $booking_time = trim($_POST['booking_time'] ?? '');
        $collection_address = trim($_POST['collection_address'] ?? '');
        $collection_type = isset($_POST['collection_type']) && $_POST['collection_type'] === 'center' ? 'center' : 'home';
        $notes = trim($_POST['notes'] ?? '');

        if (!$patient_name || !$patient_phone || !$booking_date) {
            $error = 'Please fill in all required fields.';
        } elseif ($booking_date < date('Y-m-d')) {
            $error = 'Please select a future date.';
        } else {
            $amount = $test['discount_price'] > 0 ? $test['discount_price'] : $test['price'];

            $patient_info = 'Patient: ' . $patient_name . ', Phone: ' . $patient_phone;
            $full_notes = $patient_info;
            if ($notes !== '') {
                $full_notes .= "\n" . $notes;
            }

            $stmt = $db->prepare("INSERT INTO lab_test_bookings (user_id, test_id, booking_date, booking_time, collection_address, collection_type, amount, notes, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
            $stmt->bind_param('iissssds', $_SESSION['user_id'], $test_id, $booking_date, $booking_time, $collection_address, $collection_type, $amount, $full_notes);
            if ($stmt->execute()) {
                $success = true;
                $booking_id = $stmt->insert_id;
            } else {
                $error = 'Failed to book test. Please try again.';
            }
            $stmt->close();
        }
    }
}

$orig_price = (float)$test['price'];
$disc_price = (float)($test['discount_price'] ?? 0);
$has_discount = $disc_price > 0 && $disc_price < $orig_price;
$final_price = $has_discount ? $disc_price : $orig_price;
$report_hours = (int)($test['report_time_hours'] ?? 24);
$report_text = $report_hours < 24 ? $report_hours . ' hours' : ($report_hours >= 24 && $report_hours < 48 ? '1 day' : (int)($report_hours / 24) . ' days');

require_once __DIR__ . '/includes/header.php';
?>
<style>
.bg-pattern { background-image: radial-gradient(#ffffff20 1px, transparent 0); background-size: 24px 24px; }
</style>

<!-- Hero -->
<section class="relative overflow-hidden bg-primary py-12 md:py-16">
<div class="absolute inset-0 opacity-10 bg-pattern"></div>
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop relative z-10">
<p class="text-primary-fixed font-label-lg text-label-lg mb-2 tracking-widest uppercase">Book Your Test</p>
<h1 class="font-display-lg text-display-lg text-on-primary-container"><?= htmlspecialchars($test['name']) ?></h1>
</div>
</section>

<!-- Breadcrumb -->
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop pt-6">
<nav class="flex items-center gap-2 text-label-sm text-outline mb-2">
<a href="<?= BASE_URL ?>/index.php" class="hover:text-primary transition-colors">Home</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<a href="<?= BASE_URL ?>/lab-tests.php" class="hover:text-primary transition-colors">Lab Tests</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-primary font-medium"><?= htmlspecialchars($test['name']) ?></span>
</nav>
</div>

<div class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8 lg:py-12">

<?php if ($success): ?>
<div class="max-w-2xl mx-auto text-center">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-12 shadow-xl relative overflow-hidden">
<div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
<div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
<span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
<h2 class="text-display-lg font-display-lg text-primary mb-4">Test Booked Successfully!</h2>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-xl mx-auto mb-10">Our team will contact you shortly to confirm the sample collection. Your digital reports will be available within <?= $report_text ?>.</p>
<div class="bg-surface-container rounded-xl p-8 mb-10 max-w-lg mx-auto border border-outline-variant">
<div class="grid grid-cols-2 gap-y-4 text-left">
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Booking ID</span>
<span class="font-bold text-on-surface">#LAB-<?= date('Y') ?>-<?= str_pad($booking_id, 5, '0', STR_PAD_LEFT) ?></span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Test</span>
<span class="font-bold text-on-surface"><?= htmlspecialchars($test['name']) ?></span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Date</span>
<span class="font-bold text-on-surface"><?= htmlspecialchars(date('F d, Y', strtotime($_POST['booking_date'] ?? ''))) ?></span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Amount</span>
<span class="font-bold text-primary">₹<?= number_format($final_price) ?></span>
</div>
</div>
<div class="flex flex-col sm:flex-row gap-4 justify-center">
<a href="<?= BASE_URL ?>/lab-tests.php" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-primary-container transition-all"><span class="material-symbols-outlined">lab_research</span> Book More Tests</a>
<a href="<?= BASE_URL ?>/index.php" class="border-2 border-primary text-primary px-8 py-4 rounded-lg font-bold hover:bg-surface-container transition-all">Return Home</a>
</div>
</div>
</div>
<?php else: ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
<!-- Test Info Sidebar -->
<div class="lg:col-span-1 order-2 lg:order-1">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 lg:p-8 shadow-sm lg:sticky lg:top-28">
<div class="flex items-center gap-3 mb-6">
<span class="text-[11px] font-medium text-tertiary uppercase tracking-widest px-3 py-1 bg-tertiary-fixed/30 rounded-full"><?= htmlspecialchars($test['category'] ?? 'General') ?></span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-3"><?= htmlspecialchars($test['name']) ?></h3>
<p class="text-body-md text-on-surface-variant mb-6 leading-relaxed"><?= htmlspecialchars($test['description']) ?></p>

<div class="space-y-4 mb-6">
<div class="flex items-center gap-3 p-3 bg-surface-container rounded-xl">
<span class="material-symbols-outlined text-primary">schedule</span>
<div>
<p class="text-label-sm text-on-surface-variant">Report Time</p>
<p class="font-semibold text-on-surface"><?= $report_text ?></p>
</div>
</div>
<?php if ($test['home_collection']): ?>
<div class="flex items-center gap-3 p-3 bg-surface-container rounded-xl">
<span class="material-symbols-outlined text-primary">home</span>
<div>
<p class="text-label-sm text-on-surface-variant">Collection</p>
<p class="font-semibold text-on-surface">Free home pickup available</p>
</div>
</div>
<?php endif; ?>
</div>

<div class="flex items-baseline gap-2 mb-6">
<span class="font-display-lg text-display-lg text-primary">₹<?= number_format($final_price) ?></span>
<?php if ($has_discount): ?>
<span class="text-headline-md text-outline-variant line-through">₹<?= number_format($orig_price) ?></span>
<span class="bg-error text-on-error px-2.5 py-1 rounded-full text-[10px] font-bold">SAVE <?= round((1 - $final_price / $orig_price) * 100) ?>%</span>
<?php endif; ?>
</div>

<?php if (!empty($test['preparation_instructions'])): ?>
<div class="p-4 bg-tertiary-fixed/20 rounded-xl border border-tertiary-fixed-dim/30">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-tertiary">info</span>
<div>
<h4 class="font-label-md text-on-surface mb-1">Preparation Instructions</h4>
<p class="text-body-md text-on-surface-variant"><?= nl2br(htmlspecialchars($test['preparation_instructions'])) ?></p>
</div>
</div>
</div>
<?php endif; ?>
</div>
</div>

<!-- Booking Form -->
<div class="lg:col-span-2 order-1 lg:order-2">
<?php if ($error): ?>
<div class="p-4 bg-error-container text-on-error-container rounded-xl mb-8 flex items-center gap-3">
<span class="material-symbols-outlined">error</span>
<span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<form method="POST" novalidate>
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="book_test" value="1">

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 lg:p-8 shadow-sm mb-8">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Patient Details</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
<label class="font-label-md text-on-surface-variant block mb-1.5" for="patient_name">Patient Name <span class="text-error">*</span></label>
<input id="patient_name" name="patient_name" type="text" required class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" value="<?= htmlspecialchars($_POST['patient_name'] ?? ($user['full_name'] ?? '')) ?>">
</div>
<div>
<label class="font-label-md text-on-surface-variant block mb-1.5" for="patient_phone">Phone Number <span class="text-error">*</span></label>
<input id="patient_phone" name="patient_phone" type="tel" required class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" value="<?= htmlspecialchars($_POST['patient_phone'] ?? ($user['mobile'] ?? '')) ?>">
</div>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 lg:p-8 shadow-sm mb-8">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Schedule & Collection</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
<div>
<label class="font-label-md text-on-surface-variant block mb-1.5" for="booking_date">Preferred Date <span class="text-error">*</span></label>
<input id="booking_date" name="booking_date" type="date" required min="<?= date('Y-m-d') ?>" class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface" value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>">
</div>
<div>
<label class="font-label-md text-on-surface-variant block mb-1.5" for="booking_time">Preferred Time</label>
<input id="booking_time" name="booking_time" type="time" class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface" value="<?= htmlspecialchars($_POST['booking_time'] ?? '') ?>">
</div>
</div>

<div class="mb-6">
<label class="font-label-md text-on-surface-variant block mb-3">Collection Type</label>
<div class="flex gap-4">
<label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all flex-1 <?= (!isset($_POST['collection_type']) || $_POST['collection_type'] === 'home') ? 'border-primary bg-primary/5' : 'border-outline-variant hover:border-primary' ?>">
<input type="radio" name="collection_type" value="home" class="text-primary focus:ring-primary" <?= (!isset($_POST['collection_type']) || $_POST['collection_type'] === 'home') ? 'checked' : '' ?>>
<div>
<span class="font-label-md text-on-surface">Home Collection</span>
<p class="text-label-sm text-on-surface-variant">Free sample pickup at your address</p>
</div>
</label>
<label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all flex-1 <?= (isset($_POST['collection_type']) && $_POST['collection_type'] === 'center') ? 'border-primary bg-primary/5' : 'border-outline-variant hover:border-primary' ?>">
<input type="radio" name="collection_type" value="center" class="text-primary focus:ring-primary" <?= (isset($_POST['collection_type']) && $_POST['collection_type'] === 'center') ? 'checked' : '' ?>>
<div>
<span class="font-label-md text-on-surface">Visit Center</span>
<p class="text-label-sm text-on-surface-variant">Visit our nearest collection center</p>
</div>
</label>
</div>
</div>

<div>
<label class="font-label-md text-on-surface-variant block mb-1.5" for="collection_address">Collection Address</label>
<textarea id="collection_address" name="collection_address" rows="3" class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" placeholder="Enter your full address for home collection"><?= htmlspecialchars($_POST['collection_address'] ?? '') ?></textarea>
</div>
</div>

<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 lg:p-8 shadow-sm mb-8">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Additional Notes</h2>
<textarea id="notes" name="notes" rows="3" class="w-full border border-outline-variant rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" placeholder="Any specific instructions or medical history to share..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
</div>

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-6 bg-surface-container-low rounded-2xl">
<div>
<p class="text-label-sm text-on-surface-variant">Total Amount</p>
<p class="font-display-lg text-display-lg text-primary">₹<?= number_format($final_price) ?></p>
</div>
<button type="submit" class="w-full sm:w-auto bg-primary text-on-primary px-10 py-4 rounded-xl font-bold text-headline-md shadow-md hover:bg-primary-container hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-3 justify-center">
<span class="material-symbols-outlined">lab_research</span>
Confirm Booking
</button>
</div>
</form>
</div>
</div>
<?php endif; ?>
</div>

<script>
document.querySelectorAll('input[name="collection_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="collection_type"]').forEach(r => {
            const label = r.closest('label');
            if (r.checked) {
                label.classList.add('border-primary', 'bg-primary/5');
                label.classList.remove('border-outline-variant');
            } else {
                label.classList.remove('border-primary', 'bg-primary/5');
                label.classList.add('border-outline-variant');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
