-- Akademik Başvuru Sistemi Veritabanı
-- Kocaeli Üniversitesi Akademik Personel Başvuru Sistemi

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Veritabanı: `akademik_basvuru`
--
CREATE DATABASE IF NOT EXISTS `akademik_basvuru` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `akademik_basvuru`;

-- --------------------------------------------------------

--
-- Tablo yapısı: `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tc_kimlik_no` varchar(11) NOT NULL,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` enum('aday','admin','yonetici','juri') NOT NULL DEFAULT 'aday',
  `durum` tinyint(1) NOT NULL DEFAULT 1,
  `kayit_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `son_giris_tarihi` datetime DEFAULT NULL,
  `dogum_tarihi` date DEFAULT NULL,
  `dogum_yeri` varchar(100) DEFAULT NULL,
  `cinsiyet` enum('Erkek','Kadın','Belirtilmemiş') DEFAULT 'Belirtilmemiş',
  `adres` text DEFAULT NULL,
  `il` varchar(50) DEFAULT NULL,
  `ilce` varchar(50) DEFAULT NULL,
  `posta_kodu` varchar(10) DEFAULT NULL,
  `unvan` varchar(100) DEFAULT NULL,
  `kurum` varchar(255) DEFAULT NULL,
  `profil_resmi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `ilanlar`
--

CREATE TABLE `ilanlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ilan_baslik` varchar(255) NOT NULL,
  `fakulte_birim` varchar(255) NOT NULL,
  `bolum` varchar(255) NOT NULL,
  `anabilim_dali` varchar(255) NOT NULL,
  `kadro_unvani` varchar(100) NOT NULL,
  `kadro_sayisi` int(11) NOT NULL DEFAULT 1,
  `ilan_aciklama` text DEFAULT NULL,
  `basvuru_kosullari` text DEFAULT NULL,
  `ozel_sartlar` text DEFAULT NULL,
  `ilan_baslangic_tarihi` date NOT NULL,
  `ilan_bitis_tarihi` date NOT NULL,
  `mulakat_tarihi` date DEFAULT NULL,
  `sonuc_tarihi` date DEFAULT NULL,
  `durum` enum('taslak','aktif','kapandi','iptal') NOT NULL DEFAULT 'aktif',
  `olusturan_id` int(11) NOT NULL,
  `olusturma_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `guncelleyen_id` int(11) DEFAULT NULL,
  `guncelleme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `olusturan_id` (`olusturan_id`),
  KEY `guncelleyen_id` (`guncelleyen_id`),
  CONSTRAINT `ilanlar_ibfk_1` FOREIGN KEY (`olusturan_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ilanlar_ibfk_2` FOREIGN KEY (`guncelleyen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `ilan_kriterleri`
--

CREATE TABLE `ilan_kriterleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ilan_id` int(11) NOT NULL,
  `kriter_adi` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `minimum_deger` varchar(50) DEFAULT NULL,
  `agirlik` decimal(5,2) DEFAULT 1.00,
  `kategori` varchar(100) DEFAULT NULL,
  `siralama` int(11) DEFAULT 0,
  `ekleyen_id` int(11) NOT NULL,
  `eklenme_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `guncelleme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ilan_id` (`ilan_id`),
  KEY `ekleyen_id` (`ekleyen_id`),
  CONSTRAINT `ilan_kriterleri_ibfk_1` FOREIGN KEY (`ilan_id`) REFERENCES `ilanlar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ilan_kriterleri_ibfk_2` FOREIGN KEY (`ekleyen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `basvurular`
--

CREATE TABLE `basvurular` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `ozgecmis` varchar(255) DEFAULT NULL,
  `diploma` varchar(255) DEFAULT NULL,
  `yayinlar` varchar(255) DEFAULT NULL,
  `dil_belgesi` varchar(255) DEFAULT NULL,
  `diger_belgeler` varchar(255) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `durum` enum('Beklemede','Onaylandı','Reddedildi') NOT NULL DEFAULT 'Beklemede',
  `basvuru_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `mulakat_notu` decimal(5,2) DEFAULT NULL,
  `mulakat_tarihi` datetime DEFAULT NULL,
  `mulakat_yeri` varchar(255) DEFAULT NULL,
  `nihai_puan` decimal(5,2) DEFAULT NULL,
  `sonuc_aciklama` text DEFAULT NULL,
  `red_nedeni` text DEFAULT NULL,
  `degerlendirme_tarihi` datetime DEFAULT NULL,
  `nihai_karar_tarihi` datetime DEFAULT NULL,
  `nihai_karar_veren_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  KEY `ilan_id` (`ilan_id`),
  KEY `nihai_karar_veren_id` (`nihai_karar_veren_id`),
  CONSTRAINT `basvurular_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `basvurular_ibfk_2` FOREIGN KEY (`ilan_id`) REFERENCES `ilanlar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `basvurular_ibfk_3` FOREIGN KEY (`nihai_karar_veren_id`) REFERENCES `kullanicilar` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `basvuru_kriterleri`
