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
    
    $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, ilan_id, mesaj) VALUES (?, ?, ?, ?)");
    $sonuc = $stmt->execute([$gonderen_id, $alici_id, $ilan_id, $mesaj]);
    
    if ($sonuc) {
        echo "<div class='alert alert-success'>Mesaj başarıyla gönderildi!</div>";
        header("Location: mesajlar.php"); exit;
        exit;
    } else {
        echo "<div class='alert alert-danger'>Mesaj gönderilirken hata oluştu!</div>";
    }
}
?>
