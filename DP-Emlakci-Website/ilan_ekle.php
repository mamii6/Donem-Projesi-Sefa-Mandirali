<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
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
    $kullanici_id = $_SESSION["kullanici_id"];
    $resimAdi = null;

    // Resim yükleme işlemi
    if (!empty($_FILES["resim"]["name"])) {
        $dosya = $_FILES["resim"];
        $uzanti = pathinfo($dosya["name"], PATHINFO_EXTENSION);
        $izinVerilenUzantilar = ["jpg", "jpeg", "png", "gif"];

        if (!in_array(strtolower($uzanti), $izinVerilenUzantilar)) {
            $message = "<div class='alert alert-danger'>Sadece JPG, JPEG, PNG veya GIF formatında resim yükleyebilirsiniz!</div>";
        } elseif ($dosya["size"] > 4 * 1024 * 1024) { // 2MB sınırı
            $message = "<div class='alert alert-danger'>Dosya boyutu 2MB'den büyük olamaz!</div>";
        } else {
            $resimAdi = time() . "_" . basename($dosya["name"]);
            $hedefKlasor = "uploads/ilanlar/";
            if (!is_dir($hedefKlasor)) 
            {
                mkdir($hedefKlasor, 0777, true);
            }
            $hedefYol = $hedefKlasor . $dosyaAdi;


            if (move_uploaded_file($dosya["tmp_name"], $hedefYol)) {
                $stmt = $pdo->prepare("INSERT INTO ilanlar (baslik, aciklama, fiyat, adres, oda_sayisi, metrekare, kullanici_id, resim) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$baslik, $aciklama, $fiyat, $adres, $oda_sayisi, $metrekare, $kullanici_id, $resimAdi])) {
                    $message = "<div class='alert alert-success'>İlan başarıyla eklendi!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>İlan eklenirken hata oluştu!</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Resim yüklenirken hata oluştu!</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Ekle | Emlakçı</title>
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
    <h2>Yeni İlan Ekle</h2>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Başlık</label>
            <input type="text" name="baslik" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="aciklama" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Fiyat (TL)</label>
            <input type="number" step="0.01" name="fiyat" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Adres</label>
            <input type="text" name="adres" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Oda Sayısı</label>
            <input type="number" name="oda_sayisi" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Metrekare</label>
            <input type="number" name="metrekare" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Resim Yükle</label>
            <input type="file" name="resim" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-success">İlanı Ekle</button>
    </form>
</div>

</body>
</html>  
