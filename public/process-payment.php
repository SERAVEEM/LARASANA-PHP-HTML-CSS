<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get payment data
$payment_method = $_POST['payment_method'] ?? '';
$order_id = $_POST['order_id'] ?? 0;

// Validate
if (empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Payment method is required']);
    exit;
}

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

// Check database connection
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not established']);
    exit;
}

try {
    // Verify order exists and is pending
    $stmt = $pdo->prepare("SELECT id, status, total FROM orders WHERE id = ? AND status = 'pending'");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Order not found or already processed');
    }

    // For credit card, you would normally integrate with a payment gateway (Stripe, PayPal, etc.)
    // For this demo, we'll simulate a successful payment
    
    $payment_status = 'paid'; // In production, this would come from payment gateway response
    
    // Update order with payment information
    $sql = "UPDATE orders 
            SET payment_method = :payment_method, 
                payment_status = :payment_status, 
                status = 'processing',
                updated_at = NOW() 
            WHERE id = :order_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':payment_method' => $payment_method,
        ':payment_status' => $payment_status,
        ':order_id' => $order_id
    ]);

    // Create payment record (optional - for tracking)
    $payment_record_sql = "INSERT INTO payments 
                          (order_id, payment_method, amount, status, transaction_date) 
                          VALUES (:order_id, :payment_method, :amount, :status, NOW())";
    
    try {
        $stmt = $pdo->prepare($payment_record_sql);
        $stmt->execute([
            ':order_id' => $order_id,
            ':payment_method' => $payment_method,
            ':amount' => $order['total'],
            ':status' => 'completed'
        ]);
    } catch (PDOException $e) {
        // Payments table might not exist, that's okay
        // The order update is more important
    }

    // Clear cart and order session data
    unset($_SESSION['cart']);
    unset($_SESSION['order_id']);
    unset($_SESSION['shipping_info']);
    unset($_SESSION['order_totals']);

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>