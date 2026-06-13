<?php
/**
 * Ayurwellness AI Chatbot Widget
 * Handles ordering, consultations, FAQs, and customer support
 */
$botResponses = [
    'greeting' => [
        'keywords' => ['hi', 'hello', 'hey', 'namaste', 'नमस्ते', 'नमस्कार', 'hii', 'hello ji'],
        'response' => "🙏 Namaste! I'm AyurBot, your Ayurvedic wellness assistant. I can help you with:\n\n🛒 Placing orders\n📅 Booking consultations\n🌿 Learning about Ayurveda\n❓ Answering FAQs\n\nHow can I help you today?"
    ],
    'order' => [
        'keywords' => ['order', 'buy', 'purchase', 'खरीद', 'ऑर्डर', 'product', 'उत्पाद', 'add to cart', 'cart'],
        'response' => "Sure! I can help you order products. Here are our top categories:\n\n🌿 <a href='shop.php' class='text-primary font-bold'>Immunity Boosters</a>\n🍃 <a href='shop.php' class='text-primary font-bold'>Digestive Care</a>\n💆 <a href='shop.php' class='text-primary font-bold'>Hair & Skin Care</a>\n⚖️ <a href='shop.php' class='text-primary font-bold'>Weight Management</a>\n\nOr <a href='shop.php' class='text-primary font-bold'>browse all products →</a>\n\nTell me which category interests you!"
    ],
    'consult' => [
        'keywords' => ['doctor', 'consult', 'appointment', 'अपॉइंटमेंट', 'डॉक्टर', 'consultation', 'book', 'video call', 'वीडियो'],
        'response' => "I can help you book a consultation! We offer:\n\n📹 <a href='video-consult.php' class='text-primary font-bold'>Video Consultations</a> — Face-to-face with doctors\n🏥 <a href='appointment-booking.php' class='text-primary font-bold'>Clinic Appointment</a> — In-person visits\n\nOur expert doctors specialize in:\n• Internal Medicine (Kayachikitsa)\n• Panchakarma Detox\n• Women's Health\n• Stress & Lifestyle Management\n\n<a href='doctor-listing.php' class='text-primary font-bold'>View all doctors →</a>"
    ],
    'dosha' => [
        'keywords' => ['dosha', 'दोष', 'vata', 'pitta', 'kapha', 'body type', 'prakriti', 'प्रकृति', 'ayurvedic analysis'],
        'response' => "🌿 Knowing your Dosha is the first step to wellness!\n\nTake our <a href='dosha-quiz.php' class='text-primary font-bold'>3-minute AI Body Analysis</a> to discover your Vata-Pitta-Kapha profile.\n\nYou'll get:\n✅ Your dominant Dosha\n✅ Personalized diet plan\n✅ Yoga & lifestyle tips\n✅ Herbal recommendations"
    ],
    'health_coach' => [
        'keywords' => ['reminder', 'रिमाइंडर', 'health coach', 'हेल्थ कोच', 'medicine', 'दवा', 'water', 'पानी', 'yoga', 'योग'],
        'response' => "📧 Set up your <a href='health-coach.php' class='text-primary font-bold'>Email Health Coach</a>!\n\nGet daily reminders for:\n💊 Medicine timings\n💧 Water intake (every 2 hours)\n🧘 Yoga & exercise\n🥗 Diet & meals\n\n<a href='health-coach.php' class='text-primary font-bold'>Set up reminders →</a>"
    ],
    'dashboard' => [
        'keywords' => ['dashboard', 'डैशबोर्ड', 'my health', 'मेरा स्वास्थ्य', 'progress', 'प्रोग्रेस', 'weight', 'वजन', 'track'],
        'response' => "📊 Track your health journey with your <a href='my-health.php' class='text-primary font-bold'>90-Day Dashboard</a>!\n\nYou can log:\n⚖️ Weight\n😴 Sleep hours\n🤕 Pain score\n❤️ BP & Blood Sugar\n\n<a href='my-health.php' class='text-primary font-bold'>View my dashboard →</a>"
    ],
    'family' => [
        'keywords' => ['family', 'परिवार', 'family account', 'spouse', 'बच्चे', 'parents'],
        'response' => "👨‍👩‍👧‍👦 Manage your whole family's health with one account!\n\nAdd family members and track health for:\n• Spouse\n• Children\n• Parents\n\nAll reports & medicines at one place.\n<a href='my-family.php' class='text-primary font-bold'>Manage family →</a>"
    ],
    'contact' => [
        'keywords' => ['contact', 'संपर्क', 'support', 'help', 'मदद', 'call', 'phone', 'फोन', 'email'],
        'response' => "📞 Get in touch with us:\n\n📧 Email: support@ayurwellness.com\n📱 Phone: +91-XXXXXXXXXX\n💬 <a href='contact-us.php' class='text-primary font-bold'>Contact Form</a>\n\nWe're here Mon-Sat, 9AM to 7PM!"
    ],
    'pricing' => [
        'keywords' => ['price', 'कीमत', 'cost', 'fee', 'फीस', 'rate', 'charge', '₹'],
        'response' => "💰 Our consultations start at just ₹500!\n\n• Video Consult: ₹500 - ₹1000\n• Clinic Visit: ₹750 - ₹1500\n• Wellness Plans: ₹799 - ₹1499\n\n<a href='doctor-listing.php' class='text-primary font-bold'>See all doctors & fees →</a>"
    ],
    'about' => [
        'keywords' => ['about', 'बारे में', 'who', 'कौन', 'company', 'ayurwellness'],
        'response' => "🌿 <b>Ayurwellness</b> — Ancient Wisdom for Modern Living.\n\nWe are a team of certified Ayurvedic practitioners dedicated to bringing authentic Ayurveda to your doorstep. With 30+ years of clinical experience, we combine traditional knowledge with modern quality standards.\n\n<a href='about-us.php' class='text-primary font-bold'>Learn more →</a>"
    ]
];

