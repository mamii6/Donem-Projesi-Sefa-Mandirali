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

// Yardımcı fonksiyon - değer kontrolü
function getValueOrDefault($value, $default = 'Belirtilmemiş') {
    if (isset($value) && $value !== '' && $value !== null) {
        return htmlspecialchars($value);
    }
    return $default;
}

// Favori sayısını al
$favori_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ?");
$favori_sayisi_stmt->execute([$kullanici_id]);
$favori_sayisi = $favori_sayisi_stmt->fetchColumn();

// İlan sayısını al
$ilan_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM ilanlar WHERE kullanici_id = ?");
$ilan_sayisi_stmt->execute([$kullanici_id]);
$ilan_sayisi = $ilan_sayisi_stmt->fetchColumn();

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Profil bilgilerini güncelleme
    if (isset($_POST["profil_guncelle"])) {
        $ad = trim($_POST["ad"] ?? '');
        $soyad = trim($_POST["soyad"] ?? '');
        $telefon = trim($_POST["telefon"] ?? '');
        $dogum_tarihi = trim($_POST["dogum_tarihi"] ?? '');
        $cinsiyet = trim($_POST["cinsiyet"] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE kullanicilar SET ad = ?, soyad = ?, telefon = ?, dogum_tarihi = ?, cinsiyet = ? WHERE id = ?");
            $stmt->execute([$ad, $soyad, $telefon, $dogum_tarihi, $cinsiyet, $kullanici_id]);
            $basari_mesaji = "Profil bilgileriniz başarıyla güncellendi.";
            
            // Güncellenen kullanıcı bilgilerini al
            $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullanici_id]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Kullanıcı verilerini güncelle
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
        } catch (PDOException $e) {
            $hata_mesaji = "Profil güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
    
    // Şifre değiştirme
    if (isset($_POST["sifre_guncelle"])) {
        $eski_sifre = $_POST["eski_sifre"] ?? '';
        $yeni_sifre = $_POST["yeni_sifre"] ?? '';
        $yeni_sifre_tekrar = $_POST["yeni_sifre_tekrar"] ?? '';

        try {
            $stmt = $pdo->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullanici_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($eski_sifre, $row["sifre"])) {
                if ($yeni_sifre === $yeni_sifre_tekrar) {
                    if (strlen($yeni_sifre) >= 6) {
                        $hash_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
                        $stmt->execute([$hash_sifre, $kullanici_id]);
                        $sifre_basari = "Şifreniz başarıyla değiştirildi.";
                    } else {
                        $sifre_hata = "Yeni şifre en az 6 karakter olmalıdır.";
                    }
                } else {
                    $sifre_hata = "Yeni şifreler uyuşmuyor!";
                }
            } else {
                $sifre_hata = "Eski şifre yanlış!";
            }
        } catch (PDOException $e) {
            $sifre_hata = "Şifre değiştirilirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>
<style>

/* Koyu tema için ana stil değişiklikleri */
.card {
background-color: #252525;
color: #e0e0e0;
border: 1px solid #333;
}

.card-header {
background-color: #1a1a1a !important;
border-bottom: 1px solid #333;
color: #fff;
}

.card-body {
background-color: #252525;
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
</style>
<link rel="stylesheet" href="css/profil.css">
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Profil Düzenle</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="profil.php">Profilim</a> / Profil Düzenle
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
                        <p><?php echo isset($user_data["yetki"]) && !empty($user_data["yetki"]) ? htmlspecialchars($user_data["yetki"]) : 'Üye'; ?></p>
                    </div>
                    
                    <ul class="profile-menu">
                        <li><a href="profil.php"><i class="fas fa-user"></i> Profilim</a></li>
                        <li><a href="ilanlarim.php"><i class="fas fa-home"></i> İlanlarım <span class="badge bg-primary"><?php echo $ilan_sayisi; ?></span></a></li>
                        <li><a href="favorilerim.php"><i class="fas fa-heart"></i> Favorilerim <span class="badge bg-danger"><?php echo $favori_sayisi; ?></span></a></li>
                        <li><a href="mesajlar.php"><i class="fas fa-envelope"></i> Mesajlarım</a></li>
                        <li><a href="profil_duzenle.php" class="active"><i class="fas fa-cog"></i> Ayarlar</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Sağ Ana İçerik -->
            <div class="col-lg-8 col-md-7">
                <div class="content-section mb-4">
                    <h3 class="content-title">Profil Bilgilerini Düzenle</h3>
                    
                    <?php if (isset($basari_mesaji)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($basari_mesaji); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($hata_mesaji)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($hata_mesaji); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Profil Resmi Yükleme -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="m-0">Profil Resmi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <?php if (!empty($user_data['profil_resmi'])): ?>
                                        <img src="uploads/profiller/<?php echo htmlspecialchars($user_data['profil_resmi']); ?>" alt="Profil Resmi" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="img/profile-placeholder.jpg" alt="Profil Resmi" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <form action="profil_resmi_yukle.php" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="profil_resmi" class="form-label">Yeni Profil Resmi Yükle</label>
                                            <input type="file" name="profil_resmi" id="profil_resmi" class="form-control" accept="image/*">
                                            <div class="form-text">Önerilen boyut: Minimum 200x200 piksel, maksimum 2MB.</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Resmi Yükle</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kişisel Bilgiler Formu -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="m-0">Kişisel Bilgiler</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ad" class="form-label">Ad</label>
                                        <input type="text" name="ad" id="ad" class="form-control" value="<?php echo getValueOrDefault($user_data['ad'], ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="soyad" class="form-label">Soyad</label>
                                        <input type="text" name="soyad" id="soyad" class="form-control" value="<?php echo getValueOrDefault($user_data['soyad'], ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo getValueOrDefault($user_data['email'], ''); ?>" disabled>
                                    <div class="form-text">E-posta adresiniz değiştirilemez.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telefon" class="form-label">Telefon</label>
                                    <input type="tel" name="telefon" id="telefon" class="form-control" value="<?php echo getValueOrDefault($user_data['telefon'], ''); ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="dogum_tarihi" class="form-label">Doğum Tarihi</label>
                                        <input type="date" name="dogum_tarihi" id="dogum_tarihi" class="form-control" value="<?php echo getValueOrDefault($user_data['dogum_tarihi'], ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cinsiyet" class="form-label">Cinsiyet</label>
                                        <select name="cinsiyet" id="cinsiyet" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <option value="Erkek" <?php echo (isset($user_data['cinsiyet']) && $user_data['cinsiyet'] == 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
                                            <option value="Kadın" <?php echo (isset($user_data['cinsiyet']) && $user_data['cinsiyet'] == 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="profil_guncelle" value="1">
                                <button type="submit" class="btn btn-primary">Bilgileri Güncelle</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Şifre Değiştirme Formu -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Şifre Değiştir</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($sifre_basari)): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($sifre_basari); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($sifre_hata)): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($sifre_hata); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="eski_sifre" class="form-label">Mevcut Şifre</label>
                                    <input type="password" name="eski_sifre" id="eski_sifre" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="yeni_sifre" class="form-label">Yeni Şifre</label>
                                    <input type="password" name="yeni_sifre" id="yeni_sifre" class="form-control" required>
                                    <div class="form-text">Şifreniz en az 6 karakter olmalıdır.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="yeni_sifre_tekrar" class="form-label">Yeni Şifre (Tekrar)</label>
                                    <input type="password" name="yeni_sifre_tekrar" id="yeni_sifre_tekrar" class="form-control" required>
                                </div>
                                
                                <input type="hidden" name="sifre_guncelle" value="1">
                                <button type="submit" class="btn btn-warning">Şifreyi Değiştir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>