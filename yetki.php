<?php
session_start();
require_once 'baglan.php';

// Güvenlik Kontrolü: Giriş Yapılmış mı?
if (!isset($_SESSION['admin_giris']) || $_SESSION['admin_giris'] !== true) {
    header("Location: login.php");
    exit;
}

$admin_id  = $_SESSION['admin_id'] ?? 0;
$admin_rol = $_SESSION['admin_rol'] ?? 'admin';

// Normal admin ise revize yetkisi olan formları çekelim
$my_revize_formlar = [];
if ($admin_rol !== 'superadmin') {
    $myRevStmt = $db->prepare("SELECT form_kodu FROM yonetici_izinleri WHERE yonetici_id = :yid AND revize_yetkisi = 1");
    $myRevStmt->execute([':yid' => $admin_id]);
    $my_revize_formlar = $myRevStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // Revize edebileceği hiçbir form yoksa panele yönlendir
    if (count($my_revize_formlar) === 0) {
        header("Location: panel.php");
        exit;
    }
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

// POST İşlemi: Yeni Form Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yeni_form_ekle'])) {
    $f_kodu     = trim($_POST['form_kodu'] ?? '');
    $f_adi      = trim($_POST['form_adi'] ?? '');
    $f_kategori = trim($_POST['kategori'] ?? 'Bilgi İşlem Daire Başkanlığı Formları');
    $f_dosya    = trim($_POST['dosya_adi'] ?? 'form_genel.php');

    if ($f_kategori === 'YENI_KAT') {
        $f_kategori = trim($_POST['yeni_kategori_adi'] ?? '');
    }

    if (empty($f_dosya)) {
        $f_dosya = 'form_genel.php';
    }

    $alanlar = [];
    if (isset($_POST['alan_etiket']) && is_array($_POST['alan_etiket'])) {
        for ($i = 0; $i < count($_POST['alan_etiket']); $i++) {
            $label = trim($_POST['alan_etiket'][$i]);
            if (!empty($label)) {
                $name = strtolower(str_replace([' ', '-', '.'], ['_', '_', ''], $label));
                $alanlar[] = [
                    'name' => $name,
                    'label' => $label,
                    'type' => $_POST['alan_tip'][$i] ?? 'text',
                    'required' => intval($_POST['alan_zorunlu'][$i] ?? 0),
                    'secenekler' => trim($_POST['alan_secenekler'][$i] ?? ''),
                    'target' => $_POST['alan_hedef'][$i] ?? 'user',
                    'active' => intval($_POST['alan_aktif'][$i] ?? 1)
                ];
            }
        }
    }
    $form_alanlari_json = json_encode($alanlar, JSON_UNESCAPED_UNICODE);

    if (!empty($f_kodu) && !empty($f_adi) && !empty($f_kategori)) {
        try {
            $chk = $db->prepare("SELECT COUNT(*) FROM formlar WHERE form_kodu = :fkodu");
            $chk->execute([':fkodu' => $f_kodu]);
            if ($chk->fetchColumn() > 0) {
                $hata = "Bu form kodu ($f_kodu) zaten kullanılmaktadır!";
            } else {
                $ins = $db->prepare("INSERT INTO formlar (form_kodu, form_adi, kategori, dosya_adi, form_alanlari, durum) VALUES (:fkodu, :fadi, :fkat, :fdosya, :falanlar, 1)");
                $ins->execute([
                    ':fkodu' => $f_kodu,
                    ':fadi'  => $f_adi,
                    ':fkat'  => $f_kategori,
                    ':fdosya' => $f_dosya,
                    ':falanlar' => $form_alanlari_json
                ]);

                // Tüm normal adminlere otomatik olarak izin ekle
                $admins = $db->query("SELECT id FROM yoneticiler WHERE rol = 'admin'")->fetchAll();
                $insPerm = $db->prepare("INSERT IGNORE INTO yonetici_izinleri (yonetici_id, form_kodu) VALUES (:yid, :fkodu)");
                foreach ($admins as $adm) {
                    $insPerm->execute([':yid' => $adm['id'], ':fkodu' => $f_kodu]);
                }

                $mesaj = "Yeni başvuru formu ($f_kodu - $f_adi) başarıyla sisteme eklendi.";
                $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
                logEkle($db, $curAdmin, 0, $f_kodu, "Yeni başvuru formu oluşturdu: '{$f_adi}' ({$f_kodu})");
                
                // Formlar listesini ve izin tanımlarını yeniden yükle
                $formlar_query = $db->query("SELECT * FROM formlar ORDER BY kategori ASC, id ASC")->fetchAll();
                $tum_formlar = [];
                foreach ($formlar_query as $f) {
                    $tum_formlar[$f['form_kodu']] = $f['form_kodu'] . ' - ' . $f['form_adi'] . ($f['durum'] == 0 ? ' (PASİF)' : '');
                }
            }
        } catch (PDOException $e) {
            $hata = "Form eklenirken hata oluştu: " . $e->getMessage();
        }
    } else {
        $hata = "Lütfen form bilgilerini eksiksiz giriniz!";
    }
}

// POST İşlemi 1: Yeni Admin Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yeni_admin_ekle'])) {
    $y_kadi   = trim($_POST['kullanici_adi'] ?? '');
    $y_sifre  = trim($_POST['sifre'] ?? '');
    $y_adsoyad= trim($_POST['ad_soyad'] ?? '');

    if (!empty($y_kadi) && strpos($y_kadi, '@') === false) {
        $y_kadi .= '@balikesir.edu.tr';
    }

    if (!empty($y_kadi) && !empty($y_sifre) && !empty($y_adsoyad)) {
        $chk = $db->prepare("SELECT COUNT(*) FROM yoneticiler WHERE kullanici_adi = :kadi");
        $chk->execute([':kadi' => $y_kadi]);
        
        if ($chk->fetchColumn() > 0) {
            $hata = "Bu e-posta adresi ($y_kadi) zaten kullanılmaktadır!";
        } else {
            $ins = $db->prepare("INSERT INTO yoneticiler (kullanici_adi, sifre, ad_soyad, rol) VALUES (:kadi, :sifre, :adsoyad, 'admin')");
            $hashed = password_hash($y_sifre, PASSWORD_DEFAULT);
            $ins->execute([':kadi' => $y_kadi, ':sifre' => $hashed, ':adsoyad' => $y_adsoyad]);
            $mesaj = "Yeni yönetici ($y_adsoyad - $y_kadi) başarıyla eklendi.";
            $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
            logEkle($db, $curAdmin, 0, '-', "Yeni yönetici hesabı oluşturdu: {$y_adsoyad} ({$y_kadi})");
        }
    } else {
        $hata = "Lütfen tüm yönetici bilgilerini eksiksiz giriniz!";
    }
}

