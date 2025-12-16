<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, name, price, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
exit;
