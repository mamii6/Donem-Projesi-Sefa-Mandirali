<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];

// Gelen Mesajları Al
$gelen_mesajlar = $pdo->prepare("SELECT m.*, k.ad, k.soyad, i.baslik FROM mesajlar m 
                                JOIN kullanicilar k ON m.gonderen_id = k.id 
                                LEFT JOIN ilanlar i ON m.ilan_id = i.id
                                WHERE m.alici_id = ? ORDER BY m.gonderilme_tarihi DESC");
$gelen_mesajlar->execute([$kullanici_id]);

// Gönderilen Mesajları Al
$gonderilen_mesajlar = $pdo->prepare("SELECT m.*, k.ad, k.soyad, i.baslik FROM mesajlar m 
                                    JOIN kullanicilar k ON m.alici_id = k.id 
                                    LEFT JOIN ilanlar i ON m.ilan_id = i.id
                                    WHERE m.gonderen_id = ? ORDER BY m.gonderilme_tarihi DESC");
$gonderilen_mesajlar->execute([$kullanici_id]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar | Emlakçı</title>
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
    <h2>Mesajlarım</h2>
    <ul class="nav nav-tabs" id="mesajTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#gelen">Gelen Kutusu</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#gonderilen">Gönderilen Mesajlar</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="gelen">
            <ul class="list-group">
                <?php while ($mesaj = $gelen_mesajlar->fetch(PDO::FETCH_ASSOC)): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($mesaj["ad"] . " " . $mesaj["soyad"]); ?>:</strong> 
                        <?php echo htmlspecialchars(substr($mesaj["mesaj"], 0, 50)); ?>...
                        <small class="text-muted">(<?php echo $mesaj["gonderilme_tarihi"]; ?>)</small>
                        <?php if ($mesaj["ilan_id"]): ?>
                            <br><small><a href="ilan_detay.php?id=<?php echo $mesaj["ilan_id"]; ?>">İlgili İlan: <?php echo htmlspecialchars($mesaj["baslik"]); ?></a></small>
                        <?php endif; ?>
                        <a href="mesaj_detay.php?id=<?php echo $mesaj["id"]; ?>" class="btn btn-primary btn-sm float-end">Görüntüle</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="tab-pane fade" id="gonderilen">
            <ul class="list-group">
                <?php while ($mesaj = $gonderilen_mesajlar->fetch(PDO::FETCH_ASSOC)): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($mesaj["ad"] . " " . $mesaj["soyad"]); ?>:</strong> 
                        <?php echo htmlspecialchars(substr($mesaj["mesaj"], 0, 50)); ?>...
                        <small class="text-muted">(<?php echo $mesaj["gonderilme_tarihi"]; ?>)</small>
                        <?php if ($mesaj["ilan_id"]): ?>
                            <br><small><a href="ilan_detay.php?id=<?php echo $mesaj["ilan_id"]; ?>">İlgili İlan: <?php echo htmlspecialchars($mesaj["baslik"]); ?></a></small>
                        <?php endif; ?>
                        <a href="mesaj_detay.php?id=<?php echo $mesaj["id"]; ?>" class="btn btn-primary btn-sm float-end">Görüntüle</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>