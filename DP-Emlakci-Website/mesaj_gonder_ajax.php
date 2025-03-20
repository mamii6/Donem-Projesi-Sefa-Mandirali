<?php
/* mesaj_gonder_ajax.php */
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Oturum açılmamış']);
    exit;
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alici_id']) && isset($_POST['mesaj'])) {
    $gonderen_id = $_SESSION["kullanici_id"];
    $alici_id = intval($_POST['alici_id']);
    $mesaj = trim($_POST['mesaj']);
    
    if (empty($mesaj)) {
        $response['message'] = 'Mesaj boş olamaz';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, mesaj, gonderilme_tarihi, goruldu) VALUES (?, ?, ?, NOW(), 0)");
            $result = $stmt->execute([$gonderen_id, $alici_id, $mesaj]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message_id' => $pdo->lastInsertId(),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                $response['message'] = 'Mesaj gönderilemedi';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Veritabanı hatası';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>

<?php
/* kontrol_yeni_mesaj.php */
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Oturum açılmamış']);
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : 0;

// Son kontrol zamanından itibaren gelen yeni mesajları kontrol et
$stmt = $pdo->prepare("SELECT m.*, k.ad AS gonderen_ad, k.soyad AS gonderen_soyad 
                      FROM mesajlar m 
                      JOIN kullanicilar k ON m.gonderen_id = k.id 
                      WHERE m.alici_id = ? AND m.gonderen_id = ? AND m.gonderilme_tarihi > FROM_UNIXTIME(? / 1000)
                      ORDER BY m.gonderilme_tarihi ASC");
$stmt->execute([$kullanici_id, $selected_user, $last_check]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mesajları okundu olarak işaretle
if (count($messages) > 0) {
    $pdo->prepare("UPDATE mesajlar SET goruldu = 1 WHERE alici_id = ? AND gonderen_id = ?")->execute([$kullanici_id, $selected_user]);
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'messages' => $messages,
    'count' => count($messages)
]);
?>