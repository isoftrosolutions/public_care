<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section -->
<section class="relative h-[819px] flex items-center overflow-hidden">
<div class="absolute inset-0 z-0">
<img alt="Ancient Wisdom Modern Precision" class="w-full h-full object-cover brightness-75" data-alt="A high-end cinematic shot of fresh Ayurvedic herbs like Ashwagandha and Brahmi arranged on a clean, light ivory stone surface. The lighting is soft and ethereal, capturing the fine textures and vibrant greens of the leaves. The aesthetic is clean, medical-grade minimalism blended with traditional botanical heritage, using a palette of deep forest green and soft cream tones." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBMFr7zJc1aoyvW5h0fi27sI4oYC9u-V5iUgfdVQs3obhW1HplTEi9R886lnmMKWm6x0cPWkxyul1KfCsFSe9DJ382Uh-SIxxwCEDoN_T21z0tFwQXbuv6euYBHGwI60K6lR-GcJ1Fc-h4p4qnZ5RdgyEELG0NhRoRBbMjlQDjLCeSy--qqoWt3Wrsr7xbW62ldKwCFrixEkB1-aHLtDLmYW9rUX4TuI37E7GfquLR7pWbdFttQhymuzYX9riZTfYpa_umlbjLKYE4"/>
<div class="absolute inset-0 bg-gradient-to-r from-primary/60 to-transparent"></div>
</div>
<div class="relative z-10 px-gutter max-w-container-max mx-auto w-full">
<div class="max-w-2xl">
<h1 class="font-display-lg text-display-lg text-white mb-6">Ancient Wisdom, Modern Precision</h1>
<p class="text-body-lg text-white/90 mb-8 max-w-lg">Bridging the gap between timeless Ayurvedic healing and contemporary medical excellence to nurture your enduring wellness.</p>
<div class="flex gap-4">
<a class="bg-secondary-container text-on-secondary-container px-8 py-4 rounded-full font-label-md hover:scale-105 transition-transform inline-block" href="<?= BASE_URL ?>/about-us.php">Explore Our Heritage</a>
</div>
</div>
</div>
</section>

<!-- 2. Company Story -->
<section class="py-section-gap px-gutter max-w-container-max mx-auto">
<div class="grid grid-cols-1 md:grid-cols-2 gap-gutter items-center">
<div class="relative">
<div class="aspect-[4/5] rounded-[32px] overflow-hidden shadow-2xl">
<img alt="Our Origins 1994" class="w-full h-full object-cover" data-alt="A vintage black and white photograph of an early Ayurvedic apothecary from 1994, showing glass jars filled with botanical powders and a dedicated practitioner. The image is presented with a modern, high-contrast edit on a pristine white background, framed by elegant borders to signify trust and clinical legacy in the field of natural healthcare." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDOJxywUJa4m7ToXdIYhnmdpI8p8r-QX8ofgLauwPrOjWbOL_v7k_cRfkUsgU5kPbtsfxSxx7Y1u6xIuHuwHryR-vT-6dW-5Og0O5jcYwnKz6NYORP_o_kKQVyuxYfsLrOhs5LzoRwzOMotS1ZYgJ92V2hKCTrKhsXjRXFwoV8jrAUWXsDAOSSge13tDHwhCucpFQP09mNopS591DBGw0m4H3lF6x7jQyLi3eYr5hAHqpkyLhu5uGfSdBW7903VJeXJP4mM-yH-xBE"/>
</div>
<div class="absolute -bottom-8 -right-8 bg-primary p-8 rounded-2xl text-white shadow-xl hidden md:block">
<div class="text-[48px] font-bold leading-none">30+</div>
<div class="text-label-md opacity-80">Years of Clinical Excellence</div>
</div>
</div>
<div class="space-y-6 md:pl-12">
<span class="text-secondary font-label-md tracking-widest uppercase">Our Heritage</span>
<h2 class="font-headline-lg text-headline-lg text-primary">Born from a passion for holistic integrity in 1994.</h2>
<p class="text-on-surface-variant leading-relaxed">Ayurwellness began as a small research clinic dedicated to validating the profound effects of traditional botanical medicine. Over three decades, we have evolved into a leading authority in Ayurvedic healthcare, maintaining the same rigor and respect for nature that guided our first formulations.</p>
<p class="text-on-surface-variant leading-relaxed">Today, we operate a vertically integrated ecosystem—from organic herb cultivation to state-of-the-art clinical testing—ensuring that every bottle carries the promise of purity and potency.</p>
</div>
</div>
</section>

