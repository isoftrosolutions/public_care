<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .herbal-gradient {
        background: linear-gradient(135deg, rgba(0, 82, 33, 0.05) 0%, rgba(244, 249, 241, 1) 100%);
                    }
</style>

<!-- Hero Section -->
<section class="relative w-full h-[716px] min-h-[500px] flex items-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img alt="Ayurvedic Heritage" class="w-full h-full object-cover brightness-[0.75]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCC58D1ovvqUINGsOwx8IAkH7FI4GIq-8jdMZP3JqY0ou9Z0JZzfMYImddK_c8l6kK-DM0hXW_UG20wAYuLOlty9jO0XtSiYGTW3fRf5zFs9rx5BpZbCouIbq87TeId3n_dotiqawnSl5ZIzMaxHtgUbDPRdu3BPDAoVcUybB5vtaHi5tL9GIicZhi2OeD4SFYU7QLrYTROtnBzv5yFZc9_ICE5RpWs66ALUO-Qv4L6L06666G394_eIAku-tttdwGYw64WXJWfmoU" />
    </div>
    <div class="relative z-10 w-full px-base md:px-margin-desktop max-w-container-max mx-auto text-white">
        <div class="max-w-3xl">
            <h1 class="font-display-lg text-display-lg md:text-[64px] leading-tight mb-6">Our Roots in Nature, Our Heart in Wellness.</h1>
            <p class="font-body-lg text-body-lg md:text-title-lg text-white/90 leading-relaxed mb-8">The story of how <?= SITE_NAME ?> is bridging ancient Ayurvedic wisdom with modern wellness needs.</p>
        </div>
    </div>
</section>

<!-- The Heritage Story -->
<section class="py-20 bg-background">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
        <div class="order-2 md:order-1">
            <h2 class="font-display-lg text-headline-lg text-primary mb-6">A Legacy of Healing</h2>
            <div class="space-y-4 font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
                <p>Our journey began in the lush valley of Kerala, where the rhythm of life is dictated by the seasons and the soil. For generations, our founders studied the sacred texts of Ayurveda, learning the delicate art of balancing the three doshas—Vata, Pitta, and Kapha.</p>
                <p><?= SITE_NAME ?> was born from a vision to take this time-honored expertise out of the local village and into the digital age. We believed that the path to true health shouldn't be a trade-off between tradition and convenience.</p>
                <p>Today, we serve as a bridge, preserving the authenticity of traditional herbal extraction while utilizing modern quality control and clinical insights to ensure every drop and capsule meets the highest standards of safety and efficacy.</p>
            </div>
        </div>
        <div class="order-1 md:order-2 rounded-2xl overflow-hidden shadow-xl">
            <img alt="Heritage Tradition" class="w-full h-[500px] object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZuyaTvDIEG8YHKy4Ji57cS2Fb-hsBaKcu4ALDICXuYwVVHl8z6SQGPw4foDZHcM19PWDtpRfHup57ORYG2lKc0rwSemcbLYov7_xX62bsLDHFnVWug2L4ddvDg25VBYxyojh8Wx--J3xFQE7TE6dj9m2a7RfChvnSlNKjBabOKMW0q4eZnon8yFULL2_oMg-nlfD1U5YIiC0rYfKQJF5w6LCj3YWJNnQy7GPprRafJFwJ4AlvGHTEPItHk7gSN0roRy2cYvLYrao" />
        </div>
    </div>
</section>

<!-- Our Mission & Vision -->
<section class="py-20 bg-secondary-container/30">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            <div class="bg-surface-container-lowest p-12 rounded-xl shadow-sm border border-outline-variant/30 flex flex-col justify-center text-center">
                <span class="material-symbols-outlined text-primary text-5xl mb-6">eco</span>
                <h3 class="font-headline-lg text-headline-lg text-primary mb-4">Our Mission</h3>
                <p class="font-body-lg text-body-lg text-on-surface-variant italic">"To empower every individual with natural, balanced, and healthy living through authentic Ayurveda."</p>
            </div>
            <div class="bg-primary p-12 rounded-xl shadow-lg flex flex-col justify-center text-center text-white">
                <span class="material-symbols-outlined text-primary-fixed text-5xl mb-6">visibility</span>
                <h3 class="font-headline-lg text-headline-lg text-primary-fixed mb-4">Our Vision</h3>
                <p class="font-body-lg text-body-lg text-white/90 italic">"To be the world's most trusted destination for holistic wellness and herbal healing."</p>
            </div>
        </div>
    </div>
</section>