--

CREATE TABLE `basvuru_kriterleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basvuru_id` int(11) NOT NULL,
  `kriter_id` int(11) NOT NULL,
  `deger` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `basvuru_id` (`basvuru_id`),
  KEY `kriter_id` (`kriter_id`),
  CONSTRAINT `basvuru_kriterleri_ibfk_1` FOREIGN KEY (`basvuru_id`) REFERENCES `basvurular` (`id`) ON DELETE CASCADE,
  CONSTRAINT `basvuru_kriterleri_ibfk_2` FOREIGN KEY (`kriter_id`) REFERENCES `ilan_kriterleri` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `juri_atamalari`
--

CREATE TABLE `juri_atamalari` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ilan_id` int(11) NOT NULL,
  `juri_id` int(11) NOT NULL,
  `atama_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `atayan_id` int(11) NOT NULL,
  `durum` enum('aktif','pasif') NOT NULL DEFAULT 'aktif',
  `tamamlanma_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ilan_juri_unique` (`ilan_id`,`juri_id`),
  KEY `juri_id` (`juri_id`),
  KEY `atayan_id` (`atayan_id`),
  CONSTRAINT `juri_atamalari_ibfk_1` FOREIGN KEY (`ilan_id`) REFERENCES `ilanlar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `juri_atamalari_ibfk_2` FOREIGN KEY (`juri_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `juri_atamalari_ibfk_3` FOREIGN KEY (`atayan_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `juri_degerlendirmeleri`
--

CREATE TABLE `juri_degerlendirmeleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basvuru_id` int(11) NOT NULL,
  `juri_id` int(11) NOT NULL,
  `degerlendirme` text DEFAULT NULL,
  `sonuc` enum('Olumlu','Olumsuz') NOT NULL,
  `puan` decimal(5,2) DEFAULT NULL,
  `rapor_dosyasi` varchar(255) DEFAULT NULL,
  `degerlendirme_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `guncelleme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basvuru_juri_unique` (`basvuru_id`,`juri_id`),
  KEY `juri_id` (`juri_id`),
  CONSTRAINT `juri_degerlendirmeleri_ibfk_1` FOREIGN KEY (`basvuru_id`) REFERENCES `basvurular` (`id`) ON DELETE CASCADE,
  CONSTRAINT `juri_degerlendirmeleri_ibfk_2` FOREIGN KEY (`juri_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `belgeler`
--

CREATE TABLE `belgeler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `belge_turu` varchar(100) NOT NULL,
  `belge_adi` varchar(255) NOT NULL,
  `dosya_yolu` varchar(255) NOT NULL,
  `dosya_boyutu` int(11) DEFAULT NULL,
  `dosya_tipi` varchar(50) DEFAULT NULL,
  `durum` enum('onaylandı','reddedildi','beklemede') NOT NULL DEFAULT 'beklemede',
  `gecerlilik_tarihi` date DEFAULT NULL,
  `yukleme_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `belgeler_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `akademik_bilgiler`
--

CREATE TABLE `akademik_bilgiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `okul_adi` varchar(255) NOT NULL,
  `bolum` varchar(255) NOT NULL,
  `derece` enum('Lisans','Yüksek Lisans','Doktora','Doçentlik','Profesörlük') NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date DEFAULT NULL,
  `mezuniyet_notu` varchar(20) DEFAULT NULL,
  `diploma_dosyasi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `akademik_bilgiler_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `yabanci_dil`
--

CREATE TABLE `yabanci_dil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `dil` varchar(100) NOT NULL,
  `sinav_turu` varchar(100) DEFAULT NULL,
  `puan` decimal(5,2) DEFAULT NULL,
  `sinav_tarihi` date DEFAULT NULL,
  `belge_dosyasi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `yabanci_dil_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `is_deneyimleri`
--

CREATE TABLE `is_deneyimleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `kurum_adi` varchar(255) NOT NULL,
  `pozisyon` varchar(255) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date DEFAULT NULL,
  `devam_ediyor` tinyint(1) NOT NULL DEFAULT 0,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `is_deneyimleri_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `yayinlar`
--

CREATE TABLE `yayinlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `yayin_turu` enum('Makale','Kitap','Bildiri','Diğer') NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `yazarlar` varchar(255) NOT NULL,
  `yayin_yeri` varchar(255) DEFAULT NULL,
  `yayin_tarihi` date DEFAULT NULL,
  `doi` varchar(100) DEFAULT NULL,
  `dosya` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `yayinlar_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `projeler`
--

CREATE TABLE `projeler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aday_id` int(11) NOT NULL,
  `proje_adi` varchar(255) NOT NULL,
  `proje_turu` varchar(100) DEFAULT NULL,
  `kurum` varchar(255) DEFAULT NULL,
  `baslangic_tarihi` date DEFAULT NULL,
  `bitis_tarihi` date DEFAULT NULL,
  `butce` decimal(15,2) DEFAULT NULL,
  `rol` varchar(100) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aday_id` (`aday_id`),
  CONSTRAINT `projeler_ibfk_1` FOREIGN KEY (`aday_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `bildirimler`
--

CREATE TABLE `bildirimler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `mesaj` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `okundu` tinyint(1) NOT NULL DEFAULT 0,
  `olusturma_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_id` (`kullanici_id`),
  CONSTRAINT `bildirimler_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `sistem_log`
--

CREATE TABLE `sistem_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_id` int(11) DEFAULT NULL,
  `islem` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `ip_adresi` varchar(50) DEFAULT NULL,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kullanici_id` (`kullanici_id`),
  CONSTRAINT `sistem_log_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Örnek veriler
--

-- Kullanıcılar
INSERT INTO `kullanicilar` (`tc_kimlik_no`, `ad`, `soyad`, `email`, `telefon`, `sifre`, `rol`, `durum`, `kayit_tarihi`) VALUES
('11111111111', 'Admin', 'Kullanıcı', 'admin@kocaeli.edu.tr', '5551112233', 'admin123', 'admin', 1, NOW()),
('22222222222', 'Yönetici', 'Kullanıcı', 'yonetici@kocaeli.edu.tr', '5552223344', 'yonetici123', 'yonetici', 1, NOW()),
('33333333333', 'Jüri', 'Üyesi', 'juri@kocaeli.edu.tr', '5553334455', 'juri123', 'juri', 1, NOW()),
('44444444444', 'Aday', 'Kullanıcı', 'aday@gmail.com', '5554445566', 'aday123', 'aday', 1, NOW());

-- İlanlar
INSERT INTO `ilanlar` (`ilan_baslik`, `fakulte_birim`, `bolum`, `anabilim_dali`, `kadro_unvani`, `kadro_sayisi`, `ilan_aciklama`, `basvuru_kosullari`, `ilan_baslangic_tarihi`, `ilan_bitis_tarihi`, `olusturan_id`, `olusturma_tarihi`) VALUES
('Bilgisayar Mühendisliği Doktor Öğretim Üyesi İlanı', 'Mühendislik Fakültesi', 'Bilgisayar Mühendisliği', 'Bilgisayar Yazılımı', 'Doktor Öğretim Üyesi', 1, 'Bilgisayar Mühendisliği Bölümü Bilgisayar Yazılımı Anabilim Dalı için Doktor Öğretim Üyesi alınacaktır.', 'Bilgisayar Mühendisliği alanında doktora yapmış olmak, yapay zeka ve makine öğrenmesi konularında çalışmalar yapmış olmak.', '2023-01-01', '2023-12-31', 1, NOW()),
('Elektrik Mühendisliği Araştırma Görevlisi İlanı', 'Mühendislik Fakültesi', 'Elektrik Mühendisliği', 'Elektrik Makinaları', 'Araştırma Görevlisi', 2, 'Elektrik Mühendisliği Bölümü Elektrik Makinaları Anabilim Dalı için Araştırma Görevlisi alınacaktır.', 'Elektrik Mühendisliği lisans mezunu olmak, yüksek lisans yapıyor olmak.', '2023-01-15', '2023-12-15', 1, NOW());

-- İlan Kriterleri
INSERT INTO `ilan_kriterleri` (`ilan_id`, `kriter_adi`, `aciklama`, `minimum_deger`, `ekleyen_id`) VALUES
(1, 'Yabancı Dil Puanı', 'YDS veya eşdeğeri sınavdan alınan puan', '70', 2),
(1, 'Akademik Yayın Sayısı', 'SCI, SCI-E, SSCI veya AHCI indeksli dergilerde yayınlanmış makale sayısı', '3', 2),
(2, 'Yabancı Dil Puanı', 'YDS veya eşdeğeri sınavdan alınan puan', '50', 2),
(2, 'Lisans Not Ortalaması', 'Lisans mezuniyet not ortalaması (4.00 üzerinden)', '3.00', 2);

-- Jüri Atamaları
INSERT INTO `juri_atamalari` (`ilan_id`, `juri_id`, `atama_tarihi`, `atayan_id`) VALUES
(1, 3, NOW(), 2),
(2, 3, NOW(), 2);
