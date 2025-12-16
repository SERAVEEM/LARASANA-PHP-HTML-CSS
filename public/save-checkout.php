<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors directly, send them in JSON

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$zip = trim($_POST['zip'] ?? '');
$country = trim($_POST['country'] ?? '');

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
    empty($address) || empty($city) || empty($state) || empty($zip) || empty($country)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Get cart and totals from session
$cart = $_SESSION['cart'] ?? [];
$order_totals = $_SESSION['order_totals'] ?? null;

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate totals if not in session
if (!$order_totals) {
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $order_totals = [
        'subtotal' => $subtotal,
        'shipping' => 0,
        'tax' => 0,
        'total' => $subtotal
    ];
}

// Check database connection
if (!isset($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not established']);
    exit;
}

try {
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Orders table does not exist. Please run the SQL schema first.');
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Order_items table does not exist. Please run the SQL schema first.');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert into orders table
    $sql = "INSERT INTO orders (first_name, last_name, email, phone, address, city, state, zip, country, subtotal, shipping, tax, total, status, created_at) 
            VALUES (:first_name, :last_name, :email, :phone, :address, :city, :state, :zip, :country, :subtotal, :shipping, :tax, :total, 'pending', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':city' => $city,
        ':state' => $state,
        ':zip' => $zip,
        ':country' => $country,
        ':subtotal' => $order_totals['subtotal'],
        ':shipping' => $order_totals['shipping'],
        ':tax' => $order_totals['tax'],
        ':total' => $order_totals['total']
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) 
            VALUES (:order_id, :product_id, :product_name, :product_image, :price, :quantity, :subtotal)";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($cart as $item) {
        $product_id = isset($item['id']) ? intval($item['id']) : 0;
        $product_name = $item['name'];
        $product_image = $item['image'];
        $price = floatval($item['price']);
        $quantity = intval($item['quantity']);
        $subtotal = $price * $quantity;
        
        $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':product_name' => $product_name,
            ':product_image' => $product_image,
            ':price' => $price,
            ':quantity' => $quantity,
            ':subtotal' => $subtotal
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Store order ID and shipping info in session for payment page
    $_SESSION['order_id'] = $order_id;
    $_SESSION['shipping_info'] = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'country' => $country
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order saved successfully',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>