<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$stmt = $pdo->prepare("SELECT ilanlar.* FROM favoriler 
                       JOIN ilanlar ON favoriler.ilan_id = ilanlar.id 
                       WHERE favoriler.kullanici_id = ? 
                       ORDER BY favoriler.id DESC");
$stmt->execute([$kullanici_id]);
$favoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header'ı dahil et
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Favori İlanlarım</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="profil.php">Profilim</a> / Favori İlanlarım
        </div>
    </div>
</div>

<!-- Favoriler Section -->
<section class="section-padding">
    <div class="container">
        <?php if (count($favoriler) > 0): ?>
            <div class="row properties-grid">
                <?php foreach ($favoriler as $ilan): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="property-card">
                            <div class="property-img" style="height: 200px;">
                                <?php if (!empty($ilan["resim"])): ?>
                                    <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>" style="height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <img src="img/property-placeholder.jpg" alt="Emlak Görseli" style="height: 100%; object-fit: cover;">
                                <?php endif; ?>
                                <div class="property-tag">Favori</div>
                                <div class="property-price"><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> ₺</div>
                                
                                <!-- Favorilerden çıkar butonu -->
                                <div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                                    <form action="favori_sil.php" method="POST" style="display: inline;" onsubmit="return confirm('Bu ilanı favorilerinizden çıkarmak istediğinize emin misiniz?');">
                                        <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                                        <button type="submit" style="background-color: white; width: 35px; height: 35px; border-radius: 50%; border: none; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                            <i class="fas fa-heart" style="color: #ff5a5f;"></i>
                                        </button>
                                    </form>
                                </div>
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
                                <div class="property-actions mt-3 d-flex gap-2">
                                    <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary btn-sm flex-fill">Detayları Gör</a>
                                    <form action="favori_sil.php" method="POST" class="flex-fill" onsubmit="return confirm('Bu ilanı favorilerinizden çıkarmak istediğinize emin misiniz?');">
                                        <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Favorilerden Çıkar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <div class="text-center py-5">
                    <i class="far fa-heart fa-3x mb-3"></i>
                    <h3>Henüz Favori İlanınız Yok</h3>
                    <p class="mb-4">Emlak ilanlarını keşfedin ve beğendiklerinizi favorilerinize ekleyin.</p>
                    <a href="emlaklar.php" class="btn btn-primary">İlanları Keşfet</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Öneri Bölümü - Favori Yoksa veya Az Sayıda Varsa -->
<?php if (count($favoriler) < 3): ?>
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Sizin İçin Öneriler</h2>
            <p>Favori tercihlerinize göre önerdiğimiz emlak ilanları.</p>
        </div>
        
        <?php
        // Önerileri getir - kullanıcının ilgilendiği türde veya bölgedeki ilanlar
        $oneri_sorgu = "SELECT * FROM ilanlar WHERE durum = 'onaylı' AND id NOT IN (
                            SELECT ilan_id FROM favoriler WHERE kullanici_id = ?
                        ) ORDER BY RAND() LIMIT 3";
        $oneri_stmt = $pdo->prepare($oneri_sorgu);
        $oneri_stmt->execute([$kullanici_id]);
        $oneriler = $oneri_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($oneriler) > 0):
        ?>
        <div class="row properties-grid">
            <?php foreach ($oneriler as $ilan): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card">
                        <div class="property-img" style="height: 200px;">
                            <?php if (!empty($ilan["resim"])): ?>
                                <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" alt="<?php echo htmlspecialchars($ilan["baslik"]); ?>" style="height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <img src="img/property-placeholder.jpg" alt="Emlak Görseli" style="height: 100%; object-fit: cover;">
                            <?php endif; ?>
                            <div class="property-tag">Öneri</div>
                            <div class="property-price"><?php echo number_format($ilan["fiyat"], 0, ',', '.'); ?> ₺</div>
                            
                            <!-- Favorilere Ekle Butonu -->
                            <div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                                <form action="favori_ekle.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
                                    <button type="submit" style="background-color: white; width: 35px; height: 35px; border-radius: 50%; border: none; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                        <i class="far fa-heart" style="color: #777;"></i>
                                    </button>
                                </form>
                            </div>
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
                            </div>
                            <div class="mt-3">
                                <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary btn-sm w-100">Detayları Gör</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convert form buttons to AJAX calls
    document.querySelectorAll('.property-img form button, .property-actions form button').forEach(button => {
        button.closest('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const ilanId = this.querySelector('input[name="ilan_id"]').value;
            const cardElement = this.closest('.col-lg-4');
            
            // AJAX ile favori silme
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
                    // If removed successfully, fade out and remove the card
                    if (data.action === 'remove') {
                        cardElement.style.transition = 'opacity 0.5s';
                        cardElement.style.opacity = '0';
                        setTimeout(() => {
                            cardElement.remove();
                            
                            // Check if there are any favorites left
                            const remainingCards = document.querySelectorAll('.properties-grid .col-lg-4');
                            if (remainingCards.length === 0) {
                                // Reload page to show "no favorites" message
                                window.location.reload();
                            }
                        }, 500);
                    }
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Önerilen ilanlar için favori ekleme
    document.querySelectorAll('.property-img form button i.far.fa-heart').forEach(icon => {
        icon.closest('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const ilanId = this.querySelector('input[name="ilan_id"]').value;
            const button = this.querySelector('button');
            const icon = button.querySelector('i');
            
            // AJAX ile favori ekleme
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
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        icon.style.color = '#ff5a5f';
                    }
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>