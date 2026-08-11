<?php
session_start();
require_once 'baglan.php';

$form_kodu = trim($_GET['kodu'] ?? $_GET['form_kodu'] ?? '');
$form_id   = trim($_GET['id'] ?? '');

$form = null;
if (!empty($form_kodu)) {
    $clean_kodu  = preg_replace('/[^a-zA-Z0-9]/', '', $form_kodu);
    $only_digits = preg_replace('/[^0-9]/', '', $form_kodu);
    
    $stmt = $db->prepare("
        SELECT * FROM formlar 
        WHERE form_kodu = :kodu 
           OR dosya_adi = :kodu 
           OR REPLACE(form_kodu, '.', '') = :kodu
           OR REPLACE(REPLACE(form_kodu, '.', ''), '-', '') = :clean_kodu
           OR form_kodu LIKE :like_kodu
           OR (:digits != '' AND (RIGHT(form_kodu, LENGTH(:digits)) = :digits OR form_kodu LIKE :like_digits))
        ORDER BY CASE WHEN form_kodu = :kodu THEN 0 ELSE 1 END, id ASC
        LIMIT 1
    ");
    $stmt->execute([
        ':kodu'        => $form_kodu,
        ':clean_kodu'  => $clean_kodu,
        ':like_kodu'   => '%' . $form_kodu . '%',
        ':digits'      => $only_digits,
        ':like_digits' => '%' . $only_digits
    ]);
    $form = $stmt->fetch();
} elseif (!empty($form_id)) {
    $stmt = $db->prepare("SELECT * FROM formlar WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $form_id]);
    $form = $stmt->fetch();
}

$is_admin = isset($_SESSION['admin_giris']) && $_SESSION['admin_giris'] === true;

if (!$form) {
    die("
    <!DOCTYPE html>
    <html lang='tr'>
    <head><meta charset='UTF-8'><title>Form Bulunamadı</title>
    <style>body{font-family:sans-serif; text-align:center; padding:50px; background:#f8f9fa;} .box{background:white; max-width:500px; margin:0 auto; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}</style>
    </head>
    <body>
        <div class='box'>
            <h2 style='color:#e74c3c;'>Form Bulunamadı</h2>
            <p>Aradığınız başvuru formu sisteme kayıtlı değil veya geçersiz bir kod girdiniz.</p>
            <a href='index.php' style='display:inline-block; margin-top:15px; background:#1b656e; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>← Tüm Formlar Listesine Dön</a>
        </div>
    </body>
    </html>
    ");
}

if ($form['durum'] == 0 && !$is_admin) {
    die("
    <!DOCTYPE html>
    <html lang='tr'>
    <head><meta charset='UTF-8'><title>Form Pasif Durumda</title>
    <style>body{font-family:sans-serif; text-align:center; padding:50px; background:#f8f9fa;} .box{background:white; max-width:500px; margin:0 auto; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}</style>
    </head>
    <body>
        <div class='box'>
            <h2 style='color:#e67e22;'>Form Şu An Pasif Durumda</h2>
            <p><strong>" . htmlspecialchars($form['form_kodu'] . ' - ' . $form['form_adi']) . "</strong> başvuruya geçici olarak kapatılmıştır.</p>
            <a href='index.php' style='display:inline-block; margin-top:15px; background:#1b656e; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>← Aktif Formlar Listesine Dön</a>
        </div>
    </body>
    </html>
    ");
}

$form_num = preg_replace('/[^0-9]/', '', $form['form_kodu']);
$form_num_short = ltrim($form_num, '0');
$target_file = __DIR__ . '/forms/' . $form_num_short . '.php';

if (!file_exists($target_file)) {
    if (!empty($form['dosya_adi']) && file_exists(__DIR__ . '/forms/' . $form['dosya_adi'])) {
        $target_file = __DIR__ . '/forms/' . $form['dosya_adi'];
    } else {
        $target_file = __DIR__ . '/forms/genel.php';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['form_kodu'] . ' - ' . $form['form_adi']); ?> | BAÜN Başvuru</title>
    <style>
        /* Genel Sayfa Ayarları */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; padding-bottom: 50px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .navbar-logo { display: flex; align-items: center; gap: 15px; font-weight: bold; color: #1b656e; font-size: 18px; text-decoration: none; transition: opacity 0.3s; }
        .navbar-logo:hover { opacity: 0.8; }
        .navbar-link { font-size: 14px; color: #555; text-decoration: none; transition: color 0.3s; }
        .navbar-link:hover { color: #1b656e; }
        .banner { background-color: rgb(3, 149, 159); color: white; text-align: center; padding: 50px 20px 80px 20px; border-bottom-left-radius: 50% 20%; border-bottom-right-radius: 50% 20%; margin-bottom: -40px; }
        .banner h1 { font-size: 28px; margin: 0 0 10px 0; }
        .banner p { font-size: 13.5px; opacity: 0.9; }
        .banner p a { color: white; text-decoration: none; transition: opacity 0.3s; }
        .banner p a:hover { opacity: 0.7; text-decoration: underline; }

        /* Form Container */
        .form-container { background: white; max-width: 1000px; margin: 20px auto; padding: 35px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); position: relative; z-index: 10; }
        .form-container h2 { color: #1b656e; border-bottom: 2px solid #eee; padding-bottom: 12px; margin-top: 0; text-align: center; font-size: 22px; }
        .revize-tarihi-badge { text-align:center; font-size:12.5px; color:#1b656e; margin:-5px 0 20px 0; font-weight:bold; background:#e8f4f8; padding:6px 15px; border-radius:15px; display:inline-block; border:1px solid #1b656e; }

        .form-grup { margin-bottom: 18px; text-align: left; }
        .form-grup label { display: block; font-weight: bold; margin-bottom: 6px; color: #444; font-size: 14px; }
        .form-grup input[type="text"], .form-grup input[type="date"], .form-grup input[type="time"], .form-grup input[type="email"], .form-grup select, .form-grup textarea { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        .form-grup input:focus, .form-grup select:focus, .form-grup textarea:focus { border-color: #1b656e; outline: none; }
        
        .form-bilgi { font-size: 11.5px; color: #d93025; margin-top: 5px; display: block; font-weight: 500; }
        .resmi-yazi { font-size: 14px; color: #333; text-align: justify; line-height: 1.6; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin-bottom: 20px; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; font-size: 13px; color: #333; }
        .checkbox-grid label { font-weight: normal !important; display: flex; align-items: center; gap: 8px; cursor: pointer; color: #333 !important; }
        
        .form-satir { display: flex; gap: 15px; }
        .form-satir .form-grup { flex: 1; }

        /* Tablo Stilleri */
        .form-tablosu { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        .form-tablosu th, .form-tablosu td { border: 1px solid #ddd; padding: 8px 5px; text-align: center; }
        .form-tablosu th { background-color: #1b656e; color: white; font-weight: 600; white-space: nowrap; }
        .form-tablosu td input, .form-tablosu td select { width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; font-size: 11px; }

        .btn-alt-satir-ekle { background-color: #27ae60; color: white; border: none; padding: 7px 14px; font-size: 12px; font-weight: bold; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-alt-satir-ekle:hover { background-color: #219150; }
        .btn-satir-sil { background-color: #e74c3c; color: white; border: none; width: 24px; height: 24px; border-radius: 3px; cursor: pointer; font-weight: bold; }

        /* Butonlar */
        .btn-tamam { background-color: #1b656e; color: white; border: none; padding: 15px 30px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: background 0.3s; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-tamam:hover { background-color: rgb(3, 149, 159); }

        /* Açılır / Kapanır Akordiyon Stili */
        .accordion-btn { background-color: #e8f4f8; color: #1b656e; cursor: pointer; padding: 12px 15px; width: 100%; border: 1px solid #1b656e; border-radius: 5px; text-align: left; outline: none; font-size: 13.5px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.3s; margin-top: 15px; margin-bottom: 15px; }
        .accordion-btn:hover, .accordion-btn.active { background-color: #1b656e; color: white; }
        .accordion-panel { padding: 0 18px; background-color: #fdfdfd; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; margin-top: -15px; margin-bottom: 15px; font-size: 12.5px; line-height: 1.6; color: #333; }
    </style>
</head>
<body>

    <?php if ($form['durum'] == 0 && $is_admin): ?>
        <div style="background:#fff3cd; color:#856404; padding:12px 20px; text-align:center; font-weight:bold; border-bottom:2px solid #ffeeba; position:relative; z-index:999;">
             Bu form (<?php echo htmlspecialchars($form['form_kodu']); ?>) şu an PASİF durumdadır. Yönetici önizleme modundasınız.
        </div>
    <?php endif; ?>

    <!-- Üst Menü -->
    <div class="navbar">
        <a href="index.php" class="navbar-logo">
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" alt="BAÜN Logo" height="50">
            BALIKESİR ÜNİVERSİTESİ
        </a>
        <div>
            <a href="index.php" class="navbar-link" style="font-weight:bold; color:#1b656e; margin-right:15px;">← Tüm Formlar</a>
            <a href="takip.php" class="navbar-link" style="font-weight:bold; color:#555; margin-right:15px;">Başvuru Takibi</a>
            <a href="login.php" class="navbar-link" style="font-weight:bold; color:#555;">Yönetici Girişi</a>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner">
        <h1><?php echo htmlspecialchars($form['form_adi']); ?></h1>
        <p><a href="index.php">TÜM FORMLAR</a> > <?php echo htmlspecialchars($form['form_kodu']); ?></p>
    </div>

    <!-- Başarılı Başvuru Bildirimi -->
    <?php if(isset($_GET['durum']) && $_GET['durum'] == 'basarili'): 
        $t_no = htmlspecialchars($_GET['takip_no'] ?? '-');
    ?>
        <div style="max-width:900px; margin:25px auto; background:#d4edda; color:#155724; padding:25px; border-radius:10px; border-left:8px solid #28a745; text-align:center; position:relative; z-index:20; box-shadow:0 6px 18px rgba(0,0,0,0.1);">
            <h2 style="margin:0 0 10px 0; font-size:22px; color:#155724;">✓ Form Başvurunuz Başarıyla Alınmıştır!</h2>
            <p style="margin:5px 0 10px 0; font-size:15px; color:#1e7e34;">Başvurunuz sistemimize başarıyla kaydedilmiştir. Lütfen aşağıdaki <b>Başvuru Takip Numarasını</b> saklayınız:</p>
            
            <div style="font-size:28px; font-weight:bold; color:#1b656e; background:white; display:inline-block; padding:10px 30px; border-radius:8px; margin:12px 0; border:2px dashed #1b656e; letter-spacing:2px; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                #<?php echo $t_no; ?>
            </div>
            <br>
            <div style="margin-top:15px; display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
                <a href="takip.php?takip_no=<?php echo urlencode($_GET['takip_no'] ?? ''); ?>" style="display:inline-flex; align-items:center; gap:6px; background:#1b656e; color:white; font-weight:bold; padding:10px 20px; border-radius:5px; text-decoration:none; transition:background 0.3s;"> Başvuru Durumunu Sorgula</a>
                <a href="detay.php?takip_no=<?php echo urlencode($_GET['takip_no'] ?? ''); ?>" style="display:inline-flex; align-items:center; gap:6px; background:#28a745; color:white; font-weight:bold; padding:10px 20px; border-radius:5px; text-decoration:none; transition:background 0.3s;"> Başvurumu Görüntüle / PDF İndir</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['durum']) && $_GET['durum'] == 'hata'): ?>
        <div style="max-width:900px; margin:25px auto; background:#f8d7da; color:#721c24; padding:20px; border-radius:10px; border-left:8px solid #dc3545; text-align:center; position:relative; z-index:20; box-shadow:0 6px 18px rgba(0,0,0,0.1);">
            <h3 style="margin:0 0 8px 0; font-size:18px;"> Form Kaydedilirken Hata Oluştu</h3>
            <p style="margin:0; font-size:14px;"><?php echo htmlspecialchars($_GET['mesaj'] ?? 'Bilinmeyen hata'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Form Doldurma Ekranı -->
    <div class="form-container">
        <div style="text-align: center;">
            <div class="revize-tarihi-badge">
                 Doküman No: <?php echo htmlspecialchars($form['form_kodu']); ?> &nbsp;|&nbsp; Son Revize Tarihi: <?php echo !empty($form['son_revize_tarihi']) ? date('d.m.Y H:i', strtotime($form['son_revize_tarihi'])) : date('d.m.Y'); ?>
            </div>
        </div>

        <?php 
        $mode = 'input';
        include $target_file; 
        ?>
    </div>

    <!-- JavaScript Kodları -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Gizli form içeriklerinin stilini görünür yapalım
            var gizliFormlar = document.querySelectorAll(".gizli-form");
            gizliFormlar.forEach(function(f) {
                f.style.display = "block";
                var h2 = f.querySelector("h2");
                if (h2) h2.style.display = "none"; // Konteynır başlığı zaten var
            });

            // Dinamik alanlı formlar için alanları çizelim
            var alanlarJson = '<?php echo addslashes($form['form_alanlari'] ?: ''); ?>';
            if (alanlarJson && document.getElementById("dinamik_alanlar_konteyner")) {
                try {
                    var parsedAlanlar = JSON.parse(alanlarJson);
                    if (Array.isArray(parsedAlanlar) && parsedAlanlar.length > 0) {
                        dinamikAlanlariCiz("dinamik_alanlar_konteyner", parsedAlanlar);
                    }
                } catch(e) {
                    console.error("Alanlar parse hatası:", e);
                }
            }
        });

        function dinamikAlanlariCiz(containerId, alanlar) {
            var container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = "";
            
            var pendingPair = [];

            function flushPair() {
                if (pendingPair.length === 0) return;
                var satir = document.createElement("div");
                satir.className = "form-satir";
                satir.style.marginBottom = "15px";
                pendingPair.forEach(function(item) {
                    satir.appendChild(item);
                });
                container.appendChild(satir);
                pendingPair = [];
            }

            alanlar.forEach(function(alan) {
                if (alan.target === 'admin') return;
                if (alan.active === 0 || alan.active === '0' || alan.active === false) return;
                
                var formGrup = document.createElement("div");
                formGrup.className = "form-grup";
                formGrup.style.marginBottom = "0";
                
                var labelHtml = `<label>${alan.label}${alan.required == 1 ? ' *' : ''}</label>`;
                var inputHtml = "";
                
                if (alan.type === 'textarea') {
                    flushPair();
                    inputHtml = `<textarea name="${alan.name}" rows="4" ${alan.required == 1 ? 'required' : ''} placeholder="${alan.label} giriniz..."></textarea>`;
                    formGrup.style.marginBottom = "15px";
                    formGrup.innerHTML = labelHtml + inputHtml;
                    container.appendChild(formGrup);
                } else if (alan.type === 'checkbox') {
                    flushPair();
                    var checkGridHtml = `<div style="background:#fce8e6; border-left:3px solid #d93025; padding:12px 15px; border-radius:5px; margin-top:5px; margin-bottom:15px;">`;
                    checkGridHtml += `<label style="color:#d93025; font-weight:bold; font-size:13.5px; margin-bottom:10px; display:block;">${alan.label}:</label>`;
                    checkGridHtml += `<div class="checkbox-grid">`;
                    if (alan.secenekler) {
                        var opts = alan.secenekler.split(',');
                        opts.forEach(function(o) {
                            var val = o.trim();
                            if (val) {
                                checkGridHtml += `<label style="cursor:pointer; display:flex; align-items:center; gap:6px;"><input type="checkbox" name="${alan.name}[]" value="${val}" style="width:16px; height:16px;"> ${val}</label>`;
                            }
                        });
                    }
                    checkGridHtml += `</div></div>`;
                    formGrup.style.marginBottom = "15px";
                    formGrup.innerHTML = checkGridHtml;
                    container.appendChild(formGrup);
                } else {
                    if (alan.type === 'file') {
                        var acceptAttr = alan.name.includes('fotograf') || alan.label.toLowerCase().includes('foto') ? 'accept=".jpg,.jpeg,.png"' : 'accept=".pdf,.jpg,.jpeg,.png"';
                        inputHtml = `<input type="file" name="${alan.name}" ${acceptAttr} ${alan.required == 1 ? 'required' : ''}>`;
                    } else if (alan.type === 'select') {
                        var optionOptions = `<option value="">Seçiniz...</option>`;
                        if (alan.secenekler) {
                            var opts = alan.secenekler.split(',');
                            opts.forEach(function(o) {
                                var val = o.trim();
                                if (val) {
                                    optionOptions += `<option value="${val}">${val}</option>`;
                                }
                            });
                        }
                        inputHtml = `<select name="${alan.name}" ${alan.required == 1 ? 'required' : ''}>${optionOptions}</select>`;
                    } else {
                        var inputType = alan.type || 'text';
                        var maxLen = inputType === 'text' && (alan.name.includes('tc') || alan.label.toLowerCase().includes('tc')) ? 'maxlength="11"' : '';
                        inputHtml = `<input type="${inputType}" name="${alan.name}" ${maxLen} ${alan.required == 1 ? 'required' : ''} placeholder="${alan.label} giriniz...">`;
                    }
                    
                    formGrup.innerHTML = labelHtml + inputHtml;
                    pendingPair.push(formGrup);

                    if (pendingPair.length === 2) {
                        flushPair();
                    }
                }
            });

            flushPair();
        }

        function yeniSatirEkle() {
            var tabloBody = document.querySelector("#eImzaTablosu tbody");
            if (!tabloBody) return;
            var yeniSatir = document.createElement("tr");
            yeniSatir.innerHTML = `
                <td class="sn-hucre"></td>
                <td><input type="text" name="eimza_tc[]" maxlength="11"></td>
                <td><input type="date" name="eimza_dogum[]"></td>
                <td><input type="text" name="eimza_ad[]"></td>
                <td><input type="text" name="eimza_soyad[]"></td>
                <td><input type="text" name="eimza_eposta[]"></td>
                <td><input type="text" name="eimza_birim[]"></td>
                <td><input type="text" name="eimza_gorev[]"></td>
                <td><input type="text" name="eimza_telefon[]"></td>
                <td>
                    <select name="eimza_basvuru_turu[]">
                        <option value="İlk Sertifika">İlk Sertifika</option>
                        <option value="Yenileme">Yenileme</option>
                    </select>
                </td>
                <td><input type="text" name="eimza_odeme[]"></td>
                <td><input type="text" name="eimza_aciklama[]"></td>
                <td>
                    <button type="button" class="btn-satir-sil" onclick="satirSil(this)" title="Satırı Sil">✕</button>
                </td>
            `;
            tabloBody.appendChild(yeniSatir);
            snNumaralariniGuncelle();
        }

        function satirSil(btn) {
            var satir = btn.closest("tr");
            satir.remove();
            snNumaralariniGuncelle();
        }

        function snNumaralariniGuncelle() {
            var snHucraleri = document.querySelectorAll("#eImzaTablosu tbody .sn-hucre");
            snHucraleri.forEach(function(hucre, index) {
                hucre.textContent = index + 1;
            });
        }

        function islemTuruGuncelle(selectElem) {
            var val = selectElem.value;
            var secIlkKez = document.getElementById("sec_ilk_kez");
            var secBilgiDegisikligi = document.getElementById("sec_bilgi_degisikligi");
            var secAciklama = document.getElementById("f52_aciklama_kutusu");
            var secFotograf = document.getElementById("f52_fotograf_kutusu");

            if (val === "Akıllı kartın ilk kez verilmesi") {
                if (secIlkKez) secIlkKez.style.display = "block";
                if (secBilgiDegisikligi) secBilgiDegisikligi.style.display = "none";
                if (secAciklama) secAciklama.style.display = "block";
                if (secFotograf) secFotograf.style.display = "block";
            } else if (val === "Hatalı Basılan Kart Bilgisinin Düzeltilmesi" || val === "Bilgi Değişikliği") {
                if (secIlkKez) secIlkKez.style.display = "none";
                if (secBilgiDegisikligi) secBilgiDegisikligi.style.display = "block";
                if (secAciklama) secAciklama.style.display = "block";
                if (secFotograf) secFotograf.style.display = "block";
            } else if (val === "Ayrılış") {
                if (secIlkKez) secIlkKez.style.display = "none";
                if (secBilgiDegisikligi) secBilgiDegisikligi.style.display = "none";
                if (secAciklama) secAciklama.style.display = "none";
                if (secFotograf) secFotograf.style.display = "none";
            } else {
                if (secIlkKez) secIlkKez.style.display = "none";
                if (secBilgiDegisikligi) secBilgiDegisikligi.style.display = "none";
                if (secAciklama) secAciklama.style.display = "block";
                if (secFotograf) secFotograf.style.display = "block";
            }
        }

        function toggleAccordion(btn) {
            btn.classList.toggle("active");
            var panel = btn.nextElementSibling;
            var arrow = btn.querySelector("span:last-child");
            
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
                if (arrow) arrow.textContent = "▼";
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
                if (arrow) arrow.textContent = "▲";
            }
        }
    </script>

</body>
</html>
