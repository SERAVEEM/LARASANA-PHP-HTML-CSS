<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/middleware/auth.php';
require_once __DIR__ . '/../../src/models/Product.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'description' => trim($_POST['description'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null
    ];

    // ----- VALIDASI -----
    if (!$data['name'] || !$data['slug']) {
        $error = 'Name & slug required';
    } else {
        // ===== HANDLE FILE UPLOAD =====
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {

            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('prod_') . "." . strtolower($ext);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = "/uploads/products/" . $fileName;
            } else {
                $error = "Failed to upload file";
            }
        }

        if (!$error) {
            $data['image'] = $imagePath;
            Product::create($data);

            header("Location: /admin/products.php");
            exit;
        }
    }
}
?>
<!doctype html>
<body>
<h1>Create Product</h1>

<?php if ($error): ?>
  <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input name="name" placeholder="Name" required><br>
    <input name="slug" placeholder="Slug (unique)" required><br>
    <input name="price" placeholder="Price"><br>
    <textarea name="description" placeholder="Description"></textarea><br>

    <label>Product Image</label><br>
    <input type="file" name="image" accept="image/*"><br><br>

    <button>Create</button>
</form>

<p><a href="/admin/products.php">Back</a></p>
</body>
</html>