<!-- 3. Mission & Vision -->
<section class="bg-surface-container-low py-section-gap px-gutter">
<div class="max-w-container-max mx-auto">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg text-primary mb-4">Values that Root Our Practice</h2>
<p class="text-on-surface-variant max-w-2xl mx-auto">Our principles are the bedrock of every consultation and product we deliver to our global community.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<div class="bg-white p-10 rounded-[24px] border border-outline-variant shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 bg-primary-container rounded-xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary-fixed">clinical_notes</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-3">Our Mission</h3>
<p class="text-on-surface-variant">To empower individuals through personalized Ayurvedic protocols backed by modern clinical research and ethical sourcing.</p>
</div>
<div class="bg-white p-10 rounded-[24px] border border-outline-variant shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 bg-primary-container rounded-xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary-fixed">visibility</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-3">Our Vision</h3>
<p class="text-on-surface-variant">To be the global gold standard for integrative botanical healthcare, where tradition and science coexist for human longevity.</p>
</div>
<div class="bg-white p-10 rounded-[24px] border border-outline-variant shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 bg-primary-container rounded-xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary-fixed">eco</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-3">Core Integrity</h3>
<p class="text-on-surface-variant">Uncompromising commitment to organic purity, sustainable harvesting, and complete transparency in our manufacturing process.</p>
</div>
</div>
</div>
</section>

