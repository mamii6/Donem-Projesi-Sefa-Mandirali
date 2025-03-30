<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emlak Sitesi</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="contact-info">
                            <a href="tel:+905001234567"><i class="fas fa-phone"></i> +90 500 123 4567</a>
                            <a href="mailto:info@emlaksitesi.com"><i class="fas fa-envelope"></i> info@emlaksitesi.com</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="social-links">
                            <?php if (isset($_SESSION["kullanici_id"])): ?>
                                <a href="#" id="notificationLink" data-bs-toggle="modal" data-bs-target="#userAccountModal">
                                    <i class="fas fa-bell"></i> Bildirimler
                                    <?php if ($okunmamis_bildirim_sayisi > 0): ?>
                                        <span class="badge bg-danger"><?php echo $okunmamis_bildirim_sayisi; ?></span>
                                    <?php endif; ?>
                                </a>
                                <a href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION["ad"]); ?></a>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                            <?php else: ?>
                                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
                                <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="uploads/logo.png" alt="Logo" class="logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Anasayfa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="emlaklar.php">Emlaklar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="satilik.php">Satılık</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kiralik.php">Kiralık</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="iletisim.php">İletişim</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary" href="ilan-ver.php">İlan Ver</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>