// Search products from database
function chatbotSearchProducts($query) {
    $db = getDB();
    $keyword = '%' . $query . '%';
    $stmt = $db->prepare("SELECT p.id, p.name, p.slug, p.description, p.price, p.compare_price, p.rating, p.reviews_count, p.is_bestseller, c.name AS category
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(c.name) LIKE ?
        LIMIT 5");
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get single product by exact name match
function chatbotGetProduct($query) {
    $db = getDB();
    $keyword = '%' . $query . '%';
    $stmt = $db->prepare("SELECT p.*, c.name AS category
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        WHERE LOWER(p.name) LIKE ? OR LOWER(p.slug) LIKE ? OR LOWER(p.name) = ?
        LIMIT 1");
    $stmt->bind_param("sss", $keyword, $keyword, $query);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Format single product detail card
function formatSingleProductHtml($p, $lang) {
    $isHi = $lang === 'hi';
    $savings = $p['compare_price'] > $p['price']
        ? round((($p['compare_price'] - $p['price']) / $p['compare_price']) * 100) : 0;
    $bestseller = $p['is_bestseller']
        ? '<span style="background:#fed65b;color:#012d1d;font-size:11px;padding:2px 8px;border-radius:4px;font-weight:bold;">★ ' . ($isHi ? 'बेस्टसेलर' : 'Bestseller') . '</span>' : '';
    $stars = str_repeat('⭐', round($p['rating'] ?? 0));

    $html = '<div style="background:linear-gradient(135deg,#f0faf5,#f4fafd);padding:14px;border-radius:12px;border:1px solid #b7e4c7;">';
    $html .= '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px;">';
    $html .= '<div style="font-weight:700;font-size:15px;color:#012d1d;">' . htmlspecialchars($p['name']) . '</div>';
    $html .= $bestseller;
    $html .= '</div>';
    $html .= '<div style="font-size:13px;color:#444;margin-bottom:8px;line-height:1.5;">' . htmlspecialchars($p['description']) . '</div>';
    $html .= '<div style="background:white;padding:10px 12px;border-radius:8px;margin-bottom:8px;">';
    $html .= '<div style="display:flex;align-items:center;gap:8px;">';
    $html .= '<span style="font-size:20px;font-weight:700;color:#012d1d;">₹' . $p['price'] . '</span>';
    if ($p['compare_price'] > $p['price']) {
        $html .= '<span style="text-decoration:line-through;color:#999;font-size:14px;">₹' . $p['compare_price'] . '</span>';
        $html .= '<span style="background:#dcfce7;color:#16a34a;font-size:12px;font-weight:600;padding:2px 6px;border-radius:4px;">' . $savings . '% off</span>';
    }
    $html .= '</div>';
    if ($p['rating']) {
        $html .= '<div style="font-size:13px;color:#666;margin-top:4px;">' . $stars . ' <span style="font-weight:600;">' . $p['rating'] . '</span>/5 (' . $p['reviews_count'] . ' ' . ($isHi ? 'समीक्षाएँ' : 'reviews') . ')</div>';
    }
    $html .= '<div style="font-size:12px;color:#888;margin-top:2px;">📁 ' . ($isHi ? 'श्रेणी' : 'Category') . ': ' . htmlspecialchars($p['category'] ?? '') . ($p['stock'] > 0 ? ' | ✅ ' . ($isHi ? 'स्टॉक में' : 'In Stock') : ' | ❌ ' . ($isHi ? 'स्टॉक खत्म' : 'Out of Stock')) . '</div>';
    $html .= '</div>';
    $html .= '<div style="display:flex;gap:8px;">';
    $html .= '<a href="' . BASE_URL . '/product-details.php?slug=' . urlencode($p['slug']) . '" style="background:#012d1d;color:white;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">' . ($isHi ? '📄 विवरण देखें' : '📄 View Details') . '</a>';
    $html .= '<button onclick="addToCartBtn(' . $p['id'] . ',\'' . htmlspecialchars($p['name'], ENT_QUOTES) . '\')" style="background:#fed65b;color:#012d1d;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">' . ($isHi ? '🛒 कार्ट में डालें' : '🛒 Add to Cart') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

// Format products list cards
function formatProductsHtml($products, $lang) {
    if (empty($products)) return '';
    $isHi = $lang === 'hi';
    $html = '<div style="display:flex;flex-direction:column;gap:8px;">';
    foreach ($products as $p) {
        $savings = $p['compare_price'] > $p['price']
            ? '<span style="color:#16a34a;font-size:12px;"> ' . round((($p['compare_price'] - $p['price']) / $p['compare_price']) * 100) . '% off</span>'
            : '';
        $bestseller = $p['is_bestseller']
            ? '<span style="background:#fed65b;color:#012d1d;font-size:10px;padding:1px 6px;border-radius:4px;font-weight:bold;margin-left:6px;">★ ' . ($isHi ? 'बेस्टसेलर' : 'Bestseller') . '</span>'
            : '';
        $stars = str_repeat('⭐', round($p['rating'] ?? 0));
        $html .= '<div style="background:#f4fafd;padding:10px 12px;border-radius:10px;border:1px solid #dde4e6;">';
        $html .= '<div style="font-weight:700;font-size:14px;color:#012d1d;">' . htmlspecialchars($p['name']) . $bestseller . '</div>';
        $html .= '<div style="font-size:13px;color:#555;margin:3px 0;">' . htmlspecialchars(mb_substr($p['description'], 0, 120)) . '...</div>';
        $html .= '<div style="display:flex;align-items:center;gap:8px;margin-top:4px;">';
        $html .= '<span style="font-weight:700;font-size:16px;color:#012d1d;">₹' . $p['price'] . '</span>';
        if ($p['compare_price'] > $p['price']) {
            $html .= '<span style="text-decoration:line-through;color:#999;font-size:13px;">₹' . $p['compare_price'] . '</span>';
        }
        $html .= $savings;
        $html .= '</div>';
        if ($p['rating']) {
            $html .= '<div style="font-size:12px;color:#666;margin-top:2px;">' . $stars . ' ' . $p['rating'] . ' (' . $p['reviews_count'] . ' ' . ($isHi ? 'समीक्षाएँ' : 'reviews') . ')</div>';
        }
        $html .= '<div style="display:flex;gap:6px;margin-top:6px;">';
        $html .= '<a href="' . BASE_URL . '/product-details.php?slug=' . urlencode($p['slug']) . '" style="font-size:12px;font-weight:600;color:#012d1d;text-decoration:underline;">' . ($isHi ? 'विवरण →' : 'Details →') . '</a>';
        $html .= '<button onclick="addToCartBtn(' . $p['id'] . ',\'' . htmlspecialchars($p['name'], ENT_QUOTES) . '\')" style="font-size:12px;font-weight:600;color:#012d1d;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;">' . ($isHi ? '🛒 कार्ट में डालें' : '🛒 Add to Cart') . '</button>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '<a href="' . BASE_URL . '/shop.php" style="text-align:center;display:block;margin-top:4px;font-size:13px;font-weight:600;color:#012d1d;">' . ($isHi ? '🏪 सभी उत्पाद देखें →' : '🏪 Browse All Products →') . '</a>';
    $html .= '</div>';
    return $html;
}

// Handle AJAX chatbot request
if (isset($_GET['chatbot_ajax']) && isset($_GET['message'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/ai-helper.php';
    
    $msg = trim(strtolower($_GET['message']));
    $lang = $_SESSION['lang'] ?? 'hi';
    $db = getDB();
    
    // Step 1: Check if query is product-related — search DB for real products
    $productQuery = null;
    $productKeywords = ['price', 'benefit', 'product', 'cost', 'rate', '₹', 'buy', 'herb',
        'supplement', 'capsule', 'oil', 'powder', 'drops', 'tablet', 'cream', 'mask', 'tea',
        'कीमत', 'लाभ', 'उत्पाद', 'मूल्य', 'खरीद', 'दाम', 'फायदे',
        'immunity', 'digestion', 'stress', 'hair', 'skin', 'weight', 'sugar', 'diabetes',
        'liver', 'sleep', 'energy', 'prostate', 'pregnancy', 'pcod', 'menopause',
        'ashwagandha', 'giloy', 'tulsi', 'moringa', 'chyawanprash', 'triphala', 'brahmi',
        'amla', 'neem', 'haldi', 'shilajit', 'shatavari', 'gokshura', 'bhringraj',
        'guggulu', 'pippali', 'karela', 'jamun', 'methi', 'cinnamon', 'lodhra', 'ashoka',
        'sandalwood', 'kumkumadi', 'aloe vera', 'multani', 'henna', 'garcinia',
        'echinacea', 'psyllium', 'maca', 'safed musli', 'vijaysar', 'hingwastak',
        'ajmoda', 'dhatura', 'churna', 'gurmar', 'seplilin'];
    
    // Detect if user is asking about a specific product
    $productQuery = trim($_GET['message']);
    
    // Try exact product name match first
    $exactProduct = chatbotGetProduct(mb_strtolower($productQuery));
    
    if ($exactProduct) {
        $matchedProducts = [$exactProduct];
        $isSingleProduct = true;
    } else {
        // Check if any product keyword is mentioned
        $isProductRelated = false;
        foreach ($productKeywords as $kw) {
            if (strpos($msg, $kw) !== false) {
                $isProductRelated = true;
                break;
            }
        }
        
        $matchedProducts = [];
        $isSingleProduct = false;
        
        if ($isProductRelated) {
            // Extract product name for more precise search
            if (preg_match('/(?:about|benefits?|price|cost|what is|tell me about|के बारे में|क्या है|कीमत|लाभ|फायदे)\s+(.+?)(?:\?|$)/iu', $_GET['message'], $m)) {
                $productQuery = trim($m[1]);
            }
            // Remove common words to improve search
            $searchTerms = preg_replace('/\b(about|benefits?|price|cost|what|tell|me|is|the|for|and|of|in|with|के|बारे|में|क्या|है|कीमत|लाभ|और|का|की|से|को|पर|show|list|give|want|need|some|find)\b/iu', '', $productQuery);
            $searchTerms = trim(preg_replace('/\s+/', ' ', $searchTerms));
            if (empty($searchTerms)) $searchTerms = $productQuery;
            $matchedProducts = chatbotSearchProducts(mb_strtolower($searchTerms));
        }
    }
    
    // Step 2: Build context with real product data if found
    $productContext = '';
    if (!empty($matchedProducts)) {
        $productContext = "REAL PRODUCTS FROM OUR DB:\n";
        foreach ($matchedProducts as $p) {
            $productContext .= "- {$p['name']}: ₹{$p['price']}" . ($p['compare_price'] > $p['price'] ? " (was ₹{$p['compare_price']})" : "")
                . " | Rating: {$p['rating']}/5 ({$p['reviews_count']} reviews)" . ($p['is_bestseller'] ? ' ★BESTSELLER' : '')
                . " | Category: {$p['category']}"
                . " | Description: {$p['description']}\n";
        }
    }
    
    // Step 3: Try AI with product context (don't pass context for non-product queries)
    $aiResponse = null;
    if (!empty($matchedProducts)) {
        $aiResponse = chatbotAIResponseProduct($_GET['message'], $lang, $matchedProducts, $productContext);
    } else {
        $aiResponse = chatbotAIResponse($_GET['message'], $lang);
    }
    
    if ($aiResponse !== null) {
        $extra = '';
        if (!empty($matchedProducts)) {
            $extra = '<br><br>' . ($isSingleProduct
                ? formatSingleProductHtml($matchedProducts[0], $lang)
                : formatProductsHtml($matchedProducts, $lang));
        }
        echo json_encode(['response' => nl2br($aiResponse) . $extra]);
        exit;
    }
    
    // Step 4: Fallback: show products directly or keyword matching
    $response = "OpenAI is not configured yet. Add your OpenAI API key in Admin > Settings, then AyurBot can answer general wellness questions. For now, try: order, doctor, dosha, health coach, dashboard, family, contact, about.";
    
    // Show matched products as fallback
    if (!empty($matchedProducts)) {
        $response = ($isSingleProduct ? '' : ($lang === 'hi' ? 'ये हमारे संबंधित उत्पाद हैं:' : 'Here are related products:') . '<br><br>')
            . ($isSingleProduct ? formatSingleProductHtml($matchedProducts[0], $lang) : formatProductsHtml($matchedProducts, $lang));
    } else {
        foreach ($botResponses as $intent) {
            foreach ($intent['keywords'] as $keyword) {
                if (strpos($msg, $keyword) !== false) {
                    $response = $intent['response'];
                    break 2;
                }
            }
        }
    }
    
    // Check if user wants to add a product to cart
    if (preg_match('/add\s+(.+?)\s+to\s+cart/i', $_GET['message'], $m)) {
        $productName = trim($m[1]);
        $stmt = $db->prepare("SELECT id, name, price FROM products WHERE LOWER(name) LIKE ? LIMIT 1");
        $like = '%' . strtolower($productName) . '%';
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if ($product) {
            $response = "🛒 Added <b>" . htmlspecialchars($product['name']) . "</b> (₹" . $product['price'] . ") to your cart! <a href='shopping-cart.php' class='text-primary font-bold'>View Cart →</a>";
        } else {
            $response = "Sorry, I couldn't find \"$productName\". <a href='shop.php' class='text-primary font-bold'>Browse all products →</a>";
        }
    }
    
    echo json_encode(['response' => nl2br($response)]);
    exit;
}

// Display the chatbot HTML
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!-- Ayurwellness Chatbot Widget -->
<style>
.chatbot-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #012d1d, #1b4332);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(1, 45, 29, 0.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}
.chatbot-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(1, 45, 29, 0.5);
}
.chatbot-btn .material-symbols-outlined {
    font-size: 28px;
}
.chatbot-window {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 380px;
    height: 560px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 9998;
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}
.chatbot-window.open {
    display: flex;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.chatbot-header {
    background: linear-gradient(135deg, #012d1d, #1b4332);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chatbot-header h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chatbot-header-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.chatbot-header-close:hover {
    background: rgba(255,255,255,0.3);
}
.chatbot-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    background: #f4fafd;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.chatbot-msg {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.5;
    animation: msgIn 0.3s ease;
}
@keyframes msgIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.chatbot-msg.bot {
    background: white;
    color: #161d1f;
    border: 1px solid #dde4e6;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.chatbot-msg.user {
    background: #1b4332;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.chatbot-input-area {
    padding: 12px 16px;
    border-top: 1px solid #dde4e6;
    display: flex;
    gap: 8px;
    background: white;
}
.chatbot-input-area input {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid #dde4e6;
    border-radius: 24px;
    outline: none;
    font-size: 14px;
    transition: border 0.2s;
}
.chatbot-input-area input:focus {
    border-color: #1b4332;
}
.chatbot-input-area button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #1b4332;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.chatbot-input-area button:hover {
    background: #012d1d;
}
.chatbot-typing {
    align-self: flex-start;
    padding: 12px 16px;
    background: white;
    border: 1px solid #dde4e6;
    border-radius: 16px;
    border-bottom-left-radius: 4px;
    display: none;
}
.chatbot-typing span {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #1b4332;
    border-radius: 50%;
    margin: 0 2px;
    animation: typing 1.4s infinite;
}
.chatbot-typing span:nth-child(2) { animation-delay: 0.2s; }
.chatbot-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 60%, 100% { opacity: 0.3; transform: scale(0.8); }
    30% { opacity: 1; transform: scale(1); }
}
@media (max-width: 480px) {
    .chatbot-window {
        width: calc(100vw - 32px);
        right: 16px;
        bottom: 88px;
        height: 70vh;
    }
    .chatbot-btn {
        right: 16px;
        bottom: 16px;
    }
}
</style>

<button class="chatbot-btn" id="chatbot-toggle" onclick="toggleChatbot()" aria-label="Open AyurBot AI chat" title="AyurBot AI — Click to chat">
    <span class="material-symbols-outlined">chat</span>
    <span class="absolute -top-1 -right-1 bg-[#fed65b] text-[#012d1d] text-[10px] font-bold px-1.5 py-0.5 rounded-full">AI</span>
</button>

<div class="chatbot-window" id="chatbot-window">
    <div class="chatbot-header">
        <h3><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">spa</span> AyurBot</h3>
        <button class="chatbot-header-close" onclick="toggleChatbot()">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <div class="chatbot-messages" id="chatbot-messages">
        <div class="chatbot-msg bot">🙏 Namaste! I'm <b>AyurBot</b>, your wellness assistant.<br><br>I can help you:<br>🛒 Order products<br>📅 Book consultations<br>🌿 Learn about Ayurveda<br>❓ Answer questions<br><br>How can I help you today?</div>
    </div>
    <div class="chatbot-typing" id="chatbot-typing">
        <span></span><span></span><span></span>
    </div>
    <div class="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Type your message..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button onclick="sendMessage()">
            <span class="material-symbols-outlined" style="font-size:20px;">send</span>
        </button>
    </div>
</div>

<script>
function toggleChatbot() {
    const w = document.getElementById('chatbot-window');
    const btn = document.getElementById('chatbot-toggle');
    w.classList.toggle('open');
    btn.querySelector('.material-symbols-outlined').textContent = w.classList.contains('open') ? 'close' : 'chat';
    if (w.classList.contains('open')) {
        document.getElementById('chatbot-input').focus();
    }
}

function addToCartBtn(id, name) {
    fetch('<?= BASE_URL ?>/cart-update.php?action=add&id=' + id)
        .then(() => {
            const badge = document.querySelector('#cart-count-badge');
            if (badge) {
                const c = parseInt(badge.textContent) || 0;
                badge.textContent = c + 1;
            }
            const msgs = document.getElementById('chatbot-messages');
            const div = document.createElement('div');
            div.className = 'chatbot-msg bot';
            div.innerHTML = '🛒 <b>' + name + '</b> added to cart! <a href="<?= BASE_URL ?>/shopping-cart.php" style="color:#012d1d;font-weight:700;">View Cart →</a>';
            msgs.appendChild(div);
            msgs.scrollTop = msgs.scrollHeight;
        });
}

function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const msg = input.value.trim();
    if (!msg) return;
    
    const msgs = document.getElementById('chatbot-messages');
    const userDiv = document.createElement('div');
    userDiv.className = 'chatbot-msg user';
    userDiv.textContent = msg;
    msgs.appendChild(userDiv);
    input.value = '';
    msgs.scrollTop = msgs.scrollHeight;
    
    // Show typing
    document.getElementById('chatbot-typing').style.display = 'flex';
    
    // Send to server
    fetch('<?= BASE_URL ?>/includes/chatbot.php?chatbot_ajax=1&message=' + encodeURIComponent(msg))
        .then(r => r.json())
        .then(data => {
            document.getElementById('chatbot-typing').style.display = 'none';
            const botDiv = document.createElement('div');
            botDiv.className = 'chatbot-msg bot';
            botDiv.innerHTML = data.response;
            msgs.appendChild(botDiv);
            msgs.scrollTop = msgs.scrollHeight;
        })
        .catch(() => {
            document.getElementById('chatbot-typing').style.display = 'none';
            const botDiv = document.createElement('div');
            botDiv.className = 'chatbot-msg bot';
            botDiv.textContent = 'Sorry, I\'m having trouble connecting. Please try again.';
            msgs.appendChild(botDiv);
            msgs.scrollTop = msgs.scrollHeight;
        });
}
</script>
