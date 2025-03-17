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

    // Favori daha önce eklenmiş mi kontrol 
    $stmt = $pdo->prepare("SELECT * FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    $stmt->execute([$kullanici_id, $ilan_id]);

    if ($stmt->rowCount() == 0) {
        // Favori ekle
        $stmt = $pdo->prepare("INSERT INTO favoriler (kullanici_id, ilan_id) VALUES (?, ?)");
        if ($stmt->execute([$kullanici_id, $ilan_id])) {
            header("Location: index.php?favori=eklendi");
        } else {
            header("Location: index.php?favori=hata");
        }
    } else {
        header("Location: index.php?favori=zaten_var");
    }
} else {
    header("Location: index.php");
}
?>
