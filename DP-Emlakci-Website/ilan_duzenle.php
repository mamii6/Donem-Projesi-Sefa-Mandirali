<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: profil.php");
    exit;
}

$id = $_GET["id"];
$kullanici_id = $_SESSION["kullanici_id"];

$stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE id = ? AND kullanici_id = ?");
$stmt->execute([$id, $kullanici_id]);
$ilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) {
    header("Location: profil.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $baslik = $_POST["baslik"];
    $aciklama = $_POST["aciklama"];
    $fiyat = $_POST["fiyat"];
    $adres = $_POST["adres"];
    $oda_sayisi = $_POST["oda_sayisi"];
    $metrekare = $_POST["metrekare"];
    $resimAdi = $ilan["resim"];

    if (!empty($_FILES["resim"]["name"])) {
        $dosya = $_FILES["resim"];
        $uzanti = pathinfo($dosya["name"], PATHINFO_EXTENSION);
        $izinVerilenUzantilar = ["jpg", "jpeg", "png", "gif"];

        if (in_array(strtolower($uzanti), $izinVerilenUzantilar) && $dosya["size"] <= 4 * 1024 * 1024) {
            $resimAdi = time() . "_" . basename($dosya["name"]);
            $hedefYol = "uploads/" . $resimAdi;
            move_uploaded_file($dosya["tmp_name"], $hedefYol);
        } else {
            $message = "<div class='alert alert-danger'>Geçersiz dosya formatı veya dosya boyutu çok büyük!</div>";
        }
    }

    $stmt = $pdo->prepare("UPDATE ilanlar SET baslik = ?, aciklama = ?, fiyat = ?, adres = ?, oda_sayisi = ?, metrekare = ?, resim = ? WHERE id = ? AND kullanici_id = ?");
    if ($stmt->execute([$baslik, $aciklama, $fiyat, $adres, $oda_sayisi, $metrekare, $resimAdi, $id, $kullanici_id])) {
        $message = "<div class='alert alert-success'>İlan başarıyla güncellendi!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Güncelleme sırasında hata oluştu!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle | Emlakçı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2>İlanı Düzenle</h2>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Başlık</label>
            <input type="text" name="baslik" class="form-control" value="<?php echo htmlspecialchars($ilan['baslik']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="aciklama" class="form-control" required><?php echo htmlspecialchars($ilan['aciklama']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Fiyat (TL)</label>
            <input type="number" step="0.01" name="fiyat" class="form-control" value="<?php echo $ilan['fiyat']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Adres</label>
            <input type="text" name="adres" class="form-control" value="<?php echo htmlspecialchars($ilan['adres']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Oda Sayısı</label>
            <input type="number" name="oda_sayisi" class="form-control" value="<?php echo $ilan['oda_sayisi']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Metrekare</label>
            <input type="number" name="metrekare" class="form-control" value="<?php echo $ilan['metrekare']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mevcut Resim</label><br>
            <img src="uploads/<?php echo $ilan['resim']; ?>" class="img-fluid" style="max-width: 200px;" alt="İlan Resmi">
        </div>
        <div class="mb-3">
            <label class="form-label">Yeni Resim Yükle (Opsiyonel)</label>
            <input type="file" name="resim" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-success">Güncelle</button>
    </form>
</div>

</body>
</html>
