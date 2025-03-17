<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    die("Yetkisiz işlem!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $bildirim_id = intval($_POST["id"]);

    // Bildirimi okundu olarak işaretle
    $stmt = $pdo->prepare("UPDATE bildirimler SET goruldu = 1 WHERE id = ?");
    $stmt->execute([$bildirim_id]);
}
?>
