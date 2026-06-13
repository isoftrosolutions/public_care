<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/ai-helper.php';
$site_title = 'AI Health Assistant';

function getOrCreateSession() {
    $db = getDB();
    $sid = session_id();
    $stmt = $db->prepare("SELECT id FROM ai_chat_sessions WHERE session_id = ? AND is_active = 1");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        return (int)$result['id'];
    }
    $stmt = $db->prepare("INSERT INTO ai_chat_sessions (session_id, title, is_active) VALUES (?, 'AI Health Assistant', 1)");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    return (int)$stmt->insert_id;
}

function getChatHistory($sessionId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT role, message, created_at FROM ai_chat_messages WHERE session_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function saveMessage($sessionId, $role, $message) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO ai_chat_messages (session_id, role, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $sessionId, $role, $message);
    $stmt->execute();
}

function searchDrugs($query) {
    $db = getDB();
    $like = '%' . $query . '%';
    $stmt = $db->prepare("SELECT id, name, generic_name, category, uses, side_effects, precautions, dosage, brand_names FROM drug_information WHERE name LIKE ? OR generic_name LIKE ? OR brand_names LIKE ? LIMIT 10");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getDrugById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM drug_information WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function checkInteractions($drug1Id, $drug2Id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT di.severity, di.description, di.mechanism, d1.name AS drug1_name, d2.name AS drug2_name FROM drug_interactions di JOIN drug_information d1 ON di.drug1_id = d1.id JOIN drug_information d2 ON di.drug2_id = d2.id WHERE (di.drug1_id = ? AND di.drug2_id = ?) OR (di.drug1_id = ? AND di.drug2_id = ?)");
    $stmt->bind_param("iiii", $drug1Id, $drug2Id, $drug2Id, $drug1Id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function generateChatResponse($message, $sessionId) {
    $db = getDB();
    $msg = strtolower(trim($message));
    $drugs = searchDrugs($msg);
    $chatHistory = getChatHistory($sessionId);

    if (!empty($drugs)) {
        $drug = $drugs[0];
        $resp = "**{$drug['name']}**\n\n";
        if ($drug['generic_name']) $resp .= "**Generic:** {$drug['generic_name']}\n";
        if ($drug['category']) $resp .= "**Category:** {$drug['category']}\n";
        if ($drug['uses']) $resp .= "\n**Uses:** {$drug['uses']}\n";
        if ($drug['side_effects']) $resp .= "\n**Side Effects:** {$drug['side_effects']}\n";
        if ($drug['precautions']) $resp .= "\n**Precautions:** {$drug['precautions']}\n";
        if ($drug['dosage']) $resp .= "\n**Dosage:** {$drug['dosage']}\n";
        if ($drug['brand_names']) $resp .= "\n**Brand Names:** {$drug['brand_names']}";

        if (strpos($msg, 'side effect') !== false && $drug['side_effects']) {
            $resp = "**Side Effects of {$drug['name']}:**\n{$drug['side_effects']}";
        }

        if ((strpos($msg, 'interact') !== false || strpos($msg, 'interaction') !== false) && count($drugs) >= 1) {
            $otherDrugs = searchDrugs(str_replace(['interaction', 'interact', 'with', 'and', 'between'], '', $msg));
            foreach ($otherDrugs as $od) {
                if ($od['id'] !== $drug['id']) {
                    $interactions = checkInteractions($drug['id'], $od['id']);
                    if (!empty($interactions)) {
                        $resp .= "\n\n**Interaction with {$od['name']}:**\n";
                        foreach ($interactions as $ix) {
                            $resp .= "**Severity:** {$ix['severity']}\n**Effect:** {$ix['description']}\n";
                        }
                    } else {
                        $resp .= "\n\nNo known interaction found between **{$drug['name']}** and **{$od['name']}**.";
                    }
                }
            }
        }

        return $resp;
    }

    if (strpos($msg, 'side effect') !== false) {
        preg_match('/side effect of (.+)/i', $message, $m);
        $searchTerm = $m[1] ?? '';
        $drugs = searchDrugs($searchTerm);
        if (!empty($drugs)) {
            $drug = $drugs[0];
            $resp = "**Side Effects of {$drug['name']}:**\n{$drug['side_effects']}";
            if ($drug['precautions']) $resp .= "\n\n**Precautions:** {$drug['precautions']}";
            return $resp;
        }
        return "I couldn't find specific side effect information for that. Please consult a doctor for medical advice. You can also try searching for a medicine name in the Medicine Lookup section below.";
    }

    if ((strpos($msg, 'interact') !== false || strpos($msg, 'interaction') !== false)) {
        preg_match('/(.+?)\s+(?:and|with|between)\s+(.+)/i', $message, $m);
        if (isset($m[1]) && isset($m[2])) {
            $d1 = searchDrugs(trim($m[1]));
            $d2 = searchDrugs(trim($m[2]));
            if (!empty($d1) && !empty($d2)) {
                $interactions = checkInteractions($d1[0]['id'], $d2[0]['id']);
                if (!empty($interactions)) {
                    $resp = "**Interaction between {$d1[0]['name']} and {$d2[0]['name']}:**\n\n";
                    foreach ($interactions as $ix) {
                        $sev = $ix['severity'];
                        $emoji = '⚠️';
                        if ($sev === 'severe') $emoji = '🔴';
                        elseif ($sev === 'contraindicated') $emoji = '🚫';
                        elseif ($sev === 'moderate') $emoji = '🟡';
                        elseif ($sev === 'minor') $emoji = '🟢';
                        $resp .= "{$emoji} **Severity:** {$sev}\n**Effect:** {$ix['description']}\n";
                        if ($ix['mechanism']) $resp .= "**Mechanism:** {$ix['mechanism']}\n";
                    }
                    return $resp;
                }
                return "No known interaction found between **{$d1[0]['name']}** and **{$d2[0]['name']}**. However, always consult a doctor before combining medications.";
            }
        }
        return "Please specify two medicine names to check their interaction (e.g., 'interaction between Paracetamol and Omeprazole').";
    }

    $greetings = ['hi', 'hello', 'hey', 'namaste', 'नमस्ते', 'नमस्कार', 'hii', 'hello ji'];
    foreach ($greetings as $g) {
        if (strpos($msg, $g) !== false) {
            return "🙏 Namaste! I'm your **Ayurviro AI Health Assistant**. I can help you with:\n\n💊 **Medicine Information** — Look up any medicine\n⚠️ **Side Effects** — Check possible side effects\n🔄 **Drug Interactions** — Check interactions between medicines\n🩺 **Symptom Guidance** — Get general guidance on symptoms\n🥗 **Ayurvedic Diet Tips** — Diet and wellness suggestions\n\nHow can I help you today?";
        }
    }

    if (strpos($msg, 'symptom') !== false || strpos($msg, 'लक्षण') !== false) {
        $aiResponse = healthAssistantAIResponse($message, $chatHistory, $drugs);
        if ($aiResponse !== null) {
            return $aiResponse;
        }
        return "Please describe your symptoms and I can provide general educational guidance. This does not replace professional medical advice. If symptoms are severe, sudden, worsening, or urgent, contact emergency services or a qualified doctor immediately.";
    }

    if (strpos($msg, 'diet') !== false || strpos($msg, 'खान') !== false || strpos($msg, 'आहार') !== false) {
        $aiResponse = healthAssistantAIResponse($message, $chatHistory, $drugs);
        if ($aiResponse !== null) {
            return $aiResponse;
        }
        return "**Ayurvedic Diet Tips:**\n\n- Eat your largest meal at noon when digestion is strongest.\n- Sip warm water with meals and avoid very cold drinks.\n- Favor fresh, seasonal foods and digestion-supporting spices like ginger, cumin, coriander, and turmeric.\n- For personalized diet planning, take the Dosha Quiz or consult an Ayurvedic practitioner.";
    }

    if (strpos($msg, 'thank') !== false || strpos($msg, 'धन्यवाद') !== false) {
        return "You're welcome! 🙏 I'm always here to help with your health queries. Feel free to ask me about medicines, symptoms, or Ayurvedic wellness tips. Stay healthy! 🌿";
    }

    $drugNames = [];
    $allDrugs = $db->query("SELECT name FROM drug_information LIMIT 50");
    while ($row = $allDrugs->fetch_assoc()) {
        $drugNames[] = strtolower($row['name']);
    }
    foreach ($drugNames as $dn) {
        if (strpos($msg, $dn) !== false) {
            $drugs = searchDrugs($dn);
            if (!empty($drugs)) {
                $drug = $drugs[0];
                $resp = "**{$drug['name']}**\n\n";
                if ($drug['uses']) $resp .= "**Uses:** {$drug['uses']}\n";
                if ($drug['side_effects']) $resp .= "\n**Side Effects:** {$drug['side_effects']}\n";
                if ($drug['dosage']) $resp .= "\n**Dosage:** {$drug['dosage']}\n";
                return $resp;
            }
        }
    }

    $aiResponse = healthAssistantAIResponse($message, $chatHistory, $drugs);
    if ($aiResponse !== null) {
        return $aiResponse;
    }

    return "OpenAI is not configured yet. Add your OpenAI API key in Admin > Settings, then I can answer general health and Ayurvedic wellness questions. You can still use Medicine Lookup for local database results.";
}

$sessionId = getOrCreateSession();
$chatHistory = getChatHistory($sessionId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'chat' && !empty($_POST['message'])) {
        $userMsg = trim($_POST['message']);
        saveMessage($sessionId, 'user', $userMsg);
        $response = generateChatResponse($userMsg, $sessionId);
        saveMessage($sessionId, 'assistant', $response);
        echo json_encode(['response' => nl2br(htmlspecialchars($response))]);
        exit;
    }

    if ($action === 'medicine_search' && !empty($_POST['q'])) {
        $results = searchDrugs(trim($_POST['q']));
        $html = '';
        foreach ($results as $drug) {
            $html .= '<div class="p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer medicine-result" data-drug-id="' . $drug['id'] . '">';
            $html .= '<h4 class="font-headline-md text-title-lg font-bold text-primary mb-1">' . htmlspecialchars($drug['name']) . '</h4>';
            if ($drug['generic_name']) $html .= '<p class="text-sm text-on-surface-variant mb-1"><span class="font-medium">Generic:</span> ' . htmlspecialchars($drug['generic_name']) . '</p>';
            if ($drug['category']) $html .= '<p class="text-sm text-on-surface-variant mb-1"><span class="font-medium">Category:</span> ' . htmlspecialchars($drug['category']) . '</p>';
            if ($drug['brand_names']) $html .= '<p class="text-sm text-on-surface-variant mb-1"><span class="font-medium">Brands:</span> ' . htmlspecialchars($drug['brand_names']) . '</p>';
            if ($drug['uses']) $html .= '<p class="text-sm text-on-surface-variant mb-1"><span class="font-medium">Uses:</span> ' . htmlspecialchars($drug['uses']) . '</p>';
            $html .= '<button class="mt-3 text-sm font-medium text-primary hover:underline toggle-detail" data-drug-id="' . $drug['id'] . '">View Full Details +</button>';
            $html .= '<div id="detail-' . $drug['id'] . '" class="hidden mt-3 pt-3 border-t border-outline-variant/30 space-y-2">';
            if ($drug['side_effects']) $html .= '<p class="text-sm"><span class="font-medium text-on-surface">⚠️ Side Effects:</span> <span class="text-on-surface-variant">' . htmlspecialchars($drug['side_effects']) . '</span></p>';
            if ($drug['precautions']) $html .= '<p class="text-sm"><span class="font-medium text-on-surface">🛡️ Precautions:</span> <span class="text-on-surface-variant">' . htmlspecialchars($drug['precautions']) . '</span></p>';
            if ($drug['dosage']) $html .= '<p class="text-sm"><span class="font-medium text-on-surface">💊 Dosage:</span> <span class="text-on-surface-variant">' . htmlspecialchars($drug['dosage']) . '</span></p>';
            $html .= '</div>';
            $html .= '</div>';
        }
        if (empty($results)) {
            $html = '<div class="text-center py-10 text-on-surface-variant"><span class="material-symbols-outlined text-4xl mb-2">search_off</span><p>No results found. Try a different medicine name.</p></div>';
        }
        echo json_encode(['html' => $html]);
        exit;
    }

    exit;
}

require_once __DIR__ . '/includes/header.php';
?>
<style>
@keyframes messageSlide {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.message-enter { animation: messageSlide 0.3s ease; }
.chat-messages { scroll-behavior: smooth; }
.pulse-dot { animation: pulse 1.4s infinite; }
.pulse-dot:nth-child(2) { animation-delay: 0.2s; }
.pulse-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes pulse {
    0%, 60%, 100% { opacity: 0.3; transform: scale(0.8); }
    30% { opacity: 1; transform: scale(1); }
}
.gradient-hero {
    background: linear-gradient(135deg, #005221 0%, #0a6e3a 50%, #1a8a4e 100%);
}
.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
}
.chat-input::placeholder { color: #9ca3af; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<section class="gradient-hero relative overflow-hidden pt-32 pb-24 md:pt-40 md:pb-32">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-container-max mx-auto px-base md:px-margin-desktop text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/15 backdrop-blur-sm mb-6 border border-white/20">
            <span class="material-symbols-outlined text-4xl text-white" style="font-variation-settings:'FILL' 1;">psychology</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-white mb-4">Your AI Health Companion</h1>
        <p class="font-body-lg text-body-lg text-white/80 max-w-2xl mx-auto leading-relaxed">
            Get instant information about medicines, check drug interactions, explore Ayurvedic wellness tips, and receive general health guidance — all powered by our intelligent health assistant.
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm text-white/90">💊 Medicine Info</span>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm text-white/90">⚠️ Side Effects</span>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm text-white/90">🔄 Drug Interactions</span>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm text-white/90">🥗 Diet Tips</span>
        </div>
    </div>
</section>

<section class="py-section-gap bg-background">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-3">Quick Actions</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl mx-auto">Choose from common health queries to get started instantly</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="Tell me about common medicines">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">💊</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Medicine Information</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Look up any medicine — uses, side effects, dosage, and more</p>
            </div>
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="What are common side effects of medicines">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">⚠️</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Side Effects</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Check possible side effects and precautions for any medicine</p>
            </div>
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="How can I check drug interactions">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">🔄</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Drug Interactions</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Check interactions between different medicines</p>
            </div>
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="Set a dosage reminder for me">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">⏰</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Dosage Reminder</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Set reminders for your medication schedule</p>
            </div>
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="I have some symptoms I want to check">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">🩺</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Symptom Checker</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Get guidance on symptoms and when to see a doctor</p>
            </div>
            <div class="p-6 bg-surface-container-lowest rounded-xl border border-outline-variant/40 hover-lift cursor-pointer quick-card" data-query="Give me Ayurvedic diet tips">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">🥗</span>
                </div>
                <h3 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Diet Suggestions</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">Ayurvedic diet tips for better health and wellness</p>
            </div>
        </div>
    </div>
</section>

<section class="py-section-gap bg-surface-container-low">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm">
                    <div class="bg-primary px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-on-primary" style="font-variation-settings:'FILL' 1;">psychology</span>
                            <h3 class="font-headline-md text-title-lg font-bold text-on-primary">AI Health Chat</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 backdrop-blur-sm rounded-full text-xs text-on-primary">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Online
                        </span>
                    </div>
                    <div id="chat-messages" class="chat-messages h-96 overflow-y-auto p-6 space-y-4 bg-[#fafafa] hide-scrollbar">
                        <div class="flex items-start gap-3 message-enter">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings:'FILL' 1;">psychology</span>
                            </div>
                            <div class="bg-white border border-outline-variant/30 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%] shadow-sm">
                                <p class="text-sm text-on-surface leading-relaxed">🙏 Namaste! I'm your <strong>Ayurviro AI Health Assistant</strong>. Ask me about medicines, side effects, drug interactions, symptoms, or Ayurvedic wellness tips!</p>
                            </div>
                        </div>
                        <?php foreach ($chatHistory as $msg): ?>
                            <?php if ($msg['role'] === 'user'): ?>
                            <div class="flex items-start justify-end gap-3 message-enter">
                                <div class="bg-primary rounded-2xl rounded-br-sm px-5 py-3.5 max-w-[80%] shadow-sm">
                                    <p class="text-sm text-on-primary"><?= htmlspecialchars($msg['message']) ?></p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="material-symbols-outlined text-sm text-on-primary">person</span>
                                </div>
                            </div>
                            <?php elseif ($msg['role'] === 'assistant'): ?>
                            <div class="flex items-start gap-3 message-enter">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings:'FILL' 1;">psychology</span>
                                </div>
                                <div class="bg-white border border-outline-variant/30 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%] shadow-sm">
                                    <p class="text-sm text-on-surface leading-relaxed"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div id="typing-indicator" class="hidden items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings:'FILL' 1;">psychology</span>
                            </div>
                            <div class="bg-white border border-outline-variant/30 rounded-2xl rounded-tl-sm px-5 py-4 shadow-sm">
                                <div class="flex gap-1.5">
                                    <span class="w-2 h-2 bg-primary/40 rounded-full pulse-dot"></span>
                                    <span class="w-2 h-2 bg-primary/40 rounded-full pulse-dot"></span>
                                    <span class="w-2 h-2 bg-primary/40 rounded-full pulse-dot"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-outline-variant/30 px-6 py-4 bg-white">
                        <form id="chat-form" class="flex items-center gap-3">
                            <input type="text" id="chat-input" class="chat-input flex-1 px-5 py-3 bg-surface-container-low rounded-xl border border-outline-variant/40 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm transition-all" placeholder="Ask about medicines, symptoms, diet..." autocomplete="off">
                            <button type="submit" class="w-11 h-11 rounded-xl bg-primary text-on-primary flex items-center justify-center hover:bg-primary-container transition-all active:scale-95 flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">send</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">lightbulb</span>
                        <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Try Asking</h3>
                    </div>
                    <ul class="space-y-3">
                        <li><button class="w-full text-left text-sm text-on-surface-variant hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-surface-container flex items-center gap-2 suggest-btn"><span class="text-base">💊</span> "Tell me about Paracetamol"</button></li>
                        <li><button class="w-full text-left text-sm text-on-surface-variant hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-surface-container flex items-center gap-2 suggest-btn"><span class="text-base">⚠️</span> "Side effects of Metformin"</button></li>
                        <li><button class="w-full text-left text-sm text-on-surface-variant hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-surface-container flex items-center gap-2 suggest-btn"><span class="text-base">🔄</span> "Interaction between Paracetamol and Omeprazole"</button></li>
                        <li><button class="w-full text-left text-sm text-on-surface-variant hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-surface-container flex items-center gap-2 suggest-btn"><span class="text-base">🥗</span> "Ayurvedic diet tips"</button></li>
                        <li><button class="w-full text-left text-sm text-on-surface-variant hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-surface-container flex items-center gap-2 suggest-btn"><span class="text-base">🩺</span> "I have a headache"</button></li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                    <div class="flex items-start gap-3">
                        <span class="text-xl flex-shrink-0">⚠️</span>
                        <div>
                            <h4 class="font-headline-md text-sm font-bold text-amber-800 mb-1">Medical Disclaimer</h4>
                            <p class="text-xs text-amber-700 leading-relaxed">This AI assistant provides general health information for educational purposes only. It does not replace professional medical advice, diagnosis, or treatment. Always consult a qualified healthcare provider.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="medicine-lookup" class="py-section-gap bg-background">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-3">Medicine Lookup</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl mx-auto">Search our comprehensive medicine database for detailed information</p>
        </div>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-8">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input type="text" id="medicine-search-input" class="w-full pl-11 pr-4 py-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm transition-all" placeholder="Search for a medicine (e.g., Paracetamol, Amoxicillin...)" autocomplete="off">
                </div>
                <button id="medicine-search-btn" class="px-6 py-3.5 bg-primary text-on-primary rounded-xl font-medium text-sm hover:bg-primary-container transition-all active:scale-95 flex-shrink-0">Search</button>
            </div>
            <div id="medicine-results" class="space-y-4"></div>
        </div>
    </div>
</section>

<section class="py-section-gap bg-surface-container-low">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden shadow-sm">
            <div class="p-8 md:p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-5">
                    <span class="text-3xl">🩺</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-4">Symptom Checker</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto mb-6 leading-relaxed">
                    Describe your symptoms in the chat above and I'll provide general guidance. Remember, this information is for educational purposes only.
                </p>
                <div class="inline-flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-5 text-left max-w-lg mx-auto">
                    <span class="text-lg flex-shrink-0 mt-0.5">⚠️</span>
                    <div>
                        <p class="text-sm font-bold text-amber-800 mb-1">This is for guidance only</p>
                        <p class="text-sm text-amber-700 leading-relaxed">The symptom checker provides general information and possible causes. It is <strong>not</strong> a medical diagnosis. Always consult a qualified doctor for proper evaluation and treatment, especially for persistent or severe symptoms.</p>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <button class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-primary-container transition-all active:scale-95 suggest-btn">I have a headache</button>
                    <button class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-primary-container transition-all active:scale-95 suggest-btn">I feel nauseous</button>
                    <button class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-primary-container transition-all active:scale-95 suggest-btn">I have stomach pain</button>
                    <button class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-primary-container transition-all active:scale-95 suggest-btn">I feel tired all the time</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-section-gap bg-background">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">verified</span>
                </div>
                <h4 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Reliable Information</h4>
                <p class="text-sm text-on-surface-variant">Powered by a comprehensive medicine database with accurate drug information</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">lock</span>
                </div>
                <h4 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Private & Secure</h4>
                <p class="text-sm text-on-surface-variant">Your conversations are private. No login required to use the health assistant.</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">spa</span>
                </div>
                <h4 class="font-headline-md text-title-lg font-bold text-on-surface mb-2">Ayurveda Integrated</h4>
                <p class="text-sm text-on-surface-variant">Combines modern medicine information with traditional Ayurvedic wisdom</p>
            </div>
        </div>
    </div>
</section>

<script>
const chatMessages = document.getElementById('chat-messages');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
const typingIndicator = document.getElementById('typing-indicator');

function scrollChat() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addMessage(text, isUser) {
    const div = document.createElement('div');
    div.className = 'message-enter flex items-start' + (isUser ? ' justify-end gap-3' : ' gap-3');
    if (isUser) {
        div.innerHTML = `
            <div class="bg-primary rounded-2xl rounded-br-sm px-5 py-3.5 max-w-[80%] shadow-sm">
                <p class="text-sm text-on-primary">${text}</p>
            </div>
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 mt-1">
                <span class="material-symbols-outlined text-sm text-on-primary">person</span>
            </div>
        `;
    } else {
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-1">
                <span class="material-symbols-outlined text-sm text-primary" style="font-variation-settings:'FILL' 1;">psychology</span>
            </div>
            <div class="bg-white border border-outline-variant/30 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%] shadow-sm">
                <p class="text-sm text-on-surface leading-relaxed">${text}</p>
            </div>
        `;
    }
    chatMessages.insertBefore(div, typingIndicator);
    scrollChat();
}

function showTyping(show) {
    typingIndicator.classList.toggle('hidden', !show);
    scrollChat();
}

chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if (!msg) return;

    addMessage(escapeHtml(msg), true);
    chatInput.value = '';
    showTyping(true);

    const formData = new FormData();
    formData.append('action', 'chat');
    formData.append('message', msg);

    fetch('<?= BASE_URL ?>/ai-assistant.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        showTyping(false);
        addMessage(data.response, false);
    })
    .catch(() => {
        showTyping(false);
        addMessage('Sorry, I encountered an error. Please try again.', false);
    });
});

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

document.querySelectorAll('.quick-card').forEach(card => {
    card.addEventListener('click', function() {
        chatInput.value = this.dataset.query;
        chatForm.dispatchEvent(new Event('submit'));
        document.querySelector('#chat-messages').closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

document.querySelectorAll('.suggest-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        chatInput.value = this.textContent.trim().replace(/^["']|["']$/g, '');
        chatForm.dispatchEvent(new Event('submit'));
    });
});

document.getElementById('medicine-search-btn').addEventListener('click', function() {
    const q = document.getElementById('medicine-search-input').value.trim();
    if (!q) return;
    const resultsDiv = document.getElementById('medicine-results');
    resultsDiv.innerHTML = '<div class="text-center py-8 text-on-surface-variant"><div class="inline-block w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin mb-3"></div><p>Searching...</p></div>';
    const formData = new FormData();
    formData.append('action', 'medicine_search');
    formData.append('q', q);
    fetch('<?= BASE_URL ?>/ai-assistant.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        resultsDiv.innerHTML = data.html;
        resultsDiv.querySelectorAll('.toggle-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const detail = document.getElementById('detail-' + this.dataset.drugId);
                const isHidden = detail.classList.contains('hidden');
                detail.classList.toggle('hidden');
                this.textContent = isHidden ? 'Hide Details -' : 'View Full Details +';
            });
        });
        resultsDiv.querySelectorAll('.medicine-result').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.toggle-detail')) return;
                const name = this.querySelector('h4')?.textContent || '';
                if (name) {
                    chatInput.value = 'Tell me about ' + name;
                    chatForm.dispatchEvent(new Event('submit'));
                    document.querySelector('#chat-messages').closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
});

document.getElementById('medicine-search-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') document.getElementById('medicine-search-btn').click();
});

scrollChat();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
