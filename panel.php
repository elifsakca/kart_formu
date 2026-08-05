<?php
session_start();
require_once 'baglan.php';

// Güvenlik: Giriş yapmayan erişemez
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true) {
    header("Location: login.php");
    exit;
}

// Durum Güncelleme
if (isset($_GET['islem']) && $_GET['islem'] == 'durum_guncelle' && isset($_GET['id']) && isset($_GET['yeni_durum'])) {
    $id = intval($_GET['id']);
    $yeni_durum = $_GET['yeni_durum'];
    $guncelle = $db->prepare("UPDATE basvurular SET durum = :durum WHERE id = :id");
    $guncelle->execute([':durum' => $yeni_durum, ':id' => $id]);
    header("Location: panel.php?mesaj=guncellendi");
    exit;
}

// Silme İşlemi
if (isset($_GET['islem']) && $_GET['islem'] == 'sil' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sil = $db->prepare("DELETE FROM basvurular WHERE id = :id");
    $sil->execute([':id' => $id]);
    header("Location: panel.php?mesaj=silindi");
    exit;
}

// Arama ve Filtreleme
$arama = $_GET['arama'] ?? '';
$form_filtre = $_GET['form_filtre'] ?? '';

$sql = "SELECT * FROM basvurular WHERE 1=1";
$params = [];

if (!empty($arama)) {
    $sql .= " AND (ad_soyad LIKE :arama OR tc_no LIKE :arama OR birim LIKE :arama)";
    $params[':arama'] = "%$arama%";
}

if (!empty($form_filtre)) {
    $sql .= " AND form_kodu = :form_filtre";
    $params[':form_filtre'] = $form_filtre;
}

$sql .= " ORDER BY kayit_tarihi DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$basvurular = $stmt->fetchAll();

