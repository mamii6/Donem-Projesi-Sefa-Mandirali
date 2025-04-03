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
<link rel="stylesheet" href="css/styles.css">

<!-- Hero Section -->
<section class="page-header" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../img/page-header-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Hizmetlerimiz</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Hizmetlerimiz</li>
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
                <img src="img/service-purchase.jpg" alt="Emlak Alım Satım" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="service-details">
                    <h2>Emlak Alım Satım</h2>
                    <p class="lead">Gayrimenkul alım satım süreçlerinizi profesyonel bir şekilde yönetiyoruz.</p>
                    <p>Emlak alım ve satım süreçlerinde karşılaşabileceğiniz tüm zorlukları biz üstleniyoruz. Tecrübeli danışmanlarımız, pazar araştırması, doğru fiyatlandırma, pazarlama stratejisi ve yasal süreçlerde size destek olarak satışınızın en hızlı ve doğru fiyatla gerçekleşmesini sağlar.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Ücretsiz gayrimenkul değerleme</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Profesyonel fotoğraflama ve iç mekan düzenleme tavsiyeleri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Doğru alıcı ile buluşturma garantisi</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Hukuki süreçlerde danışmanlık</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Tapu ve belediye işlemlerinde destek</li>
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
                <img src="img/service-rental.jpg" alt="Kiralama Hizmetleri" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="service-details">
                    <h2>Kiralama Hizmetleri</h2>
                    <p class="lead">Kiralama süreçlerinizi kolaylaştırıyor, her bütçeye uygun çözümler sunuyoruz.</p>
                    <p>İster ev sahibi olun ister kiracı, kiralama süreçlerinin her adımında yanınızdayız. Özel kriterlerinize uygun gayrimenkulleri sizin için buluyor, kontrat hazırlama ve kiralama işlemlerini profesyonelce yönetiyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Bölgesel pazar analizleri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Kiracı kontrol ve referans sorgulaması</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Güvenli kira sözleşmesi hazırlama</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Gayrimenkul yönetim hizmetleri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Düzenli bakım ve kontrol hizmetleri</li>
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
                <img src="img/service-investment.jpg" alt="Yatırım Danışmanlığı" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="service-details">
                    <h2>Yatırım Danışmanlığı</h2>
                    <p class="lead">Kazançlı yatırım fırsatları için uzman danışmanlığımızdan faydalanın.</p>
                    <p>Gayrimenkul yatırımı yapmak isteyenlere özel hizmetimizle, piyasa analizleri, yatırım potansiyeli yüksek bölgelerin tespiti ve finansal planlamada destek sağlıyoruz. Kısa, orta ve uzun vadede en verimli yatırım stratejilerini birlikte oluşturuyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Detaylı piyasa araştırması ve analizi</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Gelişmekte olan bölge fırsatları</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Getiri oranı hesaplamaları</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Portföy çeşitlendirme stratejileri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Vergi avantajları danışmanlığı</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <img src="img/service-consultation.jpg" alt="Emlak Danışmanlığı" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="service-details">
                    <h2>Emlak Danışmanlığı</h2>
                    <p class="lead">Uzman danışmanlarımızla doğru kararları vermenize yardımcı oluyoruz.</p>
                    <p>Gayrimenkul alımı, satımı veya kiralaması yapmadan önce piyasa koşulları, bölgesel fırsatlar ve en uygun finansman seçenekleri hakkında bilgilendirme sağlıyoruz. Her müşterimizin ihtiyaçlarına özel çözümler üretiyoruz.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Ücretsiz ilk danışmanlık görüşmesi</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Bölgesel emlak piyasası değerlendirmesi</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Gayrimenkul kredisi ve finansman seçenekleri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> Bütçenize uygun gayrimenkul seçenekleri</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary-color);"></i> İnşaat ve kentsel dönüşüm projelerinde danışmanlık</li>
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
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-camera fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3 class="h5">Profesyonel Fotoğrafçılık</h3>
                    <p>Gayrimenkulünüzü en iyi şekilde tanıtmak için profesyonel fotoğraf çekimi hizmeti sunuyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-home fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3 class="h5">Ev Taşıma Hizmetleri</h3>
                    <p>Anlaşmalı olduğumuz nakliye firmaları ile ev taşıma sürecinizi kolaylaştırıyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-paint-roller fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3 class="h5">İç Mekan Tasarımı</h3>
                    <p>İç mekan tasarımcılarımız ile mülkünüzün değerini artıracak çözümler sunuyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-gavel fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3 class="h5">Hukuki Danışmanlık</h3>
                    <p>Gayrimenkul işlemlerinizde karşılaşabileceğiniz hukuki sorunlarda uzman desteği sağlıyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-file-contract fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3 class="h5">Sigorta Hizmetleri</h3>
                    <p>Gayrimenkulünüz için en uygun sigorta poliçelerini bulmanıza yardımcı oluyoruz.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 bg-white rounded shadow-sm">
                    <div class="feature-icon mb-3" style="background-color: var(--accent-color);">
                        <i class="fas fa-search-location fa-2x" style="color: var(--primary-dark);"></i>
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