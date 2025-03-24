<?php
require_once "inc/auth.php";
require_once "admin_check.php";
require_once "../db.php";

if (isset($_GET["id"]) && isset($_GET["yetki"])) {
    $id = intval($_GET["id"]);
    $yetki = $_GET["yetki"];

    if ($yetki === "admin" || $yetki === "kullanici") {
        $stmt = $pdo->prepare("UPDATE kullanicilar SET yetki = ? WHERE id = ?");
        $stmt->execute([$yetki, $id]);
    }
}

header("Location: kullanicilar.php");
exit;
?>
