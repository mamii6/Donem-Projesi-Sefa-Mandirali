<?php
session_start();
require_once "db.php";

// Veritabanı karakter kümesini ayarla
try {
    $pdo->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    // Hata durumunda sessizce devam et
}

// Filtre parametrelerini al
$min_fiyat = isset($_GET['min_fiyat']) ? floatval($_GET['min_fiyat']) : '';
$max_fiyat = isset($_GET['max_fiyat']) ? floatval($_GET['max_fiyat']) : '';
$min_metrekare = isset($_GET['metrekare_min']) ? floatval($_GET['metrekare_min']) : '';
$max_metrekare = isset($_GET['metrekare_max']) ? floatval($_GET['metrekare_max']) : '';
$oda_sayisi = isset($_GET['oda_sayisi']) ? $_GET['oda_sayisi'] : '';
$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$adres = isset($_GET['adres']) ? trim($_GET['adres']) : '';
$siralama = isset($_GET['siralama']) ? trim($_GET['siralama']) : 'en_yeni';

// Sayfalama için değişkenler
$sayfa = isset($_GET['sayfa']) ? max(1, intval($_GET['sayfa'])) : 1;
$kayit_sayisi = 9; // Sayfa başına gösterilecek ilan sayısı
$baslangic = ($sayfa - 1) * $kayit_sayisi;

// SQL sorgusu için parametreler
$params = [];
$where_conditions = [];

// Durum filter'ı kontrolü - tabloda durum sütunu varsa
try {
    $table_info = $pdo->query("SHOW COLUMNS FROM ilanlar LIKE 'durum'")->fetchAll();
    if (!empty($table_info)) {
        $where_conditions[] = "durum = 'onaylı'"; // Sadece onaylı ilanları göster
    }
} catch (Exception $e) {
    // Sütun yoksa sessizce devam et
}

// Fiyat filtresi
if (!empty($min_fiyat)) {
    $where_conditions[] = "fiyat >= ?";
    $params[] = $min_fiyat;
}

if (!empty($max_fiyat)) {
    $where_conditions[] = "fiyat <= ?";
    $params[] = $max_fiyat;
}

// Metrekare filtresi
if (!empty($min_metrekare)) {
    $where_conditions[] = "metrekare >= ?";
    $params[] = $min_metrekare;
}

if (!empty($max_metrekare)) {
    $where_conditions[] = "metrekare <= ?";
    $params[] = $max_metrekare;
}

// Oda sayısı filtresi
if (!empty($oda_sayisi)) {
    $where_conditions[] = "oda_sayisi = ?";
    $params[] = $oda_sayisi;
}

// Adres filtresi
if (!empty($adres)) {
    $where_conditions[] = "adres LIKE ?";
    $params[] = "%{$adres}%";
}

// Arama filtresi (başlık, açıklama veya adres)
if (!empty($arama)) {
    $where_conditions[] = "(baslik LIKE ? OR aciklama LIKE ? OR adres LIKE ?)";
    $params[] = "%{$arama}%";
    $params[] = "%{$arama}%";
    $params[] = "%{$arama}%";
}

// WHERE koşulunu oluştur
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Sıralama belirleme
$order_clause = "ORDER BY i.eklenme_tarihi DESC"; // Varsayılan: En yeni
switch ($siralama) {
    case 'fiyat_artan':
        $order_clause = "ORDER BY i.fiyat ASC";
        break;
    case 'fiyat_azalan':
        $order_clause = "ORDER BY i.fiyat DESC";
        break;
    case 'metrekare_artan':
        $order_clause = "ORDER BY i.metrekare ASC";
        break;
    case 'metrekare_azalan':
        $order_clause = "ORDER BY i.metrekare DESC";
        break;
}

// Toplam ilan sayısını al
$count_sql = "SELECT COUNT(*) FROM ilanlar $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$toplam_ilan = $count_stmt->fetchColumn();

// Toplam sayfa sayısını hesapla
$toplam_sayfa = ceil($toplam_ilan / $kayit_sayisi);

// İlanları al
$sql = "SELECT i.*, k.ad, k.soyad 
        FROM ilanlar i 
        JOIN kullanicilar k ON i.kullanici_id = k.id 
        $where_clause 
        $order_clause 
        LIMIT $baslangic, $kayit_sayisi";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Oda sayıları için seçenek listesi
