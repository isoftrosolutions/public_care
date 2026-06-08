<?php
require_once __DIR__ . '/helpers.php';

$conn = getDB();
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $items = [];
    if ($user_id) {
        $result = $conn->query("
            SELECT c.*, p.name, p.price, p.image_url, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $user_id
            ORDER BY c.created_at DESC
        ");
    } else {
        $cart = $_SESSION['cart'] ?? [];
        if (!empty($cart)) {
            $ids = implode(',', array_map('intval', array_keys($cart)));
            $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
            $items = [];
            if ($result) {
                while ($p = $result->fetch_assoc()) {
                    $qty = (int)($cart[$p['id']] ?? 1);
                    $items[] = [
                        'product_id' => (int)$p['id'],
                        'quantity' => $qty,
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'image_url' => $p['image_url'] ?: '',
                        'stock' => (int)$p['stock'],
                    ];
                }
            }
        }
    }

    if ($user_id && isset($result) && $result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'cart_id' => (int)$row['id'],
                'product_id' => (int)$row['product_id'],
                'quantity' => (int)$row['quantity'],
                'name' => $row['name'],
                'price' => (float)$row['price'],
                'image_url' => $row['image_url'] ?: '',
                'stock' => (int)$row['stock'],
            ];
        }
    }

    $total = array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['quantity']), 0.0);
    $count = array_reduce($items, fn($sum, $i) => $sum + $i['quantity'], 0);

    jsonResponse(['success' => true, 'data' => $items, 'total' => $total, 'count' => $count]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product_id = (int)($input['product_id'] ?? $_GET['id'] ?? 0);
    $action = $input['action'] ?? 'add';

    if ($product_id <= 0) {
        jsonError('Product ID is required.', 400);
    }

    $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        jsonError('Product not found.', 404);
    }

    if ($user_id) {
        if ($action === 'delete') {
            $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id");
            jsonResponse(['success' => true, 'message' => 'Item removed.']);
        }

        $existing = $conn->query("SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        $existing_row = $existing->fetch_assoc();

        if ($action === 'remove') {
            if ($existing_row && $existing_row['quantity'] > 1) {
                $new_qty = (int)$existing_row['quantity'] - 1;
                $conn->query("UPDATE cart SET quantity = $new_qty WHERE id = {$existing_row['id']}");
            } else {
                $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id");
            }
            jsonResponse(['success' => true, 'message' => 'Quantity updated.']);
        }

        if ($existing_row) {
            $new_qty = (int)$existing_row['quantity'] + 1;
            $conn->query("UPDATE cart SET quantity = $new_qty WHERE id = {$existing_row['id']}");
        } else {
            $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
        }
    } else {
        if ($action === 'delete') {
            unset($_SESSION['cart'][$product_id]);
        } elseif ($action === 'remove') {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]--;
                if ($_SESSION['cart'][$product_id] <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        } else {
            $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
        }
    }

    $_SESSION['cart_count'] = $user_id
        ? ($conn->query("SELECT SUM(quantity) as c FROM cart WHERE user_id = $user_id")->fetch_assoc()['c'] ?? 0)
        : array_sum($_SESSION['cart'] ?? []);

    jsonResponse(['success' => true, 'cart_count' => (int)$_SESSION['cart_count']]);
}
