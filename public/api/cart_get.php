<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
  'success' => true,
  'cart' => $_SESSION['cart'] ?? []
]);
exit;
