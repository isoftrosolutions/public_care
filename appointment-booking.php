<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$site_title = 'Book Consultation';

$db = getDB();
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$doctor = null;
if ($doctor_id) {
    $stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->bind_param('i', $doctor_id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
}

$success = false;
$booking_id = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
    $doc_id = (int)$_POST['doctor_id'];
    $date = trim($_POST['appointment_date'] ?? '');
    $time = trim($_POST['appointment_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$doc_id || !$date || !$time) {
        $error = 'Please select a doctor, date, and time.';
    } else {
        $stmt_doc = $db->prepare("SELECT fee FROM doctors WHERE id = ?");
        $stmt_doc->bind_param('i', $doc_id);
        $stmt_doc->execute();
        $doc_data = $stmt_doc->get_result()->fetch_assoc();
        if (!$doc_data) {
            $error = 'Invalid doctor selected.';
        } else {
            $fee = $doc_data['fee'];
            $stmt = $db->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, amount, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param('iissds', $_SESSION['user_id'], $doc_id, $date, $time, $fee, $notes);
            if ($stmt->execute()) {
                $success = true;
                $booking_id = $stmt->insert_id;

                $roomName = 'pca-' . $doc_id . '-' . $booking_id . '-' . bin2hex(random_bytes(3));
                $meetingLink = BASE_URL . '/video-consult.php?room=' . $roomName;
                $stmt_c = $db->prepare("INSERT INTO consultations (user_id, doctor_id, appointment_id, type, status, meeting_link) VALUES (?, ?, ?, 'video', 'scheduled', ?)");
                $stmt_c->bind_param("iiis", $_SESSION['user_id'], $doc_id, $booking_id, $meetingLink);
                $stmt_c->execute();
                $consId = $stmt_c->insert_id;

                $stmt_u = $db->prepare("UPDATE appointments SET consultation_id = ?, meeting_link = ? WHERE id = ?");
                $stmt_u->bind_param("isi", $consId, $meetingLink, $booking_id);
                $stmt_u->execute();
            } else {
                $error = 'Failed to book appointment. Please try again.';
            }
        }
    }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="max-w-container-max mx-auto px-margin-desktop py-12">
<div class="max-w-3xl mx-auto mb-16">
<div class="flex justify-between items-center relative">
<div class="absolute top-1/2 left-0 w-full h-0.5 bg-surface-container-highest -z-10 -translate-y-1/2"></div>
<div class="flex flex-col items-center gap-2 group">
<div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary font-bold shadow-lg transition-all" id="step-dot-1">1</div>
<span class="text-label-sm font-label-sm text-primary">Doctor</span>
</div>
<div class="flex flex-col items-center gap-2">
<div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all" id="step-dot-2">2</div>
<span class="text-label-sm font-label-sm text-on-surface-variant">Schedule</span>
</div>
<div class="flex flex-col items-center gap-2">
<div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all" id="step-dot-3">3</div>
<span class="text-label-sm font-label-sm text-on-surface-variant">Payment</span>
</div>
<div class="flex flex-col items-center gap-2">
<div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all" id="step-dot-4">4</div>
<span class="text-label-sm font-label-sm text-on-surface-variant">Complete</span>
</div>
</div>
</div>

<div class="max-w-4xl mx-auto">
<?php if ($success): ?>
<section class="step-transition text-center" id="step-4">
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-12 shadow-xl relative overflow-hidden">
<div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
<div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
<span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
<h2 class="text-display-lg font-display-lg text-primary mb-4">Consultation Booked!</h2>
<p class="text-body-lg font-body-lg text-on-surface-variant max-w-xl mx-auto mb-10">Your journey to holistic wellness begins soon. We've sent a detailed confirmation to your email.</p>
<div class="bg-surface-container rounded-xl p-8 mb-10 max-w-lg mx-auto border border-outline-variant">
<div class="grid grid-cols-2 gap-y-4 text-left">
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Booking ID</span>
<span class="font-bold text-on-surface">#PCA-<?= date('Y') ?>-<?= str_pad($booking_id, 5, '0', STR_PAD_LEFT) ?></span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Date &amp; Time</span>
<span class="font-bold text-on-surface"><?= htmlspecialchars(date('F d, Y', strtotime($_POST['appointment_date']))) ?> at <?= htmlspecialchars($_POST['appointment_time']) ?></span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Platform</span>
<span class="font-bold text-on-surface">Secure Video Consultation</span>
<span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest">Meeting Link</span>
<span class="font-bold text-on-surface text-sm break-all"><?= htmlspecialchars($meetingLink) ?></span>
</div>
</div>
<div class="flex flex-col sm:flex-row gap-4 justify-center">
<a href="<?= $meetingLink ?>" class="bg-secondary text-primary px-8 py-4 rounded-lg font-bold flex items-center justify-center gap-2 hover:opacity-90 transition-all"><span class="material-symbols-outlined">videocam</span> Join Video Call</a>
<a href="<?= BASE_URL ?>/index.php" class="border-2 border-primary text-primary px-8 py-4 rounded-lg font-bold hover:bg-surface-container transition-all">Return Home</a>
</div>
</div>
<div class="mt-12">
<a href="<?= BASE_URL ?>/index.php" class="text-primary font-bold flex items-center gap-2 justify-center hover:underline">Return to Home <span class="material-symbols-outlined">arrow_forward</span></a>
</div>
</section>
<?php else: ?>
<?php if ($error): ?>
<div class="p-4 bg-error-container text-on-error-container rounded-xl mb-8 flex items-center gap-3">
<span class="material-symbols-outlined">error</span>
<span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<section class="step-transition" id="step-1">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-8 mb-8 shadow-sm">
<h2 class="text-headline-md font-headline-md text-primary mb-6">Confirmed Consultant</h2>
<div class="flex flex-col md:flex-row gap-8 items-start">
<div class="w-32 h-32 rounded-xl overflow-hidden shadow-sm flex-shrink-0">
<img alt="Doctor Portrait" class="w-full h-full object-cover" src="<?= $doctor ? htmlspecialchars($doctor['image_url']) : 'https://lh3.googleusercontent.com/aida-public/AB6AXuAq6qdsd7NoKqRjj4SaOYUAkuiQBb2STCcx5jXGgML9iMbKh0CygITarGhPuFfwFoX5skYrwJ1u5khW5fnWO77SB7jB6NNz6uPcXvJFShyevHybDeqJPMKgAN9I6ZUc4S3lhuZ6wBh2rhmMzn3nNjXK1OtQNU7aZAnbjFn2qHoLdlDA54PeljKjUS3LsjUtPIhkcCeR7joV36exQ5CPkUFVKs8POEZ7B9KSxo4LbApbeb-pl0muop2sbym1f9i8_s5tZC8DoAaPAzQ' ?>">
</div>
<div class="flex-grow">
<?php if ($doctor): ?>
<div class="flex justify-between items-start">
<div>
<h3 class="text-headline-md font-headline-md text-on-surface"><?= htmlspecialchars($doctor['name']) ?></h3>
<p class="text-label-md font-label-md text-secondary-container bg-primary px-3 py-1 rounded-full inline-block mt-1"><?= htmlspecialchars($doctor['specialty']) ?></p>
<p class="text-body-md font-body-md text-on-surface-variant mt-4 leading-relaxed"><?= htmlspecialchars($doctor['qualifications']) ?>. With over <?= (int)$doctor['experience_years'] ?> years of clinical expertise.</p>
</div>
<div class="text-right">
<p class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Consultation Fee</p>
<p class="text-headline-md font-headline-md text-primary">₹<?= number_format($doctor['fee']) ?></p>
</div>
</div>
<?php else: ?>
<select name="doctor_id" class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-4 text-body-md" required onchange="this.form.submit()">
<option value="">Select a doctor...</option>
<?php
$docs = $db->query("SELECT * FROM doctors WHERE available = 1 ORDER BY rating DESC")->fetch_all(MYSQLI_ASSOC);
foreach ($docs as $d):
?>
<option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?> (₹<?= number_format($d['fee']) ?>)</option>
<?php endforeach; ?>
</select>
<?php endif; ?>
</div>
</div>
</div>
<div class="flex justify-end">
<button type="button" class="bg-primary text-on-primary px-12 py-4 rounded-lg font-bold shadow-md hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2" onclick="goToStep(2)">Next: Choose Schedule <span class="material-symbols-outlined">arrow_forward</span></button>
</div>
</section>

<section class="hidden step-transition" id="step-2">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 shadow-sm">
<div class="flex justify-between items-center mb-6">
<h3 class="text-headline-lg font-headline-lg text-primary">Select Date</h3>
</div>
<div class="space-y-4">
<label class="font-label-md text-on-surface-variant block">Appointment Date</label>
<input name="appointment_date" type="date" class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-4 text-body-md" required min="<?= date('Y-m-d') ?>">
</div>
</div>
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 shadow-sm">
<h3 class="text-headline-md font-headline-md text-primary mb-6">Available Slots</h3>
<div class="space-y-4">
<label class="font-label-md text-on-surface-variant block">Appointment Time</label>
<input name="appointment_time" type="time" class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-4 text-body-md" required>
</div>
<div class="mt-8 p-4 bg-surface-container-low rounded-lg border-l-4 border-secondary">
<div class="flex gap-3">
<span class="material-symbols-outlined text-secondary">info</span>
<p class="text-label-sm font-label-sm text-on-surface-variant">All consultation times are in local time. Please arrive 5 minutes early.</p>
</div>
</div>
</div>
</div>
<div class="flex justify-between items-center">
<button type="button" class="text-primary font-bold flex items-center gap-2 px-6 py-3 rounded-lg hover:bg-surface-container transition-colors" onclick="goToStep(1)"><span class="material-symbols-outlined">arrow_back</span> Back</button>
<button type="button" class="bg-primary text-on-primary px-12 py-4 rounded-lg font-bold shadow-md hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2" onclick="goToStep(3)">Next: Payment Secure <span class="material-symbols-outlined">lock</span></button>
</div>
</section>

<section class="hidden step-transition" id="step-3">
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
<div class="md:col-span-1">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 shadow-sm sticky top-28">
<h3 class="text-headline-md font-headline-md text-primary mb-4">Summary</h3>
<?php
$selected_doc = $doctor;
if (!$selected_doc && isset($_POST['doctor_id'])) {
    $sid = (int)$_POST['doctor_id'];
    $stmt_doc2 = $db->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt_doc2->bind_param('i', $sid);
    $stmt_doc2->execute();
    $selected_doc = $stmt_doc2->get_result()->fetch_assoc();
}
$fee = $selected_doc ? (float)$selected_doc['fee'] : 0;
$tax = $fee * 0.05;
$total = $fee + $tax;
?>
<div class="space-y-4 mb-6">
<div class="flex justify-between text-body-md font-body-md"><span class="text-on-surface-variant">Consultation Fee</span><span>₹<?= number_format($fee, 2) ?></span></div>
<div class="flex justify-between text-body-md font-body-md"><span class="text-on-surface-variant">Service Tax (5%)</span><span>₹<?= number_format($tax, 2) ?></span></div>
<div class="pt-4 border-t border-outline-variant flex justify-between"><span class="font-bold text-on-surface">Total Amount</span><span class="font-bold text-primary">₹<?= number_format($total, 2) ?></span></div>
</div>
<div class="p-3 bg-tertiary-container rounded-lg flex items-center gap-3">
<span class="material-symbols-outlined text-tertiary-fixed-dim" style="font-variation-settings: 'FILL' 1;">verified_user</span>
<span class="text-label-sm font-label-sm text-on-tertiary-container">Encrypted &amp; Secure</span>
</div>
</div>
</div>
<div class="md:col-span-2">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-8 shadow-sm">
<h3 class="text-headline-md font-headline-md text-primary mb-8">Choose Payment Method</h3>
<div class="space-y-4">
<label class="flex items-center gap-6 p-5 border-2 border-primary bg-surface-container-low rounded-xl cursor-pointer transition-all">
<input checked="" class="w-5 h-5 text-primary focus:ring-primary border-outline" name="payment" type="radio" value="razorpay"/>
<div class="flex items-center gap-4 flex-grow">
<div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm overflow-hidden"><span class="text-blue-600 font-bold text-xl">R</span></div>
<div><p class="font-bold text-on-surface">Razorpay</p><p class="text-label-sm font-label-sm text-on-surface-variant">UPI, cards, net banking &amp; wallets</p></div>
</div>
<span class="material-symbols-outlined text-primary">verified</span>
</label>
<label class="flex items-center gap-6 p-5 border border-outline-variant rounded-xl cursor-pointer hover:border-primary transition-all">
<input class="w-5 h-5 text-primary focus:ring-primary border-outline" name="payment" type="radio" value="paytm"/>
<div class="flex items-center gap-4 flex-grow">
<div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm overflow-hidden"><span class="text-blue-500 font-bold text-xl">P</span></div>
<div><p class="font-bold text-on-surface">Paytm</p><p class="text-label-sm font-label-sm text-on-surface-variant">Paytm wallet, UPI &amp; cards</p></div>
</div>
</label>
<label class="flex items-center gap-6 p-5 border border-outline-variant rounded-xl cursor-pointer hover:border-primary transition-all">
<input class="w-5 h-5 text-primary focus:ring-primary border-outline" name="payment" type="radio" value="upi"/>
<div class="flex items-center gap-4 flex-grow">
<div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm overflow-hidden"><span class="text-green-600 font-bold text-xl">U</span></div>
<div><p class="font-bold text-on-surface">UPI</p><p class="text-label-sm font-label-sm text-on-surface-variant">Google Pay, PhonePe, BHIM &amp; more</p></div>
</div>
</label>
</div>
<div class="mt-8 space-y-4">
<label class="block">
<span class="font-label-md text-on-surface-variant block mb-1">Additional Notes (optional)</span>
<textarea name="notes" class="w-full border-outline-variant rounded-lg focus:ring-primary focus:border-primary bg-surface p-4 text-body-md" rows="3" placeholder="Any specific health concerns or questions..."></textarea>
</label>
</div>
<button type="submit" name="book_appointment" class="w-full mt-8 bg-secondary-container text-on-secondary-container py-5 rounded-xl font-bold text-headline-md shadow-lg hover:opacity-90 active:scale-95 transition-all">Complete Payment</button>
</div>
</div>
</div>
<div class="flex justify-start">
<button type="button" class="text-primary font-bold flex items-center gap-2 px-6 py-3 rounded-lg hover:bg-surface-container transition-colors" onclick="goToStep(2)"><span class="material-symbols-outlined">arrow_back</span> Back</button>
</div>
</section>

<?php if (!$doctor): ?>
<input type="hidden" name="doctor_id" value="<?= isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : $doctor_id ?>">
<?php else: ?>
<input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
<?php endif; ?>
</form>
<?php endif; ?>
</div>
</main>

<style>
.step-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.animate-bounce { animation: bounce 1s infinite; }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
</style>
<script>
function goToStep(step) {
for (let i = 1; i <= 4; i++) {
const s = document.getElementById(`step-${i}`);
if (s) s.classList.add('hidden');
const dot = document.getElementById(`step-dot-${i}`);
if (dot) {
if (i < step) { dot.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-tertiary-container text-on-tertiary-container font-bold transition-all'; dot.innerHTML = '<span class="material-symbols-outlined text-xl">check</span>'; }
else if (i === step) { dot.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary font-bold shadow-lg transition-all'; dot.innerHTML = i; }
else { dot.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all'; dot.innerHTML = i; }
}
}
const active = document.getElementById(`step-${step}`);
if (active) { active.classList.remove('hidden'); active.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => { active.classList.remove('opacity-0', 'translate-y-4'); active.classList.add('opacity-100', 'translate-y-0'); }, 10); }
window.scrollTo({ top: 150, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
