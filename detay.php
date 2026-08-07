<?php
session_start();
require_once 'baglan.php';

// Güvenlik Kontrolü: Giriş yapılmadıysa login.php'ye yönlendir
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM basvurular WHERE id = :id");
$stmt->execute([':id' => $id]);
$basvuru = $stmt->fetch();

if (!$basvuru) {
    die("Başvuru bulunamadı!");
}

// Detay sayfasına girildiğinde bu başvuruya ait bildirimleri okundu olarak işaretle
try {
    if (isset($_GET['read_log']) && intval($_GET['read_log']) > 0) {
        $rlog_id = intval($_GET['read_log']);
        $db->prepare("UPDATE islem_loglari SET okundu = 1 WHERE id = :lid")->execute([':lid' => $rlog_id]);
    }
    if ($id > 0) {
        $db->prepare("UPDATE islem_loglari SET okundu = 1 WHERE basvuru_id = :bid")->execute([':bid' => $id]);
    }
} catch (Exception $e) {}

$admin_id  = $_SESSION['admin_id'] ?? 0;
$admin_rol = $_SESSION['admin_rol'] ?? 'admin';

// Güvenlik: Normal admin sadece izinli olduğu formu görebilir
if ($admin_rol !== 'superadmin') {
    $permStmt = $db->prepare("SELECT COUNT(*) FROM yonetici_izinleri WHERE yonetici_id = :yid AND form_kodu = :fkodu");
    $permStmt->execute([':yid' => $admin_id, ':fkodu' => $basvuru['form_kodu']]);
    if ($permStmt->fetchColumn() == 0) {
        die("<div style='font-family:sans-serif; padding:50px; text-align:center;'><h2> Erişim Engellendi</h2><p>Bu başvuru formunu (".htmlspecialchars($basvuru['form_kodu']).") görüntüleme yetkiniz bulunmamaktadır.</p><br><a href='panel.php' style='color:#1b656e; font-weight:bold;'>Panele Dön</a></div>");
    }
}

$veriler = json_decode($basvuru['form_verileri'], true) ?? [];

// Form detaylarını ve admin doldurulacak alanları veritabanından çekelim
$form_info_stmt = $db->prepare("SELECT * FROM formlar WHERE form_kodu = :fk");
$form_info_stmt->execute([':fk' => $basvuru['form_kodu']]);
$formRow = $form_info_stmt->fetch();
$form_alanlari = json_decode($formRow['form_alanlari'] ?? '[]', true) ?: [];

$admin_alanlari = [];
foreach ($form_alanlari as $fa) {
    if (isset($fa['target']) && $fa['target'] === 'admin') {
        $admin_alanlari[] = $fa;
    }
}

// YÖNETİCİ TARAFINDAN GİRİLEN BİLGİLERİ KAYDETME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yonetici_kaydet'])) {
    $yonetici_verileri = $_POST['yonetici'] ?? [];
    
    foreach ($yonetici_verileri as $k => $v) {
        $veriler[$k] = $v;
    }
    
    $yeni_json = json_encode($veriler, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $updateStmt = $db->prepare("UPDATE basvurular SET form_verileri = :fv WHERE id = :id");
    $updateStmt->execute([':fv' => $yeni_json, ':id' => $id]);
    
    $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
    logEkle($db, $curAdmin, $id, $basvuru['takip_no'], "'".htmlspecialchars($basvuru['form_kodu'])."' kodlu başvuru için yönetici bilgi alanlarını doldurdu/güncelledi.");

    header("Location: detay.php?id={$id}&kaydedildi=1");
    exit;
}

