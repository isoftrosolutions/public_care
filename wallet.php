<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Wallet';
$balance = 0;
$transactions = [];
if (table_exists($db, 'wallets')) {
    $stmt = $db->prepare('SELECT * FROM wallets WHERE user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $balance = (float)($wallet['balance'] ?? 0);
    if ($wallet && table_exists($db, 'wallet_transactions')) {
        $stmt = $db->prepare('SELECT * FROM wallet_transactions WHERE wallet_id = ? ORDER BY created_at DESC LIMIT 50');
        $stmt->bind_param('i', $wallet['id']);
        $stmt->execute();
        $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} elseif (column_exists($db, 'users', 'wallet_balance')) {
    $balance = (float)(current_user($db)['wallet_balance'] ?? 0);
}
if (!$transactions) {
    $transactions = [
        ['type' => 'credit', 'amount' => 250, 'source' => 'cashback', 'description' => 'Cashback - Monsoon Sale', 'created_at' => date('Y-m-d H:i:s')],
        ['type' => 'debit', 'amount' => 499, 'source' => 'payment', 'description' => 'Order #ORD-1234 payment', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['type' => 'credit', 'amount' => 750, 'source' => 'refund', 'description' => 'Order refund credited', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
    ];
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="rounded-2xl bg-gradient-to-br from-primary to-[#003818] p-8 text-white">
    <p class="text-white/70">Available Balance</p>
    <h1 class="mt-2 font-display-lg text-5xl"><?= money($balance ?: 1250) ?></h1>
    <p class="mt-2 text-white/80">Get 5% cashback on wallet payments</p>
    <div class="mt-6 flex flex-wrap gap-3"><button data-modal="add-money" class="rounded-lg bg-white px-5 py-3 font-label-lg text-primary">Add Money</button><button data-modal="send-bank" class="rounded-lg border border-white px-5 py-3 font-label-lg text-white">Send to Bank</button></div>
</div>
<div class="mt-8 grid gap-4 md:grid-cols-3">
    <?= stat_card('redeem', 'Total Cashback Earned', '₹350', '+5%') ?>
    <?= stat_card('savings', 'Total Saved', '₹520', 'Wallet') ?>
    <?= stat_card('local_offer', 'Active Offers', '3', 'Live') ?>
</div>
<div class="mt-10 rounded-xl border border-outline-variant bg-surface-container-lowest">
    <div class="flex flex-col gap-4 border-b border-outline-variant p-6 md:flex-row md:items-center md:justify-between">
        <h2 class="font-headline-md text-headline-md text-primary">Transactions</h2>
        <div class="flex flex-wrap gap-2" data-tabs>
            <?php foreach (['All','Credit','Debit','Cashback','Refund'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 text-label-lg first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?>
        </div>
    </div>
    <div class="divide-y divide-outline-variant">
        <?php foreach ($transactions as $tx): $credit = ($tx['type'] ?? '') === 'credit'; ?>
        <button data-detail="<?= h($tx['description'] ?? ucfirst($tx['source'] ?? 'Transaction')) ?>" class="flex w-full items-center gap-4 p-5 text-left hover:bg-surface-container">
            <span class="material-symbols-outlined rounded-full <?= $credit ? 'bg-primary-fixed text-primary' : 'bg-error-container text-error' ?> p-3"><?= $credit ? 'arrow_upward' : 'arrow_downward' ?></span>
            <span class="flex-1"><strong><?= h($tx['description'] ?? ucfirst($tx['source'] ?? 'Transaction')) ?></strong><br><span class="text-label-sm text-on-surface-variant"><?= date('d M Y, h:i A', strtotime($tx['created_at'] ?? 'now')) ?></span></span>
            <span class="font-title-lg <?= $credit ? 'text-primary' : 'text-error' ?>"><?= $credit ? '+' : '-' ?><?= money($tx['amount'] ?? 0) ?></span>
            <span class="hidden rounded-full bg-primary-fixed px-3 py-1 text-label-sm text-primary sm:inline">Successful</span>
        </button>
        <?php endforeach; ?>
    </div>
</div>
</section>
<div id="wallet-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg rounded-xl bg-surface-container-lowest p-6">
        <div class="flex justify-between"><h3 id="wallet-modal-title" class="font-headline-md text-headline-md text-primary">Add Money</h3><button onclick="closeWalletModal()"><span class="material-symbols-outlined">close</span></button></div>
        <div id="add-money" class="mt-5 space-y-4"><div class="flex flex-wrap gap-2"><?php foreach ([100,200,500,1000,2000] as $amount): ?><button class="rounded-full bg-surface-container px-4 py-2">₹<?= $amount ?></button><?php endforeach; ?></div><input class="w-full rounded-lg border-outline-variant" placeholder="Custom amount"><select class="w-full rounded-lg border-outline-variant"><option>UPI</option><option>Card</option><option>Net Banking</option></select><button class="w-full rounded-lg bg-primary py-3 text-on-primary">Add to Wallet</button></div>
        <div id="send-bank" class="mt-5 hidden space-y-4"><input class="w-full rounded-lg border-outline-variant" placeholder="Amount"><input class="w-full rounded-lg border-outline-variant" placeholder="Account number"><input class="w-full rounded-lg border-outline-variant" placeholder="IFSC code"><button class="w-full rounded-lg bg-primary py-3 text-on-primary">Initiate Transfer</button><p class="text-label-sm text-on-surface-variant">Transfers take 1-3 business days.</p></div>
    </div>
</div>
<script>
document.querySelectorAll('[data-modal]').forEach(button => button.addEventListener('click', () => { document.getElementById('wallet-modal').classList.remove('hidden'); document.getElementById('wallet-modal').classList.add('flex'); document.getElementById('add-money').classList.toggle('hidden', button.dataset.modal !== 'add-money'); document.getElementById('send-bank').classList.toggle('hidden', button.dataset.modal !== 'send-bank'); document.getElementById('wallet-modal-title').textContent = button.dataset.modal === 'add-money' ? 'Add Money' : 'Send to Bank'; }));
function closeWalletModal(){ document.getElementById('wallet-modal').classList.add('hidden'); document.getElementById('wallet-modal').classList.remove('flex'); }
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
