<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Check if product is in wishlist
$inWishlist = false;
if (isset($_SESSION['user_id'])) {
    $wishlistStmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wishlistStmt->execute([$_SESSION['user_id'], $productId]);
    $inWishlist = $wishlistStmt->fetch() ? true : false;
}

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Get wishlist count
$wishlistCount = 0;
if (isset($_SESSION['user_id'])) {
    $wishlistCountStmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $wishlistCountStmt->execute([$_SESSION['user_id']]);
    $wishlistCount = $wishlistCountStmt->fetch()['count'];
}

// Get related products from same category
$relatedStmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? 
    LIMIT 3
");
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> • Larasana</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

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

        .product-detail-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 150px 80px 100px;
        }

        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            margin-bottom: 120px;
        }

        .product-image-section {
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .main-image-wrapper {
            width: 100%;
            height: 700px;
            border-radius: 24px;
            overflow: hidden;
            background: #1a1a1a;
            position: relative;
            border: 1px solid rgba(152, 122, 1, 0.2);
        }

        .main-product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .main-image-wrapper:hover .main-product-image {
            transform: scale(1.05);
        }

        .wishlist-btn-detail {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            background: rgba(13, 13, 13, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .wishlist-btn-detail:hover {
            background: rgba(13, 13, 13, 1);
            border-color: #987A01;
            transform: scale(1.1);
        }

        .wishlist-btn-detail.active {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.15);
            border-color: #dc2626;
        }

        .product-info-section {
            padding-top: 20px;
        }

        .breadcrumb {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 32px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #987A01;
        }

        .breadcrumb span {
            color: rgba(255, 255, 255, 0.4);
        }

        .category-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(152, 122, 1, 0.15);
            border: 1px solid rgba(152, 122, 1, 0.3);
            border-radius: 24px;
            font-size: 14px;
            color: #987A01;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .product-title {
            font-size: 56px;
            font-weight: 600;
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .product-price {
            font-size: 48px;
            font-weight: 700;
            color: #987A01;
            margin-bottom: 32px;
        }

        .product-description {
            font-size: 18px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            padding-bottom: 48px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        }

        .quantity-section {
            margin-bottom: 32px;
        }

        .quantity-label {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            display: block;
            color: rgba(255, 255, 255, 0.9);
        }

        .quantity-controls {
            display: inline-flex;
            align-items: center;
            gap: 20px;
            background: rgba(152, 122, 1, 0.1);
            border: 2px solid rgba(152, 122, 1, 0.3);
            border-radius: 16px;
            padding: 12px 24px;
        }

        .qty-btn {
            background: transparent;
            border: none;
            color: #987A01;
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
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
            font-size: 22px;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
        }

        .btn-add-cart {
            flex: 1;
            padding: 20px 40px;
            background: #987A01;
            border: none;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-add-cart:hover {
            background: #b39601;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(152, 122, 1, 0.4);
        }

        .btn-buy-now {
            flex: 1;
            padding: 20px 40px;
            background: transparent;
            border: 2px solid #987A01;
            color: #987A01;
            font-size: 18px;
            font-weight: 600;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-buy-now:hover {
            background: rgba(152, 122, 1, 0.1);
            transform: translateY(-2px);
        }

        .product-meta {
            display: grid;
            gap: 16px;
            padding: 32px;
            background: rgba(152, 122, 1, 0.05);
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 16px;
        }

        .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.1);
        }

        .meta-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .meta-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        .meta-value {
            font-size: 16px;
            color: white;
            font-weight: 600;
        }

        /* Related Products Section */
        .related-section {
            padding-top: 60px;
            border-top: 1px solid rgba(152, 122, 1, 0.2);
        }

        .section-title {
            font-size: 42px;
            font-weight: 600;
            margin-bottom: 48px;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .related-card {
            background: #0d0d0d;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(152, 122, 1, 0.1);
            transition: all 0.4s ease;
            text-decoration: none;
            display: block;
        }

        .related-card:hover {
            transform: translateY(-8px);
            border-color: rgba(152, 122, 1, 0.3);
            box-shadow: 0 12px 40px rgba(152, 122, 1, 0.15);
        }

        .related-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .related-card:hover .related-image {
            transform: scale(1.08);
        }

        .related-info {
            padding: 24px;
        }

        .related-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }

        .related-price {
            font-size: 22px;
            font-weight: 600;
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

        @media (max-width: 1200px) {
            .product-layout {
                grid-template-columns: 1fr;
                gap: 60px;
            }

            .product-image-section {
                position: relative;
                top: 0;
            }

            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .product-detail-container {
                padding: 120px 30px 60px;
            }

            .product-title {
                font-size: 36px;
            }

            .product-price {
                font-size: 32px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }

            .main-image-wrapper {
                height: 400px;
            }
        }

        .success-toast {
            position: fixed;
            top: 100px;
            right: 30px;
            background: #10b981;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 600;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <header class="w-full flex items-center justify-between px-[150px] py-8 bg-black fixed top-0 left-0 z-40 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px]" />
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
                <a href="cart.php" class="relative" style="position: relative;" title="Cart">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M6 6h15l-1.4 7H8.4L6 6z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10" cy="20" r="1.5"/>
                        <circle cx="18" cy="20" r="1.5"/>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <a href="register.php" class="border-2 border-[#987A01] px-9 py-3 rounded-full text-lg hover:bg-[#987A01] hover:text-white transition fade-in delay-300">
                Sign Up
            </a>
        </div>
    </header>

    <!-- Product Detail Content -->
    <div class="product-detail-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb fade-in">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="products.php">Products</a>
            <span>/</span>
            <span style="color: white;"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <!-- Product Layout -->
        <div class="product-layout">
            <!-- Left: Product Image -->
            <div class="product-image-section fade-in delay-100">
                <div class="main-image-wrapper">
                    <img src="<?php echo ltrim($product['image'], '/'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-product-image">
                    
                    <!-- Wishlist Button -->
                    <button class="wishlist-btn-detail <?php echo $inWishlist ? 'active' : ''; ?>" 
                            onclick="toggleWishlist(<?php echo $product['id']; ?>, this)"
                            title="<?php echo $inWishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                        <?php echo $inWishlist ? '♥' : '♡'; ?>
                    </button>
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="product-info-section fade-in delay-200">
                <?php if (isset($product['category_name'])): ?>
                <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                
                <h1 class="product-title title-font"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-description">
                    <?php 
                    $description = $product['description'] ?? 'Experience the beauty of traditional Indonesian batik craftsmanship. Each piece is carefully handcrafted by skilled artisans, preserving centuries-old techniques while creating contemporary designs. Made with premium materials and attention to detail, this product represents the perfect blend of cultural heritage and modern elegance.';
                    echo nl2br(htmlspecialchars($description)); 
                    ?>
                </div>
                
                <!-- Quantity Selector -->
                <div class="quantity-section">
                    <label class="quantity-label">Quantity:</label>
                    <div class="quantity-controls">
                        <button class="qty-btn" id="decreaseQty" onclick="decreaseQuantity()">−</button>
                        <span class="qty-value" id="quantityValue">1</span>
                        <button class="qty-btn" id="increaseQty" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn-add-cart" onclick="addToCart()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6h15l-1.4 7H8.4L6 6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="10" cy="20" r="1.5"/>
                            <circle cx="18" cy="20" r="1.5"/>
                        </svg>
                        Add to Cart
                    </button>
                    <button class="btn-buy-now" onclick="buyNow()">
                        Buy Now
                    </button>
                </div>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Product ID</span>
                        <span class="meta-value">#<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Category</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['category_name'] ?? 'Batik'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Availability</span>
                        <span class="meta-value" style="color: #10b981;">In Stock</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Shipping</span>
                        <span class="meta-value">Free Shipping</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (count($relatedProducts) > 0): ?>
        <div class="related-section">
            <h2 class="section-title title-font">You May Also Like</h2>
            <div class="related-grid">
                <?php foreach ($relatedProducts as $related): ?>
                <a href="product_detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                    <img src="<?php echo ltrim($related['image'], '/'); ?>" 
                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                         class="related-image">
                    <div class="related-info">
                        <?php if (isset($related['category_name'])): ?>
                        <span class="category-badge" style="margin-bottom: 12px;"><?php echo htmlspecialchars($related['category_name']); ?></span>
                        <?php endif; ?>
                        <h3 class="related-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                        <p class="related-price">$<?php echo number_format($related['price'], 2); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        let quantity = 1;
        const productId = <?php echo $product['id']; ?>;
        const productName = <?php echo json_encode($product['name']); ?>;
        const productPrice = <?php echo $product['price']; ?>;
        const productImage = <?php echo json_encode(ltrim($product['image'], '/')); ?>;

        function decreaseQuantity() {
            if (quantity > 1) {
                quantity--;
                updateQuantityDisplay();
            }
        }

        function increaseQuantity() {
            quantity++;
            updateQuantityDisplay();
        }

        function updateQuantityDisplay() {
            document.getElementById('quantityValue').textContent = quantity;
            document.getElementById('decreaseQty').disabled = quantity <= 1;
        }

        function addToCart() {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('product_price', productPrice);
            formData.append('product_image', productImage);
            formData.append('quantity', quantity);

            fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart successfully!');
                    
                    // Update cart badge
                    updateCartBadge(data.cartCount);
                    
                    // Reset quantity
                    quantity = 1;
                    updateQuantityDisplay();
                } else {
                    alert('Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function buyNow() {
            addToCart();
            setTimeout(() => {
                window.location.href = 'cart.php';
            }, 1000);
        }

        function toggleWishlist(productId, btn) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Please login to use wishlist');
                window.location.href = 'login.php?redirect=product_detail.php?id=<?php echo $productId; ?>';
                return;
            <?php endif; ?>

            const isActive = btn.classList.contains('active');
            const action = isActive ? 'remove' : 'add';
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('product_id', productId);

            fetch('api/wishlist_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('active');
                    btn.innerHTML = data.inWishlist ? '♥' : '♡';
                    btn.title = data.inWishlist ? 'Remove from wishlist' : 'Add to wishlist';
                    
                    showToast(data.message);
                    updateWishlistBadge(data.wishlistCount);
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'success-toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }

        function updateCartBadge(count) {
            const cartLink = document.querySelector('a[href="cart.php"]');
            if (!cartLink) return;
            
            let badge = cartLink.querySelector('.cart-badge');
            
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    cartLink.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
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

        // Initialize quantity display
        updateQuantityDisplay();
    </script>

</body>
</html>