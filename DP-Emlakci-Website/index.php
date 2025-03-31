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

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
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

<!-- Search Form Section -->
<div class="container">
    <div class="search-form">
        <form method="GET" action="emlaklar.php">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="min_fiyat">Minimum Fiyat</label>
                        <input type="number" id="min_fiyat" name="min_fiyat" class="form-control" placeholder="Min Fiyat" value="<?php echo htmlspecialchars($min_fiyat); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="max_fiyat">Maksimum Fiyat</label>
                        <input type="number" id="max_fiyat" name="max_fiyat" class="form-control" placeholder="Max Fiyat" value="<?php echo htmlspecialchars($max_fiyat); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="oda_sayisi">Oda Sayısı</label>
                        <input type="number" id="oda_sayisi" name="oda_sayisi" class="form-control" placeholder="Oda Sayısı" value="<?php echo htmlspecialchars($oda_sayisi); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="metrekare_min">Minimum m²</label>
                        <input type="number" id="metrekare_min" name="metrekare_min" class="form-control" placeholder="Min m²" value="<?php echo htmlspecialchars($metrekare_min); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="metrekare_max">Maksimum m²</label>
                        <input type="number" id="metrekare_max" name="metrekare_max" class="form-control" placeholder="Max m²" value="<?php echo htmlspecialchars($metrekare_max); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="adres">Adres</label>
                        <input type="text" id="adres" name="adres" class="form-control" placeholder="Adres" value="<?php echo htmlspecialchars($adres); ?>">
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary w-100">Ara</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Featured Properties Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Öne Çıkan Emlaklar</h2>
            <p>En çok ilgi gören ve öne çıkan emlak ilanlarımızı keşfedin.</p>
        </div>

        <div class="row">
            <?php foreach ($ilanlar as $ilan): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card">
                        <div class="property-img">
                            <?php if (!empty($ilan["resim"])): ?>
                                <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>">
                            <?php else: ?>
                                <img src="img/property-placeholder.jpg" alt="Emlak Görseli">
                            <?php endif; ?>
                            <div class="property-tag">Satılık</div>
                            <div class="property-price"><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> TL</div>
                        </div>
                        <div class="property-details">
                            <h3 class="property-title"><?php echo htmlspecialchars($ilan["baslik"]); ?></h3>
                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ilan["adres"]); ?>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i> <?php echo $ilan["oda_sayisi"]; ?> Oda
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i> <?php echo $ilan["metrekare"]; ?> m²
                                </div>
                                <div class="property-feature">
                                    <?php
                                    // Kullanıcının favoriye ekleyip eklemediğini kontrol et
                                    $favori_durum = false;
                                    if (isset($_SESSION["kullanici_id"])) {
                                        $favori_sorgu = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
                                        $favori_sorgu->execute([$_SESSION["kullanici_id"], $ilan["id"]]);
                                        $favori_durum = $favori_sorgu->fetchColumn() > 0;
                                    }
                                    ?>
                                    <form action="favori_ekle.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                                        <button type="submit" class="btn <?php echo $favori_durum ? 'btn-danger' : 'btn-outline-danger'; ?> btn-sm">
                                            <?php echo $favori_durum ? '❤️' : '🤍'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary btn-sm">Detayları Gör</a>
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
                        <img src="img/testimonial-1.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Bu platformda hayalimdeki evi bulmak çok kolay oldu. Profesyonel ekibi ve kullanıcı dostu arayüzü ile kesinlikle herkese tavsiye ederim."</p>
                    <h4 class="testimonial-name">Ahmet Yılmaz</h4>
                    <p class="testimonial-position">İstanbul</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/testimonial-2.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"İlan vermek çok kolay ve hızlı. Sadece birkaç günde evimi satmayı başardım, teşekkürler Profesyonel Emlak!"</p>
                    <h4 class="testimonial-name">Ayşe Demir</h4>
                    <p class="testimonial-position">Ankara</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/testimonial-3.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Emlak yatırımı konusunda aldığım danışmanlık hizmeti sayesinde doğru kararlar verdim ve kazançlı çıktım."</p>
                    <h4 class="testimonial-name">Mehmet Kaya</h4>
                    <p class="testimonial-position">İzmir</p>
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