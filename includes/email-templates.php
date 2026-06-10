<?php
function getReminderEmailHtml($type, $userName) {
    $templates = [
        'medicine' => [
            'subject' => '🌿 दवा लेने का समय - Ayurwellness',
            'icon' => '💊',
            'title' => 'दवा लेने का समय',
            'message' => "प्रिय {$userName},<br><br>आपकी आयुर्वेदिक दवा लेने का समय हो गया है। कृपया अपनी निर्धारित दवा समय पर लें।<br><br>स्वस्थ रहने का सबसे अच्छा तरीका है नियमित दिनचर्या का पालन करना।",
            'tip' => 'टिप: दवा को खाली पेट या डॉक्टर के निर्देशानुसार लें।'
        ],
        'water' => [
            'subject' => '💧 पानी पीने की याद - Ayurwellness',
            'icon' => '💧',
            'title' => 'पानी पीएँ',
            'message' => "प्रिय {$userName},<br><br>आयुर्वेद में पर्याप्त पानी पीना बहुत महत्वपूर्ण है। कृपया एक गिलास पानी पी लें।<br><br>दिन में 8-10 गिलास पानी पीना आपके स्वास्थ्य के लिए लाभदायक है।",
            'tip' => 'टिप: गुनगुना पानी पीना पाचन के लिए अधिक लाभदायक है।'
        ],
        'yoga' => [
            'subject' => '🧘 योग का समय - Ayurwellness',
            'icon' => '🧘',
            'title' => 'योग का समय',
            'message' => "प्रिय {$userName},<br><br>योग और व्यायाम का समय हो गया है। 15-20 मिनट का योग आपके शरीर और मन को स्वस्थ रखता है।<br><br>नियमित योग से वात-पित्त-कफ संतुलन बना रहता है।",
            'tip' => 'टिप: सूर्य नमस्कार से शुरू करें — यह पूरे शरीर के लिए सर्वोत्तम है।'
        ],
        'diet' => [
            'subject' => '🥗 आहार सुझाव - Ayurwellness',
            'icon' => '🥗',
            'title' => 'आहार का समय',
            'message' => "प्रिय {$userName},<br><br>आयुर्वेद में सही आहार को सबसे अच्छी दवा माना गया है। कृपया संतुलित भोजन करें।<br><br>मौसमी फल, हरी सब्जियाँ और साबुत अनाज आपके स्वास्थ्य के लिए उत्तम हैं।",
            'tip' => 'टिप: खाना हमेशा बैठकर और शांत मन से खाएँ।'
        ]
    ];

    $t = $templates[$type];
    return [
        'subject' => $t['subject'],
        'html' => <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: 'Manrope', Arial, sans-serif; background: #f4fafd; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
<div style="background: linear-gradient(135deg, #012d1d, #1b4332); padding: 30px; text-align: center;">
<div style="font-size: 48px; margin-bottom: 10px;">{$t['icon']}</div>
<h1 style="color: white; font-size: 24px; margin: 0;">{$t['title']}</h1>
</div>
<div style="padding: 30px;">
<p style="font-size: 16px; line-height: 1.6; color: #333;">{$t['message']}</p>
<div style="background: #fed65b20; border-left: 4px solid #fed65b; padding: 15px; margin: 20px 0; border-radius: 4px;">
<p style="margin: 0; font-size: 14px; color: #735c00;">💡 {$t['tip']}</p>
</div>
<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">
<p style="font-size: 12px; color: #999; text-align: center;">© Ayurwellness — Ancient Wisdom for Modern Living</p>
</div>
</div>
</body>
</html>
HTML
    ];
}

function getWaterReminderTimes() {
    return ['09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];
}
