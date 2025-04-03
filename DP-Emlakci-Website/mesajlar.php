<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$selected_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Veritabanı karakter kümesini ayarla
try {
    $pdo->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    // Hata durumunda sessizce devam et
}

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

// Kullanıcı verilerini güvenli bir şekilde yeni bir diziye aktaralım
$user_data = [];
if (is_array($kullanici)) {
    $user_data = [
        'id' => $kullanici['id'] ?? '',
        'ad' => $kullanici['ad'] ?? '',
        'soyad' => $kullanici['soyad'] ?? '',
        'email' => $kullanici['email'] ?? '',
        'telefon' => $kullanici['telefon'] ?? '',
        'dogum_tarihi' => $kullanici['dogum_tarihi'] ?? '',
        'cinsiyet' => $kullanici['cinsiyet'] ?? '',
        'yetki' => $kullanici['yetki'] ?? '',
        'profil_resmi' => $kullanici['profil_resmi'] ?? '',
        'kayit_tarihi' => $kullanici['kayit_tarihi'] ?? '',
    ];
}

// Yardımcı fonksiyon - değer kontrolü
function getValueOrDefault($value, $default = 'Belirtilmemiş') {
    if (isset($value) && $value !== '' && $value !== null) {
        return htmlspecialchars($value);
    }
    return $default;
}

// Favori sayısını al
$favori_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM favoriler WHERE kullanici_id = ?");
$favori_sayisi_stmt->execute([$kullanici_id]);
$favori_sayisi = $favori_sayisi_stmt->fetchColumn();

// İlan sayısını al
$ilan_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM ilanlar WHERE kullanici_id = ?");
$ilan_sayisi_stmt->execute([$kullanici_id]);
$ilan_sayisi = $ilan_sayisi_stmt->fetchColumn();

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

// Toplam okunmamış mesaj sayısını al
$total_unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM mesajlar WHERE alici_id = ? AND goruldu = 0");
$total_unread_stmt->execute([$kullanici_id]);
$total_unread = $total_unread_stmt->fetchColumn();

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/profil.css">
<link rel="stylesheet" href="css/mesajlar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Mesajlarım</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / <a href="profil.php">Profilim</a> / Mesajlarım
        </div>
    </div>
</div>

