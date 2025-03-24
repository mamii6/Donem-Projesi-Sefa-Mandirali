<?php

require_once "inc/auth.php";
require_once "../db.php";

if (!isset($_GET["id"])) {
    die("İlan bulunamadı.");
}

$ilan_id = $_GET["id"];
$stmt = $pdo->prepare("SELECT * FROM ilanlar WHERE id = ?");
$stmt->execute([$ilan_id]);
$ilan = $stmt->fetch();

if (!$ilan) {
    die("İlan bulunamadı.");
}

if (isset($_POST["guncelle"])) {
    $baslik = $_POST["baslik"];
    $aciklama = $_POST["aciklama"];
    $fiyat = $_POST["fiyat"];

    $stmt = $pdo->prepare("UPDATE ilanlar SET baslik = ?, aciklama = ?, fiyat = ? WHERE id = ?");
    $stmt->execute([$baslik, $aciklama, $fiyat, $ilan_id]);

    header("Location: ilanlar.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>İlan Düzenle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>İlan Düzenle</h2>
    <form method="POST">
        <label>Başlık:</label>
        <input type="text" name="baslik" class="form-control" value="<?= $ilan["baslik"] ?>" required>

        <label>Açıklama:</label>
        <textarea name="aciklama" class="form-control" required><?= $ilan["aciklama"] ?></textarea>

        <label>Fiyat:</label>
        <input type="number" name="fiyat" class="form-control" value="<?= $ilan["fiyat"] ?>" required>

        <br>
        <button type="submit" name="guncelle" class="btn btn-success">Güncelle</button>
        <a href="ilanlar.php" class="btn btn-secondary">Geri Dön</a>
    </form>
</div>
</body>
</html>
