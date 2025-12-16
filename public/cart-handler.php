<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false, 'message' => '', 'cartCount' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $productId = (int)$_POST['product_id'];
            $productName = $_POST['product_name'] ?? '';
            $productPrice = (float)$_POST['product_price'];
            $productImage = $_POST['product_image'] ?? '';
            $quantity = (int)$_POST['quantity'];

            if ($productId > 0 && $quantity > 0) {
                // Check if product already in cart
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $productId) {
                        $item['quantity'] += $quantity;
                        $found = true;
                        break;
                    }
                }

                // If not found, add new item
                if (!$found) {
                    $_SESSION['cart'][] = [
                        'id' => $productId,
                        'name' => $productName,
                        'price' => $productPrice,
                        'image' => $productImage,
                        'quantity' => $quantity
                    ];
                }

                $response['success'] = true;
                $response['message'] = 'Product added to cart successfully!';
                $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            } else {
                $response['message'] = 'Invalid product or quantity.';
            }
            break;

        case 'update':
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            if ($productId > 0) {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $productId) {
                        if ($quantity > 0) {
                            $item['quantity'] = $quantity;
                            $response['success'] = true;
                            $response['message'] = 'Cart updated successfully!';
                        } else {
                            $response['message'] = 'Invalid quantity.';
                        }
                        break;
                    }
                }
                $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            }
            break;

        case 'remove':
            $productId = (int)$_POST['product_id'];

            if ($productId > 0) {
                $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
                    return $item['id'] != $productId;
                });
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array

                $response['success'] = true;
                $response['message'] = 'Product removed from cart.';
                $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            }
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            $response['success'] = true;
            $response['message'] = 'Cart cleared.';
            $response['cartCount'] = 0;
            break;

        case 'get_count':
            $response['success'] = true;
            $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            break;

        default:
            $response['message'] = 'Invalid action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);