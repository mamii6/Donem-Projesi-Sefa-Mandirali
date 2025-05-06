<?php
session_start();
require_once "db.php";

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
        color: #ffffff;
    }
    
    .section-title h2 {
        color: #ffffff;
    }
    
    .section-title p {
        color: #b3b3b3;
    }
    
    .lead {
        color: #ffffff !important;
    }
    
    p {
        color: #b3b3b3;
    }
    
    .bg-white, .feature-box {
        background-color: #252525 !important;
    }
    
    .bg-light, .section-padding.bg-light {
        background-color: #1e1e1e !important;
    }
    
    .shadow, .shadow-sm {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3) !important;
    }
    
    .feature-icon {
        background-color: #d4af37 !important;
    }
    
    .feature-icon i {
        color: #121212 !important;
    }
    
    .h5 {
        color: #ffffff;
    }
    
    .service-features {
        list-style: none;
        padding-left: 0;
        margin-top: 20px;
    }
    
    .service-features li {
        margin-bottom: 10px;
        position: relative;
        padding-left: 30px;
        color: #b3b3b3;
    }
    
    .service-features li i {
        position: absolute;
        left: 0;
        top: 5px;
        color: #d4af37 !important;
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
    
    .list-group-item {
        background-color: #252525;
        color: #ffffff;
        border-color: #333333;
    }
    
    .text-muted {
        color: #b3b3b3 !important;
    }
    
    .modal-content {
        background-color: #252525;
        color: #ffffff;
    }
    
    .modal-header {
        border-bottom-color: #333333;
    }
    
    .service-details h2 {
        margin-bottom: 15px;
        color: #ffffff;
    }
    
    .feature-box p {
        color: #b3b3b3;
    }
    
    /* Breadcrumb düzenlemeleri */
    .breadcrumb {
        background: transparent;
    }
    
    .breadcrumb-item a {
        color: #d4af37 !important;
    }
    
    .breadcrumb-item.active {
        color: #ffffff !important;
    }
    
    .breadcrumb-item+.breadcrumb-item::before {
        color: #b3b3b3;
    }
    .hero-small {
        padding: 300px 0 50px;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('img/section4.jpg');
        background-size: cover;
        background-position: center;
        height: 700px;
    }
</style>

<!-- Hero Section -->
<section class="page-header hero-small" >
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Hizmetlerimiz</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Hizmetlerimiz</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Services Introduction -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div class="section-title">
                    <h2>Emlak Çözümlerimiz</h2>
                    <p>Emlak sektöründeki uzun yıllara dayanan tecrübemizle, siz değerli müşterilerimize en iyi hizmeti sunmak için buradayız. İhtiyacınıza en uygun çözümler için uzman ekibimiz yanınızda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Details -->
<section class="section-padding bg-light" id="alim-satim">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="img/logo.png" alt="Emlak Alım Satım" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="service-details">
                    <h2>Emlak Alım Satım</h2>
                    <p class="lead">Gayrimenkul alım satım süreçlerinizi profesyonel bir şekilde yönetiyoruz.</p>
                    <p>Emlak alım ve satım süreçlerinde karşılaşabileceğiniz tüm zorlukları biz üstleniyoruz. Tecrübeli danışmanlarımız, pazar araştırması, doğru fiyatlandırma, pazarlama stratejisi ve yasal süreçlerde size destek olarak satışınızın en hızlı ve doğru fiyatla gerçekleşmesini sağlar.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Ücretsiz gayrimenkul değerleme</li>
                        <li><i class="fas fa-check-circle"></i> Profesyonel fotoğraflama ve iç mekan düzenleme tavsiyeleri</li>
                        <li><i class="fas fa-check-circle"></i> Doğru alıcı ile buluşturma garantisi</li>
                        <li><i class="fas fa-check-circle"></i> Hukuki süreçlerde danışmanlık</li>
                        <li><i class="fas fa-check-circle"></i> Tapu ve belediye işlemlerinde destek</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="kiralama">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <img src="img/logo.png" alt="Kiralama Hizmetleri" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="service-details">
                    <h2>Kiralama Hizmetleri</h2>
                    <p class="lead">Kiralama süreçlerinizi kolaylaştırıyor, her bütçeye uygun çözümler sunuyoruz.</p>
                    <p>İster ev sahibi olun ister kiracı, kiralama süreçlerinin her adımında yanınızdayız. Özel kriterlerinize uygun gayrimenkulleri sizin için buluyor, kontrat hazırlama ve kiralama işlemlerini profesyonelce yönetiyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Bölgesel pazar analizleri</li>
                        <li><i class="fas fa-check-circle"></i> Kiracı kontrol ve referans sorgulaması</li>
                        <li><i class="fas fa-check-circle"></i> Güvenli kira sözleşmesi hazırlama</li>
                        <li><i class="fas fa-check-circle"></i> Gayrimenkul yönetim hizmetleri</li>
                        <li><i class="fas fa-check-circle"></i> Düzenli bakım ve kontrol hizmetleri</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light" id="yatirim">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="img/logo.png" alt="Yatırım Danışmanlığı" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="service-details">
                    <h2>Yatırım Danışmanlığı</h2>
                    <p class="lead">Kazançlı yatırım fırsatları için uzman danışmanlığımızdan faydalanın.</p>
                    <p>Gayrimenkul yatırımı yapmak isteyenlere özel hizmetimizle, piyasa analizleri, yatırım potansiyeli yüksek bölgelerin tespiti ve finansal planlamada destek sağlıyoruz. Kısa, orta ve uzun vadede en verimli yatırım stratejilerini birlikte oluşturuyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Detaylı piyasa araştırması ve analizi</li>
                        <li><i class="fas fa-check-circle"></i> Gelişmekte olan bölge fırsatları</li>
                        <li><i class="fas fa-check-circle"></i> Getiri oranı hesaplamaları</li>
                        <li><i class="fas fa-check-circle"></i> Portföy çeşitlendirme stratejileri</li>
                        <li><i class="fas fa-check-circle"></i> Vergi avantajları danışmanlığı</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="danismanlik">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <img src="img/logo.png" alt="Emlak Danışmanlığı" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="service-details">
                    <h2>Emlak Danışmanlığı</h2>
                    <p class="lead">Uzman danışmanlarımızla doğru kararları vermenize yardımcı oluyoruz.</p>
                    <p>Gayrimenkul alımı, satımı veya kiralaması yapmadan önce piyasa koşulları, bölgesel fırsatlar ve en uygun finansman seçenekleri hakkında bilgilendirme sağlıyoruz. Her müşterimizin ihtiyaçlarına özel çözümler üretiyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Ücretsiz ilk danışmanlık görüşmesi</li>
                        <li><i class="fas fa-check-circle"></i> Bölgesel emlak piyasası değerlendirmesi</li>
                        <li><i class="fas fa-check-circle"></i> Gayrimenkul kredisi ve finansman seçenekleri</li>
                        <li><i class="fas fa-check-circle"></i> Bütçenize uygun gayrimenkul seçenekleri</li>
                        <li><i class="fas fa-check-circle"></i> İnşaat ve kentsel dönüşüm projelerinde danışmanlık</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Services -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title text-center">
            <h2>Ek Hizmetlerimiz</h2>
            <p>Size daha iyi hizmet verebilmek için sunduğumuz diğer uzmanlık alanlarımız</p>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-camera fa-2x"></i>
                    </div>
                    <h3 class="h5">Profesyonel Fotoğrafçılık</h3>
                    <p>Gayrimenkulünüzü en iyi şekilde tanıtmak için profesyonel fotoğraf çekimi hizmeti sunuyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <h3 class="h5">Ev Taşıma Hizmetleri</h3>
                    <p>Anlaşmalı olduğumuz nakliye firmaları ile ev taşıma sürecinizi kolaylaştırıyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-paint-roller fa-2x"></i>
                    </div>
                    <h3 class="h5">İç Mekan Tasarımı</h3>
                    <p>İç mekan tasarımcılarımız ile mülkünüzün değerini artıracak çözümler sunuyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-gavel fa-2x"></i>
                    </div>
                    <h3 class="h5">Hukuki Danışmanlık</h3>
                    <p>Gayrimenkul işlemlerinizde karşılaşabileceğiniz hukuki sorunlarda uzman desteği sağlıyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-file-contract fa-2x"></i>
                    </div>
                    <h3 class="h5">Sigorta Hizmetleri</h3>
                    <p>Gayrimenkulünüz için en uygun sigorta poliçelerini bulmanıza yardımcı oluyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-search-location fa-2x"></i>
                    </div>
                    <h3 class="h5">Yerinde Tespit</h3>
                    <p>Seçtiğiniz gayrimenkulün yerinde incelenerek detaylı raporlama hizmeti sunuyoruz.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../img/cta-bg.jpg');">
    <div class="container">
        <div class="cta-content">
            <h2>Emlak İhtiyaçlarınız için Bize Ulaşın</h2>
            <p>Profesyonel ekibimizle size en iyi hizmeti sunmak için hazırız. Hemen iletişime geçin.</p>
            <a href="iletisim.php" class="btn btn-primary">İletişim</a>
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