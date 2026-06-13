<?php
require_once __DIR__ . '/includes/config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id < 1) {
    http_response_code(400);
    die('Invalid request.');
}

$uid = (int)($_SESSION['user_id'] ?? 0);
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

if (!$uid && !$is_admin) {
    $_SESSION['redirect_after_login'] = BASE_URL . '/invoice-download.php?order_id=' . $order_id;
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
require_once __DIR__ . '/includes/invoice-helper.php';

$stmt = $db->prepare('SELECT o.user_id FROM orders o WHERE o.id = ? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    die('Order not found.');
}

if (!$is_admin && (int)$order['user_id'] !== $uid) {
    http_response_code(403);
    die('Access denied.');
}

$stmt = $db->prepare('SELECT * FROM invoices WHERE order_id = ? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice || empty($invoice['pdf_path'])) {
    $invoice = generate_invoice_pdf($db, $order_id);
}

if (!$invoice || empty($invoice['pdf_path'])) {
    http_response_code(404);
    die('Invoice not available.');
}

$pdf_absolute = __DIR__ . '/' . $invoice['pdf_path'];
if (!is_file($pdf_absolute)) {
    $invoice = generate_invoice_pdf($db, $order_id);
    $pdf_absolute = __DIR__ . '/' . ($invoice['pdf_path'] ?? '');
    if (!is_file($pdf_absolute)) {
        http_response_code(404);
        die('Invoice file not found.');
    }
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($invoice['invoice_number']) . '.pdf"');
header('Content-Length: ' . filesize($pdf_absolute));
header('Cache-Control: private, no-transform');
readfile($pdf_absolute);
exit;
