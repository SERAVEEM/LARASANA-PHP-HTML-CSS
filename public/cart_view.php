<?php
// cart_view.php
// returns JSON { items: [...] }

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Cart.php';

// jika user belum login, kembalikan kosong
if (!isset($_SESSION['user'])) {
    echo json_encode(['items' => []]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$items = Cart::getUserCart($user_id);

// pastikan returned items mengandung fields: id, name, price, image, quantity (or qty)
echo json_encode(['items' => $items]);
exit;
