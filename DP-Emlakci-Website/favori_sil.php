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

    $stmt = $pdo->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
    if ($stmt->execute([$kullanici_id, $ilan_id])) {
        header("Location: favoriler.php?favori=silindi");
    } else {
        header("Location: favoriler.php?favori=hata");
    }
} else {
    header("Location: favoriler.php");
}
?>
