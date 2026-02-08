<?php include 'frontend_header.php'; ?>

<!-- Page Header -->
<header class="page-header">
    <div class="container" data-aos="fade-down">
        <h1 class="page-title"><?php echo ($lang == 'en') ? 'Contact Us' : 'رابطہ کریں'; ?></h1>
        <p class="lead mt-3"><?php echo ($lang == 'en') ? 'We are here to help you. Get in touch with us.' : 'ہماری ٹیم سے رابطہ کرنے کے لیے نیچے دی گئی تفصیلات استعمال کریں۔'; ?></p>
    </div>
</header>

<div class="container py-5">
    
    <div class="row">
        <!-- Contact Info -->
        <div class="col-lg-5 mb-4" data-aos="<?php echo ($lang=='ur')?'fade-left':'fade-right'; ?>">
            <div class="bg-white p-5 rounded-4 shadow-sm h-100">
                <h3 class="text-success fw-bold mb-4"><?php echo ($lang == 'en') ? 'Contact Information' : 'رابطہ کی تفصیلات'; ?></h3>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light text-success rounded-circle p-3 me-3">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"><?php echo ($lang == 'en') ? 'Address' : 'ایڈریس'; ?></h5>
                        <p class="text-muted mb-0">
                            <?php echo ($lang == 'en') ? ($details['contact_address_en'] ?? '') : ($details['contact_address'] ?? ''); ?>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light text-success rounded-circle p-3 me-3">
                        <i class="fas fa-phone fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"><?php echo ($lang == 'en') ? 'Phone' : 'فون نمبر'; ?></h5>
                        <p class="text-muted mb-0"><?php echo $details['contact_phone'] ?? ''; ?></p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="icon-box bg-light text-success rounded-circle p-3 me-3">
                        <i class="fas fa-envelope fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"><?php echo ($lang == 'en') ? 'Email' : 'ای میل'; ?></h5>
                        <p class="text-muted mb-0"><?php echo $details['contact_email'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="col-lg-7 mb-4" data-aos="<?php echo ($lang=='ur')?'fade-right':'fade-left'; ?>">
            <div class="rounded-4 overflow-hidden shadow-lg border-5 border-white h-100" style="min-height: 400px;">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d53200.0!2d71.4!3d33.5!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38d8ad33e5256e21%3A0x6b2817835811776d!2sKohat%2C%20Khyber%20Pakhtunkhwa%2C%20Pakistan!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>

</div>

<?php include 'frontend_footer.php'; ?>
