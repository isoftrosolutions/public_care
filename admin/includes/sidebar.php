<aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container-low border-r border-outline-variant flex flex-col py-base z-50">
<div class="px-6 py-8 mb-6">
<h1 class="text-headline-md font-headline-md font-bold text-primary">Admin Portal</h1>
<p class="text-label-sm text-on-surface-variant opacity-70">Operations Management</p>
<a href="<?= BASE_URL ?>/index.php" class="mt-4 text-xs text-primary flex items-center gap-1 hover:underline"><span class="material-symbols-outlined text-sm">arrow_back</span> Back to Public Site</a>
</div>
<nav class="flex-grow space-y-1">
<?php
$nav_items = [
    'dashboard'     => ['label' => 'Dashboard',     'icon' => 'dashboard'],
    'orders'        => ['label' => 'Orders',         'icon' => 'shopping_bag'],
    'products'      => ['label' => 'Products',       'icon' => 'medication'],
    'categories'    => ['label' => 'Categories',     'icon' => 'category'],
    'doctors'       => ['label' => 'Doctors',        'icon' => 'stethoscope'],
    'appointments'  => ['label' => 'Appointments',   'icon' => 'calendar_month'],
    'blog'          => ['label' => 'Blog Posts',     'icon' => 'article'],
    'users'         => ['label' => 'Users',          'icon' => 'group'],
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
