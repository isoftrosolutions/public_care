<?php
function getGroqApiKey() {
    if (defined('GROQ_API_KEY') && !empty(GROQ_API_KEY)) {
        return GROQ_API_KEY;
    }
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'groq_api_key' LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        return $result[0] ?? '';
    } catch (Exception $e) {
        error_log('Failed to fetch GROQ API key from DB: ' . $e->getMessage());
        return '';
    }
}

function callGroq($systemPrompt, $userMessage, $model = 'llama-3.3-70b-versatile') {
    $apiKey = getGroqApiKey();
    if (empty($apiKey)) {
        error_log('GROQ_API_KEY not defined');
        return null;
    }

    $url = 'https://api.groq.com/openai/v1/chat/completions';

    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'temperature' => 0.7,
        'max_tokens' => 800
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Groq API curl error: $error");
        return null;
    }

    if ($httpCode !== 200) {
        error_log("Groq API HTTP $httpCode: $response");
        return null;
    }

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? null;
}

function doshaAIRecommendations($scores, $dominant, $lang = 'hi') {
    $langHint = $lang === 'hi' ? 'हिंदी' : 'English';
    $userMessage = "My dosha assessment results:\n"
        . "- Vata (वात) score: {$scores['vata']}\n"
        . "- Pitta (पित्त) score: {$scores['pitta']}\n"
        . "- Kapha (कफ) score: {$scores['kapha']}\n"
        . "Dominant dosha: {$dominant}\n\n"
        . "Please provide personalized Ayurvedic recommendations in {$langHint}.";

    $systemPrompt = "You are an expert Ayurvedic practitioner with 30+ years of clinical experience. "
        . "Provide personalized wellness recommendations based on dosha scores. "
        . "Format your response in {$langHint} with the following sections using emoji markers:\n"
        . "🥗 **आहार (Diet)** — specific foods to eat and avoid\n"
        . "🧘 **जीवनशैली (Lifestyle)** — daily routine adjustments\n"
        . "🧎 **योग (Yoga)** — specific asanas and pranayama\n"
        . "🌿 **जड़ी-बूटियाँ (Herbs)** — recommended herbs with benefits\n"
        . "📋 **दैनिक दिनचर्या (Daily Routine)** — sample daily schedule\n\n"
        . "Be specific, practical, and actionable. Write in a warm, encouraging tone. "
        . "Mention specific yoga poses, foods, and herbs by name. "
        . "Keep recommendations to 3-5 points per section. "
        . "If scores are close between doshas, address the imbalance comprehensively.";

    return callGroq($systemPrompt, $userMessage, 'llama-3.3-70b-versatile');
}

function chatbotAIResponseProduct($message, $lang, $products, $productContext) {
    $langHint = $lang === 'hi' ? 'हिंदी में' : 'in English';
    $productList = '';
    foreach ($products as $p) {
        $productList .= "- {$p['name']}: ₹{$p['price']} | {$p['description']}\n";
    }
    $systemPrompt = "You are AyurBot, the official AI wellness assistant for Ayurwellness.com. "
        . "Respond {$langHint}. Be warm, concise (2-4 sentences), and helpful. "
        . "Always use '🙏 Namaste' or 'नमस्ते' greetings.\n\n"
        . "The user is asking about products. BELOW is REAL product data from our database — use these exact prices and names, NEVER make up pricing:\n\n"
        . "{$productContext}\n\n"
        . "When discussing benefits, summarize from the product descriptions above. Guide users to shop.php for more."
        . "Current user language: " . strtoupper($lang);

    return callGroq($systemPrompt, $message, 'llama-3.1-8b-instant');
}

function chatbotAIResponse($message, $lang = 'en') {
    $langHint = $lang === 'hi' ? 'हिंदी में' : 'in English';
    $systemPrompt = "You are AyurBot, the official AI wellness assistant for Ayurwellness.com — an Ayurvedic e-commerce and consultation platform. "
        . "Respond {$langHint}. Be warm, concise (2-4 sentences), and helpful. "
        . "Always use '🙏 Namaste' or 'नमस्ते' greetings.\n\n"
        . "SITE FEATURES you can guide users to:\n"
        . "- 🛒 **Shop**: " . BASE_URL . "/shop.php — Ayurvedic products (immunity, digestion, hair, skin, weight management)\n"
        . "- 📅 **Consultation**: " . BASE_URL . "/appointment-booking.php — Book doctor appointments\n"
        . "- 📹 **Video Consult**: " . BASE_URL . "/video-consult.php — Online video consultations via Jitsi\n"
        . "- 🌿 **Dosha Analysis**: " . BASE_URL . "/dosha-quiz.php — 3-minute AI body type analysis\n"
        . "- 📊 **Health Dashboard**: " . BASE_URL . "/my-health.php — 90-day wellness tracking\n"
        . "- 👨‍👩‍👧‍👦 **Family Account**: " . BASE_URL . "/my-family.php — Manage family health\n"
        . "- 📧 **Health Coach**: " . BASE_URL . "/health-coach.php — Email reminders for medicine, water, yoga, diet\n"
        . "- 👨‍⚕️ **Doctors**: " . BASE_URL . "/doctor-listing.php — View all Ayurvedic practitioners\n"
        . "- 📖 **Blog**: " . BASE_URL . "/wellness-blog.php — Health & wellness articles\n"
        . "- ℹ️ **About**: " . BASE_URL . "/about-us.php — About Ayurwellness\n"
        . "- 📞 **Contact**: " . BASE_URL . "/contact-us.php — Get in touch\n\n"
        . "When users mention health issues, ask about their dosha type and suggest relevant products/consultations. "
        . "Provide complete HTML links with <a> tags for navigation. "
        . "Never invent pricing or availability — redirect them to the relevant page.\n\n"
        . "Current user language: " . strtoupper($lang);

    return callGroq($systemPrompt, $message, 'llama-3.1-8b-instant');
}

// AJAX endpoint for dosha AI recommendations
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
        echo json_encode(['response' => nl2br($aiResponse)]);
    } else {
        echo json_encode(['error' => 'AI service unavailable. Please try again later.']);
    }
    exit;
}
