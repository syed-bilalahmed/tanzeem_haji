<?php include 'frontend_header.php'; ?>

<!-- Page Header -->
<header class="page-header">
    <div class="container" data-aos="fade-down">
        <h1 class="page-title"><?php echo ($lang == 'en') ? 'History & Introduction' : 'تاریخ و تعارف'; ?></h1>
        <p class="lead mt-3"><?php echo ($lang == 'en') ? 'Biography of Hazrat Syed Abdullah Shah (Haji Bahadur Sahib RA)' : 'حضرت سید عبداللہ شاہ (حاجی بہادر صاحبؒ) کی حیات مبارکہ'; ?></p>
    </div>
</header>

<div class="container py-5">
    
    <!-- Introduction Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 <?php echo ($lang=='ur')?'order-lg-2':''; ?>" data-aos="<?php echo ($lang=='ur')?'fade-left':'fade-right'; ?>">
            <img src="logo.jpeg" class="img-fluid rounded-4 shadow-lg border-5 border-white w-100" alt="History Image" style="max-height: 500px; object-fit: cover;">
        </div>
        <div class="col-lg-6 <?php echo ($lang=='ur')?'order-lg-1':''; ?>" data-aos="<?php echo ($lang=='ur')?'fade-right':'fade-left'; ?>">
            <div class="<?php echo ($lang=='ur')?'pe-lg-5 text-end':'ps-lg-5 text-start'; ?> bg-white p-4 rounded shadow-sm">
                <h3 class="text-success fw-bold mb-4 border-bottom d-inline-block pb-2"><?php echo ($lang == 'en') ? 'Introduction' : 'تعارف'; ?></h3>
                <p class="lead text-muted" style="line-height: 2.2; text-align: justify; direction: <?php echo $dir; ?>;">
                    <?php if($lang == 'en'): ?>
                        His real name is <strong>Syed Abdullah Shah</strong>, but he is widely known as <strong>Haji Bahadur</strong>. He was born on <strong>July 31, 1581 (900 Hijri)</strong> in Agra, India during the reign of Mughal Emperor Shah Jahan. He belongs to the noble <strong>Sadat</strong> family, and his lineage traces back to <strong>Hazrat Imam Hussain (RA)</strong>. His father's name was <strong>Syed Shah Muhammad Sultan</strong>. The family migrated from Ghazni to settle in Agra.
                    <?php else: ?>
                        آپ کا اسم گرامی <strong>سید عبداللہ شاہ</strong> ہے، لیکن عوام و خواص میں آپ <strong>حاجی بہادر</strong> کے لقب سے مشہور ہیں۔ آپ کی ولادت باسعادت <strong>31 جولائی 1581ء</strong> کو ہندوستان کے شہر آگرہ میں ہوئی۔ آپ کا تعلق سادات گھرانے سے ہے اور آپ کا سلسلہ نسب <strong>حضرت امام حسین رضی اللہ عنہ</strong> سے جا ملتا ہے۔ آپ کے والد محترم کا نام <strong>سید شاہ محمد سلطان</strong> تھا۔ آپ کا خاندان غزنی سے ہجرت کر کے آگرہ آباد ہوا تھا۔
                    <?php endif; ?>
                </p>
                <p class="lead text-muted" style="line-height: 2.2; text-align: justify; direction: <?php echo $dir; ?>;">
                    <?php if($lang == 'en'): ?>
                        From a young age, he was inclined towards spirituality. He pledged allegiance (Baet) to the renowned Sufi saint <strong>Hazrat Syed Adam Banuri (RA)</strong> (some traditions mention Shah Bulaq of Deccan) and spent years in spiritual training.
                    <?php else: ?>
                        جوانی میں ہی آپ کا رجحان تصوف اور روحانیت کی طرف مائل ہو گیا۔ آپ نے اس وقت کے نامور صوفی بزرگ <strong>حضرت سید آدم بنوریؒ</strong> (بعض روایات میں شاہ بلاق دکن) کے ہاتھ پر بیعت کی اور سالوں تک روحانی منازل طے کیں۔
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Shajra-e-Nasab (Lineage) Section -->
    <div class="row mb-5" dir="<?php echo $dir; ?>">
        <div class="col-12" data-aos="fade-up">
            <div class="bg-light p-5 rounded-4 border border-success border-opacity-25 shadow-sm">
                <h2 class="text-center text-primary fw-bold mb-5"><?php echo ($lang == 'en') ? 'Family Tree (Shajra-e-Nasab)' : 'شجرہ نسب (خاندانی پس منظر)'; ?></h2>
                
                <div class="row justify-content-center">
                    <!-- Father -->
                    <div class="col-md-8 text-center mb-4">
                        <div class="p-3 bg-white rounded shadow-sm d-inline-block border-bottom border-4 border-warning">
                            <h4 class="mb-0 fw-bold"><?php echo ($lang == 'en') ? 'Father: Syed Shah Muhammad Sultan' : 'والد: سید شاہ محمد سلطان'; ?></h4>
                        </div>
                        <div class="d-block text-secondary my-2"><i class="fas fa-arrow-down"></i></div>
                    </div>

                    <!-- Hazrat Haji Bahadur -->
                    <div class="col-md-8 text-center mb-4">
                        <div class="p-4 bg-success text-white rounded-pill shadow d-inline-block position-relative">
                            <h2 class="mb-0 fw-bold"><?php echo ($lang == 'en') ? 'Hazrat Syed Abdullah Shah (Haji Bahadur)' : 'حضرت سید عبداللہ شاہ (حاجی بہادر)'; ?></h2>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo ($lang == 'en') ? '1581 - 1691' : '1581ء - 1691ء'; ?>
                            </span>
                        </div>
                        <div class="d-block text-secondary my-2"><i class="fas fa-arrow-down"></i></div>
                    </div>

                    <!-- Wife Info -->
                    <div class="col-md-12 text-center mb-4">
                        <p class="text-muted fst-italic">
                            <?php echo ($lang == 'en') ? 'Wife: Hazrat Syeda Rukkia (RA) - From the family of Sheikh Abdul Qadir Jillani (RA)' : 'زوجہ محترمہ: حضرت سیدہ رقیہؒ (خاندان شیخ عبدالقادر جیلانیؒ)'; ?>
                        </p>
                    </div>

                    <!-- Sons Tree -->
                    <div class="col-12">
                        <h4 class="text-center mb-4 text-secondary"><?php echo ($lang == 'en') ? 'Sons (Sahibzadagan)' : 'صاحبزادگان'; ?></h4>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <?php 
                            $sons = [
                                ['en' => 'Syed Muhammad Yousuf', 'ur' => 'سید محمد یوسف'],
                                ['en' => 'Syed Muhammad Qasim', 'ur' => 'سید محمد قاسم'],
                                ['en' => 'Syed Muhammad Omar', 'ur' => 'سید محمد عمر'],
                                ['en' => 'Syed Muhammad Usman', 'ur' => 'سید محمد عثمان'],
                                ['en' => 'Syed Muhammad Yaqoob', 'ur' => 'سید محمد یعقوب']
                            ];
                            foreach($sons as $son): 
                            ?>
                            <div class="p-3 bg-white rounded border border-success shadow-sm text-center" style="min-width: 180px;">
                                <i class="fas fa-user-circle text-success mb-2 fs-4"></i>
                                <h6 class="mb-0 fw-bold"><?php echo ($lang == 'en') ? $son['en'] : $son['ur']; ?></h6>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline / Details Section -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-5" data-aos="zoom-in">
            <h2 class="fw-bold"><?php echo ($lang == 'en') ? 'Key Events' : 'اہم واقعات'; ?></h2>
        </div>
        
        <div class="col-md-4 mb-4" data-aos="flip-up" data-aos-delay="100">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="display-4 text-warning mb-3"><i class="fas fa-kaaba"></i></div>
                <h4><?php echo ($lang == 'en') ? 'Hajj Journey' : 'سفر حج'; ?></h4>
                <p class="text-muted">
                    <?php echo ($lang == 'en') ? 'He performed his first Hajj in 1645. Later in 1657, he performed his second Hajj with his Murshid Syed Adam Banuri, after which he was assigned the mission to settle in Kohat.' : 'آپ نے 1645ء میں پہلا حج ادا کیا۔ بعد ازاں 1657ء میں اپنے مرشد سید آدم بنوریؒ کے ہمراہ دوسرا حج کیا، جس کے بعد آپ کو کوہاٹ میں قیام کا حکم ملا۔'; ?>
                </p>
            </div>
        </div>

        <div class="col-md-4 mb-4" data-aos="flip-up" data-aos-delay="200">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="display-4 text-warning mb-3"><i class="fas fa-map-marked-alt"></i></div>
                <h4><?php echo ($lang == 'en') ? 'Arrival in Kohat' : 'کوہاٹ آمد'; ?></h4>
                <p class="text-muted">
                    <?php echo ($lang == 'en') ? 'On the order of his Murshid, he migrated to Kohat around 1657 AD. Here he lit the candle of Islam, guiding thousands of lost souls to the right path.' : 'مرشد کے حکم پر آپ 1657ء کے قریب کوہاٹ تشریف لائے اور یہاں مستقل قیام فرمایا۔ آپ نے دین اسلام کی شمع روشن کی اور ہزاروں لوگوں کی اصلاح فرمائی۔'; ?>
                </p>
            </div>
        </div>

        <div class="col-md-4 mb-4" data-aos="flip-up" data-aos-delay="300">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="display-4 text-warning mb-3"><i class="fas fa-crown"></i></div>
                <h4><?php echo ($lang == 'en') ? 'Aurangzeb\'s Pledge' : 'اورنگزیب کی بیعت'; ?></h4>
                <p class="text-muted">
                    <?php echo ($lang == 'en') ? 'Historical records state that Mughal Emperor Aurangzeb Alamgir was deeply impressed by his spirituality and pledged allegiance (Baet) to him.' : 'تاریخی روایات کے مطابق مغل بادشاہ اورنگزیب عالمگیر بھی آپ کی روحانی شخصیت سے متاثر ہو کر آپ کے دست حق پرست پر بیعت ہوا۔'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Legacy Section -->
    <div class="bg-light p-5 rounded-4 mt-5 shadow-inner" data-aos="fade-up">
        <div class="text-center">
            <h2 class="fw-bold mb-4"><?php echo ($lang == 'en') ? 'Demise and Shrine' : 'آپ کا وصال اور مزار مبارک'; ?></h2>
            <p class="lead fs-4" style="line-height: 2;">
                <?php if($lang == 'en'): ?>
                    He passed away in <strong>1691 AD</strong> (1070 Hijra) at the age of approximately 110 years. His shrine is located at a prominent place in the center of Kohat city, which remains a center of spirituality. Every year thousands of devotees from across the country attend his Urs.
                <?php else: ?>
                    آپ کا وصال <strong>1691ء</strong> (1070 ہجری) میں تقریباً 110 سال کی عمر میں ہوا۔ آپ کا مزار مبارک کوہاٹ شہر کے وسط میں ایک بلند مقام پر واقع ہے، جو آج بھی مرجع خلائق ہے۔ ہر سال ہزاروں عقیدت مند ملک بھر سے آپ کے عرس میں شرکت کرتے ہیں۔
                <?php endif; ?>
            </p>
        </div>
    </div>

</div>

<?php include 'frontend_footer.php'; ?>
