<?php
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/auth/login.php';
}

$site_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<main class="min-h-[calc(100vh-72px)] pt-[72px] flex flex-col md:flex-row">
  <section class="hidden md:flex md:w-1/2 bg-background hero-gradient relative items-center justify-center p-margin-desktop overflow-hidden border-r border-surface-container">
    <div class="max-w-xl relative z-10">
      <div class="mb-12">
        <img alt="Ayurwellness Logo" class="h-16 w-auto object-contain mb-4" src="<?= BASE_URL ?>/assets/uploads/logo.jpeg"/>
        <p class="text-primary font-bold tracking-widest text-label-sm uppercase">Natural &bull; Balanced &bull; Healthy</p>
      </div>
      <h1 class="font-display-lg text-display-lg leading-[1.1] text-primary mb-6">
        Your Wellness Journey <br/>Starts Here
      </h1>
      <p class="text-body-lg text-secondary mb-12 leading-relaxed max-w-lg">
        Access trusted Ayurvedic consultations, personalized wellness plans, natural remedies, and premium healthcare products tailored to your unique dosha.
      </p>
      <div class="relative w-full aspect-square max-w-sm mx-auto floating-element">
        <div class="absolute inset-0 bg-primary/5 rounded-full blur-3xl scale-125"></div>
        <div class="w-full h-full rounded-2xl bg-primary-container/30 border border-white/50 relative z-20 flex items-center justify-center">
          <span class="material-symbols-outlined text-primary" style="font-size: 80px;">spa</span>
        </div>
        <div class="absolute -top-6 -right-6 bg-white p-4 rounded-xl shadow-lg border border-primary-container z-30">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">eco</span>
            <div>
              <p class="text-label-lg text-primary">100% Natural</p>
              <p class="text-[10px] text-secondary">Pure Herb Extracts</p>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-16 flex items-center gap-8 border-t border-primary/10 pt-8">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">verified_user</span>
          <span class="text-label-sm text-secondary">Secure Login</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">medical_services</span>
          <span class="text-label-sm text-secondary">Ayurvedic Experts</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">hub</span>
          <span class="text-label-sm text-secondary">Wellness Ecosystem</span>
        </div>
      </div>
    </div>
  </section>
  <section class="w-full md:w-1/2 flex items-center justify-center p-margin-mobile md:p-margin-desktop bg-surface">
    <div class="md:hidden fixed top-[72px] left-margin-mobile z-10">
      <img alt="Ayurwellness Logo" class="h-10 w-auto" src="<?= BASE_URL ?>/assets/uploads/logo.jpeg"/>
    </div>
    <div class="w-full max-w-md">
      <div class="md:hidden mb-8 mt-16">
        <h2 class="font-headline-lg text-primary mb-2">Welcome Back</h2>
        <p class="text-body-md text-secondary">Join the thousands who trust Ayurwellness for their holistic health journey.</p>
      </div>
      <div class="bg-white p-6 md:p-10 rounded-2xl md:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08)] md:border border-surface-container-low transition-all duration-300">
        <div class="hidden md:block mb-8">
          <h2 class="font-headline-md text-on-background mb-1">Welcome Back</h2>
          <p class="text-body-md text-secondary">Enter your credentials to access your portal</p>
        </div>
        <div class="mb-8">
          <div id="google-button" class="flex justify-center"></div>
        </div>
        <div class="relative flex items-center mb-8">
          <div class="flex-grow border-t border-surface-container"></div>
          <span class="flex-shrink mx-4 text-label-sm text-outline uppercase tracking-widest">or continue with email</span>
          <div class="flex-grow border-t border-surface-container"></div>
        </div>
        <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-xl bg-error-container border border-error text-on-error-container font-label-lg flex items-center gap-2">
          <span class="material-symbols-outlined text-sm">error</span>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>
        <form class="space-y-6" method="POST" action="<?= BASE_URL ?>/login.php">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div>
            <label class="block text-label-lg text-on-surface-variant mb-2" for="email">Email Address</label>
            <div class="relative group">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">person</span>
              <input class="w-full pl-12 pr-4 py-3.5 bg-surface-container-lowest border border-surface-container-high rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline/50" id="email" name="email" placeholder="dr.sharma@example.com" type="email" required/>
            </div>
          </div>
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-label-lg text-on-surface-variant" for="password">Password</label>
              <a class="text-label-lg text-primary hover:underline font-bold" href="#">Forgot Password?</a>
            </div>
            <div class="relative group">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock</span>
              <input class="w-full pl-12 pr-12 py-3.5 bg-surface-container-lowest border border-surface-container-high rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline/50" id="password" name="password" placeholder="Enter your password" type="password" required/>
              <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors" type="button" onclick="togglePassword()">
                <span class="material-symbols-outlined" id="passwordIcon">visibility</span>
              </button>
            </div>
          </div>
          <div class="flex items-center">
            <input class="w-5 h-5 rounded border-surface-container-high text-primary focus:ring-primary cursor-pointer" id="remember" name="remember" type="checkbox"/>
            <label class="ml-3 text-body-md text-secondary cursor-pointer select-none" for="remember">Remember me for 30 days</label>
          </div>
          <button class="w-full bg-primary text-white py-4 px-6 rounded-xl font-bold text-headline-md shadow-lg shadow-primary/20 hover:bg-[#003d18] active:scale-[0.98] transition-all duration-200" type="submit">Log In</button>
        </form>
        <div class="mt-8 text-center">
          <p class="text-body-md text-secondary">
            Don't have an account?
            <a class="text-primary font-bold hover:underline" href="<?= BASE_URL ?>/register.php">Create Account</a>
          </p>
        </div>
      </div>
      <div class="mt-12 text-center md:hidden">
        <div class="flex flex-wrap justify-center gap-4 text-outline text-[12px] uppercase tracking-tighter">
          <span>Secure Login</span>
          <span class="text-primary-container">&bull;</span>
          <span>Expert Guided</span>
          <span class="text-primary-container">&bull;</span>
          <span>100% Natural</span>
        </div>
      </div>
    </div>
  </section>
</main>
<style>
.hero-gradient {
    background: radial-gradient(circle at 20% 30%, rgba(0, 82, 33, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(120, 88, 0, 0.03) 0%, transparent 50%);
}
.floating-element {
    animation: floating 6s ease-in-out infinite;
}
@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}
</style>
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.innerText = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        passwordIcon.innerText = 'visibility';
    }
}
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', () => {
        const group = input.closest('.group');
        if (group) group.classList.add('ring-2', 'ring-primary/10');
    });
    input.addEventListener('blur', () => {
        const group = input.closest('.group');
        if (group) group.classList.remove('ring-2', 'ring-primary/10');
    });
});
</script>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleGoogleCredential(response) {
    fetch('<?= BASE_URL ?>/auth/google-callback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Google sign-in failed. Please try again.');
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}
window.onload = function () {
    if (typeof google !== 'undefined') {
        google.accounts.id.initialize({
            client_id: '<?= defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '' ?>',
            callback: handleGoogleCredential
        });
        google.accounts.id.renderButton(
            document.getElementById('google-button'),
            { theme: 'outline', size: 'large', width: 320, text: 'signin_with', shape: 'pill' }
        );
    }
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
