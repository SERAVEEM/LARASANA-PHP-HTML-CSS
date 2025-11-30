<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// var_dump($pdo);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (AuthController::login($email, $password)) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html><body>
<h2>Login</h2>
<?php if($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?> 
<form method="post">
  <input name="email" placeholder="email" required><br>
  <input name="password" type="password" placeholder="password" required><br>
  <button>Login</button>
</form>
</body></html>
