<!-- Footer -->
<footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Profesyonel<span>Emlak</span></h3>
                        <p>Profesyonel Emlak olarak, müşterilerimize en kaliteli hizmeti sunmayı ve emlak sektöründe güvenilir bir rehber olmayı amaçlıyoruz.</p>
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Hızlı Bağlantılar</h3>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-angle-right"></i> Ana Sayfa</a></li>
                            <li><a href="emlaklar.php"><i class="fas fa-angle-right"></i> Emlaklar</a></li>
                            <li><a href="hizmetler.php"><i class="fas fa-angle-right"></i> Hizmetlerimiz</a></li>
                            <li><a href="hakkimizda.php"><i class="fas fa-angle-right"></i> Hakkımızda</a></li>
                            <li><a href="iletisim.php"><i class="fas fa-angle-right"></i> İletişim</a></li>
                            <li><a href="blog.php"><i class="fas fa-angle-right"></i> Blog</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>İletişim Bilgileri</h3>
                        <ul class="footer-contact">
                            <li><i class="fas fa-map-marker-alt"></i> Bağdat Caddesi No:123, Kadıköy, İstanbul</li>
                            <li><i class="fas fa-phone-alt"></i> +90 555 123 45 67</li>
                            <li><i class="fas fa-envelope"></i> info@profesyonelemlak.com</li>
                            <li><i class="fas fa-clock"></i> Pazartesi - Cumartesi: 09:00 - 18:00</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p>&copy; <?php echo date('Y'); ?> Profesyonel Emlak. Tüm hakları saklıdır.</p>
                    </div>
                    <div class="col-md-6">
                        <ul class="footer-bottom-links">
                            <li><a href="kullanici-sozlesmesi.php">Kullanıcı Sözleşmesi</a></li>
                            <li><a href="gizlilik-politikasi.php">Gizlilik Politikası</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Back to top button functionality
        const backToTopButton = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });
        
        // Sticky header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.main-header');
            if (window.scrollY > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
        
        // Mobil menü açma/kapama
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                if (mobileMenu.classList.contains('active')) {
                    mobileMenuToggle.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
    </script>
</body>
</html>