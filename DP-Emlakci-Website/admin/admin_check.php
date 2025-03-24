<?php

require_once "inc/auth.php";
require_once "../db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: ../login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$stmt = $pdo->prepare("SELECT yetki FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$yetki = $stmt->fetchColumn();

if ($yetki !== "admin") {
    header("Location: ../index.php");
    exit;
}
?>
