<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/feature_helpers.php';
require_once __DIR__ . '/mail-helper.php';

use Mpdf\Mpdf;

function ensure_invoice_table(mysqli $db): void
{
    $db->query("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NULL,
        order_punch_id INT NULL,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        pdf_path VARCHAR(255) NULL,
        email_status ENUM('pending','sent','failed') DEFAULT 'pending',
        email_error TEXT NULL,
        emailed_at TIMESTAMP NULL,
        gst_number VARCHAR(50) NULL,
        sub_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        discount DECIMAL(10,2) DEFAULT 0.00,
        taxable_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        cgst DECIMAL(10,2) DEFAULT 0.00,
        sgst DECIMAL(10,2) DEFAULT 0.00,
        igst DECIMAL(10,2) DEFAULT 0.00,
        total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        amount_in_words VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");

    foreach (['pdf_path', 'email_status', 'email_error', 'emailed_at'] as $col) {
        if (!column_exists($db, 'invoices', $col)) {
            $type = match ($col) {
                'pdf_path' => 'VARCHAR(255) NULL AFTER invoice_number',
                'email_status' => "ENUM('pending','sent','failed') DEFAULT 'pending' AFTER pdf_path",
                'email_error' => 'TEXT NULL AFTER email_status',
                'emailed_at' => 'TIMESTAMP NULL AFTER email_error',
            };
            $db->query("ALTER TABLE invoices ADD COLUMN $col $type");
        }
    }
}

function invoice_storage_dir(): string
{
    $dir = __DIR__ . '/../uploads/invoices';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return realpath($dir) ?: $dir;
}

function invoice_relative_path(string $absolute_path): string
{
    $root = realpath(__DIR__ . '/..');
    $real_dir = realpath(dirname($absolute_path));
    $normalized = str_replace('\\', '/', $real_dir ? $real_dir . DIRECTORY_SEPARATOR . basename($absolute_path) : $absolute_path);
    if ($root) {
        $root = str_replace('\\', '/', $root);
        if (str_starts_with($normalized, $root)) {
            return ltrim(substr($normalized, strlen($root)), '/');
        }
    }
    return $normalized;
}

function get_order_invoice_data(mysqli $db, int $order_id): ?array
{
    $stmt = $db->prepare("SELECT o.*, u.full_name AS customer_name, u.email AS customer_email, u.mobile AS customer_mobile
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
        LIMIT 1");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$order) {
        return null;
    }

    $items_stmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.hsn_code, p.gst_percent
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
        ORDER BY oi.id ASC");
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $items_stmt->close();

    return ['order' => $order, 'items' => $items];
}

function next_invoice_number(mysqli $db): string
{
    $year = date('Y');
    $prefix = "AYV-$year-";
    $stmt = $db->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $prefix);
    $stmt->execute();
    $last = $stmt->get_result()->fetch_assoc()['invoice_number'] ?? '';
    $stmt->close();

    $next = 1;
    if ($last && preg_match('/(\d+)$/', $last, $m)) {
        $next = ((int)$m[1]) + 1;
    }
    return $prefix . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
}

function number_to_words_indian(float $number): string
{
    $no = round($number, 2);
    $decimal = sprintf('%02d', ($no - floor($no)) * 100);
    $no = floor($no);

    if ($no == 0) return 'Zero';

    $words = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
        20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
        60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    ];
    $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

    if ($no < 100) {
        $result = $words[$no] ?? ($words[10 * floor($no / 10)] . ' ' . $words[$no % 10]);
    } else {
        $segments = [];
        $segments[] = floor($no / 100) % 10;
        $segments[] = $no % 100;
        $segments[] = floor($no / 1000) % 100;
        $segments[] = floor($no / 100000) % 100;
        $segments[] = floor($no / 10000000) % 100;

        $labels = ['', 'Hundred ', 'Thousand ', 'Lakh ', 'Crore '];
        $result = '';
        foreach ($segments as $i => $seg) {
            if ($seg == 0) continue;
            $part = $seg < 100 ? ($words[$seg] ?? ($words[10 * floor($seg / 10)] . ' ' . $words[$seg % 10])) : '';
            $result = $part . ' ' . $labels[$i] . $result;
        }
    }

    $result = trim($result);
    $paise = (int)$decimal;
    if ($paise > 0) {
        $paise_word = $paise < 100 ? ($words[$paise] ?? ($words[10 * floor($paise / 10)] . ' ' . $words[$paise % 10])) : '';
        $result .= ' and ' . $paise_word . ' Paise';
    }
    return $result . ' Only';
}

