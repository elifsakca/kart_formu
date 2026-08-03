<?php
// 1. Veritabanı bağlantısını dahil ediyoruz (2. Kişinin yazdığı dosya)
require_once 'baglan.php';

// Formun "Gönder" butonuyla gelip gelmediğini kontrol ediyoruz
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Formdaki tüm metin kutularından verileri alıyoruz
    $tc_no = $_POST['tc_no'] ?? '';
    $ad_soyad = $_POST['ad_soyad'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $kart_tipi = $_POST['kart_tipi'] ?? '';
    $islem_turu = $_POST['islem_turu'] ?? '';
    $unvan = $_POST['unvan'] ?? '';
    $birim = $_POST['birim'] ?? '';
    $bolum = $_POST['bolum'] ?? '';
    $sicil_no = $_POST['sicil_no'] ?? '';
    $ek_gosterge = $_POST['ek_gosterge'] ?? '';
    $hizmet_yeri = $_POST['hizmet_yeri'] ?? '';
    $firma_adi = $_POST['firma_adi'] ?? '';
    $kurumu = $_POST['kurumu'] ?? '';
    $kan_grubu = $_POST['kan_grubu'] ?? '';
    $gorev = $_POST['gorev'] ?? '';
    
    // 3. Fotoğraf Yükleme ve İsimlendirme İşlemleri
    $fotograf_yolu = "";
    if (isset($_FILES['fotograf']) && $_FILES['fotograf']['error'] === 0) {
        $dosya_adi = $_FILES['fotograf']['name'];
        $gecici_yol = $_FILES['fotograf']['tmp_name'];
        
        $dosya_uzantisi = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
        
        // Belgedeki kural: Sadece .jpg formatı kabul edilir[cite: 1]
        if ($dosya_uzantisi == "jpg" || $dosya_uzantisi == "jpeg") {
            
            // Fotoğrafların kaydedileceği klasör
            $klasor = 'yuklemeler/';
            // Eğer klasör yoksa kod otomatik olarak oluşturur
            if (!is_dir($klasor)) {
                mkdir($klasor, 0777, true);
            }
            
            // Belgedeki kural: Fotoğrafın adı TC Kimlik No olacak[cite: 1]
            $yeni_dosya_adi = $tc_no . "." . $dosya_uzantisi;
            $fotograf_yolu = $klasor . $yeni_dosya_adi;
            
            // Fotoğrafı geçici yerinden asıl klasöre taşı
            if (!move_uploaded_file($gecici_yol, $fotograf_yolu)) {
                die("Fotoğraf sunucuya yüklenirken bir hata oluştu.");
            }
        } else {
            die("Hata: Lütfen sadece .jpg formatında vesikalık fotoğraf yükleyiniz.[cite: 1]");
        }
    } else {
        die("Hata: Fotoğraf yüklenmesi zorunludur.");
    }

    // 4. Verileri Veritabanına Kaydetme (PDO ile güvenli kayıt)
    try {
        $sorgu = $db->prepare("INSERT INTO basvurular 
            (tc_no, ad_soyad, telefon, kart_tipi, islem_turu, unvan, birim, bolum, sicil_no, ek_gosterge, hizmet_yeri, firma_adi, kurumu, kan_grubu, gorev, fotograf_yolu) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $sorgu->execute([
            $tc_no, $ad_soyad, $telefon, $kart_tipi, $islem_turu, 
            $unvan, $birim, $bolum, $sicil_no, $ek_gosterge, 
            $hizmet_yeri, $firma_adi, $kurumu, $kan_grubu, $gorev, $fotograf_yolu
        ]);

        // İşlem başarılıysa ekrana gösterilecek sonuç mesajı
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; text-align: center;'>
                <h2 style='color: #155724;'>Başvurunuz Başarıyla Alındı!</h2>
                <p style='color: #155724; font-size: 16px;'>Sayın <b>$ad_soyad</b>, <b>$kart_tipi</b> talebiniz sisteme güvenle kaydedilmiştir.</p>
                <a href='index.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #155724; color: white; text-decoration: none; border-radius: 5px;'>Yeni Başvuru Yap</a>
              </div>";

    } catch (PDOException $e) {
        die("Veritabanına kaydedilirken hata oluştu: " . $e->getMessage());
    }
} else {
    // Sayfaya direkt linkten girilmeye çalışılırsa ana sayfaya yönlendir
    header("Location: index.php");
    exit;
}
?>