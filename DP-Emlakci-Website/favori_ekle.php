<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ilan_id"])) {
    $kullanici_id = $_SESSION["kullanici_id"];
    $ilan_id = $_POST["ilan_id"];

    // Favori daha önce eklenmiş mi kontrol et
    $stmt = $pdo->prepare("SELECT * FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    $stmt->execute([$kullanici_id, $ilan_id]);

    if ($stmt->rowCount() == 0) {
        // Favori ekle
        $stmt = $pdo->prepare("INSERT INTO favoriler (kullanici_id, ilan_id, eklenme_tarihi) VALUES (?, ?, NOW())");
        if ($stmt->execute([$kullanici_id, $ilan_id])) {
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?favori=eklendi");
        } else {
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?favori=hata");
        }
    } else {
        // Favori zaten ekliyse, kaldır
        $stmt = $pdo->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
        if ($stmt->execute([$kullanici_id, $ilan_id])) {
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?favori=silindi");
        } else {
            header("Location: " . $_SERVER["HTTP_REFERER"] . "?favori=silme_hata");
        }
    }
} else {
    header("Location: index.php");
}
?>
