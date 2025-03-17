<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET["id"])) {
    $ilan_id = $_GET["id"];
    
    // İlan sahibini kontrol et
    $stmt = $pdo->prepare("SELECT resim, kullanici_id FROM ilanlar WHERE id = ?");
    $stmt->execute([$ilan_id]);
    $ilan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ilan) {
        echo "<script>alert('İlan bulunamadı!'); window.location.href='index.php';</script>";
        exit;
    }

    if ($ilan["kullanici_id"] != $_SESSION["kullanici_id"]) {
        echo "<script>alert('Bu ilanı silme yetkiniz yok!'); window.location.href='index.php';</script>";
        exit;
    }

    // Resim dosyasını sil
    if (!empty($ilan["resim"]) && file_exists("uploads/" . $ilan["resim"])) {
        unlink("uploads/" . $ilan["resim"]);
    }

    // İlanı veritabanından sil
    $stmt = $pdo->prepare("DELETE FROM ilanlar WHERE id = ?");
    if ($stmt->execute([$ilan_id])) {
        echo "<script>alert('İlan başarıyla silindi!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('İlan silinirken bir hata oluştu!'); window.location.href='index.php';</script>";
    }
} else {
    header("Location: index.php");
    exit;
}
?>
