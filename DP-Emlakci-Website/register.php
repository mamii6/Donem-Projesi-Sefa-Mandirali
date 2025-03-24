<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST["ad"]);
    $soyad = trim($_POST["soyad"]);
    $email = trim($_POST["email"]);
    $telefon = trim($_POST["telefon"]);
    $dogum_tarihi = $_POST["dogum_tarihi"];
    $cinsiyet = $_POST["cinsiyet"];
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT);

    if (!empty($ad) && !empty($soyad) && !empty($email) && !empty($_POST["sifre"]) && !empty($telefon) && !empty($dogum_tarihi) && !empty($cinsiyet)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, soyad, email, telefon, dogum_tarihi, cinsiyet, sifre) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ad, $soyad, $email, $telefon, $dogum_tarihi, $cinsiyet, $sifre]);

            // Kullanıcının bilgilerini al
            $kullanici_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullanici_id]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "Kayıt başarılı! Hoş geldiniz, " . htmlspecialchars($kullanici["ad"]) . " " . htmlspecialchars($kullanici["soyad"]) . " (" . htmlspecialchars($kullanici["email"]) . ") - Telefon: " . htmlspecialchars($kullanici["telefon"]) . "<br>";
            echo "<a href='login.php'>Giriş yap</a>";
        } catch (PDOException $e) {
            echo "Hata: " . $e->getMessage();
        }
    } else {
        echo "Lütfen tüm alanları doldurun!";
    }
}
?>

<form method="post">
    <input type="text" name="ad" placeholder="Adınız" required><br>
    <input type="text" name="soyad" placeholder="Soyadınız" required><br>
    <input type="email" name="email" placeholder="E-posta" required><br>
    <input type="text" name="telefon" placeholder="Telefon" required><br>
    <input type="date" name="dogum_tarihi" required><br>
    <select name="cinsiyet" required>
        <option value="">Cinsiyet Seçiniz</option>
        <option value="Erkek">Erkek</option>
        <option value="Kadın">Kadın</option>
    </select><br>
    <input type="password" name="sifre" placeholder="Şifre" required><br>
    <button type="submit">Kayıt Ol</button>
</form>
