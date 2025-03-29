<?php
require_once "db.php";
session_start();

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "<div class='alert alert-danger'>İlan bulunamadı!</div>";
    exit;
}

$ilan_id = $_GET["id"];

$stmt = $pdo->prepare("SELECT i.*, k.ad, k.soyad, k.telefon 
                       FROM ilanlar i 
                       LEFT JOIN kullanicilar k ON i.kullanici_id = k.id 
                       WHERE i.id = ?");
$stmt->execute([$ilan_id]);

$ilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) {
    die("<div class='alert alert-danger'>İlan bulunamadı veya hatalı sorgu!</div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['kullanici_id'])) {
    $mesaj = trim($_POST['mesaj']);
    $gonderen_id = $_SESSION['kullanici_id'];
    $alici_id = $ilan['kullanici_id'];

    if (!empty($mesaj)) {
        $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, ilan_id, mesaj) VALUES (?, ?, ?, ?)");
        $stmt->execute([$gonderen_id, $alici_id, $ilan_id, $mesaj]);
        
        // Bildirim ekleme
        $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tur, ilgili_id, goruldu, tarih) 
                                        VALUES (?, ?, 'mesaj', ?, 0, NOW())");
        $bildirim_stmt->execute([$alici_id, "Yeni bir mesajınız var!", $ilan_id]);
        
        echo "<div class='alert alert-success'>Mesaj gönderildi.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ilan["baslik"]); ?> | Emlakçı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Emlakçı</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Anasayfa</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($ilan["baslik"]); ?></h2>
    <p class="text-muted">Eklenme Tarihi: <?php echo htmlspecialchars($ilan["eklenme_tarihi"]); ?></p>
    <p><strong>Açıklama:</strong> <?php echo nl2br(htmlspecialchars($ilan["aciklama"])); ?></p>
    <p><strong>Fiyat:</strong> <?php echo number_format($ilan["fiyat"], 2); ?> TL</p>
    <p><strong>Adres:</strong> <?php echo htmlspecialchars($ilan["adres"]); ?></p>
    <p><strong>Oda Sayısı:</strong> <?php echo $ilan["oda_sayisi"]; ?> Oda</p>
    <p><strong>Metrekare:</strong> <?php echo $ilan["metrekare"]; ?> m²</p>

    <h4>İlan Sahibi</h4>
    <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($ilan["ad"] . " " . $ilan["soyad"]); ?></p>
    <p><strong>Telefon:</strong> <a href="tel:<?php echo htmlspecialchars($ilan["telefon"]); ?>"><?php echo htmlspecialchars($ilan["telefon"]); ?></a></p>
    
    <?php if (!empty($ilan["resim"])) : ?>
    <img src="uploads/ilanlar/<?= $ilan["resim"] ?>" alt="İlan Resmi" class="img-fluid" style="max-width: 400px;">
<?php endif; ?>



    <?php if (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] != $ilan['kullanici_id']): ?>
        <h3>İlan Sahibine Mesaj Gönder</h3>
        <form method="post">
            <div class="mb-3">
                <textarea name="mesaj" class="form-control" rows="4" placeholder="Mesajınızı yazın..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gönder</button>
        </form>
    <?php endif; ?>
    
    <h3>Yorumlar</h3>
<?php
$yorum_stmt = $pdo->prepare("SELECT y.*, k.ad FROM yorumlar y 
                             JOIN kullanicilar k ON y.kullanici_id = k.id 
                             WHERE y.ilan_id = ? ORDER BY y.tarih DESC");
$yorum_stmt->execute([$ilan_id]);
$yorumlar = $yorum_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($yorumlar)): ?>
    <p>Henüz yorum yok.</p>
<?php else: ?>
    <?php foreach ($yorumlar as $yorum): ?>
        <div class="border p-3 mb-2">
            <strong><?php echo htmlspecialchars($yorum["ad"]); ?>:</strong>
            <p><?php echo nl2br(htmlspecialchars($yorum["yorum"])); ?></p>
            <p>Puan: <?php echo str_repeat("⭐", $yorum["puan"]); ?></p>
            <small><?php echo date("d.m.Y H:i", strtotime($yorum["tarih"])); ?></small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($_SESSION["kullanici_id"])): ?>
    <h3>Yorum Yap</h3>
    <form action="yorum_ekle.php" method="POST">
        <input type="hidden" name="ilan_id" value="<?php echo $ilan_id; ?>">
        <div class="mb-3">
            <label for="yorum" class="form-label">Yorumunuz:</label>
            <textarea name="yorum" id="yorum" rows="3" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="puan" class="form-label">Puan:</label>
            <select name="puan" id="puan" class="form-control" required>
                <option value="1">1 ⭐</option>
                <option value="2">2 ⭐⭐</option>
                <option value="3">3 ⭐⭐⭐</option>
                <option value="4">4 ⭐⭐⭐⭐</option>
                <option value="5">5 ⭐⭐⭐⭐⭐</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Yorumu Gönder</button>
    </form>
<?php else: ?>
    <p>Yorum yapmak için <a href="login.php">giriş yapmalısınız</a>.</p>
<?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-3">Geri Dön</a>
</div>

</body>
</html>