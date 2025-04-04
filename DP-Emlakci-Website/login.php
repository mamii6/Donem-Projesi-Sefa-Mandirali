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

// Giriş formu gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["giris_yap"])) {
    $email = trim($_POST["email"]);
    $sifre = trim($_POST["sifre"]);
    $hatirla = isset($_POST["hatirla"]) ? true : false;

    if (empty($email) || empty($sifre)) {
        $hata_mesaji = "Lütfen e-posta ve şifre alanlarını doldurun.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE email = ?");
            $stmt->execute([$email]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($kullanici && password_verify($sifre, $kullanici["sifre"])) {
                // Giriş başarılı, oturum bilgilerini kaydet
                $_SESSION["kullanici_id"] = $kullanici["id"];
                $_SESSION["ad"] = $kullanici["ad"];
                $_SESSION["soyad"] = $kullanici["soyad"];
                $_SESSION["email"] = $kullanici["email"];
                
                // Kullanıcıyı hatırla (30 gün)
                if ($hatirla) {
                    $selector = bin2hex(random_bytes(8));
                    $token = random_bytes(32);
                    
                    $expires = date('U') + 60 * 60 * 24 * 30; // 30 gün
                    
                    // Veritabanına token kaydet
                    $hash_token = password_hash($token, PASSWORD_DEFAULT);
                    
                    $delete_stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE kullanici_id = ?");
                    $delete_stmt->execute([$kullanici["id"]]);
                    
                    $insert_stmt = $pdo->prepare("INSERT INTO auth_tokens (kullanici_id, selector, token, expires) VALUES (?, ?, ?, ?)");
                    $insert_stmt->execute([$kullanici["id"], $selector, $hash_token, $expires]);
                    
                    // Cookie'leri ayarla
                    setcookie("kullanici_selector", $selector, $expires, "/");
                    setcookie("kullanici_validator", bin2hex($token), $expires, "/");
                }
                
                // Giriş tarihini güncelle
                $update_stmt = $pdo->prepare("UPDATE kullanicilar SET son_giris = NOW() WHERE id = ?");
                $update_stmt->execute([$kullanici["id"]]);
                
                // Yönlendirme öncesi bildirimi ayarla
                $success_mesaji = "Giriş başarılı. Yönlendiriliyorsunuz...";
                
                // Sayfayı yönlendir (setTimeout ile JavaScript tarafında yapılacak)
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 1500);
                </script>';
            } else {
                $hata_mesaji = "Geçersiz e-posta veya şifre!";
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Bir hata oluştu, lütfen daha sonra tekrar deneyin.";
        }
    }
}

// Parola sıfırlama isteği
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sifremi_unuttum"])) {
    $email = trim($_POST["reset_email"]);
    
    if (empty($email)) {
        $hata_mesaji = "Lütfen e-posta adresinizi girin.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, ad, email FROM kullanicilar WHERE email = ?");
            $stmt->execute([$email]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($kullanici) {
                // Token oluştur
                $selector = bin2hex(random_bytes(8));
                $token = random_bytes(32);
                $hash_token = password_hash($token, PASSWORD_DEFAULT);
                $expires = date('U') + 3600; // 1 saat
                
                // Eski token'ları temizle
                $delete_stmt = $pdo->prepare("DELETE FROM sifre_sifirlama WHERE email = ?");
                $delete_stmt->execute([$email]);
                
                // Yeni token'ı kaydet
                $insert_stmt = $pdo->prepare("INSERT INTO sifre_sifirlama (email, selector, token, expires) VALUES (?, ?, ?, ?)");
                $insert_stmt->execute([$email, $selector, $hash_token, $expires]);
                
                // Sıfırlama URL'sini oluştur
                $url = "https://example.com/sifre_yenile.php?selector=" . urlencode($selector) . "&validator=" . urlencode(bin2hex($token));
                
                // E-posta gönder (burada gerçek e-posta gönderme kodu olacak)
                // mail($email, "Şifre Sıfırlama Talebi", "Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:\n\n" . $url, "From: info@example.com");
                
                $success_mesaji = "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.";
            } else {
                // Kullanıcı bulunamadığını belli etme (güvenlik nedeniyle)
                $success_mesaji = "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.";
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Bir hata oluştu, lütfen daha sonra tekrar deneyin.";
        }
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/login.css">

<!-- Hero Section -->
<section class="page-header" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../img/page-header-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Giriş Yap</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Giriş Yap</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Login Section -->
<section class="login-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="login-form-container">
                    <div class="login-header">
                        <h2 class="section-title mb-0">Hesabınıza Giriş Yapın</h2>
                        <p class="login-subtitle">Emlak ilanlarını görüntülemek, ilan vermek ve favori ilanlarınızı takip etmek için giriş yapın.</p>
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
                    
                    <form method="post" action="" class="login-form" id="loginForm">
                        <input type="hidden" name="giris_yap" value="1">
                        <div class="mb-4">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ornek@email.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="sifre" class="form-label">Şifre</label>
                                <a href="#" class="forgot-password-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Şifremi Unuttum</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="sifre" name="sifre" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="hatirla" name="hatirla">
                                <label class="form-check-label" for="hatirla">
                                    Beni Hatırla
                                </label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                            </button>
                        </div>
                    </form>
                    
                    <div class="social-login-separator">
                        <span>veya</span>
                    </div>
                    
                    <div class="social-login-buttons">
                        <a href="#" class="btn btn-outline-primary social-login-btn facebook-btn">
                            <i class="fab fa-facebook-f me-2"></i>Facebook ile Giriş Yap
                        </a>
                        <a href="#" class="btn btn-outline-danger social-login-btn google-btn">
                            <i class="fab fa-google me-2"></i>Google ile Giriş Yap
                        </a>
                    </div>
                    
                    <div class="register-link-container">
                        Hesabınız yok mu? <a href="register.php" class="register-link">Hemen Kaydolun</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Şifremi Unuttum Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Şifremi Unuttum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p>Şifrenizi sıfırlamak için e-posta adresinizi girin. Size şifre sıfırlama bağlantısı göndereceğiz.</p>
                <form method="post" action="" id="forgotPasswordForm">
                    <input type="hidden" name="sifremi_unuttum" value="1">
                    <div class="mb-3">
                        <label for="reset_email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="reset_email" name="reset_email" placeholder="ornek@email.com" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Sıfırlama Bağlantısı Gönder
                        </button>
                    </div>
                </form>
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
    
    // Form doğrulama
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('sifre');
            
            let isValid = true;
            
            // E-posta kontrolü
            if (!emailInput.value || !emailInput.value.includes('@')) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            }
            
            // Şifre kontrolü
            if (!passwordInput.value || passwordInput.value.length < 6) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            } else {
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
    
    // Şifremi unuttum formunu modal kapatıldığında temizle
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    if (forgotPasswordModal) {
        forgotPasswordModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('forgotPasswordForm').reset();
        });
    }
    
    // Animasyon efektleri
    const loginContainer = document.querySelector('.login-form-container');
    if (loginContainer) {
        loginContainer.classList.add('animate-fade-in');
    }
});
</script>

<?php include 'includes/footer.php'; ?>