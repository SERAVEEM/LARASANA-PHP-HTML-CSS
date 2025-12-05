<?php
// src/middleware/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_auth() {
    if (!isset($_SESSION['user'])) {
        $_SESSION ['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

function require_admin() {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo 'Forbidden - admin only';
        exit;
    }
}
