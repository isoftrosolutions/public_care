<?php
function getDoshaQuestions($conn) {
    $stmt = $conn->prepare("SELECT * FROM dosha_questions WHERE active = TRUE ORDER BY display_order");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function calculateDosha($responses, $questions) {
    $scores = ['vata' => 0, 'pitta' => 0, 'kapha' => 0];
    foreach ($responses as $qId => $answer) {
        foreach ($questions as $q) {
            if ($q['id'] == $qId) {
                $category = $q['category'];
                $weight = $q['weight'];
                $scores[$category] += ($answer * $weight);
                break;
            }
        }
    }
    $max = max($scores);
    $dominant = array_search($max, $scores);
    $recommendations = getDoshaRecommendations($dominant);
    return [
        'scores' => $scores,
        'dominant' => $dominant,
        'recommendations' => $recommendations
    ];
}

function getDoshaRecommendations($dosha) {
    $all = [
        'vata' => [
            'diet' => 'गरम, तैलीय और पौष्टिक भोजन लें। कड़वे, सूखे और ठंडे खाद्य पदार्थों से बचें। मीठा, खट्टा और नमकीन स्वाद लाभदायक है।',
            'lifestyle' => 'नियमित दिनचर्या रखें। गरम तेल की मालिश करें। पर्याप्त आराम करें। हल्का योग और ध्यान करें।',
            'yoga' => 'वृक्षासन, ताड़ासन, भुजंगासन — ये आसन वात संतुलन में मददगार हैं।',
            'herbs' => 'अश्वगंधा, शतावरी, हल्दी — ये जड़ी-बूटियाँ वात को शांत करती हैं।'
        ],
        'pitta' => [
            'diet' => 'ठंडा, हल्का और मीठा भोजन लें। तीखे, खट्टे और नमकीन से बचें। तरबूज, खीरा, नारियल पानी लाभदायक है।',
            'lifestyle' => 'ठंडे वातावरण में रहें। अत्यधिक गर्मी और धूप से बचें। तैराकी और आरामदायक गतिविधियाँ करें।',
            'yoga' => 'शीतली प्राणायाम, चंद्र नमस्कार, पश्चिमोत्तानासन — पित्त शांत करते हैं।',
            'herbs' => 'एलोवेरा, आंवला, ब्राह्मी — पित्त संतुलन के लिए उत्तम हैं।'
        ],
        'kapha' => [
            'diet' => 'हल्का, गरम और सूखा भोजन लें। मीठे, तैलीय और भारी खाद्य पदार्थों से बचें। तीखा और कड़वा स्वाद लाभदायक है।',
            'lifestyle' => 'सुबह जल्दी उठें। नियमित व्यायाम करें। नई आदतें अपनाएँ और दिनचर्या में विविधता लाएँ।',
            'yoga' => 'सूर्य नमस्कार, कपालभाति, भस्त्रिका प्राणायाम — कफ को कम करते हैं।',
            'herbs' => 'अदरक, तुलसी, दालचीनी, पिप्पली — कफ दोष कम करने में सहायक।'
        ]
    ];
    $r = $all[$dosha];
    $names = ['vata' => 'वात', 'pitta' => 'पित्त', 'kapha' => 'कफ'];
    return "<h4>आपका प्रमुख दोष: {$names[$dosha]}</h4>
<p><strong>आहार (Diet):</strong> {$r['diet']}</p>
<p><strong>जीवनशैली (Lifestyle):</strong> {$r['lifestyle']}</p>
<p><strong>योग (Yoga):</strong> {$r['yoga']}</p>
<p><strong>जड़ी-बूटियाँ (Herbs):</strong> {$r['herbs']}</p>";
}

function saveDoshaAssessment($conn, $userId, $scores, $dominant, $recommendations, $responses) {
    $stmt = $conn->prepare("INSERT INTO dosha_assessments (user_id, vata_score, pitta_score, kapha_score, dominant_dosha, recommendations) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $userId, $scores['vata'], $scores['pitta'], $scores['kapha'], $dominant, $recommendations);
    $stmt->execute();
    $assessmentId = $stmt->insert_id;

    if (!empty($responses)) {
        $stmt2 = $conn->prepare("INSERT INTO dosha_responses (assessment_id, question_id, answer_value) VALUES (?, ?, ?)");
        foreach ($responses as $qId => $answer) {
            $stmt2->bind_param("iii", $assessmentId, $qId, $answer);
            $stmt2->execute();
        }
        $stmt2->close();
    }
    $stmt->close();
    return $assessmentId;
}

function getUserDoshaHistory($conn, $userId, $limit = 5) {
    $stmt = $conn->prepare("SELECT * FROM dosha_assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
