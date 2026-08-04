<?php
session_start();
require_once 'baglan.php';

// Güvenlik Kontrolü: Giriş yapılmadıysa login.php'ye yönlendir
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM basvurular WHERE id = :id");
$stmt->execute([':id' => $id]);
$basvuru = $stmt->fetch();

if (!$basvuru) {
    die("Başvuru bulunamadı!");
}

$veriler = json_decode($basvuru['form_verileri'], true) ?? [];
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

        @media print {
            .noprint { display: none !important; }
            body { background: white; }
            .paper { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 0; }
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
    </div>

</body>
</html>