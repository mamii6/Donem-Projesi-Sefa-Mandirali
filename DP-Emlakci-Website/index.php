<?php
session_start();
require_once "db.php";

// Filtreleme için değişkenleri al
$min_fiyat = $_GET['min_fiyat'] ?? '';
$max_fiyat = $_GET['max_fiyat'] ?? '';
$oda_sayisi = $_GET['oda_sayisi'] ?? '';
$metrekare_min = $_GET['metrekare_min'] ?? '';
$metrekare_max = $_GET['metrekare_max'] ?? '';
$adres = $_GET['adres'] ?? '';

$query = "SELECT * FROM ilanlar WHERE durum = 'onaylı'"; 
$params = [];

if (!empty($min_fiyat)) {
    $query .= " AND fiyat >= ?";
    $params[] = $min_fiyat;
}
if (!empty($max_fiyat)) {
    $query .= " AND fiyat <= ?";
    $params[] = $max_fiyat;
}
if (!empty($oda_sayisi)) {
    $query .= " AND oda_sayisi = ?";
    $params[] = $oda_sayisi;
}
if (!empty($metrekare_min)) {
    $query .= " AND metrekare >= ?";
    $params[] = $metrekare_min;
}
if (!empty($metrekare_max)) {
    $query .= " AND metrekare <= ?";
    $params[] = $metrekare_max;
}
if (!empty($adres)) {
    $query .= " AND adres LIKE ?";
    $params[] = "%$adres%";
}

// Son eklenen ilanları göstermek için sıralama ekledim
$query .= " ORDER BY eklenme_tarihi DESC LIMIT 9"; 

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcı giriş yaptıysa bildirimleri al
$bildirimler = [];
$okunmamis_bildirim_sayisi = 0;
if (isset($_SESSION["kullanici_id"])) {
    $kullanici_id = $_SESSION["kullanici_id"];
    $bildirim_stmt = $pdo->prepare("SELECT * FROM bildirimler WHERE kullanici_id = ? ORDER BY tarih DESC");
    $bildirim_stmt->execute([$kullanici_id]);
    $bildirimler = $bildirim_stmt->fetchAll(PDO::FETCH_ASSOC);
    $okunmamis_bildirim_sayisi = count(array_filter($bildirimler, fn($b) => $b['goruldu'] == 0));
}

// Header'ı dahil et
include 'includes/header.php';

?>
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- CSS Stilleri -->
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/styles.css">

