<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Order Punch — B2B Made Easy';
require_once __DIR__ . '/includes/header.php';
?>
<section class="relative overflow-hidden bg-gradient-to-br from-primary via-primary-container to-primary/80 px-base md:px-margin-desktop py-section-gap">
    <div class="max-w-container-max mx-auto text-center relative z-10">
        <span class="inline-block bg-white/15 text-white px-5 py-1.5 rounded-full font-label-lg text-label-lg mb-6 backdrop-blur-sm">B2B Wholesale Ordering</span>
        <h1 class="font-display-lg text-display-lg text-white max-w-3xl mx-auto leading-tight">Order Punch —<br>B2B Made Easy</h1>
        <p class="font-body-lg text-body-lg text-white/80 mt-4 max-w-xl mx-auto">Bulk order Ayurvedic products for your pharmacy, clinic, or wellness store. Quick search, barcode scan, or upload a spreadsheet — you choose.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8">
            <a href="#order-form" class="bg-white text-primary px-7 py-3 rounded-xl font-label-lg text-label-lg hover:bg-secondary-container transition-all active:scale-95 shadow-lg">Start Ordering</a>
            <a href="#recent-orders" class="bg-white/10 text-white border border-white/30 px-7 py-3 rounded-xl font-label-lg text-label-lg hover:bg-white/20 transition-all active:scale-95 backdrop-blur-sm">View Recent Orders</a>
        </div>
    </div>
    <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(circle at 25% 50%, #ffffff 1px, transparent 1px); background-size: 30px 30px;"></div>
</section>

<section class="px-base md:px-margin-desktop py-section-gap max-w-container-max mx-auto">
    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Quick Order Methods</h2>
    <p class="font-body-md text-body-md text-on-surface-variant mb-8">Choose how you want to build your order</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <a href="#order-form" class="group bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/40 hover-lift cursor-pointer transition-all">
            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-on-primary transition-all text-2xl">📝</div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 group-hover:text-primary transition-colors">Search Medicine</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Type any medicine name and add to your order instantly.</p>
        </a>
        <div class="group bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/40 hover-lift cursor-pointer transition-all">
            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-on-primary transition-all text-2xl">📷</div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 group-hover:text-primary transition-colors">Barcode Scan</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Scan product barcodes for lightning-fast addition.</p>
        </div>
        <div class="group bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/40 hover-lift cursor-pointer transition-all" onclick="document.getElementById('prescription-upload')?.click()">
            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-on-primary transition-all">
                <span class="material-symbols-outlined text-3xl">description</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 group-hover:text-primary transition-colors">Upload Prescription</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Snap or upload a handwritten prescription image.</p>
            <input type="file" id="prescription-upload" accept="image/*,.pdf" class="hidden" onchange="uploadPrescription(this)">
        </div>
        <div class="group bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/40 hover-lift cursor-pointer transition-all" onclick="document.getElementById('excel-upload')?.click()">
            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-on-primary transition-all">
                <span class="material-symbols-outlined text-3xl">table_chart</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 group-hover:text-primary transition-colors">Excel/CSV Upload</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">Upload a spreadsheet with product codes & quantities.</p>
            <input type="file" id="excel-upload" accept=".csv,.xlsx,.xls" class="hidden" onchange="uploadExcel(this)">
        </div>
    </div>
</section>

<?php if (!isset($_SESSION['user_id'])): ?>
<section class="px-base md:px-margin-desktop py-section-gap max-w-container-max mx-auto text-center">
    <div class="bg-surface-container-lowest rounded-2xl p-12 border border-outline-variant/40 max-w-lg mx-auto">
        <span class="text-5xl mb-4 inline-block">🔒</span>
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Login Required</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mb-6">Please sign in to your B2B account to place bulk orders, save drafts, and track your order history.</p>
        <a href="<?= BASE_URL ?>/login.php" class="inline-block bg-primary text-on-primary px-8 py-3 rounded-xl font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95">Login / Register</a>
    </div>
</section>
<?php else: ?>

<?php
$db = getDB();
$userId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Security validation failed. Please try again.';
        $messageType = 'error';
    } else {
        $db->begin_transaction();
        try {
            $orderNumber = 'OP-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM order_punch WHERE order_number = ?");
            $checkStmt->bind_param('s', $orderNumber);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();
            if ($count > 0) {
                $orderNumber = 'OP-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }

            $source = $_POST['source'] ?? 'manual';
            $notes = trim($_POST['notes'] ?? '');
            $deliveryDate = !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
            $saveAsDraft = isset($_POST['save_draft']) ? 'draft' : 'pending';

            $productIds = $_POST['product_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $prices = $_POST['price'] ?? [];

            if (empty($productIds) || !is_array($productIds)) {
                throw new Exception('Please add at least one product to the order.');
            }

            $totalAmount = 0;
            $items = [];
            foreach ($productIds as $idx => $pid) {
                $pid = (int)$pid;
                $qty = max(1, (int)($quantities[$idx] ?? 1));
                $price = max(0, (float)($prices[$idx] ?? 0));
                if ($pid <= 0 || $qty <= 0) continue;
                $lineTotal = $price * $qty;
                $totalAmount += $lineTotal;
                $items[] = [
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $lineTotal
                ];
            }

            if (empty($items)) {
                throw new Exception('Please add at least one valid product to the order.');
            }

            $gstPercent = (float)($_POST['gst_percent'] ?? 0);
            $gstAmount = $totalAmount * ($gstPercent / 100);
            $discount = max(0, (float)($_POST['discount'] ?? 0));
            $netAmount = $totalAmount + $gstAmount - $discount;

            $stmt = $db->prepare("INSERT INTO order_punch (user_id, order_number, order_type, source, total_amount, discount, gst_amount, net_amount, status, notes, delivery_date, created_at) VALUES (?, ?, 'retail', ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('issdddsss', $userId, $orderNumber, $source, $totalAmount, $discount, $gstAmount, $netAmount, $saveAsDraft, $notes, $deliveryDate);
            $stmt->execute();
            $orderPunchId = $db->insert_id;
            $stmt->close();

            $itemStmt = $db->prepare("INSERT INTO order_punch_items (order_punch_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->bind_param('iidd', $orderPunchId, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
                $itemStmt->execute();
            }
            $itemStmt->close();

            $db->commit();

            $message = $saveAsDraft === 'draft'
                ? 'Draft saved successfully! Order #' . htmlspecialchars($orderNumber)
                : 'Order placed successfully! Order #' . htmlspecialchars($orderNumber);
            $messageType = 'success';

            echo '<script>window.location.hash = "recent-orders";</script>';
        } catch (Exception $e) {
            $db->rollback();
            error_log("Order Punch Error: " . $e->getMessage());
            $message = 'Failed to save order: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repeat_order' && isset($_POST['repeat_order_id'])) {
    $repeatOrderId = (int)$_POST['repeat_order_id'];
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $message = 'Security validation failed.';
        $messageType = 'error';
    } else {
        $orig = $db->prepare("SELECT * FROM order_punch WHERE id = ? AND user_id = ?");
        $orig->bind_param('ii', $repeatOrderId, $userId);
        $orig->execute();
        $origOrder = $orig->get_result()->fetch_assoc();
        $orig->close();

        if ($origOrder) {
            $itemsResult = $db->prepare("SELECT pi.*, p.name as product_name FROM order_punch_items pi LEFT JOIN products p ON pi.product_id = p.id WHERE pi.order_punch_id = ?");
            $itemsResult->bind_param('i', $repeatOrderId);
            $itemsResult->execute();
            $origItems = $itemsResult->get_result()->fetch_all(MYSQLI_ASSOC);
            $itemsResult->close();

            $existingItems = [];
            foreach ($origItems as $item) {
                $existingItems[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'] ?? ('Product #' . $item['product_id']),
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ];
            }
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemsData = <?= json_encode($existingItems) ?>;
                itemsData.forEach(function(item) {
                    addOrderItem(item.product_id, item.product_name, item.quantity, item.price);
                });
                document.getElementById('order-form')?.scrollIntoView({ behavior: 'smooth' });
            });
            </script>
            <?php
            $message = 'Order items loaded from #' . htmlspecialchars($origOrder['order_number']) . ' — review and submit below.';
            $messageType = 'info';
        }
    }
}

$products = [];
try {
    $prodResult = $db->query("SELECT id, name, price, stock FROM products WHERE stock > 0 OR stock IS NULL ORDER BY name ASC LIMIT 500");
    if ($prodResult) {
        $products = $prodResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Product fetch error: " . $e->getMessage());
}

if (empty($products)) {
    $products = [
        ['id' => 0, 'name' => 'Ashwagandha Vitality', 'price' => 24.99, 'stock' => 100],
        ['id' => 0, 'name' => 'Triphala Digestive', 'price' => 18.50, 'stock' => 85],
        ['id' => 0, 'name' => 'Golden Milk Blend', 'price' => 22.00, 'stock' => 120],
        ['id' => 0, 'name' => 'Herbal Hair Oil', 'price' => 15.99, 'stock' => 200],
        ['id' => 0, 'name' => 'Shilajit Resin', 'price' => 34.99, 'stock' => 55],
        ['id' => 0, 'name' => 'Neem Capsules', 'price' => 12.50, 'stock' => 90],
        ['id' => 0, 'name' => 'Brahmi Boost Syrup', 'price' => 19.99, 'stock' => 70],
        ['id' => 0, 'name' => 'Chyawanprash Original', 'price' => 28.00, 'stock' => 150],
    ];
}

$recentOrders = [];
try {
    $roStmt = $db->prepare("SELECT op.*, COUNT(opi.id) as item_count FROM order_punch op LEFT JOIN order_punch_items opi ON opi.order_punch_id = op.id WHERE op.user_id = ? GROUP BY op.id ORDER BY op.created_at DESC LIMIT 20");
    $roStmt->bind_param('i', $userId);
    $roStmt->execute();
    $recentOrders = $roStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $roStmt->close();
} catch (Exception $e) {
    error_log("Recent orders fetch error: " . $e->getMessage());
}
?>

<section id="order-form" class="px-base md:px-margin-desktop py-section-gap max-w-container-max mx-auto scroll-mt-28">
    <?php if ($message): ?>
    <div class="mb-6 px-6 py-4 rounded-xl text-sm font-medium flex items-center gap-3 <?= $messageType === 'error' ? 'bg-error-container text-on-error-container border border-error/30' : ($messageType === 'info' ? 'bg-secondary-container text-on-secondary-container border border-secondary/30' : 'bg-primary/10 text-primary border border-primary/30') ?>">
        <span class="material-symbols-outlined"><?= $messageType === 'error' ? 'error' : ($messageType === 'info' ? 'info' : 'check_circle') ?></span>
        <?= htmlspecialchars($message) ?>
        <button class="ml-auto opacity-50 hover:opacity-100" onclick="this.parentElement.remove()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-gutter">
        <div class="flex-1">
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-6 lg:p-8">
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-1">Create Order</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">Search and add products to build your bulk order</p>

                <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>#order-form" id="orderForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="place_order">
                    <input type="hidden" name="source" value="manual">

                    <div class="mb-6">
                        <label for="product-search" class="font-label-lg text-label-lg text-on-surface block mb-2">Search Products</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">search</span>
                            <input type="text" id="product-search" class="w-full bg-surface-container-low border border-outline-variant rounded-xl pl-12 pr-4 py-3.5 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Type medicine name..." autocomplete="off">
                            <div id="search-suggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-surface-container-lowest border border-outline-variant/40 rounded-xl shadow-lg z-40 max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>

                    <div id="order-items">
                        <div class="text-center py-8 text-on-surface-variant font-body-md text-body-md">
                            <span class="material-symbols-outlined text-primary text-4xl block mb-2">add_shopping_cart</span>
                            No items added yet. Start typing above to search and add products.
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex flex-wrap gap-4 items-end">
                            <div>
                                <label class="font-label-lg text-label-lg text-on-surface block mb-1">Discount (₹)</label>
                                <input type="number" name="discount" id="order-discount" value="0" min="0" step="0.01" class="w-32 bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                            <div>
                                <label class="font-label-lg text-label-lg text-on-surface block mb-1">GST (%)</label>
                                <input type="number" name="gst_percent" id="order-gst" value="0" min="0" max="100" step="0.1" class="w-32 bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <label class="font-label-lg text-label-lg text-on-surface block mb-1">Delivery Date</label>
                                <input type="date" name="delivery_date" id="order-delivery" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="font-label-lg text-label-lg text-on-surface block mb-1">Order Notes</label>
                            <textarea name="notes" rows="2" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none" placeholder="Any special instructions..."></textarea>
                        </div>
                    </div>

                    <div class="mt-8 p-5 bg-surface-container rounded-xl">
                        <div class="flex justify-between items-center font-body-lg text-body-lg text-on-surface">
                            <span>Subtotal</span>
                            <span id="order-subtotal">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center font-body-md text-body-md text-on-surface-variant mt-1">
                            <span>GST (<span id="gst-label">0</span>%)</span>
                            <span id="order-gst-amount">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center font-body-md text-body-md text-on-surface-variant mt-1">
                            <span>Discount</span>
                            <span id="order-discount-display">-₹0.00</span>
                        </div>
                        <hr class="my-3 border-outline-variant/50">
                        <div class="flex justify-between items-center font-headline-md text-headline-md text-primary">
                            <span>Total</span>
                            <span id="order-total">₹0.00</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-6">
                        <button type="submit" name="place_order" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span> Place Order
                        </button>
                        <button type="submit" name="save_draft" class="bg-surface-container-high text-on-surface px-8 py-3 rounded-xl font-label-lg text-label-lg hover:bg-surface-container-highest transition-all active:scale-95 flex items-center gap-2 border border-outline-variant/40">
                            <span class="material-symbols-outlined">draft</span> Save as Draft
                        </button>
                        <button type="button" onclick="clearOrderForm()" class="text-on-surface-variant px-6 py-3 rounded-xl font-label-lg text-label-lg hover:bg-surface-container transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined">delete_sweep</span> Clear All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:w-80">
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-6 lg:p-8 sticky top-32">
                <h3 class="font-title-lg text-title-lg text-on-surface mb-4">Quick Tips</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">check_circle</span>
                        <span>Minimum order: ₹500 for B2B pricing</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">check_circle</span>
                        <span>Free delivery on orders above ₹2,000</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">check_circle</span>
                        <span>Save drafts and submit later</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">check_circle</span>
                        <span>Upload prescription for Schedule H drugs</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">check_circle</span>
                        <span>Bulk discounts available for 50+ units</span>
                    </li>
                </ul>
                <hr class="my-5 border-outline-variant/30">
                <div class="text-center">
                    <span class="material-symbols-outlined text-primary text-4xl">medication</span>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-2">Need help? Call our B2B helpline</p>
                    <a href="tel:+919999999999" class="font-title-lg text-title-lg text-primary hover:underline">+91 99999 99999</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="recent-orders" class="px-base md:px-margin-desktop py-section-gap max-w-container-max mx-auto scroll-mt-28">
    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Recent Orders</h2>
    <p class="font-body-md text-body-md text-on-surface-variant mb-8">Your B2B order history</p>

    <?php if (empty($recentOrders)): ?>
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-12 text-center">
        <span class="material-symbols-outlined text-primary text-5xl block mb-4">inventory_2</span>
        <h3 class="font-headline-md text-headline-md text-on-surface mb-2">No orders yet</h3>
        <p class="font-body-md text-body-md text-on-surface-variant">Place your first B2B order above.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full bg-surface-container-lowest rounded-2xl border border-outline-variant/40 overflow-hidden">
            <thead>
                <tr class="bg-surface-container text-left">
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Order #</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Date</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Items</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Amount</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Source</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Status</th>
                    <th class="font-label-lg text-label-lg text-on-surface px-5 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <?php foreach ($recentOrders as $order): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-5 py-4 font-label-lg text-label-lg text-primary"><?= htmlspecialchars($order['order_number']) ?></td>
                    <td class="px-5 py-4 font-body-md text-body-md text-on-surface-variant"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                    <td class="px-5 py-4 font-body-md text-body-md text-on-surface"><?= (int)$order['item_count'] ?> items</td>
                    <td class="px-5 py-4 font-body-md text-body-md text-on-surface font-medium">₹<?= number_format((float)$order['net_amount'], 2) ?></td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium capitalize bg-surface-container text-on-surface-variant border border-outline-variant/30">
                            <?= htmlspecialchars($order['source']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <?php
                        $statusClasses = [
                            'draft' => 'bg-surface-container text-on-surface-variant border border-outline-variant/30',
                            'pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                            'confirmed' => 'bg-green-50 text-green-700 border border-green-200',
                            'shipped' => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'delivered' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
                        ];
                        $statusLabel = $statusClasses[$order['status']] ?? 'bg-surface-container text-on-surface-variant border border-outline-variant/30';
                        ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium capitalize <?= $statusLabel ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>#order-form" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="repeat_order">
                            <input type="hidden" name="repeat_order_id" value="<?= (int)$order['id'] ?>">
                            <button type="submit" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3.5 py-2 rounded-lg font-label-sm text-label-sm hover:bg-primary/20 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-sm">repeat</span> Repeat
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php endif; ?>

<script>
const allProducts = <?= json_encode($products) ?>;
let orderItems = [];

function debounce(fn, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function renderSuggestions(query) {
    const container = document.getElementById('search-suggestions');
    if (!query || query.length < 1) {
        container.classList.add('hidden');
        return;
    }
    const q = query.toLowerCase();
    const matches = allProducts.filter(p =>
        p.name.toLowerCase().includes(q) || String(p.id).includes(q)
    ).slice(0, 10);

    if (matches.length === 0) {
        container.classList.add('hidden');
        return;
    }

    container.innerHTML = matches.map(p => `
        <div class="px-4 py-3 hover:bg-surface-container cursor-pointer border-b border-outline-variant/10 last:border-0 flex items-center justify-between transition-colors"
             onclick="addOrderItem(${p.id}, '${p.name.replace(/'/g, "\\'")}', 1, ${p.price}); document.getElementById('product-search').value = ''; document.getElementById('search-suggestions').classList.add('hidden');">
            <div>
                <div class="font-body-md text-body-md text-on-surface">${p.name}</div>
                <div class="font-label-sm text-label-sm text-on-surface-variant">₹${parseFloat(p.price).toFixed(2)} ${p.stock !== null ? '· Stock: ' + p.stock : ''}</div>
            </div>
            <span class="material-symbols-outlined text-primary">add_circle</span>
        </div>
    `).join('');
    container.classList.remove('hidden');
}

document.getElementById('product-search')?.addEventListener('input', debounce(function(e) {
    renderSuggestions(e.target.value);
}, 200));

document.addEventListener('click', function(e) {
    const container = document.getElementById('search-suggestions');
    const input = document.getElementById('product-search');
    if (container && input && !container.contains(e.target) && e.target !== input) {
        container.classList.add('hidden');
    }
});

document.getElementById('product-search')?.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('search-suggestions')?.classList.add('hidden');
    }
});

function addOrderItem(productId, productName, quantity, price) {
    const idx = orderItems.findIndex(item => item.product_id === productId && productId > 0);
    if (idx >= 0 && productId > 0) {
        orderItems[idx].quantity += quantity;
    } else {
        orderItems.push({
            product_id: productId,
            product_name: productName || 'Custom Item',
            quantity: quantity || 1,
            price: parseFloat(price) || 0
        });
    }
    renderOrderItems();
    updateTotals();
}

function removeOrderItem(index) {
    orderItems.splice(index, 1);
    renderOrderItems();
    updateTotals();
}

function updateItemQty(index, qty) {
    orderItems[index].quantity = Math.max(1, parseInt(qty) || 1);
    updateTotals();
}

function updateItemPrice(index, price) {
    orderItems[index].price = Math.max(0, parseFloat(price) || 0);
    updateTotals();
}

function renderOrderItems() {
    const container = document.getElementById('order-items');
    if (orderItems.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-on-surface-variant font-body-md text-body-md"><span class="material-symbols-outlined text-primary text-4xl block mb-2">add_shopping_cart</span>No items added yet. Start typing above to search and add products.</div>';
        return;
    }
    container.innerHTML = orderItems.map((item, idx) => `
        <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-xl mb-2 order-item-row" data-index="${idx}">
            <div class="flex-1 min-w-0">
                <div class="font-body-md text-body-md text-on-surface truncate">${item.product_name}</div>
                <input type="hidden" name="product_id[]" value="${item.product_id}">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div>
                    <label class="font-label-sm text-label-sm text-on-surface-variant block text-xs">Qty</label>
                    <input type="number" name="quantity[]" value="${item.quantity}" min="1"
                           class="w-16 bg-surface-container-lowest border border-outline-variant rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all"
                           onchange="updateItemQty(${idx}, this.value)">
                </div>
                <div>
                    <label class="font-label-sm text-label-sm text-on-surface-variant block text-xs">Price</label>
                    <input type="number" name="price[]" value="${item.price.toFixed(2)}" min="0" step="0.01"
                           class="w-20 bg-surface-container-lowest border border-outline-variant rounded-lg px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all"
                           onchange="updateItemPrice(${idx}, this.value)">
                </div>
                <div class="text-right min-w-[70px]">
                    <div class="font-body-md text-body-md text-on-surface font-medium">₹${(item.quantity * item.price).toFixed(2)}</div>
                </div>
                <button type="button" onclick="removeOrderItem(${idx})" class="text-on-surface-variant hover:text-error transition-colors p-1">
                    <span class="material-symbols-outlined text-lg">remove_circle</span>
                </button>
            </div>
        </div>
    `).join('');
}

function updateTotals() {
    let subtotal = 0;
    orderItems.forEach(item => {
        subtotal += item.quantity * item.price;
    });

    const gstPercent = parseFloat(document.getElementById('order-gst')?.value) || 0;
    const discount = parseFloat(document.getElementById('order-discount')?.value) || 0;
    const gstAmount = subtotal * (gstPercent / 100);
    const total = subtotal + gstAmount - discount;

    document.getElementById('order-subtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('gst-label').textContent = gstPercent;
    document.getElementById('order-gst-amount').textContent = '₹' + gstAmount.toFixed(2);
    document.getElementById('order-discount-display').textContent = '-₹' + discount.toFixed(2);
    document.getElementById('order-total').textContent = '₹' + Math.max(0, total).toFixed(2);
}

document.getElementById('order-gst')?.addEventListener('input', updateTotals);
document.getElementById('order-discount')?.addEventListener('input', updateTotals);

function clearOrderForm() {
    if (orderItems.length === 0) return;
    if (!confirm('Clear all items from the order?')) return;
    orderItems = [];
    renderOrderItems();
    updateTotals();
}

function uploadPrescription(input) {
    if (!input.files || !input.files[0]) return;
    const formData = new FormData();
    formData.append('prescription', input.files[0]);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token']) ?>');
    formData.append('action', 'upload_prescription');

    fetch('<?= BASE_URL ?>/order-punch.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Prescription uploaded successfully! Our team will process it shortly.');
        } else {
            alert('Upload failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(() => alert('Upload failed. Please try again.'))
    .finally(() => { input.value = ''; });
}

function uploadExcel(input) {
    if (!input.files || !input.files[0]) return;
    const formData = new FormData();
    formData.append('excel', input.files[0]);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token']) ?>');
    formData.append('action', 'upload_excel');

    fetch('<?= BASE_URL ?>/order-punch.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.items) {
            data.items.forEach(item => {
                addOrderItem(item.product_id || 0, item.product_name, item.quantity || 1, item.price || 0);
            });
            document.getElementById('order-form')?.scrollIntoView({ behavior: 'smooth' });
        } else {
            alert('Upload processed: ' + (data.message || 'Done'));
        }
    })
    .catch(() => alert('Upload failed. Please try again.'))
    .finally(() => { input.value = ''; });
}

