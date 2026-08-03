<?php
// Veritabanı bağlantı bilgileri (Kendi bilgisayarımızdaki XAMPP için geçerlidir)
$host = 'localhost'; 
$veritabani_adi = 'kart_sistemi'; 
$kullanici_adi = 'root'; 
$sifre = ''; 

try {
    // PDO kullanarak veritabanı ile güvenli bir bağlantı kuruyoruz
    $db = new PDO("mysql:host=$host;dbname=$veritabani_adi;charset=utf8", $kullanici_adi, $sifre);
    
    // Hata yakalama modunu aktif ediyoruz ki bir sorun olursa ekranda görebilelim
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Eğer bağlantı kurulamazsa sistemi durdur ve hatayı yaz
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>