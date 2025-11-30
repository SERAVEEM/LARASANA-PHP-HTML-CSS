<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'];
?>
<!doctype html><body>
<h1>Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name']) ?> (Role: <?= htmlspecialchars($user['role']) ?>)</p>
<?php if($user['role'] === 'admin'): ?>
  <p><a href="/admin/products.php">Manage products (admin)</a></p>
<?php endif; ?>
<p><a href="/">Home</a></p>
</body></html>