function generate_invoice_pdf(mysqli $db, int $order_id): ?array
{
    ensure_invoice_table($db);

    $existing = $db->prepare('SELECT * FROM invoices WHERE order_id = ? LIMIT 1');
    $existing->bind_param('i', $order_id);
    $existing->execute();
    $invoice = $existing->get_result()->fetch_assoc();
    $existing->close();
    if ($invoice && is_file(__DIR__ . '/../' . $invoice['pdf_path'])) {
        return $invoice;
    }

    $data = get_order_invoice_data($db, $order_id);
    if (!$data) {
        return null;
    }

    $order = $data['order'];
    $items = $data['items'];
    $invoice_number = $invoice['invoice_number'] ?? next_invoice_number($db);
    $filename = strtolower($invoice_number) . '-order-' . $order_id . '.pdf';
    $absolute_path = invoice_storage_dir() . DIRECTORY_SEPARATOR . $filename;
    $relative_path = invoice_relative_path($absolute_path);

    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }
    $gst = (float)($order['gst_amount'] ?? $order['tax'] ?? 0);
    $shipping = (float)($order['shipping'] ?? 0);
    $discount = (float)($order['discount_amount'] ?? 0);
    $wallet = (float)($order['wallet_used'] ?? 0);
    $grand_total = (float)$order['total'];

    $ship_addr = trim((string)($order['shipping_address'] ?? ''));
    $ship_city = trim((string)($order['shipping_city'] ?? ''));
    $ship_zip = trim((string)($order['shipping_zip'] ?? ''));
    $ship_phone = trim((string)($order['shipping_phone'] ?: $order['customer_mobile'] ?: ''));
    $ship_name = trim((string)($order['shipping_name'] ?: $order['customer_name'] ?: 'Customer'));
    $cust_name = trim((string)($order['customer_name'] ?: $ship_name));
    $cust_email = trim((string)($order['customer_email'] ?? ''));

    $taxable_total = 0.0;
    $total_cgst = 0.0;
    $total_sgst = 0.0;
    $total_igst = 0.0;

    $items_html = '';
    foreach (array_slice($items, 0, 12) as $i => $item) {
        $qty = (int)$item['quantity'];
        $unit_price = (float)$item['price'];
        $line_total = $unit_price * $qty;
        $taxable_total += $line_total;

        $gst_pct = (float)($item['gst_percent'] ?? 0);
        $hsn = h($item['hsn_code'] ?? '-');
        $gst_label = $gst_pct > 0 ? $gst_pct . '%' : 'Exempt';
        $cgst_amt = $sgst_amt = $igst_amt = 0.0;

        if ($gst_pct > 0) {
            $igst_amt = round($line_total * $gst_pct / 100, 2);
            $total_igst += $igst_amt;
        }

        $alt_class = $i % 2 === 0 ? '' : 'background: #f8fafc;';
        $price_fmt = number_format($unit_price, 2);
        $lt_fmt = number_format($line_total, 2);
        $items_html .= <<<ROW
        <tr style="{$alt_class}">
            <td style="padding: 8px 12px; font-size: 12px;">{$item['product_name']}</td>
            <td style="padding: 8px 12px; font-size: 12px; text-align: center;">{$qty}</td>
            <td style="padding: 8px 12px; font-size: 12px; text-align: right;">₹{$price_fmt}</td>
            <td style="padding: 8px 12px; font-size: 12px; text-align: right;">₹{$lt_fmt}</td>
            <td style="padding: 8px 12px; font-size: 12px; text-align: center;">{$gst_label}</td>
            <td style="padding: 8px 12px; font-size: 12px; text-align: right;">₹{$lt_fmt}</td>
        </tr>
ROW;
    }

    $logo_html = '';
    $logo_path = __DIR__ . '/../assets/uploads/logo.jpeg';
    if (is_file($logo_path)) {
        $logo_b64 = base64_encode(file_get_contents($logo_path));
        $logo_html = '<img src="data:image/jpeg;base64,' . $logo_b64 . '" alt="AyurViora" style="height: 50px; width: auto;">';
    }

    $cgst_fmt = number_format($total_cgst, 2);
    $sgst_fmt = number_format($total_sgst, 2);
    $igst_fmt = number_format($total_igst, 2);
    $subtotal_fmt = number_format($subtotal, 2);
    $gst_fmt = number_format($gst, 2);
    $shipping_fmt = $shipping > 0 ? '₹' . number_format($shipping, 2) : 'FREE';
    $grand_total_fmt = number_format($grand_total, 2);
    $discount_fmt = number_format($discount, 2);
    $amount_words = number_to_words_indian($grand_total);

    $adjustments_row = '';
    $adjustment_total = $discount + $wallet;
    if ($adjustment_total > 0) {
        $adj_fmt = number_format($adjustment_total, 2);
        $adjustments_row = <<<ROW
        <tr>
            <td colspan="4" style="padding: 6px 12px; font-size: 12px; text-align: right; font-weight: bold;">Adjustments</td>
            <td style="padding: 6px 12px; font-size: 12px; text-align: right;">-₹{$adj_fmt}</td>
        </tr>
ROW;
    }

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8">
<style>
body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; color: #1a1a1a; font-size: 12px; }
.invoice-wrap { width: 100%; max-width: 750px; margin: 0 auto; }
.header { background: #005221; color: white; padding: 20px 30px; }
.header-table { width: 100%; }
.header-left { text-align: left; }
.header-right { text-align: right; }
.header h1 { margin: 0; font-size: 22px; font-weight: bold; }
.header .tagline { margin: 2px 0 0; font-size: 11px; opacity: 0.8; }
.header h2 { margin: 0; font-size: 26px; }
.content { padding: 30px; }
.section-title { margin: 0 0 8px; font-size: 14px; color: #005221; }
.address-table { width: 100%; margin-bottom: 30px; }
.address-table td { vertical-align: top; }
.address-table .right { text-align: right; }
.info { font-size: 12px; color: #666; margin: 2px 0; }
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.items-table th { background: #005221; color: white; padding: 10px 12px; font-size: 11px; }
.items-table td { padding: 8px 12px; font-size: 12px; }
.summary-table { width: 300px; margin-left: auto; border-collapse: collapse; }
.summary-table td { padding: 6px 12px; font-size: 12px; }
.summary-table .total-row td { border-top: 2px solid #005221; padding: 10px 12px; font-size: 14px; font-weight: bold; }
.footer { margin-top: 40px; border-top: 1px solid #e0e0e0; padding-top: 20px; text-align: center; }
.footer p { margin: 5px 0; font-size: 11px; color: #999; }
.footer .thank-you { font-size: 14px; font-weight: bold; color: #1a1a1a; }
.amount-words { font-size: 12px; color: #555; margin-top: 20px; }
.amount-words strong { color: #005221; }
</style>
</head>
<body>
<div class="invoice-wrap">
<div class="header">
<table class="header-table"><tr>
<td class="header-left">
{$logo_html}
<h1>AyurViora</h1>
<p class="tagline">Ancient Wisdom for Modern Living</p>
</td>
<td class="header-right">
<h2>TAX INVOICE</h2>
<p style="margin: 2px 0; font-size: 11px;">Invoice No: {$invoice_number}</p>
<p style="margin: 0; font-size: 11px;">Order No: {$order['order_number']}</p>
<p style="margin: 2px 0 0; font-size: 11px;">Date: {$order['created_at']}</p>
</td>
</tr></table>
</div>
<div class="content">
<table class="address-table"><tr>
<td>
<h3 class="section-title">Bill To</h3>
<p style="margin: 2px 0;">{$cust_name}</p>
<p class="info">{$cust_email}</p>
</td>
<td class="right">
<h3 class="section-title">Ship To</h3>
<p style="margin: 2px 0;">{$ship_name}</p>
<p class="info">{$ship_addr}</p>
<p class="info">{$ship_city} {$ship_zip}</p>
<p class="info">{$ship_phone}</p>
</td>
</tr></table>

<table class="items-table">
<thead><tr>
<th style="text-align: left;">Description</th>
<th style="text-align: center;">Qty</th>
<th style="text-align: right;">Rate</th>
<th style="text-align: right;">Amount</th>
<th style="text-align: center;">GST</th>
<th style="text-align: right;">Total</th>
</tr></thead>
<tbody>
{$items_html}
</tbody>
</table>

<div style="margin-top: 10px;">
<table class="summary-table">
<tr><td>Subtotal</td><td style="text-align: right;">₹{$subtotal_fmt}</td></tr>
HTML;

    if ($discount > 0) {
        $html .= "<tr><td>Discount</td><td style='text-align: right;'>-₹{$discount_fmt}</td></tr>";
    }
    if ($total_igst > 0) {
        $html .= "<tr><td>IGST ({$items[0]['gst_percent']}%)</td><td style='text-align: right;'>₹{$igst_fmt}</td></tr>";
    }
    $html .= <<<HTML
<tr><td>Shipping</td><td style="text-align: right;">{$shipping_fmt}</td></tr>
{$adjustments_row}
<tr class="total-row"><td>Grand Total</td><td style="text-align: right;">₹{$grand_total_fmt}</td></tr>
</table>
</div>

<div class="amount-words">
<strong>Amount in Words:</strong> {$amount_words}
</div>

<div class="footer">
<p class="thank-you">Thank you for choosing AyurViora.</p>
<p>This is a computer-generated invoice. For support, contact support@AyurViora.com.</p>
</div>
</div>
</div>
</body>
</html>
HTML;

    try {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'default_font' => 'DejaVu Sans',
            'tempDir' => __DIR__ . '/../var/tmp/mpdf',
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($absolute_path, \Mpdf\Output\Destination::FILE);
    } catch (Throwable $e) {
        error_log('mPDF generation failed for order ' . $order_id . ': ' . $e->getMessage());
        return null;
    }

    $adjustment_total = $discount + $wallet;
    $taxable_for_db = $subtotal - $adjustment_total;

    if ($invoice) {
        $stmt = $db->prepare('UPDATE invoices SET pdf_path = ?, sub_total = ?, taxable_amount = ?, discount = ?, cgst = ?, sgst = ?, igst = ?, total_amount = ?, amount_in_words = ? WHERE id = ?');
        $invoice_id = (int)$invoice['id'];
        $stmt->bind_param('sdddddddsi', $relative_path, $subtotal, $taxable_for_db, $discount, $total_cgst, $total_sgst, $total_igst, $grand_total, $amount_words, $invoice_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $db->prepare("INSERT INTO invoices (order_id, invoice_number, pdf_path, email_status, sub_total, taxable_amount, discount, cgst, sgst, igst, total_amount, amount_in_words) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issddddddds', $order_id, $invoice_number, $relative_path, $subtotal, $taxable_for_db, $discount, $total_cgst, $total_sgst, $total_igst, $grand_total, $amount_words);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $db->prepare('SELECT * FROM invoices WHERE order_id = ? LIMIT 1');
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $saved_invoice = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $saved_invoice ?: null;
}

function send_order_invoice_email(mysqli $db, int $order_id, array $invoice): bool
{
    $data = get_order_invoice_data($db, $order_id);
    if (!$data) {
        return false;
    }
    $order = $data['order'];
    $email = (string)($order['customer_email'] ?? '');
    if ($email === '') {
        return false;
    }

    $pdf_path = __DIR__ . '/../' . $invoice['pdf_path'];
    $cust_name = $order['customer_name'] ?? $order['shipping_name'] ?? 'Customer';
    $subject = 'Your AyurViora invoice for order ' . $order['order_number'];

    $html_body = '<p>Namaste ' . h($cust_name) . ',</p>'
        . '<p>Thank you for your order <strong>' . h($order['order_number']) . '</strong>.</p>'
        . '<p>Your invoice <strong>' . h($invoice['invoice_number']) . '</strong> is attached as a PDF.</p>'
        . '<p><a href="' . BASE_URL . '/order-tracking.php?order_number=' . urlencode($order['order_number']) . '" style="background: #005221; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;">View Order</a></p>'
        . '<p>Regards,<br>AyurViora</p>';

    $alt_body = "Thank you for your order {$order['order_number']}. Your invoice {$invoice['invoice_number']} is attached.";

    $result = send_email($email, $cust_name, $subject, $html_body, $alt_body, $pdf_path, $invoice['invoice_number'] . '.pdf');

    $sent = $result['success'];
    $status = $sent ? 'sent' : 'failed';
    $error = $sent ? null : ($result['error'] ?? 'Unknown error');
    $invoice_id = (int)$invoice['id'];

    $stmt = $db->prepare("UPDATE invoices SET email_status = ?, email_error = ?, emailed_at = IF(? = 'sent', NOW(), emailed_at) WHERE id = ?");
    $stmt->bind_param('sssi', $status, $error, $status, $invoice_id);
    $stmt->execute();
    $stmt->close();

    if ($sent) {
        log_email_sent($db, $email, $cust_name, $subject, 'invoice', 'order', $order_id);
    } else {
        log_email_failed($db, $email, $cust_name, $subject, $error ?? 'Unknown', 'invoice', 'order', $order_id);
    }

    return $sent;
}

function generate_and_email_order_invoice(mysqli $db, int $order_id): void
{
    try {
        $invoice = generate_invoice_pdf($db, $order_id);
        if ($invoice) {
            send_order_invoice_email($db, $order_id, $invoice);
        }
    } catch (Throwable $e) {
        error_log('Invoice generation/email failed for order ' . $order_id . ': ' . $e->getMessage());
    }
}

