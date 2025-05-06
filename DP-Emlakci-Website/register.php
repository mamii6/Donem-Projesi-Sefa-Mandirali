<?php
require_once "db.php";
session_start();

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION["kullanici_id"])) {
    header("Location: index.php");
    exit();
}

$hata_mesaji = "";
$success_mesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST["ad"]);
    $soyad = trim($_POST["soyad"]);
    $email = trim($_POST["email"]);
    $telefon = trim($_POST["telefon"]);
    $dogum_tarihi = $_POST["dogum_tarihi"];
    $cinsiyet = $_POST["cinsiyet"];
    $sifre = $_POST["sifre"];
    $sifre_tekrar = $_POST["sifre_tekrar"];
    $kvkk_onay = isset($_POST["kvkk_onay"]) ? 1 : 0;
    
    // Temel doğrulama
    if (empty($ad) || empty($soyad) || empty($email) || empty($sifre) || empty($sifre_tekrar) || empty($telefon) || empty($dogum_tarihi) || empty($cinsiyet)) {
        $hata_mesaji = "Lütfen tüm zorunlu alanları doldurun!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata_mesaji = "Lütfen geçerli bir e-posta adresi girin!";
    } elseif ($sifre !== $sifre_tekrar) {
        $hata_mesaji = "Girdiğiniz şifreler eşleşmiyor!";
    } elseif (strlen($sifre) < 6) {
        $hata_mesaji = "Şifreniz en az 6 karakter uzunluğunda olmalıdır!";
    } elseif (!$kvkk_onay) {
        $hata_mesaji = "Devam etmek için KVKK metnini kabul etmeniz gerekmektedir.";
    } else {
        // E-posta adresinin daha önce kullanılıp kullanılmadığını kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kullanicilar WHERE email = ?");
        $stmt->execute([$email]);
        $email_count = $stmt->fetchColumn();
        
        if ($email_count > 0) {
            $hata_mesaji = "Bu e-posta adresi zaten kullanılıyor. Lütfen başka bir e-posta adresi deneyin.";
        } else {
            // Şifreyi hash'le
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
            
            try {
                // Telefon numarasından boşlukları kaldır
                $telefon = str_replace(' ', '', $telefon);
                
                // Kullanıcıyı veritabanına ekle - son_giris kaldırıldı
                $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, soyad, email, telefon, dogum_tarihi, cinsiyet, sifre, kayit_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$ad, $soyad, $email, $telefon, $dogum_tarihi, $cinsiyet, $sifre_hash]);
                
                // Kullanıcının ID'sini al
                $kullanici_id = $pdo->lastInsertId();
                
                // Başarı mesajı
                $success_mesaji = "Kayıt işleminiz başarıyla tamamlandı! Hesabınıza giriş yapabilirsiniz.";
                
                // Yönlendirme öncesi bildirimi ayarla
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 2000);
                </script>';
            } catch (PDOException $e) {
                $hata_mesaji = "Kayıt sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
                // Hata kaydı
                error_log($e->getMessage());
            }
        }
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/register.css">

