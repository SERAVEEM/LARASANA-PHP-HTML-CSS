<?php
class Cart {

    public static function getOrCreateCart($user_id) {
        global $pdo;

        // cek cart yang sudah ada
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart['id'];
        }

        // kalau belum ada → buat cart baru
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);

        return $pdo->lastInsertId();
    }

    public static function addItem($user_id, $product_id) {
        global $pdo;

        // pastikan user punya cart
        $cart_id = self::getOrCreateCart($user_id);

        // cek apakah item sudah ada di cart
        $stmt = $pdo->prepare("
            SELECT id, quantity FROM cart_items 
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->execute([$cart_id, $product_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // update quantity
            $stmt = $pdo->prepare("
                UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?
            ");
            $stmt->execute([$item['id']]);
        } else {
            // tambahkan item baru
            $stmt = $pdo->prepare("
                INSERT INTO cart_items (cart_id, product_id, quantity)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$cart_id, $product_id]);
        }

        return true;

    }
        public static function getUserCart($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, p.image, ci.quantity
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
