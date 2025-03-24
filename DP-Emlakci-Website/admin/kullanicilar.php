<?php
require_once "inc/auth.php";
require_once "admin_check.php";
require_once "../db.php";

$kullanicilar = $pdo->query("SELECT id, ad, email, yetki FROM kullanicilar")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Kullanıcı Yönetimi</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Email</th>
                    <th>Yetki</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kullanicilar as $kullanici): ?>
                    <tr>
                        <td><?= $kullanici["id"] ?></td>
                        <td><?= htmlspecialchars($kullanici["ad"]) ?></td>
                        <td><?= htmlspecialchars($kullanici["email"]) ?></td>
                        <td><?= $kullanici["yetki"] ?></td>
                        <td>
                            <a href="yetki_degistir.php?id=<?= $kullanici["id"] ?>&yetki=admin" class="btn btn-success btn-sm">Admin Yap</a>
                            <a href="yetki_degistir.php?id=<?= $kullanici["id"] ?>&yetki=kullanici" class="btn btn-warning btn-sm">Kullanıcı Yap</a>
                            <a href="kullanici_sil.php?id=<?= $kullanici["id"] ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
