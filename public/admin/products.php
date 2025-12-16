<?php
require_once __DIR__ . '/../../config/database.php';
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larasana • Product Management</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0a;
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .title-font { 
            font-family: 'Gelasio', serif; 
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: #060606;
            border-right: 1px solid rgba(152, 122, 1, 0.2);
            padding: 40px 30px;
            z-index: 50;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.6s ease-out forwards;
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .sidebar-logo {
            width: 80px;
            margin-bottom: 50px;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-link {
            padding: 14px 20px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-link:hover {
            background: rgba(152, 122, 1, 0.1);
            color: #987A01;
        }

        .sidebar-link.active {
            background: rgba(152, 122, 1, 0.15);
            color: #987A01;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 40px;
            left: 30px;
            right: 30px;
        }

        /* Main content */
        .main-content {
            margin-left: 280px;
            padding: 50px 60px;
            min-height: 100vh;
        }

        /* Header animations */
        .page-header {
            margin-bottom: 50px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out 0.2s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-title {
            font-size: 48px;
            font-weight: 600;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #987A01 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Action bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 24px 30px;
            background: #0d0d0d;
            border-radius: 16px;
            border: 1px solid rgba(152, 122, 1, 0.1);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out 0.4s forwards;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #987A01;
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-primary {
            padding: 12px 28px;
            background: #987A01;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #7a6201;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(152, 122, 1, 0.3);
        }

        /* Product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        /* Product card */
        .product-card {
            background: #0d0d0d;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(152, 122, 1, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translateY(30px);
        }

        .product-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .product-card:hover {
            transform: translateY(-8px);
            border-color: rgba(152, 122, 1, 0.4);
            box-shadow: 0 15px 40px rgba(152, 122, 1, 0.15);
        }

        .product-image-wrap {
            position: relative;
            width: 100%;
            height: 240px;
            background: #1a1a1a;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-body {
            padding: 24px;
        }

        .product-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }

        .product-category {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(152, 122, 1, 0.15);
            color: #987A01;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 600;
            color: #987A01;
            margin-bottom: 20px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid;
        }

        .btn-edit {
            background: transparent;
            border-color: #987A01;
            color: #987A01;
        }

        .btn-edit:hover {
            background: #987A01;
            color: white;
        }

        .btn-delete {
            background: transparent;
            border-color: rgba(239, 68, 68, 0.5);
            color: #ef4444;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.6s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state-text {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <img src="/assets/img/logo.png" alt="Larasana" class="sidebar-logo">
        
        <nav class="sidebar-nav">
            <a href="/dashboard.php" class="sidebar-link">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="/admin/products.php" class="sidebar-link active">
                <span>📦</span>
                <span>Products</span>
            </a>
            <a href="#" class="sidebar-link">
                <span>🛒</span>
                <span>Orders</span>
            </a>
            <a href="#" class="sidebar-link">
                <span>👥</span>
                <span>Customers</span>
            </a>
            <a href="#" class="sidebar-link">
                <span>📊</span>
                <span>Analytics</span>
            </a>
            <a href="#" class="sidebar-link">
                <span>⚙️</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout.php" class="sidebar-link" style="color: rgba(239, 68, 68, 0.8);">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title title-font">Product Management</h1>
            <p class="page-subtitle">Manage your handcrafted batik collection</p>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-box">
                <span style="opacity: 0.5;">🔍</span>
                <input type="text" placeholder="Search products..." id="searchInput">
            </div>
            <a href="/admin/product-create.php" class="btn-primary">+ Create Product</a>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <p class="empty-state-text">No products yet. Create your first product to get started!</p>
                <a href="/admin/product-create.php" class="btn-primary">Create Your First Product</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($products as $p): ?>
                    <div class="product-card">
                        <div class="product-image-wrap">

                            <img src="<?= '/uploads/products/' . basename($p['image']) ?>">
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 class="product-image">
                        </div>

                        <div class="product-body">
                            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            
                            <span class="product-category">
                                <?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?>
                            </span>
                            
                            <p class="product-price">$<?= number_format($p['price'], 2) ?></p>

                            <div class="product-actions">
                                <a href="/admin/product-edit.php?id=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                                <a href="/admin/product-delete.php?id=<?= $p['id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <script>
        // Intersection Observer for product cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 80);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => observer.observe(card));
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            
            cards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const category = card.querySelector('.product-category').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || category.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>

</body>
</html>