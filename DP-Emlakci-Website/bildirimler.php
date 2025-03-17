<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];

// Bildirimleri çek
$stmt = $pdo->prepare("SELECT * FROM bildirimler WHERE kullanici_id = ? ORDER BY tarih DESC");
$stmt->execute([$kullanici_id]);
$bildirimler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Okundu olarak işaretle
$pdo->prepare("UPDATE bildirimler SET goruldu = 1 WHERE kullanici_id = ?")->execute([$kullanici_id]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bildirimler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>📢 Bildirimler</h2>
    <ul class="list-group">
        <?php if (empty($bildirimler)): ?>
            <li class="list-group-item text-muted">Henüz bildirim yok.</li>
        <?php else: ?>
            <?php foreach ($bildirimler as $bildirim): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($bildirim["mesaj"]); ?> 
                    <small class="text-muted">(<?php echo $bildirim["tarih"]; ?>)</small>
                    
                    <!-- Bildirim türüne göre yönlendirme -->
                    <?php if ($bildirim["tur"] === 'mesaj'): ?>
                        <a href="mesajlar.php" class="btn btn-sm btn-primary float-end">Görüntüle</a>
                    <?php elseif ($bildirim["tur"] === 'yorum'): ?>
                        <a href="ilan_detay.php?id=<?php echo $bildirim["ilgili_id"]; ?>" class="btn btn-sm btn-primary float-end">Görüntüle</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
