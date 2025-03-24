<?php
require_once "inc/auth.php";
require_once "admin_check.php";

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Paneli</a>
            <a class="btn btn-danger" href="../logout.php">Çıkış Yap</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Hoş geldiniz, Admin!</h2>
        <p>Bu panelden ilanları ve kullanıcıları yönetebilirsiniz.</p>

        <a href="ilanlar.php" class="btn btn-primary">İlanları Yönet</a>
        <a href="kullanicilar.php" class="btn btn-warning">Kullanıcıları Yönet</a>
    </div>
</body>
</html>
