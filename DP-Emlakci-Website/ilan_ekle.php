<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $baslik = trim($_POST["baslik"]);
    $aciklama = trim($_POST["aciklama"]);
    $fiyat = floatval($_POST["fiyat"]);
    $adres = trim($_POST["adres"]);
    $oda_sayisi = intval($_POST["oda_sayisi"]);
    $metrekare = intval($_POST["metrekare"]);
    $kullanici_id = $_SESSION["kullanici_id"];
    $resimAdi = null;

    // Resim yükleme işlemi
    if (!empty($_FILES["resim"]["name"])) {
        $dosya = $_FILES["resim"];
        $uzanti = strtolower(pathinfo($dosya["name"], PATHINFO_EXTENSION));
        $izinVerilenUzantilar = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($uzanti, $izinVerilenUzantilar)) {
            $message = "<div class='alert alert-danger'>Sadece JPG, JPEG, PNG veya GIF formatında resim yükleyebilirsiniz!</div>";
        } elseif ($dosya["size"] > 2 * 1024 * 1024) { // 2MB sınırı
            $message = "<div class='alert alert-danger'>Dosya boyutu 2MB'den büyük olamaz!</div>";
        } else {
            $resimAdi = time() . "_" . uniqid() . "." . $uzanti;
            $hedefKlasor = "uploads/ilanlar/";
            if (!is_dir($hedefKlasor)) {
                mkdir($hedefKlasor, 0777, true);
            }
            $hedefYol = $hedefKlasor . $resimAdi;

            if (move_uploaded_file($dosya["tmp_name"], $hedefYol)) {
                $stmt = $pdo->prepare("INSERT INTO ilanlar (baslik, aciklama, fiyat, adres, oda_sayisi, metrekare, kullanici_id, resim) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$baslik, $aciklama, $fiyat, $adres, $oda_sayisi, $metrekare, $kullanici_id, $resimAdi])) {
                    $message = "<div class='alert alert-success'>İlan başarıyla eklendi!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>İlan eklenirken hata oluştu!</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Resim yüklenirken hata oluştu!</div>";
            }
        }
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Yeni İlan Ekle</h1>
        <div class="breadcrumb">
            <a href="index.php">Ana Sayfa</a> / Yeni İlan Ekle
        </div>
    </div>
</div>

<!-- Form Section -->
<div class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-box">
                    <?php echo $message; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label required-field">İlan Başlığı</label>
                                    <input type="text" name="baslik" class="form-control" placeholder="Örn: Deniz Manzaralı 3+1 Daire" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label required-field">Açıklama</label>
                                    <textarea name="aciklama" class="form-control" rows="5" placeholder="İlanınızla ilgili detaylı bilgi verin" required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label required-field">Fiyat (₺)</label>
                                    <input type="number" step="0.01" name="fiyat" class="form-control" placeholder="Fiyat" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label required-field">Adres</label>
                                    <input type="text" name="adres" class="form-control" placeholder="Tam adres" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label required-field">Oda Sayısı</label>
                                    <select name="oda_sayisi" class="form-control" required>
                                        <option value="">Oda Sayısı Seçin</option>
                                        <option value="1">1+0</option>
                                        <option value="2">1+1</option>
                                        <option value="3">2+1</option>
                                        <option value="4">3+1</option>
                                        <option value="5">4+1</option>
                                        <option value="6">5+1 veya daha fazla</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label required-field">Metrekare</label>
                                    <input type="number" name="metrekare" class="form-control" placeholder="m²" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label required-field">İlan Görseli</label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <h4>Fotoğraf Yükleyin</h4>
                                        <p>En fazla 2MB boyutunda JPG, JPEG, PNG veya GIF formatında</p>
                                        <input type="file" name="resim" id="fileInput" class="d-none" accept="image/*" required>
                                    </div>
                                    <div id="preview" class="mt-3 d-none">
                                        <div class="preview-image-container">
                                            <img id="previewImage" src="#" alt="Önizleme" class="img-fluid">
                                            <button type="button" id="removeImage" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <button type="submit" class="btn btn-primary w-100">İlanı Yayınla</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer'ı dahil et -->
<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const previewImage = document.getElementById('previewImage');
        const removeImage = document.getElementById('removeImage');
        
        // Yükleme alanına tıklanınca dosya seçiciyi aç
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Dosya seçildiğinde
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.classList.remove('d-none');
                    uploadArea.classList.add('d-none');
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Resmi kaldır butonuna tıklanınca
        removeImage.addEventListener('click', function() {
            fileInput.value = '';
            preview.classList.add('d-none');
            uploadArea.classList.remove('d-none');
        });
    });
</script>