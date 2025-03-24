<?php

require_once "inc/auth.php"; // Admin girişi kontrolü
require_once "../db.php"; // Veritabanı bağlantısı

// Eğer admin değilse yönlendir
if (!isset($_SESSION["id"]) || $_SESSION["yetki"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

try {
    // 1️⃣ İlanın durumunu güncelleme
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["durum_guncelle"])) {
            $ilan_id = $_POST["ilan_id"];
            $yeni_durum = $_POST["durum"];

            // Durumu güncelle
            $stmt = $pdo->prepare("UPDATE ilanlar SET durum = ? WHERE id = ?");
            $stmt->execute([$yeni_durum, $ilan_id]);

            // Kullanıcıya bildirim ekle
            $stmt = $pdo->prepare("SELECT kullanici_id FROM ilanlar WHERE id = ?");
            $stmt->execute([$ilan_id]);
            $ilan = $stmt->fetch();

            if ($ilan) {
                $mesaj = ($yeni_durum == "onaylı") ? "İlanınız onaylandı!" : "İlanınız reddedildi.";
                $stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tarih) VALUES (?, ?, NOW())");
                $stmt->execute([$ilan["kullanici_id"], $mesaj]);
            }

            // Sayfayı yenile
            header("Location: ilanlar.php");
            exit;
        }

        // 2️⃣ İlan silme işlemi
        if (isset($_POST["ilan_sil"])) {
            $ilan_id = $_POST["ilan_id"];

            // İlanı ve resimlerini sil
            $stmt = $pdo->prepare("SELECT resim FROM ilan_resimler WHERE ilan_id = ?");
            $stmt->execute([$ilan_id]);
            $resimler = $stmt->fetchAll();

            foreach ($resimler as $resim) {
                $dosya = "../uploads/ilanlar/" . $resim["resim"];
                if (file_exists($dosya)) {
                    unlink($dosya);
                }
            }

            $pdo->prepare("DELETE FROM ilan_resimler WHERE ilan_id = ?")->execute([$ilan_id]);
            $pdo->prepare("DELETE FROM ilanlar WHERE id = ?")->execute([$ilan_id]);

            // Sayfayı yenile
            header("Location: ilanlar.php");
            exit;
        }
    }

    // 3️⃣ İlanları çek
    $stmt = $pdo->query("SELECT i.*, k.ad FROM ilanlar i JOIN kullanicilar k ON i.kullanici_id = k.id ORDER BY i.eklenme_tarihi DESC");
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
    <title>İlanlar Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>İlanlar Yönetimi</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Sahibi</th>
                <th>Fiyat</th>
                <th>Durum</th>
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
                    <span class="badge bg-<?= ($ilan["durum"] == "onaylı") ? 'success' : 'warning' ?>">
                        <?= ucfirst($ilan["durum"]) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="ilan_id" value="<?= $ilan["id"] ?>">
                        <input type="hidden" name="durum" value="onaylı">
                        <button type="submit" name="durum_guncelle" class="btn btn-success btn-sm">Onayla</button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="ilan_id" value="<?= $ilan["id"] ?>">
                        <input type="hidden" name="durum" value="reddedildi">
                        <button type="submit" name="durum_guncelle" class="btn btn-danger btn-sm">Reddet</button>
                    </form>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Bu ilanı silmek istediğinizden emin misiniz?');">
                        <input type="hidden" name="ilan_id" value="<?= $ilan["id"] ?>">
                        <button type="submit" name="ilan_sil" class="btn btn-dark btn-sm">Sil</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