// POST İşlemi 1.1: Admin Hesabı Silme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_sil'])) {
    if ($admin_rol !== 'superadmin') {
        die("Bu işlemi sadece Süper Admin gerçekleştirebilir!");
    }
    $y_id = intval($_POST['yonetici_id']);

    if ($y_id === $admin_id) {
        $hata = "Kendi Süper Admin hesabınızı silemezsiniz!";
    } else {
        try {
            $admStmt = $db->prepare("SELECT kullanici_adi, ad_soyad, rol FROM yoneticiler WHERE id = :id");
            $admStmt->execute([':id' => $y_id]);
            $admRow = $admStmt->fetch();

            if ($admRow && $admRow['rol'] !== 'superadmin') {
                $delPerm = $db->prepare("DELETE FROM yonetici_izinleri WHERE yonetici_id = :id");
                $delPerm->execute([':id' => $y_id]);

                $delStmt = $db->prepare("DELETE FROM yoneticiler WHERE id = :id AND rol != 'superadmin'");
                $delStmt->execute([':id' => $y_id]);

                $mesaj = "Yönetici hesabı ({$admRow['ad_soyad']} - {$admRow['kullanici_adi']}) başarıyla silindi.";
                $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
                logEkle($db, $curAdmin, 0, '-', "Yönetici hesabını sildi: {$admRow['ad_soyad']} ({$admRow['kullanici_adi']})");
            } else {
                $hata = "Süper Admin hesabı silinemez!";
            }
        } catch (PDOException $e) {
            $hata = "Yönetici silinirken hata oluştu: " . $e->getMessage();
        }
    }
}

// POST İşlemi 1.2: Admin Hesabı Bilgilerini Düzenleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_duzenle'])) {
    if ($admin_rol !== 'superadmin') {
        die("Bu işlemi sadece Süper Admin gerçekleştirebilir!");
    }
    $y_id      = intval($_POST['edit_yonetici_id']);
    $y_kadi    = trim($_POST['edit_kullanici_adi'] ?? '');
    $y_adsoyad = trim($_POST['edit_ad_soyad'] ?? '');
    $y_sifre   = trim($_POST['edit_sifre'] ?? '');

    if (!empty($y_kadi) && strpos($y_kadi, '@') === false) {
        $y_kadi .= '@balikesir.edu.tr';
    }

    if ($y_id > 0 && !empty($y_kadi) && !empty($y_adsoyad)) {
        try {
            $chk = $db->prepare("SELECT COUNT(*) FROM yoneticiler WHERE kullanici_adi = :kadi AND id != :id");
            $chk->execute([':kadi' => $y_kadi, ':id' => $y_id]);

            if ($chk->fetchColumn() > 0) {
                $hata = "Bu e-posta adresi ($y_kadi) başka bir yönetici tarafından kullanılmaktadır!";
            } else {
                if (!empty($y_sifre)) {
                    $hashed = password_hash($y_sifre, PASSWORD_DEFAULT);
                    $up = $db->prepare("UPDATE yoneticiler SET kullanici_adi = :kadi, ad_soyad = :adsoyad, sifre = :sifre WHERE id = :id");
                    $up->execute([':kadi' => $y_kadi, ':adsoyad' => $y_adsoyad, ':sifre' => $hashed, ':id' => $y_id]);
                } else {
                    $up = $db->prepare("UPDATE yoneticiler SET kullanici_adi = :kadi, ad_soyad = :adsoyad WHERE id = :id");
                    $up->execute([':kadi' => $y_kadi, ':adsoyad' => $y_adsoyad, ':id' => $y_id]);
                }

                $mesaj = "Yönetici bilgileri ({$y_adsoyad} - {$y_kadi}) başarıyla güncellendi.";
                $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
                logEkle($db, $curAdmin, 0, '-', "Yönetici bilgilerini güncelledi: {$y_adsoyad} ({$y_kadi})");
            }
        } catch (PDOException $e) {
            $hata = "Yönetici güncellenirken hata oluştu: " . $e->getMessage();
        }
    } else {
        $hata = "Lütfen e-posta adresi ve ad soyad alanlarını doldurunuz!";
    }
}

