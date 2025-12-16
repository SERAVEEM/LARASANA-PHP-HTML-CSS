<?php
// src/models/User.php
class User {
    public static function findByEmail($email) {
        global $pdo;

        // 1. Coba cari di users
        $stmt = $pdo->prepare("SELECT *, 'user' AS role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) return $result;

        // 2. Kalau tidak ada, coba di admin
        $stmt = $pdo->prepare("SELECT *, 'admin' AS role FROM admin WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        global $pdo;
        // Include balance if the column exists (fallback to 0 if not)
        $stmt = $pdo->prepare('SELECT id, name, email, role, created_at, IFNULL(balance, 0) as balance FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateBalance($id, $newBalance) {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
        return $stmt->execute([$newBalance, $id]);
    }

    public static function create($data) {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
            // Ensure balance is set and integer; default to 200
            $balance = isset($data['balance']) ? (int)$data['balance'] : 200;
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, balance)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'],
                $data['email'],
                $data['password'],
                $data['role'],
                $balance
            ]);
    }
}
