<?php
require_once 'baglan.php';

// Aktif formları çekelim
try {
    $formsStmt = $db->query("SELECT * FROM formlar WHERE durum = 1 ORDER BY kategori ASC, id ASC");
    $aktif_formlar = $formsStmt->fetchAll();
} catch (PDOException $e) {
    $aktif_formlar = [];
}

// Kategorilere göre gruplayalım
$grup_formlar = [];
foreach ($aktif_formlar as $f) {
    $kategori = $f['kategori'] ?: 'Genel Formlar';
    $grup_formlar[$kategori][] = $f;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAÜN Form İşlem Merkezi</title>
    <style>
        /* Genel Sayfa Ayarları */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; padding-bottom: 50px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .navbar-logo { display: flex; align-items: center; gap: 15px; font-weight: bold; color: #1b656e; font-size: 18px; text-decoration: none; transition: opacity 0.3s; }
        .navbar-logo:hover { opacity: 0.8; }
        .navbar-link { font-size: 14px; color: #555; text-decoration: none; transition: color 0.3s; }
        .navbar-link:hover { color: #1b656e; }
        .banner { background-color: rgb(3, 149, 159); color: white; text-align: center; padding: 80px 20px 120px 20px; border-bottom-left-radius: 50% 20%; border-bottom-right-radius: 50% 20%; margin-bottom: -40px; }
        .banner h1 { font-size: 36px; margin: 0 0 10px 0; }
        .banner p { font-size: 14px; opacity: 0.9; }
        .banner p a { color: white; text-decoration: none; transition: opacity 0.3s; }
        .banner p a:hover { opacity: 0.7; text-decoration: underline; }

        /* Form Seçim Kutusu */
        .secim-kutusu { background: white; max-width: 900px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; gap: 15px; position: relative; z-index: 10; }
        .form-select { flex: 1; padding: 15px; font-size: 15px; border: 1px solid #ddd; border-radius: 5px; outline: none; color: #333; appearance: none; background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231b656e%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E"); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 15px auto; }
        .form-select:focus { border-color: #1b656e; }
        .form-select optgroup { font-weight: bold; color: #1b656e; }
        .btn-tamam { background-color: #1b656e; color: white; border: none; padding: 15px 30px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: background 0.3s; text-align: center; }
        .btn-tamam:hover { background-color: rgb(3, 149, 159); }
        #hata-mesaji { color: #e74c3c; text-align: center; margin-top: 15px; font-weight: bold; display: none; }

        /* Gizli Formların Tasarımı */
        .gizli-form { display: none; background: white; max-width: 1100px; margin: 20px auto; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .gizli-form h2 { color: #1b656e; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; text-align: center; }
        .form-grup { margin-bottom: 15px; text-align: left; }
        .form-grup label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 14px; }
        .form-grup input[type="text"], .form-grup input[type="date"], .form-grup input[type="time"], .form-grup input[type="email"], .form-grup select, .form-grup textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: inherit; }
        
        /* Önemli Uyarı ve Checkbox Sınıfları */
        .form-bilgi { font-size: 11.5px; color: #d93025; margin-top: 4px; display: block; font-weight: 500; }
        .form-bilgi-liste { font-size: 11.5px; color: #d93025; background: #fce8e6; padding: 10px; border-radius: 5px; border-left: 3px solid #d93025; margin-bottom: 15px; }
        .resmi-yazi { font-size: 14px; color: #333; text-align: justify; line-height: 1.6; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin-bottom: 20px; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; font-size: 13px; color: #333; }
        .checkbox-grid label { font-weight: normal !important; display: flex; align-items: center; gap: 8px; cursor: pointer; color: #333 !important; }
        
        .form-satir { display: flex; gap: 15px; }
        .form-satir .form-grup { flex: 1; }

        /* Tablo Stil Ayarları (E-İmza Formu İçin) */
        .form-tablosu { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        .form-tablosu th, .form-tablosu td { border: 1px solid #ddd; padding: 8px 5px; text-align: center; }
        .form-tablosu th { background-color: #1b656e; color: white; font-weight: 600; white-space: nowrap; }
        .form-tablosu td input, .form-tablosu td select { width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; font-size: 11px; }

        .btn-alt-satir-ekle { background-color: #27ae60; color: white; border: none; padding: 6px 12px; font-size: 12px; font-weight: bold; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-alt-satir-ekle:hover { background-color: #219150; }
        .btn-satir-sil { background-color: #e74c3c; color: white; border: none; width: 22px; height: 22px; border-radius: 3px; cursor: pointer; font-weight: bold; }

        /* Açılır / Kapanır Akordiyon Stili */
        .accordion-btn { background-color: #e8f4f8; color: #1b656e; cursor: pointer; padding: 12px 15px; width: 100%; border: 1px solid #1b656e; border-radius: 5px; text-align: left; outline: none; font-size: 13.5px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.3s; margin-top: 15px; margin-bottom: 15px; }
        .accordion-btn:hover, .accordion-btn.active { background-color: #1b656e; color: white; }
        .accordion-panel { padding: 0 18px; background-color: #fdfdfd; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; margin-top: -15px; margin-bottom: 15px; font-size: 12.5px; line-height: 1.6; color: #333; }
        .accordion-panel h4 { color: #1b656e; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 3px; }
        .accordion-panel ol, .accordion-panel ul { padding-left: 20px; margin-top: 5px; }
    </style>
</head>
<body>

    <!-- Üst Menü -->
    <div class="navbar">
        <a href="https://bid.balikesir.edu.tr" target="_blank" class="navbar-logo">
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" alt="BAÜN Logo" height="50">
            BALIKESİR ÜNİVERSİTESİ
        </a>
        <div>
            <a href="takip.php" class="navbar-link" style="font-weight:bold; color:#555; margin-right:15px;"> Başvuru Takibi</a>
            <a href="login.php" class="navbar-link" style="font-weight:bold; color:#555; margin-right:15px;">Yönetici Girişi</a>
            <a href="https://bid.balikesir.edu.tr" target="_blank" class="navbar-link">BİLGİ İŞLEM DAİRE BAŞKANLIĞI</a>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner">
        <h1>Üniversitemiz Form İşlem Merkezi</h1>
        <p><a href="https://bid.balikesir.edu.tr" target="_blank">ANASAYFA</a> > FORMLAR</p>
    </div>

    <!-- Başarılı Başvuru Bildirimi -->
    <?php if(isset($_GET['durum']) && $_GET['durum'] == 'basarili'): ?>
        <div style="max-width:900px; margin:20px auto; background:#d4edda; color:#155724; padding:20px; border-radius:8px; border-left:6px solid #28a745; text-align:center; position:relative; z-index:20; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
            <h3 style="margin:0 0 10px 0; font-size:20px;">✓ Form Başvurunuz Başarıyla Alınmıştır!</h3>
            <p style="margin:5px 0; font-size:15px;">Başvurunuzun durumunu sorgulamak için aşağıdaki <b>Başvuru Takip Numarasını</b> saklayınız:</p>
            <div style="font-size:26px; font-weight:bold; color:#1b656e; background:white; display:inline-block; padding:8px 25px; border-radius:5px; margin:10px 0; border:2px dashed #1b656e; letter-spacing:2px;">
                <?php echo htmlspecialchars($_GET['takip_no'] ?? '-'); ?>
            </div>
            <br>
            <a href="takip.php?takip_no=<?php echo urlencode($_GET['takip_no'] ?? ''); ?>" style="display:inline-block; margin-top:5px; color:#1b656e; font-weight:bold; text-decoration:underline;">Başvuru Durumunu Şimdi Sorgula →</a>
        </div>
    <?php endif; ?>

    <!-- Form Seçim Kutusu -->
    <div class="secim-kutusu">
        <select id="formSecici" class="form-select" onchange="formYonetlendir()">
            <option value="">-- Doldurmak İstediğiniz Formu Seçiniz --</option>
            <?php foreach ($grup_formlar as $kategori => $formlar): ?>
                <optgroup label="<?php echo htmlspecialchars($kategori); ?>">
                    <?php foreach ($formlar as $f): ?>
                        <option value="<?php echo htmlspecialchars($f['dosya_adi']); ?>" 
                                data-kodu="<?php echo htmlspecialchars($f['form_kodu']); ?>" 
                                data-adi="<?php echo htmlspecialchars($f['form_adi']); ?>"
                                data-revize="<?php echo !empty($f['son_revize_tarihi']) ? date('d.m.Y H:i', strtotime($f['son_revize_tarihi'])) : date('d.m.Y H:i'); ?>"
                                data-alanlar="<?php echo htmlspecialchars($f['form_alanlari'] ?: ''); ?>">
                            <?php echo htmlspecialchars($f['form_kodu'] . ' - ' . $f['form_adi']); ?> (Son Revize: <?php echo !empty($f['son_revize_tarihi']) ? date('d.m.Y', strtotime($f['son_revize_tarihi'])) : date('d.m.Y'); ?>)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div id="hata-mesaji">Lütfen listeden bir form seçiniz!</div>

    <!-- GİZLİ FORMLAR (MODÜLER FORM DOSYALARI DİNAMİK YÜKLENİR) -->
    <?php
    $mode = 'input';
    $form_files = glob(__DIR__ . '/forms/*.php');
    if ($form_files) {
        foreach ($form_files as $f_file) {
            include $f_file;
        }
    }
    ?>

    <!-- JavaScript Kodları -->
    <script>
        function formYonetlendir() {
            var selectSecici = document.getElementById("formSecici");
            var secilenForm = selectSecici.value;
            var hataMesaji = document.getElementById("hata-mesaji");
            var tumFormlar = document.querySelectorAll(".gizli-form");
            
            tumFormlar.forEach(function(form) { form.style.display = "none"; });
            
            if (secilenForm === "") {
                hataMesaji.style.display = "block";
            } else {
                hataMesaji.style.display = "none";
                
                var selectedOption = selectSecici.options[selectSecici.selectedIndex];
                var formKodu = selectedOption.getAttribute('data-kodu');
                var formAdi = selectedOption.getAttribute('data-adi');
                var alanlarJson = selectedOption.getAttribute('data-alanlar');
                
                var acilacakForm = document.getElementById(secilenForm);
                
                var defaultAlanlar = [
                    {name: 'ad_soyad', label: 'Adı Soyadı', type: 'text', required: 1, secenekler: ''},
                    {name: 'tc_no', label: 'T.C. Kimlik No', type: 'text', required: 1, secenekler: ''},
                    {name: 'telefon', label: 'İrtibat Telefonu', type: 'text', required: 1, secenekler: ''},
                    {name: 'eposta', label: 'E-posta Adresi', type: 'email', required: 1, secenekler: ''},
                    {name: 'birim', label: 'Çalıştığı/Öğrenim Gördüğü Birim', type: 'text', required: 1, secenekler: ''},
                    {name: 'fotograf', label: 'Fotoğraf', type: 'file', required: 0, secenekler: ''},
                    {name: 'dekont', label: 'Ödeme Dekontu / Ek Belge', type: 'file', required: 0, secenekler: ''},
                    {name: 'talep_detayi', label: 'Talep / Açıklama Detayı', type: 'textarea', required: 1, secenekler: ''}
                ];
                
                var hasCustomAlanlar = false;
                var parsedAlanlar = null;
                if (alanlarJson) {
                    try {
                        parsedAlanlar = JSON.parse(alanlarJson);
                        if (Array.isArray(parsedAlanlar) && parsedAlanlar.length > 0) {
                            hasCustomAlanlar = true;
                        }
                    } catch(e) {
                        console.error("Alanlar JSON parse hatası:", e);
                    }
                }
                
                var formRevize = selectedOption.getAttribute('data-revize');
                
                var targetFormElem = (secilenForm === "form_genel.php" || !acilacakForm || hasCustomAlanlar) ? document.getElementById("form_genel.php") : acilacakForm;

                if (targetFormElem) {
                    var h2Elem = targetFormElem.querySelector('h2');
                    if (h2Elem) {
                        var existingBadge = targetFormElem.querySelector('.revize-tarihi-badge');
                        if (!existingBadge) {
                            existingBadge = document.createElement("div");
                            existingBadge.className = "revize-tarihi-badge";
                            existingBadge.style.cssText = "text-align:center; font-size:12.5px; color:#1b656e; margin:-5px 0 15px 0; font-weight:bold; background:#e8f4f8; padding:5px 15px; border-radius:15px; display:block; border:1px solid #1b656e;";
                            h2Elem.insertAdjacentElement('afterend', existingBadge);
                        }
                        existingBadge.innerHTML = " Doküman Son Revize Tarihi: " + (formRevize || "07.08.2026 13:28");
                    }
                }

                if (secilenForm === "form_genel.php" || !acilacakForm || hasCustomAlanlar) {
                    var genelForm = document.getElementById("form_genel.php");
                    if (genelForm) {
                        genelForm.querySelector('input[name="form_kodu"]').value = formKodu;
                        genelForm.querySelector('input[name="form_adi"]').value = formAdi;
                        genelForm.querySelector('h2').textContent = formAdi + ' (' + formKodu + ')';
                        
                        var alanlar = (hasCustomAlanlar && parsedAlanlar) ? parsedAlanlar : defaultAlanlar;
                        
                        dinamikAlanlariCiz("dinamik_alanlar_konteyner", alanlar);
                        genelForm.style.display = "block";
                    } else {
                        alert("Seçtiğiniz form (" + secilenForm + ") henüz hazırlanmaktadır.");
                    }
                } else {
                    acilacakForm.style.display = "block";
                }
            }
        }

        function dinamikAlanlariCiz(containerId, alanlar) {
            var container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = ""; // Temizle
            
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
                if (alan.target === 'admin') return; // Yönetici tarafından doldurulacak alanları başvuru ekranında gizle
                if (alan.active === 0 || alan.active === '0' || alan.active === false) return; // Gizlenmiş (pasif) alanları başvuru ekranında gösterme
                
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
                    var checkGridHtml = `<div class="form-bilgi-liste" style="background:#fce8e6; border-left:3px solid #d93025; padding:12px 15px; border-radius:5px; margin-top:5px; margin-bottom:15px;">`;
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
                    // Normal ikili yan yana alanlar (text, select, date, file, email, number)
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

        // KDYS.FR.0074 Formu İçin Satır Ekleme/Silme İşlemleri
        function yeniSatirEkle() {
            var tabloBody = document.querySelector("#eImzaTablosu tbody");
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

        // F-52 (KDYS.FR.0553) Dinamik Alan Gösterimi
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

        // Akordiyon (Açılır / Kapanır Politika Alanı) Fonksiyonu
        function toggleAccordion(btn) {
            btn.classList.toggle("active");
            var panel = btn.nextElementSibling;
            var arrow = btn.querySelector("span:last-child");
            
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
                arrow.textContent = "▼";
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
                arrow.textContent = "▲";
            }
        }
    </script>

</body>
</html>