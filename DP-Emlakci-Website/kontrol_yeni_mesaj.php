<?php
session_start();
require_once "db.php";

// Kullanıcı oturum kontrolü
if (!isset($_SESSION["kullanici_id"])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Oturum süresi dolmuş veya giriş yapılmamış'
    ]);
    exit;
}

// Parametreleri al
$kullanici_id = $_SESSION["kullanici_id"];
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : 0;

// Zaman kontrolü
if ($last_check <= 0) {
    // Varsayılan olarak son 30 saniyedeki mesajları getir
    $timestamp = date('Y-m-d H:i:s', strtotime('-30 seconds'));
} else {
    // JavaScript timestamp'i PHP datetime'a çevir
    $timestamp = date('Y-m-d H:i:s', $last_check / 1000);
}

// Yanıt array'i
$response = [
    'success' => true,
    'timestamp' => time() * 1000, // Şu anki zaman (JavaScript için milisaniye cinsinden)
    'messages' => [],
    'conversations_update' => []
];

try {
    // Seçili kullanıcıdan gelen yeni mesajları getir
    if ($selected_user > 0) {
        $stmt = $pdo->prepare("SELECT 
                               m.id, 
                               m.gonderen_id, 
                               m.alici_id, 
                               m.mesaj, 
                               m.gonderilme_tarihi, 
                               m.goruldu,
                               k.ad AS gonderen_ad,
                               k.soyad AS gonderen_soyad,
                               k.profil_resmi AS gonderen_resim
                           FROM mesajlar m
                           JOIN kullanicilar k ON m.gonderen_id = k.id
                           WHERE m.gonderen_id = :gonderen 
                               AND m.alici_id = :alici 
                               AND m.gonderilme_tarihi > :zaman
                           ORDER BY m.gonderilme_tarihi ASC");
        
        $stmt->execute([
            ':gonderen' => $selected_user,
            ':alici' => $kullanici_id,
            ':zaman' => $timestamp
        ]);
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($messages) > 0) {
            // Mesajları okundu olarak işaretle
            $update = $pdo->prepare("UPDATE mesajlar 
                                   SET goruldu = 1 
                                   WHERE gonderen_id = :gonderen 
                                       AND alici_id = :alici 
                                       AND goruldu = 0");
            $update->execute([
                ':gonderen' => $selected_user,
                ':alici' => $kullanici_id
            ]);
            
            // Mesajları yanıta ekle
            $response['messages'] = $messages;
        }
    }
    
    // Tüm konuşmalardaki okunmamış mesaj sayılarını hesapla
    $unread_counts = $pdo->prepare("SELECT 
                                  k.id, 
                                  k.ad, 
                                  k.soyad,
                                  COUNT(m.id) AS okunmamis_sayisi,
                                  MAX(m.gonderilme_tarihi) AS son_mesaj_tarihi
                              FROM kullanicilar k
                              JOIN mesajlar m ON m.gonderen_id = k.id
                              WHERE m.alici_id = :kullanici 
                                  AND m.goruldu = 0
                              GROUP BY k.id");
    $unread_counts->execute([':kullanici' => $kullanici_id]);
    
    while ($row = $unread_counts->fetch(PDO::FETCH_ASSOC)) {
        $response['conversations_update'][] = [
            'user_id' => $row['id'],
            'name' => $row['ad'] . ' ' . $row['soyad'],
            'unread_count' => $row['okunmamis_sayisi'],
            'last_message_time' => $row['son_mesaj_tarihi']
        ];
    }
    
    // Son başarı alanını ayarla
    $response['last_check'] = time() * 1000;
    
} catch (PDOException $e) {
    // Hata durumunda
    $response = [
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ];
}

// JSON yanıtı gönder
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>