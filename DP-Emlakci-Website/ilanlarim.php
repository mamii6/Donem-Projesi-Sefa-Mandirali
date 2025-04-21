<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];

// Veritabanı karakter kümesini ayarla
try {
    $pdo->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    // Hata durumunda sessizce devam et
}

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

// Kullanıcı verilerini güvenli bir şekilde yeni bir diziye aktaralım
$user_data = [];
if (is_array($kullanici)) {
    $user_data = [
        'id' => $kullanici['id'] ?? '',
        'ad' => $kullanici['ad'] ?? '',
        'soyad' => $kullanici['soyad'] ?? '',
        'email' => $kullanici['email'] ?? '',
        'telefon' => $kullanici['telefon'] ?? '',
        'dogum_tarihi' => $kullanici['dogum_tarihi'] ?? '',
        'cinsiyet' => $kullanici['cinsiyet'] ?? '',
        'yetki' => $kullanici['yetki'] ?? '',
        'profil_resmi' => $kullanici['profil_resmi'] ?? '',
        'kayit_tarihi' => $kullanici['kayit_tarihi'] ?? '',
    ];
}

// Toplam ilan sayısını al
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ilanlar WHERE kullanici_id = ?");
$count_stmt->execute([$kullanici_id]);
$total_ilanlar = $count_stmt->fetchColumn();

