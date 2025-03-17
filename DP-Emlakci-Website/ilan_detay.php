<?php
require_once "db.php";


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

// Debug için ekleyelim:
if (!$ilan) {
    die("<div class='alert alert-danger'>İlan bulunamadı veya hatalı sorgu!</div>");
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
    <p class="text-muted"><?php echo htmlspecialchars($ilan["eklenme_tarihi"]); ?></p>
    <p><strong>Açıklama:</strong> <?php echo nl2br(htmlspecialchars($ilan["aciklama"])); ?></p>
    <p><strong>Fiyat:</strong> <?php echo number_format($ilan["fiyat"], 2); ?> TL</p>
    <p><strong>Adres:</strong> <?php echo htmlspecialchars($ilan["adres"]); ?></p>
    <p><strong>Oda Sayısı:</strong> <?php echo $ilan["oda_sayisi"]; ?> Oda</p>
    <p><strong>Metrekare:</strong> <?php echo $ilan["metrekare"]; ?> m²</p>

    <h4>İlan Sahibi</h4>
    
    <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($ilan["ad"] . " " . $ilan["soyad"]); ?></p>
    <p><strong>Telefon:</strong> <a href="tel:<?php echo $ilan["telefon"]; ?>"><?php echo $ilan["telefon"]; ?></a></p>
    <?php if (!empty($ilan["resim"])): ?>
    <img src="uploads/<?php echo htmlspecialchars($ilan["resim"]); ?>" class="img-fluid">
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">Geri Dön</a>
</div>

</body>
</html>
