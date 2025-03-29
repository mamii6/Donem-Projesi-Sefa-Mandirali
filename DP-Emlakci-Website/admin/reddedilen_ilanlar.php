<?php
require_once "inc/auth.php"; // Admin girişi kontrolü
require_once "../db.php"; // Veritabanı bağlantısı

// Admin değilse yönlendir
if (!isset($_SESSION["kullanici_id"]) || $_SESSION["yetki"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

try {
    // Reddedilen ilanları çek
    $stmt = $pdo->query("SELECT i.*, k.ad FROM ilanlar i JOIN kullanicilar k ON i.kullanici_id = k.id WHERE i.durum = 'reddedildi' ORDER BY i.eklenme_tarihi DESC");
    $ilanlar = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reddedilen İlanlar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Reddedilen İlanlar</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Sahibi</th>
                <th>Fiyat</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ilanlar as $ilan) : ?>
            <tr>
                <td><?= htmlspecialchars($ilan["baslik"]) ?></td>
                <td><?= htmlspecialchars($ilan["ad"]) ?></td>
                <td><?= number_format($ilan["fiyat"], 2) ?> TL</td>
                <td><?= date("d-m-Y", strtotime($ilan["eklenme_tarihi"])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
