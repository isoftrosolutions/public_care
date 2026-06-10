<?php
require_once 'includes/config.php';
$conn = getDB();
$site_title = 'दोष प्रोफाइल परिणाम';
$current_page = basename($_SERVER['SCRIPT_NAME']);

$result = $_SESSION['dosha_result'] ?? null;
if (!$result) {
    header('Location: ' . BASE_URL . '/dosha-quiz.php');
    exit;
}

$scores = $result['scores'];
$dominant = $result['dominant'];
$recommendations = $result['recommendations'];

$categoryColors = [
    'vata' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-700', 'light' => 'bg-blue-50', 'border' => 'border-blue-200', 'label' => 'वात', 'hex' => '#3B82F6'],
    'pitta' => ['bg' => 'bg-red-500', 'text' => 'text-red-700', 'light' => 'bg-red-50', 'border' => 'border-red-200', 'label' => 'पित्त', 'hex' => '#EF4444'],
    'kapha' => ['bg' => 'bg-green-500', 'text' => 'text-green-700', 'light' => 'bg-green-50', 'border' => 'border-green-200', 'label' => 'कफ', 'hex' => '#22C55E']
];

$maxScore = max($scores);
$totalScore = array_sum($scores) ?: 1;

$history = [];
if (isset($_SESSION['user_id'])) {
    require_once 'includes/dosha-questions.php';
    $history = getUserDoshaHistory($conn, $_SESSION['user_id']);
}

