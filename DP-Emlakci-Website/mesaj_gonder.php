<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    die("<div class='alert alert-danger'>Mesaj gönderebilmek için giriş yapmalısınız!</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gonderen_id = $_SESSION["kullanici_id"];
    $alici_id = $_POST["alici_id"];
    $ilan_id = $_POST["ilan_id"];
    $mesaj = trim($_POST["mesaj"]);

    if (empty($mesaj)) {
        die("<div class='alert alert-danger'>Mesaj boş olamaz!</div>");
    }

    // Mesajı veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, ilan_id, mesaj) VALUES (?, ?, ?, ?)");
    $sonuc = $stmt->execute([$gonderen_id, $alici_id, $ilan_id, $mesaj]);

    if ($sonuc) {
        // En son eklenen mesajın ID’sini al
        $mesaj_id = $pdo->lastInsertId();

        // Alıcıya bildirim ekle
        $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tur, ilgili_id) VALUES (?, ?, 'mesaj', ?)");
        $bildirim_stmt->execute([$alici_id, "Yeni bir mesajınız var!", $mesaj_id]);

        echo "<div class='alert alert-success'>Mesaj başarıyla gönderildi!</div>";

        // Yönlendirme işlemi
        ob_start();
        header("Location: mesajlar.php");
        ob_end_flush();
        exit;
    } else {
        echo "<div class='alert alert-danger'>Mesaj gönderilirken hata oluştu!</div>";
    }
}
?>
