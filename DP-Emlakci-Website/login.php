<?php
require_once "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $sifre = trim($_POST["sifre"]);

    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE email = ?");
    $stmt->execute([$email]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($kullanici && password_verify($sifre, $kullanici["sifre"])) {
        $_SESSION["kullanici_id"] = $kullanici["id"];
        $_SESSION["ad"] = $kullanici["ad"];
        header("Location: index.php");
        exit();
    } else {
        echo "Geçersiz e-posta veya şifre!";
    }
}
?>

<form method="post">
    <input type="email" name="email" placeholder="E-posta" required><br>
    <input type="password" name="sifre" placeholder="Şifre" required><br>
    <button type="submit">Giriş Yap</button>
</form>
