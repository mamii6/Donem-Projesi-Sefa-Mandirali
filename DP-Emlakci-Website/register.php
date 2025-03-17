<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST["ad"]);
    $email = trim($_POST["email"]);
    $telefon = trim($_POST["telefon"]);
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT);

    if (!empty($ad) && !empty($email) && !empty($_POST["sifre"]) && !empty($telefon)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, email, telefon, sifre) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ad, $email, $telefon, $sifre]);

            // Kullanıcının bilgilerini al
            $kullanici_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
            $stmt->execute([$kullanici_id]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "Kayıt başarılı! Hoş geldiniz, " . htmlspecialchars($kullanici["ad"]) . " (" . htmlspecialchars($kullanici["email"]) . ") - Telefon: " . htmlspecialchars($kullanici["telefon"]) . "<br>";
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
    <input type="email" name="email" placeholder="E-posta" required><br>
    <input type="text" name="telefon" placeholder="Telefon" required><br>
    <input type="password" name="sifre" placeholder="Şifre" required><br>
    <button type="submit">Kayıt Ol</button>
</form>