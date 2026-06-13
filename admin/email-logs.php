<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Email Logs';
$active_page = 'email-logs';

$has_reminder_logs = table_exists($db, 'reminder_logs');
$has_email_log = table_exists($db, 'email_log');
$has_invoices = table_exists($db, 'invoices');

$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$source = $_GET['source'] ?? ''; // reminder, invoice, manual
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 30;

// Build unified query from email_log table (primary) + reminder_logs + invoices
$all_logs = [];
$total_rows = 0;

if ($has_email_log) {
    $where = [];
    $params = [];
    $types = '';

    if ($type_filter) {
        $where[] = 'el.email_type = ?';
        $params[] = $type_filter;
        $types .= 's';
    }
    if ($status_filter) {
        $where[] = 'el.status = ?';
        $params[] = $status_filter;
        $types .= 's';
    }
    if ($search) {
        $where[] = '(el.recipient LIKE ? OR el.recipient_name LIKE ? OR el.subject LIKE ?)';
        $s = "%$search%";
        $params[] = $s; $params[] = $s; $params[] = $s;
        $types .= 'sss';
    }
    if ($source) {
        $where[] = 'el.email_type = ?';
        $params[] = $source;
        $types .= 's';
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $count_stmt = $db->prepare("SELECT COUNT(*) FROM email_log el $where_sql");
    if ($params) $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_rows = (int)$count_stmt->get_result()->fetch_row()[0];
    $count_stmt->close();

    $total_pages = max(1, (int)ceil($total_rows / $per_page));
    $offset = ($page - 1) * $per_page;

    $data_stmt = $db->prepare("SELECT el.* FROM email_log el $where_sql ORDER BY el.sent_at DESC LIMIT ? OFFSET ?");
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types = $types . 'ii';
    if ($all_types !== 'ii') {
        $data_stmt->bind_param($all_types, ...$all_params);
    } else {
        $data_stmt->bind_param('ii', $per_page, $offset);
    }
    $data_stmt->execute();
    $all_logs = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $data_stmt->close();
}

// Stats
$total_sent = $has_email_log ? (int)$db->query("SELECT COUNT(*) FROM email_log WHERE status = 'sent'")->fetch_row()[0] : 0;
$total_failed = $has_email_log ? (int)$db->query("SELECT COUNT(*) FROM email_log WHERE status = 'failed'")->fetch_row()[0] : 0;
$total_reminder_sent = $has_reminder_logs ? (int)$db->query("SELECT COUNT(*) FROM reminder_logs WHERE status = 'sent'")->fetch_row()[0] : 0;
$total_invoice_sent = $has_invoices ? (int)$db->query("SELECT COUNT(*) FROM invoices WHERE email_status = 'sent'")->fetch_row()[0] : 0;
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Email Logs</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Complete history of all outgoing emails.</p>
</div>
<a href="email-send.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm">send</span> Send Email</a>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Sent</span>
<p class="text-headline-md text-primary mt-1"><?= number_format($total_sent) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Failed</span>
<p class="text-headline-md text-on-error-container mt-1"><?= number_format($total_failed) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Reminders Sent</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_reminder_sent) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Invoices Sent</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_invoice_sent) ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-48" name="search" placeholder="Search recipient or subject..." value="<?= htmlspecialchars($search) ?>" type="text">
</div>
<select name="type" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Types</option>
<option value="invoice" <?= $type_filter === 'invoice' ? 'selected' : '' ?>>Invoice</option>
<option value="reminder" <?= $type_filter === 'reminder' ? 'selected' : '' ?>>Reminder</option>
<option value="manual" <?= $type_filter === 'manual' ? 'selected' : '' ?>>Manual</option>
</select>
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Status</option>
<option value="sent" <?= $status_filter === 'sent' ? 'selected' : '' ?>>Sent</option>
<option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
</select>
<button class="text-label-sm text-primary hover:underline" type="submit">Filter</button>
<?php if ($type_filter || $status_filter || $search): ?>
<a href="email-logs.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Recipient</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Subject</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Type</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Sent At</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($all_logs as $log): ?>
<?php
$status_class = $log['status'] === 'sent' ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-error-container text-on-error-container';
$type_labels = ['invoice' => 'Invoice', 'reminder' => 'Reminder', 'manual' => 'Manual', 'order_confirmation' => 'Order'];
$type_label = $type_labels[$log['email_type']] ?? ucfirst((string)$log['email_type']);
?>
<tr>
<td class="px-6 py-4">
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($log['recipient_name'] ?? '—') ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($log['recipient']) ?></p>
</div>
</td>
<td class="px-6 py-4 text-body-md max-w-xs truncate"><?= htmlspecialchars($log['subject']) ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm bg-surface-container-high"><?= $type_label ?></span></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $status_class ?>"><?= htmlspecialchars(ucfirst($log['status'])) ?></span></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= date('M d, Y · h:i A', strtotime($log['sent_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($all_logs)): ?>
<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No email logs found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($all_logs) ?> of <?= $total_rows ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&type=<?= urlencode($type_filter) ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&type=<?= urlencode($type_filter) ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