<!-- Ek Stil Tanımlamaları -->
<style>
    body {
        font-family: 'Quicksand', sans-serif;
        background-color: #121212;
        color: #ffffff;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Ubuntu', sans-serif;
        font-weight: 700;
    }
    
    /* Navbar yazıları için bold stil */
    .menu-list li a {
        font-weight: 700;
        font-family: 'Ubuntu', sans-serif;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    /* Modern giriş ikonları için stiller */
    .auth-icons {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .auth-icon {
        position: relative;
        font-size: 20px;
        color: white;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(212, 175, 55, 0.2);
    }
    
    .auth-icon:hover {
        background-color: #d4af37;
        color: #121212;
        transform: translateY(-3px);
    }
    
    .auth-icon .tooltip {
        position: absolute;
        bottom: -35px;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        white-space: nowrap;
        z-index: 10;
    }
    
    .auth-icon:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    
    .logo h1 {
        font-family: 'Ubuntu', sans-serif;
        font-weight: 700;
    }
    
    .logo span {
        color: #d4af37;
    }
    
    /* Mobil menü için */
    .mobile-menu-list li a {
        font-weight: 700;
        font-family: 'Ubuntu', sans-serif;
    }
    
    .form-control, .form-select {
        background-color: #333;
        border-color: #333333;
        color: #ffffff;
    }
    
    .form-control:focus, .form-select:focus {
        background-color: #3a3a3a;
        border-color: #e6c458;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }
    
    .btn-primary {
        background-color: #d4af37;
        border-color: #d4af37;
        color: #121212;
    }
    
    .btn-primary:hover {
        background-color: #e6c458;
        border-color: #e6c458;
    }
    
    .btn-secondary {
        background-color: #3a3a3a;
        border-color: #3a3a3a;
    }
    
    .btn-secondary:hover {
        background-color: #4a4a4a;
        border-color: #4a4a4a;
    }
    
    .section-title h2 {
        color: #ffffff;
    }
    
    .property-card {
        background-color: #252525;
    }
    
    .property-tag {
        background-color: #d4af37;
        color: #121212;
    }
    
    .property-price {
        background-color: #b3941e;
    }
    
    .service-box, .testimonial-box {
        background-color: #252525;
    }
    
    .service-icon {
        background-color: #d4af37;
    }
    
    .service-icon i {
        color: #121212;
    }
    
    .bg-light {
        background-color: #1e1e1e !important;
    }
    
    .testimonial-img {
        border-color: #d4af37;
    }
    
    .modal-content {
        background-color: #252525;
        color: #ffffff;
    }
    
    .list-group-item {
        background-color: #252525;
        color: #ffffff;
        border-color: #333333;
    }
    
    .input-group-text {
        background-color: #333;
        color: #b3b3b3;
        border-color: #333333;
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container" >
        <div class="hero-content">
            <h1>Hayalinizdeki Evi Keşfedin</h1>
            <p>En güvenilir emlak platformunda binlerce ilan arasından size uygun olanı seçin. Ev aramanın yeni ve kolay yolu.</p>
            <div>
                <a href="emlaklar.php" class="btn btn-primary">İlanları Keşfet</a>
                <a href="ilan_ekle.php" class="btn btn-secondary">İlan Ver</a>
            </div>
        </div>
    </div>
    
  
</section>

<!-- Featured Properties Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Öne Çıkan Emlaklar</h2>
            <p>En çok ilgi gören ve öne çıkan emlak ilanlarımızı keşfedin.</p>
        </div>

        <div class="row properties-grid">
            <?php foreach ($ilanlar as $ilan): ?>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="property-card">
                        <div class="property-img" style="height: 200px;">
                            <?php if (!empty($ilan["resim"])): ?>
                                <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>" style="height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <img src="img/property-placeholder.jpg" alt="Emlak Görseli" style="height: 100%; object-fit: cover;">
                            <?php endif; ?>
                            <div class="property-tag">Satılık</div>
                            <div class="property-price"><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> ₺</div>
                            
                            <!-- Favorileme işlemi için yeni tasarım -->
                            <?php if (isset($_SESSION["kullanici_id"])): ?>
                                <?php
                                // Kullanıcının favoriye ekleyip eklemediğini kontrol et
                                $favori_durum = false;
                                try {
                                    $favori_sorgu = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
                                    $favori_sorgu->execute([$_SESSION["kullanici_id"], $ilan["id"]]);
                                    $favori_durum = $favori_sorgu->fetchColumn() > 0;
                                } catch (PDOException $e) {
                                    // Favoriler tablosu olmayabilir, hata durumunda sessizce devam et
                                }
                                ?>
                                <div class="property-favorite">
                                    <button type="button" class="btn-favorite <?php echo $favori_durum ? 'active' : ''; ?>" 
                                            onclick="toggleFavorite(this, <?php echo $ilan['id']; ?>)">
                                        <i class="<?php echo $favori_durum ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                        </div>
                        <div class="property-details">
                            <h3 class="property-title text-truncate"><?php echo htmlspecialchars($ilan["baslik"]); ?></h3>
                            <div class="property-location mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ilan["adres"]); ?>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i> <?php echo $ilan["oda_sayisi"]; ?> Oda
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i> <?php echo $ilan["metrekare"]; ?> m²
                                </div>
                                <?php if (isset($ilan["goruntulenme"])): ?>
                                <div class="property-feature">
                                    <i class="fas fa-eye"></i> <?php echo $ilan["goruntulenme"]; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary btn-sm w-100">Detayları Gör</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($ilanlar) > 6): ?>
            <div class="text-center mt-4">
                <a href="emlaklar.php" class="btn btn-outline-primary">Tüm Emlakları Gör</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Services Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Hizmetlerimiz</h2>
            <p>Size en kaliteli hizmeti sunmak için buradayız.</p>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="service-box">
                    <div class="service-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="service-title">Emlak Alım Satım</h3>
                    <p>Profesyonel ekibimizle emlak alım ve satım süreçlerinizi en güvenli şekilde yönetiyoruz.</p>
                    <a href="hizmetler.php#alim-satim" class="btn btn-outline-primary mt-3">Detaylı Bilgi</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="service-box">
                    <div class="service-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="service-title">Kiralama Hizmetleri</h3>
                    <p>Her bütçeye uygun kiralık konut seçenekleri ile hayalinizdeki eve kavuşmanızı sağlıyoruz.</p>
                    <a href="hizmetler.php#kiralama" class="btn btn-outline-primary mt-3">Detaylı Bilgi</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="service-box">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="service-title">Yatırım Danışmanlığı</h3>
                    <p>Uzman ekibimizle emlak yatırımlarınız için size en doğru yönlendirmeleri yapıyoruz.</p>
                    <a href="hizmetler.php#yatirim" class="btn btn-outline-primary mt-3">Detaylı Bilgi</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Müşteri Yorumları</h2>
            <p>Bizimle çalışan müşterilerimizin deneyimlerini okuyun.</p>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Bu platformda hayalimdeki evi bulmak çok kolay oldu. Profesyonel ekibi ve kullanıcı dostu arayüzü ile kesinlikle herkese tavsiye ederim."</p>
                    <h4 class="testimonial-name">Ahmet Yüksek</h4>
                    <p class="testimonial-position">Antalya</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"İlan vermek çok kolay ve hızlı. Sadece birkaç günde evimi satmayı başardım, teşekkürler Profesyonel Emlak!"</p>
                    <h4 class="testimonial-name">Metin Çoşkun</h4>
                    <p class="testimonial-position">Ankara</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Emlak yatırımı konusunda aldığım danışmanlık hizmeti sayesinde doğru kararlar verdim ve kazançlı çıktım."</p>
                    <h4 class="testimonial-name">Muhammet MS Mandıralı</h4>
                    <p class="testimonial-position">Samsun</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Emlak İlanı Vermek İster Misiniz?</h2>
            <p>Binlerce potansiyel alıcıya ulaşmak için hemen ilan verin. Basit, hızlı ve etkili.</p>
            <a href="ilan_ekle.php" class="btn btn-primary">Hemen İlan Ver</a>
        </div>
    </div>
</section>

<!-- User Account Modal for Notifications -->
<?php if (isset($_SESSION["kullanici_id"])): ?>
<div class="modal fade" id="userAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bildirimler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($bildirimler)): ?>
                    <p class="text-muted">Bildirim yok</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($bildirimler as $bildirim): ?>
                            <li class="list-group-item <?php echo $bildirim['goruldu'] ? '' : 'fw-bold'; ?>">
                                <a href="bildirim_detay.php?id=<?php echo $bildirim['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($bildirim["mesaj"]); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date("d.m.Y H:i", strtotime($bildirim["tarih"])); ?>
                                    </small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<script>
function toggleFavorite(button, ilanId) {
    // AJAX isteği için formData oluştur
    const formData = new FormData();
    formData.append('ilan_id', ilanId);
    
    // AJAX isteği gönder
    fetch('favori_ekle_ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' // Çerezleri (session) gönder
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Başarılı işlem
            if (data.action === 'add') {
                // Favorilere eklendi, butonun görünümünü güncelle
                button.classList.add('active');
                button.querySelector('i').classList.remove('far');
                button.querySelector('i').classList.add('fas');
            } else if (data.action === 'remove') {
                // Favorilerden çıkarıldı, butonun görünümünü güncelle
                button.classList.remove('active');
                button.querySelector('i').classList.remove('fas');
                button.querySelector('i').classList.add('far');
            }
            
            // İsteğe bağlı: Başarılı bildirim göster
            // console.log(data.message);
        } else {
            // Hata durumu
            if (data.message === 'login_required') {
                // Giriş yapılmadıysa giriş sayfasına yönlendir
                window.location.href = 'login.php';
            } else {
                // Diğer hata mesajlarını göster
                alert('Hata: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Bağlantı hatası:', error);
        alert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.');
    });
}

// Sayfa yüklendiğinde çalışacak script
document.addEventListener('DOMContentLoaded', function() {
    // Sticky header
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.main-header');
        if (window.scrollY > 100) {
            header.classList.add('sticky');
        } else {
            header.classList.remove('sticky');
        }
    });
    
    // Back to top button
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('active');
            } else {
                backToTopBtn.classList.remove('active');
            }
        });
        
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Form element işlemleri
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(element => {
        element.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');           
        });
        
        element.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Lazyload images
    const lazyImages = document.querySelectorAll('.lazy-load');
    if (lazyImages.length > 0) {
        const lazyLoad = function() {
            lazyImages.forEach(img => {
                if (img.getBoundingClientRect().top <= window.innerHeight && img.getBoundingClientRect().bottom >= 0) {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                }
            });
        };
        
        window.addEventListener('scroll', lazyLoad);
        window.addEventListener('resize', lazyLoad);
        window.addEventListener('orientationchange', lazyLoad);
        lazyLoad(); // İlk yüklemede de kontrol et
    }
});
</script>
</body>
</html>