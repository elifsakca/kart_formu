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

// Tüm Sistem Formlarını Veritabanından Çek
try {
    $formlar_query = $db->query("SELECT * FROM formlar ORDER BY kategori ASC, id ASC")->fetchAll();
} catch (PDOException $e) {
    $formlar_query = [];
}

$tum_formlar = [];
foreach ($formlar_query as $f) {
    $tum_formlar[$f['form_kodu']] = $f['form_kodu'] . ' - ' . $f['form_adi'] . ($f['durum'] == 0 ? ' (PASİF)' : '');
}

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

// POST İşlemi 3: Form Revize Etme (Güncelleme)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_revize_et'])) {
    $f_id       = intval($_POST['form_id']);
    $f_kodu     = trim($_POST['form_kodu'] ?? '');
    $f_adi      = trim($_POST['form_adi'] ?? '');
    $f_kategori = trim($_POST['kategori'] ?? '');
    $f_dosya    = trim($_POST['dosya_adi'] ?? '');

    if ($f_kategori === 'YENI_KAT') {
        $f_kategori = trim($_POST['yeni_kategori_adi'] ?? '');
    }

    if (empty($f_dosya)) {
        $f_dosya = 'form_genel.php';
    }

    if ($f_id > 0 && !empty($f_kodu) && !empty($f_adi) && !empty($f_kategori)) {
        try {
            // Form kodunun benzersiz olup olmadığını kontrol et (kendi ID'si hariç)
            $chk = $db->prepare("SELECT COUNT(*) FROM formlar WHERE form_kodu = :fkodu AND id != :id");
            $chk->execute([':fkodu' => $f_kodu, ':id' => $f_id]);
            
            if ($chk->fetchColumn() > 0) {
                $hata = "Bu form kodu ($f_kodu) zaten başka bir form tarafından kullanılmaktadır!";
            } else {
                // Eski kodu çekelim ki yetkilerdeki form kodunu da güncelleyebilelim
                $old_kodu_stmt = $db->prepare("SELECT form_kodu FROM formlar WHERE id = :id");
                $old_kodu_stmt->execute([':id' => $f_id]);
                $eski_kodu = $old_kodu_stmt->fetchColumn();

                $up = $db->prepare("UPDATE formlar SET form_kodu = :fkodu, form_adi = :fadi, kategori = :fkat, dosya_adi = :fdosya WHERE id = :id");
                $up->execute([':fkodu' => $f_kodu, ':fadi' => $f_adi, ':fkat' => $f_kategori, ':fdosya' => $f_dosya, ':id' => $f_id]);

                // Yetkileri de güncelleyelim
                if ($eski_kodu && $eski_kodu !== $f_kodu) {
                    $upPerm = $db->prepare("UPDATE yonetici_izinleri SET form_kodu = :yeni WHERE form_kodu = :eski");
                    $upPerm->execute([':yeni' => $f_kodu, ':eski' => $eski_kodu]);
                }

                $mesaj = "Form bilgileri başarıyla revize edildi.";
                
                // Formlar listesini ve izin tanımlarını yeniden yükleyelim
                $formlar_query = $db->query("SELECT * FROM formlar ORDER BY kategori ASC, id ASC")->fetchAll();
                $tum_formlar = [];
                foreach ($formlar_query as $f) {
                    $tum_formlar[$f['form_kodu']] = $f['form_kodu'] . ' - ' . $f['form_adi'] . ($f['durum'] == 0 ? ' (PASİF)' : '');
                }
            }
        } catch (PDOException $e) {
            $hata = "Form güncellenirken hata oluştu: " . $e->getMessage();
        }
    } else {
        $hata = "Lütfen form bilgilerini eksiksiz giriniz!";
    }
}

