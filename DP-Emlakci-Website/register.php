<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST["ad"]);
    $email = trim($_POST["email"]);
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT);

    if (!empty($ad) && !empty($email) && !empty($_POST["sifre"])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, email, sifre) VALUES (?, ?, ?)");
            $stmt->execute([$ad, $email, $sifre]);
            echo "Kayıt başarılı! <a href='login.php'>Giriş yap</a>";
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
    <input type="password" name="sifre" placeholder="Şifre" required><br>
    <button type="submit">Kayıt Ol</button>
</form>
