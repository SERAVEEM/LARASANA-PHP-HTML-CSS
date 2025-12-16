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

// default categories (ensure we have a mapping for preview)
$defaultCategories = [
    ['id' => 1, 'name' => 'Minimalist'],
    ['id' => 2, 'name' => 'Modern'],
    ['id' => 3, 'name' => 'Rustic'],
    ['id' => 4, 'name' => 'Traditional'],
];

$categories = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $categories = [];
}

// Merge defaults first, then DB categories if they don't use the same IDs
$categoryMap = [];
foreach ($defaultCategories as $c) {
    $categoryMap[(int)$c['id']] = $c['name'];
}
foreach ($categories as $c) {
    $id = (int)$c['id'];
    if (!isset($categoryMap[$id])) {
        $categoryMap[$id] = $c['name'];
    }
}

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
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Product — Admin</title>
    <style>
        /* reuse create's styling */
        :root {
            --gold: #987A01;
            --gold-opaque: rgba(152, 122, 1, 0.15);
            --bg: #0b0b0b;
            --card: #0d0d0d;
            --muted: rgba(255,255,255,0.7);
        }
        html,body{height:100%;margin:0;font-family: Inter, "Segoe UI", Roboto, system-ui, -apple-system; background:#070707;color:#fff;}
        a{color:inherit;text-decoration:none}

        .page-header { padding: 120px 150px 40px; max-width: 1400px; margin: 0 auto; }
        .page-title { font-family: "Playfair Display", serif; font-size: 48px; font-weight: 700; margin: 0 0 8px; color: #fff; }
        .page-subtitle { font-size: 16px; color: rgba(255, 255, 255, 0.6); max-width: 900px; margin: 0; line-height: 1.6; }

        .form-wrap { max-width: 1200px; margin: 40px auto 120px; padding: 28px; background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent); border-radius: 20px; border: 1px solid rgba(152, 122, 1, 0.08); box-shadow: 0 6px 30px rgba(0,0,0,0.6); display: grid; grid-template-columns: 1fr 420px; gap: 28px; padding-bottom: 36px; }
        .form-left { padding: 8px 6px; }
        .form-right { padding: 8px 6px; }
        label { display:block; font-weight:600; margin-bottom:8px; color:var(--muted); font-size:14px; }
        .input, textarea, select { width:100%; padding:12px 14px; background: transparent; border: 1px solid rgba(255,255,255,0.06); border-radius:12px; color: #fff; font-size: 15px; outline: none; transition: border 0.18s ease, box-shadow 0.18s ease; }
        .input:focus, textarea:focus, select:focus { border-color: var(--gold); box-shadow: 0 8px 30px rgba(152,122,1,0.06); }
        textarea { min-height:160px; resize:vertical; padding-top:12px; line-height:1.6; }
        .row { display:flex; gap:12px; }
        .col { flex:1; }
        .image-preview { width:100%; height:320px; background:#161616; border-radius:12px; overflow:hidden; display:flex; align-items:center; justify-content:center; border: 1px dashed rgba(255,255,255,0.04); position:relative; }
        .image-preview img { width:100%; height:100%; object-fit:cover; display:block; }
        .small-muted { font-size:13px; color:rgba(255,255,255,0.6); margin-top:6px; }
        .btn { display:inline-block; padding:12px 20px; border-radius:12px; font-weight:700; font-size:15px; cursor:pointer; border:2px solid rgba(152,122,1,0.25); background:transparent; color:var(--gold); transition: all 0.22s ease; }
        .btn:hover { background:var(--gold); color:white; transform:translateY(-3px); box-shadow:0 6px 18px rgba(152,122,1,0.12); }
        .btn-primary { background: var(--gold); color: #fff; border-color: var(--gold); }
        .btn-primary:hover { background:#b39601; }
        .meta { font-size:13px; color: rgba(255,255,255,0.5); margin-top:6px; }
        .actions { display:flex; gap:12px; align-items:center; margin-top:18px; }
        @media (max-width: 1100px) { .form-wrap { grid-template-columns: 1fr; } .page-header { padding: 80px 30px 30px; } }
        @media (max-width: 520px) { .image-preview { height:220px; } }
        .error { color:#ff6b6b; font-weight:700; margin-bottom:12px; }
        .back-link { color:rgba(255,255,255,0.7); font-weight:600; font-size:14px; }
        .top-left-back { position: fixed; top: 18px; left: 18px; z-index: 9999; display: inline-flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.06); padding: 10px 12px; color: #fff; border-radius: 10px; text-decoration: none; font-weight: 600; }
        .top-left-back:hover { background: rgba(255,255,255,0.02); }
    </style>
</head>
<body>
    <a href="/admin/products.php" class="top-left-back">← Back</a>
    <div class="page-header">
        <h1 class="page-title">Edit Product</h1>
        <p class="page-subtitle">Edit product details and update image. Fill fields below to update the product.</p>
    </div>

    <main class="form-wrap" role="main">
        <div class="form-left">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="editProductForm" method="post" enctype="multipart/form-data" novalidate>
                <div style="margin-bottom:18px;">
                    <label for="name">Product Name</label>
                    <input id="name" name="name" class="input" placeholder="e.g. Batik Tenun Modern" required value="<?= htmlspecialchars($product['name']) ?>">
                    <div class="small-muted">Gunakan nama singkat yang mudah dicari.</div>
                </div>

                <div style="display:flex;gap:12px;margin-bottom:18px;">
                    <div style="flex:1;">
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" class="input" placeholder="unique-slug" required value="<?= htmlspecialchars($product['slug']) ?>">
                        <div class="small-muted">Unique URL-friendly identifier (otomatis dibuat dari nama).</div>
                    </div>
                    <div style="width:160px;">
                        <label for="price">Price (USD)</label>
                        <input id="price" name="price" class="input" placeholder="e.g. 25.00" value="<?= htmlspecialchars($product['price']) ?>">
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="input" placeholder="Describe your product..."><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div style="margin-bottom:18px;">
                    <label for="category_id">Category ID</label>
                    <input id="category_id" name="category_id" class="input" type="number" min="1" placeholder="1 Minimalist | 2 Modern | 3 Rustic | 4 Traditional" value="<?= htmlspecialchars($product['category_id']) ?>">
                    <div class="small-muted">Masukkan ID kategori secara manual (1: Minimalist, 2: Modern, 3: Rustic, 4: Traditional). Kosongkan untuk tidak menggunakan kategori.</div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>

        <aside class="form-right">
            <label>Product Image</label>
            <div class="image-preview" id="previewBox">
                <!-- show existing preview or placeholder -->
                <?php if (!empty($product['image'])): ?>
                    <img id="previewImg" src="<?= htmlspecialchars($product['image']) ?>" alt="" style="display:block;">
                <?php else: ?>
                    <img id="previewImg" src="" alt="" style="display:none;">
                    <div id="previewPlaceholder" style="text-align:center;color:rgba(255,255,255,0.35);padding:18px;">
                        <div style="font-weight:700;font-size:18px;margin-bottom:8px;">No image selected</div>
                        <div class="meta">Recommended size: 1200x1200 — JPG/PNG</div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:12px;">
                <input id="image" name="image" type="file" accept="image/*" style="display:none;">
                <label for="image" class="btn" id="chooseBtn">Choose Image</label>
                <?php if (!empty($product['image'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete current image?');">
                        <button name="delete_image" value="1" class="btn" style="margin-left:8px;background:transparent;border:2px solid rgba(255,255,255,0.06);color:rgba(255,255,255,0.8);">Delete Image</button>
                    </form>
                <?php else: ?>
                    <button id="removeImageBtn" class="btn" style="display:none;margin-left:8px;background:transparent;border:2px solid rgba(255,255,255,0.06);color:rgba(255,255,255,0.8);">Remove</button>
                <?php endif; ?>
                <div class="small-muted">Klik "Choose Image" untuk memilih file dari perangkatmu. Preview otomatis akan muncul.</div>
            </div>

            <div style="margin-top:26px;">
                <label>Preview Info</label>
                <div class="meta">Nama: <span id="metaName"><?= htmlspecialchars($product['name']) ?></span></div>
                <div class="meta">Price: <span id="metaPrice"><?= htmlspecialchars($product['price']) ?></span></div>
                <div class="meta">Category: <span id="metaCategory"><?= htmlspecialchars($categoryMap[(int)$product['category_id']] ?? (empty($product['category_id']) ? '-' : 'ID: ' . intval($product['category_id']))) ?></span></div>
            </div>
        </aside>
    </main>

<script>
    // small helper: slugify
    function slugify(text){
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    const nameEl = document.getElementById('name');
    const slugEl = document.getElementById('slug');
    const priceEl = document.getElementById('price');
    const categoryEl = document.getElementById('category_id');
    const fileInput = document.getElementById('image');
    const previewBox = document.getElementById('previewBox');
    const previewImg = document.getElementById('previewImg');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const chooseBtn = document.getElementById('chooseBtn');
    const removeBtn = document.getElementById('removeImageBtn');

    nameEl && nameEl.addEventListener('input', (e) => {
        if (!slugEl.dataset.touched) {
            slugEl.value = slugify(e.target.value);
        }
        document.getElementById('metaName').innerText = e.target.value || '-';
    });

    slugEl && slugEl.addEventListener('input', () => { slugEl.dataset.touched = true; });
    priceEl && priceEl.addEventListener('input', () => { document.getElementById('metaPrice').innerText = priceEl.value || '-'; });

    var categoryMap = <?= json_encode($categoryMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    categoryEl && categoryEl.addEventListener('input', () => {
        const val = categoryEl.value.trim();
        if (!val) { document.getElementById('metaCategory').innerText = '-'; }
        else { const id = parseInt(val,10); document.getElementById('metaCategory').innerText = (categoryMap && categoryMap[id]) ? categoryMap[id] : ('ID: ' + id); }
    });

    chooseBtn && chooseBtn.addEventListener('click', (e) => { e.preventDefault(); fileInput.click(); });
    fileInput && fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        previewImg.src = url;
        previewImg.style.display = 'block';
        if (previewPlaceholder) previewPlaceholder.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'inline-block';
    });
    removeBtn && removeBtn.addEventListener('click', (e) => { e.preventDefault(); fileInput.value = ''; previewImg.src = ''; previewImg.style.display = 'none'; if (previewPlaceholder) previewPlaceholder.style.display = 'block'; removeBtn.style.display = 'none'; });

    const form = document.getElementById('editProductForm');
    form && form.addEventListener('submit', function(ev){
        const name = nameEl.value.trim();
        const slug = slugEl.value.trim();
        if (!name || !slug) { ev.preventDefault(); alert('Please fill product name and slug.'); return false; }
    });
</script>
</body>
</html>
<?php