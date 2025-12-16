<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Get user's wishlist items if logged in
$wishlistItems = [];
if (isset($_SESSION['user_id'])) {
    $wishlistStmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wishlistStmt->execute([$_SESSION['user_id']]);
    $wishlistItems = array_column($wishlistStmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
}

// Get selected category from URL
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Fetch all categories
$categoryStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Build query based on category filter
if ($selectedCategory > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.id DESC");
    $stmt->execute([$selectedCategory]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Get wishlist count
$wishlistCount = count($wishlistItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larasana • Products</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #060606;
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .title-font { 
            font-family: 'Gelasio', serif; 
        }

        .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.6s ease-out forwards;
        }

        @keyframes fadeInDown {
            to { opacity: 1; transform: translateY(0); }
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #987A01;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after { width: 100%; }

        .page-header {
            padding: 20px 50px 40px;
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
        }

        header { /* no forced positioning here */ }
        header nav { display: flex; align-items: center; gap: 10px; }

        @media (max-width: 768px) {
            header nav { gap: 8px; }
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .page-title {
            font-size: 64px;
            font-weight: 600;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #987A01 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .filter-menu {
            display: flex;
            justify-content: center;
            gap: 16px;
            padding: 0 150px 60px;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease-out 0.4s forwards;
        }

        .filter-menu button {
            padding: 12px 32px;
            background: transparent;
            border: 2px solid rgba(152, 122, 1, 0.3);
            color: white;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .filter-menu button:hover {
            border-color: #987A01;
            color: #987A01;
        }

        .filter-menu button.active {
            background: #987A01;
            border-color: #987A01;
            color: white;
        }

        .product-container { padding-bottom: 100px; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            padding: 0 150px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .product-card {
            background: #0d0d0d;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            border: 1px solid rgba(152, 122, 1, 0.1);
            opacity: 0;
            transform: translateY(40px);
        }

        .product-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .img-wrap {
            position: relative;
            width: 100%;
            height: 380px;
            overflow: hidden;
            background: #1a1a1a;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .product-card:hover .product-img { transform: scale(1.08); }

        /* Wishlist Heart Button */
.wishlist-btn {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 40px;
    height: 40px;
    background: rgba(13, 13, 13, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: white;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}
.wishlist-btn:hover {
        background: rgba(13, 13, 13, 0.95);
        border-color: #987A01;
        transform: scale(1.1);
    }

    .wishlist-btn.active {
        color: #dc2626;
        background: rgba(220, 38, 38, 0.1);
        border-color: #dc2626;
    }

    .wishlist-btn.active:hover {
        background: rgba(220, 38, 38, 0.2);
    }

    .add-to-cart-btn {
        position: absolute;
        bottom: 22px;
        right: 22px;
        width: 36px;
        height: 36px;
        background: #987A01;
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        line-height: 1;
        text-decoration: none;
        transition: transform 0.18s ease, opacity 0.25s ease;
        opacity: 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }

    .product-card:hover .add-to-cart-btn {
        opacity: 1;
        transform: scale(1.08);
    }

    .add-to-cart-btn:hover {
        background: #b39601;
        transform: scale(1.15);
    }

    .card-body { padding: 28px; }

    .product-name {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .category-badge {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(152, 122, 1, 0.15);
        border: 1px solid rgba(152, 122, 1, 0.3);
        border-radius: 20px;
        font-size: 12px;
        color: #987A01;
        margin-bottom: 12px;
    }

    .price {
        font-size: 24px;
        font-weight: 600;
        color:  #ffffffff;
        margin-bottom: 20px;
    }

    .btn {
        display: block;
        width: 100%;
        padding: 14px;
        background: transparent;
        border: 2px solid #987A01;
        color: #987A01;
        text-align: center;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #987A01;
        color: white;
        transform: translateY(-2px);
    }

    /* Sidebar Styles */
    .cart-sidebar {
        position: fixed;
        right: -500px;
        top: 0;
        width: 450px;
        height: 100vh;
        background: #0d0d0d;
        box-shadow: -5px 0 25px rgba(0,0,0,0.5);
        z-index: 1000;
        transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        border-left: 1px solid rgba(152, 122, 1, 0.2);
    }

    .cart-sidebar.open {
        right: 0;
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .sidebar-header {
        padding: 30px;
        border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-header h2 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }

    .close-sidebar {
        background: transparent;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .close-sidebar:hover {
        background: rgba(152, 122, 1, 0.2);
        color: #987A01;
    }

    .sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 30px;
    }

    .sidebar-product-img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .sidebar-product-name {
        font-size: 26px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .sidebar-product-price {
        font-size: 28px;
        font-weight: 600;
        color: #987A01;
        margin-bottom: 30px;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .quantity-label {
        font-size: 16px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        background: rgba(152, 122, 1, 0.1);
        border: 1px solid rgba(152, 122, 1, 0.3);
        border-radius: 12px;
        padding: 8px 16px;
    }

    .qty-btn {
        background: transparent;
        border: none;
        color: #987A01;
        font-size: 20px;
        font-weight: 700;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .qty-btn:hover {
        background: rgba(152, 122, 1, 0.2);
    }

    .qty-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .qty-value {
        font-size: 18px;
        font-weight: 600;
        min-width: 30px;
        text-align: center;
    }

    .total-price {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
        border-top: 1px solid rgba(152, 122, 1, 0.2);
        border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        margin-bottom: 30px;
    }

    .total-label {
        font-size: 18px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
    }

    .total-amount {
        font-size: 28px;
        font-weight: 700;
        color: #987A01;
    }

    .add-to-cart-primary {
        width: 100%;
        padding: 18px;
        background: #987A01;
        border: none;
        color: white;
        font-size: 18px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }

    .add-to-cart-primary:hover {
        background: #b39601;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(152, 122, 1, 0.4);
    }

    .continue-shopping {
        width: 100%;
        padding: 18px;
        background: transparent;
        border: 2px solid rgba(152, 122, 1, 0.5);
        color: white;
        font-size: 16px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .continue-shopping:hover {
        border-color: #987A01;
        color: #987A01;
    }

    .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #987A01;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }

    @media (max-width: 1400px) {
        .product-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .product-grid { grid-template-columns: 1fr; padding: 0 30px; }
        .page-header { padding: 100px 30px 40px; }
        .page-title { font-size: 42px; }
        .filter-menu { padding: 0 30px 40px; }
        .cart-sidebar { width: 100%; right: -100%; }
    }

    .floating-cart {
        position: fixed;
        left: 24px;
        bottom: 24px;
        z-index: 40;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #0d0d0d;
        border: 2px solid #987A01;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(0,0,0,0.45);
        transition: transform 0.12s ease, background 0.12s ease;
    }
    .floating-cart:hover { transform: translateY(-4px); background: #111111; }
    .floating-cart .cart-badge { position: absolute; top: -8px; right: -8px; }
</style>

</head>
<body>
    <header class="w-full flex items-center justify-between px-[150px] py-8 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px] scale-in" />
        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Home</a>
            <a href="products.php" class="text-[#987A01] font-medium fade-in delay-100">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01] fade-in delay-200">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-200">About Us</a>
        </nav>
        <div class="flex items-center gap-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="wishlist.php" class="relative" style="position: relative;" title="Wishlist">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <?php if ($wishlistCount > 0): ?>
                    <span class="cart-badge"><?php echo $wishlistCount; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <a href="register.php" class="border-2 border-[#987A01] px-9 py-3 rounded-full text-lg btn-hover fade-in delay-300">
                Sign Up
            </a>
        </div>
    </header>
    <div class="product-container">

    <section class="page-header">
        <h1 class="page-title title-font">Our Collections</h1>
        <p class="page-subtitle">
            Explore our curated selection of handcrafted batik pieces, blending heritage with modern artistry.
        </p>
    </section>

    <div class="filter-menu">
        <button class="<?php echo $selectedCategory === 0 ? 'active' : ''; ?>" onclick="filterByCategory(0)">
            <span>All Products</span>
        </button>
        <?php foreach ($categories as $category): ?>
        <button class="<?php echo $selectedCategory === (int)$category['id'] ? 'active' : ''; ?>" 
                onclick="filterByCategory(<?php echo $category['id']; ?>)">
            <span><?php echo htmlspecialchars($category['name']); ?></span>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="product-grid" id="productGrid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $row): ?>
            
            <div class="product-card" data-category="<?php echo $row['category_id'] ?? 0; ?>">

                <div class="img-wrap">
                    <img src="<?php echo ltrim($row['image'], '/'); ?>" class="product-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    
                    <!-- Wishlist Heart Button -->
                    <button class="wishlist-btn <?php echo in_array($row['id'], $wishlistItems) ? 'active' : ''; ?>" 
                            data-product-id="<?php echo $row['id']; ?>"
                            onclick="toggleWishlist(<?php echo $row['id']; ?>, this)"
                            title="<?php echo in_array($row['id'], $wishlistItems) ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                        <?php if (in_array($row['id'], $wishlistItems)): ?>
                            ♥
                        <?php else: ?>
                            ♡
                        <?php endif; ?>
                    </button>
                    
                    <button class="add-to-cart-btn" 
                            onclick="openCartSidebar(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>', <?php echo $row['price']; ?>, '<?php echo ltrim($row['image'], '/'); ?>')">
                        +
                    </button>
                </div>

                <div class="card-body">
                    <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <?php if (isset($row['category_name'])): ?>
                    <span class="category-badge"><?php echo htmlspecialchars($row['category_name']); ?></span>
                    <?php endif; ?>
                    <p class="price">$<?php echo number_format($row['price'], 2); ?></p>

                    <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn">
                        View Details
                    </a>
                </div>

            </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <p style="font-size: 20px; color: rgba(255,255,255,0.5);">No products found in this category.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Floating Cart Button -->
<a href="cart.php" class="floating-cart" title="View Cart">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M6 6h15l-1.4 7H8.4L6 6z" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="10" cy="20" r="1.5" fill="#fff"/>
        <circle cx="18" cy="20" r="1.5" fill="#fff"/>
    </svg>
    <?php if ($cartCount > 0): ?>
    <span class="cart-badge"><?php echo $cartCount; ?></span>
    <?php endif; ?>
</a>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeCartSidebar()"></div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="sidebar-header">
        <h2>Add to Cart</h2>
        <button class="close-sidebar" onclick="closeCartSidebar()">&times;</button>
    </div>
    
    <div class="sidebar-body">
        <img id="sidebarProductImg" src="" alt="Product" class="sidebar-product-img">
        
        <h3 class="sidebar-product-name" id="sidebarProductName">Product Name</h3>
        
        <div class="sidebar-product-price" id="sidebarProductPrice">$0.00</div>
        
        <div class="quantity-selector">
            <span class="quantity-label">Quantity:</span>
            <div class="quantity-controls">
                <button class="qty-btn" id="decreaseQty" onclick="decreaseQuantity()">−</button>
                <span class="qty-value" id="quantityValue">1</span>
                <button class="qty-btn" id="increaseQty" onclick="increaseQuantity()">+</button>
            </div>
        </div>
        
        <div class="total-price">
            <span class="total-label">Total:</span>
            <span class="total-amount" id="totalAmount">$0.00</span>
        </div>
        
        <button class="add-to-cart-primary" onclick="addToCart()">
            Add to Cart
        </button>
        
        <button class="continue-shopping" onclick="closeCartSidebar()">
            Continue Shopping
        </button>
    </div>
</div>

<script>
    // Global variables for sidebar
    let currentProduct = {
        id: 0,
        name: '',
        price: 0,
        image: '',
        quantity: 1
    };

    // Filter products by category
    function filterByCategory(categoryId) {
        window.location.href = `products.php?category=${categoryId}`;
    }

    // Toggle Wishlist
    function toggleWishlist(productId, btn) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert('Please login to use wishlist');
            window.location.href = 'login.php?redirect=products.php';
            return;
        <?php endif; ?>

        const isActive = btn.classList.contains('active');
        const action = isActive ? 'remove' : 'add';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('product_id', productId);

        fetch('../api/wishlist_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle button state
                btn.classList.toggle('active');
                btn.innerHTML = data.inWishlist ? '♥' : '♡';
                btn.title = data.inWishlist ? 'Remove from wishlist' : 'Add to wishlist';
                
                // Update wishlist badge in header
                updateWishlistBadge(data.wishlistCount);
                
                // Show feedback
                const feedback = document.createElement('div');
                feedback.textContent = data.message;
                feedback.style.cssText = `
                    position: fixed;
                    top: 100px;
                    right: 30px;
                    background: ${data.inWishlist ? '#10b981' : '#ef4444'};
                    color: white;
                    padding: 16px 24px;
                    border-radius: 12px;
                    font-weight: 600;
                    z-index: 9999;
                    animation: slideIn 0.3s ease-out;
                `;
                document.body.appendChild(feedback);
                
                setTimeout(() => {
                    feedback.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(() => feedback.remove(), 300);
                }, 2000);
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    function updateWishlistBadge(count) {
        const wishlistLink = document.querySelector('a[href="wishlist.php"]');
        if (!wishlistLink) return;
        
        let badge = wishlistLink.querySelector('.cart-badge');
        
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                wishlistLink.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    // Open cart sidebar
    function openCartSidebar(id, name, price, image) {
        currentProduct = {
            id: id,
            name: name,
            price: parseFloat(price),
            image: image,
            quantity: 1
        };

        document.getElementById('sidebarProductImg').src = image;
        document.getElementById('sidebarProductName').textContent = name;
        document.getElementById('sidebarProductPrice').textContent = '$' + price.toFixed(2);
        document.getElementById('quantityValue').textContent = '1';
        document.getElementById('totalAmount').textContent = '$' + price.toFixed(2);

        document.getElementById('cartSidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Close cart sidebar
    function closeCartSidebar() {
        document.getElementById('cartSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('active');
        document.body.style.overflow = 'auto';
        
        setTimeout(() => {
            currentProduct.quantity = 1;
            document.getElementById('quantityValue').textContent = '1';
        }, 400);
    }

    // Increase quantity
    function increaseQuantity() {
        currentProduct.quantity++;
        updateQuantityDisplay();
    }

    // Decrease quantity
    function decreaseQuantity() {
        if (currentProduct.quantity > 1) {
            currentProduct.quantity--;
            updateQuantityDisplay();
        }
    }

    // Update quantity display and total
    function updateQuantityDisplay() {
        document.getElementById('quantityValue').textContent = currentProduct.quantity;
        const total = currentProduct.price * currentProduct.quantity;
        document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
        
        document.getElementById('decreaseQty').disabled = currentProduct.quantity <= 1;
    }

    // Add to cart
    function addToCart() {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', currentProduct.id);
        formData.append('product_name', currentProduct.name);
        formData.append('product_price', currentProduct.price);
        formData.append('product_image', currentProduct.image);
        formData.append('quantity', currentProduct.quantity);

        fetch('cart-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.querySelector('.add-to-cart-primary');
                const originalText = btn.textContent;
                btn.textContent = '✓ Added to Cart!';
                btn.style.background = '#10b981';
                
                const badge = document.querySelector('.floating-cart .cart-badge');
                if (badge) {
                    badge.textContent = data.cartCount;
                } else if (data.cartCount > 0) {
                    const cartLink = document.querySelector('.floating-cart');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'cart-badge';
                    newBadge.textContent = data.cartCount;
                    cartLink.appendChild(newBadge);
                }
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#987A01';
                    closeCartSidebar();
                }, 1500);
            } else {
                alert('Failed to add to cart. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Intersection Observer for card animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, index * 100);
            }
        });
    }, observerOptions);

    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => observer.observe(card));
    });

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
</script>
</body>
</html>
