<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch wishlist items with product details
$stmt = $pdo->prepare("
    SELECT w.id as wishlist_id, w.created_at, p.*, c.name as category_name 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE w.user_id = ? 
    ORDER BY w.created_at DESC
");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist • Larasana</title>

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
            padding: 180px 150px 60px;
            text-align: center;
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

        .wishlist-container {
            padding: 0 150px 100px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .wishlist-card {
            background: #0d0d0d;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(152, 122, 1, 0.1);
            transition: all 0.4s ease;
            position: relative;
        }

        .wishlist-card:hover {
            transform: translateY(-8px);
            border-color: rgba(152, 122, 1, 0.3);
            box-shadow: 0 12px 40px rgba(152, 122, 1, 0.15);
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

        .wishlist-card:hover .product-img {
            transform: scale(1.08);
        }

        .remove-wishlist-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            background: rgba(13, 13, 13, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
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

        .remove-wishlist-btn:hover {
            background: #dc2626;
            border-color: #dc2626;
            transform: scale(1.1);
        }

        .add-to-cart-icon {
            position: absolute;
            bottom: 16px;
            right: 16px;
            width: 48px;
            height: 48px;
            background: #987A01;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0;
            z-index: 10;
        }

        .wishlist-card:hover .add-to-cart-icon {
            opacity: 1;
        }

        .add-to-cart-icon:hover {
            background: #b39601;
            transform: scale(1.1);
        }

        .card-body {
            padding: 28px;
        }

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
            color: #ffffff;
            margin-bottom: 16px;
        }

        .added-date {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 12px;
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

        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .empty-text {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 40px;
        }

        .empty-btn {
            display: inline-block;
            padding: 16px 40px;
            background: #987A01;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .empty-btn:hover {
            background: #b39601;
            transform: translateY(-2px);
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
            .wishlist-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .wishlist-grid { grid-template-columns: 1fr; }
            .page-header, .wishlist-container { padding-left: 30px; padding-right: 30px; }
            .page-title { font-size: 42px; }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <header class="w-full flex items-center justify-between px-[150px] py-8 bg-black fixed top-0 left-0 z-40 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px]" />
        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01] fade-in delay-200">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-200">About Us</a>
        </nav>
        <div class="flex items-center gap-4">
            <a href="wishlist.php" class="relative" style="position: relative;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#987A01" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <?php if (count($wishlistItems) > 0): ?>
                <span class="cart-badge"><?php echo count($wishlistItems); ?></span>
                <?php endif; ?>
            </a>
            <a href="cart.php" class="relative" style="position: relative;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M6 6h15l-1.4 7H8.4L6 6z" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="20" r="1.5"/>
                    <circle cx="18" cy="20" r="1.5"/>
                </svg>
                <?php if ($cartCount > 0): ?>
                <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <h1 class="page-title title-font">My Wishlist</h1>
        <p class="page-subtitle">
            Your favorite products saved for later
        </p>
    </section>

    <!-- Wishlist Content -->
    <div class="wishlist-container">
        <?php if (count($wishlistItems) > 0): ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlistItems as $item): ?>
            <div class="wishlist-card" data-product-id="<?php echo $item['id']; ?>">
                <div class="img-wrap">
                    <img src="<?php echo ltrim($item['image'], '/'); ?>" class="product-img" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    
                    <button class="remove-wishlist-btn" onclick="removeFromWishlist(<?php echo $item['id']; ?>, this)" title="Remove from wishlist">
                        ×
                    </button>
                    
                    <button class="add-to-cart-icon" onclick="quickAddToCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>', <?php echo $item['price']; ?>, '<?php echo ltrim($item['image'], '/'); ?>')" title="Add to cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6h15l-1.4 7H8.4L6 6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="10" cy="20" r="1.5"/>
                            <circle cx="18" cy="20" r="1.5"/>
                        </svg>
                    </button>
                </div>

                <div class="card-body">
                    <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <?php if (isset($item['category_name'])): ?>
                    <span class="category-badge"><?php echo htmlspecialchars($item['category_name']); ?></span>
                    <?php endif; ?>
                    <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                    
                    <a href="product_detail.php?id=<?php echo $item['id']; ?>" class="btn">
                        View Details
                    </a>
                    
                    <p class="added-date">Added <?php echo date('M d, Y', strtotime($item['created_at'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">♡</div>
            <h2 class="empty-title">Your Wishlist is Empty</h2>
            <p class="empty-text">Start adding products you love to your wishlist</p>
            <a href="products.php" class="empty-btn">Browse Products</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function removeFromWishlist(productId, btn) {
            if (!confirm('Remove this item from your wishlist?')) return;
            
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);

            fetch('api/wishlist_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove card with animation
                    const card = btn.closest('.wishlist-card');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Check if wishlist is now empty
                        const remainingCards = document.querySelectorAll('.wishlist-card');
                        if (remainingCards.length === 0) {
                            location.reload();
                        }
                        
                        // Update badge
                        updateWishlistBadge(data.wishlistCount);
                    }, 300);
                } else {
                    alert(data.message || 'Failed to remove item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function quickAddToCart(id, name, price, image) {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', id);
            formData.append('product_name', name);
            formData.append('product_price', price);
            formData.append('product_image', image);
            formData.append('quantity', 1);

            fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success feedback
                    const btn = event.target.closest('.add-to-cart-icon');
                    btn.innerHTML = '✓';
                    btn.style.background = '#10b981';
                    
                    // Update cart badge
                    updateCartBadge(data.cartCount);
                    
                    setTimeout(() => {
                        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.4 7H8.4L6 6z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>';
                        btn.style.background = '#987A01';
                    }, 1500);
                } else {
                    alert('Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function updateWishlistBadge(count) {
            const wishlistLink = document.querySelector('a[href="wishlist.php"]');
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

        function updateCartBadge(count) {
            const cartLink = document.querySelector('a[href="cart.php"]');
            let badge = cartLink.querySelector('.cart-badge');
            
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    cartLink.appendChild(badge);
                }
                badge.textContent = count;
            }
        }
    </script>

</body>
</html>