<?php if (!empty($_SESSION['user_id'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryInput = document.getElementById('order-delivery');
    if (deliveryInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        deliveryInput.value = tomorrow.toISOString().split('T')[0];
        deliveryInput.min = tomorrow.toISOString().split('T')[0];
    }
});
<?php endif; ?>

document.getElementById('orderForm')?.addEventListener('submit', function(e) {
    if (orderItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one product to the order.');
        return false;
    }
});
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $csrf = $_POST['csrf_token'] ?? '';

    if ($action === 'upload_prescription' && isset($_FILES['prescription'])) {
        header('Content-Type: application/json');
        try {
            if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
                throw new Exception('Invalid security token');
            }
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Login required');
            }

            $uploadDir = __DIR__ . '/assets/uploads/prescriptions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $_FILES['prescription'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, PDF');
            }

            $fileName = 'prescription_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new Exception('Failed to save file');
            }

            $stmt = $db->prepare("INSERT INTO uploaded_prescriptions (user_id, file_path, file_type, notes, status) VALUES (?, ?, ?, ?, 'pending')");
            $notes = trim($_POST['prescription_notes'] ?? '');
            $stmt->bind_param('iss', $userId, $destPath, $ext, $notes);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Prescription uploaded']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'upload_excel' && isset($_FILES['excel'])) {
        header('Content-Type: application/json');
        try {
            if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
                throw new Exception('Invalid security token');
            }
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Login required');
            }

            $file = $_FILES['excel'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                throw new Exception('Invalid file type. Allowed: CSV, XLSX, XLS');
            }

            $uploadDir = __DIR__ . '/assets/uploads/excel/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'bulk_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $uploadDir . $fileName;
            move_uploaded_file($file['tmp_name'], $destPath);

            if ($ext === 'csv') {
                $handle = fopen($destPath, 'r');
                $parsedItems = [];
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 2) continue;
                    $prodName = trim($row[0]);
                    $qty = max(1, (int)($row[1] ?? 1));
                    $price = 0;

                    foreach ($products as $p) {
                        if (stripos($p['name'], $prodName) !== false || strtolower($p['name']) === strtolower($prodName)) {
                            $price = (float)$p['price'];
                            $parsedItems[] = [
                                'product_id' => (int)$p['id'],
                                'product_name' => $p['name'],
                                'quantity' => $qty,
                                'price' => $price
                            ];
                            break;
                        }
                    }
                    if ($price == 0) {
                        $parsedItems[] = [
                            'product_id' => 0,
                            'product_name' => $prodName,
                            'quantity' => $qty,
                            'price' => 0
                        ];
                    }
                }
                fclose($handle);
                echo json_encode(['success' => true, 'items' => $parsedItems, 'message' => count($parsedItems) . ' products loaded from CSV']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Excel file uploaded. Our team will process it.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
