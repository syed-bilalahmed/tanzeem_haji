    <!-- Scroll To Top Button -->
    <button type="button" class="btn btn-floating" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Professional Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 text-center <?php echo ($lang=='en')?'text-md-start':'text-md-end'; ?>">
                    <h5><?php echo ($lang=='en') ? 'About Organization' : 'تنظیم کا تعارف'; ?></h5>
                    <p class="small" style="line-height: 1.8;">
                        <?php 
                            $ft_txt = ($lang == 'en') ? ($details['about_text_en'] ?? '') : ($details['about_text'] ?? '');
                            echo substr($ft_txt, 0, 300) . '...'; 
                        ?>
                    </p>
                </div>
                <div class="col-md-4 mb-4 text-center">
                    <h5><?php echo ($lang=='en') ? 'Quick Links' : 'اہم لنکس'; ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php"><?php echo $trans['home'][$lang]; ?></a></li>
                        <li class="mb-2"><a href="org_reports.php"><?php echo $trans['reports'][$lang]; ?></a></li>
                        <li class="mb-2"><a href="org_history.php"><?php echo $trans['history'][$lang]; ?></a></li>
                        <li class="mb-2"><a href="org_services.php"><?php echo $trans['services'][$lang]; ?></a></li>
                        <li class="mb-2"><a href="org_contact.php"><?php echo $trans['contact'][$lang]; ?></a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4 text-center <?php echo ($lang=='en')?'text-md-end':'text-md-start'; ?>">
                    <h5><?php echo ($lang=='en') ? 'Contact Details' : 'رابطہ کی تفصیلات'; ?></h5>
                    <p><i class="fas fa-map-marker-alt text-warning me-2"></i> <?php echo ($lang=='en') ? ($details['contact_address_en'] ?? '') : ($details['contact_address'] ?? ''); ?></p>
                    <p><i class="fas fa-phone text-warning me-2"></i> <?php echo $details['contact_phone'] ?? ''; ?></p>
                    <p><i class="fas fa-envelope text-warning me-2"></i> <?php echo $details['contact_email'] ?? ''; ?></p>
                </div>
            </div>
            <hr style="border-color: #444;">
            <div class="text-center pt-3">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <strong><?php echo $org_name; ?></strong>. <?php echo $trans['rights'][$lang]; ?></p>
                <small class="text-muted"><?php echo $trans['powered'][$lang]; ?></small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100,
            duration: 900,
            easing: 'ease-out-cubic',
        });
        
        // Highlight active link
        const currentPath = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-link').forEach(link => {
            if(link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });

        // Scroll to Top Logic
        let mybutton = document.getElementById("btn-back-to-top");

        window.onscroll = function () {
            scrollFunction();
        };

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                mybutton.style.display = "flex"; // Changed to flex to center icon
            } else {
                mybutton.style.display = "none";
            }
        }

        mybutton.addEventListener("click", backToTop);

        function backToTop() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
    </script>
</body>
</html>
