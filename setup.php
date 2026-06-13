<?php
$is_cli = php_sapi_name() === 'cli';

function log_msg(string $msg): void
{
    global $is_cli;
    if ($is_cli) {
        echo $msg . PHP_EOL;
    } else {
        echo '<div style="padding:6px 12px; margin:4px 0; background:#f5f5f5; border-radius:4px; font-family:monospace; font-size:13px;">' . htmlspecialchars($msg) . '</div>';
    }
}

log_msg('=== Ayurviro Setup ===');

$dirs = [
    'uploads/invoices'      => [0775, 'Invoice PDF storage — .htaccess denies direct HTTP'],
    'uploads/health-records' => [0775, 'Uploaded health records'],
    'var/tmp/mpdf'          => [0777, 'mPDF font cache (runtime temp)'],
];

foreach ($dirs as $path => [$perms, $desc]) {
    $abs = __DIR__ . '/' . $path;
    if (!is_dir($abs)) {
        mkdir($abs, $perms, true);
        log_msg("  [CREATE] $path — $desc");
    } else {
        log_msg("  [  OK  ] $path — already exists");
    }
}

$htaccess_files = [
    'uploads/invoices'   => "# Deny direct HTTP access to generated invoice PDFs\n<IfModule !mod_authz_core.c>\n    Order Deny,Allow\n    Deny from all\n</IfModule>\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n",
    'uploads/health-records' => "# Deny direct HTTP access\n<IfModule !mod_authz_core.c>\n    Order Deny,Allow\n    Deny from all\n</IfModule>\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n",
    'var'                => "# Deny all\n<IfModule !mod_authz_core.c>\n    Order Deny,Allow\n    Deny from all\n</IfModule>\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n",
];

foreach ($htaccess_files as $path => $content) {
    $file = __DIR__ . '/' . $path . '/.htaccess';
    if (!is_file($file)) {
        file_put_contents($file, $content);
        log_msg("  [CREATE] $path/.htaccess");
    } else {
        log_msg("  [  OK  ] $path/.htaccess — already exists");
    }
}

log_msg('=== Setup complete ===');
