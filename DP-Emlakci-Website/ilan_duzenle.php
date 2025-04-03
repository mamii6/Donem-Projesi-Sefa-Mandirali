<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: ilanlarim.php");
    exit;
}

$ilan_id = $_GET["id"];
$kullanici_id = $_SESSION["kullanici_id"];

// Veritabanı karakter kümesini ayarla
try {
    $pdo->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    // Hata durumunda sessizce devam et
}

// İlan bilgilerini çek
$ilan_stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE id = ? AND kullanici_id = ?");
$ilan_stmt->execute([$ilan_id, $kullanici_id]);
$ilan = $ilan_stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) {
    header("Location: ilanlarim.php");
    exit;
}

// Kullanıcı bilgilerini çek
$user_stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$user_stmt->execute([$kullanici_id]);
$kullanici = $user_stmt->fetch(PDO::FETCH_ASSOC);

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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $baslik = trim($_POST["baslik"] ?? '');
    $aciklama = trim($_POST["aciklama"] ?? '');
    $fiyat = floatval($_POST["fiyat"] ?? 0);
    $adres = trim($_POST["adres"] ?? '');
    $oda_sayisi = intval($_POST["oda_sayisi"] ?? 0);
    $metrekare = floatval($_POST["metrekare"] ?? 0);
    $ilan_tipi = trim($_POST["ilan_tipi"] ?? '');
    $emlak_tipi = trim($_POST["emlak_tipi"] ?? '');
    $resimAdi = $ilan["resim"] ?? '';

    // Dosya yükleme işlemi
    if (!empty($_FILES["resim"]["name"])) {
        $dosya = $_FILES["resim"];
        $uzanti = strtolower(pathinfo($dosya["name"], PATHINFO_EXTENSION));
        $izinVerilenUzantilar = ["jpg", "jpeg", "png", "gif", "webp"];

        if (in_array($uzanti, $izinVerilenUzantilar) && $dosya["size"] <= 5 * 1024 * 1024) { // 5MB limit
            $resimAdi = time() . "_" . basename($dosya["name"]);
            $hedefYol = "uploads/ilanlar/" . $resimAdi;
            
            // Hedef klasörün varlığını kontrol et
            if (!file_exists("uploads/ilanlar/")) {
                mkdir("uploads/ilanlar/", 0777, true);
            }
            
            if (move_uploaded_file($dosya["tmp_name"], $hedefYol)) {
                // Başarıyla yüklendi, eski resmi silmek isteyebilirsiniz
                if (!empty($ilan["resim"]) && $ilan["resim"] != $resimAdi && file_exists("uploads/ilanlar/" . $ilan["resim"])) {
                    @unlink("uploads/ilanlar/" . $ilan["resim"]);
                }
            } else {
                $hata_mesaji = "Resim yüklenirken bir hata oluştu.";
            }
        } else {
            $hata_mesaji = "Geçersiz dosya formatı veya dosya boyutu çok büyük! Maksimum boyut: 5MB";
        }
    }

    try {
        // İlan bilgilerini güncelle
        $update_stmt = $pdo->prepare("UPDATE ilanlar SET 
            baslik = ?, 
            aciklama = ?, 
            fiyat = ?, 
            adres = ?, 
            oda_sayisi = ?, 
            metrekare = ?, 
            ilan_tipi = ?,
            emlak_tipi = ?,
            resim = ? 
            WHERE id = ? AND kullanici_id = ?");
            
        $update_stmt->execute([
            $baslik, 
            $aciklama, 
            $fiyat, 
            $adres, 
            $oda_sayisi, 
            $metrekare,
            $ilan_tipi,
            $emlak_tipi,
            $resimAdi, 
            $ilan_id, 
            $kullanici_id
        ]);
        
        $success_mesaji = "İlan başarıyla güncellendi!";
        
        // Güncel ilan bilgilerini al
        $ilan_stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE id = ?");
        $ilan_stmt->execute([$ilan_id]);
        $ilan = $ilan_stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $hata_mesaji = "İlan güncellenirken bir hata oluştu: " . $e->getMessage();
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/profil.css">
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>İlan Düzenle</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="ilanlarim.php">İlanlarım</a> / İlan Düzenle
        </div>
    </div>
</div>

<!-- Main Section -->
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
                        <li><a href="ilanlarim.php" class="active"><i class="fas fa-home"></i> İlanlarım <span class="badge bg-primary"><?php echo $ilan_sayisi; ?></span></a></li>
                        <li><a href="favorilerim.php"><i class="fas fa-heart"></i> Favorilerim <span class="badge bg-danger"><?php echo $favori_sayisi; ?></span></a></li>
                        <li><a href="mesajlar.php"><i class="fas fa-envelope"></i> Mesajlarım</a></li>
                        <li><a href="profil_duzenle.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="m-0">İlan Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>İlan No</span>
                            <span class="fw-bold">#<?php echo $ilan["id"]; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Durum</span>
                            <span class="fw-bold">
                                <?php 
                                $durum = isset($ilan["durum"]) ? $ilan["durum"] : 'onaylı';
                                if ($durum === 'onaylı') {
                                    echo '<span class="badge bg-success">Aktif</span>';
                                } elseif ($durum === 'beklemede') {
                                    echo '<span class="badge bg-warning">Beklemede</span>';
                                } elseif ($durum === 'reddedildi') {
                                    echo '<span class="badge bg-danger">Reddedildi</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Eklenme Tarihi</span>
                            <span class="fw-bold"><?php echo isset($ilan["eklenme_tarihi"]) ? date('d.m.Y', strtotime($ilan["eklenme_tarihi"])) : '-'; ?></span>
                        </div>
                        <?php if (isset($ilan["goruntulenme"])): ?>
                        <div class="d-flex justify-content-between">
                            <span>Görüntülenme</span>
                            <span class="fw-bold"><?php echo $ilan["goruntulenme"]; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <a href="ilan_detay.php?id=<?php echo $ilan_id; ?>" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-eye me-2"></i>İlanı Görüntüle
                        </a>
                        <a href="ilanlarim.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>İlanlarıma Dön
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Ana İçerik -->
            <div class="col-lg-8 col-md-7">
                <div class="content-section mb-4">
                    <h3 class="content-title">İlan Bilgilerini Düzenle</h3>
                    
                    <?php if (isset($success_mesaji)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_mesaji); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($hata_mesaji)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($hata_mesaji); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="m-0">Temel Bilgiler</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="baslik" class="form-label">İlan Başlığı</label>
                                    <input type="text" name="baslik" id="baslik" class="form-control" value="<?php echo htmlspecialchars($ilan['baslik'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ilan_tipi" class="form-label">İlan Tipi</label>
                                        <select name="ilan_tipi" id="ilan_tipi" class="form-select" required>
                                            <option value="">Seçiniz</option>
                                            <option value="satilik" <?php echo (isset($ilan['ilan_tipi']) && $ilan['ilan_tipi'] == 'satilik') ? 'selected' : ''; ?>>Satılık</option>
                                            <option value="kiralik" <?php echo (isset($ilan['ilan_tipi']) && $ilan['ilan_tipi'] == 'kiralik') ? 'selected' : ''; ?>>Kiralık</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emlak_tipi" class="form-label">Emlak Tipi</label>
                                        <select name="emlak_tipi" id="emlak_tipi" class="form-select" required>
                                            <option value="">Seçiniz</option>
                                            <option value="daire" <?php echo (isset($ilan['emlak_tipi']) && $ilan['emlak_tipi'] == 'daire') ? 'selected' : ''; ?>>Daire</option>
                                            <option value="mustakil" <?php echo (isset($ilan['emlak_tipi']) && $ilan['emlak_tipi'] == 'mustakil') ? 'selected' : ''; ?>>Müstakil Ev</option>
                                            <option value="villa" <?php echo (isset($ilan['emlak_tipi']) && $ilan['emlak_tipi'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
                                            <option value="arsa" <?php echo (isset($ilan['emlak_tipi']) && $ilan['emlak_tipi'] == 'arsa') ? 'selected' : ''; ?>>Arsa</option>
                                            <option value="is_yeri" <?php echo (isset($ilan['emlak_tipi']) && $ilan['emlak_tipi'] == 'is_yeri') ? 'selected' : ''; ?>>İş Yeri</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fiyat" class="form-label">Fiyat (₺)</label>
                                        <input type="number" step="0.01" name="fiyat" id="fiyat" class="form-control" value="<?php echo $ilan['fiyat'] ?? 0; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="metrekare" class="form-label">Metrekare (m²)</label>
                                        <input type="number" step="0.01" name="metrekare" id="metrekare" class="form-control" value="<?php echo $ilan['metrekare'] ?? 0; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="oda_sayisi" class="form-label">Oda Sayısı</label>
                                    <input type="number" name="oda_sayisi" id="oda_sayisi" class="form-control" value="<?php echo $ilan['oda_sayisi'] ?? 0; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="adres" class="form-label">Adres</label>
                                    <textarea name="adres" id="adres" class="form-control" rows="2" required><?php echo htmlspecialchars($ilan['adres'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="m-0">İlan Açıklaması</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="aciklama" class="form-label">Detaylı Açıklama</label>
                                    <textarea name="aciklama" id="aciklama" class="form-control" rows="6" required><?php echo htmlspecialchars($ilan['aciklama'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="m-0">İlan Görseli</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Mevcut Görsel</label>
                                        <div class="border p-2 rounded text-center">
                                            <?php if (!empty($ilan["resim"])): ?>
                                                <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" class="img-fluid" style="max-height: 250px;" alt="İlan Görseli">
                                            <?php else: ?>
                                                <img src="img/property-placeholder.jpg" class="img-fluid" style="max-height: 250px;" alt="Görsel Yok">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-7 mb-3">
                                        <label for="resim" class="form-label">Yeni Görsel Yükle</label>
                                        <input type="file" name="resim" id="resim" class="form-control" accept="image/*">
                                        <div class="form-text">
                                            <small>İzin verilen formatlar: JPG, JPEG, PNG, GIF, WEBP</small><br>
                                            <small>Maksimum dosya boyutu: 5MB</small><br>
                                            <small>Yeni görsel yüklerseniz, eski görsel silinecektir.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>İlanı Güncelle
                            </button>
                            <a href="ilanlarim.php" class="btn btn-outline-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>