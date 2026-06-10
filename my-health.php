<?php
require_once 'includes/config.php';
$site_title = 'My Health Dashboard';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$conn = getDB();
$userId = $_SESSION['user_id'];
$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;
$familyName = $_SESSION['user_name'];
if ($memberId) {
    $stmt = $conn->prepare("SELECT * FROM family_members WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $memberId, $_SESSION['user_id']);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    if ($member) {
        $familyName = $member['full_name'];
    }
    $stmt->close();
}
$period = isset($_GET['period']) ? min(90, max(7, (int)$_GET['period'])) : 7;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $today = date('Y-m-d');
        $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
        $sleep = !empty($_POST['sleep_hours']) ? (float)$_POST['sleep_hours'] : null;
        $pain = !empty($_POST['pain_score']) ? (int)$_POST['pain_score'] : null;
        $bpSys = !empty($_POST['bp_systolic']) ? (int)$_POST['bp_systolic'] : null;
        $bpDia = !empty($_POST['bp_diastolic']) ? (int)$_POST['bp_diastolic'] : null;
        $sugar = !empty($_POST['blood_sugar']) ? (int)$_POST['blood_sugar'] : null;
        $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;

        $stmt = $conn->prepare("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score, bp_systolic, bp_diastolic, blood_sugar, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE weight = VALUES(weight), sleep_hours = VALUES(sleep_hours), pain_score = VALUES(pain_score),
            bp_systolic = VALUES(bp_systolic), bp_diastolic = VALUES(bp_diastolic), blood_sugar = VALUES(blood_sugar), notes = VALUES(notes)");
        $stmt->bind_param("isddiiiis", $userId, $today, $weight, $sleep, $pain, $bpSys, $bpDia, $sugar, $notes);
        $stmt->execute();
        $stmt->close();
        $success = 'आज का डेटा सेव हो गया है!';
    }
}

$stmt = $conn->prepare("SELECT * FROM patient_metrics WHERE user_id = ? ORDER BY record_date DESC LIMIT ?");
$stmt->bind_param("ii", $userId, $period);
$stmt->execute();
$metrics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$latest = !empty($metrics) ? $metrics[0] : null;
$metricsChart = array_reverse($metrics);

