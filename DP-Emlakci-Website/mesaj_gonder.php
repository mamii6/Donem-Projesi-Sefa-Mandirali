<?php
session_start();
require_once "db.php";

$log_dosya = "log.txt"; // Log dosyasının yolu

function log_yaz($mesaj) {
    global $log_dosya;
    file_put_contents($log_dosya, date("[Y-m-d H:i:s] ") . $mesaj . "\n", FILE_APPEND);
}

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION["kullanici_id"])) {
    log_yaz("HATA: Kullanıcı giriş yapmamış.");
    die("<div class='alert alert-danger'>Mesaj gönderebilmek için giriş yapmalısınız!</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gonderen_id = $_SESSION["kullanici_id"];
    $alici_id = $_POST["alici_id"];
    $ilan_id = $_POST["ilan_id"];
    $mesaj = trim($_POST["mesaj"]);

    if (empty($mesaj)) {
        log_yaz("HATA: Boş mesaj gönderilmeye çalışıldı.");
        die("<div class='alert alert-danger'>Mesaj boş olamaz!</div>");
    }

    try {
        // Mesajı ekleyelim
        $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, ilan_id, mesaj, gonderilme_tarihi) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$gonderen_id, $alici_id, $ilan_id, $mesaj]);

        $mesaj_id = $pdo->lastInsertId();
        log_yaz("MESAJ: ID: $mesaj_id, Gönderen: $gonderen_id, Alıcı: $alici_id, İlan: $ilan_id");

        // Bildirim ekleyelim
        log_yaz("BİLDİRİM EKLEME DENENİYOR: Alıcı: $alici_id, İlgili Mesaj ID: $mesaj_id");

        $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (kullanici_id, mesaj, tur, ilgili_id, goruldu, tarih) VALUES (?, ?, 'mesaj', ?, 0, NOW())");
        $bildirim_sonuc = $bildirim_stmt->execute([$alici_id, "Yeni bir mesajınız var!", $mesaj_id]);

        if ($bildirim_sonuc) {
            log_yaz("BİLDİRİM BAŞARILI: Alıcı: $alici_id, İlgili Mesaj ID: $mesaj_id");
        } else {
            log_yaz("BİLDİRİM BAŞARISIZ: Alıcı: $alici_id, Mesaj ID: $mesaj_id");
        }

        // Başarılıysa yönlendir
        header("Location: mesajlar.php");
        exit;
    } catch (PDOException $e) {
        log_yaz("PDO HATASI: " . $e->getMessage());
        die("<div class='alert alert-danger'>Hata: " . $e->getMessage() . "</div>");
    }
}
?>