// POST İşlemi 4: Form Aktif/Pasif Yapma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_durum_degistir'])) {
    $f_id = intval($_POST['form_id']);
    $yeni_durum = intval($_POST['yeni_durum']);

    try {
        $up = $db->prepare("UPDATE formlar SET durum = :durum WHERE id = :id");
        $up->execute([':durum' => $yeni_durum, ':id' => $f_id]);
        $mesaj = "Form durumu başarıyla güncellendi.";
        
        // Formlar listesini ve izin tanımlarını yeniden yükleyelim
        $formlar_query = $db->query("SELECT * FROM formlar ORDER BY kategori ASC, id ASC")->fetchAll();
        $tum_formlar = [];
        foreach ($formlar_query as $f) {
            $tum_formlar[$f['form_kodu']] = $f['form_kodu'] . ' - ' . $f['form_adi'] . ($f['durum'] == 0 ? ' (PASİF)' : '');
        }
    } catch (PDOException $e) {
        $hata = "Form durumu güncellenirken hata oluştu: " . $e->getMessage();
    }
}

// POST İşlemi 5: Form Silme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_sil'])) {
    $f_id = intval($_POST['form_id']);
    try {
        $f_kodu_stmt = $db->prepare("SELECT form_kodu FROM formlar WHERE id = :id");
        $f_kodu_stmt->execute([':id' => $f_id]);
        $f_kodu = $f_kodu_stmt->fetchColumn();

        if ($f_kodu) {
            $delPerm = $db->prepare("DELETE FROM yonetici_izinleri WHERE form_kodu = :fkodu");
            $delPerm->execute([':fkodu' => $f_kodu]);
        }

        $del = $db->prepare("DELETE FROM formlar WHERE id = :id");
        $del->execute([':id' => $f_id]);
        $mesaj = "Form başarıyla sistemden silindi.";
        
        // Formlar listesini ve izin tanımlarını yeniden yükleyelim
        $formlar_query = $db->query("SELECT * FROM formlar ORDER BY kategori ASC, id ASC")->fetchAll();
        $tum_formlar = [];
        foreach ($formlar_query as $f) {
            $tum_formlar[$f['form_kodu']] = $f['form_kodu'] . ' - ' . $f['form_adi'] . ($f['durum'] == 0 ? ' (PASİF)' : '');
        }
    } catch (PDOException $e) {
        $hata = "Form silinirken hata oluştu: " . $e->getMessage();
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
            <a href="panel.php" class="header-btn"> Panele Dön</a>
            <a href="cikis.php" class="header-btn" style="background:#d93025; margin-left:10px;">Güvenli Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if(!empty($mesaj)): ?>
            <div class="alert-success"> <?php echo htmlspecialchars($mesaj); ?></div>
        <?php endif; ?>
        <?php if(!empty($hata)): ?>
            <div class="alert-danger"> <?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <!-- YENİ ADMİN EKLEME KARTI -->
        <div class="card">
            <h2> Yeni Yönetici Hesabı Ekle</h2>
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

        <!-- FORM YÖNETİM KARTI -->
        <div class="card">
            <h2> Sistem Formlarını Yönet (Revize Et / Pasifleştir / Sil)</h2>
            
            <!-- Bilgilendirme Uyarısı (Form seçilmediğinde gösterilir) -->
            <div id="edit_form_warning" class="alert-danger" style="background:#e8f4f8; color:#1b656e; border-left-color:#1b656e; margin-bottom:15px; font-size:13.5px; font-weight:normal;">
                ℹ Lütfen revize etmek (düzenlemek) istediğiniz formun yanındaki <strong>Revize Et</strong> butonuna tıklayınız.
            </div>

            <!-- Form Revize Etme Alt Bölümü (Varsayılan olarak gizlidir, Revize Et'e tıklayınca açılır) -->
            <div id="edit_form_container" style="display:none; margin-bottom: 30px; padding: 15px; background: #fbfcfc; border: 1px solid #dcdde1; border-radius: 6px;">
                <form method="POST">
                    <input type="hidden" name="form_id" id="edit_form_id" value="">
                    <h3 style="color:#1b656e; font-size:16px; margin-top:0;">Başvuru Formu Bilgilerini Revize Et</h3>
                    <div class="form-satir">
                        <div class="form-grup">
                            <label>Form Kodu *</label>
                            <input type="text" name="form_kodu" id="edit_form_kodu" placeholder="Örn: KDYS.FR.0090" required autocomplete="off">
                        </div>
                        <div class="form-grup">
                            <label>Form Adı *</label>
                            <input type="text" name="form_adi" id="edit_form_adi" placeholder="Örn: Yemek Kartı Talep Formu" required>
                        </div>
                    </div>
                    <div class="form-satir">
                        <div class="form-grup">
                            <label>Kategori *</label>
                            <select name="kategori" id="edit_form_kategori_sec" onchange="kategoriSecimKontrolEdit(this)" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                                <option value="Bilgi İşlem Daire Başkanlığı Formları">Bilgi İşlem Daire Başkanlığı Formları</option>
                                <option value="Akıllı Kart Formları">Akıllı Kart Formları</option>
                                <option value="YENI_KAT">-- Yeni Kategori Ekle --</option>
                            </select>
                            <input type="text" name="yeni_kategori_adi" id="edit_yeni_kategori_adi" placeholder="Yeni Kategori Adını Yazınız" style="display:none; margin-top:8px; width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box;">
                        </div>
                        <div class="form-grup">
                            <label>Sayfa / Şablon (Dosya Adı)</label>
                            <input type="text" name="dosya_adi" id="edit_dosya_adi" placeholder="Boş bırakılırsa genel şablon kullanılır (form_genel.php)">
                        </div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" name="form_revize_et" class="btn-kaydet" style="background:#27ae60;">Değişiklikleri Kaydet</button>
                        <button type="button" class="btn-kaydet" style="background:#7f8c8d;" onclick="düzenlemeyiKapat()">İptal Et</button>
                    </div>
                </form>
            </div>

            <!-- Mevcut Formların Listesi ve Yönetimi -->
            <h3 style="color:#1b656e; font-size:16px;">Sistemde Kayıtlı Formlar</h3>
            <table class="log-table" style="width:100%; border-collapse:collapse; margin-top:10px;">
                <thead>
                    <tr style="background:#e8f4f8; color:#1b656e;">
                        <th style="padding:10px; text-align:left;">Form Kodu</th>
                        <th style="padding:10px; text-align:left;">Form Adı</th>
                        <th style="padding:10px; text-align:left;">Kategori</th>
                        <th style="padding:10px; text-align:left;">Şablon / Sayfa</th>
                        <th style="padding:10px; text-align:center;">Durum</th>
                        <th style="padding:10px; text-align:center; width:220px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formlar_query as $f): ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><strong><?php echo htmlspecialchars($f['form_kodu']); ?></strong></td>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($f['form_adi']); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><span style="font-size:11px; background:#e8f4f8; padding:3px 6px; border-radius:3px; color:#1b656e;"><?php echo htmlspecialchars($f['kategori']); ?></span></td>
                            <td style="padding:10px; border-bottom:1px solid #eee; font-family:monospace; font-size:12px;"><?php echo htmlspecialchars($f['dosya_adi']); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                                <?php if ($f['durum'] == 1): ?>
                                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">Aktif</span>
                                <?php else: ?>
                                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                                <div style="display:inline-flex; gap:5px; align-items:center;">
                                    <button type="button" class="btn-sec-hepsi" style="background:#3498db; font-size:11px; padding:4px 8px; cursor:pointer;" onclick="formuDuzenle(<?php echo $f['id']; ?>, '<?php echo addslashes($f['form_kodu']); ?>', '<?php echo addslashes($f['form_adi']); ?>', '<?php echo addslashes($f['kategori']); ?>', '<?php echo addslashes($f['dosya_adi']); ?>')">Revize Et</button>
                                    
                                    <form method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                        <input type="hidden" name="yeni_durum" value="<?php echo $f['durum'] == 1 ? 0 : 1; ?>">
                                        <?php if ($f['durum'] == 1): ?>
                                            <button type="submit" name="form_durum_degistir" class="btn-sec-hepsi" style="background:#f39c12; font-size:11px; padding:4px 8px; cursor:pointer;">Pasif Et</button>
                                        <?php else: ?>
                                            <button type="submit" name="form_durum_degistir" class="btn-sec-hepsi" style="background:#27ae60; font-size:11px; padding:4px 8px; cursor:pointer;">Aktif Et</button>
                                        <?php endif; ?>
                                    </form>
                                    <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Bu formu silmek istediğinize emin misiniz? İzinler de temizlenecektir.');">
                                        <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                        <button type="submit" name="form_sil" class="btn-sec-hepsi" style="background:#d93025; font-size:11px; padding:4px 8px; cursor:pointer;">Sil</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- YÖNETİCİ İZİN / GÖREV ATAMA KARTLARI -->
        <div class="card" style="background:#f8fbfd; border:1px solid #d0e4eb;">
            <h2 style="border-bottom:none; margin:0;"> Yönetici Görev & Form İzinleri</h2>
            <p style="font-size:13px; color:#555; margin-top:5px;">Aşağıdan <strong>admin1</strong>, <strong>admin2</strong> ve yeni eklediğiniz tüm yöneticilerin inceleyebileceği formları seçip kaydedebilirsiniz.</p>
        </div>

        <?php foreach ($adminler as $admin): ?>
            <?php 
                $aid = $admin['id'];
                $atanmis_formlar = $mevcut_izinler[$aid] ?? [];
            ?>
            <div class="card">
                <h2>
                    <span> <?php echo htmlspecialchars($admin['ad_soyad']); ?> (Kullanıcı Adı: <strong><?php echo htmlspecialchars($admin['kullanici_adi']); ?></strong>)</span>
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
            <h2> Yöneticilerin İşlem Günlüğü </h2>
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

        function formuDuzenle(id, kodu, adi, kategori, dosya) {
            document.getElementById("edit_form_container").style.display = "block";
            document.getElementById("edit_form_warning").style.display = "none";
            
            document.getElementById("edit_form_id").value = id;
            document.getElementById("edit_form_kodu").value = kodu;
            document.getElementById("edit_form_adi").value = adi;
            document.getElementById("edit_dosya_adi").value = dosya;
            
            var selectKat = document.getElementById("edit_form_kategori_sec");
            var found = false;
            for (var i = 0; i < selectKat.options.length; i++) {
                if (selectKat.options[i].value === kategori) {
                    selectKat.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            
            var yeniKatInput = document.getElementById("edit_yeni_kategori_adi");
            if (!found) {
                selectKat.value = "YENI_KAT";
                yeniKatInput.value = kategori;
                yeniKatInput.style.display = "block";
                yeniKatInput.required = true;
            } else {
                yeniKatInput.value = "";
                yeniKatInput.style.display = "none";
                yeniKatInput.required = false;
            }
            
            document.getElementById("edit_form_container").scrollIntoView({ behavior: 'smooth' });
        }

        function düzenlemeyiKapat() {
            document.getElementById("edit_form_container").style.display = "none";
            document.getElementById("edit_form_warning").style.display = "block";
            document.getElementById("edit_form_id").value = "";
            document.getElementById("edit_form_kodu").value = "";
            document.getElementById("edit_form_adi").value = "";
            document.getElementById("edit_dosya_adi").value = "";
        }

        function kategoriSecimKontrolEdit(selectElem) {
            var yeniKatInput = document.getElementById("edit_yeni_kategori_adi");
            if (selectElem.value === "YENI_KAT") {
                yeniKatInput.style.display = "block";
                yeniKatInput.required = true;
                yeniKatInput.focus();
            } else {
                yeniKatInput.style.display = "none";
                yeniKatInput.required = false;
            }
        }
    </script>
</body>
</html>
