<?php
session_start();
require_once "db.php";

// Kullanıcı giriş yaptıysa bildirimleri al
$bildirimler = [];
$okunmamis_bildirim_sayisi = 0;
if (isset($_SESSION["kullanici_id"])) {
    $kullanici_id = $_SESSION["kullanici_id"];
    $bildirim_stmt = $pdo->prepare("SELECT * FROM bildirimler WHERE kullanici_id = ? ORDER BY tarih DESC");
    $bildirim_stmt->execute([$kullanici_id]);
    $bildirimler = $bildirim_stmt->fetchAll(PDO::FETCH_ASSOC);
    $okunmamis_bildirim_sayisi = count(array_filter($bildirimler, fn($b) => $b['goruldu'] == 0));
}

// İletişim formu gönderildiğinde
$mesaj_durum = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["iletisim_formu"])) {
    // Form verilerini al
    $ad_soyad = trim($_POST["ad_soyad"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $telefon = trim($_POST["telefon"] ?? '');
    $konu = trim($_POST["konu"] ?? '');
    $mesaj = trim($_POST["mesaj"] ?? '');
    
    // Basit bir doğrulama yapalım
    if (empty($ad_soyad) || empty($email) || empty($mesaj)) {
        $mesaj_durum = "error";
        $mesaj_icerik = "Lütfen ad-soyad, e-posta ve mesaj alanlarını doldurunuz.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj_durum = "error";
        $mesaj_icerik = "Lütfen geçerli bir e-posta adresi giriniz.";
    } else {
        // İletişim mesajını veritabanına kaydet
        try {
            $stmt = $pdo->prepare("INSERT INTO iletisim_mesajlari (ad_soyad, email, telefon, konu, mesaj, ip_adresi, tarih) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$ad_soyad, $email, $telefon, $konu, $mesaj, $_SERVER['REMOTE_ADDR']]);
            
            $mesaj_durum = "success";
            $mesaj_icerik = "Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.";
            
            // Form alanlarını temizle
            $ad_soyad = $email = $telefon = $konu = $mesaj = "";
            
        } catch (PDOException $e) {
            $mesaj_durum = "error";
            $mesaj_icerik = "Mesajınız gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
        }
    }
}


?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php include 'includes/header.php'; ?>




<link rel="stylesheet" href="css/styles.css">

<!-- Hero Section -->
<section class="page-header" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../img/page-header-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>İletişim</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">İletişim</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- İletişim Bilgileri -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-box h-100">
                    <div class="contact-icon" style="background-color: var(--accent-color);">
                        <i class="fas fa-map-marker-alt fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3>Adresimiz</h3>
                    <p>Atatürk Caddesi No:123<br>Merkez / İstanbul</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-box h-100">
                    <div class="contact-icon" style="background-color: var(--accent-color);">
                        <i class="fas fa-phone-alt fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3>Bizi Arayın</h3>
                    <p>Telefon: +90 212 345 67 89<br>Fax: +90 212 345 67 90</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-info-box h-100">
                    <div class="contact-icon" style="background-color: var(--accent-color);">
                        <i class="fas fa-envelope fa-2x" style="color: var(--primary-dark);"></i>
                    </div>
                    <h3>E-posta Adresimiz</h3>
                    <p>info@profesyonelemlak.com<br>satis@profesyonelemlak.com</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- İletişim Formu ve Harita Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="content-section">
                    <h2 class="content-title">Bize Mesaj Gönderin</h2>
                    <p class="content-text mb-4">Sorularınız, önerileriniz veya emlak danışmanlığı hizmetlerimiz hakkında bilgi almak için aşağıdaki formu doldurabilirsiniz. En kısa sürede size dönüş yapacağız.</p>
                    
                    <?php if ($mesaj_durum == "success"): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $mesaj_icerik; ?>
                    </div>
                    <?php elseif ($mesaj_durum == "error"): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $mesaj_icerik; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="contact-form">
                        <input type="hidden" name="iletisim_formu" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ad_soyad" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ad_soyad" name="ad_soyad" value="<?php echo htmlspecialchars($ad_soyad ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefon" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="telefon" name="telefon" value="<?php echo htmlspecialchars($telefon ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="konu" class="form-label">Konu</label>
                                <select class="form-select" id="konu" name="konu">
                                    <option value="Genel Bilgi" <?php echo isset($konu) && $konu == 'Genel Bilgi' ? 'selected' : ''; ?>>Genel Bilgi</option>
                                    <option value="Satılık Emlak" <?php echo isset($konu) && $konu == 'Satılık Emlak' ? 'selected' : ''; ?>>Satılık Emlak</option>
                                    <option value="Kiralık Emlak" <?php echo isset($konu) && $konu == 'Kiralık Emlak' ? 'selected' : ''; ?>>Kiralık Emlak</option>
                                    <option value="Yatırım Danışmanlığı" <?php echo isset($konu) && $konu == 'Yatırım Danışmanlığı' ? 'selected' : ''; ?>>Yatırım Danışmanlığı</option>
                                    <option value="Diğer" <?php echo isset($konu) && $konu == 'Diğer' ? 'selected' : ''; ?>>Diğer</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mesaj" class="form-label">Mesajınız <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="mesaj" name="mesaj" rows="5" required><?php echo htmlspecialchars($mesaj ?? ''); ?></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="kvkk" required>
                            <label class="form-check-label" for="kvkk">
                                <small>Kişisel verilerin korunması kapsamında <a href="#" data-bs-toggle="modal" data-bs-target="#kvkkModal">KVKK metnini</a> okudum ve kabul ediyorum. <span class="text-danger">*</span></small>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Mesaj Gönder
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="content-section">
                    <h2 class="content-title">Bize Ulaşın</h2>
                    <p class="content-text mb-4">Ofisimizi ziyaret etmek için aşağıdaki haritayı kullanabilirsiniz. Pazartesi'den Cumartesi'ye 09:00 - 18:00 saatleri arasında hizmet vermekteyiz.</p>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Çalışma Saatlerimiz</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span><i class="fas fa-clock me-2" style="color: var(--primary-color);"></i> Pazartesi - Cuma</span>
                                    <span>09:00 - 18:00</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span><i class="fas fa-clock me-2" style="color: var(--primary-color);"></i> Cumartesi</span>
                                    <span>10:00 - 15:00</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span><i class="fas fa-clock me-2" style="color: var(--primary-color);"></i> Pazar</span>
                                    <span class="text-danger">Kapalı</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="map-container mt-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.984858503263!2d28.97741801541608!3d41.037347679297204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab7650656bd63%3A0x8ca058b28c20b6c3!2zVGFrc2ltIE1leWRhbsSxLCBHw7xtw7zFn3N1eXUsIDM0NDM1IEJleW_En2x1L8Swc3RhbmJ1bA!5e0!3m2!1str!2str!4v1649080955311!5m2!1str!2str" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Şubelerimiz -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Şubelerimiz</h2>
                    <p>Türkiye genelindeki şubelerimizle hizmetinizdeyiz</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="branch-box">
                    <div class="branch-img">
                        <img src="img/branch-istanbul.jpg" alt="İstanbul Şubesi" class="img-fluid">
                        <div class="branch-overlay">
                            <a href="tel:+902123456789" class="btn btn-sm btn-light">
                                <i class="fas fa-phone-alt me-2"></i> Ara
                            </a>
                        </div>
                    </div>
                    <div class="branch-info">
                        <h4>İstanbul Merkez</h4>
                        <address>
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color);"></i> Atatürk Caddesi No:123, Merkez / İstanbul<br>
                            <i class="fas fa-phone-alt me-2" style="color: var(--primary-color);"></i> +90 212 345 67 89<br>
                            <i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i> istanbul@profesyonelemlak.com
                        </address>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="branch-box">
                    <div class="branch-img">
                        <img src="img/branch-ankara.jpg" alt="Ankara Şubesi" class="img-fluid">
                        <div class="branch-overlay">
                            <a href="tel:+903123456789" class="btn btn-sm btn-light">
                                <i class="fas fa-phone-alt me-2"></i> Ara
                            </a>
                        </div>
                    </div>
                    <div class="branch-info">
                        <h4>Ankara Şubesi</h4>
                        <address>
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color);"></i> Kızılay Meydanı No:45, Çankaya / Ankara<br>
                            <i class="fas fa-phone-alt me-2" style="color: var(--primary-color);"></i> +90 312 345 67 89<br>
                            <i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i> ankara@profesyonelemlak.com
                        </address>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="branch-box">
                    <div class="branch-img">
                        <img src="img/branch-izmir.jpg" alt="İzmir Şubesi" class="img-fluid">
                        <div class="branch-overlay">
                            <a href="tel:+902323456789" class="btn btn-sm btn-light">
                                <i class="fas fa-phone-alt me-2"></i> Ara
                            </a>
                        </div>
                    </div>
                    <div class="branch-info">
                        <h4>İzmir Şubesi</h4>
                        <address>
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color);"></i> Alsancak Limanı Cad. No:78, Konak / İzmir<br>
                            <i class="fas fa-phone-alt me-2" style="color: var(--primary-color);"></i> +90 232 345 67 89<br>
                            <i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i> izmir@profesyonelemlak.com
                        </address>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Sıkça Sorulan Sorular</h2>
                    <p>Emlak işlemleri hakkında merak edilenler</p>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Emlak alım-satım sürecinde hangi masraflar oluşur?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Emlak alım-satım sürecinde genellikle şu masraflar oluşur:</p>
                                <ul>
                                    <li>Emlak komisyonu (genellikle satış bedelinin %2-4'ü arasında)</li>
                                    <li>Tapu harcı (alıcı ve satıcı için ayrı ayrı hesaplanır)</li>
                                    <li>Döner sermaye harcı</li>
                                    <li>Eğer kredi kullanılacaksa ekspertiz ücreti ve kredi işlem masrafları</li>
                                    <li>KDV ve diğer vergiler (gayrimenkulün durumuna göre değişiklik gösterebilir)</li>
                                </ul>
                                <p>Detaylı bilgi için danışmanlarımızla iletişime geçebilirsiniz.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Gayrimenkul değerlemesi nasıl yapılır?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Gayrimenkul değerlemesi, bir mülkün piyasa değerinin belirlenmesi işlemidir. Bu değerleme şu faktörlere bakılarak yapılır:</p>
                                <ul>
                                    <li>Mülkün konumu ve bulunduğu semt</li>
                                    <li>Gayrimenkulün büyüklüğü, oda sayısı, yaşı ve durumu</li>
                                    <li>Bölgedeki benzer mülklerin satış fiyatları (emsal karşılaştırması)</li>
                                    <li>Altyapı ve ulaşım imkanları</li>
                                    <li>Çevredeki sosyal ve kültürel imkanlar</li>
                                    <li>İmar durumu ve gelecekteki potansiyel değeri</li>
                                </ul>
                                <p>Profesyonel Emlak olarak ücretsiz değerleme hizmeti sunmaktayız.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Emlak kredisi çekmek için ne gibi şartlar gerekiyor?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Emlak kredisi (konut kredisi) çekebilmek için genellikle şu şartlar aranır:</p>
                                <ul>
                                    <li>18 yaşını doldurmuş ve düzenli gelir sahibi olmak</li>
                                    <li>Kredi sicil kaydının olumlu olması</li>
                                    <li>Alınacak gayrimenkulün değerinin belirli bir oranında (genellikle %20-25) peşinat</li>
                                    <li>Bankaya verilecek teminatların yeterli olması</li>
                                    <li>Gelirin, kredi taksitlerini ödemeye yeterli olması</li>
                                </ul>
                                <p>Danışmanlarımız, size en uygun konut kredisi seçeneklerini bulmanızda yardımcı olabilir ve bankalardaki özel anlaşmalarımız sayesinde avantajlı faiz oranları sunabiliriz.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Tapu işlemleri ne kadar sürer?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Tapu devir işlemleri, gerekli tüm belgelerin hazır olması durumunda genellikle bir gün içinde tamamlanabilir. Ancak bazı durumlarda bu süre uzayabilir:</p>
                                <ul>
                                    <li>Tapu dairesinin yoğunluğu</li>
                                    <li>Gayrimenkul üzerinde şerh, ipotek veya haciz olması</li>
                                    <li>Belediye ve vergi dairesinden alınması gereken evrakların hazırlanma süresi</li>
                                    <li>Kredi kullanılacaksa bankanın işlem süreçleri</li>
                                </ul>
                                <p>Profesyonel Emlak olarak, tapu işlemlerinizin hızlı ve sorunsuz bir şekilde tamamlanması için tüm süreçte yanınızda oluyoruz.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Emlak yatırımı yapmak istiyorum, nereden başlamalıyım?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Emlak yatırımı yapmadan önce şu adımları izlemenizi öneririz:</p>
                                <ol>
                                    <li>Finansal durumunuzu değerlendirin ve yatırım bütçenizi belirleyin</li>
                                    <li>Yatırım amacınızı netleştirin (kısa vadeli kar, uzun vadeli değer artışı, kira geliri vb.)</li>
                                    <li>Piyasa araştırması yapın ve gelişmekte olan bölgeleri tespit edin</li>
                                    <li>Profesyonel bir emlak danışmanı ile çalışın</li>
                                    <li>Risklerinizi çeşitlendirin ve tüm yatırımınızı tek bir gayrimenkule bağlamayın</li>
                                    <li>Vergi avantajlarını ve yasal düzenlemeleri öğrenin</li>
                                </ol>
                                <p>Yatırım danışmanlığı hizmetimiz ile size özel stratejiler geliştirerek, en doğru emlak yatırımlarını yapmanıza yardımcı oluyoruz.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

<!-- KVKK Modal -->
<div class="modal fade" id="kvkkModal" tabindex="-1" aria-labelledby="kvkkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kvkkModalLabel">Kişisel Verilerin Korunması</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="mb-3">KİŞİSEL VERİLERİN KORUNMASI HAKKINDA AYDINLATMA METNİ</h5>
                <p>Bu aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, Profesyonel Emlak olarak veri sorumlusu sıfatıyla işleme amacı ve hukuki sebebi ile bağlantılı, sınırlı ve ölçülü şekilde kişisel verilerinizin işlenmesi ve aktarılması kapsamında sizleri bilgilendirmek amacıyla hazırlanmıştır.</p>
                
                <h6>1. Kişisel Verilerin İşlenme Amacı</h6>
                <p>Kişisel verileriniz aşağıdaki amaçlar doğrultusunda işlenmektedir:</p>
                <ul>
                    <li>İletişim faaliyetlerinin yürütülmesi</li>
                    <li>Müşteri ilişkileri yönetimi ve pazarlama süreçlerinin yürütülmesi</li>
                    <li>Hizmetlerimize ilişkin talep ve şikayetlerin değerlendirilmesi</li>
                    <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                    <li>İlgili mevzuat gereğince saklanması gereken bilgilerinizin muhafazası</li>
                    <li>Gerekli durumlarda kimlik bilgilerinizin teyit edilmesi</li>
                    <li>Web sitesi/mobil uygulamalarımız üzerinden sağlanan hizmetlerin iyileştirilmesi</li>
                </ul>
                
                <h6>2. Kişisel Verilerin Aktarılabileceği Taraflar ve Aktarım Amacı</h6>
                <p>Kişisel verileriniz, KVKK'nın 8. ve 9. maddelerinde belirtilen kişisel veri işleme şartları ve amaçları çerçevesinde, aşağıdaki taraflara aktarılabilecektir:</p>
                <ul>
                    <li>Yasal yetkiye sahip kamu kurumları ve özel kişiler</li>
                    <li>İş ortaklarımız, tedarikçilerimiz ve hizmet sağlayıcılarımız</li>
                    <li>Şirket faaliyetlerinin yürütülmesi için destek alınan hukuk, mali müşavirlik vb. danışmanlık firmaları</li>
                </ul>
                
                <h6>3. Kişisel Veri Toplamanın Yöntemi ve Hukuki Sebebi</h6>
                <p>Kişisel verileriniz, her türlü sözlü, yazılı, elektronik ortamda; web siteleri, mobil uygulamalar, e-posta, çağrı merkezi, sosyal medya mecraları ve benzeri vasıtalarla toplanabilmektedir. Kişisel verileriniz, KVKK'nın 5. ve 6. maddelerinde belirtilen kişisel veri işleme şartları ve amaçları kapsamında işlenebilmektedir.</p>
                
                <h6>4. KVKK Kapsamındaki Haklarınız</h6>
                <p>KVKK'nın 11. maddesi uyarınca, kişisel veri sahibi olarak aşağıdaki haklara sahipsiniz:</p>
                <ul>
                    <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
                    <li>Kişisel verileriniz işlenmişse buna ilişkin bilgi talep etme</li>
                    <li>Kişisel verilerinizin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
                    <li>Yurt içinde veya yurt dışında kişisel verilerinizin aktarıldığı üçüncü kişileri bilme</li>
                    <li>Kişisel verilerinizin eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme</li>
                    <li>Kişisel verilerinizin silinmesini veya yok edilmesini isteme</li>
                    <li>İşlenen verilerinizin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi suretiyle kişinin kendisi aleyhine bir sonucun ortaya çıkmasına itiraz etme</li>
                    <li>Kişisel verilerinizin kanuna aykırı olarak işlenmesi sebebiyle zarara uğraması hâlinde zararın giderilmesini talep etme</li>
                </ul>
                
                <p>Bu haklarınızı kullanmak için bizimle kvkk@profesyonelemlak.com e-posta adresi üzerinden iletişime geçebilirsiniz.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap ve özel scriptler -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form doğrulama için
        const contactForm = document.querySelector('.contact-form');
        
        if (contactForm) {
            contactForm.addEventListener('submit', function(event) {
                // KVKK onay kutusu kontrolü
                const kvkkCheckbox = document.getElementById('kvkk');
                if (!kvkkCheckbox.checked) {
                    event.preventDefault();
                    alert('Devam etmek için KVKK metnini kabul etmeniz gerekmektedir.');
                    return false;
                }
                
                // E-posta kontrolü
                const emailInput = document.getElementById('email');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    event.preventDefault();
                    alert('Lütfen geçerli bir e-posta adresi giriniz.');
                    emailInput.focus();
                    return false;
                }
            });
        }
        
        // Başarı mesajı otomatik kapanması
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.transition = 'opacity 1s';
                successAlert.style.opacity = '0';
                setTimeout(function() {
                    successAlert.remove();
                }, 1000);
            }, 5000);
        }
        
        // Accordion için manuel kontrol (eğer Bootstrap JS çalışmazsa)
        var accordionItems = document.querySelectorAll('.accordion-item');
        if (accordionItems.length > 0) {
            accordionItems.forEach(function(item) {
                var header = item.querySelector('.accordion-header');
                var collapse = item.querySelector('.accordion-collapse');
                
                header.addEventListener('click', function() {
                    var isExpanded = header.querySelector('.accordion-button').getAttribute('aria-expanded') === 'true';
                    
                    // Tüm accordion itemları kapat
                    document.querySelectorAll('.accordion-button').forEach(function(btn) {
                        btn.setAttribute('aria-expanded', 'false');
                        btn.classList.add('collapsed');
                    });
                    
                    document.querySelectorAll('.accordion-collapse').forEach(function(clps) {
                        clps.classList.remove('show');
                    });
                    
                    // Tıklanan accordion'ı aç/kapat
                    if (!isExpanded) {
                        header.querySelector('.accordion-button').setAttribute('aria-expanded', 'true');
                        header.querySelector('.accordion-button').classList.remove('collapsed');
                        collapse.classList.add('show');
                    }
                });
            });
        }
    });
</script>