$oda_sayilari = [
    '1' => '1+0',
    '2' => '1+1',
    '3' => '2+1',
    '4' => '3+1',
    '5' => '4+1',
    '6' => '5+1 ve üzeri'
];

// Sıralama seçenekleri
$siralama_secenekleri = [
    'en_yeni' => 'En Yeni',
    'fiyat_artan' => 'Fiyat (Artan)',
    'fiyat_azalan' => 'Fiyat (Azalan)',
    'metrekare_artan' => 'Metrekare (Artan)',
    'metrekare_azalan' => 'Metrekare (Azalan)'
];

// Sayfalama URL'sini oluşturmak için yardımcı fonksiyon
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['sayfa'] = $page;
    return 'emlaklar.php?' . http_build_query($params);
}

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/styles.css">

<!-- Hero Section -->
<section class="hero-section hero-small">
    <div class="container">
        <div class="hero-content">
            <h1>Emlak İlanları</h1>
            <div class="breadcrumb-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Emlak İlanları</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding pt-4">
    <div class="container">
        <div class="row">
            <!-- Filtre Sidebar -->
            <div class="col-lg-3 mb-4">
                <!-- Arama kartı -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white">
                        <h5 class="mb-0">Arama</h5>
                    </div>
                    <div class="card-body">
                        <form action="emlaklar.php" method="GET">
                            <div class="mb-3">
                                <label class="form-label">Başlık, açıklama veya adres ile arama yapın</label>
                                <input type="text" name="arama" class="form-control" placeholder="Arama terimi..." value="<?php echo htmlspecialchars($arama); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Ara
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Filtre kartı -->
                <div class="card shadow-sm">
                    <div class="card-header  text-white">
                        <h5 class="mb-0">Filtreleme</h5>
                    </div>
                    <div class="card-body">
                        <form action="emlaklar.php" method="GET" id="filter-form">
                            <!-- Konum -->
                            <div class="mb-3">
                                <label class="form-label">Konum</label>
                                <input type="text" name="adres" class="form-control" placeholder="Şehir, ilçe veya mahalle" value="<?php echo htmlspecialchars($adres); ?>">
                            </div>
                            
                            <!-- Fiyat Aralığı -->
                            <div class="mb-3">
                                <label class="form-label">Fiyat Aralığı (₺)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_fiyat" class="form-control" placeholder="Min" value="<?php echo $min_fiyat; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_fiyat" class="form-control" placeholder="Max" value="<?php echo $max_fiyat; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Metrekare Aralığı -->
                            <div class="mb-3">
                                <label class="form-label">Metrekare (m²)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="metrekare_min" class="form-control" placeholder="Min" value="<?php echo $min_metrekare; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="metrekare_max" class="form-control" placeholder="Max" value="<?php echo $max_metrekare; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Oda Sayısı -->
                            <div class="mb-3">
                                <label class="form-label">Oda Sayısı</label>
                                <select name="oda_sayisi" class="form-select">
                                    <option value="">Tümü</option>
                                    <?php foreach ($oda_sayilari as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($oda_sayisi == $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Arama değeri varsa saklı input olarak ekleyelim -->
                            <?php if (!empty($arama)): ?>
                                <input type="hidden" name="arama" value="<?php echo htmlspecialchars($arama); ?>">
                            <?php endif; ?>
                            
                            <!-- Sıralama değeri varsa saklı input olarak ekleyelim -->
                            <?php if (!empty($siralama)): ?>
                                <input type="hidden" name="siralama" value="<?php echo htmlspecialchars($siralama); ?>">
                            <?php endif; ?>

                            <!-- Filtre Butonları -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Filtrele</button>
                                <a href="emlaklar.php" class="btn btn-outline-secondary">Filtreleri Temizle</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- İstatistikler -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-header  text-white">
                        <h5 class="mb-0">İstatistikler</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Toplam İlanlar
                                <span class="badge bg-primary rounded-pill"><?php echo $toplam_ilan; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- İlan Listesi -->
            <div class="col-lg-9">
                <!-- Sonuç Özeti -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <?php if (!empty($arama)): ?>
                            <i class="fas fa-search me-2"></i> "<?php echo htmlspecialchars($arama); ?>" için sonuçlar
                        <?php else: ?>
                            <i class="fas fa-home me-2"></i> Tüm İlanlar
                        <?php endif; ?>
                        <span class="text-muted">(<?php echo $toplam_ilan; ?> ilan)</span>
                    </h4>
                    <form id="siralama-form" action="emlaklar.php" method="GET" class="d-flex align-items-center">
                        <!-- Diğer filtreleri saklı inputlar olarak ekleyelim -->
                        <?php if (!empty($min_fiyat)): ?>
                            <input type="hidden" name="min_fiyat" value="<?php echo htmlspecialchars($min_fiyat); ?>">
                        <?php endif; ?>
                        <?php if (!empty($max_fiyat)): ?>
                            <input type="hidden" name="max_fiyat" value="<?php echo htmlspecialchars($max_fiyat); ?>">
                        <?php endif; ?>
                        <?php if (!empty($min_metrekare)): ?>
                            <input type="hidden" name="metrekare_min" value="<?php echo htmlspecialchars($min_metrekare); ?>">
                        <?php endif; ?>
                        <?php if (!empty($max_metrekare)): ?>
                            <input type="hidden" name="metrekare_max" value="<?php echo htmlspecialchars($max_metrekare); ?>">
                        <?php endif; ?>
                        <?php if (!empty($oda_sayisi)): ?>
                            <input type="hidden" name="oda_sayisi" value="<?php echo htmlspecialchars($oda_sayisi); ?>">
                        <?php endif; ?>
                        <?php if (!empty($adres)): ?>
                            <input type="hidden" name="adres" value="<?php echo htmlspecialchars($adres); ?>">
                        <?php endif; ?>
                        <?php if (!empty($arama)): ?>
                            <input type="hidden" name="arama" value="<?php echo htmlspecialchars($arama); ?>">
                        <?php endif; ?>
                        
                        <label for="siralama" class="me-2 text-nowrap">Sıralama:</label>
                        <select name="siralama" id="siralama" class="form-select form-select-sm" onchange="document.getElementById('siralama-form').submit();">
                            <?php foreach ($siralama_secenekleri as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($siralama == $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if (empty($ilanlar)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Aradığınız kriterlere uygun ilan bulunamadı.
                    </div>
                <?php else: ?>
                    <!-- İlan Grid -->
                    <div class="row properties-grid">
                        <?php foreach ($ilanlar as $ilan): ?>
                            <div class="col-xl-4 col-lg-6 col-md-6">
                                <div class="property-card">
                                    <div class="property-img">
                                        <?php if (!empty($ilan["resim"])): ?>
                                            <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>">
                                        <?php else: ?>
                                            <img src="img/property-placeholder.jpg" alt="Emlak Görseli">
                                        <?php endif; ?>
                                        <div class="property-tag">Satılık</div>
                                        <div class="property-price"><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> ₺</div>
                                        
                                        <!-- Favorileme işlemi -->
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
                                                <button type="button" class="btn-favorite <?php echo $favori_durum ? 'active' : ''; ?>" data-id="<?php echo $ilan["id"]; ?>">
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

                    <!-- Sayfalama -->
                    <?php if ($toplam_sayfa > 1): ?>
                        <nav aria-label="Sayfalama" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($sayfa <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo ($sayfa > 1) ? buildPaginationUrl($sayfa - 1) : '#'; ?>" aria-label="Önceki">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php
                                // Sayfa numaralarını göster
                                $start_page = max(1, $sayfa - 2);
                                $end_page = min($toplam_sayfa, $sayfa + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl(1) . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . (($i == $sayfa) ? 'active' : '') . '"><a class="page-link" href="' . buildPaginationUrl($i) . '">' . $i . '</a></li>';
                                }
                                
                                if ($end_page < $toplam_sayfa) {
                                    if ($end_page < $toplam_sayfa - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl($toplam_sayfa) . '">' . $toplam_sayfa . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?php echo ($sayfa >= $toplam_sayfa) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo ($sayfa < $toplam_sayfa) ? buildPaginationUrl($sayfa + 1) : '#'; ?>" aria-label="Sonraki">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Favorilere ekleme işlemi - AJAX ile 
    document.querySelectorAll('.btn-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const ilanId = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            // AJAX ile favori ekleme/çıkarma
            fetch('favori_ekle_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ilan_id=${ilanId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'add') {
                        this.classList.add('active');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        this.classList.remove('active');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                } else {
                    if (data.message === 'login_required') {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>