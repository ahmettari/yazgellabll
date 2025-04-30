<?php
// Veritabanı bağlantı bilgileri
$host = "localhost";
$username = "root";
$password = "";
$database = "akademik_basvuru";

// Veritabanı bağlantısı oluştur
$conn = mysqli_connect($host, $username, $password, $database);

// Bağlantıyı kontrol et
if (!$conn) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

// Türkçe karakter sorunu için karakter seti ayarı
mysqli_set_charset($conn, "utf8");
?>
