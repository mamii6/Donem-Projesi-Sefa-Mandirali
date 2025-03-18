<?php
session_start();
require_once "db.php";

// Kullanıcı giriş yapmamışsa işlemi durdur
if (!isset($_SESSION["kullanici_id"])) {
    die("Yorum eklemek için giriş yapmalısınız.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ilan_id = $_POST["ilan_id"];
    $yorum = trim($_POST["yorum"]);
    $puan = (int) $_POST["puan"];
    $kullanici_id = $_SESSION["kullanici_id"];

    if (empty($yorum) || $puan < 1 || $puan > 5) {
        die("Geçerli bir yorum ve puan giriniz.");
    }

    try {
        $pdo->beginTransaction();

        // Yorumu veritabanına ekle
        $stmt = $pdo->prepare("INSERT INTO yorumlar (ilan_id, kullanici_id, yorum, puan) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ilan_id, $kullanici_id, $yorum, $puan]);

        // İlanın sahibini bul
        $ilan_sahibi_sorgu = $pdo->prepare("SELECT kullanici_id FROM ilanlar WHERE id = ?");
        $ilan_sahibi_sorgu->execute([$ilan_id]);
        $ilan_sahibi = $ilan_sahibi_sorgu->fetch(PDO::FETCH_ASSOC);

        if ($ilan_sahibi) {
            $ilan_sahibi_id = $ilan_sahibi["kullanici_id"];

            // Eğer yorum yapan kişi ilan sahibiyse bildirim ekleme
            if ($ilan_sahibi_id != $kullanici_id) {
                $bildirim_mesaj = "İlanınıza yeni bir yorum yapıldı!";
                $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tur, ilgili_id, goruldu) VALUES (?, ?, ?, ?, 0)");
                $bildirim_stmt->execute([$ilan_sahibi_id, $bildirim_mesaj, 'yorum', $ilan_id]);
            }
        }

        $pdo->commit();

        // Yorumu ekledikten sonra ilan detay sayfasına yönlendir
        header("Location: ilan_detay.php?id=" . $ilan_id);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Yorum eklenirken hata oluştu: " . $e->getMessage());
    }
}
?>
