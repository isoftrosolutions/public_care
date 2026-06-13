<?php
require_once __DIR__ . '/includes/config.php';

$site_title = 'Returns & Replacements';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$errors = [];
$success_msg = '';
$view_return = null;
$return_items = [];

// Handle viewing a specific return
if (isset($_GET['id'])) {
    $return_id = (int)$_GET['id'];
    if ($uid > 0) {
        $stmt = $db->prepare("SELECT * FROM return_requests WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $return_id, $uid);
        $stmt->execute();
        $view_return = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($view_return) {
            $stmt2 = $db->prepare(
                "SELECT ri.*, p.name as product_name, p.image_url, oi.price
                 FROM return_items ri
                 JOIN order_items oi ON ri.order_item_id = oi.id
                 JOIN products p ON ri.product_id = p.id
                 WHERE ri.return_id = ?"
            );
            $stmt2->bind_param('i', $return_id);
            $stmt2->execute();
            $return_items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
        }
    }
    if (!$view_return) {
        header('Location: ' . BASE_URL . '/returns.php');
        exit;
    }
}

// Get user's delivered/completed orders for dropdown
$orders = [];
$order_items_map = [];
if ($uid > 0) {
    $result = $db->prepare("SELECT * FROM orders WHERE user_id = ? AND status IN ('delivered','completed') ORDER BY created_at DESC");
    $result->bind_param('i', $uid);
    $result->execute();
    $orders = $result->get_result()->fetch_all(MYSQLI_ASSOC);
    $result->close();

    if (!empty($orders)) {
        $order_ids = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
        $types = str_repeat('i', count($order_ids));
        $stmt3 = $db->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($placeholders)");
        $stmt3->bind_param($types, ...$order_ids);
        $stmt3->execute();
        $all_items = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();
        foreach ($all_items as $item) {
            $order_items_map[$item['order_id']][] = $item;
        }
    }
}

// Get user's return requests
$my_returns = [];
if ($uid > 0) {
    $stmt4 = $db->prepare("SELECT rr.*, o.order_number FROM return_requests rr JOIN orders o ON rr.order_id = o.id WHERE rr.user_id = ? ORDER BY rr.created_at DESC");
    $stmt4->bind_param('i', $uid);
    $stmt4->execute();
    $my_returns = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt4->close();
}

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } elseif ($uid <= 0) {
        $errors[] = 'You must be logged in to submit a return request.';
    } else {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $selected_items = $_POST['items'] ?? [];
        $reason = $_POST['reason'] ?? '';
        $reason_detail = trim($_POST['reason_detail'] ?? '');
        $return_type = $_POST['return_type'] ?? 'return';

        $valid_reasons = ['wrong_product', 'damaged', 'expired', 'defective', 'other'];
        $valid_types = ['return', 'replacement'];

        if ($order_id <= 0) {
            $errors[] = 'Please select an order.';
        }
        if (empty($selected_items)) {
            $errors[] = 'Please select at least one item to return.';
        }
        if (!in_array($reason, $valid_reasons)) {
            $errors[] = 'Please select a valid reason.';
        }
        if (!in_array($return_type, $valid_types)) {
            $return_type = 'return';
        }

        // Verify order belongs to user and is delivered
        $order_check = $db->prepare("SELECT id, total, shipping_name, shipping_address, shipping_city, shipping_zip FROM orders WHERE id = ? AND user_id = ? AND status IN ('delivered','completed')");
        $order_check->bind_param('ii', $order_id, $uid);
        $order_check->execute();
        $order_data = $order_check->get_result()->fetch_assoc();
        $order_check->close();

        if (!$order_data) {
            $errors[] = 'Invalid order selected.';
        }

        if (empty($errors)) {
            $selected_items = array_map('intval', $selected_items);
            $return_number = 'RET-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Build pickup address from order shipping info
            $pickup_address = trim(($order_data['shipping_address'] ?? '') . ', ' . ($order_data['shipping_city'] ?? ''));
            $pickup_zip = $order_data['shipping_zip'] ?? '';

            $db->begin_transaction();
            try {
                $stmt = $db->prepare(
                    "INSERT INTO return_requests (user_id, order_id, return_number, return_type, reason, reason_detail, status, pickup_address, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())"
                );
                $stmt->bind_param('iisssss', $uid, $order_id, $return_number, $return_type, $reason, $reason_detail, $pickup_address);
                $stmt->execute();
                $return_req_id = $stmt->insert_id;
                $stmt->close();

                $stmt_item = $db->prepare(
                    "INSERT INTO return_items (return_id, order_item_id, product_id, quantity) VALUES (?, ?, ?, ?)"
                );

                foreach ($selected_items as $order_item_id) {
                    $item_data = $db->prepare("SELECT product_id, quantity, price FROM order_items WHERE id = ? AND order_id = ?");
                    $item_data->bind_param('ii', $order_item_id, $order_id);
                    $item_data->execute();
                    $oi = $item_data->get_result()->fetch_assoc();
                    $item_data->close();

                    if ($oi) {
                        $stmt_item->bind_param('iiii', $return_req_id, $order_item_id, $oi['product_id'], $oi['quantity']);
                        $stmt_item->execute();
                    }
                }
                $stmt_item->close();

                $db->commit();
                $success_msg = 'Return request submitted successfully! Your return number is <strong>' . htmlspecialchars($return_number) . '</strong>.';
            } catch (Exception $e) {
                $db->rollback();
                error_log("Return submission failed: " . $e->getMessage());
                $errors[] = 'Failed to submit return request. Please try again.';
            }
        }
    }
}