include 'includes/header.php';
?>
<div class="min-h-screen bg-surface">
    <section class="relative bg-gradient-to-br from-[#012d1d] to-[#1b4332] text-white py-16">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <span class="inline-block bg-[#fed65b] text-[#012d1d] px-4 py-1 rounded-full text-sm font-semibold mb-4">🧬 आपका आयुर्वेदिक प्रोफाइल</span>
            <h1 class="text-headline-lg-mobile md:text-display-lg font-display-lg mb-4">आपका दोष प्रोफाइल</h1>
            <p class="text-body-lg text-white/80 max-w-2xl mx-auto">नीचे दिए गए परिणाम आपके उत्तरों के आधार पर वात, पित्त और कफ दोषों के संतुलन को दर्शाते हैं।</p>
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php foreach (['vata', 'pitta', 'kapha'] as $dosha): 
                $c = $categoryColors[$dosha];
                $pct = $totalScore > 0 ? round(($scores[$dosha] / $totalScore) * 100) : 0;
                $isDominant = $dosha === $dominant;
            ?>
            <div class="bg-white rounded-xl p-6 shadow-sm border <?= $isDominant ? 'border-[#fed65b] ring-2 ring-[#fed65b]/30' : 'border-[#dde4e6]' ?> text-center transition-all hover:shadow-md">
                <div class="w-16 h-16 rounded-full <?= $c['bg'] ?> flex items-center justify-center mx-auto mb-3">
                    <span class="text-white text-2xl font-bold"><?= strtoupper($dosha[0]) ?></span>
                </div>
                <h3 class="text-headline-md font-headline-md text-on-surface mb-1"><?= $c['label'] ?></h3>
                <p class="text-4xl font-bold <?= $c['text'] ?> mb-2"><?= $scores[$dosha] ?></p>
                <div class="w-full bg-[#dde4e6] rounded-full h-3 mb-2">
                    <div class="<?= $c['bg'] ?> h-3 rounded-full transition-all duration-1000" style="width: <?= $pct ?>%"></div>
                </div>
                <p class="text-sm text-on-surface-variant"><?= $pct ?>% संतुलन</p>
                <?php if ($isDominant): ?>
                <span class="inline-block mt-2 bg-[#fed65b] text-[#012d1d] px-3 py-1 rounded-full text-xs font-bold">प्रमुख दोष</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-xl p-8 shadow-sm border border-[#dde4e6] mb-6">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-[#1b4332]">spa</span>
                <h2 class="text-headline-md font-headline-md text-primary">आपके लिए सुझाव</h2>
            </div>
            <div class="prose max-w-none"><?= $recommendations ?></div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div id="ai-recommendations" class="bg-gradient-to-br from-[#f0faf5] to-[#f4fafd] rounded-xl p-8 shadow-sm border border-[#b7e4c7] mb-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-[#1b4332]">auto_awesome</span>
                <h2 class="text-headline-md font-headline-md text-primary">AI-पावर्ड व्यक्तिगत सुझाव</h2>
                <span class="ml-auto bg-[#1b4332] text-white px-3 py-1 rounded-full text-xs font-bold">AI</span>
            </div>
            <div id="ai-content" class="text-center py-4">
                <div class="animate-pulse flex flex-col items-center gap-3">
                    <span class="material-symbols-outlined text-3xl text-[#1b4332]">auto_awesome</span>
                    <p class="text-sm text-on-surface-variant">AI आपके व्यक्तिगत सुझाव तैयार कर रहा है...</p>
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-[#1b4332] rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-[#1b4332] rounded-full animate-bounce" style="animation-delay:0.1s"></span>
                        <span class="w-2 h-2 bg-[#1b4332] rounded-full animate-bounce" style="animation-delay:0.2s"></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-4 justify-center mb-10">
            <a href="<?= BASE_URL ?>/appointment-booking.php" class="bg-[#012d1d] text-white px-8 py-3 rounded-full font-bold text-body-md hover:bg-[#1b4332] transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">calendar_month</span> बुक अपॉइंटमेंट
            </a>
            <a href="<?= BASE_URL ?>/dosha-quiz.php" class="border-2 border-[#012d1d] text-[#012d1d] px-8 py-3 rounded-full font-bold text-body-md hover:bg-[#012d1d]/5 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">refresh</span> दोबारा टेस्ट लें
            </a>
            <button onclick="window.print()" class="border-2 border-[#dde4e6] text-on-surface-variant px-8 py-3 rounded-full font-bold text-body-md hover:bg-surface-container-high transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">download</span> रिपोर्ट डाउनलोड करें
            </button>
        </div>

        <?php if (!empty($history)): ?>
        <div class="bg-white rounded-xl p-8 shadow-sm border border-[#dde4e6]">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-[#1b4332]">history</span>
                <h2 class="text-headline-md font-headline-md text-primary">आपके पिछले परिणाम</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-[#eef5f7]">
                            <th class="px-4 py-3 text-label-sm text-on-surface-variant uppercase">दिनांक</th>
                            <th class="px-4 py-3 text-label-sm text-on-surface-variant uppercase">वात</th>
                            <th class="px-4 py-3 text-label-sm text-on-surface-variant uppercase">पित्त</th>
                            <th class="px-4 py-3 text-label-sm text-on-surface-variant uppercase">कफ</th>
                            <th class="px-4 py-3 text-label-sm text-on-surface-variant uppercase">प्रमुख दोष</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#dde4e6]">
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td class="px-4 py-3 text-body-md"><?= htmlspecialchars(date('d M Y', strtotime($h['created_at']))) ?></td>
                            <td class="px-4 py-3"><span class="text-blue-600 font-bold"><?= (int)$h['vata_score'] ?></span></td>
                            <td class="px-4 py-3"><span class="text-red-600 font-bold"><?= (int)$h['pitta_score'] ?></span></td>
                            <td class="px-4 py-3"><span class="text-green-600 font-bold"><?= (int)$h['kapha_score'] ?></span></td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    <?= $h['dominant_dosha'] === 'vata' ? 'bg-blue-100 text-blue-700' : ($h['dominant_dosha'] === 'pitta' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700') ?>">
                                    <?= $categoryColors[$h['dominant_dosha']]['label'] ?? htmlspecialchars($h['dominant_dosha']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>
<script>
<?php if (isset($_SESSION['user_id'])): ?>
fetch('<?= BASE_URL ?>/includes/ai-helper.php?dosha_ai_ajax=1')
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('ai-content');
        if (data.response) {
            el.innerHTML = '<div class="prose max-w-none">' + data.response + '</div>';
        } else {
            el.innerHTML = '<p class="text-sm text-on-surface-variant">' + (data.error || 'AI service unavailable') + '</p>';
        }
    })
    .catch(() => {
        document.getElementById('ai-content').innerHTML = '<p class="text-sm text-on-surface-variant">AI recommendations unavailable. Please try again later.</p>';
    });
<?php endif; ?>
</script>
<?php include 'includes/footer.php'; ?>
