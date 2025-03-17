<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    die("<div class='alert alert-danger'>Giriş yapmalısınız!</div>");
}

if (!isset($_GET["id"])) {
    die("<div class='alert alert-danger'>Geçersiz bildirim!</div>");
}

$bildirim_id = $_GET["id"];
$kullanici_id = $_SESSION["kullanici_id"];

// Bildirimin sahibi mi kontrol et
$stmt = $pdo->prepare("SELECT * FROM bildirimler WHERE id = ? AND kullanici_id = ?");
$stmt->execute([$bildirim_id, $kullanici_id]);
$bildirim = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bildirim) {
    die("<div class='alert alert-danger'>Bildirim bulunamadı!</div>");
}

// Bildirimi okundu olarak işaretle
$pdo->prepare("UPDATE bildirimler SET goruldu = 1 WHERE id = ?")->execute([$bildirim_id]);

// Eğer türü "mesaj" ise mesaj sayfasına yönlendir
if ($bildirim["tur"] === "mesaj") {
    header("Location: mesajlar.php?id=" . $bildirim["ilgili_id"]);
    exit;
}

// Eğer türü "yorum" ise ilan detay sayfasına yönlendir
if ($bildirim["tur"] === "yorum") {
    header("Location: ilan_detay.php?id=" . $bildirim["ilgili_id"]);
    exit;
}

// Eğer başka bir şeyse, ana sayfaya yönlendir
header("Location: index.php");
exit;
?>
