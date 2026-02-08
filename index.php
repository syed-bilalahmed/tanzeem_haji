<?php include 'frontend_header.php'; ?>

<!-- Hero Section -->
<section id="home" class="hero-section d-flex align-items-center" style="min-height: 90vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10" data-aos="fade-up" data-aos-duration="1500">
                <h1 class="hero-title fw-bold">
                    <?php echo ($lang == 'en') ? ($details['hero_title_en'] ?? 'Welcome to Tanzeem e Aulad Hazrat Haji Bahadur Since 1657') : ($details['hero_title'] ?? 'خوش آمدید'); ?>
                </h1>
                <div class="motto-strip mb-4" data-aos="fade-up" data-aos-delay="200">
                    <span class="badge bg-dark-subtle text-white px-4 py-2 rounded-pill shadow-sm fs-5 border border-white border-opacity-25" style="background: rgba(0,0,0,0.3) !important;">
                        <?php echo ($lang == 'en') ? 'Unity • Respect • Management' : 'اتحاد • احترام • انصرام'; ?>
                    </span>
                </div>
                <p class="lead mb-5 fs-3" style="line-height: 2.2; text-shadow: 1px 1px 3px #000;">
                    <?php echo ($lang == 'en') ? ($details['hero_subtitle_en'] ?? 'Serving Humanity with Spirituality & Dedication') : ($details['hero_subtitle'] ?? ''); ?>
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="org_history.php" class="btn btn-outline-light btn-lg px-5 rounded-pill border-2 fw-bold animate__animated animate__pulse animate__infinite">
                        <?php echo $trans['history'][$lang]; ?>
                    </a>
                    <a href="org_services.php" class="btn btn-warning btn-lg px-5 rounded-pill fw-bold text-dark shadow-lg">
                        <?php echo $trans['services'][$lang]; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Summary -->
<section class="py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4" data-aos="<?php echo ($lang=='ur')?'fade-left':'fade-right'; ?>">
                <img src="logo.jpeg" class="img-fluid rounded-4 shadow" alt="About" style="border: 5px solid #004d40;">
            </div>
            <div class="col-md-6 <?php echo ($lang=='ur')?'text-end':'text-start'; ?>" data-aos="<?php echo ($lang=='ur')?'fade-right':'fade-left'; ?>">
                <h2 class="text-success fw-bold mb-3"><?php echo ($lang == 'en') ? 'About Us' : 'مختصر تعارف'; ?></h2>
                <p class="lead text-muted" style="line-height: 2; direction: <?php echo $dir; ?>;">
                    <?php 
                        $default_about_en = "Tanzeem Aulad Hazrat Haji Bahadur is a spiritual and welfare organization based in Kohat. Established to serve humanity and propagate the teachings of Islam, we manage various charitable activities including Langar (free food), religious education, and maintenance of the holy shrine. Our mission is to continue the legacy of Hazrat Haji Bahadur (RA) by serving the community with dedication and compassion.";
                        
                        $txt = ($lang == 'en') ? ($details['about_text_en'] ?? $default_about_en) : ($details['about_text'] ?? '');
                        
                        // Fallback if DB returns empty string for EN
                        if($lang == 'en' && empty(trim($txt))) $txt = $default_about_en;

                        echo mb_substr($txt, 0, 400); 
                    ?>...
                </p>
                <a href="org_history.php" class="btn btn-primary rounded-pill mt-3">
                    <?php echo $trans['read_more'][$lang]; ?> 
                    <i class="fas <?php echo ($lang=='ur')?'fa-arrow-left':'fa-arrow-right'; ?> ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold"><?php echo ($lang == 'en') ? 'Our Services' : 'ہماری خدمات'; ?></h2>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="bg-white p-4 rounded shadow h-100">
                    <i class="fas fa-utensils fa-3x text-warning mb-3"></i>
                    <h4><?php echo ($lang == 'en') ? 'Langer Khana' : 'لنگر خانہ'; ?></h4>
                    <p class="text-muted"><?php echo ($lang == 'en') ? 'Arrangement of daily food distribution.' : 'روزانہ کی بنیاد پر لنگر کا اہتمام۔'; ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="bg-white p-4 rounded shadow h-100">
                    <i class="fas fa-moon fa-3x text-success mb-3"></i>
                    <h4><?php echo ($lang == 'en') ? 'Urs Mubarak' : 'عرس مبارک'; ?></h4>
                    <p class="text-muted"><?php echo ($lang == 'en') ? 'Annual spiritual Urs ceremonies.' : 'سالانہ عرس کی روحانی تقریبات۔'; ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="bg-white p-4 rounded shadow h-100">
                    <i class="fas fa-book fa-3x text-primary mb-3"></i>
                    <h4><?php echo ($lang == 'en') ? 'Religious Education' : 'دینی تعلیم'; ?></h4>
                    <p class="text-muted"><?php echo ($lang == 'en') ? 'Promotion of Quran and Sunnah education.' : 'قرآن و سنت کی تعلیم کا فروغ۔'; ?></p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
             <a href="org_services.php" class="btn btn-outline-dark rounded-pill px-4"><?php echo ($lang == 'en') ? 'View All Services' : 'تمام خدمات دیکھیں'; ?></a>
        </div>
    </div>
</section>

<?php include 'frontend_footer.php'; ?>