// İstatistikler
$toplam_basvuru    = $db->query("SELECT COUNT(*) FROM basvurular")->fetchColumn();
$bekleyen_basvuru  = $db->query("SELECT COUNT(*) FROM basvurular WHERE durum='Beklemede'")->fetchColumn();
$onaylanan_basvuru = $db->query("SELECT COUNT(*) FROM basvurular WHERE durum='Onaylandı'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - BAÜN Form İşlem Merkezi</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f6f9; }
        .header { background-color: #1b656e; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { font-size: 20px; margin: 0; display: flex; align-items: center; gap: 10px; }
        .header-btn { background: #d93025; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; transition: 0.3s; }
        .header-btn:hover { background: #b02319; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #1b656e; }
        .stat-card.warning { border-left-color: #f39c12; }
        .stat-card.success { border-left-color: #27ae60; }
        .stat-card h3 { margin: 0 0 5px 0; font-size: 28px; color: #333; }
        .stat-card p { margin: 0; color: #777; font-size: 13px; font-weight: bold; text-transform: uppercase; }

        .filter-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; gap: 15px; align-items: center; }
        .filter-card input, .filter-card select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; outline: none; }
        .filter-card input { flex: 2; }
        .filter-card select { flex: 1; }
        .btn-ara { background: #1b656e; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-sifirla { background: #7f8c8d; color: white; border: none; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-size: 13px; }

        .table-card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: left; }
        th { background: #e8f4f8; color: #1b656e; padding: 14px 15px; font-weight: bold; border-bottom: 2px solid #ddd; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background-color: #f9f9f9; }

        .btn-detay { background: #2980b9; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-block; }
        .btn-detay:hover { background: #1f618d; }
        .btn-sil { background: #e74c3c; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-block; margin-left: 5px; }
        .btn-sil:hover { background: #c0392b; }
        
        .durum-select { padding: 4px 8px; font-size: 12px; border-radius: 4px; border: 1px solid #ccc; background: #fff; cursor: pointer; }
    </style>
</head>
<body>

    <div class="header">
        <h1>
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" height="35" style="background:white; border-radius:4px; padding:2px;">
            BAÜN Form İşlem Merkezi - Yönetici Paneli
        </h1>
        <div>
            <span style="margin-right:15px; font-size:14px;">Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['admin_kullanici'] ?? 'Admin'); ?></strong></span>
            <a href="cikis.php" class="header-btn">Güvenli Çıkış</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $toplam_basvuru; ?></h3>
                <p>Toplam Başvuru</p>
            </div>
            <div class="stat-card warning">
                <h3><?php echo $bekleyen_basvuru; ?></h3>
                <p>Bekleyen Başvurular</p>
            </div>
            <div class="stat-card success">
                <h3><?php echo $onaylanan_basvuru; ?></h3>
                <p>Onaylanan Başvurular</p>
            </div>
        </div>

        <form method="GET" class="filter-card">
            <input type="text" name="arama" value="<?php echo htmlspecialchars($arama); ?>" placeholder="Ad Soyad, TC Kimlik No veya Birim ara...">
            <select name="form_filtre">
                <option value="">-- Tüm Formlar --</option>
                <option value="F-52" <?php echo $form_filtre=='F-52'?'selected':''; ?>>F-52 - Akıllı Kart İşlem Formu</option>
                <option value="F-53" <?php echo $form_filtre=='F-53'?'selected':''; ?>>F-53 - Öğrenci Akıllı Kart Formu</option>
                <option value="F-54" <?php echo $form_filtre=='F-54'?'selected':''; ?>>F-54 - Kayıp Kart Müracaat Formu</option>
                <option value="F-55" <?php echo $form_filtre=='F-55'?'selected':''; ?>>F-55 - Arızalı Kart Müracaat Formu</option>
                <option value="KDYS.FR.0071" <?php echo $form_filtre=='KDYS.FR.0071'?'selected':''; ?>>KDYS.FR.0071 - Bakım Onarım Takip</option>
                <option value="KDYS.FR.0072" <?php echo $form_filtre=='KDYS.FR.0072'?'selected':''; ?>>KDYS.FR.0072 - Kurumsal E-Posta Talep</option>
                <option value="KDYS.FR.0073" <?php echo $form_filtre=='KDYS.FR.0073'?'selected':''; ?>>KDYS.FR.0073 - E-İmza Okuyucu Tutanağı</option>
                <option value="KDYS.FR.0074" <?php echo $form_filtre=='KDYS.FR.0074'?'selected':''; ?>>KDYS.FR.0074 - E-İmza Talep Formu</option>
                <option value="KDYS.FR.0077" <?php echo $form_filtre=='KDYS.FR.0077'?'selected':''; ?>>KDYS.FR.0077 - Kişisel Web Sözleşmesi</option>
                <option value="KDYS.FR.0078" <?php echo $form_filtre=='KDYS.FR.0078'?'selected':''; ?>>KDYS.FR.0078 - Kurumsal Statik IP Sözleşmesi</option>
                <option value="KDYS.FR.0079" <?php echo $form_filtre=='KDYS.FR.0079'?'selected':''; ?>>KDYS.FR.0079 - Kurumsal Web Sözleşmesi</option>
                <option value="KDYS.FR.0080" <?php echo $form_filtre=='KDYS.FR.0080'?'selected':''; ?>>KDYS.FR.0080 - Mernis Taahhütnamesi</option>
                <option value="KDYS.FR.0082" <?php echo $form_filtre=='KDYS.FR.0082'?'selected':''; ?>>KDYS.FR.0082 - Personel E-Posta Başvuru</option>
            </select>
            <button type="submit" class="btn-ara">Filtrele</button>
            <?php if(!empty($arama) || !empty($form_filtre)): ?>
                <a href="panel.php" class="btn-sifirla">Filtreyi Temizle</a>
            <?php endif; ?>
        </form>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Form Kodu / İsmi</th>
                        <th>Başvuran Ad Soyad</th>
                        <th>T.C. Kimlik No</th>
                        <th>Birim / Fakülte</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                        <th style="text-align:center;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($basvurular) > 0): ?>
                        <?php foreach($basvurular as $b): ?>
                            <tr>
                                <td><strong>#<?php echo $b['id']; ?></strong></td>
                                <td>
                                    <span style="font-weight:bold; color:#1b656e; display:block;"><?php echo htmlspecialchars($b['form_kodu']); ?></span>
                                    <span style="font-size:12px; color:#666;"><?php echo htmlspecialchars($b['form_adi']); ?></span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($b['ad_soyad'] ?: '-'); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['tc_no'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($b['birim'] ?: '-'); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($b['kayit_tarihi'])); ?></td>
                                <td>
                                    <select class="durum-select" onchange="durumDegistir(<?php echo $b['id']; ?>, this.value)">
                                        <option value="Beklemede" <?php echo $b['durum']=='Beklemede'?'selected':''; ?>>Beklemede</option>
                                        <option value="Onaylandı" <?php echo $b['durum']=='Onaylandı'?'selected':''; ?>>Onaylandı</option>
                                        <option value="Reddedildi" <?php echo $b['durum']=='Reddedildi'?'selected':''; ?>>Reddedildi</option>
                                    </select>
                                </td>
                                <td style="text-align:center;">
                                    <a href="detay.php?id=<?php echo $b['id']; ?>" class="btn-detay">Detayları Gör</a>
                                    <a href="panel.php?islem=sil&id=<?php echo $b['id']; ?>" class="btn-sil" onclick="return confirm('Bu başvuruyu silmek istediğinize emin misiniz?');">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:30px; color:#999;">Henüz kayıtlı bir başvuru bulunmamaktadır.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function durumDegistir(id, yeniDurum) {
            window.location.href = 'panel.php?islem=durum_guncelle&id=' + id + '&yeni_durum=' + encodeURIComponent(yeniDurum);
        }
    </script>
</body>
</html>