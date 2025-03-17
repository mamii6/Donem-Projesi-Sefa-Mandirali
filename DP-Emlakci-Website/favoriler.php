<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$stmt = $pdo->prepare("SELECT ilanlar.* FROM favoriler 
                       JOIN ilanlar ON favoriler.ilan_id = ilanlar.id 
                       WHERE favoriler.kullanici_id = ?");
$stmt->execute([$kullanici_id]);
$favoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim | Emlakçı</title>
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
    <h2>Favori İlanlarım</h2>
    <div class="row">
        <?php if (count($favoriler) > 0): ?>
            <?php foreach ($favoriler as $ilan): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="uploads/<?php echo $ilan['resim']; ?>" class="card-img-top" alt="İlan Resmi">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($ilan["baslik"]); ?></h5>
                            <p class="card-text"><strong>Fiyat:</strong> <?php echo number_format($ilan["fiyat"], 2); ?> TL</p>
                            <p class="card-text"><strong>Adres:</strong> <?php echo htmlspecialchars($ilan["adres"]); ?></p>
                            <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary">Detayları Gör</a>
                            <form method="POST" action="favori_sil.php" class="mt-2">
                                <input type="hidden" name="ilan_id" value="<?php echo $ilan['id']; ?>">
                                <button type="submit" class="btn btn-danger">❌ Favorilerden Çıkar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Henüz favorilere eklediğiniz bir ilan bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
