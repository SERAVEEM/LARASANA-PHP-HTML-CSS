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

$id = intval($_GET['id'] ?? 0);
$product = Product::find($id);

if (!$product) {
    echo "Product not found";
    exit;
}

$error = null;

// ===== DELETE IMAGE ONLY =====
if (isset($_POST['delete_image'])) {
    if ($product['image']) {
        $file = __DIR__ . '/../../public' . $product['image'];
        if (file_exists($file)) unlink($file);

        Product::update($id, ['image' => null]);
    }
    header("Location: product-edit.php?id=" . $id);
    exit;
}

// ===== UPDATE PRODUCT ===== 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_image'])) {

    $data = [
        'name' => trim($_POST['name']),
        'slug' => trim($_POST['slug']),
        'price' => floatval($_POST['price']),
        'description' => trim($_POST['description']),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
        'image' => $product['image']  // default image lama
    ];

    if (!$data['name'] || !$data['slug']) {
        $error = "Name & slug required";
    } else {
        // ===== Jika upload gambar baru =====
        if (!empty($_FILES['image']['name'])) {

            // Hapus gambar lama
            if ($product['image']) {
                $oldFile = __DIR__ . '/../../public' . $product['image'];
                if (file_exists($oldFile)) unlink($oldFile);
            }

            // Upload gambar baru
            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prod_') . "." . strtolower($ext);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = "/uploads/products/" . $filename;
            } else {
                $error = "Failed to upload image.";
            }
        }

        if (!$error) {
            Product::update($id, $data);
            header("Location: /admin/products.php");
            exit;
        }
    }
}
?>
<!doctype html>
<body>

<h1>Edit Product</h1>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <input name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Name" required><br>
    <input name="slug" value="<?= htmlspecialchars($product['slug']) ?>" placeholder="Slug" required><br>
    <input name="price" value="<?= htmlspecialchars($product['price']) ?>" placeholder="Price"><br>

    <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea><br>

    <label>Current Image:</label><br>

    <?php if ($product['image']): ?>
        <img src="<?= $product['image'] ?>" width="150" style="border:1px solid #ccc"><br>

        <button name="delete_image" value="1" style="margin-top:10px;color:red">
            Delete Image
        </button><br><br>
    <?php else: ?>
        <p>No image uploaded.</p>
    <?php endif; ?>

    <label>Upload New Image:</label><br>
    <input type="file" name="image" accept="image/*" id="imgInput"><br><br>

    <!-- Preview Gambar Baru -->
    <img id="preview" src="#" style="display:none;width:150px;border:1px solid #aaa">

    <br><br>
    <button>Update Product</button>
</form>

<p><a href="/admin/products.php">Back</a></p>

<script>
// ===== IMAGE PREVIEW =====
document.getElementById('imgInput').addEventListener('change', function(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>

</body>
</html>
<?php