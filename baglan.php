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
    $tum_form_kodlari = ['F-52', 'F-53', 'F-54', 'F-55', 'KDYS.FR.0072', 'KDYS.FR.0073', 'KDYS.FR.0074', 'KDYS.FR.0077', 'KDYS.FR.0078', 'KDYS.FR.0079', 'KDYS.FR.0080', 'KDYS.FR.0082'];
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