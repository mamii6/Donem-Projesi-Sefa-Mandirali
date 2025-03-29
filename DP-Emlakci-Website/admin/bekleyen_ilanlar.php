<?php
require_once "inc/auth.php";
require_once "../db.php";

// Admin değilse yönlendir
if (!isset($_SESSION["kullanici_id"]) || $_SESSION["yetki"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// Bekleyen ilanları çek
$stmt = $pdo->query("SELECT i.*, k.ad FROM ilanlar i 
    JOIN kullanicilar k ON i.kullanici_id = k.id 
    WHERE i.durum = 'beklemede' 
    ORDER BY i.eklenme_tarihi DESC");
$ilanlar = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ilan_id"])) {
    $ilan_id = $_POST["ilan_id"];
    $yeni_durum = $_POST["durum"];
    
    $pdo->prepare("UPDATE ilanlar SET durum = ? WHERE id = ?")
        ->execute([$yeni_durum, $ilan_id]);

    header("Location: bekleyen_ilanlar.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bekleyen İlanlar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Bekleyen İlanlar</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Sahibi</th>
                <th>Fiyat</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ilanlar as $ilan) : ?>
            <tr>
                <td><?= htmlspecialchars($ilan["baslik"]) ?></td>
                <td><?= htmlspecialchars($ilan["ad"]) ?></td>
                <td><?= number_format($ilan["fiyat"], 2) ?> TL</td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="ilan_id" value="<?= $ilan["id"] ?>">
                        <input type="hidden" name="durum" value="onaylı">
                        <button type="submit" class="btn btn-success btn-sm">Onayla</button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="ilan_id" value="<?= $ilan["id"] ?>">
                        <input type="hidden" name="durum" value="reddedildi">
                        <button type="submit" class="btn btn-danger btn-sm">Reddet</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
