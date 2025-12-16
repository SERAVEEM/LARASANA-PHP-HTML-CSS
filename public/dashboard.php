<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larasana • Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0a;
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .title-font { 
            font-family: 'Gelasio', serif; 
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 60px;
            background: #060606;
            border-bottom: 1px solid rgba(152, 122, 1, 0.2);
            z-index: 50;
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

        .navbar-logo {
            width: 80px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 20px;
            background: rgba(152, 122, 1, 0.1);
            border-radius: 50px;
            border: 1px solid rgba(152, 122, 1, 0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #987A01, #7a6201);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #987A01;
            text-transform: capitalize;
        }

        .btn-logout {
            padding: 10px 24px;
            background: transparent;
            border: 2px solid rgba(239, 68, 68, 0.5);
            color: #ef4444;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }

        /* Main content */
        .main-content {
            margin-top: 100px;
            padding: 60px;
            min-height: calc(100vh - 100px);
        }

        /* Welcome section */
        .welcome-section {
            max-width: 1200px;
            margin: 0 auto 60px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-title {
            font-size: 56px;
            font-weight: 600;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #987A01 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }

        .welcome-role {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(152, 122, 1, 0.15);
            color: #987A01;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Dashboard cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-card {
            background: #0d0d0d;
            border: 1px solid rgba(152, 122, 1, 0.1);
            border-radius: 20px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translateY(30px);
        }

        .dashboard-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            border-color: rgba(152, 122, 1, 0.4);
            box-shadow: 0 20px 60px rgba(152, 122, 1, 0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: rgba(152, 122, 1, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
            color: white;
        }

        .card-description {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .card-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #987A01;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .card-link:hover {
            background: #7a6201;
            transform: translateX(4px);
        }

        .card-link-secondary {
            background: transparent;
            border: 2px solid #987A01;
            color: #987A01;
        }

        .card-link-secondary:hover {
            background: rgba(152, 122, 1, 0.1);
        }

        /* Quick stats (for admin) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto 40px;
            opacity: 0;
            animation: fadeIn 0.8s ease-out 0.4s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(152, 122, 1, 0.1) 0%, rgba(152, 122, 1, 0.05) 100%);
            border: 1px solid rgba(152, 122, 1, 0.2);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #987A01;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="/">
            <img src="/assets/img/logo.png" alt="Larasana" class="navbar-logo">
        </a>
        
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($user['role']) ?></span>
                </div>
            </div>
            <a href="/logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title title-font">Welcome Back!</h1>
            <p class="welcome-subtitle">Hello, <?= htmlspecialchars($user['name']) ?></p>
            <span class="welcome-role"><?= htmlspecialchars($user['role']) ?></span>
        </div>

        <?php if($user['role'] === 'admin'): ?>
            <!-- Admin Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Orders Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">$0</div>
                    <div class="stat-label">Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            
            <!-- Home Card -->
            <div class="dashboard-card">
                <div class="card-icon">🏠</div>
                <h2 class="card-title">Browse Collections</h2>
                <p class="card-description">
                    Explore our curated selection of handcrafted batik pieces and traditional textiles.
                </p>
                <a href="/" class="card-link card-link-secondary">
                    Go to Homepage
                    <span>→</span>
                </a>
            </div>

            <!-- Products Card -->
            <div class="dashboard-card">
                <div class="card-icon">🛍️</div>
                <h2 class="card-title">Shop Products</h2>
                <p class="card-description">
                    Discover unique, heritage-inspired products that tell a story of culture and craftsmanship.
                </p>
                <a href="/products.php" class="card-link card-link-secondary">
                    View Products
                    <span>→</span>
                </a>
            </div>

            <?php if($user['role'] === 'admin'): ?>
            <!-- Admin Products Management -->
            <div class="dashboard-card">
                <div class="card-icon">⚙️</div>
                <h2 class="card-title">Manage Products</h2>
                <p class="card-description">
                    Add, edit, or remove products from your collection. Full admin control over inventory.
                </p>
                <a href="/admin/products.php" class="card-link">
                    Admin Panel
                    <span>→</span>
                </a>
            </div>

            <!-- Analytics Card -->
            <div class="dashboard-card">
                <div class="card-icon">📊</div>
                <h2 class="card-title">Analytics</h2>
                <p class="card-description">
                    View detailed insights, sales reports, and customer behavior analytics.
                </p>
                <a href="#" class="card-link">
                    View Analytics
                    <span>→</span>
                </a>
            </div>

            <!-- Orders Card -->
            <div class="dashboard-card">
                <div class="card-icon">📦</div>
                <h2 class="card-title">Orders</h2>
                <p class="card-description">
                    Manage customer orders, track shipments, and handle order fulfillment.
                </p>
                <a href="#" class="card-link">
                    View Orders
                    <span>→</span>
                </a>
            </div>

            <!-- Customers Card -->
            <div class="dashboard-card">
                <div class="card-icon">👥</div>
                <h2 class="card-title">Customers</h2>
                <p class="card-description">
                    View and manage customer accounts, profiles, and purchase history.
                </p>
                <a href="#" class="card-link">
                    View Customers
                    <span>→</span>
                </a>
            </div>
            <?php endif; ?>

        </div>

    </main>

    <script>
        // Intersection Observer for dashboard cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach(card => observer.observe(card));
        });
    </script>

</body>
</html>