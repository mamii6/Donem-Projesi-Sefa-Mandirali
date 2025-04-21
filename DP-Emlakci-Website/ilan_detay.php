<?php
require_once "db.php";
session_start();

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$ilan_id = $_GET["id"];

// İlan sahibinin profil resmini de çekiyoruz
$stmt = $pdo->prepare("SELECT i.*, k.ad, k.soyad, k.telefon, k.email, k.profil_resmi 
                       FROM ilanlar i 
                       LEFT JOIN kullanicilar k ON i.kullanici_id = k.id 
                       WHERE i.id = ?");
$stmt->execute([$ilan_id]);

$ilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) {
    header("Location: index.php");
    exit;
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['kullanici_id'])) {
    $mesaj = trim($_POST['mesaj']);
    $gonderen_id = $_SESSION['kullanici_id'];
    $alici_id = $ilan['kullanici_id'];

    if (!empty($mesaj)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, ilan_id, mesaj) VALUES (?, ?, ?, ?)");
            $stmt->execute([$gonderen_id, $alici_id, $ilan_id, $mesaj]);
            
            // Bildirim ekleme
            $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tur, ilgili_id, goruldu, tarih) 
                                            VALUES (?, ?, 'mesaj', ?, 0, NOW())");
            $bildirim_stmt->execute([$alici_id, "Yeni bir mesajınız var!", $ilan_id]);
            
            $success_message = "Mesajınız başarıyla gönderildi. İlan sahibi sizinle en kısa sürede iletişime geçecektir.";
        } catch (PDOException $e) {
            $error_message = "Mesaj gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        }
    } else {
        $error_message = "Mesaj alanı boş olamaz.";
    }
}

