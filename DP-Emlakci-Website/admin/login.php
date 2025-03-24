<?php
session_start(); // Oturumu başlat

require_once "../db.php"; // Veritabanı bağlantısı

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $sifre = trim($_POST["sifre"]);

    $stmt = $pdo->prepare("SELECT id, sifre, yetki FROM kullanicilar WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($sifre, $user["sifre"])) {
        $_SESSION["id"] = $user["id"];
        $_SESSION["yetki"] = $user["yetki"];

        if ($user["yetki"] === "admin") {
            header("Location: index.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $hata = "E-posta veya şifre yanlış!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
<div class="card p-4 shadow-lg" style="width: 350px;">
    <h3 class="text-center mb-3">Admin Giriş</h3>
    <?php if (isset($hata)) echo "<div class='alert alert-danger'>$hata</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="sifre" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
    </form>
</div>
</body>
</html>
