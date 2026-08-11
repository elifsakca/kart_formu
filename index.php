<?php
require_once 'baglan.php';

// Aktif formları çekelim
try {
    $formsStmt = $db->query("SELECT * FROM formlar WHERE durum = 1 ORDER BY kategori ASC, form_kodu ASC");
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f8; padding-bottom: 60px; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 50px; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .navbar-logo { display: flex; align-items: center; gap: 15px; font-weight: bold; color: #1b656e; font-size: 18px; text-decoration: none; transition: opacity 0.3s; }
        .navbar-logo:hover { opacity: 0.8; }
        .navbar-link { font-size: 14px; color: #555; text-decoration: none; transition: color 0.3s; }
        .navbar-link:hover { color: #1b656e; }
        
        .banner { background-color: rgb(3, 149, 159); color: white; text-align: center; padding: 60px 20px 100px 20px; border-bottom-left-radius: 50% 20%; border-bottom-right-radius: 50% 20%; margin-bottom: -40px; }
        .banner h1 { font-size: 34px; margin: 0 0 10px 0; font-weight: 700; }
        .banner p { font-size: 15px; opacity: 0.95; }
        .banner p a { color: white; text-decoration: none; transition: opacity 0.3s; }
        .banner p a:hover { opacity: 0.7; text-decoration: underline; }

        /* Arama Kutusu */
        .arama-konteyner { max-width: 900px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 10; }
        .arama-kutusu { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; }
        .arama-input { flex: 1; border: none; font-size: 16px; outline: none; padding: 8px 5px; color: #333; font-family: inherit; }
        .arama-input::placeholder { color: #94a3b8; }
        .arama-icon { font-size: 20px; color: #1b656e; }

        /* Ana İçerik ve Kategoriler */
        .main-container { max-width: 1100px; margin: 35px auto 0 auto; padding: 0 20px; }
        
        .kategori-blok { margin-bottom: 40px; }
        .kategori-baslik { font-size: 20px; color: #1b656e; font-weight: 700; border-bottom: 2.5px solid #1b656e; padding-bottom: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .kategori-rozeti { background: #e8f4f8; color: #1b656e; font-size: 12px; padding: 3px 10px; border-radius: 12px; font-weight: bold; }

        /* Form Kartları Listesi */
        .form-grid { display: flex; flex-direction: column; gap: 15px; }

        .form-kart { background: white; border-radius: 10px; padding: 20px 25px; box-shadow: 0 3px 10px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; }
        .form-kart:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(27,101,110,0.1); border-color: #1b656e; }

        .form-sol-bilgi { flex: 1; padding-right: 20px; }
        .form-kodu-badge { display: inline-block; background: #1b656e; color: white; font-size: 11.5px; font-weight: bold; padding: 3px 10px; border-radius: 4px; margin-bottom: 8px; letter-spacing: 0.5px; }
        .form-revize-badge { display: inline-block; background: #f1f5f9; color: #64748b; font-size: 11px; padding: 3px 8px; border-radius: 4px; margin-left: 8px; }
        .form-baslik { font-size: 16.5px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0; line-height: 1.3; }
        .form-aciklama { font-size: 13px; color: #64748b; margin: 0; line-height: 1.5; }

        .btn-basvur { background-color: #1b656e; color: white; border: none; padding: 12px 24px; font-size: 14.5px; font-weight: 700; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background 0.3s, transform 0.2s; white-space: nowrap; }
        .btn-basvur:hover { background-color: rgb(3, 149, 159); transform: scale(1.03); color: white; }

        .bos-sonuc { background: white; text-align: center; padding: 40px; border-radius: 10px; color: #64748b; box-shadow: 0 3px 10px rgba(0,0,0,0.04); display: none; }

        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; flex-direction: column; gap: 10px; }
            .form-kart { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-basvur { width: 100%; justify-content: center; }
        }
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
        <h1>Balıkesir Üniversitesi Form İşlem Merkezi</h1>
        <p><a href="https://bid.balikesir.edu.tr" target="_blank">ANASAYFA</a> > FORMLAR</p>
    </div>

    <!-- Arama Kutusu -->
    <div class="arama-konteyner">
        <div class="arama-kutusu">
            <span class="arama-icon"></span>
            <input type="text" id="formAramaInput" class="arama-input" placeholder="Form adı, form kodu veya anahtar kelime ile arayınız... (Örn: E-İmza, 0072, Statik IP, Akıllı Kart)" onkeyup="formFiltrele()">
        </div>
    </div>

    <!-- Form Kataloğu Ana Alanı -->
    <div class="main-container">
        
        <div id="bosSonucKutusu" class="bos-sonuc">
            <h3>Aradığınız kriterlere uygun form bulunamadı.</h3>
            <p>Lütfen farklı bir kelime ile tekrar arama yapınız.</p>
        </div>

        <?php foreach ($grup_formlar as $kategori => $formlar): ?>
            <div class="kategori-blok">
                <div class="kategori-baslik">
                     <?php echo htmlspecialchars($kategori); ?>
                    <span class="kategori-rozeti"><?php echo count($formlar); ?> Form</span>
                </div>

                <div class="form-grid">
                    <?php foreach ($formlar as $f): 
                        $form_kodu = htmlspecialchars($f['form_kodu']);
                        $form_adi = htmlspecialchars($f['form_adi']);
                        $dosya_adi = htmlspecialchars($f['dosya_adi']);
                        $revize_tarihi = !empty($f['son_revize_tarihi']) ? date('d.m.Y', strtotime($f['son_revize_tarihi'])) : date('d.m.Y');
                    ?>
                        <div class="form-kart" data-search="<?php echo mb_strtolower($form_kodu . ' ' . $form_adi . ' ' . $kategori, 'UTF-8'); ?>">
                            <div class="form-sol-bilgi">
                                <div>
                                    
                                    <span class="form-baslik"><?php echo $form_adi; ?> </span>
                                    <span class="form-kodu-badge"><?php echo $form_kodu; ?></span>
                                    <div style="display: flex; justify-content: flex-end;">
                                        <span class="form-revize-badge">Son Revize: <?php echo $revize_tarihi; ?></span>
                                    </div>
                                    
                                </div>
                                
                               
                            </div>
                            
                            <div>
                                <a href="doldur.php?kodu=<?php echo urlencode($form_kodu); ?>" target="_blank" class="btn-basvur">
                                    Başvur <span>→</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- JavaScript Kodları -->
    <script>
        function formFiltrele() {
            var input = document.getElementById("formAramaInput");
            var filter = input.value.toLocaleLowerCase("tr");
            var kartlar = document.querySelectorAll(".form-kart");
            var bloklar = document.querySelectorAll(".kategori-blok");
            var bosSonuc = document.getElementById("bosSonucKutusu");
            
            var visibleCount = 0;

            kartlar.forEach(function(kart) {
                var text = kart.getAttribute("data-search");
                if (text.indexOf(filter) > -1) {
                    kart.style.display = "flex";
                    visibleCount++;
                } else {
                    kart.style.display = "none";
                }
            });

            // Kategorileri kontrol et, içinde kart görünen blokları açık tut
            bloklar.forEach(function(blok) {
                var icerdekiKartlar = blok.querySelectorAll(".form-kart");
                var bloktaVarMi = false;
                icerdekiKartlar.forEach(function(k) {
                    if (k.style.display !== "none") {
                        bloktaVarMi = true;
                    }
                });

                if (bloktaVarMi) {
                    blok.style.display = "block";
                } else {
                    blok.style.display = "none";
                }
            });

            if (visibleCount === 0) {
                bosSonuc.style.display = "block";
            } else {
                bosSonuc.style.display = "none";
            }
        }
    </script>

</body>
</html>