// POST İşlemi 2: İzinleri Kaydetme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['izinleri_kaydet'])) {
    if ($admin_rol !== 'superadmin') {
        die("Bu işlemi sadece Süper Admin gerçekleştirebilir!");
    }
    $yonetici_id = intval($_POST['yonetici_id']);
    $secilen_izinler = $_POST['izinler'] ?? [];
    $secilen_revize = $_POST['revize_izinleri'] ?? [];

    try {
        $delStmt = $db->prepare("DELETE FROM yonetici_izinleri WHERE yonetici_id = :yid");
        $delStmt->execute([':yid' => $yonetici_id]);

        $insStmt = $db->prepare("INSERT INTO yonetici_izinleri (yonetici_id, form_kodu, revize_yetkisi) VALUES (:yid, :fkodu, :rev)");
        foreach ($secilen_izinler as $fkodu) {
            if (array_key_exists($fkodu, $tum_formlar)) {
                $rev = in_array($fkodu, $secilen_revize) ? 1 : 0;
                $insStmt->execute([':yid' => $yonetici_id, ':fkodu' => $fkodu, ':rev' => $rev]);
            }
        }
        $mesaj = "Yönetici görev ve revize izinleri başarıyla güncellendi.";
        $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
        logEkle($db, $curAdmin, 0, '-', "Yönetici (ID: {$yonetici_id}) görev ve revize izinlerini güncelledi.");
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

    // Bilgi kutucuklarını (Alanları) JSON formatına dönüştürelim
    $alanlar = [];
    if (isset($_POST['alan_etiket'])) {
        for ($i = 0; $i < count($_POST['alan_etiket']); $i++) {
            $label = trim($_POST['alan_etiket'][$i]);
            if (!empty($label)) {
                $name = strtolower(str_replace([' ', '-', '.'], ['_', '_', ''], $label));
                $alanlar[] = [
                    'name' => $name,
                    'label' => $label,
                    'type' => $_POST['alan_tip'][$i] ?? 'text',
                    'required' => intval($_POST['alan_zorunlu'][$i] ?? 0),
                    'secenekler' => trim($_POST['alan_secenekler'][$i] ?? ''),
                    'target' => $_POST['alan_hedef'][$i] ?? 'user',
                    'active' => intval($_POST['alan_aktif'][$i] ?? 1)
                ];
            }
        }
    }
    $form_alanlari_json = json_encode($alanlar, JSON_UNESCAPED_UNICODE);

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

                $up = $db->prepare("UPDATE formlar SET form_kodu = :fkodu, form_adi = :fadi, kategori = :fkat, dosya_adi = :fdosya, form_alanlari = :falanlar, son_revize_tarihi = NOW() WHERE id = :id");
                $up->execute([
                    ':fkodu' => $f_kodu,
                    ':fadi' => $f_adi,
                    ':fkat' => $f_kategori,
                    ':fdosya' => $f_dosya,
                    ':falanlar' => $form_alanlari_json,
                    ':id' => $f_id
                ]);

                // Yetkileri de güncelleyelim
                if ($eski_kodu && $eski_kodu !== $f_kodu) {
                    $upPerm = $db->prepare("UPDATE yonetici_izinleri SET form_kodu = :yeni WHERE form_kodu = :eski");
                    $upPerm->execute([':yeni' => $f_kodu, ':eski' => $eski_kodu]);
                }

                $toplam_alan = count($alanlar);
                $pasif_alan = 0;
                foreach ($alanlar as $a) {
                    if (($a['active'] ?? 1) == 0) $pasif_alan++;
                }

                $detayMetni = "'{$f_adi}' ({$f_kodu}) isimli formu revize etti. (Toplam {$toplam_alan} bilgi kutucuğu";
                if ($pasif_alan > 0) {
                    $detayMetni .= ", {$pasif_alan} tanesi göz simgesiyle pasife alındı/gizlendi";
                }
                $detayMetni .= ")";

                $mesaj = "Form bilgileri ve bilgi kutucukları başarıyla revize edildi.";
                $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
                logEkle($db, $curAdmin, 0, $f_kodu, $detayMetni);
                
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
        $f_adi_stmt = $db->prepare("SELECT form_adi, form_kodu FROM formlar WHERE id = :id");
        $f_adi_stmt->execute([':id' => $f_id]);
        $fRow = $f_adi_stmt->fetch();
        $fKoduAdi = ($fRow) ? "{$fRow['form_kodu']} ({$fRow['form_adi']})" : "Form #{$f_id}";

        $up = $db->prepare("UPDATE formlar SET durum = :durum, son_revize_tarihi = NOW() WHERE id = :id");
        $up->execute([':durum' => $yeni_durum, ':id' => $f_id]);
        
        $durumMetni = ($yeni_durum == 1) ? 'AKTİF' : 'PASİF';
        $mesaj = "Form durumu başarıyla güncellendi.";
        $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
        logEkle($db, $curAdmin, 0, $fRow['form_kodu'] ?? $f_id, "'{$fKoduAdi}' formunun durumunu {$durumMetni} yaptı.");
        
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
        $f_kodu_stmt = $db->prepare("SELECT form_kodu, form_adi FROM formlar WHERE id = :id");
        $f_kodu_stmt->execute([':id' => $f_id]);
        $fRow = $f_kodu_stmt->fetch();
        $f_kodu = $fRow['form_kodu'] ?? '';
        $f_adi  = $fRow['form_adi'] ?? '';

        if ($f_kodu) {
            $delPerm = $db->prepare("DELETE FROM yonetici_izinleri WHERE form_kodu = :fkodu");
            $delPerm->execute([':fkodu' => $f_kodu]);
        }

        $del = $db->prepare("DELETE FROM formlar WHERE id = :id");
        $del->execute([':id' => $f_id]);
        $mesaj = "Form başarıyla sistemden silindi.";
        $curAdmin = $_SESSION['admin_ad_soyad'] ?? $_SESSION['admin_kullanici'] ?? 'Yönetici';
        logEkle($db, $curAdmin, 0, $f_kodu ?: $f_id, "'{$f_kodu} - {$f_adi}' isimli başvuru formunu sistemden tamamen sildi.");
        
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

// Bildirim Okundu İşlemleri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tum_bildirimleri_oku'])) {
    $db->exec("UPDATE islem_loglari SET okundu = 1");
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tekli_oku'])) {
    $log_id = intval($_POST['tekli_oku_id']);
    $db->prepare("UPDATE islem_loglari SET okundu = 1 WHERE id = :lid")->execute([':lid' => $log_id]);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$okunmamis_sayisi = $db->query("SELECT COUNT(*) FROM islem_loglari WHERE okundu = 0")->fetchColumn() ?: 0;
$bildirim_loglari = $db->query("SELECT * FROM islem_loglari ORDER BY tarih DESC LIMIT 30")->fetchAll() ?: [];

// Normal Yöneticileri Getir (rol = admin)
$adminler = $db->query("SELECT * FROM yoneticiler WHERE rol = 'admin' ORDER BY kullanici_adi ASC")->fetchAll();

// Her Yöneticinin Mevcut İzinleri
$mevcut_izinler = [];
$mevcut_revize_izinler = [];
$izinRows = $db->query("SELECT * FROM yonetici_izinleri")->fetchAll();
foreach ($izinRows as $row) {
    $mevcut_izinler[$row['yonetici_id']][] = $row['form_kodu'];
    if (($row['revize_yetkisi'] ?? 0) == 1) {
        $mevcut_revize_izinler[$row['yonetici_id']][] = $row['form_kodu'];
    }
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
        .header-btn:hover { background: rgba(255,255,255,0.35); }

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
    <script>
        function toggleBildirimKutusu() {
            var el = document.getElementById('bildirimKutusu');
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</head>
<body>

    <div class="header">
        <h1>
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" height="35" style="background:white; border-radius:4px; padding:2px;">
            BAÜN Form İşlem Merkezi - Yönetici & İzin Yapılandırması
        </h1>
        <div style="display:flex; align-items:center; gap:10px; position:relative;">
            <!-- BİLDİRİMLER BUTONU VE AÇILIR PANEL -->
            <div style="position:relative; display:inline-block;">
                <button type="button" class="header-btn" onclick="toggleBildirimKutusu()" style="border:none; cursor:pointer; display:flex; align-items:center; gap:6px;">
                     Bildirimler
                    <?php if ($okunmamis_sayisi > 0): ?>
                        <span id="bildirimRozet" style="background:#d93025; color:white; font-size:11px; padding:2px 7px; border-radius:10px; font-weight:bold;"><?php echo $okunmamis_sayisi; ?></span>
                    <?php endif; ?>
                </button>

                <!-- AÇILIR BİLDİRİM PANELİ -->
                <div id="bildirimKutusu" style="display:none; position:absolute; top:42px; right:0; width:400px; background:white; color:#333; border-radius:8px; box-shadow:0 8px 30px rgba(0,0,0,0.3); z-index:999999; border:1px solid #d0e4eb; overflow:hidden;">
                    <div style="background:#1b656e; color:white; padding:12px 15px; font-size:13.5px; font-weight:bold; display:flex; justify-content:space-between; align-items:center;">
                        <span> Yönetici Bildirimleri & Günlüğü</span>
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="tum_bildirimleri_oku" style="background:rgba(255,255,255,0.2); color:white; border:none; padding:4px 8px; border-radius:4px; font-size:11px; cursor:pointer;">✓ Tümünü Okundu Yap</button>
                        </form>
                    </div>
                    <div style="max-height:400px; overflow-y:auto;">
                        <?php if (count($bildirim_loglari) > 0): ?>
                            <?php foreach ($bildirim_loglari as $log): ?>
                                <div style="padding:12px 15px; border-bottom:1px solid #eee; background:<?php echo ($log['okundu'] == 0) ? '#f0f7f7' : '#ffffff'; ?>; text-align:left;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px;">
                                        <strong style="color:#1b656e;">👤 <?php echo htmlspecialchars($log['yonetici_adi']); ?></strong>
                                        <span style="color:#888; font-size:11px;"><?php echo date('d.m.Y H:i', strtotime($log['tarih'])); ?></span>
                                    </div>
                                    <div style="font-size:13px; color:#444; line-height:1.4;">
                                        <?php echo htmlspecialchars($log['islem_detayi']); ?>
                                    </div>
                                    <div style="margin-top:6px; display:flex; justify-content:space-between; align-items:center;">
                                        <?php if ($log['basvuru_id'] > 0): ?>
                                            <a href="detay.php?id=<?php echo $log['basvuru_id']; ?>&read_log=<?php echo $log['id']; ?>" style="color:#1b656e; font-size:11.5px; font-weight:bold; text-decoration:none;">Başvuru Detayına Git →</a>
                                        <?php else: ?>
                                            <span></span>
                                        <?php endif; ?>
                                        <?php if ($log['okundu'] == 0): ?>
                                            <form method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="tekli_oku_id" value="<?php echo $log['id']; ?>">
                                                <button type="submit" name="tekli_oku" style="background:none; border:none; color:#7f8c8d; font-size:11px; cursor:pointer; text-decoration:underline;">✓ Okundu Yap</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size:11px; color:#aaa;">✓ Okundu</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding:20px; text-align:center; color:#888; font-size:13px;">Henüz hiç bildirim yok.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

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

        <?php if ($admin_rol === 'superadmin'): ?>
        <!-- YENİ FORM OLUŞTURMA KARTI -->
        <div class="card" style="border-top: 4px solid #1b656e;">
            <h2>➕ Yeni Başvuru Formu Oluştur & Ekle (Dinamik Form Builder)</h2>
            <p style="font-size:13px; color:#666; margin-top:-5px;">Sisteme yeni bir başvuru formu ekleyebilir ve forma özel bilgi kutucukları (alanlar) tanımlayabilirsiniz.</p>
            
            <form method="POST">
                <div class="form-satir">
                    <div class="form-grup">
                        <label>Form Kodu *</label>
                        <input type="text" name="form_kodu" placeholder="Örn: KDYS.FR.0095 veya F-56" required autocomplete="off">
                    </div>
                    <div class="form-grup">
                        <label>Form Adı *</label>
                        <input type="text" name="form_adi" placeholder="Örn: Araştırma Projesi Destek Talep Formu" required>
                    </div>
                </div>

                <div class="form-satir">
                    <div class="form-grup">
                        <label>Kategori *</label>
                        <select name="kategori" onchange="kategoriSecimKontrolAdd(this)" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                            <option value="Bilgi İşlem Daire Başkanlığı Formları">Bilgi İşlem Daire Başkanlığı Formları</option>
                            <option value="Akıllı Kart Formları">Akıllı Kart Formları</option>
                            <option value="YENI_KAT">-- Yeni Kategori Ekle --</option>
                        </select>
                        <input type="text" name="yeni_kategori_adi" id="add_yeni_kategori_adi" placeholder="Yeni Kategori Adını Yazınız" style="display:none; margin-top:8px; width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box;">
                    </div>
                    <div class="form-grup">
                        <label>Sayfa / Şablon (Dosya Adı)</label>
                        <input type="text" name="dosya_adi" placeholder="Boş bırakılırsa genel şablon kullanılır (form_genel.php)" value="form_genel.php">
                    </div>
                </div>

                <h3 style="color:#1b656e; font-size:15px; margin-top:20px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Form Alanları (Dinamik Bilgi Kutucukları)</h3>
                <p style="font-size:12px; color:#666; margin-top:0;">Formda doldurulmasını istediğiniz bilgileri (ad, soyad, tc, telefon, dosya vb.) aşağıdan ekleyebilirsiniz.</p>
                
                <div id="add_alanlar_listesi" style="margin-top:15px; margin-bottom:15px;">
                    <!-- Dinamik alan satırları buraya eklenecek -->
                </div>

                <button type="button" class="btn-sec-hepsi" style="background:#1b656e; margin-bottom:20px; padding: 6px 12px; cursor:pointer;" onclick="alanSatiriEkleTarget('add_alanlar_listesi')">+ Yeni Bilgi Kutucuğu (Alan) Ekle</button>

                <br>
                <button type="submit" name="yeni_form_ekle" class="btn-kaydet" style="background:#1b656e; padding:12px 30px; font-size:15px;">✓ Formu Oluştur ve Yayınla</button>
            </form>
        </div>

        <!-- YENİ ADMİN EKLEME KARTI -->
        <div class="card">
            <h2> Yeni Yönetici Hesabı Ekle</h2>
            <form method="POST">
                <div class="form-satir">
                    <div class="form-grup">
                        <label>Kurumsal E-posta Adresi</label>
                        <input type="email" name="kullanici_adi" placeholder="Örn: admin3@balikesir.edu.tr" required autocomplete="off">
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
        <?php endif; ?>

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

                    <h3 style="color:#1b656e; font-size:15px; margin-top:20px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Form Alanları (Dinamik Bilgi Kutucukları)</h3>
                    <p style="font-size:12px; color:#666; margin-top:0;">Formda doldurulmasını istediğiniz bilgi alanlarını (ad, soyad, tc, telefon, dosya vb.) aşağıdan ekleyebilir, silebilir veya türlerini değiştirebilirsiniz.</p>
                    
                    <div id="edit_alanlar_listesi" style="margin-top:15px; margin-bottom:15px;">
                        <!-- Dinamik alan satırları buraya yüklenecek -->
                    </div>

                    <button type="button" class="btn-sec-hepsi" style="background:#1b656e; margin-bottom:20px; padding: 6px 12px; cursor:pointer;" onclick="alanSatiriEkle()">+ Yeni Bilgi Kutucuğu (Alan) Ekle</button>

                    <div style="display:flex; gap:10px;">
                        <button type="submit" name="form_revize_et" class="btn-kaydet" style="background:#27ae60;">Değişiklikleri Kaydet</button>
                        <button type="button" class="btn-kaydet" style="background:#7f8c8d;" onclick="duzenlemeyiKapat()">İptal Et</button>
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
                        <th style="padding:10px; text-align:center;">Son Revize Tarihi</th>
                        <th style="padding:10px; text-align:center;">Durum</th>
                        <th style="padding:10px; text-align:center; width:220px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formlar_query as $f): 
                        if ($admin_rol !== 'superadmin' && !in_array($f['form_kodu'], $my_revize_formlar)) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><strong><?php echo htmlspecialchars($f['form_kodu']); ?></strong></td>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($f['form_adi']); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><span style="font-size:11px; background:#e8f4f8; padding:3px 6px; border-radius:3px; color:#1b656e;"><?php echo htmlspecialchars($f['kategori']); ?></span></td>
                            <td style="padding:10px; border-bottom:1px solid #eee; font-family:monospace; font-size:12px;"><?php echo htmlspecialchars($f['dosya_adi']); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center; font-size:12px; color:#555;">
                                 <?php echo !empty($f['son_revize_tarihi']) ? date('d.m.Y H:i', strtotime($f['son_revize_tarihi'])) : '-'; ?>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                                <?php if ($f['durum'] == 1): ?>
                                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">Aktif</span>
                                <?php else: ?>
                                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                                <div style="display:inline-flex; gap:5px; align-items:center;">
                                    <?php 
                                    $varsayilan_alanlar = [
                                        ['label' => 'Adı Soyadı', 'type' => 'text', 'required' => 1, 'secenekler' => ''],
                                        ['label' => 'T.C. Kimlik No', 'type' => 'text', 'required' => 1, 'secenekler' => ''],
                                        ['label' => 'İrtibat Telefonu', 'type' => 'text', 'required' => 1, 'secenekler' => ''],
                                        ['label' => 'E-posta Adresi', 'type' => 'email', 'required' => 1, 'secenekler' => ''],
                                        ['label' => 'Çalıştığı/Öğrenim Gördüğü Birim', 'type' => 'text', 'required' => 1, 'secenekler' => ''],
                                        ['label' => 'Fotoğraf', 'type' => 'file', 'required' => 0, 'secenekler' => ''],
                                        ['label' => 'Ödeme Dekontu / Ek Belge', 'type' => 'file', 'required' => 0, 'secenekler' => ''],
                                        ['label' => 'Talep / Açıklama Detayı', 'type' => 'textarea', 'required' => 1, 'secenekler' => '']
                                    ];
                                    $varsayilan_alanlar_json = json_encode($varsayilan_alanlar, JSON_UNESCAPED_UNICODE);
                                    $alanlar_verisi = $f['form_alanlari'] ?: $varsayilan_alanlar_json;
                                    ?>
                                    <button type="button" class="btn-sec-hepsi" style="background:#3498db; font-size:11px; padding:4px 8px; cursor:pointer;"
                                        data-id="<?php echo $f['id']; ?>"
                                        data-kodu="<?php echo htmlspecialchars($f['form_kodu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-adi="<?php echo htmlspecialchars($f['form_adi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-kategori="<?php echo htmlspecialchars($f['kategori'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-dosya="<?php echo htmlspecialchars($f['dosya_adi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-alanlar="<?php echo htmlspecialchars($alanlar_verisi ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="formuDuzenleBtn(this)">Revize Et</button>
                                    
                                    <form method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                        <input type="hidden" name="yeni_durum" value="<?php echo $f['durum'] == 1 ? 0 : 1; ?>">
                                        <?php if ($f['durum'] == 1): ?>
                                            <button type="submit" name="form_durum_degistir" class="btn-sec-hepsi" style="background:#f39c12; font-size:11px; padding:4px 8px; cursor:pointer;">Pasif Et</button>
                                        <?php else: ?>
                                            <button type="submit" name="form_durum_degistir" class="btn-sec-hepsi" style="background:#27ae60; font-size:11px; padding:4px 8px; cursor:pointer;">Aktif Et</button>
                                        <?php endif; ?>
                                    </form>

                                    <?php if ($admin_rol === 'superadmin'): ?>
                                        <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Bu formu silmek istediğinize emin misiniz? İzinler de temizlenecektir.');">
                                            <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                            <button type="submit" name="form_sil" class="btn-sec-hepsi" style="background:#d93025; font-size:11px; padding:4px 8px; cursor:pointer;">Sil</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($admin_rol === 'superadmin'): ?>
        <!-- YÖNETİCİ İZİN / GÖREV ATAMA KARTLARI -->
        <div class="card" style="background:#f8fbfd; border:1px solid #d0e4eb;">
            <h2 style="border-bottom:none; margin:0;"> Yönetici Görev, İnceleme & Revize İzinleri</h2>
            <p style="font-size:13px; color:#555; margin-top:5px;">Aşağıdan tüm yöneticilerin <strong>başvuruları inceleme yetkisini</strong> ve <strong>formları revize etme / pasifleştirme yetkisini</strong> ayrı ayrı yönetebilirsiniz.</p>
        </div>

        <?php foreach ($adminler as $admin): ?>
            <?php 
                $aid = $admin['id'];
                $atanmis_formlar = $mevcut_izinler[$aid] ?? [];
                $atanmis_revize_formlar = $mevcut_revize_izinler[$aid] ?? [];
            ?>
            <div class="card">
                <h2>
                    <span>👤 <?php echo htmlspecialchars($admin['ad_soyad']); ?> (E-posta: <strong><?php echo htmlspecialchars($admin['kullanici_adi']); ?></strong>)</span>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <button type="button" class="btn-sec-hepsi" style="background:#f39c12; color:white;" onclick="adminDuzenleBtn(<?php echo $aid; ?>, '<?php echo htmlspecialchars($admin['kullanici_adi'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($admin['ad_soyad'], ENT_QUOTES); ?>')"> Düzenle</button>
                        
                        <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Bu yönetici hesabını (<?php echo htmlspecialchars($admin['ad_soyad'], ENT_QUOTES); ?> - <?php echo htmlspecialchars($admin['kullanici_adi'], ENT_QUOTES); ?>) silmek istediğinize emin misiniz? Atanmış tüm form izinleri de temizlenecektir.');">
                            <input type="hidden" name="yonetici_id" value="<?php echo $aid; ?>">
                            <button type="submit" name="admin_sil" class="btn-sec-hepsi" style="background:#d93025; color:white;"> Yöneticiyi Sil</button>
                        </form>

                        <button type="button" class="btn-sec-hepsi" onclick="tumGoruntulemeSec('form_grid_<?php echo $aid; ?>')">Tümünü Seç / Kaldır (Görüntüleme)</button>
                        <button type="button" class="btn-sec-hepsi" style="background:#e67e22;" onclick="tumRevizeSec('form_grid_<?php echo $aid; ?>')">Seçilen Tüm Formlar İçin Revizeye İzin Ver / Kaldır</button>
                    </div>
                </h2>
                
                <form method="POST">
                    <input type="hidden" name="yonetici_id" value="<?php echo $aid; ?>">

                    <div class="form-grid" id="form_grid_<?php echo $aid; ?>" style="display:grid; grid-template-columns: repeat(2, 1fr); gap:12px; margin: 15px 0;">
                        <?php foreach ($tum_formlar as $kodu => $adi): 
                            $goruntule_checked = in_array($kodu, $atanmis_formlar) ? 'checked' : '';
                            $revize_checked = in_array($kodu, $atanmis_revize_formlar) ? 'checked' : '';
                        ?>
                            <div style="background:#fff; border:1px solid #d0e4eb; border-radius:6px; padding:10px 12px; display:flex; flex-direction:column; gap:6px;">
                                <div style="font-weight:bold; color:#1b656e; border-bottom:1px solid #eee; padding-bottom:5px; font-size:13.5px;">
                                    <?php echo htmlspecialchars($adi); ?>
                                </div>
                                <div style="display:flex; gap:15px; font-size:13px; align-items:center;">
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; margin:0;">
                                        <input type="checkbox" class="chk-goruntule-<?php echo $aid; ?>" name="izinler[]" value="<?php echo $kodu; ?>" <?php echo $goruntule_checked; ?>> 
                                        👁 Görüntüleme İzni
                                    </label>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; margin:0; color:#d93025; font-weight:500;">
                                        <input type="checkbox" class="chk-revize-<?php echo $aid; ?>" name="revize_izinleri[]" value="<?php echo $kodu; ?>" <?php echo $revize_checked; ?>> 
                                        Revize Etme & Pasifleştirme İzni
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="izinleri_kaydet" class="btn-kaydet">✓ Görev ve İzinleri Kaydet</button>
                </form>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function tumunuSec(gridId) {
            var checkboxes = document.querySelectorAll('#' + gridId + ' input[type="checkbox"]');
            var tumuSecili = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !tumuSecili);
        }

        function formuDuzenleBtn(btn) {
            var id = btn.getAttribute("data-id");
            var kodu = btn.getAttribute("data-kodu");
            var adi = btn.getAttribute("data-adi");
            var kategori = btn.getAttribute("data-kategori");
            var dosya = btn.getAttribute("data-dosya");
            var alanlarJson = btn.getAttribute("data-alanlar");
            
            formuDuzenle(id, kodu, adi, kategori, dosya, alanlarJson);
        }

        function formuDuzenle(id, kodu, adi, kategori, dosya, alanlarJson) {
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
            
            // Alanları (Bilgi kutucuklarını) yükleme
            var alanlarListesi = document.getElementById("edit_alanlar_listesi");
            alanlarListesi.innerHTML = ""; // Temizle
            
            if (alanlarJson) {
                try {
                    var alanlar = JSON.parse(alanlarJson);
                    alanlar.forEach(function(alan) {
                        alanSatiriEkle(alan.label, alan.type, alan.required, alan.secenekler, alan.target || 'user', alan.active !== undefined ? alan.active : 1);
                    });
                } catch(e) {
                    console.error("Alanlar JSON parse hatası:", e);
                }
            }
            
            document.getElementById("edit_form_container").scrollIntoView({ behavior: 'smooth' });
        }

        function alanSatiriEkle(label = '', type = 'text', required = 0, secenekler = '', target = 'user', active = 1) {
            alanSatiriEkleTarget('edit_alanlar_listesi', label, type, required, secenekler, target, active);
        }

        function alanSatiriEkleTarget(targetId = 'edit_alanlar_listesi', label = '', type = 'text', required = 0, secenekler = '', target = 'user', active = 1) {
            var alanlarListesi = document.getElementById(targetId);
            if (!alanlarListesi) return;
            var div = document.createElement("div");
            div.className = "alan-satir";
            
            var isAktif = (active == 1 || active === '1' || active === true);
            div.style = `display:flex; gap:10px; margin-bottom:10px; align-items:center; background:${isAktif ? '#f9f9f9' : '#ebebeb'}; padding:8px; border-radius:4px; border:1px solid ${isAktif ? '#e0e0e0' : '#ccc'}; opacity:${isAktif ? '1' : '0.55'}; transition: all 0.2s ease;`;

            var selectTargetHtml = `
                <select name="alan_hedef[]" style="padding:6px; border:1px solid #ddd; border-radius:4px; width:135px; font-weight:bold; color:${target === 'admin' ? '#1b656e' : '#333'};" onchange="this.style.color=(this.value==='admin'?'#1b656e':'#333')">
                    <option value="user" ${target === 'user' ? 'selected' : ''}> Başvuru Sahibi</option>
                    <option value="admin" ${target === 'admin' ? 'selected' : ''}> Yönetici (Admin)</option>
                </select>
            `;

            var selectRequiredHtml = `
                <select name="alan_zorunlu[]" style="padding:6px; border:1px solid #ddd; border-radius:4px; width:90px;">
                    <option value="1" ${required == 1 ? 'selected' : ''}>Zorunlu</option>
                    <option value="0" ${required == 0 ? 'selected' : ''}>İsteğe Bağlı</option>
                </select>
            `;
            
            var selectTypeHtml = `
                <select name="alan_tip[]" style="padding:6px; border:1px solid #ddd; border-radius:4px; width:130px;" onchange="alanTipiKontrol(this)">
                    <option value="text" ${type === 'text' ? 'selected' : ''}>Kısa Metin</option>
                    <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Uzun Metin</option>
                    <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Tik Atmalık Kutular (Checkbox)</option>
                    <option value="select" ${type === 'select' ? 'selected' : ''}>Açılır Liste (Dropdown)</option>
                    <option value="number" ${type === 'number' ? 'selected' : ''}>Sayı</option>
                    <option value="email" ${type === 'email' ? 'selected' : ''}>E-posta</option>
                    <option value="date" ${type === 'date' ? 'selected' : ''}>Tarih</option>
                    <option value="file" ${type === 'file' ? 'selected' : ''}>Dosya Yükleme</option>
                </select>
            `;

            var eyeTitle = isAktif ? "Bunu revize et (Şu an Görünür - Gizlemek için tıklayın)" : "Bunu revize et (Şu an Gizli - Göstermek için tıklayın)";
            var eyeBtnHtml = `
                <input type="hidden" name="alan_aktif[]" class="alan-aktif-input" value="${isAktif ? 1 : 0}">
                <button type="button" class="btn-goz-toggle" title="${eyeTitle}" onclick="alanAktiflikToggle(this)" style="background:${isAktif ? '#e8f4f8' : '#e0e0e0'}; color:${isAktif ? '#1b656e' : '#777'}; border:1px solid ${isAktif ? '#1b656e' : '#aaa'}; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:15px; font-weight:bold; transition:all 0.2s ease;">${isAktif ? '️👁' : '👁️‍🗨️'}</button>
            `;

            div.innerHTML = `
                <div style="flex:2;">
                    <input type="text" name="alan_etiket[]" value="${escapeHtml(label)}" placeholder="Kutucuk Etiketi (Örn: Adı Soyadı)" required style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box; ${isAktif ? '' : 'text-decoration:line-through; color:#777;'}">
                </div>
                <div>
                    ${eyeBtnHtml}
                </div>
                <div>
                    ${selectTargetHtml}
                </div>
                <div>
                    ${selectTypeHtml}
                </div>
                <div>
                    ${selectRequiredHtml}
                </div>
                <div style="flex:2; display:${(type === 'select' || type === 'checkbox') ? 'block' : 'none'};" class="secenekler-grup">
                    <input type="text" name="alan_secenekler[]" value="${escapeHtml(secenekler)}" placeholder="Seçenekleri virgülle ayırın (Örn: Ad, Soyad, Unvan, Birim)" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
                </div>
                <div>
                    <button type="button" onclick="this.closest('.alan-satir').remove()" style="background:#d93025; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-weight:bold;">✕</button>
                </div>
            `;
            alanlarListesi.appendChild(div);
        }

        function alanAktiflikToggle(btn) {
            var satir = btn.closest('.alan-satir');
            var inputAktif = satir.querySelector('.alan-aktif-input');
            var inputEtiket = satir.querySelector('input[name="alan_etiket[]"]');
            var suAnkiVal = inputAktif.value;

            if (suAnkiVal == "1") {
                inputAktif.value = "0";
                btn.innerHTML = "👁️‍🗨️";
                btn.title = "Bunu revize et (Şu an Gizli - Göstermek için tıklayın)";
                btn.style.background = "#e0e0e0";
                btn.style.color = "#777";
                btn.style.borderColor = "#aaa";
                satir.style.opacity = "0.55";
                satir.style.background = "#ebebeb";
                satir.style.borderColor = "#ccc";
                if (inputEtiket) {
                    inputEtiket.style.textDecoration = "line-through";
                    inputEtiket.style.color = "#777";
                }
            } else {
                inputAktif.value = "1";
                btn.innerHTML = "👁";
                btn.title = "Bunu revize et (Şu an Görünür - Gizlemek için tıklayın)";
                btn.style.background = "#e8f4f8";
                btn.style.color = "#1b656e";
                btn.style.borderColor = "#1b656e";
                satir.style.opacity = "1";
                satir.style.background = "#f9f9f9";
                satir.style.borderColor = "#e0e0e0";
                if (inputEtiket) {
                    inputEtiket.style.textDecoration = "none";
                    inputEtiket.style.color = "#333";
                }
            }
        }

        function alanTipiKontrol(selectElem) {
            var seceneklerGrup = selectElem.closest('.alan-satir').querySelector('.secenekler-grup');
            if (selectElem.value === 'select' || selectElem.value === 'checkbox') {
                seceneklerGrup.style.display = 'block';
            } else {
                seceneklerGrup.style.display = 'none';
                seceneklerGrup.querySelector('input').value = '';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function duzenlemeyiKapat() {
            document.getElementById("edit_form_container").style.display = "none";
            document.getElementById("edit_form_warning").style.display = "block";
            document.getElementById("edit_form_id").value = "";
            document.getElementById("edit_form_kodu").value = "";
            document.getElementById("edit_form_adi").value = "";
            document.getElementById("edit_dosya_adi").value = "";
            document.getElementById("edit_alanlar_listesi").innerHTML = "";
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

        function kategoriSecimKontrolAdd(selectElem) {
            var yeniKatInput = document.getElementById("add_yeni_kategori_adi");
            if (selectElem.value === "YENI_KAT") {
                yeniKatInput.style.display = "block";
                yeniKatInput.required = true;
                yeniKatInput.focus();
            } else {
                yeniKatInput.style.display = "none";
                yeniKatInput.required = false;
            }
        }

        function tumGoruntulemeSec(gridId) {
            var grid = document.getElementById(gridId);
            if (!grid) return;
            var inputs = grid.querySelectorAll('input[name="izinler[]"]');
            var hepsiSecili = Array.from(inputs).every(input => input.checked);
            inputs.forEach(input => input.checked = !hepsiSecili);
        }

        function tumRevizeSec(gridId) {
            var grid = document.getElementById(gridId);
            if (!grid) return;
            var inputs = grid.querySelectorAll('input[name="revize_izinleri[]"]');
            var hepsiSecili = Array.from(inputs).every(input => input.checked);
            inputs.forEach(input => {
                input.checked = !hepsiSecili;
                if (!hepsiSecili) {
                    var goruntuleInput = input.closest('div').querySelector('input[name="izinler[]"]');
                    if (goruntuleInput) goruntuleInput.checked = true;
                }
            });
        }

        // Sayfa yüklendiğinde yeni form alanına 1 varsayılan satır ekle
        document.addEventListener("DOMContentLoaded", function() {
            var addTarget = document.getElementById('add_alanlar_listesi');
            if (addTarget) {
                alanSatiriEkleTarget('add_alanlar_listesi', 'Talep Açıklaması', 'text', 1, '');
            }
        });

        // Yönetici Düzenleme Modalı Fonksiyonları
        function adminDuzenleBtn(id, kadi, adsoyad) {
            document.getElementById('edit_yonetici_id').value = id;
            document.getElementById('edit_kullanici_adi').value = kadi;
            document.getElementById('edit_ad_soyad').value = adsoyad;
            
            var modal = document.getElementById('adminEditModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeAdminEditModal() {
            var modal = document.getElementById('adminEditModal');
            if (modal) modal.style.display = 'none';
        }
    </script>

    <!-- YÖNETİCİ BİLGİLERİNİ DÜZENLEME MODALI -->
    <div id="adminEditModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; justify-content:center; align-items:center;">
        <div style="background:white; padding:30px; border-radius:8px; max-width:450px; width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
            <h3 style="margin-top:0; color:#1b656e; border-bottom:2px solid #e8f4f8; padding-bottom:10px;"> Yönetici Hesabını Düzenle</h3>
            <form method="POST">
                <input type="hidden" name="edit_yonetici_id" id="edit_yonetici_id">
                
                <div style="margin-bottom:15px; text-align:left;">
                    <label style="display:block; font-weight:bold; font-size:13.5px; margin-bottom:5px; color:#333;">Adı Soyadı *</label>
                    <input type="text" name="edit_ad_soyad" id="edit_ad_soyad" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size:14px;">
                </div>

                <div style="margin-bottom:15px; text-align:left;">
                    <label style="display:block; font-weight:bold; font-size:13.5px; margin-bottom:5px; color:#333;">Kurumsal E-posta Adresi *</label>
                    <input type="email" name="edit_kullanici_adi" id="edit_kullanici_adi" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size:14px;">
                </div>

                <div style="margin-bottom:20px; text-align:left;">
                    <label style="display:block; font-weight:bold; font-size:13.5px; margin-bottom:5px; color:#333;">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                    <input type="text" name="edit_sifre" placeholder="Boş bırakılırsa mevcut şifre korunur" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size:14px;">
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn-sec-hepsi" style="background:#7f8c8d; padding:8px 16px; font-size:13px;" onclick="closeAdminEditModal()">İptal</button>
                    <button type="submit" name="admin_duzenle" class="btn-kaydet" style="padding:8px 20px; font-size:13px; background:#27ae60;">✓ Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
