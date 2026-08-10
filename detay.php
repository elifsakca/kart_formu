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
            <button onclick="window.print()" style="font-size: 14px; padding: 8px 16px;"> PDF İndir</button>
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
            case 'KDYS.FR.0073':
            case 'KDYS.FR.0074':
            case 'KDYS.FR.0077':
            case 'KDYS.FR.0078':
            case 'KDYS.FR.0079':
            case 'KDYS.FR.0080':
                // Bu resmi formların tüm taahhüt, mevzuat ve politikaları alttaki 3 sayfalık resmi çıktıda basılır.
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

        <!-- FORMA ÖZEL TAAHHÜT, BİRİM YÖNETİCİSİ İMZA VE E-POSTA KULLANIM KURALLARI (MODÜLER FORM YÜKLEME) -->
        <?php
        $form_num = preg_replace('/[^0-9]/', '', $basvuru['form_kodu']);
        $form_num_short = ltrim($form_num, '0');
        $form_file_path = __DIR__ . '/forms/' . $form_num_short . '.php';

        if (file_exists($form_file_path)) {
            $mode = 'print';
            include $form_file_path;
        } elseif (file_exists(__DIR__ . '/forms/genel.php')) {
            $mode = 'print';
            include __DIR__ . '/forms/genel.php';
        }
        ?>
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