// Handle image upload inline via separate action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $return_req_id = (int)($_POST['return_id'] ?? 0);
    if ($uid > 0 && $return_req_id > 0) {
        $check = $db->prepare("SELECT id FROM return_requests WHERE id = ? AND user_id = ?");
        $check->bind_param('ii', $return_req_id, $uid);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (isset($_FILES['return_image']) && $_FILES['return_image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['return_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'return_' . $return_req_id . '_' . time() . '.' . $ext;
                    $dest = __DIR__ . '/assets/uploads/' . $filename;
                    if (move_uploaded_file($_FILES['return_image']['tmp_name'], $dest)) {
                        $upd = $db->prepare("UPDATE return_requests SET admin_note = CONCAT(COALESCE(admin_note,''), ?) WHERE id = ?");
                        $tag = "\n[Image: " . $filename . "]";
                        $upd->bind_param('si', $tag, $return_req_id);
                        $upd->execute();
                        $upd->close();
                        $success_msg = 'Image uploaded successfully.';
                    } else {
                        $errors[] = 'Failed to upload image.';
                    }
                } else {
                    $errors[] = 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp.';
                }
            }
        }
        $check->close();
    }
}
?>

<!-- Hero Section -->
<section class="pt-32 pb-16 md:pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="text-center max-w-3xl mx-auto">
        <div class="w-16 h-16 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl text-primary" style="font-variation-settings:'FILL'1">assignment_return</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary mb-4">Hassle-Free Returns & Replacements</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-8">We stand behind every product we sell. If something isn&rsquo;t right, we&rsquo;ll make it right &mdash; no questions asked.</p>
        <div class="flex flex-wrap justify-center gap-6 text-sm font-body-md text-body-md text-on-surface-variant">
            <span class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings:'FILL'1">verified</span> 7-Day Easy Return</span>
            <span class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings:'FILL'1">local_shipping</span> Free Pickup</span>
            <span class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings:'FILL'1">currency_rupee</span> Instant Refund</span>
        </div>
    </div>
</section>

