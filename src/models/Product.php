<?php
class Product {

    //get all prouducts
    public static function all() {
        global $pdo;
        $stmt = $pdo->query("
            SELECT p.*, c.name AS category 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //find search product by id
    public static function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //create
    public static function create($data) {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO products (name, slug, price, description, category_id, image)
            VALUES (:name, :slug, :price, :description, :category_id, :image)
        ");

        return $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':price' => $data['price'],
            ':description' => $data['description'],
            ':category_id' => $data['category_id'],
            ':image' => $data['image'] ?? null
        ]);
    }

    //update
    public static function update($id, $data) {
        global $pdo;

        $stmt = $pdo->prepare("
            UPDATE products
            SET name = :name,
                slug = :slug,
                price = :price,
                description = :description,
                category_id = :category_id,
                image = :image,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':price' => $data['price'],
            ':description' => $data['description'],
            ':category_id' => $data['category_id'],
            ':image' => $data['image'] ?? null
        ]);
    }

    //delete product
    public static function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
