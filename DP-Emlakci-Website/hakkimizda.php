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

// Header'ı dahil et
include 'includes/header.php';
?>
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/hakkimizda.css">

<style>
    .hero-small {
    padding: 300px 0 50px;
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('img/section5.jpg');
    background-size: cover;
    background-position: center;
    height: 700px;
}
</style>

<!-- Hero Section -->
<section class="page-header hero-small" >
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Hakkımızda</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Hakkımızda</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Introduction Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="img/hakkimizdas.jpg"alt="Emlak Şirketimiz" class="img-fluid rounded shadow-lg">
            </div>
            <div class="col-lg-6">
                <div class="content-section">
                    <h2 class="content-title">Emlak Sektöründe Güvenilir Adresiniz</h2>
                    <p class="lead mb-4">2010 yılında kurulan şirketimiz, emlak sektöründe müşteri memnuniyetini ön planda tutarak hizmet vermektedir.</p>
                    <p class="content-text">Profesyonel Emlak olarak 15 yıllık deneyimimizle, siz değerli müşterilerimize en kaliteli hizmeti sunmak için çalışıyoruz. Gayrimenkul alım, satım ve kiralama süreçlerini en şeffaf ve güvenilir şekilde yöneterek hayalinizdeki eve kavuşmanızı sağlıyoruz.</p>
                    <p class="content-text">Uzman kadromuz, geniş portföyümüz ve müşteri odaklı hizmet anlayışımızla, emlak sektöründe fark yaratmaya devam ediyoruz.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-4">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                        <h3 class="card-title h4 mb-3">Vizyonumuz</h3>
                        <p class="card-text">Türkiye genelinde güvenilir, şeffaf ve müşteri odaklı yaklaşımıyla tanınan öncü bir emlak şirketi olmak. Teknolojinin tüm imkanlarını kullanarak, emlak sektöründe yenilikçi çözümler sunmak ve müşterilerimizin beklentilerinin ötesinde bir deneyim yaşatmak için sürekli kendimizi geliştiriyoruz.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-4">
                            <i class="fas fa-bullseye fa-2x"></i>
                        </div>
                        <h3 class="card-title h4 mb-3">Misyonumuz</h3>
                        <p class="card-text">Müşterilerimizin ihtiyaçlarını en iyi şekilde anlayarak, doğru gayrimenkul çözümleri sunmak. Dürüstlük, güven ve profesyonellik ilkelerimizden ödün vermeden, müşterilerimize en değerli varlıkları olan gayrimenkulleri için en doğru kararları almalarında yardımcı olmak ve sektörde kalite standartlarını yükseltmek.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Değerlerimiz</h2>
                    <p>Çalışma prensiplerimizi şekillendiren temel değerlerimiz</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h3 class="h5">Dürüstlük ve Şeffaflık</h3>
                    <p>Tüm işlemlerimizde açık ve şeffaf olmayı, müşterilerimize karşı her zaman dürüst davranmayı ilke ediniyoruz.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-award fa-2x"></i>
                    </div>
                    <h3 class="h5">Kalite ve Güvenilirlik</h3>
                    <p>Her hizmetimizde en yüksek kaliteyi sunmayı ve müşterilerimizin güvenini kazanmayı hedefliyoruz.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h3 class="h5">Müşteri Odaklılık</h3>
                    <p>Müşterilerimizin ihtiyaç ve beklentilerini her zaman ön planda tutuyor, onlara özel çözümler sunuyoruz.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-lightbulb fa-2x"></i>
                    </div>
                    <h3 class="h5">Yenilikçilik</h3>
                    <p>Sektördeki yenilikleri takip ediyor, teknolojik gelişmeleri hizmetlerimize entegre ediyoruz.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-certificate fa-2x"></i>
                    </div>
                    <h3 class="h5">Uzmanlık</h3>
                    <p>Alanında uzman kadromuzla, emlak sektöründeki bilgi ve deneyimimizi müşterilerimizin hizmetine sunuyoruz.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hands-helping fa-2x"></i>
                    </div>
                    <h3 class="h5">Toplumsal Sorumluluk</h3>
                    <p>Çevreye ve topluma karşı sorumluluklarımızın farkındayız, sürdürülebilir çözümler üretiyoruz.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company History Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Şirket Tarihimiz</h2>
                    <p>Yıllar içinde gelişimimiz ve başarı hikayemiz</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="timeline-content right">
                                    <div class="timeline-date">2010</div>
                                    <h4 class="timeline-title">Kuruluş</h4>
                                    <p>Şirketimiz, tecrübeli emlak danışmanları tarafından İstanbul'da kuruldu. İlk ofisimizi merkezi bir lokasyonda açarak hizmet vermeye başladık.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6 offset-lg-6">
                                <div class="timeline-content left">
                                    <div class="timeline-date">2012</div>
                                    <h4 class="timeline-title">Büyüme ve Genişleme</h4>
                                    <p>Artan müşteri talebine cevap vermek için kadromuzu genişlettik ve ikinci ofisimizi açtık. Hizmet portföyümüzü geliştirerek kurumsal müşterilere de hizmet vermeye başladık.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="timeline-content right">
                                    <div class="timeline-date">2015</div>
                                    <h4 class="timeline-title">Dijitalleşme</h4>
                                    <p>Online platformumuzu hayata geçirerek dijital dönüşümümüzü başlattık. Müşterilerimize 7/24 hizmet verebilmek için teknolojik altyapımızı güçlendirdik.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6 offset-lg-6">
                                <div class="timeline-content left">
                                    <div class="timeline-date">2018</div>
                                    <h4 class="timeline-title">Ödül ve Başarılar</h4>
                                    <p>"En Güvenilir Emlak Şirketi" ödülünü kazandık. Müşteri memnuniyeti anketlerinde sektör ortalamasının üzerinde skorlar elde etmeye başladık.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="timeline-content right">
                                    <div class="timeline-date">2020</div>
                                    <h4 class="timeline-title">10. Yılımız</h4>
                                    <p>10. kuruluş yıldönümümüzü kutladık. Türkiye genelinde 5 şubeye ulaştık ve 50'den fazla emlak danışmanı ile müşterilerimize hizmet vermeye devam ettik.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="row">
                            <div class="col-lg-6 offset-lg-6">
                                <div class="timeline-content left">
                                    <div class="timeline-date">2025</div>
                                    <h4 class="timeline-title">Bugün</h4>
                                    <p>Yenilikçi yaklaşımımız ve müşteri odaklı hizmet anlayışımızla sektörde öncü konumumuzu sürdürüyor, binlerce müşterimize hayallerindeki eve kavuşma yolunda rehberlik ediyoruz.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Ekibimiz</h2>
                    <p>Profesyonel ve uzman kadromuzla hizmetinizdeyiz</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-box text-center">
                    <div class="team-img">
                        <img src="img/profilerkek.jpg" alt="Ekip Üyesi" class="img-fluid rounded-circle">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Muhammet MS Mandıralı</h4>
                    <p>Genel Müdür</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-box text-center">
                    <div class="team-img">
                        <img src="img/profilerkek.jpg" alt="Ekip Üyesi" class="img-fluid rounded-circle">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Ahmet Yüksek</h4>
                    <p>Satış Direktörü</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-box text-center">
                    <div class="team-img">
                        <img src="img/profilerkek.jpg" alt="Ekip Üyesi" class="img-fluid rounded-circle">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Metin Çoşkun</h4>
                    <p>Kıdemli Emlak Danışmanı</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-box text-center">
                    <div class="team-img">
                        <img src="img/profilerkek.jpg" alt="Ekip Üyesi" class="img-fluid rounded-circle">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Ali Veli</h4>
                    <p>Pazarlama Uzmanı</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stats-box text-center">
                    <div class="stats-icon">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <h3 class="counter">5000</h3>
                    <p>Satılan Emlak</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stats-box text-center">
                    <div class="stats-icon">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h3 class="counter">7500</h3>
                    <p>Mutlu Müşteri</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stats-box text-center">
                    <div class="stats-icon">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                    <h3 class="counter">5</h3>
                    <p>Şube Sayısı</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stats-box text-center">
                    <div class="stats-icon">
                        <i class="fas fa-award fa-2x"></i>
                    </div>
                    <h3 class="counter">15</h3>
                    <p>Yıllık Deneyim</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <div class="section-title">
                    <h2>Müşteri Yorumları</h2>
                    <p>Değerli müşterilerimizin bizimle ilgili düşünceleri</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Profesyonel Emlak ile çalışmak gerçekten çok keyifliydi. Hayalimdeki evi bulmamda büyük yardımları oldu. Emlak danışmanım her adımda yanımdaydı ve tüm sorularımı sabırla yanıtladı."</p>
                    <h4 class="testimonial-name">Muhammet MS Mandıralı</h4>
                    <p class="testimonial-position">Samsun</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Evimizi satmak için birçok emlakçı ile görüştük ancak en profesyonel yaklaşımı Profesyonel Emlak'tan gördük. Tüm süreç boyunca şeffaf ve bilgilendirici bir yaklaşım sergilediler."</p>
                    <h4 class="testimonial-name">Ahmet Yüksek</h4>
                    <p class="testimonial-position">Antalya</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-box">
                    <div class="testimonial-img">
                        <img src="img/profilerkek.jpg" alt="Müşteri Yorumu">
                    </div>
                    <p class="testimonial-text">"Yatırım amaçlı bir daire arıyordum ve Profesyonel Emlak sayesinde hem bütçeme uygun hem de yüksek getiri potansiyeli olan bir daire buldum. Danışmanlık hizmetleri gerçekten çok değerliydi."</p>
                    <h4 class="testimonial-name">Metin Çoşkun</h4>
                    <p class="testimonial-position">Ankara</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../img/cta-bg.jpg');">
    <div class="container">
        <div class="cta-content">
            <h2>Hayalinizdeki Eve Kavuşmak İçin Bize Ulaşın</h2>
            <p>Profesyonel ekibimizle size en uygun gayrimenkul çözümlerini sunmak için hazırız.</p>
            <div class="mt-4">
                <a href="iletisim.php" class="btn btn-primary me-2">İletişime Geçin</a>
                <a href="emlaklar.php" class="btn btn-outline-light">Emlakları Keşfedin</a>
            </div>
        </div>
    </div>
</section>

<!-- User Account Modal for Notifications -->
<?php if (isset($_SESSION["kullanici_id"])): ?>
<div class="modal fade" id="userAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bildirimler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($bildirimler)): ?>
                    <p class="text-muted">Bildirim yok</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($bildirimler as $bildirim): ?>
                            <li class="list-group-item <?php echo $bildirim['goruldu'] ? '' : 'fw-bold'; ?>">
                                <a href="bildirim_detay.php?id=<?php echo $bildirim['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($bildirim["mesaj"]); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date("d.m.Y H:i", strtotime($bildirim["tarih"])); ?>
                                    </small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script>
// Sayaç animasyonu için script
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    
    // IntersectionObserver ile görünüme girdiğinde animasyonu başlat
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.textContent);
                let count = 0;
                const duration = 2500; // 2.5 saniye
                const interval = Math.floor(duration / target);
                
                const timer = setInterval(() => {
                    count += 1;
                    counter.textContent = count;
                    
                    if (count >= target) {
                        clearInterval(timer);
                    }
                }, interval);
                
                // Bu elemanı bir daha gözlemleme
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    // Tüm sayaçları gözlemle
    counters.forEach(counter => {
        observer.observe(counter);
    });
});
</script>