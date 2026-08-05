<?php
session_start();
require_once 'baglan.php';

// Güvenlik Kontrolü: Sadece Süper Admin Erişebilir!
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true || ($_SESSION['admin_rol'] ?? '') !== 'superadmin') {
    header("Location: panel.php");
    exit;
}

$mesaj = "";

// Tüm Sistem Formlarının Listesi
$tum_formlar = [
    'F-52'         => 'Akıllı Kart İşlem Formu (F-52)',
    'F-53'         => 'Akıllı Kart Öğrenci İşlem Formu (F-53)',
    'F-54'         => 'Kayıp Akıllı Kart Müracaat Formu (F-54)',
    'F-55'         => 'Arızalı Akıllı Kart Müracaat Formu (F-55)',
    'KDYS.FR.0072' => 'KDYS.FR.0072 - Kurumsal E-Posta Talep Formu',
    'KDYS.FR.0073' => 'KDYS.FR.0073 - E-İmza Mini Kart Okuyucu Tutanağı',
    'KDYS.FR.0074' => 'KDYS.FR.0074 - E-İmza Talep Formu',
    'KDYS.FR.0077' => 'KDYS.FR.0077 - Kişisel Web Sözleşmesi',
    'KDYS.FR.0078' => 'KDYS.FR.0078 - Kurumsal Statik IP Sözleşmesi',
    'KDYS.FR.0079' => 'KDYS.FR.0079 - Kurumsal Web Sözleşmesi',
    'KDYS.FR.0080' => 'KDYS.FR.0080 - Mernis Taahhütnamesi',
    'KDYS.FR.0082' => 'KDYS.FR.0082 - Personel E-Posta Başvuru Formu'
];

// POST İşlemi: İzinleri Kaydetme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['izinleri_kaydet'])) {
    $yonetici_id = intval($_POST['yonetici_id']);
    $secilen_formlar = $_POST['izinler'] ?? [];

    try {
        // Önceki tüm izinleri temizle
        $delStmt = $db->prepare("DELETE FROM yonetici_izinleri WHERE yonetici_id = :yid");
        $delStmt->execute([':yid' => $yonetici_id]);

        // Yeni seçilen izinleri ekle
        $insStmt = $db->prepare("INSERT INTO yonetici_izinleri (yonetici_id, form_kodu) VALUES (:yid, :fkodu)");
        foreach ($secilen_formlar as $fkodu) {
            if (array_key_exists($fkodu, $tum_formlar)) {
                $insStmt->execute([':yid' => $yonetici_id, ':fkodu' => $fkodu]);
            }
        }
        $mesaj = "İzinler başarıyla güncellendi.";
    } catch (PDOException $e) {
        $mesaj = "Hata oluştu: " . $e->getMessage();
    }
}

// Normal Yöneticileri Getir (Sadece rol = admin olanlar)
$adminler = $db->query("SELECT * FROM yoneticiler WHERE rol = 'admin' ORDER BY kullanici_adi ASC")->fetchAll();

// Her Yöneticinin Mevcut İzinlerini Çek
$mevcut_izinler = [];
$izinRows = $db->query("SELECT * FROM yonetici_izinleri")->fetchAll();
foreach ($izinRows as $row) {
    $mevcut_izinler[$row['yonetici_id']][] = $row['form_kodu'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin İzin Yönetimi (Görev Atama) - BAÜN</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f6f9; color: #333; }
        .header { background-color: #1b656e; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { font-size: 20px; margin: 0; display: flex; align-items: center; gap: 10px; }
        .header-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; transition: 0.3s; }
        .header-btn:hover { background: rgba(255,255,255,0.3); }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        .card h2 { margin-top: 0; color: #1b656e; font-size: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin: 20px 0; background: #fafafa; padding: 15px; border-radius: 6px; border: 1px solid #eee; }
        .form-grid label { display: flex; align-items: center; gap: 10px; font-size: 13.5px; cursor: pointer; padding: 6px; border-radius: 4px; transition: background 0.2s; }
        .form-grid label:hover { background: #e8f4f8; }
        .form-grid input[type="checkbox"] { width: 18px; height: 18px; accent-color: #1b656e; cursor: pointer; }

        .btn-kaydet { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-kaydet:hover { background: #219150; }
        
        .btn-sec-hepsi { background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .btn-sec-hepsi:hover { background: #2980b9; }

        .alert-success { background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 5px; border-left: 5px solid #28a745; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" height="35" style="background:white; border-radius:4px; padding:2px;">
            BAÜN Form İşlem Merkezi - Admin İzin Yönetimi (Görev Atama)
        </h1>
        <div>
            <a href="panel.php" class="header-btn">← Panele Dön</a>
            <a href="cikis.php" class="header-btn" style="background:#d93025; margin-left:10px;">Güvenli Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if(!empty($mesaj)): ?>
            <div class="alert-success">✓ <?php echo htmlspecialchars($mesaj); ?></div>
        <?php endif; ?>

        <div style="background: #e8f4f8; border-left: 5px solid #1b656e; padding: 15px; border-radius: 5px; margin-bottom: 25px; font-size: 14px;">
            <strong>ℹ Bilgilendirme:</strong> Süper Admin (<strong>superadmin</strong>) olarak tüm başvuru formlarına ve yönetim işlemlerine tam erişiminiz vardır. Aşağıdan <strong>admin1</strong> ve <strong>admin2</strong> kullanıcılarının inceleyebileceği ve yönetebileceği form başvurularını işaretleyip kaydedebilirsiniz.
        </div>

        <?php foreach ($adminler as $admin): ?>
            <?php 
                $aid = $admin['id'];
                $atanmis_formlar = $mevcut_izinler[$aid] ?? [];
            ?>
            <div class="card">
                <h2>
                    <span>👤 <?php echo htmlspecialchars($admin['ad_soyad']); ?> (Kullanıcı Adı: <strong><?php echo htmlspecialchars($admin['kullanici_adi']); ?></strong>)</span>
                    <button type="button" class="btn-sec-hepsi" onclick="tumunuSec('form_grid_<?php echo $aid; ?>')">Tümünü Seç / Kaldır</button>
                </h2>
                
                <form method="POST">
                    <input type="hidden" name="yonetici_id" value="<?php echo $aid; ?>">

                    <div class="form-grid" id="form_grid_<?php echo $aid; ?>">
                        <?php foreach ($tum_formlar as $kodu => $adi): ?>
                            <?php $checked = in_array($kodu, $atanmis_formlar) ? 'checked' : ''; ?>
                            <label>
                                <input type="checkbox" name="izinler[]" value="<?php echo $kodu; ?>" <?php echo $checked; ?>>
                                <strong><?php echo htmlspecialchars($kodu); ?></strong> - <?php echo htmlspecialchars($adi); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="izinleri_kaydet" class="btn-kaydet">Görev ve İzinleri Kaydet</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function tumunuSec(gridId) {
            var checkboxes = document.querySelectorAll('#' + gridId + ' input[type="checkbox"]');
            var tumuSecili = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !tumuSecili);
        }
    </script>
</body>
</html>
