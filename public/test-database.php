<?php
// DEBUG    : public/test-database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Try to include database config
try {
    require_once __DIR__ . '/../config/database.php';
    echo "✓ Database config file loaded<br>";
} catch (Exception $e) {
    echo "✗ Error loading database config: " . $e->getMessage() . "<br>";
    exit;
}

// Check connection
if (!isset($conn)) {
    echo "✗ \$conn variable not found<br>";
    exit;
}

if ($conn->connect_error) {
    echo "✗ Connection failed: " . $conn->connect_error . "<br>";
    exit;
}

echo "✓ Database connected successfully<br>";
echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br><br>";

// Check if orders table exists
echo "<h3>Checking Tables:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "✓ 'orders' table exists<br>";
    
    // Show table structure
    echo "<pre>";
    $structure = $conn->query("DESCRIBE orders");
    while ($row = $structure->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "✗ 'orders' table NOT found<br>";
    echo "<p style='color: red;'>You need to create the orders table. Run the SQL schema in phpMyAdmin.</p>";
}

$result = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($result->num_rows > 0) {
    echo "✓ 'order_items' table exists<br>";
    
    // Show table structure
    echo "<pre>";
    $structure = $conn->query("DESCRIBE order_items");
    while ($row = $structure->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "✗ 'order_items' table NOT found<br>";
    echo "<p style='color: red;'>You need to create the order_items table. Run the SQL schema in phpMyAdmin.</p>";
}

// Check session
echo "<h3>Session Check:</h3>";
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "✓ Cart exists in session with " . count($_SESSION['cart']) . " items<br>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
} else {
    echo "✗ Cart is empty or not set<br>";
}

if (isset($_SESSION['order_totals'])) {
    echo "✓ Order totals in session:<br>";
    echo "<pre>";
    print_r($_SESSION['order_totals']);
    echo "</pre>";
} else {
    echo "⚠ Order totals not in session (will be calculated)<br>";
}

echo "<br><h3>Test Insert:</h3>";
try {
    $test_sql = "INSERT INTO orders (first_name, last_name, email, phone, address, city, state, zip, country, subtotal, shipping, tax, total, status, created_at) 
                 VALUES ('Test', 'User', 'test@test.com', '1234567890', '123 Test St', 'Test City', 'TS', '12345', 'Test Country', 100.00, 0.00, 0.00, 100.00, 'pending', NOW())";
    
    if ($conn->query($test_sql)) {
        $test_id = $conn->insert_id;
        echo "✓ Test insert successful! Order ID: " . $test_id . "<br>";
        
        // Clean up test
        $conn->query("DELETE FROM orders WHERE id = " . $test_id);
        echo "✓ Test order cleaned up<br>";
    } else {
        echo "✗ Test insert failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "✗ Test insert error: " . $e->getMessage() . "<br>";
}

$conn->close();

echo "<br><h3>Summary:</h3>";
echo "If all checks pass above, your database is configured correctly.<br>";
echo "If you see errors, please fix them before trying checkout again.<br>";
?>