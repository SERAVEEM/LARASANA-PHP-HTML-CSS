<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['id'])) {
  echo json_encode(['success' => false]);
  exit;
}

$id = (int) $_POST['id'];
$name  = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$image = $_POST['image'] ?? '';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$found = false;

foreach ($_SESSION['cart'] as &$item) {
  if ($item['id'] == $id) {
    $item['qty'] += 1;
    $found = true;
    break;
  }
}

if (!$found) {
  $_SESSION['cart'][] = [
    'id'    => $id,
    'name'  => $name,
    'price' => $price,
    'image' => $image,
    'qty'   => 1
  ];
}

echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
exit;
