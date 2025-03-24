<?php
require_once "inc/auth.php";
require_once "../db.php";

if (!isset($_GET["id"])) {
    die("İlan bulunamadı.");
}

$ilan_id = $_GET["id"];
$stmt = $pdo->prepare("SELECT i.*, k.ad FROM ilanlar i JOIN kullanicilar k ON i.kullanici_id = k.id WHERE i.id = ?");
$stmt->execute([$ilan_id]);
$ilan = $stmt->fetch();

if (!$ilan) {
    die("İlan bulunamadı.");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>İlan Detayı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>İlan Detayı</h2>
    <p><strong>Başlık:</strong> <?= $ilan["baslik"] ?></p>
    <p><strong>Açıklama:</strong> <?= $ilan["aciklama"] ?></p>
    <p><strong>Fiyat:</strong> <?= number_format($ilan["fiyat"], 2) ?> TL</p>
    <p><strong>Sahibi:</strong> <?= $ilan["ad"] ?></p>
    <p><strong>Durum:</strong> <?= ucfirst($ilan["durum"]) ?></p>

    <?php if (!empty($ilan["resim"])) : ?>
        <img src="../uploads/ilanlar/<?= $ilan["resim"] ?>" alt="İlan Resmi" class="img-fluid" style="max-width: 400px;">
    <?php endif; ?>

    <br><br>
    <a href="ilanlar.php" class="btn btn-secondary">Geri Dön</a>
</div>
</body>
</html>
