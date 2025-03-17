<?php
session_start();
require_once "db.php";
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
    <div class="row">
        <?php
        $stmt = $pdo->query("SELECT * FROM ilanlar ORDER BY eklenme_tarihi DESC");
        while ($ilan = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-md-4">';
            echo '<div class="card mb-4">';
            
            // Resim eklenmişse göster
            if (!empty($ilan["resim"])) {
                echo '<img src="uploads/' . htmlspecialchars($ilan["resim"]) . '" class="card-img-top" style="width: 100%; height: 200px; object-fit: cover;" alt="İlan Resmi">';
            }
            
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($ilan["baslik"]) . '</h5>';
            echo '<p class="card-text">' . htmlspecialchars($ilan["aciklama"]) . '</p>';
            echo '<p class="card-text"><strong>Fiyat:</strong> ' . number_format($ilan["fiyat"], 2) . ' TL</p>';
            echo '<p class="card-text"><strong>Adres:</strong> ' . htmlspecialchars($ilan["adres"]) . '</p>';
            echo '<p class="card-text"><strong>Oda Sayısı:</strong> ' . $ilan["oda_sayisi"] . '</p>';
            echo '<p class="card-text"><strong>Metrekare:</strong> ' . $ilan["metrekare"] . ' m²</p>';
            echo '<a href="ilan_detay.php?id=' . $ilan["id"] . '" class="btn btn-primary">Detayları Gör</a>';
            echo '</div></div></div>';
        }
        ?>
    </div>
</div>

</body>
</html>