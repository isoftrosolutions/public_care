<?php
require_once 'includes/config.php';
$site_title = 'Health Coach';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("DELETE FROM health_reminders WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        $types = ['medicine', 'water', 'yoga', 'diet'];
        $stmt = $conn->prepare("INSERT INTO health_reminders (user_id, reminder_type, reminder_time, active) VALUES (?, ?, ?, ?)");

        foreach ($types as $type) {
            if (isset($_POST[$type . '_active']) && isset($_POST[$type . '_time'])) {
                $time = $_POST[$type . '_time'];
                $active = 1;
                $stmt->bind_param("issi", $_SESSION['user_id'], $type, $time, $active);
                $stmt->execute();
            }
        }
        $stmt->close();
        $success = 'आपके रिमाइंडर सहेज लिए गए हैं!';
    }
}

$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM health_reminders WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reminders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$reminderMap = [];
foreach ($reminders as $r) {
    $reminderMap[$r['reminder_type']] = $r;
}

include 'includes/header.php';
?>
<div class="min-h-screen bg-surface py-12">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-10">
            <span class="text-4xl">📧</span>
            <h1 class="text-headline-lg font-headline-lg text-on-surface mt-2">Email Health Coach</h1>
            <p class="text-on-surface-variant mt-2">अपने स्वास्थ्य रिमाइंडर सेट करें — दवा, पानी, योग और आहार के लिए ईमेल पाएँ</p>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">💊</span>
                        <div>
                            <h3 class="font-bold text-on-surface">दवा रिमाइंडर</h3>
                            <p class="text-sm text-on-surface-variant">दवा लेने का समय सेट करें</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="medicine_active" value="1" class="sr-only peer" <?= isset($reminderMap['medicine']) ? 'checked' : '' ?> onchange="document.getElementById('medicine_time').disabled = !this.checked">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1b4332] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1b4332]"></div>
                    </label>
                </div>
                <input type="time" name="medicine_time" id="medicine_time" value="<?= htmlspecialchars($reminderMap['medicine']['reminder_time'] ?? '09:00') ?>" class="w-full px-4 py-2 border border-[#dde4e6] rounded-lg <?= !isset($reminderMap['medicine']) ? 'opacity-50' : '' ?>">
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">💧</span>
                        <div>
                            <h3 class="font-bold text-on-surface">पानी रिमाइंडर</h3>
                            <p class="text-sm text-on-surface-variant">हर 2 घंटे में (सुबह 9 से रात 9 बजे तक)</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="water_active" value="1" class="sr-only peer" <?= isset($reminderMap['water']) ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1b4332] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1b4332]"></div>
                    </label>
                </div>
                <input type="hidden" name="water_time" value="09:00">
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🧘</span>
                        <div>
                            <h3 class="font-bold text-on-surface">योग रिमाइंडर</h3>
                            <p class="text-sm text-on-surface-variant">योग और व्यायाम का समय</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="yoga_active" value="1" class="sr-only peer" <?= isset($reminderMap['yoga']) ? 'checked' : '' ?> onchange="document.getElementById('yoga_time').disabled = !this.checked">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1b4332] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1b4332]"></div>
                    </label>
                </div>
                <input type="time" name="yoga_time" id="yoga_time" value="<?= htmlspecialchars($reminderMap['yoga']['reminder_time'] ?? '06:00') ?>" class="w-full px-4 py-2 border border-[#dde4e6] rounded-lg <?= !isset($reminderMap['yoga']) ? 'opacity-50' : '' ?>">
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🥗</span>
                        <div>
                            <h3 class="font-bold text-on-surface">आहार रिमाइंडर</h3>
                            <p class="text-sm text-on-surface-variant">भोजन का समय सेट करें</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="diet_active" value="1" class="sr-only peer" <?= isset($reminderMap['diet']) ? 'checked' : '' ?> onchange="document.getElementById('diet_time').disabled = !this.checked">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#1b4332] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1b4332]"></div>
                    </label>
                </div>
                <input type="time" name="diet_time" id="diet_time" value="<?= htmlspecialchars($reminderMap['diet']['reminder_time'] ?? '08:00') ?>" class="w-full px-4 py-2 border border-[#dde4e6] rounded-lg <?= !isset($reminderMap['diet']) ? 'opacity-50' : '' ?>">
            </div>

            <button type="submit" class="w-full bg-[#012d1d] text-white py-4 rounded-xl font-bold hover:bg-[#1b4332] transition-all">रिमाइंडर सेव करें</button>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('input[type="checkbox"][name$="_active"]').forEach(cb => {
    cb.addEventListener('change', function() {
        const timeId = this.name.replace('_active', '_time');
        const timeInput = document.getElementById(timeId);
        if (timeInput) timeInput.disabled = !this.checked;
    });
});
</script>
<?php include 'includes/footer.php'; ?>
