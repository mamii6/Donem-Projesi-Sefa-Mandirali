<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

// Kullanıcının ilanlarını çek
$stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE kullanici_id = ? ORDER BY eklenme_tarihi DESC");
$stmt->execute([$kullanici_id]);
$ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim | Emlakçı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Emlakçı</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Anasayfa</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 text-center">
            <img src="uploads/profiller/<?= htmlspecialchars($kullanici['profil_resmi']) ?>" class="rounded-circle border border-2 border-primary" alt="Profil Resmi" width="150" height="150">
                <h3 class="mt-2"><?= htmlspecialchars($kullanici["ad"] . " " . $kullanici["soyad"]) ?></h3>
                <p>Email: <?= htmlspecialchars($kullanici["email"]) ?></p>
                <p>Telefon: <?= htmlspecialchars($kullanici["telefon"]) ?></p>
                <p>Doğum Tarihi: <?= htmlspecialchars($kullanici["dogum_tarihi"]) ?></p>
                <p>Cinsiyet: <?= htmlspecialchars($kullanici["cinsiyet"]) ?></p>
                <a href="profil_duzenle.php" class="btn btn-warning">Profili Düzenle</a>
            </div>
        </div>

        <div class="col-md-8">
            <h2>İlanlarım</h2>
            <div class="row">
                <?php foreach ($ilanlar as $ilan): ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <img src="uploads/ilanlar/<?= htmlspecialchars($ilan["resim"]) ?>" class="card-img-top" alt="İlan Resmi">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($ilan["baslik"]) ?></h5>
                                <p class="card-text"><strong>Açıklama:</strong> <?= htmlspecialchars($ilan["aciklama"]) ?></p>
                                <p class="card-text"><strong>Fiyat:</strong> <?= number_format($ilan["fiyat"], 2) ?> TL</p>
                                <p class="card-text"><strong>Adres:</strong> <?= htmlspecialchars($ilan["adres"]) ?></p>
                                <p class="card-text"><strong>Oda Sayısı:</strong> <?= $ilan["oda_sayisi"] ?></p>
                                <p class="card-text"><strong>Metrekare:</strong> <?= $ilan["metrekare"] ?> m²</p>
                                <a href="ilan_duzenle.php?id=<?= $ilan["id"] ?>" class="btn btn-warning">Düzenle</a>
                                <a href="ilan_sil.php?id=<?= $ilan["id"] ?>" class="btn btn-danger" onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?');">Sil</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>