<!-- Mesajlar Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Sol Kenar Menüsü -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="profile-sidebar">
                    <div class="profile-img">
                        <?php if (!empty($user_data['profil_resmi'])): ?>
                            <img src="uploads/profiller/<?php echo htmlspecialchars($user_data['profil_resmi']); ?>" alt="Profil Resmi">
                        <?php else: ?>
                            <img src="img/profile-placeholder.jpg" alt="Profil Resmi">
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h4><?php echo getValueOrDefault($user_data["ad"]); ?> <?php echo getValueOrDefault($user_data["soyad"], ''); ?></h4>
                        <p><?php echo isset($user_data["yetki"]) && !empty($user_data["yetki"]) ? htmlspecialchars($user_data["yetki"]) : 'Üye'; ?></p>
                    </div>
                    
                    <ul class="profile-menu">
                        <li><a href="profil.php"><i class="fas fa-user"></i> Profilim</a></li>
                        <li><a href="ilanlarim.php"><i class="fas fa-home"></i> İlanlarım <span class="badge bg-primary"><?php echo $ilan_sayisi; ?></span></a></li>
                        <li><a href="favorilerim.php"><i class="fas fa-heart"></i> Favorilerim <span class="badge bg-danger"><?php echo $favori_sayisi; ?></span></a></li>
                        <li><a href="mesajlar.php" class="active"><i class="fas fa-envelope"></i> Mesajlarım <?php if($total_unread > 0): ?><span class="badge bg-danger"><?php echo $total_unread; ?></span><?php endif; ?></a></li>
                        <li><a href="profil_duzenle.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
                        <li><a href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Sağ Ana İçerik -->
            <div class="col-lg-8 col-md-7">
                <div class="chat-container">
                    <!-- Konuşma Listesi -->
                    <div class="conversation-list">
                        <?php 
                        $has_conversations = false;
                        while ($conv = $conversations->fetch(PDO::FETCH_ASSOC)): 
                            $has_conversations = true;
                            $profile_img = !empty($conv['profil_resmi']) ? "uploads/profiller/" . $conv['profil_resmi'] : "img/profile-placeholder.jpg"; 
                            $isActive = $selected_user && $selected_user == $conv['id'];
                        ?>
                            <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?>" 
                                 onclick="window.location.href='mesajlar.php?user_id=<?php echo $conv['id']; ?>'">
                                <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profil" class="conversation-avatar">
                                <div class="conversation-info">
                                    <div class="conversation-name">
                                        <span><?php echo htmlspecialchars($conv['ad'] . ' ' . $conv['soyad']); ?></span>
                                        <?php if ($conv['okunmamis_mesaj_sayisi'] > 0): ?>
                                            <span class="unread-badge"><?php echo $conv['okunmamis_mesaj_sayisi']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-last-message">
                                        <?php echo !empty($conv['son_mesaj']) ? htmlspecialchars(mb_substr($conv['son_mesaj'], 0, 30) . (mb_strlen($conv['son_mesaj']) > 30 ? '...' : '')) : 'Henüz mesaj yok'; ?>
                                    </div>
                                    <?php if (!empty($conv['son_mesaj_tarihi'])): ?>
                                        <div class="conversation-time">
                                            <?php 
                                                $date = new DateTime($conv['son_mesaj_tarihi']);
                                                echo $date->format('d.m.Y H:i'); 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <?php if (!$has_conversations): ?>
                            <div class="no-conversation">
                                <div class="text-center p-4">
                                    <i class="bi bi-chat-dots-fill empty-icon"></i>
                                    <p>Henüz mesajlaşma bulunmuyor.</p>
                                    <small>İlan sahibi ile iletişime geçtiğinizde burada görünecektir.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mesajlaşma Alanı -->
                    <div class="chat-area">
                        <?php if ($selected_user && $selected_user_info): ?>
                            <!-- Sohbet Başlığı -->
                            <div class="chat-header">
                                <?php $user_img = !empty($selected_user_info['profil_resmi']) ? "uploads/profiller/" . $selected_user_info['profil_resmi'] : "img/profile-placeholder.jpg"; ?>
                                <img src="<?php echo htmlspecialchars($user_img); ?>" alt="Profil" class="chat-header-avatar">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($selected_user_info['ad'] . ' ' . $selected_user_info['soyad']); ?></h5>
                                </div>
                            </div>

                            <!-- Mesajlar -->
                            <div class="chat-messages" id="chat-messages">
                                <?php 
                                $has_messages = false;
                                while ($mesaj = $mesajlar->fetch(PDO::FETCH_ASSOC)): 
                                    $has_messages = true;
                                ?>
                                    <div class="message <?php echo ($mesaj['gonderen_id'] == $kullanici_id) ? 'sent' : 'received'; ?>">
                                        <div class="message-content">
                                            <?php echo htmlspecialchars($mesaj['mesaj']); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php 
                                                $date = new DateTime($mesaj['gonderilme_tarihi']);
                                                echo $date->format('H:i'); 
                                            ?>
                                            <?php if ($mesaj['gonderen_id'] == $kullanici_id): ?>
                                                <span class="read-status <?php echo $mesaj['goruldu'] ? 'read' : ''; ?>">
                                                    <i class="bi bi-check2-all"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                
                                <?php if (!$has_messages): ?>
                                    <div class="no-messages">
                                        <div class="text-center">
                                            <i class="bi bi-chat-text empty-icon"></i>
                                            <p>Henüz mesaj yok</p>
                                            <small>Bu kişiyle sohbet başlatmak için aşağıdan bir mesaj gönderebilirsiniz.</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Mesaj Giriş Alanı -->
                            <div class="chat-input">
                                <form id="message-form">
                                    <input type="hidden" id="alici_id" value="<?php echo $selected_user; ?>">
                                    <textarea id="mesaj" class="form-control" placeholder="Mesajınızı yazın..." rows="1" required></textarea>
                                    <button type="submit" class="btn btn-primary send-btn">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <!-- Boş Durum - Henüz sohbet seçilmemiş -->
                            <div class="empty-state">
                                <i class="bi bi-chat-square-text-fill empty-icon"></i>
                                <h5>Mesajlarınız</h5>
                                <p>Mesajlaşmak için sol menüden bir kişi seçin</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mesaj alanını en sonuna kaydır
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Enter tuşu ile mesaj gönderme
    const mesajInput = document.getElementById('mesaj');
    if (mesajInput) {
        mesajInput.addEventListener('keydown', function(e) {
            // Enter tuşuna basıldı ve Shift tuşu basılı değil
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Enter'ın yeni satır eklemesini engelle
                const messageForm = document.getElementById('message-form');
                if (messageForm) {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // Mesaj gönderme işlemi
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const aliciId = document.getElementById('alici_id').value;
            const mesajInput = document.getElementById('mesaj');
            const mesaj = mesajInput.value;
            
            if (mesaj.trim() === '') return;
            
            // Input'u hemen temizle
            mesajInput.value = '';
            
            // Mesajı hemen sohbete ekle
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
            
            const readStatus = document.createElement('span');
            readStatus.className = 'read-status';
            readStatus.innerHTML = '<i class="bi bi-check2-all"></i>';
            messageTime.appendChild(readStatus);
            
            messageElement.appendChild(messageContent);
            messageElement.appendChild(messageTime);
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // No message bölümünü gizle
            const noMessages = document.querySelector('.no-messages');
            if (noMessages) {
                noMessages.style.display = 'none';
            }
            
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
                if (!data.success) {
                    console.error('Mesaj gönderilemedi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Her 5 saniyede bir yeni mesaj kontrolü yap
    if (document.getElementById('chat-messages')) {
        setInterval(checkNewMessages, 5000);
    }
    
    // Real-time mesaj kontrolü (polling)
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
                
                // No message bölümünü gizle
                const noMessages = document.querySelector('.no-messages');
                if (noMessages) {
                    noMessages.style.display = 'none';
                }
                
                chatMessages.scrollTop = chatMessages.scrollHeight;
                lastCheck = new Date().getTime();
            }
        })
        .catch(error => {
            console.error('Error checking new messages:', error);
        });
    }

    // Textarea yüksekliğini otomatik ayarla
    const textarea = document.getElementById('mesaj');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.scrollHeight > 150) {
                this.style.height = '150px';
            }
        });
    }
});
</script>