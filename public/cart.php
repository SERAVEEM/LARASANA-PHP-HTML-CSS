<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$cart = $_SESSION['cart'] ?? [];
$cartTotal = 0;

foreach ($cart as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart • Larasana</title>

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
            padding: 180px 150px 80px;
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
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

        .cart-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 150px 100px;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .cart-items {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 20px;
            padding: 30px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.1);
            margin-bottom: 20px;
        }

        .cart-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .cart-item-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
        }

        .cart-item-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cart-item-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .cart-item-price {
            font-size: 18px;
            color: #987A01;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(152, 122, 1, 0.1);
            border: 1px solid rgba(152, 122, 1, 0.3);
            border-radius: 8px;
            padding: 6px 12px;
            width: fit-content;
        }

        .qty-btn {
            background: transparent;
            border: none;
            color: #987A01;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
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
            font-size: 16px;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
        }

        .item-total {
            font-size: 22px;
            font-weight: 700;
            color: #987A01;
        }

        .remove-btn {
            background: transparent;
            border: 1px solid rgba(255, 59, 48, 0.5);
            color: #ff3b30;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: rgba(255, 59, 48, 0.1);
            border-color: #ff3b30;
        }

        .cart-summary {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 20px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .summary-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .summary-label {
            color: rgba(255, 255, 255, 0.7);
        }

        .summary-value {
            font-weight: 600;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(152, 122, 1, 0.2);
            font-size: 24px;
            font-weight: 700;
        }

        .summary-total .summary-value {
            color: #987A01;
        }

        .checkout-btn {
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
            margin-top: 25px;
        }

        .checkout-btn:hover {
            background: #b39601;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(152, 122, 1, 0.4);
        }

        .continue-shopping-btn {
            width: 100%;
            padding: 16px;
            background: transparent;
            border: 2px solid rgba(152, 122, 1, 0.5);
            color: white;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .continue-shopping-btn:hover {
            border-color: #987A01;
            color: #987A01;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-cart-text {
            font-size: 24px;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 1200px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .cart-container { padding: 0 30px 60px; }
            .page-header { padding: 140px 30px 60px; }
            .page-title { font-size: 42px; }
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
            .cart-item-actions {
                grid-column: 2;
                flex-direction: row;
                align-items: center;
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>
    <header class="w-full flex items-center justify-between px-[150px] py-8 bg-black text-white fixed top-0 left-0 z-40 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px]" />
        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01] fade-in delay-200">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-200">About Us</a>
        </nav>
        <a href="register.php" class="border-2 border-[#987A01] px-9 py-3 rounded-full text-lg hover:bg-[#987A01] hover:text-white transition fade-in delay-300">
            Sign Up
        </a>
    </header>

    <div class="page-header">
        <h1 class="page-title title-font">Shopping Cart</h1>
    </div>

    <div class="cart-container">
        <?php if (count($cart) > 0): ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cart as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                    
                    <div class="cart-item-details">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">−</button>
                            <span class="qty-value" id="qty-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                            <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                        </div>
                    </div>
                    
                    <div class="cart-item-actions">
                        <div class="item-total" id="total-<?php echo $item['id']; ?>">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)">Remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2 class="summary-title">Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value" id="subtotal">$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping:</span>
                    <span class="summary-value">Free</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tax:</span>
                    <span class="summary-value">$0.00</span>
                </div>
                
                <div class="summary-total">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value" id="grandTotal">$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                
                <a href="products.php" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <div class="empty-cart-text">Your cart is empty</div>
            <a href="products.php" class="checkout-btn" style="max-width: 300px; margin: 0 auto; display: block;">
                Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Update quantity
        function updateQuantity(productId, change) {
            const qtyElement = document.getElementById(`qty-${productId}`);
            let currentQty = parseInt(qtyElement.textContent);
            let newQty = currentQty + change;

            if (newQty < 1) return;

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('product_id', productId);
            formData.append('quantity', newQty);

            fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    qtyElement.textContent = newQty;
                    
                    // Update item total
                    const itemElement = document.querySelector(`[data-product-id="${productId}"]`);
                    const priceText = itemElement.querySelector('.cart-item-price').textContent;
                    const price = parseFloat(priceText.replace('$', ''));
                    const itemTotal = price * newQty;
                    document.getElementById(`total-${productId}`).textContent = '$' + itemTotal.toFixed(2);
                    
                    // Recalculate totals
                    recalculateTotals();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Remove item
        function removeItem(productId) {
            if (!confirm('Remove this item from cart?')) return;

            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);

            fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const itemElement = document.querySelector(`[data-product-id="${productId}"]`);
                    itemElement.style.opacity = '0';
                    itemElement.style.transform = 'translateX(-50px)';
                    
                    setTimeout(() => {
                        itemElement.remove();
                        recalculateTotals();
                        
                        // Check if cart is empty
                        if (data.cartCount === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Recalculate totals
        function recalculateTotals() {
            let subtotal = 0;
            
            document.querySelectorAll('.cart-item').forEach(item => {
                const totalText = item.querySelector('.item-total').textContent;
                const total = parseFloat(totalText.replace('$', ''));
                subtotal += total;
            });
            
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('grandTotal').textContent = '$' + subtotal.toFixed(2);
        }

        // Proceed to checkout
        function proceedToCheckout() {
            // Redirect to the new payment page which will calculate totals server-side
            window.location.href = 'shipping.php';
        }
    </script>

</body>
</html>