// Sayfalama için değişkenler
$ilans_per_page = 6; // Sayfa başına ilan sayısı
$total_pages = ceil($total_ilanlar / $ilans_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $ilans_per_page;

// Kullanıcının ilanlarını çek - sayfalama ile
$stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE kullanici_id = ? ORDER BY eklenme_tarihi DESC LIMIT " . $ilans_per_page . " OFFSET " . $offset);
$stmt->execute([$kullanici_id]);
$ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Favori sayısını al
$favori_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ?");
$favori_sayisi_stmt->execute([$kullanici_id]);
$favori_sayisi = $favori_sayisi_stmt->fetchColumn();

// Yardımcı fonksiyon - değer kontrolü
function getValueOrDefault($value, $default = 'Belirtilmemiş') {
    if (isset($value) && $value !== '' && $value !== null) {
        return htmlspecialchars($value);
    }
    return $default;
}

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/profil.css">

<style>
.property-card {
    background-color: #252525;
    color: #e0e0e0;
    border: 1px solid #333;
}

.property-details {
    background-color: #252525;
    color: #e0e0e0;
}

.property-title {
    color: #fff;
}

.text-muted {
    color: #aaa !important;
}

.alert-info {
    background-color: #1a2a36;
    color: #a8c7df;
    border-color: #274967;
}
.card {
    background-color: #252525 !important;
    color: #e0e0e0;
    border: 1px solid #333;
}

.card-header {
    background-color: #1a1a1a !important;
    border-bottom: 1px solid #333;
    color: #fff;
}

.card-body {
    background-color: #252525 !important;
    color: #e0e0e0;
}

.content-section {
    background-color: #252525;
    color: #e0e0e0;
    border: 1px solid #333;
    border-radius: 5px;
    padding: 20px;
}

.content-title {
    color: #fff;
    border-bottom: 1px solid #333;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.profile-sidebar {
    background-color: #252525;
    border: 1px solid #333;
}

.profile-menu {
    background-color: #252525;
}

.profile-menu a {
    color: #e0e0e0;
    border-bottom: 1px solid #333;
}

.profile-menu a:hover, 
.profile-menu a.active {
    background-color: #333;
    color: #fff;
}

.profile-info {
    border-bottom: 1px solid #333;
}

.profile-info h4, 
.profile-info p {
    color: #fff;
}

/* Form elemanları için koyu tema ayarları */
.form-control, 
.form-select {
    background-color: #333;
    border: 1px solid #444;
    color: #e0e0e0;
}

.form-control:focus, 
.form-select:focus {
    background-color: #3a3a3a;
    border-color: #555;
    color: #fff;
    box-shadow: 0 0 0 0.25rem rgba(66, 70, 73, 0.25);
}

.form-text {
    color: #aaa;
}

.form-label {
    color: #e0e0e0;
}

/* Diğer bileşenlerin düzenlemeleri */
.alert-success {
    background-color: #1e392a;
    color: #a3cfbb;
    border-color: #2c5942;
}

.alert-danger {
    background-color: #392a2a;
    color: #cfb0b0;
    border-color: #5c3838;
}

.bg-light {
    background-color: #1a1a1a !important;
    color: #fff;
}

/* Sayfalama düğmeleri için koyu tema */
.pagination .page-link {
    background-color: #333;
    border-color: #444;
    color: #e0e0e0;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    background-color: #252525;
    border-color: #333;
    color: #666;
}

.page-header {
    background-color: #1a1a1a;
    color: #fff;
}

.breadcrumb {
    color: #aaa;
}

.breadcrumb a {
    color: #aaa;
}

.breadcrumb a:hover {
    color: #fff;
}

/* Butonlar için opsiyonel düzenlemeler */
.btn-outline-secondary {
    color: #e0e0e0;
    border-color: #444;
}

.btn-outline-secondary:hover {
    background-color: #444;
    color: #fff;
}

/* Ek stillemeler */
.border {
    border-color: #444 !important;
}

/* Badge için düzenlemeler */
.badge.bg-primary {
    background-color: #0d6efd !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-success {
    background-color: #198754 !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

/* Mevcut görsel için çerçeve */
.border.p-2.rounded {
    background-color: #333;
}
<style>
</style>
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>İlanlarım</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="profil.php">Profilim</a> / İlanlarım
        </div>
    </div>
</div>

<!-- İlanlarım Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Sol Kenar Menüsü -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="profile-sidebar">
                    <div class="profile-img">
                        <?php if (!empty($user_data['profil_resmi'])): ?>
                            <img src="uploads/profiller/<?php echo htmlspecialchars($user_data['profil_resmi']); ?>" alt="Profil Resmi">
                        <?php else: ?>
                            <img src="img/profile-placeholder.jpg" alt="Profil Resmi">
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h4><?php echo getValueOrDefault($user_data["ad"]); ?> <?php echo getValueOrDefault($user_data["soyad"], ''); ?></h4>
                        <p><?php echo isset($user_data["yetki"]) && !empty($user_data["yetki"]) ? htmlspecialchars($user_data["yetki"]) : 'Üye'; ?></p>
                    </div>
                    
                    <ul class="profile-menu">
                        <li><a href="profil.php"><i class="fas fa-user"></i> Profilim</a></li>
                        <li><a href="ilanlarim.php" class="active"><i class="fas fa-home"></i> İlanlarım <span class="badge bg-primary"><?php echo $total_ilanlar; ?></span></a></li>
                        <li><a href="favoriler.php"><i class="fas fa-heart"></i> Favorilerim <span class="badge bg-danger"><?php echo $favori_sayisi; ?></span></a></li>
                        <li><a href="mesajlar.php"><i class="fas fa-envelope"></i> Mesajlarım</a></li>
                        <li><a href="profil_duzenle.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
                
                <!-- Yeni İlan Ekle Kartı -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                        <h5>Yeni İlan Ekle</h5>
                        <p class="text-muted">Emlak portföyünüze yeni bir ilan eklemek için tıklayın.</p>
                        <a href="ilan_ekle.php" class="btn btn-primary w-100">İlan Ekle</a>
                    </div>
                </div>
                
                <!-- İlan İstatistikleri -->
                <?php
                // Onaylı/bekleyen/reddedilen ilan sayıları
                $onayliIlanlar = 0;
                $bekleyenIlanlar = 0;
                $reddedilenIlanlar = 0;
                
                try {
                    $durum_stmt = $pdo->prepare("SELECT durum, COUNT(*) as sayi FROM ilanlar WHERE kullanici_id = ? GROUP BY durum");
                    $durum_stmt->execute([$kullanici_id]);
                    $durumlar = $durum_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($durumlar as $durum) {
                        if ($durum['durum'] === 'onaylı') {
                            $onayliIlanlar = $durum['sayi'];
                        } elseif ($durum['durum'] === 'beklemede') {
                            $bekleyenIlanlar = $durum['sayi'];
                        } elseif ($durum['durum'] === 'reddedildi') {
                            $reddedilenIlanlar = $durum['sayi'];
                        }
                    }
                } catch (PDOException $e) {
                    // Sütun yoksa hata vermez
                }
                ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="m-0">İlan İstatistikleri</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Toplam İlanlar</span>
                            <span class="fw-bold"><?php echo $total_ilanlar; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Onaylı İlanlar</span>
                            <span class="fw-bold"><?php echo $onayliIlanlar; ?></span>
                        </div>
                        <?php if ($bekleyenIlanlar > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Bekleyen İlanlar</span>
                            <span class="fw-bold"><?php echo $bekleyenIlanlar; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($reddedilenIlanlar > 0): ?>
                        <div class="d-flex justify-content-between">
                            <span>Reddedilen İlanlar</span>
                            <span class="fw-bold"><?php echo $reddedilenIlanlar; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Ana İçerik -->
            <div class="col-lg-8 col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="content-title mb-0">İlanlarım (<?php echo $total_ilanlar; ?>)</h3>
                    <a href="ilan_ekle.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Yeni İlan Ekle</a>
                </div>
                
                <?php if (count($ilanlar) > 0): ?>
                    <div class="row properties-grid">
                        <?php foreach ($ilanlar as $ilan): ?>
                            <div class="col-md-6 mb-4">
                                <div class="property-card">
                                    <div class="property-img" style="height: 200px;">
                                        <?php if (!empty($ilan["resim"])): ?>
                                            <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"] ?? 'İlan'); ?>" style="height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="img/property-placeholder.jpg" alt="Emlak Görseli" style="height: 100%; object-fit: cover;">
                                        <?php endif; ?>
                                        
                                        <div class="property-tag">
                                            <?php
                                            $durum = isset($ilan["durum"]) ? $ilan["durum"] : 'onaylı';
                                            if ($durum === 'onaylı') {
                                                echo 'Aktif';
                                            } elseif ($durum === 'beklemede') {
                                                echo 'Beklemede';
                                            } elseif ($durum === 'reddedildi') {
                                                echo 'Reddedildi';
                                            }
                                            ?>
                                        </div>
                                        <div class="property-price"><?php echo isset($ilan["fiyat"]) ? number_format($ilan["fiyat"], 0, ',', '.') : '0'; ?> ₺</div>
                                    </div>
                                    <div class="property-details">
                                        <h3 class="property-title text-truncate"><?php echo htmlspecialchars($ilan["baslik"] ?? 'İlan Başlığı'); ?></h3>
                                        <div class="property-location mb-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo isset($ilan["adres"]) ? htmlspecialchars($ilan["adres"]) : 'Adres belirtilmemiş'; ?>
                                        </div>
                                        <div class="property-features">
                                            <div class="property-feature">
                                                <i class="fas fa-bed"></i> <?php echo $ilan["oda_sayisi"] ?? '0'; ?> Oda
                                            </div>
                                            <div class="property-feature">
                                                <i class="fas fa-ruler-combined"></i> <?php echo $ilan["metrekare"] ?? '0'; ?> m²
                                            </div>
                                            <?php if (isset($ilan["goruntulenme"])): ?>
                                            <div class="property-feature">
                                                <i class="fas fa-eye"></i> <?php echo $ilan["goruntulenme"]; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="property-date mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                <?php echo isset($ilan["eklenme_tarihi"]) ? date('d.m.Y', strtotime($ilan["eklenme_tarihi"])) : '-'; ?>
                                            </small>
                                        </div>
                                        <div class="property-actions d-flex gap-2">
                                            <a href="ilan_detay.php?id=<?php echo $ilan["id"] ?? '0'; ?>" class="btn btn-primary btn-sm flex-fill">
                                                <i class="fas fa-eye me-1"></i>Görüntüle
                                            </a>
                                            <a href="ilan_duzenle.php?id=<?php echo $ilan["id"] ?? '0'; ?>" class="btn btn-warning btn-sm flex-fill">
                                                <i class="fas fa-edit me-1"></i>Düzenle
                                            </a>
                                            <a href="ilan_sil.php?id=<?php echo $ilan["id"] ?? '0'; ?>" 
                                               class="btn btn-danger btn-sm flex-fill" 
                                               onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?');">
                                                <i class="fas fa-trash-alt me-1"></i>Sil
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Sayfalama -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="İlan sayfaları" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Önceki">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Sonraki">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-info text-center p-5">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <h4>Henüz İlan Eklenmemiş</h4>
                        <p class="mb-4">Emlak portföyünüze yeni bir ilan eklemek için aşağıdaki butona tıklayın.</p>
                        <a href="ilan_ekle.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Yeni İlan Ekle</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>