// Benzer ilanlar
$benzer_ilanlar_stmt = $pdo->prepare("SELECT * FROM ilanlar 
                                      WHERE id != ? 
                                      AND oda_sayisi = ? 
                                      AND durum = 'onaylı'
                                      ORDER BY RAND() 
                                      LIMIT 3");
$benzer_ilanlar_stmt->execute([$ilan_id, $ilan['oda_sayisi']]);
$benzer_ilanlar = $benzer_ilanlar_stmt->fetchAll(PDO::FETCH_ASSOC);

// Görüntülenme sayısını artır - önce sütun var mı kontrol et
try {
    $checkColumn = $pdo->query("SHOW COLUMNS FROM ilanlar LIKE 'goruntulenme'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if ($columnExists) {
        // Oturum kontrolü - aynı kullanıcı tekrar tekrar görüntüleme sayısını artırmasın
        if (!isset($_SESSION['viewed_ads']) || !in_array($ilan_id, $_SESSION['viewed_ads'])) {
            $pdo->prepare("UPDATE ilanlar SET goruntulenme = goruntulenme + 1 WHERE id = ?")->execute([$ilan_id]);
            if (!isset($_SESSION['viewed_ads'])) {
                $_SESSION['viewed_ads'] = [];
            }
            $_SESSION['viewed_ads'][] = $ilan_id;
        }
    }
} catch (PDOException $e) {
    // Hata durumunda sessizce devam et
}

// Header'ı dahil et
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($ilan["baslik"]); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="emlaklar.php">Emlaklar</a> / <?php echo htmlspecialchars($ilan["baslik"]); ?>
        </div>
    </div>
</div>

<!-- Property Detail Section -->
<section class="section-padding">
    <div class="container">
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Property Images - Daha küçük boyutlandırma -->
                <div class="property-single-img" style="max-height: 400px; overflow: hidden;">
                    <?php if (!empty($ilan["resim"])): ?>
                        <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>" style="width: 100%; height: auto; max-height: 400px; object-fit: cover;">
                    <?php else: ?>
                        <img src="img/property-placeholder.jpg" alt="Emlak Görseli" style="width: 100%; height: auto; max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                </div>
                
                <!-- Property Price & Location -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                    <div class="property-price-tag">
                        <h2><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> ₺</h2>
                    </div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ilan["adres"]); ?>
                    </div>
                </div>
                
                <!-- Property Info -->
                <div class="property-info mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="property-info-item">
                                <strong>Oda Sayısı:</strong> <span><?php echo $ilan["oda_sayisi"]; ?> Oda</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info-item">
                                <strong>Metrekare:</strong> <span><?php echo $ilan["metrekare"]; ?> m²</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info-item">
                                <strong>Eklenme Tarihi:</strong> <span><?php echo date("d.m.Y", strtotime($ilan["eklenme_tarihi"])); ?></span>
                            </div>
                        </div>
                        <?php
                        // Görüntülenme sütunu var mı kontrol et
                        $checkColumn = $pdo->query("SHOW COLUMNS FROM ilanlar LIKE 'goruntulenme'");
                        $columnExists = $checkColumn->rowCount() > 0;
                        if ($columnExists):
                        ?>
                        <div class="col-md-6">
                            <div class="property-info-item">
                                <strong>Görüntülenme:</strong> <span><?php echo $ilan["goruntulenme"] ?? 0; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Property Description -->
                <div class="content-section">
                    <h3 class="content-title">İlan Açıklaması</h3>
                    <div class="content-text">
                        <?php echo nl2br(htmlspecialchars($ilan["aciklama"])); ?>
                    </div>
                </div>
                
                <!-- Property Actions -->
                <div class="property-action mb-5">
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <?php
                        // Favoriye ekleyip eklemediğini kontrol et
                        $favori_durum = false;
                        try {
                            $favori_sorgu = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
                            $favori_sorgu->execute([$_SESSION["kullanici_id"], $ilan["id"]]);
                            $favori_durum = $favori_sorgu->fetchColumn() > 0;
                        } catch (PDOException $e) {
                            // Favoriler tablosu olmayabilir
                        }
                        ?>
                        <form action="favori_ekle.php" method="POST" style="display: inline;">
                            <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                            <button type="submit" class="btn <?php echo $favori_durum ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                <?php echo $favori_durum ? 'Favorilerden Çıkar' : 'Favorilere Ekle'; ?>
                                <?php echo $favori_durum ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="javascript:void(0)" class="btn btn-outline-primary ms-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Yazdır
                    </a>
                    
                    <a href="javascript:void(0)" class="btn btn-outline-primary ms-2" onclick="shareProperty()">
                        <i class="fas fa-share-alt"></i> Paylaş
                    </a>
                    
                    <!-- Doğrudan Mesaj Gönderme Butonu - Mobil Görünüm İçin -->
                    <?php if (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] != $ilan['kullanici_id']): ?>
                        <a href="#messageForm" class="btn btn-success ms-2 d-block d-lg-none mt-2">
                            <i class="fas fa-envelope"></i> Mesaj Gönder
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Agent Info -->
                <div class="agent-box">
                    <div class="agent-img">
                        <?php if (!empty($ilan["profil_resmi"])): ?>
                            <img src="uploads/profiller/<?php echo htmlspecialchars($ilan["profil_resmi"]); ?>" alt="İlan Sahibi">
                        <?php else: ?>
                            <img src="img/agent-placeholder.jpg" alt="İlan Sahibi">
                        <?php endif; ?>
                    </div>
                    <div class="agent-info">
                        <h4><?php echo htmlspecialchars($ilan["ad"] . " " . $ilan["soyad"]); ?></h4>
                        <p>İlan Sahibi</p>
                    </div>
                    <ul class="agent-contact">
                        <?php if (!empty($ilan["telefon"])): ?>
                            <li>
                                <i class="fas fa-phone"></i> 
                                <a href="tel:<?php echo htmlspecialchars($ilan["telefon"]); ?>">
                                    <?php echo htmlspecialchars($ilan["telefon"]); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($ilan["email"])): ?>
                            <li>
                                <i class="fas fa-envelope"></i> 
                                <a href="mailto:<?php echo htmlspecialchars($ilan["email"]); ?>">
                                    <?php echo htmlspecialchars($ilan["email"]); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Mesaj Gönderme Formu - Daha Belirgin -->
                    <?php if (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] != $ilan['kullanici_id']): ?>
                        <div class="mt-4" id="messageForm">
                            <div class="card">
                                <div class="card-header  text-white">
                                    <h5 class="m-0">İlan Sahibine Mesaj Gönder</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="mb-3">
                                            <textarea name="mesaj" class="form-control" rows="4" placeholder="İlan ile ilgili sorularınızı buraya yazabilirsiniz..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-paper-plane me-2"></i> Mesaj Gönder
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php elseif (!isset($_SESSION['kullanici_id'])): ?>
                        <div class="mt-4 alert alert-info">
                            <p>İlan sahibine mesaj göndermek için <a href="login.php" class="alert-link">giriş yapın</a> veya <a href="register.php" class="alert-link">üye olun</a>.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Similar Properties -->
                <?php if (count($benzer_ilanlar) > 0): ?>
                <div class="similar-properties mt-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4 class="m-0">Benzer İlanlar</h4>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($benzer_ilanlar as $benzer_ilan): ?>
                                <div class="property-card mb-0 border-bottom">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <div class="property-img h-100">
                                                <?php if (!empty($benzer_ilan["resim"])): ?>
                                                    <img src="uploads/ilanlar/<?php echo htmlspecialchars($benzer_ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($benzer_ilan["baslik"]); ?>" class="img-fluid h-100 w-100 object-fit-cover">
                                                <?php else: ?>
                                                    <img src="img/property-placeholder.jpg" alt="Emlak Görseli" class="img-fluid h-100 w-100 object-fit-cover">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="p-3">
                                                <h5 class="property-title fs-6 text-truncate"><?php echo htmlspecialchars($benzer_ilan["baslik"]); ?></h5>
                                                <div class="d-flex justify-content-between">
                                                    <div class="property-feature small">
                                                        <i class="fas fa-bed"></i> <?php echo $benzer_ilan["oda_sayisi"]; ?> Oda
                                                    </div>
                                                    <div class="property-feature small">
                                                        <i class="fas fa-ruler-combined"></i> <?php echo $benzer_ilan["metrekare"]; ?> m²
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="fw-bold text-primary"><?php echo number_format($benzer_ilan["fiyat"], 0, ',', '.'); ?> ₺</span>
                                                </div>
                                                <div class="mt-2">
                                                    <a href="ilan_detay.php?id=<?php echo $benzer_ilan["id"]; ?>" class="btn btn-sm btn-outline-primary">Detaylar</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>

<script>
// Paylaşım fonksiyonu
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($ilan["baslik"]); ?>',
            text: '<?php echo htmlspecialchars(substr($ilan["aciklama"], 0, 100)) . "..."; ?>',
            url: window.location.href
        })
        .catch(console.error);
    } else {
        // Kopyala butonu
        const tempInput = document.createElement('input');
        tempInput.value = window.location.href;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        alert('İlan linki panoya kopyalandı!');
    }
}
</script>