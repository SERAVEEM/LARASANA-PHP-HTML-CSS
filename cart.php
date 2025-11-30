<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/models/Cart.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$items = Cart::getUserCart($_SESSION['user_id']);
?>

<h2>Your Shopping Cart</h2>

<?php foreach ($items as $item): ?>
<div style="display:flex; gap:20px; margin-bottom:20px;">
    <img src="/uploads/products/<?= $item['image'] ?>" width="80">
    <div>
        <h3><?= $item['name'] ?></h3>
        <p>Price: Rp<?= number_format($item['price']) ?></p>
        <p>Qty: <?= $item['quantity'] ?></p>
    </div>
</div>
<?php endforeach;
?>