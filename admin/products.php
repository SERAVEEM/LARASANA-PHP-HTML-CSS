<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/middleware/auth.php';
require_once __DIR__ . '/../../src/middleware/auth.php';
require_once __DIR__ . '/../../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// admin check
if(($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Forbidden'; 
    exit;
}




$products = Product::all();
?>
<!doctype html><body>
<h1>Admin - Products</h1>
<p><a href="/admin/product-create.php">Create product</a></p> 
<?php foreach($products as $p): ?>
  <div>
    <strong><?= htmlspecialchars($p['name']) ?></strong>
    <img src="/uploads/products/<?= htmlspecialchars($p['image']) ?>" width="80">
    <p>Category: <?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></p>
    <p>$<?= number_format($p['price'],2) ?></p>
    <p>
      <a href="/admin/product-edit.php?id=<?= $p['id'] ?>">Edit</a> |
      <a href="/admin/product-delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    </p>
  </div>
<?php endforeach; ?>
<p><a href="/dashboard.php">Back to dashboard</a></p>
</body></html>
