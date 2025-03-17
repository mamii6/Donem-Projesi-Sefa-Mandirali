<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "<div class='alert alert-danger'>Mesaj bulunamadı!</div>";
    exit;
}

$mesaj_id = $_GET["id"];
$kullanici_id = $_SESSION["kullanici_id"];

$stmt = $pdo->prepare("SELECT m.*, g.ad AS gonderen_ad, g.soyad AS gonderen_soyad, a.ad AS alici_ad, a.soyad AS alici_soyad, i.baslik AS ilan_baslik
                        FROM mesajlar m
                        LEFT JOIN kullanicilar g ON m.gonderen_id = g.id
                        LEFT JOIN kullanicilar a ON m.alici_id = a.id
                        LEFT JOIN ilanlar i ON m.ilan_id = i.id
                        WHERE m.id = ? AND (m.gonderen_id = ? OR m.alici_id = ?)");
$stmt->execute([$mesaj_id, $kullanici_id, $kullanici_id]);
$mesaj = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesaj) {
    echo "<div class='alert alert-danger'>Bu mesaja erişim yetkiniz yok!</div>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesaj Detayı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Mesaj Detayı</h2>
    <p><strong>İlan:</strong> <?php echo htmlspecialchars($mesaj["ilan_baslik"] ?? "Genel Mesaj"); ?></p>
    <p><strong>Gönderen:</strong> <?php echo htmlspecialchars($mesaj["gonderen_ad"] . " " . $mesaj["gonderen_soyad"]); ?></p>
    <p><strong>Alıcı:</strong> <?php echo htmlspecialchars($mesaj["alici_ad"] . " " . $mesaj["alici_soyad"]); ?></p>
    <p><strong>Gönderilme Tarihi:</strong> <?php echo $mesaj["gonderilme_tarihi"]; ?></p>
    <p><strong>Mesaj:</strong></p>
    <div class="border p-3 mb-3 bg-light">
        <?php echo nl2br(htmlspecialchars($mesaj["mesaj"])); ?>
    </div>
    
    <h4>Yanıt Gönder</h4>
    <form action="mesaj_gonder.php" method="POST">
        <input type="hidden" name="alici_id" value="<?php echo $mesaj["gonderen_id"]; ?>">
        <input type="hidden" name="ilan_id" value="<?php echo $mesaj["ilan_id"]; ?>">
        <textarea name="mesaj" class="form-control" rows="4" required></textarea>
        <button type="submit" class="btn btn-primary mt-2">Gönder</button>
    </form>
    <a href="mesajlar.php" class="btn btn-secondary mt-3">Geri Dön</a>
</div>
</body>
</html>
