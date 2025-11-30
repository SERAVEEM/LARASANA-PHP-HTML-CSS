<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';    
require_once __DIR__ . '/../src/models/Cart.php';
require_once __DIR__ . '/../src/middleware/auth.php';

require_auth(); // buat mastiin login dulu

// Only allow adding to cart if user is logged in
if (!isset($_SESSION['user'])) {
    die("Anda harus login untuk menambah ke keranjang.");
}

$user_id = $_SESSION['user']['id'];
$product_id = $_POST['product_id']?? $_GET ['product_id'];

if(!$product_id) {
    die ("product ID ilang njir");
}

Cart::addItem($user_id, $product_id);

header('Location: /cart.php');
exit;
?>