<!-- Quick Return Types -->
<section class="pb-16 md:pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <h2 class="font-headline-lg text-headline-lg text-on-surface text-center mb-10">What Would You Like to Do?</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 hover-lift text-center">
            <div class="w-14 h-14 bg-error-container rounded-full flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-2xl text-error">medication</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface mb-2">Wrong Medicine Delivered</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Received a different product than what you ordered? We&rsquo;ll replace it immediately.</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 hover-lift text-center">
            <div class="w-14 h-14 bg-error-container rounded-full flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-2xl text-error">inventory_2</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface mb-2">Damaged Product</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Package arrived damaged or product is broken? We&rsquo;ll send a replacement right away.</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 hover-lift text-center">
            <div class="w-14 h-14 bg-error-container rounded-full flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-2xl text-error">calendar_month</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface mb-2">Expired Product</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Received an expired or near-expiry product? Full refund or replacement guaranteed.</p>
        </div>
    </div>
</section>

<!-- Messages -->
<?php if (!empty($errors)): ?>
<div class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-8">
    <?php foreach ($errors as $err): ?>
    <div class="p-4 bg-error-container text-on-error-container rounded-xl mb-3 font-body-md text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-error" style="font-variation-settings:'FILL'1">error</span>
        <?= htmlspecialchars($err) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($success_msg): ?>
<div class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-8">
    <div class="p-4 bg-primary-fixed text-primary rounded-xl font-body-md text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL'1">check_circle</span>
        <?= $success_msg ?>
    </div>
</div>
<?php endif; ?>

<?php if ($view_return): ?>

