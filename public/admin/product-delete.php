<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/middleware/auth.php';
require_once __DIR__ . '/../../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if(($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403); echo 'Forbidden'; exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    Product::delete($id);
}
header('Location: /admin/products.php');
exit;