$bugun = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Detayı #<?php echo $basvuru['takip_no'] ?: $basvuru['id']; ?> - BAÜN</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f6f9; color: #333; }
        .noprint { display: flex; justify-content: space-between; align-items: center; background: #1b656e; color: white; padding: 15px 30px; }
        .noprint a { color: white; text-decoration: none; font-weight: bold; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px; }
        .noprint button { background: #27ae60; color: white; border: none; padding: 8px 18px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        
        /* Resimdeki Kurumsal KDYS Başlık Tablosu Stilleri */
        .kdys-header-table {
            width: 100%;
            border-collapse: collapse;
            border: 3px double #333;
            margin-bottom: 25px;
            background: #fff;
        }
        .kdys-header-table td {
            border: 1px solid #333;
            vertical-align: middle;
        }
        .kdys-logo-cell {
            width: 120px;
            text-align: center;
            padding: 8px;
        }
        .kdys-logo-img {
            max-height: 80px;
            max-width: 95px;
            object-fit: contain;
        }
        .kdys-title-cell {
            text-align: center;
            padding: 10px 15px;
            font-family: 'Times New Roman', Times, serif, 'Segoe UI';
            color: #111;
        }
        .kdys-tc {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }
        .kdys-unv {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .kdys-form-title {
            font-size: 14px;
            font-weight: bold;
            line-height: 1.3;
            text-transform: uppercase;
        }
        .kdys-info-cell {
            width: 250px;
            padding: 0 !important;
        }
        .kdys-info-subtable {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .kdys-info-subtable td {
            padding: 3px 8px;
            border: 1px solid #333;
        }
        .kdys-info-subtable tr:first-child td {
            border-top: none;
        }
        .kdys-info-subtable tr:last-child td {
            border-bottom: none;
        }
        .kdys-info-subtable td.kdys-lbl {
            border-left: none;
            font-weight: bold;
            color: #444;
            width: 48%;
            white-space: nowrap;
        }
        .kdys-info-subtable td.kdys-val {
            border-right: none;
            color: #000;
            font-weight: bold;
            width: 52%;
        }

        .paper { background: white; max-width: 900px; margin: 30px auto; padding: 40px; border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border: 1px solid #000; }
        
        .photo-box { width: 120px; height: 150px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 4px; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; border: 1px solid #000; background: #fff; }
        .grid-table th, .grid-table td { border: 1px solid #000; padding: 8px 12px; font-size: 13.5px; text-align: left; }
        .grid-table th { background: #f4f4f4; width: 32%; color: #000; font-weight: bold; }
        
        .status-badge { padding: 5px 12px; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; background: #fff; color: #000; border: 1px solid #000; }

        /* Yönetici Online Doldurma Alanı Stilleri */
        .yonetici-input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #000;
            border-radius: 3px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
            background-color: #fff;
        }
        .yonetici-input:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.4);
        }
        .btn-yonetici-kaydet {
            background: #1b656e;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .btn-yonetici-kaydet:hover {
            background: #144d54;
        }

        .only-print { display: none; }

        @media print {
            .noprint, .btn-yonetici-kaydet, .kayit-bildirimi { display: none !important; }
            .only-print { display: block !important; }
            body { background: white; margin: 0; padding: 0; color: #000; }
            .paper { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 0; border: none; }
            .kdys-header-table { border: 3px double #000 !important; }
            .kdys-header-table td, .kdys-info-subtable td { border: 1px solid #000 !important; color: #000 !important; }
            .grid-table { border: 1px solid #000 !important; }
            .grid-table th, .grid-table td { border: 1px solid #000 !important; color: #000 !important; }
            .grid-table th { background: #f0f0f0 !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            div { border-color: #000 !important; }
            h3, h4 { color: #000 !important; border-color: #000 !important; }
            .yonetici-input {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                font-weight: bold !important;
                color: #000 !important;
                appearance: none !important;
                -webkit-appearance: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="noprint">
        <a href="panel.php">← Panele Dön</a>
        <div>
            <span style="margin-right: 15px;">Yönetici Paneli</span>
            <button onclick="window.print()" style="font-size: 14px; padding: 8px 16px;">📄 PDF İndir / Yazdır</button>
        </div>
    </div>

    <?php if(isset($_GET['kaydedildi']) && $_GET['kaydedildi'] == 1): ?>
        <div class="kayit-bildirimi" style="max-width:900px; margin:15px auto -15px auto; background:#d4edda; color:#155724; padding:12px 20px; border-radius:5px; border-left:5px solid #28a745; font-weight:bold;">
            ✓ Yönetici tarafından girilen bilgiler başarıyla veritabanına kaydedildi!
        </div>
    <?php endif; ?>

    <div class="paper">
        <!-- RESİMDEKİ RESMİ KDYS BAŞLIK TABLOSU -->
        <table class="kdys-header-table">
            <tr>
                <td class="kdys-logo-cell">
                    <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" alt="BAÜN Logo" class="kdys-logo-img">
                </td>
                <td class="kdys-title-cell">
                    <div class="kdys-tc">T.C.</div>
                    <div class="kdys-unv">BALIKESİR ÜNİVERSİTESİ</div>
                    <div class="kdys-form-title">
                        <?php 
                            $temiz_baslik = preg_replace('/\s*\(KDYS\.FR\.\d+\)\s*/i', '', $basvuru['form_adi']);
                            if (mb_strpos($temiz_baslik, 'Formu') === false && mb_strpos($temiz_baslik, 'FORMU') === false) {
                                $temiz_baslik .= ' FORMU';
                            }
                            echo htmlspecialchars(mb_strtoupper($temiz_baslik, 'UTF-8')); 
                        ?>
                    </div>
                </td>
                <td class="kdys-info-cell">
                    <table class="kdys-info-subtable">
                        <tr>
                            <td class="kdys-lbl">Doküman No</td>
                            <td class="kdys-val"><?php echo htmlspecialchars($basvuru['form_kodu'] ?: 'KDYS.FR.0001'); ?></td>
                        </tr>
                        <tr>
                            <td class="kdys-lbl">İlk Yayın Tarihi</td>
                            <td class="kdys-val">19.03.2025</td>
                        </tr>
                        <tr>
                            <td class="kdys-lbl">Revizyon Tarihi</td>
                            <td class="kdys-val"><?php 
                                $fRevDate = null;
                                if (!empty($basvuru['form_kodu'])) {
                                    $rev_q = $db->prepare("SELECT son_revize_tarihi FROM formlar WHERE form_kodu = :fk");
                                    $rev_q->execute([':fk' => $basvuru['form_kodu']]);
                                    $fRevDate = $rev_q->fetchColumn();
                                }
                                echo !empty($fRevDate) ? date('d.m.Y H:i', strtotime($fRevDate)) : date('d.m.Y');
                            ?></td>
                        </tr>
                        <tr>
                            <td class="kdys-lbl">Revizyon No</td>
                            <td class="kdys-val">01</td>
                        </tr>
                        <tr>
                            <td class="kdys-lbl">Sayfa No</td>
                            <td class="kdys-val">1/1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- TAKİP NO & FOTOĞRAF / DURUM BİLGİ ALANI -->
        <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 12px 18px; border-radius: 4px; border: 1px solid #000; margin-bottom: 25px;">
            <div>
                <p style="margin: 0 0 5px 0; font-size: 14px;">
                    <strong>Takip No:</strong> <span style="font-size:16px; font-weight:bold; color:#000;">#<?php echo htmlspecialchars($basvuru['takip_no'] ?: $basvuru['id']); ?></span>
                </p>
                <p style="margin: 0; font-size: 13px; color: #333;">
                    <strong>Başvuru Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?> &nbsp;|&nbsp;
                    <strong>Form Son Revize Tarihi:</strong> <?php echo !empty($fRevDate) ? date('d.m.Y H:i', strtotime($fRevDate)) : date('d.m.Y H:i'); ?>
                </p>
            </div>
            <div>
                <?php if(!empty($basvuru['fotograf_yolu']) && file_exists($basvuru['fotograf_yolu'])): ?>
                    <div class="photo-box">
                        <img src="<?php echo htmlspecialchars($basvuru['fotograf_yolu']); ?>" alt="Vesikalık Fotoğraf">
                    </div>
                <?php else: ?>
                    <span class="status-badge">Durum: <?php echo htmlspecialchars($basvuru['durum']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($basvuru['durum'] == 'Reddedildi' && !empty($basvuru['red_sebebi'])): ?>
            <div style="background:#fce8e6; border-left:5px solid #d93025; border:1px solid #d93025; padding:12px 18px; border-radius:4px; margin-bottom:20px; color:#d93025;">
                <strong style="font-size:15px;"> Bu Başvuru Reddedilmiştir</strong><br>
                <strong>Red Gerekçesi:</strong> <?php echo htmlspecialchars($basvuru['red_sebebi']); ?>
            </div>
        <?php endif; ?>

        <h3 style="color:#000; border-bottom:2px solid #000; padding-bottom:5px; margin-top:25px;">Forma Girilen Tüm Detaylar</h3>
        <table class="grid-table">
            <tbody>
                <?php 
                if (is_array($veriler)) {
                    foreach ($veriler as $anahtar => $deger) {
                        if ($anahtar == 'form_kodu' || $anahtar == 'form_adi' || $anahtar == 'id') continue;
                        
                        $etiket = ucwords(str_replace(['_', '[]'], [' ', ''], $anahtar));
                        
                        echo '<tr>';
                        echo '<th>' . htmlspecialchars($etiket) . '</th>';
                        echo '<td>';
                        if (is_array($deger)) {
                            echo htmlspecialchars(implode(', ', $deger));
                        } else {
                            echo nl2br(htmlspecialchars($deger));
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Hüküm, Koşul ve Taahhütler Onay Bloğu -->
        <?php 
        $taahhut_metni = '';
        $ek_bilgi_ve_politikalar = '';
        switch ($basvuru['form_kodu']) {
            case 'KDYS.FR.0553':
                $taahhut_metni = "Bu formdaki tüm bilgilerin doğruluğunu, kart tipime göre gerekli olan tüm alanları doldurduğumu beyan ederim. Hatalı basılan veya değişecek kart bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 12px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <strong>AÇIKLAMA (Lütfen kart tipinize göre zorunlu alanları kontrol ediniz):</strong><br>
                    • <b>Akademik Personel Kimlik Kartı</b> için Ad, Soyad, Unvan, Görev, Birim, Bölüm, Kurum sicil no ve T.C Kimlik No kısımları doldurulacaktır.<br>
                    • <b>İdari Personel Kimlik Kartı</b> için Ad, Soyad, Unvan, Kadrosunun Olduğu Birim/Bölüm, Kurum sicil no ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Yerleşke Hizmet Giriş Kartı</b> için Ad, Soyad, Unvan, Firma Adı, Birim, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Yerleşke Firma Giriş Kartı</b> için Ad, Soyad, Unvan, Firma Adı, Birim, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Yerleşke Kurum Giriş Kartı</b> için Ad, Soyad, Unvan, Kurum, Kurum Sicil no, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Yerleşke Misafir Giriş Kartı</b> için Ad, Soyad, Unvan, Görev, Birim ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Yerleşke Emekli, Onursal ve Kütüphane Giriş Kartı</b> için Ad, Soyad, Unvan ve T.C Kimlik no kısımları doldurulacaktır.<br>
                    • <b>Koruma ve Güvenlik Görevlisi</b> için Ad, Soyad, Kan Grubu, Kurum Sicil no ve T.C Kimlik no doldurulacaktır.<br>
                    • <b>Özel Güvenlik Görevlisi</b> için Ad, Soyad, Kan Grubu ve T.C Kimlik no doldurulacaktır.
                </div>
                <div style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; background: #fffde8; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <b>Önemli not:</b> Hatalı basılan veya değişecek kart bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır. Ödemeye esas ek göstergenin değişimi için akıllı kart gönderilmeyecektir.
                </div>';
                break;
            case 'KDYS.FR.0556':
                $taahhut_metni = "Bu formdaki tüm öğrenci bilgilerinin doğruluğunu beyan ederim. Hatalı basılan veya değişecek kartlar bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; background: #fffde8; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <b>Önemli Not:</b> Hatalı basılan veya değişecek kartlar bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.
                </div>';
                break;
            case 'KDYS.FR.0555':
                $taahhut_metni = "Aşağıda belirttiğim adıma kayıtlı olan akıllı kimlik kartımı kaybettim. Eski kimlik kartımın AKS sisteminden iptal edilmesini ve bedeli karşılığında yeni kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 12px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <b>Not:</b> Yeni kart bedelinin yatırıldığına dair banka dekontunu (PDF, JPG veya PNG formatında) yükleyebilirsiniz.
                </div>';
                break;
            case 'KDYS.FR.0554':
                $taahhut_metni = "Eski kimlik kartımın AKS sisteminden iptal edilmesi ve akıllı kart merkezince yapılan teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.";
                break;
            case 'KDYS.FR.0072':
            case 'KDYS.FR.0082':
                $taahhut_metni = "Birimimiz/şahsım adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılması talep edilmektedir. Ayrıca T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceği taahhüt edilir.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 15px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <h5 style="margin: 0 0 10px 0; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center;">BALIKESİR ÜNİVERSİTESİ ELEKTRONİK POSTA (e-mail) ADRESİ KULLANIM KURALLARI</h5>
                    <p><strong>1- KANUNİ YÜKÜMLÜLÜK:</strong></p>
                    <p style="margin-left: 15px;">1.1- @balikesir.edu.tr domain’i T.C. Balıkesir Üniversitesi personeline (Akademik ve İdari) hizmet vermektedir. Bu hizmet akademik eğitim-öğretim amaçlı araştırma ve geliştirme faaliyetleri içermektedir.</p>
                    <p style="margin-left: 15px;">1.2- @balikesir.edu.tr domain’ine ait e-posta hesaplarını kullanan şahıslar Türkiye Cumhuriyeti kanun ve bunlara bağlı olan yönetmeliklere, TÜBİTAK ULAKBİM tarafından işletilen Ulusal Akademik Ağ\'ın (ULAKNET) kullanımına ilişkin usul ve esaslara, T.C. Balıkesir Üniversitesi yönetmeliklerine aykırı hareket edemezler.</p>
                    <p><strong>2- GİZLİLİK ve GÜVENLİK:</strong></p>
                    <p style="margin-left: 15px;">2.1- T.C. Balıkesir Üniversitesinden personel e-posta adresi talep eden şahıslar, bu formu doldurup personel kimlikleri ile birlikte Bilgi İşlem Dairesi Başkanlığına şahsen müracaat etmeleri gerekmektedir.</p>
                    <p style="margin-left: 15px;">2.2- Kullanıcı adı ve şifrenin seçimi ve korunması tamamıyla kullanıcının sorumluluğundadır.</p>
                </div>';
                break;
            case 'KDYS.FR.0073':
                $taahhut_metni = "Talep edilmiş olan e-imza mini kart okuyucuyu TÜBİTAK Bilişim ve Bilgi Güvenliği İleri Teknolojileri Araştırma Merkezi firmasından şahsen teslim aldığımı beyan ve taahhüt ederim.";
                break;
            case 'KDYS.FR.0074':
                $taahhut_metni = "Başvuru sahibi personellerimiz için e-imza sertifikası ve kart okuyucu talep edilmektedir. İlgili yasal mevzuat, kullanım politikaları ve taahhütlerin tarafımızca okunduğu ve bunlara uygun hareket edileceği beyan edilir.";
                break;
            case 'KDYS.FR.0077':
                $taahhut_metni = "Akademik/İdari çalışmalarımda kullanmak üzere, sistemde yukarıda belirtilen alan adının açılması, 150 MB web ve 20 MB veritabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaları okuduğumu ve bunlara uygun hareket edeceğimi taahhüt ederim.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 15px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <h5 style="margin: 0 0 10px 0; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center;">T.C. BALIKESİR ÜNİVERSİTESİ BİLİŞİM KAYNAKLARI KULLANIM POLİTİKASI</h5>
                    <p><strong>1. Tanımlamalar</strong></p>
                    <p style="margin-left: 15px;"><b>BAÜN Bilişim Kaynakları:</b> Mülkiyet hakları BAÜN’ ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                    <p style="margin-left: 15px;"><b>BAÜN Bilişim Kaynakları Kullanıcıları:</b> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                    <p style="margin-left: 15px;"><b>BAÜN Kullanıcıları:</b> BAÜN’ nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN’ de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır.</p>
                    <p><strong>2. Genel İlkeler</strong></p>
                    <p style="margin-left: 15px;">BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</p>
                </div>';
                break;
            case 'KDYS.FR.0078':
                $taahhut_metni = "Birimimiz adına kullanılmak üzere, bir adet statik ip'nin tarafımıza tahsis edilmesini talep ediyoruz. Kullanacağımız tüm bilgisayar, sunucu ve cihazlar birimimiz tarafından temin edilecektir. Bu statik ip'nin erişim sağlayıcı (gateway) olarak kullanılmayacağını, ayrıca T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 15px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <h5 style="margin: 0 0 10px 0; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center;">T.C. BALIKESİR ÜNİVERSİTESİ BİLİŞİM KAYNAKLARI KULLANIM POLİTİKASI</h5>
                    <p><strong>1. Tanımlamalar</strong></p>
                    <p style="margin-left: 15px;"><b>BAÜN Bilişim Kaynakları:</b> Mülkiyet hakları BAÜN’ ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                    <p style="margin-left: 15px;"><b>BAÜN Bilişim Kaynakları Kullanıcıları:</b> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                    <p style="margin-left: 15px;"><b>BAÜN Kullanıcıları:</b> BAÜN’ nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN’ de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır.</p>
                    <p><strong>2. Genel İlkeler</strong></p>
                    <p style="margin-left: 15px;">BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</p>
                </div>';
                break;
            case 'KDYS.FR.0079':
                $taahhut_metni = "Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen alan adının açılması, 250 MB web ve 100 MB veri tabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak yukarıda adı belirtilen personele teslim edilmesini talep ediyoruz. Ayrıca T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, eki Web Kullanıcıları Servis Politikası ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 15px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.5; margin-bottom: 15px;">
                    <h5 style="margin: 0 0 10px 0; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center;">T.C. BALIKESİR ÜNİVERSİTESİ BİLİŞİM KAYNAKLARI KULLANIM POLİTİKASI</h5>
                    <p><strong>1. Tanımlamalar</strong></p>
                    <p style="margin-left: 15px;"><b>BAÜN Bilişim Kaynakları:</b> Mülkiyet hakları BAÜN’ ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                </div>';
                break;
            case 'KDYS.FR.0080':
                $taahhut_metni = "TAAHHÜTNAME\n\nAnayasamızın 20 nci maddesinde “Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz.” denilmektedir. Bu kapsamda KPS’den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.";
                $ek_bilgi_ve_politikalar = '
                <div style="margin-top: 15px; padding: 15px; border: 1px solid #ddd; background: #fff; border-radius: 4px; font-size: 12px; line-height: 1.6; margin-bottom: 15px;">
                    10/07/2005 tarih ve 25871 sayılı resmi gazetede yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında “ikili anlaşma” imzalanmıştır.<br><br>
                    İlgili Yönetmeliğe ilişkin usul ve esasları içerisinde yer alan “Özel Hayatın Gizliliği” ve “Kişisel Verilerin Korunması” hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız için aşağıdaki taahhütname hazırlanmıştır.
                </div>';
                break;
        }
        
        if (!empty($taahhut_metni) || !empty($ek_bilgi_ve_politikalar)):
        ?>
            <div style="margin-top: 20px; padding: 15px; border: 1px solid #000; border-radius: 4px; background: #fafafa; font-size: 13px; line-height: 1.5; color: #333;">
                <h4 style="margin: 0 0 8px 0; color: #000; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Hüküm, Koşul ve Politikalar</h4>
                
                <?php if (!empty($ek_bilgi_ve_politikalar)) echo $ek_bilgi_ve_politikalar; ?>
                
                <?php if (!empty($taahhut_metni)): ?>
                    <p style="margin: 15px 0 5px 0; text-align: justify; font-weight: bold; color: #000;">ONAY BEYANI / TAAHHÜT:</p>
                    <p style="margin: 0 0 12px 0; text-align: justify; font-style: italic; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($taahhut_metni); ?></p>
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: bold; color: #1b656e;">
                        <span style="font-size: 18px; line-height: 1; color: #1b656e;">☑</span>
                        <span>Yukarıda belirtilen tüm hüküm, koşul, politika ve taahhütleri okudum, kabul ettim ve onaylıyorum.</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($basvuru['fotograf_yolu'])): ?>
            <p class="noprint"><strong>Yüklenen Fotoğraf / Ek Belge:</strong> <a href="<?php echo htmlspecialchars($basvuru['fotograf_yolu']); ?>" target="_blank">Görüntüle / İndir</a></p>
        <?php endif; ?>

        <?php if(!empty($basvuru['dekont_yolu'])): ?>
            <p class="noprint" style="background:#e8f4f8; padding:10px 15px; border-radius:5px; border-left:4px solid #1b656e;">
                <strong> Ödeme Dekontu:</strong> <a href="<?php echo htmlspecialchars($basvuru['dekont_yolu']); ?>" target="_blank" style="color:#1b656e; font-weight:bold;">Banka Dekontunu Görüntüle / İndir →</a>
            </p>

            <div class="only-print" style="margin-top:20px;">
                <h3 style="color:#1b656e; border-bottom:1px solid #ddd; padding-bottom:5px;">Ödeme Dekontu</h3>
                <?php 
                $ext = strtolower(pathinfo($basvuru['dekont_yolu'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                ?>
                    <img src="<?php echo htmlspecialchars($basvuru['dekont_yolu']); ?>" alt="Ödeme Dekontu" style="max-width:100%; max-height:800px; object-fit:contain; border:1px solid #ddd; border-radius:4px; display:block; margin:10px 0;">
                <?php elseif ($ext === 'pdf'): ?>
                    <iframe src="<?php echo htmlspecialchars($basvuru['dekont_yolu']); ?>" style="width:100%; height:700px; border:none;"></iframe>
                <?php else: ?>
                    <p><strong>Ödeme Dekontu Dosyası:</strong> <?php echo htmlspecialchars($basvuru['dekont_yolu']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- FORMA ÖZEL ONLINE YÖNETİCİ DOLDURMA ALANLARI (FİZİKİ İMZA KUTULARI TAMAMEN REMOVED) -->

        <?php if ($basvuru['form_kodu'] == 'KDYS.FR.0072' || $basvuru['form_kodu'] == 'KDYS.FR.0074'): ?>
            <!-- FORM 72 / 74 YÖNETİCİ BİLGİ İŞLEM İŞLEMLERİ ONLINE DOLDURMA -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 10px 0; color:#000; text-align:center; border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? $bugun); ?>"></td>
                        </tr>
                        <tr>
                            <th>E-posta Adresi *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[acilan_eposta]" value="<?php echo htmlspecialchars($veriler['acilan_eposta'] ?? ''); ?>" placeholder="örnek@balikesir.edu.tr"></td>
                        </tr>
                        <tr>
                            <th>E-posta Adresinin Geçerlilik Süresi *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[eposta_gecerlilik]" style="width:30%; display:inline-block;">
                                    <option value="Süresiz" <?php echo ($veriler['eposta_gecerlilik'] ?? '') == 'Süresiz' ? 'selected' : ''; ?>>Süresiz</option>
                                    <option value="Süreli" <?php echo ($veriler['eposta_gecerlilik'] ?? '') == 'Süreli' ? 'selected' : ''; ?>>Süreli</option>
                                </select>
                                <input type="date" class="yonetici-input" name="yonetici[eposta_gecerlilik_tarihi]" value="<?php echo htmlspecialchars($veriler['eposta_gecerlilik_tarihi'] ?? ''); ?>" style="width:65%; display:inline-block;">
                            </td>
                        </tr>
                        <tr>
                            <th>Kullanıcı Şifresi *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[kullanici_sifresi]" value="<?php echo htmlspecialchars($veriler['kullanici_sifresi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>İşlemi Yapan Personel *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0077'): ?>
            <!-- FORM 77 KİŞİSEL WEB SÖZLEŞMESİ ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 10px 0; color:#000; text-align:center; border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? $bugun); ?>"></td>
                        </tr>
                        <tr>
                            <th>Web Alanı Adı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[web_alani_adi]" value="<?php echo htmlspecialchars($veriler['web_alani_adi'] ?? ''); ?>" placeholder="kullanici.baun.edu.tr"></td>
                        </tr>
                        <tr>
                            <th>Veri Tabanı Kullanılacak mı? *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[veritabani_kullanilacak_mi]">
                                    <option value="Evet" <?php echo ($veriler['veritabani_kullanilacak_mi'] ?? '') == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                    <option value="Hayır" <?php echo ($veriler['veritabani_kullanilacak_mi'] ?? '') == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Sayfanın Geçerlilik Süresi *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[sayfa_gecerlilik]" style="width:30%; display:inline-block;">
                                    <option value="Süresiz" <?php echo ($veriler['sayfa_gecerlilik'] ?? '') == 'Süresiz' ? 'selected' : ''; ?>>Süresiz</option>
                                    <option value="Süreli" <?php echo ($veriler['sayfa_gecerlilik'] ?? '') == 'Süreli' ? 'selected' : ''; ?>>Süreli</option>
                                </select>
                                <input type="date" class="yonetici-input" name="yonetici[sayfa_gecerlilik_tarihi]" value="<?php echo htmlspecialchars($veriler['sayfa_gecerlilik_tarihi'] ?? ''); ?>" style="width:65%; display:inline-block;">
                            </td>
                        </tr>
                        <tr>
                            <th>Kullanıcı Adı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[kullanici_adi]" value="<?php echo htmlspecialchars($veriler['kullanici_adi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>Kullanıcı Şifresi *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[kullanici_sifresi]" value="<?php echo htmlspecialchars($veriler['kullanici_sifresi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>DNS Tanımı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[dns_tanimi]" value="<?php echo htmlspecialchars($veriler['dns_tanimi'] ?? ''); ?>"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0078'): ?>
            <!-- FORM 78 STATİK IP SÖZLEŞMESİ ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 10px 0; color:#000; text-align:center; border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? $bugun); ?>"></td>
                        </tr>
                        <tr>
                            <th>Statik IP *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[statik_ip]" value="<?php echo htmlspecialchars($veriler['statik_ip'] ?? ''); ?>" placeholder="192.168.x.x"></td>
                        </tr>
                        <tr>
                            <th>IP Geçerlilik Süresi *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[ip_gecerlilik]" style="width:30%; display:inline-block;">
                                    <option value="Süresiz" <?php echo ($veriler['ip_gecerlilik'] ?? '') == 'Süresiz' ? 'selected' : ''; ?>>Süresiz</option>
                                    <option value="Süreli" <?php echo ($veriler['ip_gecerlilik'] ?? '') == 'Süreli' ? 'selected' : ''; ?>>Süreli</option>
                                </select>
                                <input type="date" class="yonetici-input" name="yonetici[ip_gecerlilik_tarihi]" value="<?php echo htmlspecialchars($veriler['ip_gecerlilik_tarihi'] ?? ''); ?>" style="width:65%; display:inline-block;">
                            </td>
                        </tr>
                        <tr>
                            <th>DNS Tanımı (İstenirse)</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[dns_tanimi]" value="<?php echo htmlspecialchars($veriler['dns_tanimi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>İşlemi Yapan Personel *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0079'): ?>
            <!-- FORM 79 KURUMSAL WEB ADI SÖZLEŞMESİ ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 10px 0; color:#000; text-align:center; border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? $bugun); ?>"></td>
                        </tr>
                        <tr>
                            <th>Web Alanı Adı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[web_alani_adi]" value="<?php echo htmlspecialchars($veriler['web_alani_adi'] ?? ''); ?>" placeholder="birimadi.baun.edu.tr"></td>
                        </tr>
                        <tr>
                            <th>Veri Tabanı Kullanılacak mı? *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[veritabani_kullanilacak_mi]">
                                    <option value="Evet" <?php echo ($veriler['veritabani_kullanilacak_mi'] ?? '') == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                    <option value="Hayır" <?php echo ($veriler['veritabani_kullanilacak_mi'] ?? '') == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Sayfanın Geçerlilik Süresi *</th>
                            <td>
                                <select class="yonetici-input" name="yonetici[sayfa_gecerlilik]" style="width:30%; display:inline-block;">
                                    <option value="Süresiz" <?php echo ($veriler['sayfa_gecerlilik'] ?? '') == 'Süresiz' ? 'selected' : ''; ?>>Süresiz</option>
                                    <option value="Süreli" <?php echo ($veriler['sayfa_gecerlilik'] ?? '') == 'Süreli' ? 'selected' : ''; ?>>Süreli</option>
                                </select>
                                <input type="date" class="yonetici-input" name="yonetici[sayfa_gecerlilik_tarihi]" value="<?php echo htmlspecialchars($veriler['sayfa_gecerlilik_tarihi'] ?? ''); ?>" style="width:65%; display:inline-block;">
                            </td>
                        </tr>
                        <tr>
                            <th>Kullanıcı Adı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[kullanici_adi]" value="<?php echo htmlspecialchars($veriler['kullanici_adi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>Kullanıcı Şifresi *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[kullanici_sifresi]" value="<?php echo htmlspecialchars($veriler['kullanici_sifresi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>DNS Tanımı *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[dns_tanimi]" value="<?php echo htmlspecialchars($veriler['dns_tanimi'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th>İşlemi Yapan Personel *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0080'): ?>
            <!-- FORM 80 MERNİS TAAHHÜTNAMESİ ONLINE BİRİM YETKİLİSİ DOLDURMA -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 15px; border: 1px solid #000;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
                        <tr style="background:#f5f5f5; font-weight:bold; text-align:center;">
                            <td style="width: 50%; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 8px;">Personel Bilgisi</td>
                            <td style="width: 50%; border-bottom: 1px solid #000; padding: 8px;">Birim Yetkilisi (Yönetici Doldurabilir)</td>
                        </tr>
                        <tr>
                            <td style="width: 50%; border-right: 1px solid #000; padding: 12px; vertical-align: top;">
                                <p style="margin: 4px 0;"><strong>Adı Soyadı:</strong> <?php echo htmlspecialchars($veriler['personel_ad_soyad'] ?? $basvuru['ad_soyad']); ?></p>
                                <p style="margin: 4px 0;"><strong>Kurum Sicili, Unvanı:</strong> <?php echo htmlspecialchars($veriler['personel_sicil_unvan'] ?? '-'); ?></p>
                            </td>
                            <td style="width: 50%; padding: 12px; vertical-align: top;">
                                <p style="margin: 4px 0;"><strong>Adı Soyadı:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_ad_soyad]" value="<?php echo htmlspecialchars($veriler['yetkili_ad_soyad'] ?? ''); ?>" placeholder="Ad Soyad"></p>
                                <p style="margin: 4px 0;"><strong>Kurum Sicili, Unvanı:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_sicil_unvan]" value="<?php echo htmlspecialchars($veriler['yetkili_sicil_unvan'] ?? ''); ?>" placeholder="Sicil No / Unvan"></p>
                            </td>
                        </tr>
                        <tr style="background:#f5f5f5; font-weight:bold;">
                            <td style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 6px;">Personel</td>
                            <td style="border-top: 1px solid #000; padding: 6px;">Birim Yetkilisi</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 8px;">
                                <p style="margin: 2px 0;"><strong>T.C. Kimlik No:</strong> <?php echo htmlspecialchars($veriler['personel_tc_no'] ?? '-'); ?></p>
                                <p style="margin: 2px 0;"><strong>E-Mail:</strong> <?php echo htmlspecialchars($veriler['personel_eposta'] ?? '-'); ?></p>
                            </td>
                            <td style="border-top: 1px solid #000; padding: 8px;">
                                <p style="margin: 2px 0;"><strong>T.C. Kimlik No:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_tc_no]" value="<?php echo htmlspecialchars($veriler['yetkili_tc_no'] ?? ''); ?>" placeholder="TC Kimlik No"></p>
                                <p style="margin: 2px 0;"><strong>E-Mail:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_eposta]" value="<?php echo htmlspecialchars($veriler['yetkili_eposta'] ?? ''); ?>" placeholder="ornek@balikesir.edu.tr"></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Birim Yetkilisi Bilgilerini Kaydet</button>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0082'): ?>
            <!-- FORM 82 E-POSTA ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 10px 0; color:#000; text-align:center; border-bottom:1px solid #000; padding-bottom:5px; font-weight:bold;">Aşağıdaki Bölümü Boş Bırakınız (Bilgi İşlem Dairesince Doldurulacaktır)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:35%;">E-posta Adresi :</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[bilgi_islem_eposta]" value="<?php echo htmlspecialchars($veriler['bilgi_islem_eposta'] ?? ''); ?>" placeholder="örnek@balikesir.edu.tr"></td>
                        </tr>
                        <tr>
                            <th>Veriliş Tarihi :</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[verilis_tarihi]" value="<?php echo htmlspecialchars($veriler['verilis_tarihi'] ?? $bugun); ?>"></td>
                        </tr>
                        <tr>
                            <th>E-posta Hesabı Açan Personelin Onayı :</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php endif; ?>

        <?php if (!empty($admin_alanlari)): ?>
            <!-- DİNAMİK YÖNETİCİ / BİLGİ İŞLEM DOLDURMA KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div style="margin-top: 25px; border: 1px solid #000; border-radius: 4px; padding: 15px; background: #fff;">
                    <h4 style="margin:0 0 12px 0; color:#000; text-align:center; border-bottom:2px solid #000; padding-bottom:8px; font-weight:bold;">
                        ⚙️ BİLGİ İŞLEM DAİRESİ / YÖNETİCİ DOLDURMA ALANI (* Sadece Yönetici Tarafından İşlenebilir)
                    </h4>
                    <table class="grid-table" style="margin-bottom:15px; width:100%;">
                        <?php foreach ($admin_alanlari as $fa): 
                            $val = $veriler[$fa['name']] ?? '';
                        ?>
                            <tr>
                                <th style="width:35%; background:#f4f4f4; color:#000; padding:10px; border:1px solid #000; font-weight:bold;">
                                    <?php echo htmlspecialchars($fa['label']); ?> <?php echo $fa['required'] == 1 ? '*' : ''; ?>
                                </th>
                                <td style="padding:8px 10px; border:1px solid #000; background:#fff;">
                                    <?php if ($fa['type'] === 'textarea'): ?>
                                        <textarea class="yonetici-input" name="yonetici[<?php echo htmlspecialchars($fa['name']); ?>]" rows="3" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;"><?php echo htmlspecialchars(is_array($val) ? implode(', ', $val) : $val); ?></textarea>
                                    <?php elseif ($fa['type'] === 'select'): ?>
                                        <select class="yonetici-input" name="yonetici[<?php echo htmlspecialchars($fa['name']); ?>]" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                            <option value="">Seçiniz...</option>
                                            <?php 
                                            if (!empty($fa['secenekler'])) {
                                                $opts = explode(',', $fa['secenekler']);
                                                foreach ($opts as $o) {
                                                    $o = trim($o);
                                                    if ($o !== '') {
                                                        $sel = ($val === $o) ? 'selected' : '';
                                                        echo "<option value=\"".htmlspecialchars($o)."\" $sel>".htmlspecialchars($o)."</option>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    <?php elseif ($fa['type'] === 'checkbox'): ?>
                                        <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                            <?php 
                                            $val_arr = is_array($val) ? $val : array_map('trim', explode(',', (string)$val));
                                            if (!empty($fa['secenekler'])) {
                                                $opts = explode(',', $fa['secenekler']);
                                                foreach ($opts as $o) {
                                                    $o = trim($o);
                                                    if ($o !== '') {
                                                        $chk = in_array($o, $val_arr) ? 'checked' : '';
                                                        echo "<label style='margin-right:12px; cursor:pointer;'><input type='checkbox' name='yonetici[".htmlspecialchars($fa['name'])."][]' value='".htmlspecialchars($o)."' $chk> ".htmlspecialchars($o)."</label>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?php elseif ($fa['type'] === 'date'): ?>
                                        <input type="date" class="yonetici-input" name="yonetici[<?php echo htmlspecialchars($fa['name']); ?>]" value="<?php echo htmlspecialchars(is_array($val) ? implode(', ', $val) : $val); ?>" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                    <?php else: ?>
                                        <input type="<?php echo htmlspecialchars($fa['type'] ?: 'text'); ?>" class="yonetici-input" name="yonetici[<?php echo htmlspecialchars($fa['name']); ?>]" value="<?php echo htmlspecialchars(is_array($val) ? implode(', ', $val) : $val); ?>" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <div style="text-align:center;">
                        <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet" style="background:#1b656e; color:white; border:none; padding:10px 25px; border-radius:4px; cursor:pointer; font-weight:bold; font-size:14px;">✓ Yönetici Bilgilerini Kaydet</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <!-- FORMA ÖZEL TAAHHÜT, BİRİM YÖNETİCİSİ İMZA VE E-POSTA KULLANIM KURALLARI (OFFICIAL 3-PAGE FLOW) -->
        <?php if ($basvuru['form_kodu'] == 'KDYS.FR.0072' || strpos($basvuru['form_kodu'], '0072') !== false || $basvuru['form_kodu'] == 'KDYS.FR.0082' || $basvuru['form_kodu'] == 'KDYS.FR.0078' || strpos($basvuru['form_kodu'], '0078') !== false || $basvuru['form_kodu'] == 'KDYS.FR.0079' || strpos($basvuru['form_kodu'], '0079') !== false): ?>
            <!-- PAGE 1 BOTTOM: TAAHHÜT VE İMZA KUTUSU -->
            <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyoruz. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikalara okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
                
                <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:flex-end;">
                    <div>
                        <strong>Tarih:</strong> _____ / _____ / ________
                    </div>
                    <div style="text-align:center;">
                        <strong>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı</strong><br><br><br>
                        <strong>İmza</strong>
                    </div>
                </div>
            </div>

            <!-- PAGES 2 & 3: ELEKTRONİK POSTA KULLANIM KURALLARI MADDELERİ -->
            <div style="page-break-before: always; margin-top:30px; border-top:2px solid #000; padding-top:15px; font-size:11px; line-height:1.45; color:#000; font-family:Arial, sans-serif;">
                <h3 style="text-align:center; color:#000; font-size:12.5px; font-weight:bold; margin-bottom:12px; border-bottom:2px solid #000; padding-bottom:5px;">
                    BALIKESİR ÜNİVERSİTESİ ELEKTRONİK POSTA (e-mail) ADRESİ KULLANIM KURALLARI
                </h3>

                <h4 style="margin:8px 0 4px 0; font-size:11.5px; font-weight:bold;">1- KANUNİ YÜKÜMLÜLÜK:</h4>
                <p style="margin:2px 0;"><strong>1.1-</strong> @balikesir.edu.tr domain’i T.C. Balıkesir Üniversitesi personeline (Akademik ve İdari) hizmet vermektedir. Bu hizmet akademik eğitim-öğretim amaçlı araştırma ve geliştirme faaliyetleri içermektedir.</p>
                <p style="margin:2px 0;"><strong>1.2-</strong> @balikesir.edu.tr domain’ine ait e-posta hesaplarını kullanan şahıslar Türkiye Cumhuriyeti kanun ve bunlara bağlı olan yönetmeliklere, Türkiye Bilimsel ve Teknik Araştırma Kurumu'nun (TÜBİTAK) bir enstitüsü olan Ulusal Akademik Ağ ve Bilgi Merkezi (ULAKBİM) tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esaslara, T.C. Balıkesir Üniversitesi yönetmeliklerine aykırı hareket edemezler.</p>
                <p style="margin:2px 0 2px 12px;"><strong>1.2.1-</strong> İnternet Ortamında Yapılan Yayınların Düzenlenmesi ve Bu Yayınlar Yoluyla İşlenen Suçlarla Mücadele Edilmesi Hakkında Kanun. (Kanun/Karar No: 5651, Tarih: 23.05.2007)</p>
                <p style="margin:2px 0 2px 12px;"><strong>1.2.2-</strong> İnternet Ortamında Yapılan Yayınların Düzenlenmesine Dair Usul ve Esaslar Hakkında Yönetmelik (Tarih: 30.11.2007)</p>
                <p style="margin:2px 0 2px 12px;"><strong>1.2.3-</strong> Birlikte Çalışabilirlik Esasları Rehberi ile İlgili 2005/20 Sayılı Başbakanlık Genelgesi. (Tarih: 05.08.2005)</p>
                <p style="margin:2px 0 2px 12px;"><strong>1.2.4-</strong> Türkiye Bilimsel ve Teknik Araştırma Kurumu'nun (TÜBİTAK) bir enstitüsü olan Ulusal Akademik Ağ ve Bilgi Merkezi (ULAKBİM) tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esasları.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">2- GİZLİLİK ve GÜVENLİK:</h4>
                <p style="margin:2px 0;"><strong>2.1-</strong> T.C. Balıkesir Üniversitesinden personel e-posta adresi talep eden şahıslar, bu formu doldurup imzalayarak, personel kimlikleri ile birlikte Bilgi İşlem Dairesi Başkanlığına şahsen müracaat etmeleri gerekmektedir. Diğer talepler değerlendirmeye alınmayacaktır.</p>
                <p style="margin:2px 0;"><strong>2.2-</strong> Balıkesir Üniversitesinden e-posta adresi alan kişi, Bilgi İşlem Daire Başkanlığı’nın belirleyeceği bir e-posta hesap adı, öğrenci numarasından oluşan bir kullanıcı adı ve kendisinin belirleyeceği bir kullanıcı şifresine sahip olur.</p>
                <p style="margin:2px 0;"><strong>2.3-</strong> Kullanıcı adı ve e-posta adı kişiye özeldir ve @balikesir.edu.tr domaininde bir benzeri daha yoktur.</p>
                <p style="margin:2px 0;"><strong>2.4-</strong> Kullanıcı şifresi sadece kullanıcı tarafından bilinir. Kullanıcı dilediği zaman şifresini değiştirebilir. Şifrenin seçimi ve korunması tamamıyla kullanıcının sorumluluğundadır. Bilgi İşlem Daire Başkanlığı, şifre kullanımından doğacak problemlerden kesinlikle sorumlu değildir.</p>
                <p style="margin:2px 0;"><strong>2.5-</strong> E-posta şifresini unutan kullanıcılar, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığına bizzat müracaat etmek zorundadır.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">3- E-POSTA ADRESİ ALAN KİŞİNİN YÜKÜMLÜLÜKLERİ: (Kişi;)</h4>
                <p style="margin:1.5px 0;"><strong>3.1-</strong> E-posta hesabı sahibi, bu servisi kullanırken ileri sürdüğü şahsi fikir, düşünce ve ifadeler ile elektronik ortama eklediği dosya ve/veya bilgilerin sorumluluğunun şahsına ait olduğunu ve bundan dolayı bu e-posta ile ekli dosyalardan dolayı hiçbir şekilde Balıkesir Üniversitesinin sorumlu tutulmayacağını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.2-</strong> Balıkesir Üniversitesi e-posta hizmetlerinde, e-posta sitesinin geneline zarar verecek veya Balıkesir Üniversitesi’ni başka şahıs ya da kuruluşlarla adli (mahkemelik) duruma getirecek herhangi bir yazılım veya materyal bulunduramayacağını, paylaşamayacağını ve hukuki bir durum doğarsa tüm adli ve cezai sorumlulukları üstüne aldığını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.3-</strong> E-posta servisinin kullanımı sırasında kaybolacak ve/veya eksik alınacak, yanlış adrese iletilecek bilgi, mesaj ve dosyalardan Balıkesir Üniversitesi Bilgi İşlem Dairesi Başkanlığının sorumlu olmayacağını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.4-</strong> E-posta hesabı sahibi, teknik nedenlerden (arıza, güncelleme, aktarma vb.) dolayı e-postalardaki gecikme ve kayıplardan dolayı Balıkesir Üniversitesi Bilgi İşlem Dairesi Bşk. sorumlu olmayacağını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.5-</strong> E-posta hesabı sahibi, posta hesaplarındaki verilerinin, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın ihmali görülmeden, yetkisiz kişilerce okunmasından (e-posta sahiplerinin, gizli bilgilerini başka kişiler ile paylaşması, siteden ayrılırken çıkış yapmaması, vb. durumlardan) dolayı gelebilecek maddi ve manevi zararlardan ötürü, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın sorumlu olmadığını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.6-</strong> E-posta hesabı sahibi, başka şahıs veya kuruluşlardaki bilgisayara, bu bilgisayarlardaki bilgilere ya da yazılıma zarar verecek bilgi veya programlar göndermemeyi ve barındırmamayı, aksi takdirde doğacak tüm hukuki ve cezai sorumluluğun şahsına ait olduğunu kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.7-</strong> Üniversite e-posta servisini kullanarak elde edilen herhangi bir bilgi veya materyalin tamamıyla kullanıcının rızası dahilinde olduğunu, kullanıcı bilgisayarında yaratacağı arızalar, bilgi kaybı ve diğer kayıpların sorumluluğunun tamamıyla kendisine ait olduğunu, e-posta servisinin kullanımından dolayı uğrayabileceği zararlardan Balıkesir Üniversitesinin sorumlu olmadığını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.8-</strong> E-posta hesabı sahibi, genel ahlak ve adaba aykırı, ırkçı, ayrımcı, ticari, siyasi propaganda, taciz ve tehdit edici ile Türkiye Cumhuriyeti yasalarına, vatandaşı olduğu diğer ülkelerin yasalarına ve uluslararası anlaşmalara aykırı e-posta göndermemeyi, barındırmamayı ve bunlara aykırı her türlü uygulamalardan doğacak cezai ve hukuki sorumluluğun şahsına ait olduğunu kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.9-</strong> E-posta hesabı sahibi, T.C. Kanunlarına göre postalanması yasak, gizli olan bilgileri postalamamayı, barındırmamayı ve gönderilme yetkisi olmayan postaları dağıtmamayı ile bunlara ait yasal yükümlülüğü kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.10-</strong> E-posta hesabı sahibi, zincir posta (chain mail), yazılım virüsü vb. postaları başka posta hesaplarına dağıtmamayı, barındırmamayı ve bunlara ait cezai ve yasal yükümlülüğü kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.11-</strong> E-posta hesabı sahibi, rastgele ve alıcının istemi dışında mesaj (spam iletiler) göndermeyeceğini ve bunlara ait yasal yükümlülüğü kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.12-</strong> E-posta hesabı sahibi, e-posta kullanıcı adıyla yapacağı her türlü işlemden bizzat kendisinin sorumlu olduğunu kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.13-</strong> E-posta hesabı sahipleri, kullanım haklarını, doğrudan ya da dolaylı olarak 3. şahıslara devredemez ve kiralayamazlar.</p>
                <p style="margin:1.5px 0;"><strong>3.14-</strong> E-posta hesabı sahibi yasa ve kurallara aykırı davrandığı takdirde Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın gerekli müdahalelerde bulunma, kişiyi hizmet dışına çıkarma ve üyeliğine son verme hakkına sahip olduğunu kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.15-</strong> E-posta hesabı sahibi, yasa ve kurallara aykırı davrandığı takdirde T.C. Balıkesir Üniversitesi makamlarının; gerekli sözlü ve yazılı uyarıda bulunmaya, kişiyi sınırlı veya sınırsız hizmet dışına çıkarmaya, üniversite içi idari soruşturma başlatmaya ya da adli yargıya bildirimde bulunma hakkına sahip olduğunu kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.16-</strong> E-posta hesabı sahibi, e-posta hesabını tek taraflı olarak iptal ettirse bile, bu iptal işleminden önce, üyeliği sırasında gerçekleştirdiği icraatlardan kendisinin sorumlu olacağını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.17-</strong> E-posta sahibi, Balıkesir Üniversitesi e-posta hizmetinden yararlandığı sırada, kayıt formunda yer alan bilgilerin doğru olduğunu ve bu bilgilerin gerekli olduğu (şifre unutma gibi) durumlarda, bilginin hatalı veya noksan olmasından doğacak zararlardan dolayı sorumluluğun kendisine ait olduğunu, bu hallerde e-mail adresinin iptal edileceğini kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.18-</strong> E-posta hesabı sahibinin, e-posta hesabını 3 ay süre ile kullanmadığı takdirde e-posta hesabının içeriği silinecek, üniversiteden ayrılması (emeklilik,tayin,istifa,vb.) durumunda 30 gün içerisinde e-posta hesabı iptal edilecektir.</p>
                <p style="margin:1.5px 0;"><strong>3.19-</strong> T.C. Balıkesir Üniversitesi Bilgi İşlem Dairesi Başkanlığı, kullanıcıların bilgisi olmaksızın E-posta hizmetleri servis politikasında değişiklik yapma hakkına sahiptir. Kullanıcılar bu metinde yer alan bilgileri izlemek ve olası değişikliklerden haberdar olmakla yükümlüdürler.</p>
                <p style="margin:1.5px 0;"><strong>3.20-</strong> İmzalayan Taraf, ULAKBİM'in yasal ve teknolojik gelişmeleri göz önünde tutarak bu Kullanım Politikası'nı kısmen değiştirebileceğinden haberdardır ve bunu açıkça kabul eder. Değiştirilen Kullanım Politikası, http://ulakbim.tubitak.gov.tr/sites/images/Ulakbim/ukp-v2011.pdf adresinde yeralması ile birlikte yürürlüğe girer. İmzalayan Taraf ULAKBİM'in Kullanım Politikası'nı tam olarak anladığını, tanıdığını, uyacağını kabul eder.</p>
                <p style="margin:1.5px 0;"><strong>3.21-</strong> Tüm bu maddeleri daha sonra hiçbir itiraza mahal vermeyecek şekilde okudığını, KABUL ve TAAHHÜT ETMİŞTİR.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">4- YÜRÜRLÜLÜK:</h4>
                <p style="margin:2px 0;">Kullanıcı, adına düzenlenmiş bu formu doldurup imzaladıktan sonra bu sözleşme yürürlüğe girer ve T.C. Balıkesir Üniversitesi birimi olduğu sürece devam eder.</p>
            </div>
        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0077' || strpos($basvuru['form_kodu'], '0077') !== false): ?>
            <!-- PAGE 1 BOTTOM: PERSONEL TAAHHÜT VE İMZA KUTUSU -->
            <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
                Akademik/İdari çalışmalarımda kullanılmak üzere, sitemizde yukarıda belirtilen alan adının, 150 MB web ve 20 MB veritabanı (otomasyon) kuralı alan olarak tahsis edilmesini ve bu alanların kullanımına izin verilerek web alanının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikalara okunduğu ve bunlara uygun hareket edileceğini taahhüt ederim.
                
                <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:flex-end;">
                    <div>
                        <strong>Tarih:</strong> _____ / _____ / ________
                    </div>
                    <div style="text-align:center;">
                        <strong>PERSONEL</strong><br><br><br>
                        <strong>İmza</strong>
                    </div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; justify-content:space-between; font-size:11px; font-weight:bold; color:#000;">
                <div>Ek: Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası</div>
                <div>* Bilgi İşlem Dairesince doldurulacaktır.</div>
            </div>

            <!-- PAGES 2, 3, 4: BAÜN BİLİŞİM KAYNAKLARI KULLANIM POLİTİKASI -->
            <div style="page-break-before: always; margin-top:30px; border-top:2px solid #000; padding-top:15px; font-size:11px; line-height:1.45; color:#000; font-family:Arial, sans-serif;">
                <h3 style="text-align:center; color:#000; font-size:12.5px; font-weight:bold; margin-bottom:12px; border-bottom:2px solid #000; padding-bottom:5px;">
                    BAÜN BİLİŞİM KAYNAKLARI KULLANIM POLİTİKASI
                </h3>

                <h4 style="margin:8px 0 4px 0; font-size:11.5px; font-weight:bold;">1. Tanımlamalar</h4>
                <p style="margin:2px 0;"><strong>BAÜN Bilişim Kaynakları:</strong> Mülkiyet hakları BAÜN’ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                <p style="margin:2px 0;"><strong>BAÜN Bilişim Kaynakları Kullanıcıları:</strong> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                <p style="margin:2px 0;"><strong>BAÜN Kullanıcıları:</strong> BAÜN’nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN’de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır. Bu kullanıcılar, BAÜN Bilişim Kaynaklarını doğrudan kullanım hakkına sahiptir.</p>
                <p style="margin:2px 0;"><strong>Kapsamdışı Kullanıcılar:</strong> BAÜN Bilişim Kaynaklarını, BAÜN Kullanıcıları ve Özel Kullanıcılar başlığı altında tanımlandığı biçimiyle kullanım hakkına sahip olmayan, sadece genel kullanıma açık kaynak ya da servisleri (Örneğin; BAÜN web sayfaları, BAÜN Elektronik Liste Servisi, ftp servisi vb.) kullanan kişi ve kuruluşlar Kapsamdışı Kullanıcılar olarak tanımlanır.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">2. Kullanım</h4>
                <p style="margin:2px 0;"><strong>Temel Kullanım:</strong> BAÜN Bilişim Kaynaklarının, Üniversitenin eğitim, öğretim, araştırma, geliştirme, toplumsal hizmet ve idari/yönetimsel faaliyetleri ile doğrudan ilişkili olan kullanımı “Temel Kullanım” olarak tanımlanır.</p>
                <p style="margin:2px 0;"><strong>İkincil (tali) Kullanım:</strong> Temel Kullanım tanımı dışında kalan her türlü kullanım, “İkincil (tali) Kullanım” olarak tanımlanır. Kaynakların, ancak Temel Kullanım kapsamında ihtiyaç duyulmayan atıl kapasitesinin bu amaç için kullanılabilmesi söz konusudur. İkincil Kullanım, Temel Kullanımı kısıtlayıcı/engelleyici boyutlara ulaştığında “genel ilkelere aykırı kullanım” kapsamına girer.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">3. Genel İlkeler</h4>
                <p style="margin:2px 0;"><strong>1.</strong> BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</p>
                <p style="margin:2px 0;"><strong>2.</strong> BAÜN Bilişim Kaynaklarını kullanıma sunan birimler;</p>
                <p style="margin:1px 0 1px 12px;">• Kullanıcı bilgilerinin gizliliğini, mahremiyetini korumalı,</p>
                <p style="margin:1px 0 1px 12px;">• Kaynakların adil olarak paylaştırılmasını sağlamalı,</p>
                <p style="margin:1px 0 1px 12px;">• Kaynağa yönelik tehditleri en aza indirebilmek için risk düzeylerine göre güvenlik önlemlerini almalı,</p>
                <p style="margin:1px 0 1px 12px;">• Kritik olma düzeyine göre kaynakları yedeklemeli,</p>
                <p style="margin:1px 0 1px 12px;">• Güvenliği ilgilendiren durumlarda kanıt özelliği taşıyabilecek bilgileri, kaynakları kullananların kimliğinin tespit edilmesini sağlayacak düzende tutmalıdır.</p>
                <p style="margin:2px 0;"><strong>3.</strong> BAÜN Bilişim Kaynakları kullanıcıları, Temel Kullanım kapsamında kullanımlarına tahsis edilen mülkiyetin, kendilerine ait olan kaynakların güvenliği ile ilgili kişisel önlemlerini almalı, bu kaynaklar üzerinde yer alan bilgileri, kritik olma düzeyine göre yedeklemelidir.</p>
                <p style="margin:2px 0;"><strong>4.</strong> BAÜN Bilişim Kaynakları, BAÜN yönetiminin yetkilendirdiği makamlarca belirlenmiş kurallar ve yönergeler çerçevesinde, yetkinin veriliş amacını aşmayacak şekilde ve yapılacak her iş için uygun yetkilendirme ile kullanılmalı, yetki almadan değiştirilmemeli, ortadan kaldırılmamalıdır.</p>
                <p style="margin:2px 0;"><strong>5.</strong> BAÜN Bilişim Kaynakları, bu kaynaklar kullanılarak oluşturulan ve bu kaynaklar üzerinde barındırılan/kullanılan her türlü kaynağın (yazılım, donanım, ağ kaynağı, ...) kullanım kurallarına ve koşullarına (izin, kaynak gösterim koşulu, telif hakkı, lisans koşulları, ağ kullanım kuralları, vb.) uyularak kullanılmalıdır.</p>
                <p style="margin:2px 0;"><strong>6.</strong> BAÜN Bilişim Kaynakları;</p>
                <p style="margin:1px 0 1px 12px;">• Kullanım hakkını, doğrudan ya da dolaylı olarak devretmek ya da kiralamak amacıyla,</p>
                <p style="margin:1px 0 1px 12px;">• Ticari nitelik taşıyan ve gelir teminine yönelik kullanımlar için, Rektörlük makamından izin alınmadan, kullanılmamalıdır.</p>
                <p style="margin:2px 0;"><strong>7.</strong> BAÜN Bilişim Kaynakları;</p>
                <p style="margin:1px 0 1px 12px;">• BAÜN içi bilgi kaynaklarını (duyuru, haber, doküman vb.), yetkisiz ve/veya izinsiz olarak 3. kişilere/kuruluşlara dağıtmak amacıyla,</p>
                <p style="margin:1px 0 1px 12px;">• BAÜN’ye ve 3. kişilere/kuruluşlara ait bilgilere ve kaynaklara (bilgisayar, bilgisayar ağı, yazılım ve servisler) izinsiz ve/veya yetkisiz erişim sağlamak amacıyla,</p>
                <p style="margin:1px 0 1px 12px;">• Diğer kullanıcıların kaynak kullanım hakkını engelleyici faaliyetlerde bulunmak amacıyla,</p>
                <p style="margin:1px 0 1px 12px;">• Kaynaklara zarar verici/kaynakların güvenliğini tehdit edici biçimde kullanılmamalıdır.</p>
                <p style="margin:2px 0;"><strong>8.</strong> BAÜN Bilişim Kaynakları;</p>
                <p style="margin:1px 0 1px 12px;">• Genel ahlak ilkelerine aykırı materyal üretmek, barındırmak, iletmek,</p>
                <p style="margin:1px 0 1px 12px;">• Siyasi propaganda yapmak,</p>
                <p style="margin:1px 0 1px 12px;">• Rastgele ve alıcının istemi dışında mesaj (SPAM iletiler) göndermeyeceğini ve bunlara ait yasal yükümlülüğü kabul eder.</p>
                <p style="margin:2px 0;"><strong>9.</strong> BAÜN Bilişim Kaynakları, Üniversite yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve bunlara bağlı olan yönetmeliklere aykırı faaliyetlerde bulunmak amacıyla kullanılamaz.</p>
                <p style="margin:2px 0;"><strong>10.</strong> “Yasal Sorumluluk Reddi (Disclaimer)” metinleri, Genel İlkelere aykırı kullanımların kabul edilebilir olduğunu göstermez.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">4. Yetki ve Sorumluluklar</h4>
                <p style="margin:2px 0;">• Üniversite, temel amaçları doğrultusunda, BAÜN Bilişim Kaynaklarını kullanıcılarına sunar, bu hizmetlerin çalışırlığını ve sürekliliğini sağlar.</p>
                <p style="margin:2px 0;">• BAÜN Bilişim Kaynakları kullanıcıları, BAÜN sunucuları üzerinde kendilerine tahsis edilen “Kullanıcı Adı/Şifre” ikilisi ve/veya IP (Internet Protokol) adresi kullanılarak gerçekleştirdikleri her türlü etkinlikten, BAÜN Bilişim Kaynaklarını kullanarak oluşturdukları ve/veya kendilerine tahsis edilen BAÜN Bilişim Kaynağı üzerinde bulundurdukları her türlü kaynağın (belge, doküman, yazılım, vb.) içeriğinden, kaynağın kullanımı hakkında yetkili makamlar tarafından talep edilen bilgilerin doğru ve eksiksiz verilmesinden, yedeklerinin tutulmasından, ilgili kaynağın kullanım kurallarına, Üniversite Yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve yasal mevzuata karşı birebir kendileri sorumludur.</p>
                <p style="margin:2px 0;">• BAÜN yönetimi, BAÜN Kullanıcıları ve Özel Kullanıcılar ile üçüncü kişi veya kuruluşlar arasında doğabilecek her türlü ihtilaf durumunda doğrudan taraf olma hakkını saklı tutar.</p>
                <p style="margin:2px 0;">• BAÜN Rektörlüğü ve/veya yetkilendirdiği birimler, BAÜN Bilişim Kaynakları kullanımı hakkında genel-geçer kuralları belirleyip, bu kuralları gelişen teknolojinin öngördüğü biçimde sürekli olarak değerlendirir ve gerekli değişiklikleri hayata geçirir. Bu tür değişiklikler yapıldığında genel duyuru mekanizmaları ile kullanıcılar bilgilendirilir.</p>

                <h4 style="margin:10px 0 4px 0; font-size:11.5px; font-weight:bold;">5. Uygulama ve Yaptırımlar</h4>
                <p style="margin:2px 0;">BAÜN makamları, BAÜN Bilişim Kaynaklarının “Genel İlkelere” aykırı etkinlikler dâhilinde kullanılması durumunda, gerçekleştirilen eylemin yoğunluğuna, kaynaklara veya kişi/kurumlara verilen zararın boyutuna ve tekrarına aşağıdaki işlemlerin bir ya da birden fazla maddesini, sıra ile ya da sırasız uygulayabilir;</p>
                <p style="margin:1px 0 1px 12px;">• Kullanıcı sözlü ve/veya yazılı olarak uyarılır</p>
                <p style="margin:1px 0 1px 12px;">• Kullanıcıya tahsis edilmiş BAÜN Bilişim Kaynakları sınırlı veya sınırsız süre ile kapatılabilir</p>
                <p style="margin:1px 0 1px 12px;">• Üniversite bünyesindeki akademik/idari soruşturma mekanizmaları harekete geçirilebilir</p>
                <p style="margin:1px 0 1px 12px;">• Adli yargı mekanizmaları harekete geçirilebilir.</p>
                <p style="margin:2px 0;">Kullanım ve Kullanıcı tanımlarının yetersiz kaldığı ya da “BAÜN Bilişim Kaynakları Kullanım Politikası” belgesi dâhilinde tanımlı olmayan durumlar BAÜN makamlarınca değerlendirilir.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['print']) && $_GET['print'] == 1): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        </script>
    <?php endif; ?>
</body>
</html>