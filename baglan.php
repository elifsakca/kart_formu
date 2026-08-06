<?php
$host     = "localhost";
$db_name  = "kart_sistemi";
$username = "root";
$password = ""; // XAMPP varsayılan şifre boştur

try {
    $db = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Tabloların Otomatik Oluşturulması
    $db->exec("CREATE TABLE IF NOT EXISTS yoneticiler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
        sifre VARCHAR(255) NOT NULL,
        ad_soyad VARCHAR(100) NOT NULL,
        rol ENUM('superadmin', 'admin') DEFAULT 'admin'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Sütun Kontrolü ve Güncellemesi (yoneticiler)
    $columnsYonetici = $db->query("SHOW COLUMNS FROM yoneticiler")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('ad_soyad', $columnsYonetici)) {
        $db->exec("ALTER TABLE yoneticiler ADD COLUMN ad_soyad VARCHAR(100) NOT NULL DEFAULT ''");
    }
    if (!in_array('rol', $columnsYonetici)) {
        $db->exec("ALTER TABLE yoneticiler ADD COLUMN rol ENUM('superadmin', 'admin') DEFAULT 'admin'");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS yonetici_izinleri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        yonetici_id INT NOT NULL,
        form_kodu VARCHAR(50) NOT NULL,
        UNIQUE KEY (yonetici_id, form_kodu),
        FOREIGN KEY (yonetici_id) REFERENCES yoneticiler(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Formlar Tablosunun Oluşturulması
    $db->exec("CREATE TABLE IF NOT EXISTS formlar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_kodu VARCHAR(50) NOT NULL UNIQUE,
        form_adi VARCHAR(255) NOT NULL,
        kategori VARCHAR(100) NOT NULL DEFAULT 'Bilgi İşlem Daire Başkanlığı Formları',
        dosya_adi VARCHAR(100) NOT NULL DEFAULT 'form_genel.php',
        durum TINYINT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Varsayılan formların kontrolü ve eklenmesi
    $form_kontrol = $db->query("SELECT COUNT(*) FROM formlar")->fetchColumn();
    if ($form_kontrol == 0) {
        $varsayilan_formlar = [
            ['KDYS.FR.0553', 'Akıllı Kart İşlem Formu', 'Akıllı Kart Formları', 'form_f52.php'],
            ['KDYS.FR.0556', 'Akıllı Kart Öğrenci İşlem Formu', 'Akıllı Kart Formları', 'form_f53.php'],
            ['KDYS.FR.0555', 'Kayıp Akıllı Kart Müracaat Formu', 'Akıllı Kart Formları', 'form_f54.php'],
            ['KDYS.FR.0554', 'Arızalı Akıllı Kart Müracaat Formu', 'Akıllı Kart Formları', 'form_f55.php'],
            ['KDYS.FR.0072', 'Kurumsal E-Posta Talep Formu', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0072.php'],
            ['KDYS.FR.0073', 'E-İmza Mini Kart Okuyucu Tutanağı', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0073.php'],
            ['KDYS.FR.0074', 'E-İmza Talep Formu', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0074.php'],
            ['KDYS.FR.0077', 'Kişisel Web Sözleşmesi', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0077.php'],
            ['KDYS.FR.0078', 'Kurumsal Statik IP Sözleşmesi', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0078.php'],
            ['KDYS.FR.0079', 'Kurumsal Web Sözleşmesi', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0079.php'],
            ['KDYS.FR.0080', 'Mernis Taahhütnamesi', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0080.php'],
            ['KDYS.FR.0082', 'Personel E-Posta Başvuru Formu', 'Bilgi İşlem Daire Başkanlığı Formları', 'form_0082.php']
        ];
        $insertFormStmt = $db->prepare("INSERT INTO formlar (form_kodu, form_adi, kategori, dosya_adi, durum) VALUES (?, ?, ?, ?, 1)");
        foreach ($varsayilan_formlar as $vf) {
            $insertFormStmt->execute($vf);
        }
    }

    // Sütun Kontrolü ve Güncellemesi (basvurular)
    $columnsBasvuru = $db->query("SHOW COLUMNS FROM basvurular")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('takip_no', $columnsBasvuru)) {
        $db->exec("ALTER TABLE basvurular ADD COLUMN takip_no VARCHAR(20) NULL UNIQUE");
    }
    if (!in_array('red_sebebi', $columnsBasvuru)) {
        $db->exec("ALTER TABLE basvurular ADD COLUMN red_sebebi TEXT NULL");
    }
    if (!in_array('dekont_yolu', $columnsBasvuru)) {
        $db->exec("ALTER TABLE basvurular ADD COLUMN dekont_yolu VARCHAR(255) NULL");
    }

    // İşlem Günlüğü (Log) Tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS islem_loglari (
        id INT AUTO_INCREMENT PRIMARY KEY,
        yonetici_adi VARCHAR(100) NOT NULL,
        basvuru_id INT NOT NULL,
        takip_no VARCHAR(20) NOT NULL,
        islem_detayi TEXT NOT NULL,
        tarih DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Mevcut takipsiz başvurulara takip no atama
    $takipsizler = $db->query("SELECT id FROM basvurular WHERE takip_no IS NULL OR takip_no = ''")->fetchAll();
    if ($takipsizler) {
        $upStmt = $db->prepare("UPDATE basvurular SET takip_no = :tno WHERE id = :id");
        foreach ($takipsizler as $row) {
            $yeni_tno = date('Y') . random_int(100000, 999999);
            $upStmt->execute([':tno' => $yeni_tno, ':id' => $row['id']]);
        }
    }

    // Eski 'admin' veya gereksiz hesapları temizleme
    $db->exec("DELETE FROM yoneticiler WHERE kullanici_adi NOT IN ('superadmin', 'admin1', 'admin2') AND rol != 'admin'");

    // Varsayılan Yöneticilerin Oluşturulması
    $varsayilan_yoneticiler = [
        ['kullanici_adi' => 'superadmin', 'sifre' => '123456', 'ad_soyad' => 'Süper Yönetici', 'rol' => 'superadmin'],
        ['kullanici_adi' => 'admin1',      'sifre' => '123456', 'ad_soyad' => 'Yönetici 1',     'rol' => 'admin'],
        ['kullanici_adi' => 'admin2',      'sifre' => '123456', 'ad_soyad' => 'Yönetici 2',     'rol' => 'admin']
    ];

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM yoneticiler WHERE kullanici_adi = :kadi");
    $insertStmt = $db->prepare("INSERT INTO yoneticiler (kullanici_adi, sifre, ad_soyad, rol) VALUES (:kadi, :sifre, :ad_soyad, :rol)");

    foreach ($varsayilan_yoneticiler as $y) {
        $checkStmt->execute([':kadi' => $y['kullanici_adi']]);
        if ($checkStmt->fetchColumn() == 0) {
            $hashed_pass = password_hash($y['sifre'], PASSWORD_DEFAULT);
            $insertStmt->execute([
                ':kadi'     => $y['kullanici_adi'],
                ':sifre'    => $hashed_pass,
                ':ad_soyad' => $y['ad_soyad'],
                ':rol'      => $y['rol']
            ]);
        }
    }

    // Normal yöneticilere varsayılan tüm form izinlerini otomatik tanımla
    $tum_form_kodlari = $db->query("SELECT form_kodu FROM formlar")->fetchAll(PDO::FETCH_COLUMN);
    $admins = $db->query("SELECT id FROM yoneticiler WHERE rol = 'admin'")->fetchAll();
    $insPerm = $db->prepare("INSERT IGNORE INTO yonetici_izinleri (yonetici_id, form_kodu) VALUES (:yid, :fkodu)");
    foreach ($admins as $adm) {
        foreach ($tum_form_kodlari as $fk) {
            $insPerm->execute([':yid' => $adm['id'], ':fkodu' => $fk]);
        }
    }

} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>