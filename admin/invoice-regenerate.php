<?php
require_once __DIR__ . '/includes/head.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$db = getDB();
require_once __DIR__ . '/../includes/invoice-helper.php';

$message = '';
$error = '';
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$invoice = null;

if ($order_id > 0) {
    $stmt = $db->prepare('SELECT o.*, u.full_name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? LIMIT 1');
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order_check = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order_check) {
        $error = 'Order not found.';
    } else {
        $stmt = $db->prepare('SELECT * FROM invoices WHERE order_id = ? LIMIT 1');
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token.';
            } else {
                $result = generate_invoice_pdf($db, $order_id);
                if ($result && !empty($result['pdf_path'])) {
                    $message = 'Invoice regenerated successfully.';
                    $invoice = $result;
                } else {
                    $error = 'Failed to regenerate invoice. Check error logs.';
                }
            }
        }
    }
}

$active_page = 'orders';
?>

<div class="flex h-full min-h-screen">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="flex-1 p-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-display-lg text-display-lg text-primary">Regenerate Invoice</h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant mt-1">Force-regenerate and re-save an invoice PDF</p>
                </div>
                <a href="orders.php" class="text-label-sm bg-surface-container-high text-on-surface px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors">
                    ← Back to Orders
                </a>
            </div>

            <?php if ($error): ?>
            <div class="bg-error-container text-on-error-container p-4 rounded-lg mb-6 font-body-md"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
            <div class="bg-primary-container text-on-primary-container p-4 rounded-lg mb-6 font-body-md"><?= h($message) ?></div>
            <?php endif; ?>

            <form method="GET" class="bg-surface-container-low rounded-xl p-6 mb-8 border border-outline-variant">
                <label class="font-label-lg text-label-lg text-on-surface mb-2 block">Enter Order ID</label>
                <div class="flex gap-3">
                    <input type="number" name="order_id" value="<?= $order_id ?>" min="1" required class="flex-1 bg-surface border border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-lg text-label-lg hover:bg-primary-container transition-colors">Look Up</button>
                </div>
            </form>

            <?php if ($invoice): ?>
            <div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant mb-6">
                <h2 class="font-title-lg text-title-lg text-on-surface mb-4">Current Invoice</h2>
                <dl class="grid grid-cols-2 gap-4 font-body-md text-body-md">
                    <div><dt class="text-on-surface-variant">Invoice Number</dt><dd class="font-bold text-on-surface"><?= h($invoice['invoice_number']) ?></dd></div>
                    <div><dt class="text-on-surface-variant">Order</dt><dd class="font-bold text-on-surface"><?= h($order_check['order_number'] ?? 'N/A') ?></dd></div>
                    <div><dt class="text-on-surface-variant">Total</dt><dd class="font-bold text-on-surface">₹<?= number_format((float)$invoice['total_amount'], 2) ?></dd></div>
                    <div><dt class="text-on-surface-variant">Email Status</dt><dd class="font-bold text-on-surface"><?= h($invoice['email_status']) ?></dd></div>
                </dl>
            </div>

            <form method="POST" onsubmit="return confirm('Regenerate invoice for order #<?= (int)$order_id ?>? This will overwrite the existing PDF.')">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <button type="submit" class="w-full bg-error text-on-error font-label-lg text-label-lg px-6 py-3 rounded-xl hover:bg-error/80 transition-colors">
                    Regenerate Invoice PDF
                </button>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
