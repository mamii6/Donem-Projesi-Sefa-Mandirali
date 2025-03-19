<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profil_resmi"])) {
    $dosya = $_FILES["profil_resmi"];
    $dosyaAdi = time() . "_" . basename($dosya["name"]);
    $hedefKlasor = "uploads/profiller/";
    if (!is_dir($hedefKlasor))
     {
        mkdir($hedefKlasor, 0777, true); // Klasör yoksa oluştur
     }   
        $hedefYol = $hedefKlasor . $dosyaAdi;


    if (move_uploaded_file($dosya["tmp_name"], $hedefYol)) {
        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE kullanicilar SET profil_resmi = ? WHERE id = ?");
        $stmt->execute([$dosyaAdi, $kullanici_id]);
        header("Location: profil.php?resim=guncellendi");
    } else {
        header("Location: profil.php?resim=hata");
    }
} else {
    header("Location: profil.php");
}
?>