<!-- Status Timeline -->
<section class="pb-16 md:pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="bg-surface-container-lowest rounded-xl p-8 md:p-12 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="font-headline-md text-headline-md text-primary mb-1">Return Status</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">#<?= htmlspecialchars($view_return['return_number']) ?></p>
            </div>
            <?php
            $status_badges = [
                'pending'    => ['bg-yellow-100 text-yellow-800', 'pending'],
                'approved'   => ['bg-green-100 text-green-800', 'check_circle'],
                'picked_up'  => ['bg-blue-100 text-blue-800', 'local_shipping'],
                'refunded'   => ['bg-emerald-100 text-emerald-800', 'currency_rupee'],
                'replaced'   => ['bg-green-100 text-green-800', 'swap_horiz'],
                'rejected'   => ['bg-red-100 text-red-800', 'cancel'],
            ];
            $sb = $status_badges[$view_return['status']] ?? $status_badges['pending'];
            ?>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-label-sm text-label-sm <?= $sb[0] ?>">
                <span class="material-symbols-outlined text-[16px]"><?= $sb[1] ?></span>
                <?= ucfirst($view_return['status']) ?>
            </span>
        </div>

        <?php
        $steps = [
            'pending'   => ['label' => 'Return Requested',  'icon' => 'assignment_return', 'desc' => 'Your request has been submitted and is under review.'],
            'approved'  => ['label' => 'Approved',           'icon' => 'check_circle',     'desc' => 'Your return has been approved. We will schedule a pickup shortly.'],
            'picked_up' => ['label' => 'Pickup Scheduled',   'icon' => 'local_shipping',   'desc' => 'Our courier partner will pick up the item from your address.'],
            'replaced'  => ['label' => 'Replacement Sent',   'icon' => 'swap_horiz',       'desc' => 'Your replacement has been dispatched.'],
            'refunded'  => ['label' => 'Refund Issued',      'icon' => 'currency_rupee',   'desc' => 'The amount has been refunded to your original payment method.'],
        ];
        $status_order = ['pending', 'approved', 'picked_up', 'replaced', 'refunded'];
        $current_idx = array_search($view_return['status'], $status_order);
        if ($view_return['status'] === 'rejected') {
            $steps['rejected'] = ['label' => 'Rejected', 'icon' => 'cancel', 'desc' => 'Your return request could not be approved. Please contact support for details.'];
            $status_order[] = 'rejected';
            $current_idx = count($status_order) - 1;
        }
        // For refunded, don't show replaced as future
        if ($view_return['status'] === 'refunded') {
            $status_order = ['pending', 'approved', 'picked_up', 'refunded'];
            $current_idx = 3;
        }
        // For replaced, refunded is not relevant
        if ($view_return['status'] === 'replaced') {
            $current_idx = 3;
        }
        ?>

        <div class="relative">
            <div class="absolute left-[19px] top-0 bottom-0 w-0.5 bg-outline-variant hidden md:block"></div>
            <div class="space-y-8">
                <?php foreach ($status_order as $i => $key): ?>
                <?php $step = $steps[$key]; ?>
                <?php
                $is_done = $i < $current_idx;
                $is_current = $i === $current_idx;
                $is_future = $i > $current_idx;
                ?>
                <div class="flex gap-5 items-start">
                    <div class="hidden md:flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 z-10 transition-all <?= $is_done ? 'bg-primary-container text-on-primary-container' : ($is_current ? 'bg-primary text-on-primary ring-4 ring-primary-fixed shadow-lg' : 'bg-surface-container-highest text-on-surface-variant') ?>">
                            <span class="material-symbols-outlined text-lg"><?= $step['icon'] ?></span>
                        </div>
                    </div>
                    <div class="flex-1 pt-1">
                        <h4 class="font-title-lg text-title-lg <?= $is_future ? 'text-on-surface-variant/50' : 'text-on-surface' ?>"><?= $step['label'] ?></h4>
                        <p class="font-body-md text-body-md <?= $is_future ? 'text-on-surface-variant/40' : 'text-on-surface-variant' ?>"><?= $step['desc'] ?></p>
                        <?php if ($is_current && $view_return['tracking_id'] && ($key === 'picked_up' || $key === 'replaced')): ?>
                        <a href="<?= htmlspecialchars($view_return['tracking_id']) ?>" target="_blank" class="inline-flex items-center gap-2 mt-2 text-primary font-label-sm text-label-sm hover:underline">
                            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                            Track Shipment
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($return_items)): ?>
        <div class="mt-10 pt-8 border-t border-outline-variant/20">
            <h3 class="font-title-lg text-title-lg text-on-surface mb-4">Return Items</h3>
            <div class="space-y-4">
                <?php foreach ($return_items as $ritem): ?>
                <div class="flex items-center gap-4 p-4 bg-surface-bright rounded-lg">
                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-surface-container flex-shrink-0">
                        <?php if (!empty($ritem['image_url'])): ?>
                        <img class="w-full h-full object-cover" src="<?= htmlspecialchars($ritem['image_url']) ?>" alt="<?= htmlspecialchars($ritem['product_name']) ?>">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-secondary-container text-primary font-bold"><?= htmlspecialchars(mb_substr($ritem['product_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-body-lg text-body-lg text-on-surface"><?= htmlspecialchars($ritem['product_name']) ?></p>
                        <p class="font-body-md text-body-md text-on-surface-variant">Qty: <?= (int)$ritem['quantity'] ?> &middot; ₹<?= number_format($ritem['price'], 2) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="<?= BASE_URL ?>/returns.php" class="inline-flex items-center gap-2 text-primary font-label-lg text-label-lg hover:underline">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to All Returns
            </a>
        </div>
    </div>
</section>

<?php else: ?>

