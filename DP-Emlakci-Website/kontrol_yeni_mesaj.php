<?php
session_start();
require_once "db.php";

// Yetkilendirme kontrolü
if (!isset($_SESSION["kullanici_id"])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Oturum bulunamadı'
    ]);
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$last_check = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;

// Geçerli zaman kontrolü
if ($last_check <= 0) {
    $last_check = time() * 1000 - 10000; // Son 10 saniye
} else {
    // JavaScript timestamp'i (milisaniye) PHP timestamp'ine (saniye) çevir
    $last_check = floor($last_check / 1000);
}

// Sonuç dizisi
$result = [
    'success' => true,
    'messages' => []
];

try {
    // Seçili kullanıcıdan gelen yeni mesajları getir
    if ($selected_user > 0) {
        $query = $pdo->prepare("SELECT 
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
                            WHERE m.gonderen_id = ? 
                            AND m.alici_id = ? 
                            AND UNIX_TIMESTAMP(m.gonderilme_tarihi) > ?
                            ORDER BY m.gonderilme_tarihi ASC");
        
        $query->execute([$selected_user, $kullanici_id, $last_check]);
        $messages = $query->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($messages) > 0) {
            // Mesajları okundu olarak işaretle
            $update = $pdo->prepare("UPDATE mesajlar SET goruldu = 1 
                                    WHERE gonderen_id = ? AND alici_id = ? AND goruldu = 0");
            $update->execute([$selected_user, $kullanici_id]);
            
            $result['messages'] = $messages;
        }
    }
    
    // Okunmamış mesaj sayılarını güncelle
    $conversations = $pdo->prepare("SELECT 
                                    k.id,
                                    COUNT(CASE WHEN m.goruldu = 0 AND m.alici_id = ? THEN 1 END) AS okunmamis_mesaj_sayisi
                                FROM kullanicilar k
                                LEFT JOIN mesajlar m ON (m.gonderen_id = k.id AND m.alici_id = ?)
                                WHERE k.id != ?
                                GROUP BY k.id");
    $conversations->execute([$kullanici_id, $kullanici_id, $kullanici_id]);
    
    $result['conversations'] = [];
    while ($conv = $conversations->fetch(PDO::FETCH_ASSOC)) {
        if ($conv['okunmamis_mesaj_sayisi'] > 0) {
            $result['conversations'][] = [
                'user_id' => $conv['id'],
                'unread_count' => $conv['okunmamis_mesaj_sayisi']
            ];
        }
    }
    
} catch (PDOException $e) {
    $result = [
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ];
}

// JSON döndür
header('Content-Type: application/json');
echo json_encode($result);
?>