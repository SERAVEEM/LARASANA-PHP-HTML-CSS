<?php
// src/controllers/AuthController.php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public static function register($name, $email, $password, $balance = 200) {
        // basic validation left to caller
        return
        //  User::create($name, $email, $password, 'user');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'user'
        ] + ['balance' => (int)$balance]);
    }

    public static function login($email, $password) {
        $user = User::findByEmail($email);

        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;

        // remove password before storing in session
        unset($user['password']);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        return true;

        //redirect ke halaman semula sebelum login
        if(!empty($_SESSION['redirect_after_login'])){
            $redirect_url = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect_url");
            exit;
        }

        //default redirect to dashboard
        header('Location: /dashboard.php');
        exit;

    }

    public static function logout() {
        session_unset();
        session_destroy();
    }
}