include 'includes/header.php';
?>
<div class="min-h-screen bg-surface py-8">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-headline-lg font-headline-lg text-on-surface"><?= htmlspecialchars($familyName) ?>'s Health Dashboard</h1>
            <div class="flex items-center gap-3">
                <?php
                $fstmt = $conn->prepare("SELECT id, full_name, relationship FROM family_members WHERE user_id = ? ORDER BY FIELD(relationship,'self','spouse','son','daughter','father','mother','other')");
                $fstmt->bind_param("i", $userId);
                $fstmt->execute();
                $familyMembers = $fstmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $fstmt->close();
                if (count($familyMembers) > 1):
                ?>
                <select onchange="if(this.value) window.location='?member_id='+this.value+(this.dataset.period ? '&period='+this.dataset.period : '')" class="px-3 py-2 border border-outline-variant rounded-lg text-sm bg-white" <?php if (isset($period)): ?>data-period="<?= $period ?>"<?php endif; ?>>
                    <?php foreach ($familyMembers as $fm): ?>
                    <option value="<?= $fm['id'] ?>" <?= ($fm['id'] == $memberId) ? 'selected' : '' ?>><?= htmlspecialchars($fm['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/dosha-quiz.php" class="bg-[#012d1d] text-white px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-[#1b4332] transition-all">Dosha Quiz</a>
            </div>
        </div>
        <p class="text-on-surface-variant mb-6">अपनी सेहत पर नज़र रखें — 7, 30 या 90 दिन का डेटा देखें</p>

        <?php if (isset($success)): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="flex gap-2 mb-6">
            <a href="?period=7" class="px-4 py-2 rounded-lg text-sm font-medium <?= $period == 7 ? 'bg-[#1b4332] text-white' : 'bg-white border border-[#dde4e6] text-on-surface-variant hover:bg-surface-container-highest' ?>">7 दिन</a>
            <a href="?period=30" class="px-4 py-2 rounded-lg text-sm font-medium <?= $period == 30 ? 'bg-[#1b4332] text-white' : 'bg-white border border-[#dde4e6] text-on-surface-variant hover:bg-surface-container-highest' ?>">30 दिन</a>
            <a href="?period=90" class="px-4 py-2 rounded-lg text-sm font-medium <?= $period == 90 ? 'bg-[#1b4332] text-white' : 'bg-white border border-[#dde4e6] text-on-surface-variant hover:bg-surface-container-highest' ?>">90 दिन</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-[#dde4e6]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">⚖️</span>
                    <span class="text-xs text-on-surface-variant">Weight</span>
                </div>
                <p class="text-2xl font-bold"><?= ($latest['weight'] ?? '--') ?> <span class="text-sm font-normal text-on-surface-variant">kg</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-[#dde4e6]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">😴</span>
                    <span class="text-xs text-on-surface-variant">Sleep</span>
                </div>
                <p class="text-2xl font-bold"><?= ($latest['sleep_hours'] ?? '--') ?> <span class="text-sm font-normal text-on-surface-variant">hrs</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-[#dde4e6]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">🤕</span>
                    <span class="text-xs text-on-surface-variant">Pain</span>
                </div>
                <p class="text-2xl font-bold"><?= ($latest['pain_score'] ?? '--') ?> <span class="text-sm font-normal text-on-surface-variant">/10</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-[#dde4e6]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">❤️</span>
                    <span class="text-xs text-on-surface-variant">BP</span>
                </div>
                <p class="text-2xl font-bold"><?= ($latest['bp_systolic'] ?? '--') . '/' . ($latest['bp_diastolic'] ?? '--') ?></p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                <h2 class="text-headline-md font-headline-md mb-4">आज का डेटा दर्ज करें</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-on-surface">वज़न (kg)</label>
                            <input type="number" step="0.1" name="weight" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['weight'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-on-surface">नींद (hours)</label>
                            <input type="number" step="0.1" name="sleep_hours" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['sleep_hours'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-on-surface">दर्द स्कोर (1-10)</label>
                            <input type="number" min="1" max="10" name="pain_score" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['pain_score'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-on-surface">BP सिस्टोलिक</label>
                            <input type="number" name="bp_systolic" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['bp_systolic'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-on-surface">BP डायस्टोलिक</label>
                            <input type="number" name="bp_diastolic" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['bp_diastolic'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-on-surface">ब्लड शुगर (mg/dL)</label>
                            <input type="number" name="blood_sugar" class="w-full px-3 py-2 border border-outline-variant rounded-lg" value="<?= htmlspecialchars($latest['blood_sugar'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-on-surface">नोट्स</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg"><?= htmlspecialchars($latest['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="mt-4 bg-[#012d1d] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-[#1b4332] transition-all">आज का डेटा सेव करें</button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                    <h3 class="font-bold mb-4">⚖️ Weight Trend</h3>
                    <?php if (!empty($metricsChart)): ?>
                    <div class="space-y-2">
                        <?php foreach (array_slice($metricsChart, -10) as $m): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-14 text-on-surface-variant"><?= date('d M', strtotime($m['record_date'])) ?></span>
                            <div class="flex-1 bg-surface-container-highest rounded-full h-6 overflow-hidden">
                                <div class="bg-[#1b4332] h-full rounded-full transition-all" style="width: <?= min(100, ($m['weight'] / 100) * 100) ?>%"></div>
                            </div>
                            <span class="text-sm font-bold w-14 text-right"><?= $m['weight'] ?> kg</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-on-surface-variant text-sm">अभी तक कोई डेटा नहीं। ऊपर फॉर्म भरें।</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                    <h3 class="font-bold mb-4">😴 Sleep Trend</h3>
                    <?php if (!empty($metricsChart)): ?>
                    <div class="space-y-2">
                        <?php foreach (array_slice($metricsChart, -10) as $m): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-14 text-on-surface-variant"><?= date('d M', strtotime($m['record_date'])) ?></span>
                            <div class="flex-1 bg-surface-container-highest rounded-full h-6 overflow-hidden">
                                <div class="bg-[#1b4332] h-full rounded-full transition-all" style="width: <?= min(100, (($m['sleep_hours'] ?? 0) / 12) * 100) ?>%"></div>
                            </div>
                            <span class="text-sm font-bold w-14 text-right"><?= $m['sleep_hours'] ?? '--' ?> h</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-on-surface-variant text-sm">अभी तक कोई डेटा नहीं।</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6]">
                    <h3 class="font-bold mb-4">🤕 Pain Score Trend</h3>
                    <?php if (!empty($metricsChart)): ?>
                    <div class="space-y-2">
                        <?php foreach (array_slice($metricsChart, -10) as $m): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-14 text-on-surface-variant"><?= date('d M', strtotime($m['record_date'])) ?></span>
                            <div class="flex-1 bg-surface-container-highest rounded-full h-6 overflow-hidden">
                                <div class="bg-red-500 h-full rounded-full transition-all" style="width: <?= min(100, (($m['pain_score'] ?? 0) / 10) * 100) ?>%"></div>
                            </div>
                            <span class="text-sm font-bold w-14 text-right"><?= $m['pain_score'] ?? '--' ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-on-surface-variant text-sm">अभी तक कोई डेटा नहीं।</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
