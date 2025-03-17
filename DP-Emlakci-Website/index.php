<?php
session_start();
require_once "db.php";

// Filtreleme için değişkenleri al
$min_fiyat = isset($_GET['min_fiyat']) ? $_GET['min_fiyat'] : '';
$max_fiyat = isset($_GET['max_fiyat']) ? $_GET['max_fiyat'] : '';
$oda_sayisi = isset($_GET['oda_sayisi']) ? $_GET['oda_sayisi'] : '';
$metrekare_min = isset($_GET['metrekare_min']) ? $_GET['metrekare_min'] : '';
$metrekare_max = isset($_GET['metrekare_max']) ? $_GET['metrekare_max'] : '';
$adres = isset($_GET['adres']) ? $_GET['adres'] : '';

$query = "SELECT * FROM ilanlar WHERE 1=1";
$params = [];

if (!empty($min_fiyat)) {
    $query .= " AND fiyat >= ?";
    $params[] = $min_fiyat;
}
if (!empty($max_fiyat)) {
    $query .= " AND fiyat <= ?";
    $params[] = $max_fiyat;
}
if (!empty($oda_sayisi)) {
    $query .= " AND oda_sayisi = ?";
    $params[] = $oda_sayisi;
}
if (!empty($metrekare_min)) {
    $query .= " AND metrekare >= ?";
    $params[] = $metrekare_min;
}
if (!empty($metrekare_max)) {
    $query .= " AND metrekare <= ?";
    $params[] = $metrekare_max;
}
if (!empty($adres)) {
    $query .= " AND adres LIKE ?";
    $params[] = "%$adres%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ilanlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anasayfa | Emlakçı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Emlakçı</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["kullanici_id"])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION["ad"]); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Emlak İlanları</h1>
    <form method="GET" class="row g-3">
        <div class="col-md-2">
            <input type="number" name="min_fiyat" class="form-control" placeholder="Min Fiyat" value="<?php echo $min_fiyat; ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_fiyat" class="form-control" placeholder="Max Fiyat" value="<?php echo $max_fiyat; ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="oda_sayisi" class="form-control" placeholder="Oda Sayısı" value="<?php echo $oda_sayisi; ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="metrekare_min" class="form-control" placeholder="Min m²" value="<?php echo $metrekare_min; ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="metrekare_max" class="form-control" placeholder="Max m²" value="<?php echo $metrekare_max; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="adres" class="form-control" placeholder="Adres" value="<?php echo $adres; ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filtrele</button>
        </div>
    </form>
    <div class="row mt-4">
        <?php foreach ($ilanlar as $ilan): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <?php if (!empty($ilan["resim"])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($ilan["resim"]); ?>" class="card-img-top" style="width: 100%; height: 200px; object-fit: cover;" alt="İlan Resmi">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"> <?php echo htmlspecialchars($ilan["baslik"]); ?> </h5>
                        <p class="card-text"> <?php echo htmlspecialchars($ilan["aciklama"]); ?> </p>
                        <p class="card-text"><strong>Fiyat:</strong> <?php echo number_format($ilan["fiyat"], 2); ?> TL</p>
                        <p class="card-text"><strong>Adres:</strong> <?php echo htmlspecialchars($ilan["adres"]); ?></p>
                        <p class="card-text"><strong>Oda Sayısı:</strong> <?php echo $ilan["oda_sayisi"]; ?></p>
                        <p class="card-text"><strong>Metrekare:</strong> <?php echo $ilan["metrekare"]; ?> m²</p>
                        <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary">Detayları Gör</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>