<?php
require_once 'includes/config.php';
$site_title = 'My Family';
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$conn = getDB();
$userId = $_SESSION['user_id'];

$check = $conn->prepare("SELECT COUNT(*) as cnt FROM family_members WHERE user_id = ?");
$check->bind_param("i", $userId);
$check->execute();
if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
    $ins = $conn->prepare("INSERT INTO family_members (user_id, full_name, relationship) VALUES (?, ?, 'self')");
    $ins->bind_param("is", $userId, $_SESSION['user_name']);
    $ins->execute();
    $ins->close();
}
$check->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $fullName = trim($_POST['full_name']);
        $relationship = $_POST['relationship'];
        $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $gender = $_POST['gender'] ?? null;
        $memberId = !empty($_POST['member_id']) ? (int)$_POST['member_id'] : null;

        if (empty($fullName)) {
            $error = 'नाम दर्ज करें';
        } else {
            if ($memberId) {
                $stmt = $conn->prepare("UPDATE family_members SET full_name=?, relationship=?, age=?, gender=? WHERE id=? AND user_id=?");
                $stmt->bind_param("ssisii", $fullName, $relationship, $age, $gender, $memberId, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO family_members (user_id, full_name, relationship, age, gender) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issis", $userId, $fullName, $relationship, $age, $gender);
            }
            $stmt->execute();
            $stmt->close();
            $success = 'परिवार सदस्य सेव हो गया!';
        }
    }
}

if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $delId = (int)$_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM family_members WHERE id=? AND user_id=? AND relationship != 'self'");
        $stmt->bind_param("ii", $delId, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['activate'])) {
    $_SESSION['active_family_member'] = (int)$_GET['activate'];
    header('Location: my-family.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM family_members WHERE user_id = ? ORDER BY FIELD(relationship,'self','spouse','son','daughter','father','mother','other'), created_at");
$stmt->bind_param("i", $userId);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$activeMemberId = $_SESSION['active_family_member'] ?? ($members[0]['id'] ?? null);

include 'includes/header.php';
?>
<div class="min-h-screen bg-surface py-12">
    <div class="max-w-4xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-headline-lg font-headline-lg text-on-surface">My Family</h1>
                <p class="text-on-surface-variant">पूरे परिवार का स्वास्थ्य एक जगह — एक लॉगिन में</p>
            </div>
            <button onclick="document.getElementById('addForm').classList.toggle('hidden')" class="bg-[#012d1d] text-white px-4 py-2 rounded-lg hover:bg-[#1b4332] transition-all">+ सदस्य जोड़ें</button>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <?php
            $icons = ['self'=>'👤', 'spouse'=>'👩', 'son'=>'👦', 'daughter'=>'👧', 'father'=>'👨', 'mother'=>'👩‍🦳', 'other'=>'👤'];
            $relationshipNames = ['self'=>'स्वयं', 'spouse'=>'पति/पत्नी', 'son'=>'पुत्र', 'daughter'=>'पुत्री', 'father'=>'पिता', 'mother'=>'माता', 'other'=>'अन्य'];
            foreach ($members as $m):
                $isActive = ($m['id'] == $activeMemberId);
                $icon = $icons[$m['relationship']] ?? '👤';
            ?>
            <div class="bg-white rounded-xl p-6 shadow-sm border <?= $isActive ? 'border-[#1b4332] ring-2 ring-[#1b4332]/20' : 'border-[#dde4e6]' ?> transition-all">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-[#1b4332]/10 flex items-center justify-center text-2xl"><?= $icon ?></div>
                    <div>
                        <h3 class="font-bold text-on-surface"><?= htmlspecialchars($m['full_name']) ?></h3>
                        <p class="text-sm text-on-surface-variant">
                            <?= $relationshipNames[$m['relationship']] ?? $m['relationship'] ?>
                            <?= $m['age'] ? ' | '.$m['age'].' वर्ष' : '' ?>
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <?php if ($m['relationship'] !== 'self'): ?>
                    <a href="?delete=<?= $m['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="text-sm text-red-600 hover:underline" onclick="return confirm('हटाएँ?')">हटाएँ</a>
                    <?php endif; ?>
                    <a href="?activate=<?= $m['id'] ?>" class="text-sm font-bold text-[#1b4332] hover:underline">स्वास्थ्य डैशबोर्ड →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="addForm" class="hidden bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6] mb-8">
            <h2 class="text-headline-md font-headline-md mb-4">नया सदस्य जोड़ें</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-on-surface mb-1">पूरा नाम *</label>
                    <input type="text" name="full_name" required class="w-full px-3 py-2 border border-outline-variant rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">रिश्ता</label>
                    <select name="relationship" class="w-full px-3 py-2 border border-outline-variant rounded-lg">
                        <option value="spouse">पति/पत्नी</option>
                        <option value="son">पुत्र</option>
                        <option value="daughter">पुत्री</option>
                        <option value="father">पिता</option>
                        <option value="mother">माता</option>
                        <option value="other">अन्य</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">आयु</label>
                    <input type="number" name="age" min="1" max="120" class="w-full px-3 py-2 border border-outline-variant rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">लिंग</label>
                    <select name="gender" class="w-full px-3 py-2 border border-outline-variant rounded-lg">
                        <option value="">चुनें</option>
                        <option value="male">पुरुष</option>
                        <option value="female">महिला</option>
                        <option value="other">अन्य</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-3">
                    <button type="submit" class="bg-[#012d1d] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-[#1b4332]">सेव करें</button>
                    <button type="button" onclick="document.getElementById('addForm').classList.add('hidden')" class="px-6 py-2.5 border border-[#dde4e6] rounded-lg">रद्द करें</button>
                </div>
            </form>
        </div>

        <div class="bg-gradient-to-br from-[#012d1d] to-[#1b4332] rounded-xl p-8 text-white">
            <h2 class="text-headline-md font-headline-md mb-2">🏠 पूरे परिवार का स्वास्थ्य डैशबोर्ड</h2>
            <p class="text-white/80 mb-6">हर सदस्य का अलग डैशबोर्ड — वजन, नींद, दर्द, BP, शुगर ट्रैक करें</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php foreach ($members as $m): ?>
                <a href="my-health.php?member_id=<?= $m['id'] ?>" class="bg-white/10 hover:bg-white/20 rounded-lg p-4 text-center transition-all backdrop-blur-sm">
                    <div class="text-3xl mb-1"><?= $icons[$m['relationship']] ?? '👤' ?></div>
                    <div class="text-sm font-bold"><?= htmlspecialchars($m['full_name']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
