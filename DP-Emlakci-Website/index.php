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

    <!-- Arama ve Filtreleme Formu -->
    <form method="GET" action="index.php" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="arama" class="form-control" placeholder="Başlık veya açıklama" value="<?php echo isset($_GET['arama']) ? htmlspecialchars($_GET['arama']) : ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="min_fiyat" class="form-control" placeholder="Min Fiyat" value="<?php echo isset($_GET['min_fiyat']) ? htmlspecialchars($_GET['min_fiyat']) : ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="max_fiyat" class="form-control" placeholder="Max Fiyat" value="<?php echo isset($_GET['max_fiyat']) ? htmlspecialchars($_GET['max_fiyat']) : ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="oda_sayisi" class="form-control" placeholder="Oda Sayısı" value="<?php echo isset($_GET['oda_sayisi']) ? htmlspecialchars($_GET['oda_sayisi']) : ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="metrekare" class="form-control" placeholder="Metrekare" value="<?php echo isset($_GET['metrekare']) ? htmlspecialchars($_GET['metrekare']) : ''; ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </div>
        </div>
    </form>

    <div class="row">
        <?php
        $sql = "SELECT * FROM ilanlar WHERE 1=1";
        $params = [];

        if (!empty($_GET['arama'])) {
            $sql .= " AND (baslik LIKE ? OR aciklama LIKE ?)";
            $params[] = "%" . $_GET['arama'] . "%";
            $params[] = "%" . $_GET['arama'] . "%";
        }

        if (!empty($_GET['min_fiyat'])) {
            $sql .= " AND fiyat >= ?";
            $params[] = $_GET['min_fiyat'];
        }

        if (!empty($_GET['max_fiyat'])) {
            $sql .= " AND fiyat <= ?";
            $params[] = $_GET['max_fiyat'];
        }

        if (!empty($_GET['oda_sayisi'])) {
            $sql .= " AND oda_sayisi = ?";
            $params[] = $_GET['oda_sayisi'];
        }

        if (!empty($_GET['metrekare'])) {
            $sql .= " AND metrekare >= ?";
            $params[] = $_GET['metrekare'];
        }

        $sql .= " ORDER BY eklenme_tarihi DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($ilan = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="col-md-4">';
            echo '<div class="card mb-4">';
            
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
