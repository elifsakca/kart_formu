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

$admin_id  = $_SESSION['admin_id'] ?? 0;
$admin_rol = $_SESSION['admin_rol'] ?? 'admin';

// Güvenlik: Normal admin sadece izinli olduğu formu görebilir
if ($admin_rol !== 'superadmin') {
    $permStmt = $db->prepare("SELECT COUNT(*) FROM yonetici_izinleri WHERE yonetici_id = :yid AND form_kodu = :fkodu");
    $permStmt->execute([':yid' => $admin_id, ':fkodu' => $basvuru['form_kodu']]);
    if ($permStmt->fetchColumn() == 0) {
        die("<div style='font-family:sans-serif; padding:50px; text-align:center;'><h2>⚠️ Erişim Engellendi</h2><p>Bu başvuru formunu (".htmlspecialchars($basvuru['form_kodu']).") görüntüleme yetkiniz bulunmamaktadır.</p><br><a href='panel.php' style='color:#1b656e; font-weight:bold;'>Panele Dön</a></div>");
    }
}

$veriler = json_decode($basvuru['form_verileri'], true) ?? [];

// YÖNETİCİ TARAFINDAN GİRİLEN BİLGİLERİ KAYDETME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yonetici_kaydet'])) {
    $yonetici_verileri = $_POST['yonetici'] ?? [];
    
    foreach ($yonetici_verileri as $k => $v) {
        $veriler[$k] = $v;
    }
    
    $yeni_json = json_encode($veriler, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $updateStmt = $db->prepare("UPDATE basvurular SET form_verileri = :fv WHERE id = :id");
    $updateStmt->execute([':fv' => $yeni_json, ':id' => $id]);
    
    header("Location: detay.php?id={$id}&kaydedildi=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Detayı #<?php echo $basvuru['id']; ?> - BAÜN</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f6f9; color: #333; }
        .noprint { display: flex; justify-content: space-between; align-items: center; background: #1b656e; color: white; padding: 15px 30px; }
        .noprint a { color: white; text-decoration: none; font-weight: bold; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px; }
        .noprint button { background: #27ae60; color: white; border: none; padding: 8px 18px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        
        .paper { background: white; max-width: 900px; margin: 30px auto; padding: 40px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .paper-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1b656e; padding-bottom: 15px; margin-bottom: 25px; }
        .paper-header h2 { margin: 0; color: #1b656e; font-size: 22px; }
        .paper-header p { margin: 5px 0 0 0; color: #666; font-size: 13px; }
        
        .photo-box { width: 120px; height: 150px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 6px; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .grid-table th, .grid-table td { border: 1px solid #e0e0e0; padding: 10px 12px; font-size: 14px; text-align: left; }
        .grid-table th { background: #f8f9fa; width: 30%; color: #1b656e; font-weight: 600; }
        
        .status-badge { padding: 6px 14px; border-radius: 15px; font-size: 13px; font-weight: bold; display: inline-block; background: #e8f4f8; color: #1b656e; }

        /* Yönetici Online Doldurma Alanı Stilleri */
        .yonetici-input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #1b656e;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
            background-color: #fff;
        }
        .yonetici-input:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.4);
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

        @media print {
            .noprint, .btn-yonetici-kaydet, .kayit-bildirimi { display: none !important; }
            body { background: white; }
            .paper { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 0; }
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
            <button onclick="window.print()">Yazdır / PDF Çıktısı Al</button>
        </div>
    </div>

    <?php if(isset($_GET['kaydedildi']) && $_GET['kaydedildi'] == 1): ?>
        <div class="kayit-bildirimi" style="max-width:900px; margin:15px auto -15px auto; background:#d4edda; color:#155724; padding:12px 20px; border-radius:5px; border-left:5px solid #28a745; font-weight:bold;">
            ✓ Yönetici tarafından girilen bilgiler başarıyla veritabanına kaydedildi!
        </div>
    <?php endif; ?>

    <div class="paper">
        <div class="paper-header">
            <div>
                <h2>BALIKESİR ÜNİVERSİTESİ</h2>
                <p><strong>Form Kodu / Adı:</strong> <?php echo htmlspecialchars($basvuru['form_kodu'] . ' - ' . $basvuru['form_adi']); ?></p>
                <p><strong>Başvuru No:</strong> #<?php echo $basvuru['id']; ?> | <strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></p>
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

        <h3 style="color:#1b656e; border-bottom:1px solid #ddd; padding-bottom:5px;">Forma Girilen Tüm Detaylar</h3>
        <table class="grid-table">
            <tbody>
                <?php 
                if (is_array($veriler)) {
                    foreach ($veriler as $anahtar => $deger) {
                        if ($anahtar == 'form_kodu' || $anahtar == 'form_adi') continue;
                        
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

        <?php if(!empty($basvuru['fotograf_yolu'])): ?>
            <p><strong>Yüklenen Fotoğraf / Ek Belge:</strong> <a href="<?php echo htmlspecialchars($basvuru['fotograf_yolu']); ?>" target="_blank">Görüntüle / İndir</a></p>
        <?php endif; ?>

        <!-- FORMA ÖZEL İMZA VE YÖNETİCİ DOLDURMA ALANLARI -->

        <?php if ($basvuru['form_kodu'] == 'F-52'): ?>
            <!-- F-52 İMZA ALANI -->
            <div style="margin-top: 40px; border: 1px solid #000;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 12px;">
                    <tr>
                        <td style="width: 50%; border-right: 1px solid #000; padding: 15px; vertical-align: top;">
                            <strong>Formu hazırlayan</strong><br><br><br>
                            ____ / ____ / ________<br>
                            <span style="font-size: 10px; color: #777;">İmza / Kaşe</span>
                        </td>
                        <td style="width: 50%; padding: 15px; vertical-align: top;">
                            <strong>Fakülte/Yüksekokul/Meslek Yüksekokul Sekreteri/<br>Müdür/Daire Başkanı/Birim Amiri/Firma Sorumlusu</strong><br><br>
                            ____ / ____ / ________<br>
                            <span style="font-size: 10px; color: #777;">İmza / Kaşe</span>
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'F-53'): ?>
            <!-- F-53 İMZA ALANI -->
            <div style="margin-top: 40px; border: 1px solid #000;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 12px;">
                    <tr>
                        <td style="width: 50%; border-right: 1px solid #000; padding: 15px; vertical-align: top;">
                            <strong>Kart İşlem Formunu Hazırlayan</strong><br><br>
                            ____ / ____ / ________<br><br>
                            Ad Soyad: ___________________<br>
                            <span style="font-size: 10px; color: #777;">İmza / Kaşe</span>
                        </td>
                        <td style="width: 50%; padding: 15px; vertical-align: top;">
                            <strong>Fakülte/Enstitü/Yüksekokul/Meslek Yüksekokul<br>Sekreteri / Müdürü</strong><br><br>
                            ____ / ____ / ________<br><br>
                            Ad Soyad: ___________________<br>
                            <span style="font-size: 10px; color: #777;">İmza / Kaşe</span>
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'F-54'): ?>
            <!-- F-54 BEYAN VE İMZA ALANI -->
            <div style="margin-top: 25px; font-size: 13px; line-height: 1.6; background: #fafafa; padding: 15px; border: 1px solid #ddd; text-align: justify;">
                Aşağıda belirttiğim adıma kayıtlı olan akıllı kimlik kartımı kaybettim. Eski kimlik kartımın AKS sisteminden iptal edilmesini ve bedeli karşılığında yeni kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </div>
            <div style="margin-top: 30px; border: 1px solid #ccc; padding: 20px; text-align: right; font-size: 13px;">
                <p><strong>Başvuru Sahibi:</strong> <?php echo htmlspecialchars($basvuru['ad_soyad'] ?? ''); ?></p>
                <p><strong>Tarih:</strong> ____ / ____ / ________</p>
                <br>
                <p><strong>İmza:</strong> ______________________</p>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'F-55'): ?>
            <!-- F-55 BEYAN VE İMZA ALANI -->
            <div style="margin-top: 25px; font-size: 13px; line-height: 1.6; background: #fafafa; padding: 15px; border: 1px solid #ddd; text-align: justify;">
                Eski kimlik kartımın AKS sisteminden iptal edilmesi ve akıllı kart merkezince yapılan teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </div>
            <div style="margin-top: 30px; border: 1px solid #ccc; padding: 20px; text-align: right; font-size: 13px;">
                <p><strong>Başvuru Sahibi:</strong> <?php echo htmlspecialchars($basvuru['ad_soyad'] ?? ''); ?></p>
                <p><strong>Tarih:</strong> ____ / ____ / ________</p>
                <br>
                <p><strong>İmza:</strong> ______________________</p>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0072'): ?>
            <!-- FORM 72 YÖNETİCİ BİLGİ İŞLEM İŞLEMLERİ ONLINE DOLDURMA -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div style="margin-top: 30px; border: 1px solid #000; padding: 15px; text-align: center; font-size: 12px;">
                    <strong>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı</strong><br><br>
                    Tarih: ____ / ____ / ________<br><br>
                    İmza: ______________________
                </div>

                <div style="margin-top: 25px; border: 2px solid #1b656e; border-radius: 6px; padding: 15px; background: #fafafa;">
                    <h4 style="margin:0 0 10px 0; color:#1b656e; text-align:center; border-bottom:1px solid #ccc; padding-bottom:5px;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? ''); ?>"></td>
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
                            <th>İşlemi Yapan Personel ve İmzası *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0073'): ?>
            <!-- FORM 73 TESLİM ALAN BEYANI VE İMZA BOŞLUĞU (FIZIKI ÇIKTI İÇİN BOŞ YER) -->
            <div style="margin-top: 25px; font-size: 13px; line-height: 1.6; background: #fafafa; padding: 15px; border: 1px solid #ddd; text-align: justify;">
                Yukarıda belirtilen tarihte talep etmiş olduğum e-imza mini kart okuyucuyu TÜBİTAK Bilişim ve Bilgi Güvenliği İleri Teknolojileri Araştırma Merkezi firmasından tarafımca teslim aldığımı beyan ederim.
            </div>
            <div style="margin-top: 25px; border: 1px solid #000; padding: 20px; max-width: 450px; margin-left: auto; font-size: 13px;">
                <h4 style="margin:0 0 10px 0; border-bottom:1px solid #000; padding-bottom:5px;">TESLİM ALAN BİLGİLERİ</h4>
                <p style="margin:5px 0;"><strong>Adı Soyadı :</strong> ____________________________________</p>
                <p style="margin:5px 0;"><strong>Görevi / Unvanı :</strong> ____________________________________</p>
                <p style="margin:15px 0 5px 0;"><strong>İmzası :</strong> ____________________________________</p>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0074'): ?>
            <!-- FORM 74 E-İMZA TABLOSUNA İMZA SÜTUNU NOTU -->
            <div style="margin-top: 25px; border: 1px solid #1b656e; padding: 15px; background:#f9f9f9; border-radius:5px;">
                <h4 style="margin:0 0 10px 0; color:#1b656e;">E-İmza Teslim / Onay Tablosu (Fiziki Çıktı İçin İmza Sütunlu)</h4>
                <table class="grid-table" style="font-size:12px;">
                    <thead>
                        <tr style="background:#1b656e; color:white;">
                            <th>TC Kimlik No</th>
                            <th>Ad Soyad</th>
                            <th>Birim / Görev</th>
                            <th>Başvuru Türü</th>
                            <th style="width:120px; text-align:center;">Fiziki İmza</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $eimza_tcler = $veriler['eimza_tc'] ?? [];
                        $eimza_adlar = $veriler['eimza_ad'] ?? [];
                        $eimza_soyadlar = $veriler['eimza_soyad'] ?? [];
                        $eimza_birimler = $veriler['eimza_birim'] ?? [];
                        $eimza_turler = $veriler['eimza_basvuru_turu'] ?? [];

                        if (is_array($eimza_tcler) && count($eimza_tcler) > 0) {
                            for ($i = 0; $i < count($eimza_tcler); $i++) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($eimza_tcler[$i] ?? '-') . '</td>';
                                echo '<td><strong>' . htmlspecialchars(($eimza_adlar[$i] ?? '') . ' ' . ($eimza_soyadlar[$i] ?? '')) . '</strong></td>';
                                echo '<td>' . htmlspecialchars(($eimza_birimler[$i] ?? '-')) . '</td>';
                                echo '<td>' . htmlspecialchars(($eimza_turler[$i] ?? '-')) . '</td>';
                                echo '<td style="border:1px solid #000; height:35px;"></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0077'): ?>
            <!-- FORM 77 KİŞİSEL WEB SÖZLEŞMESİ İMZA VE ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div style="margin-top: 30px; border: 1px solid #000; padding: 15px; text-align: center; font-size: 12px;">
                    <strong>PERSONEL</strong><br><br>
                    Tarih: ____ / ____ / ________<br><br>
                    İmza: ______________________
                </div>

                <div style="margin-top: 25px; border: 2px solid #1b656e; border-radius: 6px; padding: 15px; background: #fafafa;">
                    <h4 style="margin:0 0 10px 0; color:#1b656e; text-align:center; border-bottom:1px solid #ccc; padding-bottom:5px;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? ''); ?>"></td>
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
                        <tr>
                            <th>İşlemi Yapan Personel ve İmzası *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0078'): ?>
            <!-- FORM 78 STATİK IP SÖZLEŞMESİ İMZA VE ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div style="margin-top: 30px; border: 1px solid #000; padding: 15px; text-align: center; font-size: 12px;">
                    <strong>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı</strong><br><br>
                    Tarih: ____ / ____ / ________<br><br>
                    İmza: ______________________
                </div>

                <div style="margin-top: 25px; border: 2px solid #1b656e; border-radius: 6px; padding: 15px; background: #fafafa;">
                    <h4 style="margin:0 0 10px 0; color:#1b656e; text-align:center; border-bottom:1px solid #ccc; padding-bottom:5px;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? ''); ?>"></td>
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
                            <th>İşlemi Yapan Personel ve İmzası *</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[islem_yapan_personel]" value="<?php echo htmlspecialchars($veriler['islem_yapan_personel'] ?? ''); ?>" placeholder="Ad Soyad"></td>
                        </tr>
                    </table>
                    <button type="submit" name="yonetici_kaydet" class="btn-yonetici-kaydet">Yönetici Bilgilerini Kaydet</button>
                </div>
            </form>

        <?php elseif ($basvuru['form_kodu'] == 'KDYS.FR.0079'): ?>
            <!-- FORM 79 KURUMSAL WEB ADI SÖZLEŞMESİ İMZA VE ONLINE BİLGİ İŞLEM KUTUSU -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div style="margin-top: 30px; border: 1px solid #000; padding: 15px; text-align: center; font-size: 12px;">
                    <strong>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı</strong><br><br>
                    Tarih: ____ / ____ / ________<br><br>
                    İmza: ______________________
                </div>

                <div style="margin-top: 25px; border: 2px solid #1b656e; border-radius: 6px; padding: 15px; background: #fafafa;">
                    <h4 style="margin:0 0 10px 0; color:#1b656e; text-align:center; border-bottom:1px solid #ccc; padding-bottom:5px;">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ (* Yönetici Tarafından Doldurulabilir)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:30%;">İşlem Tarihi *</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[islem_tarihi]" value="<?php echo htmlspecialchars($veriler['islem_tarihi'] ?? ''); ?>"></td>
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
                            <th>İşlemi Yapan Personel ve İmzası *</th>
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
                <div style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e; padding: 10px; border-radius: 4px; margin-top: 25px;">
                    KİMLİK PAYLAŞIM SİSTEMİ (KPS) KULLANICI TAAHHÜTNAMESİ<br>
                    <span style="font-weight: normal; font-style: italic; font-size: 12px;">- Gizlilik Taahhüt Belgesi -</span>
                </div>

                <div style="font-size: 12.5px; line-height: 1.5; margin-top: 15px; text-align: justify; background: #fafafa; padding: 12px; border: 1px solid #eee;">
                    <strong>AÇIKLAMA:</strong> 10/07/2005 tarih ve 25871 sayılı Resmi Gazete'de yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında "ikili anlaşma" imzalanmıştır. İlgili Yönetmeliğe ilişkin usul ve esaslar içerisinde yer alan "Özel Hayatın Gizliliği" ve "Kişisel Verilerin Korunması" hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız için aşağıdaki taahhütname hazırlanmıştır.
                </div>

                <div style="font-size: 12.5px; line-height: 1.5; margin-top: 15px; text-align: justify; background: #fafafa; padding: 12px; border: 1px solid #eee; font-weight: bold;">
                    TAAHHÜTNAME: Anayasamızın 20. maddesinde "Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz." denilmektedir. Bu kapsamda KPS'den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı, aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.
                </div>

                <div style="text-align: right; margin-top: 15px; font-size: 13px; font-weight: bold;">
                    Tarih: <?php echo date('d.m.Y', strtotime($veriler['taahhut_tarihi'] ?? $basvuru['kayit_tarihi'])); ?>
                </div>

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
                                <br><br>
                                <p style="margin: 4px 0; text-align: center;"><strong>(İmza):</strong> ________________________</p>
                            </td>
                            <td style="width: 50%; padding: 12px; vertical-align: top;">
                                <p style="margin: 4px 0;"><strong>Adı Soyadı:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_ad_soyad]" value="<?php echo htmlspecialchars($veriler['yetkili_ad_soyad'] ?? ''); ?>" placeholder="Ad Soyad"></p>
                                <p style="margin: 4px 0;"><strong>Kurum Sicili, Unvanı:</strong> <input type="text" class="yonetici-input" name="yonetici[yetkili_sicil_unvan]" value="<?php echo htmlspecialchars($veriler['yetkili_sicil_unvan'] ?? ''); ?>" placeholder="Sicil No / Unvan"></p>
                                <br>
                                <p style="margin: 4px 0; text-align: center;"><strong>(İmza):</strong> ________________________</p>
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
            <!-- FORM 82 E-POSTA KULLANIM ONAYI VE BİLGİ İŞLEM DAİRESİNCE DOLDURULACAK ONLINE ALAN -->
            <form method="POST" action="detay.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div style="margin-top: 30px; border: 1px solid #000; padding: 15px; font-size: 12px;">
                    <p><strong>Yukarıda açıklanan e-posta kullanım kurallarının tümünü okudum ve kabul ediyorum.</strong></p>
                    <br>
                    <p><strong>Adı Soyadı :</strong> <?php echo htmlspecialchars($basvuru['ad_soyad']); ?></p>
                    <br>
                    <p><strong>İmzası :</strong> ___________________________________</p>
                </div>

                <div style="margin-top: 25px; border: 2px solid #1b656e; border-radius: 6px; padding: 15px; background: #fafafa;">
                    <h4 style="margin:0 0 10px 0; color:#1b656e; text-align:center; border-bottom:1px solid #ccc; padding-bottom:5px;">Aşağıdaki Bölümü Boş Bırakınız (Bilgi İşlem Dairesince Doldurulacaktır)</h4>
                    <table class="grid-table" style="margin-bottom:0;">
                        <tr>
                            <th style="width:35%;">E-posta Adresi :</th>
                            <td><input type="text" class="yonetici-input" name="yonetici[bilgi_islem_eposta]" value="<?php echo htmlspecialchars($veriler['bilgi_islem_eposta'] ?? ''); ?>" placeholder="örnek@balikesir.edu.tr"></td>
                        </tr>
                        <tr>
                            <th>Veriliş Tarihi :</th>
                            <td><input type="date" class="yonetici-input" name="yonetici[verilis_tarihi]" value="<?php echo htmlspecialchars($veriler['verilis_tarihi'] ?? ''); ?>"></td>
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
    </div>

</body>
</html>