<!-- The Pillars -->
<section class="py-24 bg-white">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop text-center mb-16">
        <h2 class="font-display-lg text-headline-lg text-primary mb-4">The Pillars of <?= SITE_NAME ?></h2>
        <div class="w-24 h-1 bg-tertiary-fixed-dim mx-auto rounded-full"></div>
    </div>
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop grid grid-cols-1 md:grid-cols-3 gap-12">
        <div class="group flex flex-col items-center text-center p-8 hover:bg-surface-container-low transition-all duration-300 rounded-2xl">
            <div class="w-20 h-20 flex items-center justify-center bg-primary-container/10 rounded-full mb-6 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-primary text-4xl">verified</span>
            </div>
            <h4 class="font-title-lg text-title-lg text-primary mb-3">Authenticity</h4>
            <p class="font-body-md text-body-md text-on-surface-variant">Sourcing the purest herbs from organic farms, ensuring every ingredient is harvested at its peak medicinal potency.</p>
        </div>
        <div class="group flex flex-col items-center text-center p-8 hover:bg-surface-container-low transition-all duration-300 rounded-2xl">
            <div class="w-20 h-20 flex items-center justify-center bg-primary-container/10 rounded-full mb-6 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-primary text-4xl">medical_services</span>
            </div>
            <h4 class="font-title-lg text-title-lg text-primary mb-3">Expertise</h4>
            <p class="font-body-md text-body-md text-on-surface-variant">Guided by certified Ayurvedic doctors and practitioners who ensure ancient formulas are applied with modern wisdom.</p>
        </div>
        <div class="group flex flex-col items-center text-center p-8 hover:bg-surface-container-low transition-all duration-300 rounded-2xl">
            <div class="w-20 h-20 flex items-center justify-center bg-primary-container/10 rounded-full mb-6 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-primary text-4xl">biotech</span>
            </div>
            <h4 class="font-title-lg text-title-lg text-primary mb-3">Innovation</h4>
            <p class="font-body-md text-body-md text-on-surface-variant">Modern scientific rigor applied to ancient formulas to create bio-available, effective, and safe wellness solutions.</p>
        </div>
    </div>
</section>

