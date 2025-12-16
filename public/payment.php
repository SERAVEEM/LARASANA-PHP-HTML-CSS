<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Check if order exists
if (!isset($_SESSION['order_id']) || !isset($_SESSION['shipping_info']) || !isset($_SESSION['order_totals'])) {
    header('Location: cart.php');
    exit;
}

$order_id = $_SESSION['order_id'];
$shipping_info = $_SESSION['shipping_info'];
$order_totals = $_SESSION['order_totals'];
$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment • Larasana</title>

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

        .payment-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 150px 100px;
        }

        .payment-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .payment-form {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 20px;
            padding: 40px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.2);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-method {
            padding: 20px;
            background: rgba(152, 122, 1, 0.05);
            border: 2px solid rgba(152, 122, 1, 0.3);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .payment-method:hover {
            border-color: #987A01;
            background: rgba(152, 122, 1, 0.1);
            transform: translateY(-2px);
        }

        .payment-method.active {
            border-color: #987A01;
            background: rgba(152, 122, 1, 0.15);
            box-shadow: 0 0 20px rgba(152, 122, 1, 0.3);
        }

        .payment-icon {
            font-size: 32px;
        }

        .payment-name {
            font-size: 14px;
            font-weight: 500;
        }

        .card-fields {
            display: none;
            margin-top: 30px;
        }

        .card-fields.active {
            display: block;
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

        .info-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(152, 122, 1, 0.1);
        }

        .info-title {
            font-size: 14px;
            font-weight: 600;
            color: #987A01;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-text {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
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

        .pay-now-btn {
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

        .pay-now-btn:hover {
            background: #b39601;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(152, 122, 1, 0.4);
        }

        .pay-now-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .back-btn {
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

        .back-btn:hover {
            border-color: #987A01;
            color: #987A01;
        }

        /* Success Modal */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .success-modal.active {
            display: flex;
        }

        .success-content {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.3);
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            max-width: 500px;
            animation: scaleIn 0.4s ease-out;
        }

        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounceIn 0.6s ease-out;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .success-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #987A01;
        }

        .success-message {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .order-number {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }

        .success-btn {
            padding: 16px 40px;
            background: #987A01;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .success-btn:hover {
            background: #b39601;
            transform: translateY(-2px);
        }

        @media (max-width: 1200px) {
            .payment-content {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .payment-container { padding: 0 30px 60px; }
            .page-header { padding: 140px 30px 60px; }
            .page-title { font-size: 42px; }
            .payment-form { padding: 30px 20px; }
            .payment-methods { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .success-content { padding: 40px 30px; }
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
        <h1 class="page-title title-font">Payment</h1>
    </div>

    <div class="payment-container">
        <div class="payment-content">
            <div class="payment-form">
                <form id="paymentForm">
                    <input type="hidden" id="selectedPaymentMethod" name="payment_method" value="">
                    
                    <h2 class="section-title">Select Payment Method</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method" onclick="selectPayment('credit-card')">
                            <div class="payment-icon">💳</div>
                            <div class="payment-name">Credit Card</div>
                        </div>
                        <div class="payment-method" onclick="selectPayment('paypal')">
                            <div class="payment-icon">💰</div>
                            <div class="payment-name">PayPal</div>
                        </div>
                        <div class="payment-method" onclick="selectPayment('bank-transfer')">
                            <div class="payment-icon">🏦</div>
                            <div class="payment-name">Bank Transfer</div>
                        </div>
                    </div>

                    <!-- Credit Card Fields -->
                    <div class="card-fields" id="cardFields">
                        <h3 class="section-title">Card Information</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Card Number</label>
                                <input type="text" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Cardholder Name</label>
                                <input type="text" name="card_name" class="form-input" placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" name="expiry" class="form-input" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CVV</label>
                                <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                
                <div class="info-section">
                    <div class="info-title">Shipping Address</div>
                    <div class="info-text">
                        <?php echo htmlspecialchars($shipping_info['first_name'] . ' ' . $shipping_info['last_name']); ?><br>
                        <?php echo htmlspecialchars($shipping_info['address']); ?><br>
                        <?php echo htmlspecialchars($shipping_info['city'] . ', ' . $shipping_info['state'] . ' ' . $shipping_info['zip']); ?><br>
                        <?php echo htmlspecialchars($shipping_info['country']); ?>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-title">Contact</div>
                    <div class="info-text">
                        <?php echo htmlspecialchars($shipping_info['email']); ?><br>
                        <?php echo htmlspecialchars($shipping_info['phone']); ?>
                    </div>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">$<?php echo number_format($order_totals['subtotal'], 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Shipping:</span>
                    <span class="summary-value">Free</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tax:</span>
                    <span class="summary-value">$<?php echo number_format($order_totals['tax'], 2); ?></span>
                </div>
                
                <div class="summary-total">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value">$<?php echo number_format($order_totals['total'], 2); ?></span>
                </div>
                
                <button type="button" class="pay-now-btn" onclick="processPayment()">Pay Now</button>
                
                <a href="checkout.php" class="back-btn">Back to Checkout</a>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">✓</div>
            <h2 class="success-title title-font">Payment Successful!</h2>
            <p class="success-message">Thank you for your purchase. Your order has been confirmed and will be shipped soon.</p>
            <p class="order-number">Order #<?php echo $order_id; ?></p>
            <button class="success-btn" onclick="goToHome()">Continue Shopping</button>
        </div>
    </div>

    <script>
        let selectedMethod = '';

        // Select payment method
        function selectPayment(method) {
            selectedMethod = method;
            
            // Remove active class from all methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            
            // Add active class to selected method
            event.target.closest('.payment-method').classList.add('active');
            
            // Update hidden field
            document.getElementById('selectedPaymentMethod').value = method;
            
            // Show/hide card fields
            const cardFields = document.getElementById('cardFields');
            if (method === 'credit-card') {
                cardFields.classList.add('active');
            } else {
                cardFields.classList.remove('active');
            }
        }

        // Format card number
        const cardNumberInput = document.querySelector('input[name="card_number"]');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }

        // Format expiry date
        const expiryInput = document.querySelector('input[name="expiry"]');
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2, 4);
                }
                e.target.value = value;
            });
        }

        // Process payment
        function processPayment() {
            if (!selectedMethod) {
                alert('Please select a payment method');
                return;
            }

            // Validate credit card fields if credit card is selected
            if (selectedMethod === 'credit-card') {
                const form = document.getElementById('paymentForm');
                const cardNumber = form.card_number.value;
                const cardName = form.card_name.value;
                const expiry = form.expiry.value;
                const cvv = form.cvv.value;

                if (!cardNumber || !cardName || !expiry || !cvv) {
                    alert('Please fill in all card details');
                    return;
                }
            }

            // Disable button
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Processing...';

            // Send payment data
            const formData = new FormData();
            formData.append('payment_method', selectedMethod);
            formData.append('order_id', '<?php echo $order_id; ?>');

            if (selectedMethod === 'credit-card') {
                const form = document.getElementById('paymentForm');
                formData.append('card_number', form.card_number.value);
                formData.append('card_name', form.card_name.value);
                formData.append('expiry', form.expiry.value);
                formData.append('cvv', form.cvv.value);
            }

            fetch('process-payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    document.getElementById('successModal').classList.add('active');
                } else {
                    alert('Payment failed: ' + (data.message || 'Please try again'));
                    btn.disabled = false;
                    btn.textContent = 'Pay Now';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment failed. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Pay Now';
            });
        }

        // Go to home after successful payment
        function goToHome() {
            window.location.href = 'index.php';
        }
    </script>

</body>
</html>