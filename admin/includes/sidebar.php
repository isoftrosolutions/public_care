<aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container-low border-r border-outline-variant flex flex-col py-base z-50">
<nav class="flex-grow space-y-1 mt-4 overflow-y-auto">
<?php
$nav_items = [
    'dashboard'     => ['label' => 'Dashboard',     'icon' => 'dashboard'],
    'orders'        => ['label' => 'Orders',         'icon' => 'shopping_bag'],
    'returns'       => ['label' => 'Returns & Refunds', 'icon' => 'assignment_return'],
    'products'      => ['label' => 'Products',       'icon' => 'medication'],
    'categories'    => ['label' => 'Categories',     'icon' => 'category'],
    'doctors'       => ['label' => 'Doctors',        'icon' => 'stethoscope'],
    'appointments'  => ['label' => 'Appointments',   'icon' => 'calendar_month'],
    'consultations' => ['label' => 'Consultations',  'icon' => 'videocam'],
    'prescriptions' => ['label' => 'Prescriptions',  'icon' => 'prescriptions'],
    'email-logs'    => ['label' => 'Email Logs',     'icon' => 'mail'],
    'email-settings'=> ['label' => 'Email Settings',  'icon' => 'settings'],
    'email-send'    => ['label' => 'Send Email',     'icon' => 'send'],
    'blog'          => ['label' => 'Blog Posts',     'icon' => 'article'],
    'coupons'       => ['label' => 'Coupons & Offers', 'icon' => 'sell'],
    'reports'       => ['label' => 'Reports',        'icon' => 'monitoring'],
    'dosha-questions' => ['label' => 'Dosha Questions', 'icon' => 'self_improvement'],
    'patient-metrics' => ['label' => 'Patient Metrics', 'icon' => 'monitoring'],
    'users'          => ['label' => 'Users',           'icon' => 'group'],
    'family-members' => ['label' => 'Family Accounts', 'icon' => 'family_history'],
    'settings'      => ['label' => 'Settings',        'icon' => 'settings'],
];
foreach ($nav_items as $key => $item):
    $is_active = ($active_page === $key);
?>
<a class="<?= $is_active ? 'bg-secondary-container text-on-secondary-container rounded-lg' : 'text-on-surface-variant hover:bg-surface-container-highest' ?> px-4 py-3 mx-2 flex items-center gap-3 transition-all" href="<?= $key ?>.php"><span class="material-symbols-outlined"><?= $item['icon'] ?></span><span class="text-label-md"><?= $item['label'] ?></span></a>
<?php endforeach; ?>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 pb-4">
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="<?= BASE_URL ?>/logout.php"><span class="material-symbols-outlined">logout</span><span class="text-label-md">Logout</span></a>
</div>
</aside>
