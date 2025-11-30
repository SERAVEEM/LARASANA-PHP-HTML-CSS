<?php
// public/index.php - simple home that lists some products
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// var_dump($pdo);


$products = Product::all();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Larasana - Home</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <header>
    <nav>
      <a href="/">Home</a> |
      <?php if(isset($_SESSION['user'])): ?>
        Hello <?= htmlspecialchars($_SESSION['user']['name']) ?> |
        <a href="/dashboard.php">Dashboard</a> |
        <a href="/logout.php">Logout</a>
      <?php else: ?>
        <a href="/login.php">Login</a> |
        <a href="/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <h1>Featured products</h1>
    <div class="grid">
    <?php foreach($products as $p): ?>
      <div class="card">
        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <p><?= htmlspecialchars($p['category']) ?></p>
        <p>$<?= number_format($p['price'],2) ?></p>
        <p><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></p>
        <p><a href="/product.php?id=<?= $p['id'] ?>">View</a></p>
        <form method="post" action="/cart-add.php">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <button type="submit">Add to cart</button>
        </form>
      </div>
    <?php endforeach; ?>
    </div>
  </main>
</body>
</html>
