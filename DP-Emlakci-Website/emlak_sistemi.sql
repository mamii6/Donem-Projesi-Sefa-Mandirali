CREATE DATABASE IF NOT EXISTS emlak_sistemi;
USE emlak_sistemi;

-- Kullanıcılar Tablosu
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    telefon VARCHAR(15),
    yetki ENUM('admin', 'kullanici') DEFAULT 'kullanici',
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İlanlar Tablosu
CREATE TABLE ilanlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    aciklama TEXT NOT NULL,
    fiyat DECIMAL(10,2) NOT NULL,
    adres VARCHAR(255) NOT NULL,
    oda_sayisi INT NOT NULL,
    metrekare INT NOT NULL,
    kullanici_id INT,
    eklenme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
);

-- Resimler Tablosu
CREATE TABLE ilan_resimler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ilan_id INT NOT NULL,
    resim_yolu VARCHAR(255) NOT NULL,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE
);

-- Favoriler Tablosu
CREATE TABLE favoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    ilan_id INT NOT NULL,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE
);

-- Mesajlar Tablosu
CREATE TABLE mesajlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gonderen_id INT NOT NULL,
    alici_id INT NOT NULL,
    ilan_id INT,
    mesaj TEXT NOT NULL,
    gonderilme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gonderen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (alici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE
);


