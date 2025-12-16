<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wishlist Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: white; }
        .test { background: #2a2a2a; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        button { background: #987A01; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <h1>Wishlist System Debug</h1>

    <div class="test">
        <h3>1. Check Session</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p class="success">✓ User is logged in - ID: <?php echo $_SESSION['user_id']; ?></p>
        <?php else: ?>
            <p class="error">✗ User is NOT logged in</p>
            <p>You need to login first. <a href="login.php" style="color: #987A01;">Go to Login</a></p>
        <?php endif; ?>
    </div>

    <div class="test">
        <h3>2. Check Database Table</h3>
        <?php
        try {
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'wishlist'");
            if ($tableCheck->rowCount() > 0) {
                echo '<p class="success">✓ Wishlist table exists</p>';
                
                // Show table structure
                $structure = $pdo->query("DESCRIBE wishlist")->fetchAll();
                echo '<pre style="background: #1a1a1a; padding: 10px; border-radius: 5px; overflow: auto;">';
                print_r($structure);
                echo '</pre>';
            } else {
                echo '<p class="error">✗ Wishlist table does NOT exist</p>';
                echo '<p>Run this SQL:</p>';
                echo '<pre style="background: #1a1a1a; padding: 10px; border-radius: 5px;">
CREATE TABLE wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_wishlist (user_id, product_id)
);
</pre>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="test">
        <h3>3. Check Products Table</h3>
        <?php
        try {
            $products = $pdo->query("SELECT id, name FROM products LIMIT 3")->fetchAll();
            if (count($products) > 0) {
                echo '<p class="success">✓ Products found: ' . count($products) . '</p>';
                echo '<ul>';
                foreach ($products as $p) {
                    echo '<li>ID: ' . $p['id'] . ' - ' . htmlspecialchars($p['name']) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p class="error">✗ No products found</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="test">
        <h3>4. Check API File</h3>
        <?php
        $apiPath = __DIR__ . '/api/wishlist_handler.php';
        if (file_exists($apiPath)) {
            echo '<p class="success">✓ API file exists at: ' . $apiPath . '</p>';
        } else {
            echo '<p class="error">✗ API file NOT found at: ' . $apiPath . '</p>';
            echo '<p>Expected location: your-project/api/wishlist_handler.php</p>';
        }
        ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="test">
        <h3>5. Test Wishlist Functions</h3>
        <p>Test with Product ID 1:</p>
        <button onclick="testAdd()">Test Add to Wishlist</button>
        <button onclick="testRemove()">Test Remove from Wishlist</button>
        <button onclick="testCheck()">Test Check Wishlist</button>
        <div id="result" style="margin-top: 15px; padding: 10px; background: #1a1a1a; border-radius: 5px;"></div>
    </div>
    <?php endif; ?>

    <script>
        function testAdd() {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', 1);

            fetch('api/wishlist_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<p class="error">Error: ' + error + '</p>';
            });
        }

        function testRemove() {
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', 1);

            fetch('api/wishlist_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<p class="error">Error: ' + error + '</p>';
            });
        }

        function testCheck() {
            const formData = new FormData();
            formData.append('action', 'check');
            formData.append('product_id', 1);

            fetch('api/wishlist_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<p class="error">Error: ' + error + '</p>';
            });
        }
    </script>
</body>
</html>
        