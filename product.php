<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /');
    exit;
}
$p = Product::find($id);
if (!$p) {
    echo 'Product not found';
    exit;
}
?>
<!doctype html><body>
<h1><?= htmlspecialchars($p['name']) ?></h1>
<p>Category: <?= htmlspecialchars($p['category']) ?></p>
<p>Price: $<?= number_format($p['price'],2) ?></p>
<p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
<p><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></p>
<p><a href="/">Back</a></p>
</body></html>
