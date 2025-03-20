<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Konuşma listesini al (son mesaj tarihi sıralamasına göre)
$conversations = $pdo->prepare("SELECT 
                                k.id, k.ad, k.soyad, k.profil_resmi,
                                m.mesaj AS son_mesaj,
                                m.gonderilme_tarihi AS son_mesaj_tarihi,
                                COUNT(CASE WHEN m.goruldu = 0 AND m.alici_id = ? THEN 1 END) AS okunmamis_mesaj_sayisi
                              FROM kullanicilar k
                              LEFT JOIN (
                                SELECT 
                                    CASE 
                                        WHEN gonderen_id = ? THEN alici_id
                                        ELSE gonderen_id
                                    END AS diger_kullanici_id,
                                    mesaj,
                                    gonderilme_tarihi,
                                    goruldu,
                                    alici_id,
                                    gonderen_id,
                                    ROW_NUMBER() OVER (PARTITION BY 
                                        CASE 
                                            WHEN gonderen_id = ? THEN alici_id
                                            ELSE gonderen_id
                                        END
                                    ORDER BY gonderilme_tarihi DESC) AS row_num
                                FROM mesajlar
                                WHERE gonderen_id = ? OR alici_id = ?
                              ) m ON k.id = m.diger_kullanici_id AND m.row_num = 1 
                              WHERE k.id != ?
                              GROUP BY k.id, k.ad, k.soyad, k.profil_resmi, m.mesaj, m.gonderilme_tarihi
                              ORDER BY m.gonderilme_tarihi DESC");
$conversations->execute([$kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id]);

// Eğer seçili kullanıcı varsa, o kullanıcı ile olan tüm mesajları getir
if ($selected_user) {
    $mesajlar = $pdo->prepare("SELECT m.*, k1.ad AS gonderen_ad, k1.soyad AS gonderen_soyad, k1.profil_resmi AS gonderen_resim, k2.ad AS alici_ad, k2.soyad AS alici_soyad, k2.profil_resmi AS alici_resim 
                              FROM mesajlar m 
                              JOIN kullanicilar k1 ON m.gonderen_id = k1.id 
                              JOIN kullanicilar k2 ON m.alici_id = k2.id 
                              WHERE (m.gonderen_id = ? AND m.alici_id = ?) OR (m.gonderen_id = ? AND m.alici_id = ?) 
                              ORDER BY m.gonderilme_tarihi ASC");
    $mesajlar->execute([$kullanici_id, $selected_user, $selected_user, $kullanici_id]);
    
    // Seçili kullanıcıdan gelen mesajları okundu olarak işaretle
    $pdo->prepare("UPDATE mesajlar SET goruldu = 1 WHERE gonderen_id = ? AND alici_id = ?")->execute([$selected_user, $kullanici_id]);
    
    // Seçili kullanıcının bilgilerini al
    $user_info = $pdo->prepare("SELECT ad, soyad, profil_resmi FROM kullanicilar WHERE id = ?");
    $user_info->execute([$selected_user]);
    $selected_user_info = $user_info->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar | Emlakçı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .chat-container {
            display: flex;
            height: 80vh;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
        }
        .conversation-list {
            width: 300px;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            background: #f8f9fa;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .conversation-item:hover {
            background-color: #eaeaea;
        }
        .conversation-item.active {
            background-color: #e3f2fd;
        }
        .conversation-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        .conversation-info {
            flex-grow: 1;
            overflow: hidden;
        }
        .conversation-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .conversation-last-message {
            color: #777;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .conversation-time {
            font-size: 12px;
            color: #999;
        }
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
            display: flex;
            align-items: center;
        }
        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        .chat-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background: #eee;
            display: flex;
            flex-direction: column;
        }
        .message {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            max-width: 70%;
            position: relative;
        }
        .message-content {
            word-break: break-word;
        }
        .sent {
            align-self: flex-end;
            background: #dcf8c6;
            border-bottom-right-radius: 0;
        }
        .received {
            align-self: flex-start;
            background: #fff;
            border-bottom-left-radius: 0;
        }
        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            text-align: right;
        }
        .read-status {
            font-size: 12px;
            margin-left: 5px;
            color: #34b7f1;
        }
        .chat-input {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
        }
        .chat-input form {
            display: flex;
        }
        .chat-input textarea {
            flex-grow: 1;
            border-radius: 20px;
            padding: 10px 15px;
            resize: none;
        }
        .chat-input button {
            margin-left: 10px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
        }
        .unread-badge {
            background-color: #25d366;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 10px;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            background: #f8f9fa;
            color: #777;
        }
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Emlakçı</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Anasayfa</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Mesajlarım</h2>
    <div class="chat-container">
        <!-- Konuşma Listesi -->
        <div class="conversation-list">
            <?php while ($conv = $conversations->fetch(PDO::FETCH_ASSOC)): ?>
                <?php 
                    $profile_img = !empty($conv['profil_resmi']) ? "uploads/profiller/" . $conv['profil_resmi'] : "uploads/profiller/default.png"; 
                    $isActive = $selected_user && $selected_user == $conv['id'];
                ?>
                <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?>" 
                     onclick="window.location.href='mesajlar.php?user_id=<?php echo $conv['id']; ?>'">
                    <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profil">
                    <div class="conversation-info">
                        <div class="conversation-name">
                            <?php echo htmlspecialchars($conv['ad'] . ' ' . $conv['soyad']); ?>
                            <?php if ($conv['okunmamis_mesaj_sayisi'] > 0): ?>
                                <span class="unread-badge"><?php echo $conv['okunmamis_mesaj_sayisi']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="conversation-last-message">
                            <?php echo !empty($conv['son_mesaj']) ? htmlspecialchars(mb_substr($conv['son_mesaj'], 0, 30) . (mb_strlen($conv['son_mesaj']) > 30 ? '...' : '')) : 'Henüz mesaj yok'; ?>
                        </div>
                    </div>
                    <?php if (!empty($conv['son_mesaj_tarihi'])): ?>
                        <div class="conversation-time">
                            <?php 
                                $date = new DateTime($conv['son_mesaj_tarihi']);
                                echo $date->format('H:i'); 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Mesajlaşma Alanı -->
        <div class="chat-area">
            <?php if ($selected_user && $selected_user_info): ?>
                <!-- Sohbet Başlığı -->
                <div class="chat-header">
                    <?php $user_img = !empty($selected_user_info['profil_resmi']) ? "uploads/profiller/" . $selected_user_info['profil_resmi'] : "uploads/profiller/default.png"; ?>
                    <img src="<?php echo htmlspecialchars($user_img); ?>" alt="Profil">
                    <div>
                        <h5 class="m-0"><?php echo htmlspecialchars($selected_user_info['ad'] . ' ' . $selected_user_info['soyad']); ?></h5>
                        
                    </div>
                </div>

                <!-- Mesajlar -->
                <div class="chat-messages" id="chat-messages">
                    <?php while ($mesaj = $mesajlar->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="message <?php echo ($mesaj['gonderen_id'] == $kullanici_id) ? 'sent' : 'received'; ?>">
                            <div class="message-content">
                                <?php echo htmlspecialchars($mesaj['mesaj']); ?>
                            </div>
                            <div class="message-time">
                                <?php 
                                    $date = new DateTime($mesaj['gonderilme_tarihi']);
                                    echo $date->format('H:i'); 
                                ?>
                                <?php if ($mesaj['gonderen_id'] == $kullanici_id && $mesaj['goruldu']): ?>
                                    <span class="read-status">✓✓</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mesaj Giriş Alanı -->
                <div class="chat-input">
                    <form id="message-form">
                        <input type="hidden" id="alici_id" value="<?php echo $selected_user; ?>">
                        <textarea id="mesaj" class="form-control" placeholder="Mesajınızı yazın..." rows="1" required></textarea>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Boş Durum - Henüz sohbet seçilmemiş -->
                <div class="empty-state">
                    <i class="bi bi-chat-dots"></i>
                    <p>Mesajlaşmak için bir kişi seçin</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mesaj alanını en sonuna kaydır
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Mesaj gönderme işlemi
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const aliciId = document.getElementById('alici_id').value;
            const mesaj = document.getElementById('mesaj').value;
            
            if (mesaj.trim() === '') return;
            
            // AJAX ile mesajı gönder
            const formData = new FormData();
            formData.append('alici_id', aliciId);
            formData.append('mesaj', mesaj);
            
            fetch('mesaj_gonder_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mesajı sohbete ekle
                    const messageElement = document.createElement('div');
                    messageElement.className = 'message sent';
                    
                    const messageContent = document.createElement('div');
                    messageContent.className = 'message-content';
                    messageContent.textContent = mesaj;
                    
                    const messageTime = document.createElement('div');
                    messageTime.className = 'message-time';
                    
                    const now = new Date();
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    messageTime.textContent = `${hours}:${minutes}`;
                    
                    messageElement.appendChild(messageContent);
                    messageElement.appendChild(messageTime);
                    
                    chatMessages.appendChild(messageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    
                    // Mesaj alanını temizle
                    document.getElementById('mesaj').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Real-time mesaj kontrolü (poling)
    let lastCheck = new Date().getTime();
    
    function checkNewMessages() {
        const selectedUser = document.getElementById('alici_id')?.value;
        if (!selectedUser) return;
        
        fetch(`kontrol_yeni_mesaj.php?user_id=${selectedUser}&last_check=${lastCheck}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                // Yeni mesajları ekle
                data.messages.forEach(msg => {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'message received';
                    
                    const messageContent = document.createElement('div');
                    messageContent.className = 'message-content';
                    messageContent.textContent = msg.mesaj;
                    
                    const messageTime = document.createElement('div');
                    messageTime.className = 'message-time';
                    
                    const msgDate = new Date(msg.gonderilme_tarihi);
                    const hours = msgDate.getHours().toString().padStart(2, '0');
                    const minutes = msgDate.getMinutes().toString().padStart(2, '0');
                    messageTime.textContent = `${hours}:${minutes}`;
                    
                    messageElement.appendChild(messageContent);
                    messageElement.appendChild(messageTime);
                    
                    chatMessages.appendChild(messageElement);
                });
                
                chatMessages.scrollTop = chatMessages.scrollHeight;
                lastCheck = new Date().getTime();
            }
        })
        .catch(error => {
            console.error('Error checking new messages:', error);
        });
    }
    
    // Her 3 saniyede bir yeni mesaj kontrolü yap
    if (document.getElementById('chat-messages')) {
        setInterval(checkNewMessages, 3000);
    }
});
</script>
</body>
</html>