<!-- Return Form -->
<section class="pb-16 md:pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="bg-surface-container-lowest rounded-xl p-8 md:p-12 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 max-w-3xl mx-auto">
        <h2 class="font-headline-md text-headline-md text-primary mb-2">Submit a Return Request</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mb-8">Fill in the details below and we&rsquo;ll take care of the rest.</p>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="p-6 bg-surface-bright rounded-xl text-center">
            <span class="material-symbols-outlined text-4xl text-primary mb-4 block">lock</span>
            <p class="font-body-lg text-body-lg text-on-surface mb-4">Please log in to submit a return request.</p>
            <a href="<?= BASE_URL ?>/login.php" class="inline-block bg-primary text-on-primary px-8 py-3 rounded-lg font-label-lg text-label-lg hover:shadow-lg transition-all">Log In</a>
        </div>
        <?php elseif (empty($orders)): ?>
        <div class="p-6 bg-surface-bright rounded-xl text-center">
            <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-4 block">inbox</span>
            <p class="font-body-lg text-body-lg text-on-surface mb-2">No completed orders found.</p>
            <p class="font-body-md text-body-md text-on-surface-variant mb-4">You can only request returns for orders that have been delivered.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="inline-block bg-primary text-on-primary px-8 py-3 rounded-lg font-label-lg text-label-lg hover:shadow-lg transition-all">Start Shopping</a>
        </div>
        <?php else: ?>

        <form method="POST" id="return-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-lg text-label-lg text-on-surface-variant" for="order_id">Select Order</label>
                <select name="order_id" id="order_id" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface font-body-lg text-body-lg" required>
                    <option value="">Choose an order...</option>
                    <?php foreach ($orders as $o): ?>
                    <option value="<?= $o['id'] ?>" data-order='<?= htmlspecialchars(json_encode($order_items_map[$o['id']] ?? []), ENT_QUOTES) ?>'>
                        #<?= htmlspecialchars($o['order_number']) ?> &mdash; ₹<?= number_format($o['total'], 2) ?> (<?= date('d M Y', strtotime($o['created_at'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col gap-2 mb-6" id="items-container" style="display:none">
                <label class="font-label-lg text-label-lg text-on-surface-variant">Select Items to Return</label>
                <div id="items-list" class="space-y-3"></div>
            </div>

            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-lg text-label-lg text-on-surface-variant" for="reason">Reason for Return</label>
                <select name="reason" id="reason" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface font-body-lg text-body-lg" required>
                    <option value="">Select a reason...</option>
                    <option value="wrong_product">Wrong Medicine Delivered</option>
                    <option value="damaged">Damaged Product</option>
                    <option value="expired">Expired Product</option>
                    <option value="defective">Defective / Not Working</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-lg text-label-lg text-on-surface-variant" for="reason_detail">Detailed Description</label>
                <textarea name="reason_detail" id="reason_detail" rows="4" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface font-body-lg text-body-lg" placeholder="Tell us more about the issue..."></textarea>
            </div>

            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-lg text-label-lg text-on-surface-variant" for="return_image">Upload Image (optional)</label>
                <input type="file" name="return_image" id="return_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface font-body-lg text-body-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-on-primary file:font-label-lg file:text-label-lg hover:file:bg-primary-container">
            </div>

            <div class="flex flex-col gap-3 mb-8">
                <label class="font-label-lg text-label-lg text-on-surface-variant">Return Type</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10 flex-1 min-w-[180px]">
                        <input type="radio" name="return_type" value="return" checked class="w-5 h-5 text-primary focus:ring-primary">
                        <span class="material-symbols-outlined text-primary">currency_rupee</span>
                        <div>
                            <span class="font-title-lg text-title-lg text-on-surface block">Return</span>
                            <span class="font-body-md text-body-md text-on-surface-variant">Get a full refund</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10 flex-1 min-w-[180px]">
                        <input type="radio" name="return_type" value="replacement" class="w-5 h-5 text-primary focus:ring-primary">
                        <span class="material-symbols-outlined text-primary">swap_horiz</span>
                        <div>
                            <span class="font-title-lg text-title-lg text-on-surface block">Replacement</span>
                            <span class="font-body-md text-body-md text-on-surface-variant">Get a new item sent</span>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" name="submit_return" class="w-full bg-primary text-on-primary px-8 py-4 rounded-lg font-headline-md text-headline-md hover:shadow-xl hover:scale-[1.01] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <span class="material-symbols-outlined">send</span>
                Submit Return Request
            </button>
        </form>

        <script>
        const orderSelect = document.getElementById('order_id');
        const itemsContainer = document.getElementById('items-container');
        const itemsList = document.getElementById('items-list');

        orderSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const data = selected.getAttribute('data-order');
            if (data) {
                try {
                    const items = JSON.parse(data);
                    if (items.length > 0) {
                        itemsContainer.style.display = 'flex';
                        itemsList.innerHTML = items.map(function(item) {
                            return '<label class="flex items-center gap-4 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10">' +
                                '<input type="checkbox" name="items[]" value="' + item.id + '" class="w-5 h-5 text-primary focus:ring-primary rounded">' +
                                '<div class="w-12 h-12 rounded-lg overflow-hidden bg-surface-container flex-shrink-0">' +
                                (item.image_url
                                    ? '<img class="w-full h-full object-cover" src="' + item.image_url + '" alt="' + item.name + '">'
                                    : '<div class="w-full h-full flex items-center justify-center bg-secondary-container text-primary font-bold">' + item.name.charAt(0) + '</div>'
                                ) +
                                '</div>' +
                                '<div class="flex-1">' +
                                '<p class="font-body-lg text-body-lg text-on-surface">' + item.name + '</p>' +
                                '<p class="font-body-md text-body-md text-on-surface-variant">Qty: ' + item.quantity + ' &middot; ₹' + parseFloat(item.price).toFixed(2) + '</p>' +
                                '</div>' +
                                '</label>';
                        }).join('');
                        return;
                    }
                } catch(e) {}
            }
            itemsContainer.style.display = 'none';
            itemsList.innerHTML = '';
        });
        </script>

        <?php endif; ?>
    </div>
