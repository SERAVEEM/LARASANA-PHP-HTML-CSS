<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Product.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Fetch best selling products
try {
    $stmt = $pdo->query("SELECT * FROM products LIMIT 4");
    $bestSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bestSellingProducts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Larasana • Home</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        .title-font { font-family: 'Gelasio', serif; }
        
        /* Initial hidden states */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .fade-in-left {
            opacity: 0;
            transform: translateX(-40px);
            animation: fadeInLeft 0.8s ease-out forwards;
        }
        
        .fade-in-right {
            opacity: 0;
            transform: translateX(40px);
            animation: fadeInRight 0.8s ease-out forwards;
        }
        
        .scale-in {
            opacity: 0;
            transform: scale(0.9);
            animation: scaleIn 0.6s ease-out forwards;
        }

        /* Keyframe animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Staggered delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
        .delay-700 { animation-delay: 0.7s; }

        /* Smooth hover transitions */
        .btn-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(152, 122, 1, 0.3);
        }

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

        /* Image reveal effect */
        .image-reveal {
            position: relative;
            overflow: hidden;
        }

        .image-reveal img {
            animation: imageZoom 1.2s cubic-bezier(0.77, 0, 0.175, 1) 0.3s forwards;
            transform: scale(1.1);
        }

        @keyframes imageZoom {
            to {
                transform: scale(1);
            }
        }

        .image-reveal::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(6, 6, 6, 0.8) 0%, rgba(6, 6, 6, 0) 100%);
            opacity: 1;
            animation: fadeOutOverlay 1s ease-out 0.5s forwards;
            z-index: 1;
        }

        @keyframes fadeOutOverlay {
            to {
                opacity: 0;
            }
        }

        /* Scroll animations */
        .scroll-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .partner-slide {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .partner-slide.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .partner-section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .partner-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Gallery grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 500px;
        }

        .gallery-item {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }

        .gallery-item.visible {
            opacity: 1;
            transform: scale(1);
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        /* Product card */
        .product-card {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .product-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-image {
            transition: transform 0.4s ease;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="bg-[#060606] text-white">

    <!-- ================= NAVBAR ================= -->
    <header class="w-full flex items-center justify-between px-[150px] py-8 fade-in">
      
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px] scale-in" />

        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="text-[#987A01] font-medium fade-in delay-100">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01] fade-in delay-200">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01] fade-in delay-300">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-400">About Us</a>
        </nav>

        <a href="register.php" class="border-2 border-[#987A01] px-9 py-3 rounded-full text-lg btn-hover fade-in delay-500">
            Sign Up
        </a>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="relative flex w-full justify-between px-[150px] mt-[80px]">

        <!-- Left Text -->
        <div class="flex flex-col gap-6 max-w-[620px] mt-10">

            <h1 class="title-font text-[78px] leading-[1.15] fade-in-left delay-200">
                Preserving Culture, One Thread at a Time
            </h1>

            <p class="text-[20px] leading-[1.4] opacity-90 fade-in-left delay-400">
                Every piece of cloth tells a story of skill, soul, and heritage.
                proudly handmade for those who value culture and impact.
            </p>

            <a href="products.php" class="border-2 border-[#987A01] px-9 py-4 rounded-full inline-flex items-center justify-center mt-2 w-fit btn-hover fade-in-left delay-600">
              <span class="text-[20px] font-medium">Shop Now</span>
            </a>
        </div>

        <!-- Hero Image -->
        <div class="relative image-reveal fade-in-right delay-300">
            <img 
                src="assets/img/hero-image.jpg" 
                class="w-[560px] h-[760px] object-cover"
                alt="Hero">
            
            <!-- Gradient overlays -->
            <div class="absolute bottom-0 left-0 w-full h-[260px] 
                        bg-gradient-to-t from-[#060606] to-transparent"></div>

            <div class="absolute top-0 left-0 w-full h-[240px] 
                        bg-gradient-to-b from-[#060606] to-transparent"></div>
        </div>
    </section>

    <!-- ================= PARTNER SECTION ================= -->
    <section class="w-full bg-[#EFEFEF] mt-[120px] py-12 flex justify-center overflow-hidden partner-section">
        <div class="flex items-center gap-[100px] px-[150px]">
            <img src="assets/img/partner1.png" class="h-[80px] object-contain partner-slide" data-delay="0">
            <img src="assets/img/partner2.png" class="h-[80px] object-contain partner-slide" data-delay="150">
            <img src="assets/img/partner3.png" class="h-[80px] object-contain partner-slide" data-delay="300">
            <img src="assets/img/partner4.png" class="h-[80px] object-contain partner-slide" data-delay="450">
        </div>
    </section>

    <!-- ================= SETIAP HELAI BENANG SECTION ================= -->
    <section class="w-full px-[150px] py-[120px] flex items-center justify-between gap-[100px] scroll-animate">
        
        <!-- Gallery Grid -->
        <div class="gallery-grid">
            <img src="assets/img/f1.png" class="gallery-item" data-delay="0" style="grid-column: span 2; height: 240px;">
            <img src="assets/img/f2.png" class="gallery-item" data-delay="100" style ="height: 240px;">
            <img src="assets/img/f3.png" class="gallery-item" data-delay="200">
            <img src="assets/img/f4.png" class="gallery-item" data-delay="300">
            <img src="assets/img/f5.png" class="gallery-item" data-delay="400">
            <img src="assets/img/f6.png" class="gallery-item" data-delay="500" style="grid-column: span 2;">
            <img src="assets/img/f7.png" class="gallery-item" data-delay="600">
        </div>

        <!-- Text Content -->
        <div class="flex flex-col gap-6 max-w-[600px]">
            <h2 class="title-font text-[56px] leading-[1.2]">
                Setiap Helai Benang, Sebuah Cerita
            </h2>
            <p class="text-[18px] leading-[1.7] opacity-90">
                Di Larasana, kami percaya bahwa setiap kain tenun memiliki jiwa. 
                Kami bekerja sama langsung dengan para maestro penenun di pelosok 
                Lombok, memastikan setiap motif ditenun dengan penuh dedikasi dan 
                cinta. Setiap pembelian Anda tidak hanya membawa keindahan untuk 
                tradisi langsung ke tangan Anda.
            </p>
            <a href="products.php" class="bg-[#987A01] text-white px-8 py-4 rounded-full w-fit btn-hover mt-4">
                <span class="text-[18px] font-medium">Jelajahi Koleksi</span>
            </a>
        </div>
    </section>

    <!-- ================= BEST SELLING PRODUCTS ================= -->
    <section class="w-full px-[150px] py-[80px] scroll-animate">
        <h2 class="title-font text-[56px] text-center mb-[80px]">Our Product</h2>
        
        <div class="grid grid-cols-4 gap-8">
            <?php if (count($bestSellingProducts) > 0): ?>
                <?php foreach ($bestSellingProducts as $index => $product): ?>
                <div class="product-card flex flex-col gap-4" data-delay="<?php echo $index * 100; ?>">
                    <div class="relative overflow-hidden rounded-lg bg-[#EFEFEF] aspect-[3/4]">
                        <img 
                            src="<?php echo htmlspecialchars($product['image'] ?? 'assets/img/index-asset.jpg'); ?>" 
                            alt="<?php echo htmlspecialchars($product['name'] ?? 'Product'); ?>"
                            class="w-full h-full object-cover product-image">
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <span class="text-[14px] text-[#987A01] uppercase tracking-wider">
                            <?php echo htmlspecialchars($product['category'] ?? 'Tenun'); ?>
                        </span>
                        <h3 class="title-font text-[22px] leading-tight">
                            <?php echo htmlspecialchars($product['name'] ?? 'Tenun Product'); ?>
                        </h3>
                        <p class="text-[14px] opacity-70">
                            <?php echo htmlspecialchars($product['description'] ?? 'Combination of wood and wool'); ?>
                        </p>
                        <span class="text-[20px] font-semibold text-[#987A01] mt-2">
                            $<?php echo number_format($product['price'] ?? 63.47, 2); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback products if database is empty -->
                <div class="product-card flex flex-col gap-4" data-delay="0">
                    <div class="relative overflow-hidden rounded-lg bg-[#EFEFEF] aspect-[3/4]">
                        <img src="assets/img/index-asset.jpg" alt="Tenun Product" class="w-full h-full object-cover product-image">
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-[14px] text-[#987A01] uppercase tracking-wider">Blazer</span>
                        <h3 class="title-font text-[22px] leading-tight">Tenun Blazer</h3>
                        <p class="text-[14px] opacity-70">Combination of wood and wool</p>
                        <span class="text-[20px] font-semibold text-[#987A01] mt-2">$63.47</span>
                    </div>
                </div>
                <div class="product-card flex flex-col gap-4" data-delay="100">
                    <div class="relative overflow-hidden rounded-lg bg-[#EFEFEF] aspect-[3/4]">
                        <img src="assets/img/index-asset.jpg" alt="Tenun Product" class="w-full h-full object-cover product-image">
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-[14px] text-[#987A01] uppercase tracking-wider">Sarong</span>
                        <h3 class="title-font text-[22px] leading-tight">Tenun Ikat Troso</h3>
                        <p class="text-[14px] opacity-70">Combination of wood and wool</p>
                        <span class="text-[20px] font-semibold text-[#987A01] mt-2">$63.47</span>
                    </div>
                </div>
                <div class="product-card flex flex-col gap-4" data-delay="200">
                    <div class="relative overflow-hidden rounded-lg bg-[#EFEFEF] aspect-[3/4]">
                        <img src="assets/img/index-asset.jpg" alt="Tenun Product" class="w-full h-full object-cover product-image">
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-[14px] text-[#987A01] uppercase tracking-wider">Vest</span>
                        <h3 class="title-font text-[22px] leading-tight">Trosorompi Tenun</h3>
                        <p class="text-[14px] opacity-70">Combination of wood and wool</p>
                        <span class="text-[20px] font-semibold text-[#987A01] mt-2">$63.47</span>
                    </div>
                </div>
                <div class="product-card flex flex-col gap-4" data-delay="300">
                    <div class="relative overflow-hidden rounded-lg bg-[#EFEFEF] aspect-[3/4]">
                        <img src="assets/img/index-asset.jpg" alt="Tenun Product" class="w-full h-full object-cover product-image">
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-[14px] text-[#987A01] uppercase tracking-wider">Tenun</span>
                        <h3 class="title-font text-[22px] leading-tight">Classic Tenun</h3>
                        <p class="text-[14px] opacity-70">Combination of wood and wool</p>
                        <span class="text-[20px] font-semibold text-[#987A01] mt-2">$63.47</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<!-- ================= BOTTOM WARISAN SECTION ================= -->
    <section class="relative w-full h-[480px] mt-[120px] overflow-hidden scroll-animate">
        <img src="assets/img/last index.png" class="w-full h-full object-cover" alt="Warisan Background">
        
        <!-- Gradient overlays (same as hero) -->
        <div class="absolute bottom-0 left-0 w-full h-[200px] 
                    bg-gradient-to-t from-black to-transparent"></div>
        <div class="absolute top-0 left-0 w-full h-[180px] 
                    bg-gradient-to-b from-black to-transparent"></div>
        
        <!-- Content -->
        <div class="absolute inset-0 flex items-center justify-center px-[150px]">
            <div class="text-center max-w-[800px]">
                <h2 class="title-font text-[#987A01] text-[56px] leading-[1.2] mb-6">
                    Sebuah Warisan dalam Setiap Helai Benang
                </h2>
                <p class="text-[20px] leading-[1.7] text-white opacity-90 mb-8">
                    Jauh sebelum menjadi kain yang indah, tenun adalah warisan turun temurun yang mencakup keahlian, filosofi, dan kebanggaan lokal.
                </p>
                <a href="products.php" class="inline-block bg-[#987A01] text-white px-10 py-4 rounded-full btn-hover">
                    <span class="text-[18px] font-medium">Explore Our Collection</span>
                </a>
            </div>
        </div>
    </section>

    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Animate child elements with delays
                    const items = entry.target.querySelectorAll('[data-delay]');
                    items.forEach((item) => {
                        const delay = item.getAttribute('data-delay') || 0;
                        setTimeout(() => {
                            item.classList.add('visible');
                        }, parseInt(delay));
                    });
                }
            });
        }, observerOptions);

        // Observe all scroll-animate elements
        document.addEventListener('DOMContentLoaded', () => {
            const animateElements = document.querySelectorAll('.scroll-animate, .partner-section');
            animateElements.forEach(el => observer.observe(el));
        });
    </script>

</body>
</html>