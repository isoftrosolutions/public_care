<?php

function getSettingValue(string $key, string $default = ''): string
{
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        if (!$stmt) {
            return $default;
        }
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        $stmt->close();
        return $result[0] ?? $default;
    } catch (Exception $e) {
        error_log("Failed to fetch setting {$key}: " . $e->getMessage());
        return $default;
    }
}

function getOpenAIApiKey(): string
{
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
        return OPENAI_API_KEY;
    }
    $envKey = getenv('OPENAI_API_KEY');
    if ($envKey) {
        return $envKey;
    }
    return getSettingValue('openai_api_key');
}

function getOpenAIModel(string $fallback = 'gpt-5.2'): string
{
    if (defined('OPENAI_MODEL') && OPENAI_MODEL) {
        return OPENAI_MODEL;
    }
    $envModel = getenv('OPENAI_MODEL');
    if ($envModel) {
        return $envModel;
    }
    return getSettingValue('openai_model', $fallback) ?: $fallback;
}

function openAIExtractText(array $result): ?string
{
    if (!empty($result['output_text']) && is_string($result['output_text'])) {
        return trim($result['output_text']);
    }
    if (!empty($result['output']) && is_array($result['output'])) {
        $parts = [];
        foreach ($result['output'] as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (isset($content['text']) && is_string($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
        }
        if ($parts) {
            return trim(implode("\n", $parts));
        }
    }
    return null;
}

function callOpenAI(string $systemPrompt, string $userMessage, ?string $model = null, int $maxOutputTokens = 800): ?string
{
    $apiKey = getOpenAIApiKey();
    if ($apiKey === '') {
        error_log('OPENAI_API_KEY is not configured.');
        return null;
    }

    $payload = [
        'model' => $model ?: getOpenAIModel(),
        'input' => [
            [
                'role' => 'developer',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => $userMessage,
            ],
        ],
        'max_output_tokens' => $maxOutputTokens,
    ];

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('OpenAI API curl error: ' . $error);
        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log("OpenAI API HTTP {$httpCode}: {$response}");
        return null;
    }

    $result = json_decode((string)$response, true);
    if (!is_array($result)) {
        error_log('OpenAI API returned invalid JSON.');
        return null;
    }

    return openAIExtractText($result);
}

function doshaAIRecommendations($scores, $dominant, $lang = 'hi')
{
    $langHint = $lang === 'hi' ? 'Hindi' : 'English';
    $userMessage = "My dosha assessment results:\n"
        . "- Vata score: {$scores['vata']}\n"
        . "- Pitta score: {$scores['pitta']}\n"
        . "- Kapha score: {$scores['kapha']}\n"
        . "Dominant dosha: {$dominant}\n\n"
        . "Provide personalized Ayurvedic recommendations in {$langHint}.";

    $systemPrompt = "You are AyurBot, an Ayurvedic wellness assistant for AyurViora. "
        . "Give educational wellness guidance, not a medical diagnosis. "
        . "Use warm, practical language and include these sections: Diet, Lifestyle, Yoga, Herbs, Daily Routine. "
        . "Keep each section to 3-5 concise, actionable bullets. "
        . "Recommend consulting a qualified practitioner for persistent, severe, pregnancy-related, pediatric, or chronic concerns.";

    return callOpenAI($systemPrompt, $userMessage, null, 1000);
}

function chatbotAIResponseProduct($message, $lang, $products, $productContext)
{
    $langHint = $lang === 'hi' ? 'Hindi' : 'English';
    $systemPrompt = "You are AyurBot, the official AI wellness assistant for AyurViora, an Ayurvedic e-commerce and consultation platform. "
        . "Reply in {$langHint}. Be concise, warm, and practical. "
        . "Use only the product data supplied by the app for product names, prices, and availability. Never invent pricing or stock. "
        . "When appropriate, guide users to " . BASE_URL . "/shop.php. "
        . "For health claims, keep wording educational and suggest consulting a qualified doctor for personal treatment.";

    $userMessage = "User message:\n{$message}\n\nReal product data from the database:\n{$productContext}";
    return callOpenAI($systemPrompt, $userMessage, null, 700);
}

function chatbotAIResponse($message, $lang = 'en')
{
    $langHint = $lang === 'hi' ? 'Hindi' : 'English';
    $systemPrompt = "You are AyurBot, the official AI wellness assistant for AyurViora. "
        . "Reply in {$langHint}. Keep answers concise, helpful, and suitable for an Ayurvedic healthcare website. "
        . "You can route users to: Shop " . BASE_URL . "/shop.php, Consultation " . BASE_URL . "/appointment-booking.php, Video Consult " . BASE_URL . "/video-consult.php, Dosha Quiz " . BASE_URL . "/dosha-quiz.php, Health Dashboard " . BASE_URL . "/my-health.php, Doctors " . BASE_URL . "/doctor-listing.php, Blog " . BASE_URL . "/wellness-blog.php, Contact " . BASE_URL . "/contact-us.php. "
        . "Do not diagnose disease or prescribe medication. For urgent, severe, pediatric, pregnancy-related, chronic, or worsening symptoms, advise professional medical care. "
        . "Never invent product pricing or availability.";

    return callOpenAI($systemPrompt, $message, null, 700);
}

function healthAssistantAIResponse(string $message, array $chatHistory = [], array $drugContext = []): ?string
{
    $historyText = '';
    foreach (array_slice($chatHistory, -8) as $entry) {
        $role = $entry['role'] ?? 'user';
        $text = trim((string)($entry['message'] ?? ''));
        if ($text !== '') {
            $historyText .= strtoupper($role) . ': ' . $text . "\n";
        }
    }

    $drugText = '';
    foreach (array_slice($drugContext, 0, 5) as $drug) {
        $drugText .= "- {$drug['name']}";
        if (!empty($drug['generic_name'])) {
            $drugText .= " ({$drug['generic_name']})";
        }
        if (!empty($drug['uses'])) {
            $drugText .= ": {$drug['uses']}";
        }
        $drugText .= "\n";
    }

    $systemPrompt = "You are AyurViora's AI Health Assistant. Provide general educational health information, medicine lookup help, Ayurvedic wellness guidance, and safe next steps. "
        . "Do not diagnose, prescribe, or replace a clinician. Include a brief safety note when symptoms, medication, dosage, interactions, pregnancy, children, chronic disease, or emergencies are mentioned. "
        . "If local medicine data is provided, use it as context and do not invent missing details. Keep answers readable in short paragraphs or bullets.";

    $userPrompt = "Recent conversation:\n{$historyText}\n"
        . ($drugText ? "Relevant local medicine data:\n{$drugText}\n" : '')
        . "Current user question:\n{$message}";

    return callOpenAI($systemPrompt, $userPrompt, null, 900);
}

if (isset($_GET['dosha_ai_ajax'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/config.php';

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['dosha_result'])) {
        echo json_encode(['error' => 'Unauthorized or no dosha result']);
        exit;
    }

    $result = $_SESSION['dosha_result'];
    $scores = $result['scores'];
    $dominant = $result['dominant'];
    $lang = $_SESSION['lang'] ?? 'hi';

    $aiResponse = doshaAIRecommendations($scores, $dominant, $lang);

    if ($aiResponse !== null) {
        echo json_encode(['response' => nl2br(htmlspecialchars($aiResponse, ENT_QUOTES, 'UTF-8'))]);
    } else {
        echo json_encode(['error' => 'OpenAI is not configured or is temporarily unavailable.']);
    }
    exit;
}

