<?php
require_once "inc/auth.php";
require_once "admin_check.php";
require_once "../db.php";

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $stmt = $pdo->prepare("DELETE FROM kullanicilar WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: kullanicilar.php");
exit;
?>
