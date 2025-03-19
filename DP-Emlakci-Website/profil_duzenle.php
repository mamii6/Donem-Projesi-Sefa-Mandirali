<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$stmt = $pdo->prepare("SELECT ad, soyad, email, telefon, dogum_tarihi, cinsiyet, profil_resmi FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["ad"], $_POST["soyad"], $_POST["telefon"], $_POST["dogum_tarihi"], $_POST["cinsiyet"])) {
        $ad = $_POST["ad"];
        $soyad = $_POST["soyad"];
        $telefon = $_POST["telefon"];
        $dogum_tarihi = $_POST["dogum_tarihi"];
        $cinsiyet = $_POST["cinsiyet"];
        
        $stmt = $pdo->prepare("UPDATE kullanicilar SET ad = ?, soyad = ?, telefon = ?, dogum_tarihi = ?, cinsiyet = ? WHERE id = ?");
        $stmt->execute([$ad, $soyad, $telefon, $dogum_tarihi, $cinsiyet, $kullanici_id]);
        header("Location: profil.php?guncelle=basarili");
        exit;
    }
    
    if (isset($_POST["eski_sifre"], $_POST["yeni_sifre"], $_POST["yeni_sifre_tekrar"])) {
        $eski_sifre = $_POST["eski_sifre"];
        $yeni_sifre = $_POST["yeni_sifre"];
        $yeni_sifre_tekrar = $_POST["yeni_sifre_tekrar"];

        $stmt = $pdo->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
        $stmt->execute([$kullanici_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($eski_sifre, $row["sifre"])) {
            if ($yeni_sifre === $yeni_sifre_tekrar) {
                $hash_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
                $stmt->execute([$hash_sifre, $kullanici_id]);
                header("Location: profil.php?sifre=degistirildi");
                exit;
            } else {
                $hata = "Yeni şifreler uyuşmuyor!";
            }
        } else {
            $hata = "Eski şifre yanlış!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Bilgileri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profil-resmi {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Profil Bilgileri</h2>
        
        <div class="text-center">
            <img src="uploads/<?php echo htmlspecialchars($kullanici['profil_resmi'] ?? 'default.png'); ?>" class="profil-resmi" alt="Profil Resmi">
        </div>
        
        <form action="profil_resmi_yukle.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="profil_resmi" class="form-control">
            <button type="submit" class="btn btn-primary mt-2">Resmi Yükle</button>
        </form>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Ad</label>
                <input type="text" name="ad" class="form-control" value="<?php echo htmlspecialchars($kullanici['ad']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Soyad</label>
                <input type="text" name="soyad" class="form-control" value="<?php echo htmlspecialchars($kullanici['soyad']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefon</label>
                <input type="text" name="telefon" class="form-control" value="<?php echo htmlspecialchars($kullanici['telefon']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Doğum Tarihi</label>
                <input type="date" name="dogum_tarihi" class="form-control" value="<?php echo $kullanici['dogum_tarihi']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Cinsiyet</label>
                <select name="cinsiyet" class="form-control">
                    <option value="Erkek" <?php echo ($kullanici['cinsiyet'] == 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
                    <option value="Kadın" <?php echo ($kullanici['cinsiyet'] == 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Bilgileri Güncelle</button>
        </form>

        <h2 class="mt-5">Şifre Değiştir</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Eski Şifre</label>
                <input type="password" name="eski_sifre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre</label>
                <input type="password" name="yeni_sifre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre (Tekrar)</label>
                <input type="password" name="yeni_sifre_tekrar" class="form-control" required>
            </div>
            <?php if (isset($hata)) { echo "<p class='text-danger'>$hata</p>"; } ?>
            <button type="submit" class="btn btn-warning">Şifreyi Güncelle</button>
        </form>
    </div>
</body>
</html>