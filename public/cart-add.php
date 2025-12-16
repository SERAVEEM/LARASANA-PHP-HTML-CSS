<?php
// cart_add.php
// POST: product_id
// returns JSON { success: true, items: [ ... ] }

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Cart.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/middleware/auth.php';

// pastikan user login (require_auth() jika ada)
if (function_exists('require_auth')) {
    require_auth();
}

// jika kamu menyimpan user di session['user'] berisi id
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'No product_id provided']);
    exit;
}

// cek product exist (opsional)
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// pakai model Cart (sesuaikan implementasi model Cart di src/models/Cart.php)
Cart::addItem($user_id, $product_id);

// ambil cart terbaru
$items = Cart::getUserCart($user_id);

// jika model mengembalikan fields berbeda, pastikan field: id, name, price, image, quantity
echo json_encode(['success' => true, 'items' => $items]);
exit;
