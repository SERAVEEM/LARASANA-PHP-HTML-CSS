<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact • Larasana</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #060606;
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .title-font { 
            font-family: 'Gelasio', serif; 
        }

        .fade-in {
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.6s ease-out forwards;
        }

        @keyframes fadeInDown {
            to { opacity: 1; transform: translateY(0); }
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

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

        .nav-link:hover::after { width: 100%; }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 180px 150px 100px;
            text-align: center;
        }

        .hero-content {
            max-width: 1000px;
        }

        /* Stats Section */
.stats-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    margin-top: 80px;
    border: 0px solid rgba(255, 255, 255, 1);
    border-radius: 0;
}

.stat-card {
    text-align: center;
    padding: 60px 30px;
    border-right: 2px solid rgba(255, 255, 255, 1);
    border-radius: 0;
    background: transparent;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-out;
}

.stat-card:last-child {
    border-right: none;
}

.stat-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.stat-number {
    font-size: 72px;
    font-weight: 700;
    color: white;
    margin-bottom: 15px;
    line-height: 1;
}

.stat-label {
    font-size: 18px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.4;
}
        /* Impact Icons Section */
        .impact-icons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 60px;
            margin-top: 100px;
        }

        .impact-icon-card {
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .impact-icon-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            transition: all 0.3s ease;
        }

        .impact-icon-card:hover .icon-wrapper {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .icon-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            color: white;
        }

        .icon-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        /* Partnership Section */
        .partnership-section {
            background: rgba(15, 6, 6, 1)
            border-radius: 20px;
            padding: 80px 60px;
            margin-top: 120px;
            text-align: center;
        }

        .partnership-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 60px;
            margin-top: 60px;
            align-items: center;
        }

        .partner-logo {
            height: 80px;
            width: 100%;
            object-fit: contain;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            filter: brightness(0) invert(1);
        }

        .partner-logo:hover {
            opacity: 1;
        }

        /* Image Gallery Section */
        .gallery-section {
            margin-top: 120px;
            position: relative;
        }

        .gallery-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 20px;
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(6, 6, 6, 0.9) 0%, transparent 60%);
            border-radius: 20px;
            display: flex;
            align-items: flex-end;
            padding: 60px;
        }

        .gallery-text h3 {
            font-size: 42px;
            margin-bottom: 20px;
            color: #987A01;
        }

        .gallery-text p {
            font-size: 18px;
            line-height: 1.8;
            max-width: 800px;
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

        @media (max-width: 1200px) {
            .stats-container,
            .impact-icons,
            .partnership-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .hero-section {
                padding: 150px 80px 80px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 140px 30px 60px;
            }

            .stats-container,
            .impact-icons,
            .partnership-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .stat-number {
                font-size: 56px;
            }
            
            .partnership-grid {
                gap: 40px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <header class="w-full flex items-center justify-between px-[150px] py-8 bg-black text-white fixed top-0 left-0 z-40 fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="w-[90px]" />
        <nav class="flex items-center gap-10 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01] fade-in delay-100">Product</a>
            <a href="impact.php" class="text-[#987A01] font-medium fade-in delay-200">Impact</a>
            <a href="about_us.php" class="nav-link hover:text-[#987A01] fade-in delay-200">About Us</a>
        </nav>
        <a href="register.php" class="border-2 border-[#987A01] px-9 py-3 rounded-full text-lg hover:bg-[#987A01] hover:text-white transition fade-in delay-300">
            Sign Up
        </a>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="title-font text-[80px] leading-[1.1] mb-8 fade-in">
                Making an Impact Through Traditional Craftsmanship
            </h1>
            <p class="text-[22px] leading-[1.7] opacity-90 mb-12 fade-in delay-100">
                Every thread we weave tells a story of empowerment, sustainability, and cultural preservation. 
                Together, we're creating meaningful change in the lives of artisan communities across Indonesia.
            </p>
            <a href="products.php" class="inline-block bg-[#987A01] text-white px-12 py-5 rounded-full text-[18px] font-medium hover:bg-[#b39601] transition fade-in delay-200">
                Explore Our Collection
            </a>
        </div>
    </section>

    <!-- Stats Section -->
<section class="px-[150px] py-[100px]">
    <div class="stats-container">
        <div class="stat-card" data-delay="0">
            <div class="stat-number">10</div>
            <div class="stat-label">Years<br>Experience</div>
        </div>
        <div class="stat-card" data-delay="100">
            <div class="stat-number">5</div>
            <div class="stat-label">Provinces in<br>the Country</div>
        </div>
        <div class="stat-card" data-delay="200">
            <div class="stat-number">15k+</div>
            <div class="stat-label">Purchase from<br>1987</div>
        </div>
        <div class="stat-card" data-delay="300">
            <div class="stat-number">500+</div>
            <div class="stat-label">Weavers<br>Empowered</div>
        </div>
    </div>
</section>

    <!-- Impact Section -->
    <section class="px-[150px] py-[80px] scroll-animate">
        <h2 class="title-font text-[56px] text-center mb-[80px]">Impact</h2>
        
        <div class="impact-icons">
            <div class="impact-icon-card" data-delay="0">
                <div class="icon-wrapper">🌾</div>
                <h3 class="icon-title">Cultural Preservation</h3>
                <p class="icon-description">
                    Every thread we weave preserves centuries-old traditions and keeps cultural heritage alive
                </p>
            </div>
            
            <div class="impact-icon-card" data-delay="100">
                <div class="icon-wrapper">💰</div>
                <h3 class="icon-title">Fair Income for Artisans</h3>
                <p class="icon-description">
                    Direct partnerships ensure artisans receive fair compensation for their skilled craftsmanship
                </p>
            </div>
            
            <div class="impact-icon-card" data-delay="200">
                <div class="icon-wrapper">🌱</div>
                <h3 class="icon-title">Sustainable Fashion</h3>
                <p class="icon-description">
                    Our eco-friendly approach reduces environmental impact while creating beautiful textiles
                </p>
            </div>
            
            <div class="impact-icon-card" data-delay="300">
                <div class="icon-wrapper">👥</div>
                <h3 class="icon-title">Community Development</h3>
                <p class="icon-description">
                    We invest in education and training to empower local communities and future generations
                </p>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="px-[150px] gallery-section scroll-animate">
        <div class="relative">
            
                <div class="gallery-text">
                    <h3 class="title-font">Community Empowerment</h3>
                    <p>
                        We believe every thread carries hope. Through direct partnerships with 
                    traditional weavers, we not only preserve cultural heritage, but also 
                    empower local communities by providing access to global markets and ensuring 
                    fair compensation for their expertise.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partnership Section -->
    <section class="px-[150px] py-[80px] scroll-animate">
        <div class="partnership-section">
            <h2 class="title-font text-[48px] mb-6">Trusted Partners</h2>
            <p class="text-[18px] opacity-80 max-w-[700px] mx-auto mb-8">
                Working together with leading organizations to create meaningful impact
            </p>
            
            <div class="partnership-grid">
                <img src="assets/img/partner1.png" alt="Partner 1" class="partner-logo">
                <img src="assets/img/partner2.png" alt="Partner 2" class="partner-logo">
                <img src="assets/img/partner3.png" alt="Partner 3" class="partner-logo">
                <img src="assets/img/partner4.png" class="h-[80px] object-contain partner-slide" data-delay="450" alt="Partner 4" class="partner-logo">
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="px-[150px] py-[120px] text-center scroll-animate">
        <h2 class="title-font text-[56px] mb-6">Join Our Mission</h2>
        <p class="text-[20px] opacity-90 max-w-[800px] mx-auto mb-10">
            Every purchase makes a difference. Be part of preserving Indonesia's 
            rich cultural heritage while supporting local artisan communities.
        </p>
        <a href="products.php" class="inline-block bg-[#987A01] text-white px-12 py-5 rounded-full text-[18px] font-medium hover:bg-[#b39601] transition">
            Explore Our Collection
        </a>
    </section>

    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -80px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Animate children with delays
                    const children = entry.target.querySelectorAll('[data-delay]');
                    children.forEach((child) => {
                        const delay = child.getAttribute('data-delay') || 0;
                        setTimeout(() => {
                            child.classList.add('visible');
                        }, parseInt(delay));
                    });
                }
            });
        }, observerOptions);

        // Observe all animate elements
        document.addEventListener('DOMContentLoaded', () => {
            const statCards = document.querySelectorAll('.stat-card');
            const impactCards = document.querySelectorAll('.impact-icon-card');
            const scrollSections = document.querySelectorAll('.scroll-animate');
            
            statCards.forEach(el => observer.observe(el));
            impactCards.forEach(el => observer.observe(el));
            scrollSections.forEach(el => observer.observe(el));
        });
    </script>

</body>
</html>