<?php
require_once __DIR__ . '/includes/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0);
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

if ($product_id < 1) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $db = getDB();

    switch ($action) {
        case 'add':
            $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $stmt->bind_param('iiii', $uid, $product_id, $quantity, $quantity);
            $stmt->execute();
            break;
        case 'remove':
            $stmt = $db->prepare("UPDATE cart SET quantity = GREATEST(quantity - 1, 0) WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $uid, $product_id);
            $stmt->execute();
            $stmt2 = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ? AND quantity < 1");
            $stmt2->bind_param('ii', $uid, $product_id);
            $stmt2->execute();
            break;
        case 'delete':
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $uid, $product_id);
            $stmt->execute();
            break;
    }

    $count_result = $db->query("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = $uid");
    $_SESSION['cart_count'] = (int)$count_result->fetch_row()[0];
} else {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    switch ($action) {
        case 'add':
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            break;
        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]--;
                if ($_SESSION['cart'][$product_id] < 1) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;
        case 'delete':
            unset($_SESSION['cart'][$product_id]);
            break;
    }

    $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
}

header('Location: ' . BASE_URL . '/shopping-cart.php');
exit;