<!-- Meet Our Team -->
<section class="py-20 bg-background overflow-hidden">
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div>
                <h2 class="font-display-lg text-headline-lg text-primary mb-2">Meet Our Experts</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant">A panel of world-class practitioners dedicated to your health.</p>
            </div>
            <a class="text-primary font-label-lg hover:underline flex items-center gap-2" href="<?= BASE_URL ?>/doctor-listing.php">View All Experts <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <img alt="Dr. Vaidya Anand" class="w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBfQub61zTAsRw661ad8_jdHEzA-S6ribEQQENYPpn5WKSMfKHeiCmryGOhyviRhT2mB-QJdxDFEErGdwQDhsp50V17ZUVZBVi6zGIaQLzLpid-4793q7OAq6IigrRPyT0e9JGp1cD7P-uRRZR--ARyP26jFIRubOUtTdrYIudPZvhxjVyIcKQzDL4wX4hN4dwLPRuC8njaCM3Pw5YdlMeqRrPzg369TtBCShcgAfe_0uxzWPWgHHHbriN7vAU3zQNh0zqgUB6ohIg" />
                <div class="p-6 text-center">
                    <h5 class="font-title-lg text-primary mb-1">Dr. Vaidya Anand</h5>
                    <p class="font-label-sm text-secondary mb-4 uppercase tracking-wider">Chief Consultant, BAMS</p>
                    <p class="text-body-md text-on-surface-variant line-clamp-2">20+ years of experience in classical Ayurvedic internal medicine.</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <img alt="Dr. Priyanka Sharma" class="w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuiW5XmOvBQQJgR4NrukF2_uayvrKfQFArb2HJvK9B854qrEl11se0Ft3mPauuxUAK1juZFzeSfFl3X9fhwdnyvmzcRsAvOUrf5Tfy8WeSYVATK4xAcg44oH42VjXLv8yn-Is_eRp13QSutdCSZdCby1l2qvuXndzk3_MQ0fz4znWMJuU8eFn6UzGuOJILG71nxBNYKN5AYbvgEa6l7-czFYDFJxwu3HRFkTJyIq2N149VKkYEuquIPsKT4QoUpNxMJf7iI8i9C00" />
                <div class="p-6 text-center">
                    <h5 class="font-title-lg text-primary mb-1">Dr. Priyanka Sharma</h5>
                    <p class="font-label-sm text-secondary mb-4 uppercase tracking-wider">Herbal Pharmacist, MD</p>
                    <p class="text-body-md text-on-surface-variant line-clamp-2">Specializing in botanical formulations and modern safety protocols.</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <img alt="Acharya Rishi" class="w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD4mhyIyFEri6_gkGU02Dj0K2dmZFDmbBPaUJFbxfMh-CWSI1LXbE7qQwVZrqTEAovSJjaX5dHjEuXYFHmF3ehyjjpTZDsMpH3_iLEgFPaSlH7emSBSmmSHIcrwuVu9z1_9Unt4_ESR92cKQGWksrJs5soohdIBznj5GZfuLc-MIBf0vP_8xpjWMhbMUp-Ni0cdbKgSIiYASDSd695OBI5CGwP_RKFujbf9zNL_PxMwDoX9ACz0kNBFALkCS9ae1_Mq8DGFyvWHjLE" />
                <div class="p-6 text-center">
                    <h5 class="font-title-lg text-primary mb-1">Acharya Rishi</h5>
                    <p class="font-label-sm text-secondary mb-4 uppercase tracking-wider">Lifestyle Coach</p>
                    <p class="text-body-md text-on-surface-variant line-clamp-2">Expert in Yogic practices and holistic daily wellness routines.</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <img alt="Dr. Meera Iyer" class="w-full h-64 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDX2blHF08sMRdZuQhk-xn7XZSV3XPQyjn6KnD6s0LUYKiwh40HDueaAzsKQIbxaPNHmCgWFDIyYp6AP2ciTt5EUWZ8fvk4Dc6vQY_d3PqrMiFO-PDOzoihSHY1C-8B91Cs_R8AZ5fQf-_W6Tu9IigPgfhDmKOQfW8MBi4ehm3sFOrdRryxu9YpPWLoHj94o5_83kZoaiagPjLlxJ2w8eP0gaETyJmckWCToUgE6I5HBz1TSe8h16Z6aay1uf-6NiRwa9TI5MDOfnY" />
                <div class="p-6 text-center">
                    <h5 class="font-title-lg text-primary mb-1">Dr. Meera Iyer</h5>
                    <p class="font-label-sm text-secondary mb-4 uppercase tracking-wider">Clinical Researcher</p>
                    <p class="text-body-md text-on-surface-variant line-clamp-2">Focused on bridging clinical research with traditional Ayurvedic remedies.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sustainability & Community -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 z-0 bg-secondary-container/10"></div>
    <div class="max-w-container-max mx-auto px-base md:px-margin-desktop relative z-10 flex flex-col md:flex-row items-center gap-16">
        <div class="flex-1">
            <img alt="Organic Farming" class="w-full h-[400px] object-cover rounded-3xl shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBHTywBIs-LhGLV0SrX7XRH1Dhncus3suR9iYAFZT1EIaGfyQnpcp2wGIDWpVQMi72BOAyCXlYCaoN4Vntwlo941bcO0Eb-QPraqG7yva-s_bKDQmKe1UY_2vtaIf4Ub-O0zaCzznp_NwA4QZLwZOSmCdctVaU-56V-kObtpSUvsd7dN11X5-kSuZbgCf8T4NuyGkgIaAc9efuLsJUCFsDd0HZnI_PWTtgLbyrnHK94q89ExppMvCdxRCADAq_ryI-8yiYt5hTCYag" />
        </div>
        <div class="flex-1 space-y-6">
            <h2 class="font-display-lg text-headline-lg text-primary">Nurturing Earth & Community</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Our commitment extends beyond your health. We believe in the health of the planet and the prosperity of those who tend it.</p>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <span class="material-symbols-outlined text-primary bg-primary-fixed p-2 rounded-lg h-fit">handshake</span>
                    <div>
                        <h6 class="font-title-lg text-primary mb-1">Fair Trade Practices</h6>
                        <p class="text-body-md text-on-surface-variant">We partner directly with small-scale farmers, ensuring they receive fair wages and technical support for organic cultivation.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <span class="material-symbols-outlined text-primary bg-primary-fixed p-2 rounded-lg h-fit">compost</span>
                    <div>
                        <h6 class="font-title-lg text-primary mb-1">Zero Waste Packaging</h6>
                        <p class="text-body-md text-on-surface-variant">Reducing our footprint with eco-friendly, recyclable, and biodegradable packaging for all our wellness products.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-24 text-center px-base">
    <div class="max-w-3xl mx-auto bg-primary py-16 px-8 rounded-[40px] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-primary-fixed opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-primary-fixed opacity-10 rounded-full blur-3xl"></div>
        <h2 class="font-display-lg text-headline-lg md:text-[40px] text-white mb-6 relative z-10">Ready to transform your lifestyle?</h2>
        <p class="font-body-lg text-body-lg text-primary-fixed mb-10 relative z-10">Discover personalized wellness solutions tailored to your unique body constitution.</p>
        <a class="inline-flex items-center justify-center px-10 py-4 bg-white text-primary font-title-lg rounded-full hover:bg-primary-fixed-dim hover:text-on-primary-fixed transition-all active:scale-95 shadow-lg relative z-10" href="<?= BASE_URL ?>/appointment-booking.php">
            Start Your Wellness Journey
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 