<!-- 4. Team -->
<section class="py-section-gap px-gutter max-w-container-max mx-auto">
<div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
<div class="max-w-xl">
<h2 class="font-headline-lg text-headline-lg text-primary mb-4">Leadership &amp; Medical Board</h2>
<p class="text-on-surface-variant">Guided by a diverse team of Ayurvedic scholars, modern pharmacologists, and clinical specialists.</p>
</div>
<a class="border border-primary text-primary px-8 py-3 rounded-full font-label-md hover:bg-primary hover:text-white transition-all inline-block" href="<?= BASE_URL ?>/doctor-listing.php">View All Members</a>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
<div class="group">
<div class="aspect-[3/4] rounded-2xl overflow-hidden mb-6 relative">
<img alt="Dr. Aruna Varma" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="A professional portrait of a senior medical director in a minimalist modern clinical setting. The doctor is wearing a clean white coat with a sophisticated botanical lapel pin. The lighting is bright and warm, conveying a sense of authoritative kindness and medical precision. The overall style is editorial and premium." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAFCE4gzH34usGEOY5haA4gcrrBp-LrMKWKLsNGPuvRy9UxEbEKt1Rp2jipDFZD6vOrnCtp98h7je1lq-q12jhJeq7_AZPod48u5drp3MJZPuWWQvJ2G6b8YUB61QhErDSDV_ag3ZARAH23_9d4EHqgCS4lHBNjLGcGlM6HjTBIn2d4GQD7ef_f-jj52Egl5PPBomZVUAuxCUAbMewJeb6BcbgogK4FikLwdFe5Bhtj7dBGrqAEJ9gTu58dIDp00K_i9BEPp3-e5hg"/>
<div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
<div class="flex gap-4">
<span class="material-symbols-outlined text-white cursor-pointer">share</span>
</div>
</div>
</div>
<h4 class="font-headline-md text-headline-md text-primary">Dr. Aruna Varma</h4>
<p class="text-label-md text-secondary uppercase">Chief Medical Officer</p>
</div>
<div class="group">
<div class="aspect-[3/4] rounded-2xl overflow-hidden mb-6 relative">
<img alt="Dr. Vikram Singh" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="A portrait of a male Ayurvedic scholar and researcher in a high-tech laboratory filled with glass flasks and botanical extracts. He is dressed in professional clinical attire, exuding confidence and expertise. The lighting emphasizes the intersection of nature and science, with soft focus on the botanical elements in the background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuChFH-ZSRrwYAPI7mjj5mHwayheMWyfe5fuuRLJFhggrhdkVvpBcNREzqkejletVivDipV6EDkLn3QYuLO_nX8lHsXz97ZHgjdkwRFAw_nYl6dT14sHYCwYOdy2ododQCNXQqrU1gnaJB2GACA5mZDyBghgPtjpQos_GGnzIZ5-9IUAScp_nBVqX9hOTGkGYxGvdbs6kRR4pbC-e5kfm_R6_inAPIjAoyeIDkponDwRNlpAxJ8lhMXzZ2_6zR0RghorggZlGgVkXbU"/>
<div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
<div class="flex gap-4">
<span class="material-symbols-outlined text-white cursor-pointer">share</span>
</div>
</div>
</div>
<h4 class="font-headline-md text-headline-md text-primary">Dr. Vikram Singh</h4>
<p class="text-label-md text-secondary uppercase">Head of Research</p>
</div>
<div class="group">
<div class="aspect-[3/4] rounded-2xl overflow-hidden mb-6 relative">
<img alt="Meera Kapoor" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="A portrait of a female pharmaceutical operations lead in a sleek, modern corporate office environment. The background shows soft-focus glass partitions and botanical plants. She has a composed, professional expression. The aesthetic is clean and high-contrast, representing modern operational excellence." src="https://lh3.googleusercontent.com/aida-public/AB6AXuB1I3vw-DPUdQ3m9EmJnSJziz25-JJnU73TVGF5Iyg1hoHCxYd3UlGDA9DiI0Dn6-iyZEhbiOvqWknctlPA9k2Nd87uaR5AvIxLn_CN91EYxRB40iKMLI8ArlQhmkoDGeTSLqxk_MoCWSU4da1BptLhj0iTvFyWghpuTIbGaIyrFbVzwqxB8mL9kKOMDXTqCZqiv0McI4ga0oKGhPkoE_z_ZRxrhQBKfYH5nLbaQ42zbj8xG8OZp3DfkJEdD5xqcZcpi9pUX17f8dg"/>
<div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
<div class="flex gap-4">
<span class="material-symbols-outlined text-white cursor-pointer">share</span>
</div>
</div>
</div>
<h4 class="font-headline-md text-headline-md text-primary">Meera Kapoor</h4>
<p class="text-label-md text-secondary uppercase">Operations Director</p>
</div>
<div class="group">
<div class="aspect-[3/4] rounded-2xl overflow-hidden mb-6 relative">
<img alt="Dr. Rajesh Iyer" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="A portrait of a senior Ayurvedic practitioner and board member. The setting is a serene, traditionally-inspired healthcare suite with wood paneling and soft warm lighting. He has a warm, welcoming presence. The style reflects a deep respect for heritage and patient-focused wellness." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDMuzg0E0wfk8rHLVRZnLREoYizqoUbSh8y6iUMLqzka71ANM7aqprsCe_HC8m0j__7EdwCWJgBlZD2PXykWFFhrzypCmuWUkoZ2BgkkX-FwN1VrDNV0e4n4Le5Wa5Lpyem1O5lJcC4AtcSeGNe95mnYex2EU4lnboYqMkh3ULBCOCcGVMREyXWDkz433ZRDgpuu5lEZ78D1csIASudJvCW02wxg_zlrOj_d7RJP9zPPgTR186KXUUjbLLm_bkB9_8qY0dL7kt5cVw"/>
<div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
<div class="flex gap-4">
<span class="material-symbols-outlined text-white cursor-pointer">share</span>
</div>
</div>
</div>
<h4 class="font-headline-md text-headline-md text-primary">Dr. Rajesh Iyer</h4>
<p class="text-label-md text-secondary uppercase">Senior Board Member</p>
</div>
</div>
</section>

