<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$cart = $_SESSION['cart'] ?? [];

// Redirect if cart is empty
if (count($cart) === 0) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 0; // Free shipping
$tax = 0; // No tax for now
$total = $subtotal + $shipping + $tax;

// Store totals in session for payment page
$_SESSION['order_totals'] = [
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'tax' => $tax,
    'total' => $total
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout • Larasana</title>

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

        .checkout-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 150px 100px;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .checkout-form {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 20px;
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-input {
            padding: 14px 18px;
            background: rgba(152, 122, 1, 0.05);
            border: 1px solid rgba(152, 122, 1, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #987A01;
            background: rgba(152, 122, 1, 0.1);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .order-summary {
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

        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 10px;
        }

        .order-items::-webkit-scrollbar {
            width: 6px;
        }

        .order-items::-webkit-scrollbar-track {
            background: rgba(152, 122, 1, 0.1);
            border-radius: 10px;
        }

        .order-items::-webkit-scrollbar-thumb {
            background: rgba(152, 122, 1, 0.3);
            border-radius: 10px;
        }

        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.1);
            align-items: center;
        }

        .order-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .order-item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .order-item-name {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.3;
        }

        .order-item-qty {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }

        .order-item-price {
            font-size: 16px;
            font-weight: 600;
            color: #987A01;
            flex-shrink: 0;
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

        .place-order-btn {
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

        .place-order-btn:hover {
            background: #b39601;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(152, 122, 1, 0.4);
        }

        .place-order-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .back-to-cart {
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

        .back-to-cart:hover {
            border-color: #987A01;
            color: #987A01;
        }

        @media (max-width: 1200px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .checkout-container { padding: 0 30px 60px; }
            .page-header { padding: 140px 30px 60px; }
            .page-title { font-size: 42px; }
            .checkout-form { padding: 30px 20px; }
            .form-grid { grid-template-columns: 1fr; }
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
        <h1 class="page-title title-font">Checkout</h1>
    </div>

    <div class="checkout-container">
        <div class="checkout-content">
            <div class="checkout-form">
                <form id="checkoutForm" method="POST" action="save-checkout.php">
                    <!-- Shipping Information -->
                    <div class="form-section">
                        <h2 class="section-title">Shipping Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-input" placeholder="John" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-input" placeholder="Doe" required>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-input" placeholder="john.doe@example.com" required>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-input" placeholder="+1 (555) 000-0000" required>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Street Address *</label>
                                <input type="text" name="address" class="form-input" placeholder="123 Main Street" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City *</label>
                                <input type="text" name="city" class="form-input" placeholder="New York" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">State/Province *</label>
                                <input type="text" name="state" class="form-input" placeholder="NY" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">ZIP/Postal Code *</label>
                                <input type="text" name="zip" class="form-input" placeholder="10001" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country *</label>
                                <input type="text" name="country" class="form-input" placeholder="United States" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                
                <div class="order-items">
                    <?php foreach ($cart as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
                        <div class="order-item-details">
                            <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="order-item-qty">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="order-item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping:</span>
                    <span class="summary-value">Free</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tax:</span>
                    <span class="summary-value">$<?php echo number_format($tax, 2); ?></span>
                </div>
                
                <div class="summary-total">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value">$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <button type="button" class="place-order-btn" onclick="submitCheckout()">Go to Payment</button>
                
                <a href="cart.php" class="back-to-cart">Back to Cart</a>
            </div>
        </div>
    </div>

    <script>
        // Submit checkout form and save to database
        function submitCheckout() {
            const form = document.getElementById('checkoutForm');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Disable button
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Saving...';

            // Submit form via AJAX
            const formData = new FormData(form);

            fetch('save-checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to payment page
                    window.location.href = 'payment.php';
                } else {
                    alert('Error saving information: ' + (data.message || 'Please try again.'));
                    btn.disabled = false;
                    btn.textContent = 'Go to Payment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving information. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Go to Payment';
            });
        }
    </script>

</body>
</html>