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
<link rel="stylesheet" href="css/styles.css">

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
    
    <!-- Yeni filtreleme formu - Sayfanın ortasında ve üstte -->
    <div class="container position-relative">
        <div class="search-form-floating">
            <div class="search-form-tabs">
                <ul class="nav nav-tabs" id="searchTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="buy-tab" data-bs-toggle="tab" data-bs-target="#buy-tab-pane" type="button" role="tab" aria-controls="buy-tab-pane" aria-selected="true">Satılık</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rent-tab" data-bs-toggle="tab" data-bs-target="#rent-tab-pane" type="button" role="tab" aria-controls="rent-tab-pane" aria-selected="false">Kiralık</button>
                    </li>
                </ul>
                <div class="tab-content" id="searchTabsContent">
                    <div class="tab-pane fade show active" id="buy-tab-pane" role="tabpanel" aria-labelledby="buy-tab" tabindex="0">
                        <form method="GET" action="emlaklar.php" class="search-form p-4">
                            <div class="row align-items-end">
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="adres" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Konum</label>
                                        <input type="text" id="adres" name="adres" class="form-control" placeholder="Şehir, ilçe veya mahalle" value="<?php echo htmlspecialchars($adres); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="oda_sayisi" class="form-label"><i class="fas fa-bed me-2"></i>Oda Sayısı</label>
                                        <select id="oda_sayisi" name="oda_sayisi" class="form-select">
                                            <option value="">Tümü</option>
                                            <option value="1" <?php echo $oda_sayisi == '1' ? 'selected' : ''; ?>>1+0</option>
                                            <option value="2" <?php echo $oda_sayisi == '2' ? 'selected' : ''; ?>>1+1</option>
                                            <option value="3" <?php echo $oda_sayisi == '3' ? 'selected' : ''; ?>>2+1</option>
                                            <option value="4" <?php echo $oda_sayisi == '4' ? 'selected' : ''; ?>>3+1</option>
                                            <option value="5" <?php echo $oda_sayisi == '5' ? 'selected' : ''; ?>>4+1</option>
                                            <option value="6" <?php echo $oda_sayisi == '6' ? 'selected' : ''; ?>>5+ ve üzeri</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="price_range" class="form-label"><i class="fas fa-tag me-2"></i>Fiyat Aralığı</label>
                                        <div class="input-group">
                                            <input type="number" id="min_fiyat" name="min_fiyat" class="form-control" placeholder="Min TL" value="<?php echo htmlspecialchars($min_fiyat); ?>">
                                            <span class="input-group-text">-</span>
                                            <input type="number" id="max_fiyat" name="max_fiyat" class="form-control" placeholder="Max TL" value="<?php echo htmlspecialchars($max_fiyat); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Ara</button>
                                </div>
                            </div>
                            
                            <div class="advanced-search-toggle mt-3">
                                <a href="#advancedSearchOptions" data-bs-toggle="collapse" aria-expanded="false" aria-controls="advancedSearchOptions">
                                    <i class="fas fa-sliders-h me-2"></i>Gelişmiş Arama
                                </a>
                            </div>
                            
                            <div class="collapse mt-3" id="advancedSearchOptions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="metrekare_min" class="form-label"><i class="fas fa-ruler-combined me-2"></i>Metrekare Aralığı</label>
                                            <div class="input-group">
                                                <input type="number" id="metrekare_min" name="metrekare_min" class="form-control" placeholder="Min m²" value="<?php echo htmlspecialchars($metrekare_min); ?>">
                                                <span class="input-group-text">-</span>
                                                <input type="number" id="metrekare_max" name="metrekare_max" class="form-control" placeholder="Max m²" value="<?php echo htmlspecialchars($metrekare_max); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Diğer gelişmiş arama seçenekleri buraya eklenebilir -->
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="rent-tab-pane" role="tabpanel" aria-labelledby="rent-tab" tabindex="0">
                        <form method="GET" action="emlaklar.php" class="search-form p-4">
                            <input type="hidden" name="tur" value="kiralik">
                            <div class="row align-items-end">
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="adres_kira" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Konum</label>
                                        <input type="text" id="adres_kira" name="adres" class="form-control" placeholder="Şehir, ilçe veya mahalle" value="<?php echo htmlspecialchars($adres); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="oda_sayisi_kira" class="form-label"><i class="fas fa-bed me-2"></i>Oda Sayısı</label>
                                        <select id="oda_sayisi_kira" name="oda_sayisi" class="form-select">
                                            <option value="">Tümü</option>
                                            <option value="1" <?php echo $oda_sayisi == '1' ? 'selected' : ''; ?>>1+0</option>
                                            <option value="2" <?php echo $oda_sayisi == '2' ? 'selected' : ''; ?>>1+1</option>
                                            <option value="3" <?php echo $oda_sayisi == '3' ? 'selected' : ''; ?>>2+1</option>
                                            <option value="4" <?php echo $oda_sayisi == '4' ? 'selected' : ''; ?>>3+1</option>
                                            <option value="5" <?php echo $oda_sayisi == '5' ? 'selected' : ''; ?>>4+1</option>
                                            <option value="6" <?php echo $oda_sayisi == '6' ? 'selected' : ''; ?>>5+ ve üzeri</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group mb-md-0 mb-3">
                                        <label for="price_range_kira" class="form-label"><i class="fas fa-tag me-2"></i>Kira Aralığı</label>
                                        <div class="input-group">
                                            <input type="number" id="min_fiyat_kira" name="min_fiyat" class="form-control" placeholder="Min TL" value="<?php echo htmlspecialchars($min_fiyat); ?>">
                                            <span class="input-group-text">-</span>
                                            <input type="number" id="max_fiyat_kira" name="max_fiyat" class="form-control" placeholder="Max TL" value="<?php echo htmlspecialchars($max_fiyat); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Ara</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
                                    <form action="favori_ekle.php" method="POST" class="property-favorite-form">
                                        <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                                        <button type="submit" class="btn-favorite <?php echo $favori_durum ? 'active' : ''; ?>">
                                            <i class="<?php echo $favori_durum ? 'fas' : 'far'; ?> fa-heart"></i>
                                        </button>
                                    </form>
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