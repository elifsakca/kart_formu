<?php
session_start();
require_once 'baglan.php';

// Güvenlik Kontrolü: Sadece Süper Admin Erişebilir!
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true || ($_SESSION['admin_rol'] ?? '') !== 'superadmin') {
    header("Location: panel.php");
    exit;
}

$mesaj = "";
$hata = "";

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

// POST İşlemi 1: Yeni Admin Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yeni_admin_ekle'])) {
    $y_kadi   = trim($_POST['kullanici_adi'] ?? '');
    $y_sifre  = trim($_POST['sifre'] ?? '');
    $y_adsoyad= trim($_POST['ad_soyad'] ?? '');

    if (!empty($y_kadi) && !empty($y_sifre) && !empty($y_adsoyad)) {
        $chk = $db->prepare("SELECT COUNT(*) FROM yoneticiler WHERE kullanici_adi = :kadi");
        $chk->execute([':kadi' => $y_kadi]);
        
        if ($chk->fetchColumn() > 0) {
            $hata = "Bu kullanıcı adı ($y_kadi) zaten kullanılmaktadır!";
        } else {
            $ins = $db->prepare("INSERT INTO yoneticiler (kullanici_adi, sifre, ad_soyad, rol) VALUES (:kadi, :sifre, :adsoyad, 'admin')");
            $hashed = password_hash($y_sifre, PASSWORD_DEFAULT);
            $ins->execute([':kadi' => $y_kadi, ':sifre' => $hashed, ':adsoyad' => $y_adsoyad]);
            $mesaj = "Yeni yönetici ($y_adsoyad - $y_kadi) başarıyla eklendi.";
        }
    } else {
        $hata = "Lütfen tüm yönetici bilgilerini eksiksiz giriniz!";
    }
}

// POST İşlemi 2: İzinleri Kaydetme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['izinleri_kaydet'])) {
    $yonetici_id = intval($_POST['yonetici_id']);
    $secilen_formlar = $_POST['izinler'] ?? [];

    try {
        $delStmt = $db->prepare("DELETE FROM yonetici_izinleri WHERE yonetici_id = :yid");
        $delStmt->execute([':yid' => $yonetici_id]);

        $insStmt = $db->prepare("INSERT INTO yonetici_izinleri (yonetici_id, form_kodu) VALUES (:yid, :fkodu)");
        foreach ($secilen_formlar as $fkodu) {
            if (array_key_exists($fkodu, $tum_formlar)) {
                $insStmt->execute([':yid' => $yonetici_id, ':fkodu' => $fkodu]);
            }
        }
        $mesaj = "Görev izinleri başarıyla güncellendi.";
    } catch (PDOException $e) {
        $hata = "Hata oluştu: " . $e->getMessage();
    }
}

// Normal Yöneticileri Getir (rol = admin)
$adminler = $db->query("SELECT * FROM yoneticiler WHERE rol = 'admin' ORDER BY kullanici_adi ASC")->fetchAll();

// Her Yöneticinin Mevcut İzinleri
$mevcut_izinler = [];
$izinRows = $db->query("SELECT * FROM yonetici_izinleri")->fetchAll();
foreach ($izinRows as $row) {
    $mevcut_izinler[$row['yonetici_id']][] = $row['form_kodu'];
}

// İşlem Günlüğü (Loglar)
$loglar = $db->query("SELECT * FROM islem_loglari ORDER BY tarih DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin İzin & Yönetim Paneli - BAÜN</title>
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

        .form-satir { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-satir .form-grup { flex: 1; }
        .form-grup label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #555; }
        .form-grup input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }

        .btn-kaydet { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-kaydet:hover { background: #219150; }
        
        .btn-sec-hepsi { background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .btn-sec-hepsi:hover { background: #2980b9; }

        .alert-success { background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 5px; border-left: 5px solid #28a745; margin-bottom: 20px; font-weight: bold; }
        .alert-danger { background: #fce8e6; color: #d93025; padding: 12px 20px; border-radius: 5px; border-left: 5px solid #d93025; margin-bottom: 20px; font-weight: bold; }

        table.log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.log-table th { background: #e8f4f8; color: #1b656e; padding: 10px; text-align: left; }
        table.log-table td { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="header">
        <h1>
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" height="35" style="background:white; border-radius:4px; padding:2px;">
            BAÜN Form İşlem Merkezi - Yönetici & İzin Yapılandırması
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
        <?php if(!empty($hata)): ?>
            <div class="alert-danger">⚠️ <?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <!-- YENİ ADMİN EKLEME KARTI -->
        <div class="card">
            <h2>➕ Yeni Yönetici Hesabı Ekle</h2>
            <form method="POST">
                <div class="form-satir">
                    <div class="form-grup">
                        <label>Kullanıcı Adı</label>
                        <input type="text" name="kullanici_adi" placeholder="Örn: admin3" required autocomplete="off">
                    </div>
                    <div class="form-grup">
                        <label>Şifre</label>
                        <input type="text" name="sifre" value="123456" required>
                    </div>
                    <div class="form-grup">
                        <label>Adı Soyadı</label>
                        <input type="text" name="ad_soyad" placeholder="Örn: Ahmet Yılmaz" required>
                    </div>
                </div>
                <button type="submit" name="yeni_admin_ekle" class="btn-kaydet" style="background:#1b656e;">Yöneticiyi Ekle</button>
            </form>
        </div>

        <!-- YÖNETİCİ İZİN / GÖREV ATAMA KARTLARI -->
        <div class="card" style="background:#f8fbfd; border:1px solid #d0e4eb;">
            <h2 style="border-bottom:none; margin:0;">📋 Yönetici Görev & Form İzinleri</h2>
            <p style="font-size:13px; color:#555; margin-top:5px;">Aşağıdan <strong>admin1</strong>, <strong>admin2</strong> ve yeni eklediğiniz tüm yöneticilerin inceleyebileceği formları seçip kaydedebilirsiniz.</p>
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

        <!-- İŞLEM GÜNLÜĞÜ (AUDIT LOG) KARTI -->
        <div class="card">
            <h2>📜 Yöneticilerin İşlem Günlüğü (Audit Log)</h2>
            <p style="font-size:13px; color:#666;">Yöneticilerin başvurular üzerinde yaptığı tüm durum güncellemeleri ve red sebepleri aşağıda kronolojik olarak listelenmektedir.</p>
            
            <?php if (count($loglar) > 0): ?>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>İşlemi Yapan Yönetici</th>
                            <th>Takip No</th>
                            <th>Yapılan İşlem ve Detayı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($loglar as $l): ?>
                            <tr>
                                <td><?php echo date('d.m.Y H:i:s', strtotime($l['tarih'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($l['yonetici_adi']); ?></strong></td>
                                <td><span style="color:#1b656e; font-weight:bold;">#<?php echo htmlspecialchars($l['takip_no']); ?></span></td>
                                <td><?php echo htmlspecialchars($l['islem_detayi']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999; text-align:center; padding:20px;">Henüz kaydedilmiş bir yönetici işlemi bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
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
