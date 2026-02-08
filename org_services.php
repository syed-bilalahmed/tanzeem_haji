<?php include 'frontend_header.php'; ?>

<!-- Page Header -->
<header class="page-header">
    <div class="container" data-aos="fade-down">
        <h1 class="page-title"><?php echo ($lang == 'en') ? 'Our Services' : 'ہماری خدمات'; ?></h1>
        <p class="lead mt-3"><?php echo ($lang == 'en') ? 'Welfare and Religious Services of Tanzeem Aulad Haji Bahadur' : 'تنظیم اولاد حاجی بہادر کی فلاحی اور مذہبی خدمات'; ?></p>
    </div>
</header>

<div class="container py-5">
    
    <div class="row">
        <?php
        $services_list = [
            [
                'icon' => 'fa-utensils',
                'title_ur' => 'لنگر خانہ',
                'title_en' => 'Lungar Khana',
                'desc_ur' => 'زائرین اور مساکین کے لیے روزانہ لنگر کا اہتمام کیا جاتا ہے۔ ہزاروں لوگ اس سے مستفید ہوتے ہیں۔',
                'desc_en' => 'Daily Lungar (Free Food) is arranged for pilgrims and the poor. Thousands of people benefit from this.'
            ],
            [
                'icon' => 'fa-moon',
                'title_ur' => 'سالانہ عرس مبارک',
                'title_en' => 'Annual Urs Mubarak',
                'desc_ur' => 'ہر سال عقیدت و احترام سے عرس منایا جاتا ہے جس میں ملک بھر سے زائرین شرکت کرتے ہیں۔',
                'desc_en' => 'The Urs is celebrated every year with devotion and respect, attended by pilgrims from all over the country.'
            ],
            [
                'icon' => 'fa-quran',
                'title_ur' => 'دینی تعلیم',
                'title_en' => 'Religious Education',
                'desc_ur' => 'تنظیم کے زیر اہتمام دینی مدارس چل رہے ہیں جہاں بچوں کو قرآن پاک کی تعلیم دی جاتی ہے۔',
                'desc_en' => 'Religious schools are running under the organization where children are taught the Holy Quran.'
            ],
            [
                'icon' => 'fa-tombstone',
                'title_ur' => 'قبرستان کی دیکھ بھال',
                'title_en' => 'Graveyard Maintenance',
                'desc_ur' => 'قدیم قبرستان اور مزار مبارک کی صفائی، مرمت اور دیکھ بھال کا کام باقاعدگی سے کیا جاتا ہے۔',
                'desc_en' => 'Cleaning, repairing, and maintenance of the old graveyard and the holy shrine are carried out regularly.'
            ],
            [
                'icon' => 'fa-hands-helping',
                'title_ur' => 'خاندان بہادر کی فلاح',
                'title_en' => 'Family Welfare',
                'desc_ur' => 'اولاد حضرت حاجی بہادرؒ کے غریب اور مستحق خاندانوں کی مالی معاونت کی جاتی ہے۔',
                'desc_en' => 'Financial assistance is provided to the poor and deserving families of the descendants of Hazrat Haji Bahadur (RA).'
            ],
            [
                'icon' => 'fa-mosque',
                'title_ur' => 'مسجد کی تعمیر و ترقی',
                'title_en' => 'Mosque Development',
                'desc_ur' => 'دربار سے ملحقہ مسجد کی تعمیر و توسیع اور دیگر ضروریات کا خیال رکھا جاتا ہے۔',
                'desc_en' => 'Construction, expansion, and other needs of the mosque adjacent to the shrine are taken care of.'
            ]
        ];

        foreach($services_list as $index => $svc): 
        ?>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
            <div class="card h-100 text-center p-4 service-card">
                <div class="icon-box mb-4 mx-auto bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 30px;">
                    <i class="fas <?php echo $svc['icon']; ?>"></i>
                </div>
                <h4 class="mb-3 fw-bold"><?php echo ($lang == 'en') ? $svc['title_en'] : $svc['title_ur']; ?></h4>
                <p class="text-muted">
                    <?php echo ($lang == 'en') ? $svc['desc_en'] : $svc['desc_ur']; ?>
                </p>
                <a href="#" class="btn btn-outline-success rounded-pill mt-auto"><?php echo $trans['read_more'][$lang]; ?></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
</style>

<?php include 'frontend_footer.php'; ?>