<!-- Hero Section -->
<section class="page-header" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../img/page-header-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Kayıt Ol</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Kayıt Ol</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Register Section -->
<section class="register-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="register-form-container">
                    <div class="register-header">
                        <h2 class="section-title mb-0">Yeni Hesap Oluştur</h2>
                        <p class="register-subtitle">Emlak portalımıza üye olarak ilan verebilir, favori ilanlarınızı kaydedebilir ve çok daha fazlasına erişebilirsiniz.</p>
                    </div>
                    
                    <?php if (!empty($hata_mesaji)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $hata_mesaji; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_mesaji)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success_mesaji; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" class="register-form" id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ad" class="form-label">Adınız <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="ad" name="ad" placeholder="Adınızı girin" value="<?php echo isset($ad) ? htmlspecialchars($ad) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="soyad" class="form-label">Soyadınız <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="soyad" name="soyad" placeholder="Soyadınızı girin" value="<?php echo isset($soyad) ? htmlspecialchars($soyad) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-posta Adresi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="ornek@email.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>
                                <div class="form-text">E-posta adresiniz giriş yaparken kullanılacaktır.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefon" class="form-label">Telefon Numarası <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" placeholder="05XX XXX XX XX" value="<?php echo isset($telefon) ? htmlspecialchars($telefon) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dogum_tarihi" class="form-label">Doğum Tarihi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="dogum_tarihi" name="dogum_tarihi" value="<?php echo isset($dogum_tarihi) ? htmlspecialchars($dogum_tarihi) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cinsiyet" class="form-label">Cinsiyet <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-venus-mars"></i></span>
                                    <select class="form-select" id="cinsiyet" name="cinsiyet" required>
                                        <option value="" disabled selected>Cinsiyet Seçiniz</option>
                                        <option value="Erkek" <?php echo (isset($cinsiyet) && $cinsiyet == 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
                                        <option value="Kadın" <?php echo (isset($cinsiyet) && $cinsiyet == 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
                                        <option value="Belirtmek İstemiyorum" <?php echo (isset($cinsiyet) && $cinsiyet == 'Belirtmek İstemiyorum') ? 'selected' : ''; ?>>Belirtmek İstemiyorum</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sifre" class="form-label">Şifre <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="sifre" name="sifre" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Şifreniz en az 6 karakter uzunluğunda olmalıdır.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sifre_tekrar" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary toggle-password-confirm" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="password-strength">
                                <div class="strength-label">Şifre Gücü: <span id="strength-text">Çok Zayıf</span></div>
                                <div class="strength-meter">
                                    <div class="strength-meter-fill" id="strength-meter-fill"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="kvkk_onay" name="kvkk_onay" required>
                                <label class="form-check-label" for="kvkk_onay">
                                    <small>Kişisel verilerin korunması kapsamında <a href="#" data-bs-toggle="modal" data-bs-target="#kvkkModal">KVKK metnini</a> okudum ve kabul ediyorum. <span class="text-danger">*</span></small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Ücretsiz Üye Ol
                            </button>
                        </div>
                    </form>
                    
                    <div class="social-login-separator">
                        <span>veya</span>
                    </div>
                    
                    <div class="social-login-buttons">
                        <a href="#" class="btn btn-outline-primary social-login-btn facebook-btn">
                            <i class="fab fa-facebook-f me-2"></i>Facebook ile Kayıt Ol
                        </a>
                        <a href="#" class="btn btn-outline-danger social-login-btn google-btn">
                            <i class="fab fa-google me-2"></i>Google ile Kayıt Ol
                        </a>
                    </div>
                    
                    <div class="login-link-container">
                        Zaten hesabınız var mı? <a href="login.php" class="login-link">Giriş Yapın</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- KVKK Modal -->
<div class="modal fade" id="kvkkModal" tabindex="-1" aria-labelledby="kvkkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kvkkModalLabel">Kişisel Verilerin Korunması</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
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

<!-- İşlevsellik için JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Şifre göster/gizle
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.querySelector('#sifre');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // İkon değiştir
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Şifre tekrarı göster/gizle
    const togglePasswordConfirm = document.querySelector('.toggle-password-confirm');
    const passwordConfirmInput = document.querySelector('#sifre_tekrar');
    
    if (togglePasswordConfirm) {
        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
            
            // İkon değiştir
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Şifre gücü kontrolü
    const passwordField = document.getElementById('sifre');
    const strengthMeter = document.getElementById('strength-meter-fill');
    const strengthText = document.getElementById('strength-text');
    
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            let strengthPercentage = 0;
            let strengthLabel = '';
            let strengthColor = '';
            
            if (strength === 0) {
                strengthPercentage = 0;
                strengthLabel = 'Çok Zayıf';
                strengthColor = '#ff4444';
            } else if (strength === 1) {
                strengthPercentage = 25;
                strengthLabel = 'Zayıf';
                strengthColor = '#ffbb33';
            } else if (strength === 2) {
                strengthPercentage = 50;
                strengthLabel = 'Orta';
                strengthColor = '#ffbb33';
            } else if (strength === 3) {
                strengthPercentage = 75;
                strengthLabel = 'İyi';
                strengthColor = '#00C851';
            } else {
                strengthPercentage = 100;
                strengthLabel = 'Güçlü';
                strengthColor = '#007E33';
            }
            
            strengthMeter.style.width = strengthPercentage + '%';
            strengthMeter.style.backgroundColor = strengthColor;
            strengthText.textContent = strengthLabel;
            strengthText.style.color = strengthColor;
        });
    }
    
    // Şifre gücünü ölçme fonksiyonu
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length === 0) {
            return strength;
        }
        
        // Uzunluk kontrolü
        if (password.length >= 6) {
            strength += 1;
        }
        
        // Karışık karakter kontrolü
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
            strength += 1;
        }
        
        // Sayı kontrolü
        if (password.match(/\d/)) {
            strength += 1;
        }
        
        // Özel karakter kontrolü
        if (password.match(/[^a-zA-Z\d]/)) {
            strength += 1;
        }
        
        return strength;
    }
    
    // Form doğrulama
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const password = document.getElementById('sifre').value;
            const confirmPassword = document.getElementById('sifre_tekrar').value;
            const kvkkCheck = document.getElementById('kvkk_onay').checked;
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('telefon');
            
            let isValid = true;
            
            // E-posta kontrolü
            if (!emailInput.value || !emailInput.value.includes('@')) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            }
            
            // Telefon numarası formatı kontrolü (basit bir Türkiye telefon numarası kontrolü)
            const phoneRegex = /^(05)[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/;
            if (!phoneInput.value || !phoneRegex.test(phoneInput.value.replace(/\s/g, ''))) {
                phoneInput.classList.add('is-invalid');
                isValid = false;
            } else {
                phoneInput.classList.remove('is-invalid');
                phoneInput.classList.add('is-valid');
            }
            
            // Şifre kontrolü
            if (password.length < 6) {
                document.getElementById('sifre').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('sifre').classList.remove('is-invalid');
                document.getElementById('sifre').classList.add('is-valid');
            }
            
            // Şifre eşleşme kontrolü
            if (password !== confirmPassword) {
                document.getElementById('sifre_tekrar').classList.add('is-invalid');
                isValid = false;
            } else if (confirmPassword.length > 0) {
                document.getElementById('sifre_tekrar').classList.remove('is-invalid');
                document.getElementById('sifre_tekrar').classList.add('is-valid');
            }
            
            // KVKK onayı kontrolü
            if (!kvkkCheck) {
                document.getElementById('kvkk_onay').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('kvkk_onay').classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
    
    // Telefon numarası formatı
    const phoneInput = document.getElementById('telefon');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let input = e.target.value.replace(/\D/g, ''); // Sadece rakamları al
            
            if (input.length > 0) {
                // 0 ile başlamasını sağla
                if (input[0] !== '0') {
                    input = '0' + input;
                }
                
                // Telefon numarası formatı
                let formatted = '';
                if (input.length <= 4) {
                    formatted = input;
                } else if (input.length <= 7) {
                    formatted = input.slice(0, 4) + ' ' + input.slice(4);
                } else if (input.length <= 9) {
                    formatted = input.slice(0, 4) + ' ' + input.slice(4, 7) + ' ' + input.slice(7);
                } else {
                    formatted = input.slice(0, 4) + ' ' + input.slice(4, 7) + ' ' + input.slice(7, 9) + ' ' + input.slice(9, 11);
                }
                
                e.target.value = formatted;
            }
        });
    }
    
    // Animasyon efektleri
    const registerContainer = document.querySelector('.register-form-container');
    if (registerContainer) {
        registerContainer.classList.add('animate-fade-in');
    }
});
</script>

<?php include 'includes/footer.php'; ?>