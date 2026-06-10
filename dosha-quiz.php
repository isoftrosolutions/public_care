<?php
require_once 'includes/config.php';
$conn = getDB();
$site_title = 'आयुर्वेदिक शरीर विश्लेषण';
$current_page = basename($_SERVER['SCRIPT_NAME']);

$result = null;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        require_once 'includes/dosha-questions.php';
        $questions = getDoshaQuestions($conn);
        $responses = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'q_') === 0) {
                $qId = (int) substr($key, 2);
                $responses[$qId] = (int) $value;
            }
        }
        if (count($responses) < 5) {
            $errors[] = 'कृपया कम से कम 5 प्रश्नों के उत्तर दें।';
        } else {
            $result = calculateDosha($responses, $questions);
            $result['responses'] = $responses;
            $result['questions'] = $questions;
            if (isset($_SESSION['user_id'])) {
                saveDoshaAssessment($conn, $_SESSION['user_id'], $result['scores'], $result['dominant'], $result['recommendations'], $responses);
            }
            $_SESSION['dosha_result'] = $result;
            header('Location: ' . BASE_URL . '/dosha-result.php');
            exit;
        }
    }
}

include 'includes/header.php';
?>
<div class="min-h-screen bg-surface">
    <section class="relative bg-gradient-to-br from-[#012d1d] to-[#1b4332] text-white py-16">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <span class="inline-block bg-[#fed65b] text-[#012d1d] px-4 py-1 rounded-full text-sm font-semibold mb-4">🔬 AI + आयुर्वेद</span>
            <h1 class="text-headline-lg-mobile md:text-display-lg font-display-lg mb-4">अपना आयुर्वेदिक शरीर प्रकार जानें</h1>
            <p class="text-body-lg text-white/80 max-w-2xl mx-auto">3 मिनट का यह टेस्ट आपकी नींद, पाचन, शरीर की प्रकृति और आदतों के आधार पर आपका वात-पित्त-कफ प्रोफाइल बताएगा।</p>
        </div>
    </section>

    <section class="max-w-3xl mx-auto px-6 py-12">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-8" id="doshaForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <?php
            $questions = getDoshaQuestions($conn);
            $qNum = 0;
            foreach ($questions as $q):
                $qNum++;
                $categoryLabels = ['vata' => 'वात', 'pitta' => 'पित्त', 'kapha' => 'कफ'];
                $cat = $categoryLabels[$q['category']];
            ?>
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#dde4e6] question-card" data-category="<?= $q['category'] ?>">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs px-2 py-0.5 rounded-full 
                        <?= $q['category'] === 'vata' ? 'bg-blue-100 text-blue-700' : ($q['category'] === 'pitta' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700') ?>">
                        <?= $cat ?>
                    </span>
                    <span class="text-xs text-on-surface-variant">प्रश्न <?= $qNum ?>/18</span>
                </div>
                <p class="text-body-md font-semibold text-on-surface mb-4"><?= htmlspecialchars($q['question_text']) ?></p>
                <div class="grid grid-cols-5 gap-2">
                    <?php $labels = ['बिल्कुल नहीं', 'कभी-कभी', 'अक्सर', 'ज़्यादातर', 'हमेशा']; ?>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <label class="flex flex-col items-center gap-1 cursor-pointer">
                        <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $i + 1 ?>" class="sr-only peer" required>
                        <div class="w-10 h-10 rounded-full border-2 border-[#dde4e6] flex items-center justify-center peer-checked:bg-[#1b4332] peer-checked:text-white peer-checked:border-[#1b4332] transition-all text-sm font-bold text-on-surface-variant"><?= $i + 1 ?></div>
                        <span class="text-[10px] text-on-surface-variant text-center leading-tight"><?= $labels[$i] ?></span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="w-full bg-[#012d1d] text-white py-4 rounded-xl font-bold text-body-lg hover:bg-[#1b4332] transition-all">परिणाम देखें →</button>
        </form>
    </section>
</div>
<?php include 'includes/footer.php'; ?>
