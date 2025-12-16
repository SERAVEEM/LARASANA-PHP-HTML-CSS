<?php
require_once __DIR__ . '/../../config/database.php';  // <-- Fixed path (go up two levels)

if (session_status() === PHP_SESSION_NONE) session_start();


// ... rest of the code
header('Content-Type: application/json');

// Debug: Log the request
error_log("Wishlist handler called - Action: " . ($_POST['action'] ?? 'none') . ", Product: " . ($_POST['product_id'] ?? 'none'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Please login to use wishlist', 'debug' => 'no_session']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

error_log("User ID: $userId, Action: $action, Product ID: $productId");

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product', 'debug' => 'invalid_product_id']);
    exit;
}

try {
    // First, check if the wishlist table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'wishlist'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Wishlist table does not exist. Please create it first.', 'debug' => 'table_missing']);
        exit;
    }

    if ($action === 'add') {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $result = $stmt->execute([$userId, $productId]);
        
        error_log("Insert result: " . ($result ? 'success' : 'failed'));
        
        // Get wishlist count
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist',
            'wishlistCount' => (int)$count,
            'inWishlist' => true
        ]);
        
    } elseif ($action === 'remove') {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$userId, $productId]);
        
        error_log("Delete result: " . ($result ? 'success' : 'failed') . ", Rows affected: " . $stmt->rowCount());
        
        // Get wishlist count
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Removed from wishlist',
            'wishlistCount' => (int)$count,
            'inWishlist' => false
        ]);
        
    } elseif ($action === 'check') {
        // Check if product is in wishlist
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $exists = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'inWishlist' => $exists ? true : false
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action', 'debug' => 'action: ' . $action]);
    }
    
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode(),
        'debug' => 'pdo_exception'
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => 'general_exception'
    ]);
}
?>