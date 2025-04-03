<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesyonel Emlak - Hayalinizdeki Evi Keşfedin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-wrapper">
                <!-- Sol taraf - Menü -->
                <div class="main-menu">
                    <ul class="menu-list">
                        <li><a href="index.php" class="active">Ana Sayfa</a></li>
                        <li><a href="emlaklar.php">Emlaklar</a></li>
                        <li><a href="hizmetler.php">Hizmetlerimiz</a></li>
                        <li><a href="hakkimizda.php">Hakkımızda</a></li>
                        <li><a href="iletisim.php">İletişim</a></li>
                    </ul>
                </div>
                
                <!-- Orta - Logo -->
                <div class="logo">
                    <a href="index.php">
                        <h1>MMSM<span>Emlak</span></h1>
                    </a>
                </div>
                
                <!-- Sağ taraf - Kullanıcı -->
                <div class="user-menu">
                    <?php if (isset($_SESSION["kullanici_id"])): ?>
                        <div class="dropdown">
                            <a class="user-dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> 
                                <?php 
                                // Kullanıcı adını al
                                $kullanici_id = $_SESSION["kullanici_id"];
                                $kullanici_sorgu = $pdo->prepare("SELECT ad FROM kullanicilar WHERE id = ?");
                                $kullanici_sorgu->execute([$kullanici_id]);
                                $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
                                
                                echo htmlspecialchars($kullanici["ad"] ?? "Kullanıcı"); 
                                ?>
                                <?php if (isset($toplam_okunmamis) && $toplam_okunmamis > 0): ?>
                                    <span class="badge rounded-pill bg-danger"><?php echo $toplam_okunmamis; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profil.php">Profilim</a></li>
                                <li><a class="dropdown-item" href="ilanlarim.php">İlanlarım</a></li>
                                <li><a class="dropdown-item" href="favoriler.php">Favorilerim</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#userAccountModal">
                                    Bildirimler
                                    <?php if (isset($okunmamis_bildirim_sayisi) && $okunmamis_bildirim_sayisi > 0): ?>
                                        <span class="badge rounded-pill bg-danger"><?php echo $okunmamis_bildirim_sayisi; ?></span>
                                    <?php endif; ?>
                                </a></li>
                                <li><a class="dropdown-item" href="mesajlar.php">
                                    Mesajlar
                                    <?php if (isset($okunmamis_mesaj_sayisi) && $okunmamis_mesaj_sayisi > 0): ?>
                                        <span class="badge rounded-pill bg-danger"><?php echo $okunmamis_mesaj_sayisi; ?></span>
                                    <?php endif; ?>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a class="btn btn-outline-light btn-sm me-2" href="login.php">Giriş Yap</a>
                            <a class="btn btn-primary btn-sm" href="register.php">Üye Ol</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobil Menü Butonu -->
                <div class="mobile-menu-toggle">
                    <button type="button" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Mobil Menü -->
    <div class="mobile-menu">
        <div class="container">
            <ul class="mobile-menu-list">
                <li><a href="index.php" class="active">Ana Sayfa</a></li>
                <li><a href="emlaklar.php">Emlaklar</a></li>
                <li><a href="hizmetler.php">Hizmetlerimiz</a></li>
                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                <li><a href="iletisim.php">İletişim</a></li>
            </ul>
        </div>
    </div>