<!-- 5. Certifications -->
<section class="bg-primary py-16">
<div class="px-gutter max-w-container-max mx-auto">
<div class="flex flex-wrap justify-center items-center gap-12 md:gap-24 opacity-60 hover:opacity-100 transition-opacity">
<div class="flex flex-col items-center gap-2">
<span class="material-symbols-outlined text-white text-[48px]">verified</span>
<span class="text-white font-label-md">ISO 9001:2015</span>
</div>
<div class="flex flex-col items-center gap-2">
<span class="material-symbols-outlined text-white text-[48px]">health_and_safety</span>
<span class="text-white font-label-md">WHO GMP Certified</span>
</div>
<div class="flex flex-col items-center gap-2">
<span class="material-symbols-outlined text-white text-[48px]">nature</span>
<span class="text-white font-label-md">USDA Organic</span>
</div>
<div class="flex flex-col items-center gap-2">
<span class="material-symbols-outlined text-white text-[48px]">science</span>
<span class="text-white font-label-md">NABL Accredited</span>
</div>
</div>
</div>
</section>

<!-- 6. Manufacturing Process -->
<section class="py-section-gap px-gutter max-w-container-max mx-auto">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg text-primary mb-4">The Alchemy of Excellence</h2>
<p class="text-on-surface-variant max-w-2xl mx-auto">From the forest floor to your doorstep, our 4-step process ensures unrivaled quality control.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
<div class="hidden md:block absolute top-1/2 left-0 w-full h-[1px] bg-outline-variant -z-10"></div>
<div class="bg-white p-8 rounded-[32px] border border-outline-variant flex flex-col items-center text-center group hover:border-primary transition-colors">
<div class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mb-6 border border-outline-variant group-hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-white">potted_plant</span>
</div>
<h4 class="font-headline-md text-headline-md text-primary mb-2">Source</h4>
<p class="text-body-md text-on-surface-variant">Sustainably wild-harvested from certified organic forest beds.</p>
</div>
<div class="bg-white p-8 rounded-[32px] border border-outline-variant flex flex-col items-center text-center group hover:border-primary transition-colors">
<div class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mb-6 border border-outline-variant group-hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-white">biotech</span>
</div>
<h4 class="font-headline-md text-headline-md text-primary mb-2">Extract</h4>
<p class="text-body-md text-on-surface-variant">Cold-press and CO2 extraction to preserve vital nutrients.</p>
</div>
<div class="bg-white p-8 rounded-[32px] border border-outline-variant flex flex-col items-center text-center group hover:border-primary transition-colors">
<div class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mb-6 border border-outline-variant group-hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-white">microscope</span>
</div>
<h4 class="font-headline-md text-headline-md text-primary mb-2">Test</h4>
<p class="text-body-md text-on-surface-variant">Third-party lab verification for purity and active compounds.</p>
</div>
<div class="bg-white p-8 rounded-[32px] border border-outline-variant flex flex-col items-center text-center group hover:border-primary transition-colors">
<div class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mb-6 border border-outline-variant group-hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-white">inventory_2</span>
</div>
<h4 class="font-headline-md text-headline-md text-primary mb-2">Package</h4>
<p class="text-body-md text-on-surface-variant">Miron violet glass to protect potency from light degradation.</p>
</div>
</div>
</section>

<!-- CTA Section -->
<section class="mb-section-gap px-gutter max-w-container-max mx-auto">
<div class="bg-primary-container rounded-[40px] p-12 md:p-24 text-center relative overflow-hidden">
<div class="absolute inset-0 opacity-10">
<div class="absolute top-0 right-0 w-96 h-96 bg-primary-fixed rounded-full blur-[100px]"></div>
<div class="absolute bottom-0 left-0 w-96 h-96 bg-tertiary-fixed rounded-full blur-[100px]"></div>
</div>
<div class="relative z-10">
<h2 class="font-display-lg text-display-lg text-white mb-6">Ready to start your wellness journey?</h2>
<p class="text-body-lg text-white/80 mb-10 max-w-xl mx-auto">Our specialists are here to guide you toward a balanced life with personalized care.</p>
<a class="bg-secondary-container text-on-secondary-container px-12 py-5 rounded-full font-headline-md hover:scale-105 transition-transform shadow-lg inline-block" href="<?= BASE_URL ?>/appointment-booking.php">Book Your Consultation</a>
</div>
</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
