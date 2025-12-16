<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Cart count is useful in header (some pages show it); safe to compute if available
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Larasana • About Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Gelasio:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #060606; color: white; margin: 0; }
        .title-font { font-family: 'Gelasio', serif; }
        .fade-in { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s ease-out forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); }}

        header { padding: 28px 150px; }
        header img { width: 90px; }
        nav a { margin: 0 12px; }

        .hero { padding: 120px 150px 40px; text-align: left; display: grid; grid-template-columns: 1fr 420px; gap: 40px; align-items: center; }
        .hero .title { font-size: 56px; font-weight: 700; color: linear-gradient(135deg, #fff, #987A01); }
        .hero p { color: rgba(255,255,255,0.75); font-size: 18px; line-height: 1.6; }

        .features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 40px 150px; }
        .feature-card { background: #0d0d0d; border: 1px solid rgba(152, 122, 1, 0.08); padding: 20px; border-radius: 12px; }
        .feature-card h4 { color: #987A01; }

        /* Case Description / Problems */
        .case { background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.00)); padding: 40px 150px; border-top: 1px solid rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.02); }
        .case h2 { color: #fff; font-size: 26px; margin-bottom: 12px; }
        .case p { color: rgba(255,255,255,0.78); margin-bottom: 10px; }

        .problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 30px 150px; }
        .problem-card { background: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); padding: 18px; border-radius: 12px; }
        .problem-card h4 { color: #987A01; margin-bottom: 8px; }

        .approach { padding: 30px 150px; display: grid; grid-template-columns: 1fr; gap: 10px; }
        .approach h3 { color: #fff; margin-bottom: 8px; }
        .approach p { color: rgba(255,255,255,0.8); }

        .team-section { padding: 40px 150px; }
        .team-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .team-card { background: #0f0f0f; border: 1px solid rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; text-align: center; }
        .team-avatar { width: 80px; height: 80px; border-radius: 50%; background: rgba(152,122,1,0.12); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 20px; margin-bottom: 12px; }

        .cta { padding: 40px 150px 100px; display: flex; justify-content: center; }
        .btn { padding: 14px 26px; border-radius: 12px; background: #987A01; color: white; border: none; font-weight: 600; text-decoration: none; }

        .contact { padding: 60px 150px; background: #0d0d0d; border-top: 1px solid rgba(255,255,255,0.05); }
        .contact h2 { font-size: 28px; margin-bottom: 12px; color: #987A01; }
        .contact p { color: rgba(255,255,255,0.8); font-size: 16px; margin-bottom: 6px; }

        @media (max-width: 1000px) {
            .hero { grid-template-columns: 1fr; padding: 60px 30px; }
            .features { grid-template-columns: 1fr; padding: 20px 30px; }
            .team-grid { grid-template-columns: repeat(2, 1fr); }
            header { padding: 18px 24px; }
            .cta, .team-section, .features { padding-left: 24px; padding-right: 24px; }
        }
    </style>
</head>
<body>
    <header class="w-full flex items-center justify-between fade-in">
        <img src="assets/img/logo.png" alt="Logo" class="title-font">
        <nav class="flex items-center gap-6 text-lg">
            <a href="index.php" class="nav-link hover:text-[#987A01]">Home</a>
            <a href="products.php" class="nav-link hover:text-[#987A01]">Product</a>
            <a href="impact.php" class="nav-link hover:text-[#987A01]">Impact</a>
            <a href="about_us.php" class="text-[#987A01] font-medium">About Us</a>
        </nav>
        <a href="register.php" class="border-2 border-[#987A01] px-7 py-3 rounded-full text-lg btn-hover">Sign Up</a>
    </header>

    <main>
        <section class="hero">
            <div>
                <h1 class="title title-font">About Larasana</h1>
                <p class="fade-in">We are a small team dedicated to sharing Indonesian batik craftsmanship with the world. This project showcases handcrafted batik pieces through a simple marketplace built for browsing, managing, and purchasing artisanal products.</p>
                <p class="fade-in" style="margin-top:12px;">Our goal is to make it easy for makers and collectors to connect — with features for product listings, admin product management, image uploads, category filtering, and a lightweight cart workflow.</p>
            </div>
            <aside>
                <div style="background:#0d0d0d;padding:20px;border-radius:12px;border:1px solid rgba(152,122,1,0.08);">
                    <h3 class="title-font" style="margin-top:0;margin-bottom:10px;color:#987A01;">What we build</h3>
                    <ul style="margin:0;padding-left:18px;color:rgba(255,255,255,0.8);">
                        <li>Responsive product catalog and detail pages</li>
                        <li>Floating cart & add-to-cart sidebar</li>
                        <li>Admin pages for creating and editing products</li>
                        <li>Image uploads, categories, and product previews</li>
                    </ul>
                </div>
            </aside>
        </section>

        <!-- Case Description -->
        <section class="case">
            <h2 class="title-font">Case Description</h2>
            <p>
                Larasana is a digital platform developed to empower women, especially the traditional weaving mothers in Lombok. For generations, weaving has been part of Lombok's cultural identity, but artisans have not always received economic benefits proportional to their craftsmanship.
            </p>
            <p>
                Many weavers work in irregular jobs, earn unstable incomes, and lack reliable access to markets beyond their local area. Larasana helps by creating digital market access, enabling weavers to sell directly to wider and international audiences while giving buyers context about the cultural value of each piece.
            </p>
            <p>
                The site is built to be simple, secure, and capable of showcasing each woven product attractively to increase visibility, strengthen pricing, and support sustainable economic opportunities for women in the community.
            </p>
        </section>

        <!-- Problems / Challenges -->
        <section class="problems">
            <div class="problem-card">
                <h4>1. Limited Market Access</h4>
                <p>Most weavers sell locally and lack channels to reach broader regional or international markets.</p>
            </div>
            <div class="problem-card">
                <h4>2. No Stable Income</h4>
                <p>Income often depends on seasonal orders; when demand is low, earnings fall sharply.</p>
            </div>
            <div class="problem-card">
                <h4>3. Limited Digital Support</h4>
                <p>Artisans are often not connected to digital platforms and face technology barriers that prevent online sales.</p>
            </div>
            <div class="problem-card">
                <h4>4. Prices Don't Reflect Value</h4>
                <p>Because of weak distribution channels, pieces are often sold cheaply, not reflecting the time and skill invested.</p>
            </div>
            <div class="problem-card">
                <h4>5. Lack of Sustainable Support</h4>
                <p>There is no consistent system for mentoring, transaction tracking, product management, and brand-building for artisans.</p>
            </div>
            <div class="problem-card">
                <h4>6. Disconnect Between Culture & Commerce</h4>
                <p>Handwoven cloth is cultural heritage; it needs a platform that tells its story while helping artisans earn stable income.</p>
            </div>
        </section>

        <!-- Approach -->
        <section class="approach">
            <h3 class="title-font">How Larasana Helps</h3>
            <p>Larasana combines marketplace features, storytelling, and administrative tools to help artisans present their work, manage listings, and access buyers.</p>
            <p>We focus on: digital market access, product storytelling (context and maker stories), streamlined admin tools, transparent pricing guidance, and partnerships to expand distribution and training.</p>
        </section>

        <section class="features">
            <div class="feature-card">
                <h4>Curated Collections</h4>
                <p>Each product is handpicked and presented with detailed images and descriptions.</p>
            </div>
            <div class="feature-card">
                <h4>Easy Admin Management</h4>
                <p>Admin interface lets you create, edit, and delete products, including images and categories.</p>
            </div>
            <div class="feature-card">
                <h4>Lightweight Checkout</h4>
                <p>A simple cart flow allows visitors to add items and check out quickly.</p>
            </div>
        </section>

        <section class="team-section">
            <h2 class="title-font" style="font-size:28px;margin-bottom:18px;">Meet the Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">L</div>
                    <h4>Founder & Curator</h4>
                    <p style="color:rgba(255,255,255,0.75);font-size:14px;">Guided the vision, curated batik collections, and maintains the artisan relationships.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">D</div>
                    <h4>Lead Developer</h4>
                    <p style="color:rgba(255,255,255,0.75);font-size:14px;">Built the product catalog, cart system, and admin tools used across the site.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">U</div>
                    <h4>UX & Visual Designer</h4>
                    <p style="color:rgba(255,255,255,0.75);font-size:14px;">Designed the product imagery and user experience across pages.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">C</div>
                    <h4>Customer Success</h4>
                    <p style="color:rgba(255,255,255,0.75);font-size:14px;">Handles inquiries and helps customers find the right pieces.</p>
                </div>
            </div>
        </section>

        

        <section class="cta">
            <a href="products.php" class="btn">Browse Collections</a>
        </section>

        <section class="contact fade-in">
        <h2 class="title-font">Contact Us</h2>
        <p><strong>Phone:</strong> 0821-3394-8400</p>
        <p><strong>Email:</strong> larasana@gmail.com</p>
        </section>
    </main>
</body>
</html>