</section>

<!-- My Returns Table -->
<section class="pb-16 md:pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="bg-surface-container-lowest rounded-xl p-8 md:p-12 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 max-w-5xl mx-auto">
        <h2 class="font-headline-md text-headline-md text-primary mb-2">My Returns</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mb-8">Track all your return and replacement requests.</p>

        <?php if (empty($my_returns)): ?>
        <div class="text-center py-12">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/50 mb-4 block">assignment_return</span>
            <p class="font-body-lg text-body-lg text-on-surface-variant">No return requests yet.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-outline-variant/20 font-label-lg text-label-lg text-on-surface-variant">
                        <th class="pb-4 pr-4">Return #</th>
                        <th class="pb-4 pr-4">Order #</th>
                        <th class="pb-4 pr-4 hidden md:table-cell">Type</th>
                        <th class="pb-4 pr-4">Status</th>
                        <th class="pb-4 pr-4 hidden md:table-cell">Date</th>
                        <th class="pb-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_returns as $r): ?>
                    <?php
                    $badge_map = [
                        'pending'   => 'bg-yellow-100 text-yellow-800',
                        'approved'  => 'bg-green-100 text-green-800',
                        'picked_up' => 'bg-blue-100 text-blue-800',
                        'refunded'  => 'bg-emerald-100 text-emerald-800',
                        'replaced'  => 'bg-green-100 text-green-800',
                        'rejected'  => 'bg-red-100 text-red-800',
                    ];
                    $badge_class = $badge_map[$r['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="border-b border-outline-variant/10 hover:bg-surface-bright/50 transition-colors">
                        <td class="py-4 pr-4 font-body-lg text-body-lg text-on-surface"><?= htmlspecialchars($r['return_number']) ?></td>
                        <td class="py-4 pr-4 font-body-md text-body-md text-on-surface-variant">#<?= htmlspecialchars($r['order_number'] ?? '') ?></td>
                        <td class="py-4 pr-4 hidden md:table-cell font-body-md text-body-md text-on-surface-variant capitalize"><?= htmlspecialchars($r['return_type']) ?></td>
                        <td class="py-4 pr-4"><span class="inline-block px-3 py-1 rounded-full font-label-sm text-label-sm <?= $badge_class ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td class="py-4 pr-4 hidden md:table-cell font-body-md text-body-md text-on-surface-variant"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="py-4">
                            <a href="<?= BASE_URL ?>/returns.php?id=<?= $r['id'] ?>" class="inline-flex items-center gap-1 text-primary font-label-sm text-label-sm hover:underline">
                                View Details
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
