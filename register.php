<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/models/User.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill all fields';
    } elseif (User::findByEmail($email)) {
        $error = 'Email already used';
    } else {
        AuthController::register($name, $email, $password);
        header('Location: /login.php');
        exit;
    }
}
?>
<!doctype html><body>
<h2>Register</h2>
<?php if($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input name="name" placeholder="Name" required><br>
  <input name="email" placeholder="Email" required><br>
  <input name="password" type="password" placeholder="Password" required><br>
  <button>Register</button>
</form>
</body></html>
