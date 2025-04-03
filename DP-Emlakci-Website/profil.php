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

// Kullanıcının son 3 ilanını çek
$stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE kullanici_id = ? ORDER BY eklenme_tarihi DESC LIMIT 3");
$stmt->execute([$kullanici_id]);
$son_ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İlan sayısını al
$ilan_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM ilanlar WHERE kullanici_id = ?");
$ilan_sayisi_stmt->execute([$kullanici_id]);
$ilan_sayisi = $ilan_sayisi_stmt->fetchColumn();

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
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Profilim</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / Profilim
        </div>
    </div>
</div>

<!-- Profile Section -->
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
                    </div>
                    
                    <ul class="profile-menu">
                        <li><a href="profil.php" class="active"><i class="fas fa-user"></i> Profilim</a></li>
                        <li><a href="ilanlarim.php"><i class="fas fa-home"></i> İlanlarım <span class="badge bg-primary"><?php echo $ilan_sayisi; ?></span></a></li>
                        <li><a href="favoriler.php"><i class="fas fa-heart"></i> Favorilerim <span class="badge bg-danger"><?php echo $favori_sayisi; ?></span></a></li>
                        <li><a href="mesajlar.php"><i class="fas fa-envelope"></i> Mesajlarım</a></li>
                        <li><a href="profil_duzenle.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Sağ Ana İçerik -->
            <div class="col-lg-8 col-md-7">
                <div class="profile-content">
                    <div class="row">
                        <!-- İstatistik Kartları -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-card-icon bg-primary-light">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="stat-card-info">
                                    <h5><?php echo $ilan_sayisi; ?></h5>
                                    <p>İlan</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-card-icon bg-danger-light">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stat-card-info">
                                    <h5><?php echo $favori_sayisi; ?></h5>
                                    <p>Favori</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-card-icon bg-success-light">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-card-info">
                                    <h5>
                                        <?php 
                                        if (!empty($user_data["kayit_tarihi"])) {
                                            echo date('d.m.Y', strtotime($user_data["kayit_tarihi"]));
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </h5>
                                    <p>Üyelik Tarihi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kişisel Bilgiler -->
                    <div class="content-section mb-4">
                        <h3 class="content-title">Kişisel Bilgilerim</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Ad Soyad</span>
                                    <span class="info-value">
                                        <?php 
                                        $adSoyad = getValueOrDefault($user_data["ad"]);
                                        if (!empty($user_data["soyad"])) {
                                            $adSoyad .= ' ' . getValueOrDefault($user_data["soyad"]);
                                        }
                                        echo $adSoyad;
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">E-posta</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!empty($user_data["email"])) {
                                            echo htmlspecialchars($user_data["email"]);
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Telefon</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!empty($user_data["telefon"])) {
                                            echo htmlspecialchars($user_data["telefon"]);
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Doğum Tarihi</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!empty($user_data["dogum_tarihi"])) {
                                            echo htmlspecialchars($user_data["dogum_tarihi"]);
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Cinsiyet</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!empty($user_data["cinsiyet"])) {
                                            echo htmlspecialchars($user_data["cinsiyet"]);
                                        } else {
                                            echo 'Belirtilmemiş';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <a href="profil_duzenle.php" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Profili Düzenle</a>
                        </div>
                    </div>
                    
                    <!-- Son İlanlar Özeti -->
                    <div class="content-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="content-title mb-0">Son İlanlarım</h3>
                            <a href="ilanlarim.php" class="btn btn-sm btn-outline-primary">Tüm İlanlarımı Gör</a>
                        </div>
                        
                        <?php if (count($son_ilanlar) > 0): ?>
                            <div class="row">
                                <?php foreach ($son_ilanlar as $ilan): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="property-card-small">
                                        <div class="property-img-small">
                                            <?php if (!empty($ilan["resim"])): ?>
                                                <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"] ?? 'İlan'); ?>">
                                            <?php else: ?>
                                                <img src="img/property-placeholder.jpg" alt="Emlak Görseli">
                                            <?php endif; ?>
                                        </div>
                                        <div class="property-info-small">
                                            <h4 class="property-title-small text-truncate"><?php echo htmlspecialchars($ilan["baslik"] ?? 'İlan Başlığı'); ?></h4>
                                            <p class="property-price-small"><?php echo isset($ilan["fiyat"]) ? number_format($ilan["fiyat"], 0, ',', '.') : '0'; ?> ₺</p>
                                            <div class="property-meta-small">
                                                <span><i class="fas fa-bed"></i> <?php echo $ilan["oda_sayisi"] ?? '0'; ?></span>
                                                <span><i class="fas fa-ruler-combined"></i> <?php echo $ilan["metrekare"] ?? '0'; ?> m²</span>
                                            </div>
                                            <a href="ilan_detay.php?id=<?php echo $ilan["id"] ?? '0'; ?>" class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">Henüz ilan eklememişsiniz.</p>
                            </div>
                            <a href="ilan_ekle.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Yeni İlan Ekle</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>