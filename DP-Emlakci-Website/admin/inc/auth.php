<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../db.php";

// **Doğru değişkeni kontrol et!**
if (!isset($_SESSION["id"]) || $_SESSION["yetki"] !== "admin") {
    header("Location: ../login.php?error=unauthorized");
    exit;
}
