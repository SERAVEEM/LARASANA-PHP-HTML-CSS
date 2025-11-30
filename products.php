<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$products = Product::all();
?>
<!doctype html><body>
<h1>All Products</h1>
<?php foreach($products as $p): ?>
  <div>
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p>$<?= number_format($p['price'],2) ?></p>
    <p><a href="/product.php?id=<?= $p['id'] ?>">View</a></p>
  </div>
<?php endforeach; ?>
<p><a href="/">Home</a></p>
</body></html>
