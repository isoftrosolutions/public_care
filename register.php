<?php
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    } else {
    $form_data['full_name'] = trim($_POST['full_name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['mobile'] = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$form_data['full_name']) {
        $errors['full_name'] = 'Full name is required.';
    }
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    if (!$form_data['mobile']) {
        $errors['mobile'] = 'Mobile number is required.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, and a number.';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $form_data['email']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors['email'] = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->bind_param("ssss", $form_data['full_name'], $form_data['email'], $form_data['mobile'], $hashed);
            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $form_data['full_name'];
                $_SESSION['role'] = 'customer';
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }
    }
    }
}

$site_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<main class="min-h-screen pt-32 pb-section-gap botanical-bg flex items-center justify-center px-margin-mobile">
<div class="w-full max-w-[560px] bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant overflow-hidden">
<div class="relative h-48 w-full overflow-hidden">
<img alt="Ayurvedic herbs" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAemPAzmDlqmKy6Bm93VNmtaLbrp8k6S-hEjfbgEoryUb2-__wTdwfuiMaJvXBAwmefy_Qg-TwJvcneLU3Jh3junRTq7YePjwht9LV6d5DL9-XuQRJcc2ImctVMbhKV3tMU5weCkE7fUMqfB2RMFX2btXLph4iTFSDih0y7VVVe4JoHF6RWmXKhgA_Ue1NU7xd3kmnneUCEOMg8yKAKkepmr93_-PRDwj6aZrckqknupjiVzPtUB8DAByfH3QGDgE40YQMpXUWXOzo"/>
<div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest to-transparent"></div>
</div>
<div class="px-8 md:px-12 pb-12 -mt-12 relative z-10">
<div class="text-center mb-8">
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Join Our Community</h1>
<p class="text-on-surface-variant font-body-md">Begin your journey towards holistic wellness and timeless vitality.</p>
</div>
<?php if (!empty($errors['general'])): ?>
<div class="mb-6 p-4 rounded-lg bg-error-container border border-error text-on-error-container font-label-md"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>
<form class="space-y-6" id="registrationForm" method="POST" action="">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<div class="space-y-2">
<label class="block font-label-md text-on-surface" for="fullName">Full Name</label>
<input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md bg-white <?= isset($errors['full_name']) ? 'border-error' : '' ?>" id="fullName" name="full_name" placeholder="e.g. Aarav Sharma" type="text" value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>" required/>
<?php if (isset($errors['full_name'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['full_name']) ?></p><?php endif; ?>
</div>
<div class="space-y-2">
<label class="block font-label-md text-on-surface" for="email">Email Address</label>
<div class="relative">
<input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md bg-white pr-10 <?= isset($errors['email']) ? 'border-error' : '' ?>" id="email" name="email" placeholder="aarav@example.com" type="email" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required/>
</div>
<?php if (isset($errors['email'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
</div>
<div class="space-y-2">
<label class="block font-label-md text-on-surface" for="mobile">Mobile Number</label>
<div class="flex gap-2">
<select class="w-24 px-3 py-3 rounded-lg border border-outline-variant bg-white font-body-md focus:border-primary outline-none" name="country_code">
<option value="+91">+91</option>
<option value="+1">+1</option>
<option value="+44">+44</option>
</select>
<input class="flex-1 px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md bg-white <?= isset($errors['mobile']) ? 'border-error' : '' ?>" id="mobile" name="mobile" placeholder="98765 43210" type="tel" value="<?= htmlspecialchars($form_data['mobile'] ?? '') ?>" required/>
</div>
<?php if (isset($errors['mobile'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['mobile']) ?></p><?php endif; ?>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-2">
<label class="block font-label-md text-on-surface" for="password">Password</label>
<div class="relative">
<input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md bg-white <?= isset($errors['password']) ? 'border-error' : '' ?>" id="password" name="password" placeholder="••••••••" type="password" required/>
<button class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" onclick="togglePassword('password')" type="button">visibility</button>
</div>
<?php if (isset($errors['password'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['password']) ?></p><?php endif; ?>
</div>
<div class="space-y-2">
<label class="block font-label-md text-on-surface" for="confirmPassword">Confirm Password</label>
<div class="relative">
<input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md bg-white <?= isset($errors['confirm_password']) ? 'border-error' : '' ?>" id="confirmPassword" name="confirm_password" placeholder="••••••••" type="password" required/>
</div>
<?php if (isset($errors['confirm_password'])): ?><p class="text-[12px] text-error font-medium"><?= htmlspecialchars($errors['confirm_password']) ?></p><?php endif; ?>
</div>
</div>
<div class="space-y-2">
<label class="flex items-start gap-3 cursor-pointer group">
<div class="relative mt-1">
<input class="peer hidden" id="terms" name="terms" type="checkbox" required/>
<div class="w-5 h-5 border-2 border-error rounded flex items-center justify-center peer-checked:bg-primary peer-checked:border-primary transition-all">
<span class="material-symbols-outlined text-white text-[16px] hidden peer-checked:block">check</span>
</div>
</div>
<span class="text-label-sm text-on-surface-variant leading-tight">
I agree to the <a class="text-primary underline font-semibold" href="#">Terms of Service</a> and <a class="text-primary underline font-semibold" href="#">Privacy Policy</a>.
</span>
</label>
</div>
<button class="w-full bg-primary text-on-primary font-label-md py-4 rounded-lg shadow-md hover:bg-primary-container transition-all active:scale-95" type="submit">Create Ayurvedic Account</button>
</form>
<div class="mt-8 flex items-center justify-center gap-6 py-4 border-t border-outline-variant">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[18px]">lock</span>
<span class="text-[12px] font-label-sm">Secure Data Encryption</span>
</div>
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[18px]">verified_user</span>
<span class="text-[12px] font-label-sm">Privacy Guaranteed</span>
</div>
</div>
<div class="mt-6 text-center">
<p class="font-body-md text-on-surface-variant">
Already have an account?
<a class="text-primary font-bold hover:underline" href="<?= BASE_URL ?>/login.php">Sign In</a>
</p>
</div>
</div>
</div>
</main>
<style>
.botanical-bg {
    background-color: #f4fafd;
    background-image: radial-gradient(circle at 2px 2px, #dde4e6 1px, transparent 0);
    background-size: 40px 40px;
}
</style>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling;
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility_off";
    } else {
        input.type = "password";
        icon.textContent = "visibility";
    }
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
