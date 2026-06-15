<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome | <?= SITE_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
<style>
body{font-family:'DM Sans',sans-serif}.brand{font-family:'Plus Jakarta Sans',sans-serif}@keyframes in{from{opacity:0;transform:translateY(18px) scale(.96)}to{opacity:1;transform:none}}.enter{opacity:0;animation:in .8s ease forwards}.d1{animation-delay:.4s}.d2{animation-delay:1.2s}.d3{animation-delay:2s}.d4{animation-delay:3s}
</style>
</head>
<body class="min-h-screen bg-gradient-to-b from-[#005221] to-[#003818] text-white">
<main class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-6 text-center" onclick="document.querySelectorAll('.enter').forEach(el=>el.style.animationDelay='0s')">
    <div class="absolute inset-0 opacity-[0.05]" style="background-image:radial-gradient(circle at 20px 20px,#fff 2px,transparent 0);background-size:42px 42px"></div>
    <div class="enter d1 flex h-24 w-24 items-center justify-center rounded-full bg-white/10"><span class="material-symbols-outlined text-6xl">eco</span></div>
    <h1 class="enter d2 brand mt-8 text-5xl font-bold">AyurViora</h1>
    <p class="enter d3 mt-4 text-lg text-white/70">Your Complete Ayurvedic Healthcare Platform</p>
    <div class="enter d4 mt-10 flex max-w-4xl flex-wrap justify-center gap-3"><?php foreach (['Medicine Delivery','Video Consult','Lab Tests','AI Health Assistant','B2B Order Punch'] as $badge): ?><span class="rounded-full bg-white px-5 py-2 font-semibold text-[#005221]"><?= htmlspecialchars($badge) ?></span><?php endforeach; ?></div>
    <div class="enter d4 mt-10"><a href="<?= BASE_URL ?>/index.php" class="inline-flex rounded-xl bg-white px-8 py-4 font-bold text-[#005221] shadow-xl transition hover:bg-[#D4AF37] hover:text-white">Continue to AyurViora</a><p class="mt-4 text-sm text-white/60">By continuing, you agree to our Terms & Privacy Policy.</p><p class="mt-2 text-sm"><a href="<?= BASE_URL ?>/login.php" class="underline">Already have an account? Log in</a></p></div>
</main>
</body>
</html>

