<?php
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email && $password) {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] === 'admin') {
                    $redirect = BASE_URL . '/admin/dashboard.php';
                } else {
                    $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php';
                }
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Please fill in all fields.';
        }
    }
}

$site_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<main class="min-h-screen flex items-center justify-center p-4 md:p-margin-desktop overflow-hidden relative">
<div class="absolute inset-0 z-0">
<div class="absolute inset-0 gradient-overlay mix-blend-multiply"></div>
<img class="w-full h-full object-cover" alt="Serene Ayurvedic treatment room" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCYNLz-cizpLDWesgjA8jZFNPsH8OdjxdidAwZ4tUPXJUhC0UGzmo-wARMsRC55timJlsIW9nDZ-RI3zqMIYA_cKuuQkPVJbyRohhBc8jqPRpker0dYwxyKwHAndTHFimCz1AmRwNT0urkd3_wg-oxlcDl7fmExFsO6edEUdyE3KxxEF2UipmD6jTSVGXNUcE8TySyyRHZi5myS_J509zhJR2DjXvC1Ai7bwEGU6uYEmvSGC8DNA2y4NywRdFjNEPcixmPnqJyNQzw"/>
</div>
<div class="relative z-10 w-full max-w-[1100px] flex flex-col md:flex-row bg-surface-container-lowest rounded-xl overflow-hidden login-card">
<div class="hidden md:flex md:w-5/12 bg-primary-container p-12 flex-col justify-between items-start">
<div>
<img alt="Public Care Ayurveda Logo" class="h-20 w-auto mb-8 grayscale invert brightness-200" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAgVQC_h4DCP40UtuYrlJ03BVhpay5bSt3cpc4bPCDRl-ZVkDBnj_z3Q33ioLX_zHcOsZjhv8vzInXaeRF2kZqEnIWAb7dixDOHTvOa7cxOxJF9ORm9O087kv7k55AJ4B1Ovlk3ljZpw0oOLh3i7XXZaXOBoG9u63PWz1diuVZe4hZT3d6kUCNhmZvGczSxP4uf2tSMtuGfxnsnghKUXNUvEi2Ooi-wo9wdyQpdI-2MYf3Ns3nTRFDyFvJQn8bktqAwbi2iAgbUfPQ"/>
<h1 class="font-headline-lg text-headline-lg text-primary-fixed leading-tight mb-4">Ancient Wisdom,<br/>Modern Precision.</h1>
<p class="font-body-md text-body-md text-on-primary-container max-w-xs">
Access your personalized Ayurvedic healthcare journey through our clinical portal. Secure, private, and professional.
</p>
</div>
<div class="space-y-6">
<div class="flex items-center gap-4 group">
<div class="w-10 h-10 rounded-full bg-tertiary-container flex items-center justify-center text-primary-fixed transition-transform group-hover:scale-110">
<span class="material-symbols-outlined">verified_user</span>
</div>
<div>
<p class="font-label-md text-label-md text-primary-fixed">Clinical Security</p>
<p class="text-xs text-on-primary-container opacity-80">256-bit AES Encryption</p>
</div>
</div>
<div class="flex items-center gap-4 group">
<div class="w-10 h-10 rounded-full bg-tertiary-container flex items-center justify-center text-primary-fixed transition-transform group-hover:scale-110">
<span class="material-symbols-outlined">eco</span>
</div>
<div>
<p class="font-label-md text-label-md text-primary-fixed">Botanical Heritage</p>
<p class="text-xs text-on-primary-container opacity-80">Pure Traditional Ethics</p>
</div>
</div>
</div>
</div>
<div class="w-full md:w-7/12 p-8 md:p-16 flex flex-col">
<div class="md:hidden mb-8">
<img alt="Public Care Ayurveda Logo" class="h-12 w-auto" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAgVQC_h4DCP40UtuYrlJ03BVhpay5bSt3cpc4bPCDRl-ZVkDBnj_z3Q33ioLX_zHcOsZjhv8vzInXaeRF2kZqEnIWAb7dixDOHTvOa7cxOxJF9ORm9O087kv7k55AJ4B1Ovlk3ljZpw0oOLh3i7XXZaXOBoG9u63PWz1diuVZe4hZT3d6kUCNhmZvGczSxP4uf2tSMtuGfxnsnghKUXNUvEi2Ooi-wo9wdyQpdI-2MYf3Ns3nTRFDyFvJQn8bktqAwbi2iAgbUfPQ"/>
</div>
<div class="mb-8">
<h2 class="font-headline-lg text-headline-lg text-primary mb-2">Welcome Back</h2>
<p class="text-on-surface-variant font-body-md">Please enter your credentials to access your account.</p>
</div>
<?php if ($error): ?>
<div class="mb-6 p-4 rounded-lg bg-error-container border border-error text-on-error-container font-label-md">
<span class="material-symbols-outlined text-sm align-middle mr-2">error</span><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<div class="flex border-b border-outline-variant mb-8">
<button class="pb-3 px-4 font-label-md text-label-md border-b-2 border-primary text-primary transition-all" id="tab-email" onclick="switchTab('email')">Email Login</button>
<button class="pb-3 px-4 font-label-md text-label-md border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-all" id="tab-phone" onclick="switchTab('phone')">Phone / OTP</button>
</div>
<div class="flex-grow">
<form class="space-y-5" id="form-email" method="POST" action="">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<div>
<label class="block font-label-md text-label-md text-on-surface mb-2" for="email">Email Address</label>
<input class="w-full px-4 py-3 rounded-lg border border-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline-variant" id="email" name="email" placeholder="dr.sharma@example.com" type="email" required/>
</div>
<div>
<div class="flex justify-between mb-2">
<label class="font-label-md text-label-md text-on-surface" for="password">Password</label>
<a class="text-xs font-label-sm text-primary hover:underline" href="#">Forgot Password?</a>
</div>
<div class="relative">
<input class="w-full px-4 py-3 rounded-lg border border-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline-variant" id="password" name="password" placeholder="••••••••" type="password" required/>
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary" type="button" onclick="togglePassword('password')">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
<button class="w-full bg-primary text-on-primary py-4 rounded-lg font-label-md text-label-md hover:bg-primary-container transition-all shadow-md active:scale-[0.98]" type="submit">Login to Portal</button>
</form>
<form class="hidden space-y-5" id="form-phone">
<div id="phone-input-section">
<label class="block font-label-md text-label-md text-on-surface mb-2" for="phone">Phone Number</label>
<div class="flex gap-2">
<div class="px-3 py-3 rounded-lg border border-outline bg-surface-container-low text-on-surface font-label-md flex items-center">+91</div>
<input class="flex-grow px-4 py-3 rounded-lg border border-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline-variant" id="phone" placeholder="98765 43210" type="tel"/>
</div>
</div>
<div class="hidden" id="otp-section">
<div class="flex justify-between items-center mb-2">
<label class="font-label-md text-label-md text-on-surface">Enter 6-Digit OTP</label>
<span class="text-xs text-error font-label-sm" id="timer">Resend in 0:59</span>
</div>
<div class="flex justify-between gap-2">
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
<input class="otp-input w-12 h-14 text-center border border-outline rounded-lg text-headline-md font-headline-md" maxlength="1" type="text"/>
</div>
</div>
<button class="w-full bg-primary text-on-primary py-4 rounded-lg font-label-md text-label-md hover:bg-primary-container transition-all shadow-md active:scale-[0.98]" id="phone-action-btn" onclick="sendOTP()" type="button">Send OTP Code</button>
</form>
</div>
<div class="mt-8">
<div class="relative flex items-center justify-center mb-8">
<div class="flex-grow border-t border-outline-variant"></div>
<span class="mx-4 text-xs font-label-sm text-outline uppercase tracking-widest">or continue with</span>
<div class="flex-grow border-t border-outline-variant"></div>
</div>
<button class="w-full flex items-center justify-center gap-3 bg-white border border-outline-variant py-3 rounded-lg hover:bg-surface-container-low transition-colors group">
<svg class="w-5 h-5" viewbox="0 0 24 24">
<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
</svg>
<span class="font-label-md text-label-md text-on-surface">Continue with Google</span>
</button>
<p class="mt-8 text-center text-body-md">
<span class="text-on-surface-variant">Don't have an account?</span>
<a class="text-primary font-bold hover:underline ml-1" href="<?= BASE_URL ?>/register.php">Sign Up</a>
</p>
</div>
<div class="mt-auto pt-8 flex justify-center items-center gap-8 border-t border-surface-variant opacity-60">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-sm">lock</span>
<span class="text-[10px] font-label-sm uppercase tracking-wider">Secure SSL</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-sm">privacy_tip</span>
<span class="text-[10px] font-label-sm uppercase tracking-wider">HIPAA Compliant</span>
</div>
</div>
</div>
</div>
</main>
<style>
.login-card { box-shadow: 0 12px 40px rgba(27, 67, 50, 0.08); }
.gradient-overlay { background: linear-gradient(135deg, rgba(27, 67, 50, 0.85) 0%, rgba(1, 45, 29, 0.6) 100%); }
.otp-input:focus { border-color: #1b4332; box-shadow: 0 0 0 2px rgba(27, 67, 50, 0.1); }
</style>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
function switchTab(type) {
    const emailForm = document.getElementById('form-email');
    const phoneForm = document.getElementById('form-phone');
    const emailTab = document.getElementById('tab-email');
    const phoneTab = document.getElementById('tab-phone');
    if (type === 'email') {
        emailForm.classList.remove('hidden');
        phoneForm.classList.add('hidden');
        emailTab.classList.replace('border-transparent', 'border-primary');
        emailTab.classList.replace('text-on-surface-variant', 'text-primary');
        phoneTab.classList.replace('border-primary', 'border-transparent');
        phoneTab.classList.replace('text-primary', 'text-on-surface-variant');
    } else {
        phoneForm.classList.remove('hidden');
        emailForm.classList.add('hidden');
        phoneTab.classList.replace('border-transparent', 'border-primary');
        phoneTab.classList.replace('text-on-surface-variant', 'text-primary');
        emailTab.classList.replace('border-primary', 'border-transparent');
        emailTab.classList.replace('text-primary', 'text-on-surface-variant');
    }
}
function sendOTP() {
    const phoneSection = document.getElementById('phone-input-section');
    const otpSection = document.getElementById('otp-section');
    const btn = document.getElementById('phone-action-btn');
    phoneSection.classList.add('opacity-40', 'pointer-events-none');
    otpSection.classList.remove('hidden');
    btn.innerText = 'Verify & Login';
    document.querySelector('.otp-input').focus();
}
const otpInputs = document.querySelectorAll('.otp-input');
otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        if (e.target.value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
