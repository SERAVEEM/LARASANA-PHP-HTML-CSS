<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (AuthController::login($email, $password)) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larasana • Login</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-image: url('assets/img/pattern-bg.jpg');
            background-repeat: no-repeat;
            background-position: center top;
            background-size: cover;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
        }

        .title-font { 
            font-family: 'Gelasio', serif; 
        }

        /* Navbar animations */
        .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.6s ease-out forwards;
        }

        @keyframes fadeInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        /* Card animations */
        .card {
            width: 100%;
            max-width: 520px;
            padding: 60px 50px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(152, 122, 1, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            animation: cardEnter 0.8s ease-out 0.3s forwards;
        }

        @keyframes cardEnter {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Input field animations */
        .field {
            margin-bottom: 24px;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.5s ease-out forwards;
        }

        .field:nth-child(1) { animation-delay: 0.6s; }
        .field:nth-child(2) { animation-delay: 0.75s; }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }

        .field input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(152, 122, 1, 0.2);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .field input:focus {
            outline: none;
            border-color: #987A01;
            background: white;
            box-shadow: 0 0 0 4px rgba(152, 122, 1, 0.1);
        }

        /* Button animation */
        .btn {
            width: 100%;
            padding: 16px;
            background: #987A01;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease-out 0.9s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn:hover {
            background: #7a6201;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(152, 122, 1, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Footer text animation */
        .footer-text {
            opacity: 0;
            animation: fadeIn 0.5s ease-out 1.05s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Error message */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Nav link hover effect */
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #987A01;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .page {
            padding-top: 140px;
        }

        .wrapper {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
    </style>
</head>

<body>
    <header class="w-full flex items-center justify-between px-[150px] py-8 bg-black text-white fixed top-0 left-0 z-40 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px]" />
        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01] fade-in delay-200">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-200">About Us</a>
        </nav>
        <a href="register.php" class="border-2 border-[#987A01] px-8 py-3 rounded-full text-lg hover:bg-[#987A01] hover:text-white transition fade-in delay-300">
            Sign Up
        </a>
    </header>

    <div class="page">
        <div class="wrapper">
            <div class="card">
                <h2 class="title-font text-4xl font-semibold mb-8 text-center text-gray-800">Login</h2>

                <?php if($error): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?> 

                <form method="post">
                    <div class="field">
                        <label>Email</label>
                        <input name="email" type="email" placeholder="Enter your email" required>
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <input name="password" type="password" placeholder="Enter your password" required>
                    </div>

                    <button class="btn">Login</button>
                    
                    <p class="text-center mt-6 text-gray-600 footer-text">
                        Don't have an account yet? <a href="register.php" class="text-[#987A01] font-medium hover:underline">Sign Up</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>
</html>