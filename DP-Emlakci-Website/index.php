<?php
session_start();
require_once "db.php";

// Filtreleme için değişkenleri al
$min_fiyat = $_GET['min_fiyat'] ?? '';
$max_fiyat = $_GET['max_fiyat'] ?? '';
$oda_sayisi = $_GET['oda_sayisi'] ?? '';
$metrekare_min = $_GET['metrekare_min'] ?? '';
$metrekare_max = $_GET['metrekare_max'] ?? '';
$adres = $_GET['adres'] ?? '';

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

// Kullanıcı giriş yaptıysa bildirimleri al
$bildirimler = [];
$okunmamis_bildirim_sayisi = 0;
if (isset($_SESSION["kullanici_id"])) {
    $kullanici_id = $_SESSION["kullanici_id"];
    $bildirim_stmt = $pdo->prepare("SELECT * FROM bildirimler WHERE kullanici_id = ? ORDER BY tarih DESC");
    $bildirim_stmt->execute([$kullanici_id]);
    $bildirimler = $bildirim_stmt->fetchAll(PDO::FETCH_ASSOC);
    $okunmamis_bildirim_sayisi = count(array_filter($bildirimler, fn($b) => $b['goruldu'] == 0));
}
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
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">Emlakçı</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["kullanici_id"])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="bildirimDropdown" role="button" data-bs-toggle="dropdown">
                            📩 Bildirimler 
                            <?php if ($okunmamis_bildirim_sayisi > 0): ?>
                                <span class="badge bg-danger"><?php echo $okunmamis_bildirim_sayisi; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (empty($bildirimler)): ?>
                                <li><a class="dropdown-item text-muted">Bildirim yok</a></li>
                            <?php else: ?>
                                <?php foreach ($bildirimler as $bildirim): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $bildirim['goruldu'] ? '' : 'fw-bold'; ?>" href="bildirim_detay.php?id=<?php echo $bildirim['id']; ?>">
                                            <?php echo htmlspecialchars($bildirim["mesaj"]); ?> - <?php echo date("H:i", strtotime($bildirim["tarih"])); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION["ad"]); ?></a>
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
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-2"><input type="number" name="min_fiyat" class="form-control" placeholder="Min Fiyat" value="<?php echo htmlspecialchars($min_fiyat); ?>"></div>
            <div class="col-md-2"><input type="number" name="max_fiyat" class="form-control" placeholder="Max Fiyat" value="<?php echo htmlspecialchars($max_fiyat); ?>"></div>
            <div class="col-md-2"><input type="number" name="oda_sayisi" class="form-control" placeholder="Oda Sayısı" value="<?php echo htmlspecialchars($oda_sayisi); ?>"></div>
            <div class="col-md-2"><input type="number" name="metrekare_min" class="form-control" placeholder="Min m²" value="<?php echo htmlspecialchars($metrekare_min); ?>"></div>
            <div class="col-md-2"><input type="number" name="metrekare_max" class="form-control" placeholder="Max m²" value="<?php echo htmlspecialchars($metrekare_max); ?>"></div>
            <div class="col-md-2"><input type="text" name="adres" class="form-control" placeholder="Adres" value="<?php echo htmlspecialchars($adres); ?>"></div>
            <div class="col-md-2 mt-2"><button type="submit" class="btn btn-primary w-100">Filtrele</button></div>
        </div>
    </form>
    <div class="row mt-4">
        <?php foreach ($ilanlar as $ilan): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <?php if (!empty($ilan["resim"])): ?>
                        <img src="uploads/ilanlar/<?php echo htmlspecialchars($ilan["resim"]); ?>" class="card-img-top" alt="İlan Resmi">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"> <?php echo htmlspecialchars($ilan["baslik"]); ?> </h5>
                        <p class="card-text"><strong>Fiyat:</strong> <?php echo number_format($ilan["fiyat"], 2); ?> TL</p>
                        <p class="card-text"><strong>Oda Sayısı:</strong> <?php echo $ilan["oda_sayisi"]; ?></p>
                        <p class="card-text"><strong>Metrekare:</strong> <?php echo $ilan["metrekare"]; ?> m²</p>
                        <a href="ilan_detay.php?id=<?php echo $ilan["id"]; ?>" class="btn btn-primary">Detayları Gör</a>
                        <?php
    // Kullanıcının favoriye ekleyip eklemediğini kontrol et
    $favori_durum = false;
    if (isset($_SESSION["kullanici_id"])) {
        $favori_sorgu = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
        $favori_sorgu->execute([$_SESSION["kullanici_id"], $ilan["id"]]);
        $favori_durum = $favori_sorgu->fetchColumn() > 0;
    }
?>

<form action="favori_ekle.php" method="POST" style="display: inline;">
    <input type="hidden" name="ilan_id" value="<?php echo $ilan["id"]; ?>">
    <button type="submit" class="btn <?php echo $favori_durum ? 'btn-danger' : 'btn-outline-danger'; ?>">
        <?php echo $favori_durum ? '❤️' : '🤍'; ?>
    </button>
</form>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>