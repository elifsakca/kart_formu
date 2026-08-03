<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAÜN Form İşlem Merkezi</title>
    <style>
        /* Genel Sayfa Ayarları */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            padding-bottom: 50px; /* Sayfa altına rahatlık payı */
        }

        /* Üst Menü (Navbar) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: bold;
            color: #1b656e;
            font-size: 18px;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .navbar-logo:hover {
            opacity: 0.8;
        }

        /* Navbar Link Stili */
        .navbar-link {
            font-size: 14px;
            color: #555;
            text-decoration: none;
            transition: color 0.3s;
        }

        .navbar-link:hover {
            color: #1b656e;
        }

        /* Banner Alanı */
        .banner {
            background-color: rgb(3, 149, 159);
            color: white;
            text-align: center;
            padding: 80px 20px 120px 20px;
            border-bottom-left-radius: 50% 20%;
            border-bottom-right-radius: 50% 20%;
            margin-bottom: -40px;
        }

        .banner h1 {
            font-size: 36px;
            margin: 0 0 10px 0;
        }

        .banner p {
            font-size: 14px;
            opacity: 0.9;
        }

        .banner p a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .banner p a:hover {
            opacity: 0.7;
            text-decoration: underline;
        }

        /* Form Seçim Kutusu */
        .secim-kutusu {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            position: relative;
            z-index: 10;
        }

        /* Açılır Liste (Select) */
        .form-select {
            flex: 1;
            padding: 15px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            color: #333;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231b656e%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: 15px auto;
        }

        .form-select:focus {
            border-color: #1b656e;
        }
        
        .form-select optgroup {
            font-weight: bold;
            color: #1b656e;
        }

        /* Tamam Butonu */
        .btn-tamam {
            background-color: #1b656e;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
        }

        .btn-tamam:hover {
            background-color: rgb(3, 149, 159);
        }
        
        /* Hata Mesajı */
        #hata-mesaji {
            color: #e74c3c;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            display: none;
        }

        /* =========================================
           GİZLİ FORMLARIN TASARIMI
           ========================================= */
        .gizli-form { display: none; background: white; max-width: 900px; margin: 20px auto; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .gizli-form h2 { color: #1b656e; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; text-align: center; }
        .form-grup { margin-bottom: 15px; text-align: left; }
        .form-grup label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 14px; }
        .form-grup input, .form-grup select, .form-grup textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: inherit; }
        .form-bilgi { font-size: 12px; color: #e74c3c; margin-top: 5px; display: block; }
        .form-satir { display: flex; gap: 15px; }
        .form-satir .form-grup { flex: 1; }
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
            <a href="https://bid.balikesir.edu.tr" target="_blank" class="navbar-link">BİLGİ İŞLEM DAİRE BAŞKANLIĞI | Balıkesir Üniversitesi</a>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner">
        <h1>Üniversitemiz Form İşlem Merkezi</h1>
        <p><a href="https://bid.balikesir.edu.tr" target="_blank">ANASAYFA</a> > FORMLAR</p>
    </div>

    <!-- Form Seçim Kutusu -->
    <div class="secim-kutusu">
        <select id="formSecici" class="form-select">
            <option value="">-- Doldurmak İstediğiniz Formu Seçiniz --</option>
            
            <!-- Akıllı Kart Formları -->
            <optgroup label="Akıllı Kart Formları">
                <option value="form_f52.php">Akıllı Kart İşlem Formu (F-52)</option>
                <option value="form_f53.php">Akıllı Kart Öğrenci İşlem Formu (F-53)</option>
                <option value="form_f54.php">Kayıp Akıllı Kart Müracaat Formu (F-54)</option>
                <option value="form_f55.php">Arızalı Akıllı Kart Müracaat Formu (F-55)</option>
            </optgroup>

            <!-- Bilgi İşlem Daire Başkanlığı Formları -->
            <optgroup label="Bilgi İşlem Daire Başkanlığı Formları">
                <option value="form_0071.php">KDYS.FR.0071 - Bilgi İşlem DB Bakım Onarım Takip Formu</option>
                <option value="form_0072.php">KDYS.FR.0072 - Bilgi İşlem DB Kurumsal E-Posta Talep Formu</option>
                <option value="form_0073.php">KDYS.FR.0073 - Bilgi İşlem DB E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı</option>
                <option value="form_0074.php">KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu</option>
                <option value="form_0077.php">KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi</option>
                <option value="form_0078.php">KDYS.FR.0078 - Bilgi İşlem DB Kurumsal Statik IP Sözleşmesi</option>
                <option value="form_0079.php">KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi</option>
                <option value="form_0080.php">KDYS.FR.0080 - Bilgi İşlem DB Mernis Taahhütnamesi</option>
                <option value="form_0082.php">KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu</option>
                <option value="form_0087.php">KDYS.FR.0087 - Bilgi İşlem UAM Mernis Taahhütnamesi</option>
            </optgroup>
        </select>
        
        <button class="btn-tamam" onclick="formYonetlendir()">
            Tamam
        </button>
    </div>
    
    <div id="hata-mesaji">Lütfen listeden bir form seçiniz!</div>

    <!-- =========================================
         GİZLİ FORMLAR BURAYA EKLENDİ
         ========================================= -->

    <!-- F-52 FORMU -->
    <div id="form_f52.php" class="gizli-form">
        <h2>Akıllı Kart İşlem Formu (F-52)</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup"><label>Ad, Soyad</label><input type="text" required></div>
                <div class="form-grup"><label>TC Kimlik No</label><input type="text" maxlength="11" required></div>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Fakülte/YO/MYO/Birim</label><input type="text"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>
            <div class="form-grup">
                <label>Kart (Kişi) Tipi</label>
                <select required>
                    <option value="">Seçiniz...</option>
                    <option>Akademik Personel</option>
                    <option>İdari Personel</option>
                    <option>Hizmet Alımı Personeli</option>
                    <option>Firma Personeli</option>
                    <option>Diğer Kurum Personeli</option>
                    <option>Misafir Personel</option>
                    <option>Koruma ve Güvenlik Personeli</option>
                    <option>Özel Güvenlik Personeli</option>
                    <option>Emekli Personel</option>
                    <option>Onursal</option>
                    <option>Kütüphane</option>
                </select>
            </div>
            <div class="form-grup">
                <label>Yapılacak İşlem Türü</label>
                <select required>
                    <option value="">Seçiniz...</option>
                    <option>Akıllı kartın ilk kez verilmesi</option>
                    <option>Hatalı Basılan Kart Bilgisinin Düzeltilmesi</option>
                    <option>Bilgi Değişikliği</option>
                    <option>Ayrılış (İstifa, Emeklilik vb.)</option>
                </select>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Unvanı</label><input type="text"></div>
                <div class="form-grup"><label>Bölüm</label><input type="text"></div>
                <div class="form-grup"><label>Kurum Sicil No</label><input type="text"></div>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Ödemeye Esas Ek Gösterge</label><input type="text"></div>
                <div class="form-grup"><label>Kan Grubu</label><input type="text"></div>
            </div>
            <div class="form-grup">
                <label>Fotoğraf Yükle</label>
                <input type="file" accept=".jpg, .jpeg">
                <span class="form-bilgi">* Fotoğraflar yakın tarihli, vesikalık, dijital çekilmiş (en az 300dpi) ve T.C. kimlik no adıyla .jpg olmalıdır.</span>
            </div>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-53 FORMU -->
    <div id="form_f53.php" class="gizli-form">
        <h2>Akıllı Kart Öğrenci İşlem Formu (F-53)</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup"><label>Ad Soyad</label><input type="text" required></div>
                <div class="form-grup"><label>Okul No</label><input type="text" required></div>
                <div class="form-grup"><label>TC Kimlik No</label><input type="text" maxlength="11" required></div>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Fakülte/Yüksekokul/MYO/Enstitü</label><input type="text"></div>
                <div class="form-grup"><label>Bölüm</label><input type="text"></div>
                <div class="form-grup"><label>Program</label><input type="text"></div>
            </div>
            <div class="form-grup">
                <label>Açıklama (Öğrencinin son durumu ile ilgili bilgi)</label>
                <textarea rows="3" placeholder="Yeni kayıt, mezun, kayıt dondurmuş vb."></textarea>
            </div>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-54 FORMU -->
    <div id="form_f54.php" class="gizli-form">
        <h2>Kayıp Akıllı Kart Müracaat Formu (F-54)</h2>
        <form>
            <div class="form-grup"><label>Görev Yapılan / Öğrenim Görülen Yer</label><input type="text" required></div>
            <div class="form-satir">
                <div class="form-grup"><label>TC Kimlik No</label><input type="text" maxlength="11" required></div>
                <div class="form-grup"><label>Ad Soyad</label><input type="text" required></div>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Kart Seri No</label><input type="text"></div>
                <div class="form-grup"><label>Kayıp Tarihi</label><input type="date"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>
            <p style="font-size: 14px; color: #555; text-align: justify;">
                Yukarıda belirttiğim adıma kayıtlı olan akıllı kimlik kartımı kaybettim. Eski kimlik kartımın AKS sisteminden iptal edilmesini ve bedeli karşılığında yeni kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </p>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-55 FORMU -->
    <div id="form_f55.php" class="gizli-form">
        <h2>Arızalı Akıllı Kart Müracaat Formu (F-55)</h2>
        <form>
            <div class="form-grup"><label>Görev Yapılan / Öğrenim Görülen Yer</label><input type="text" required></div>
            <div class="form-satir">
                <div class="form-grup"><label>TC Kimlik No</label><input type="text" maxlength="11" required></div>
                <div class="form-grup"><label>Ad Soyad</label><input type="text" required></div>
            </div>
            <div class="form-satir">
                <div class="form-grup"><label>Kart Seri No</label><input type="text"></div>
                <div class="form-grup"><label>Arıza Tarihi</label><input type="date"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>
            <p style="font-size: 14px; color: #555; text-align: justify;">
                Eski kimlik kartımın AKS sisteminden iptal edilmesi ve teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </p>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- JS SCRİPT ALANI -->
    <script>
        function formYonetlendir() {
            var secilenForm = document.getElementById("formSecici").value;
            var hataMesaji = document.getElementById("hata-mesaji");
            var tumFormlar = document.querySelectorAll(".gizli-form");
            
            // "Tamam" butonuna her basıldığında önce tüm formları ekrandan gizle
            tumFormlar.forEach(function(form) {
                form.style.display = "none";
            });
            
            // Eğer açılır listeden bir şey seçilmemişse
            if (secilenForm === "") {
                hataMesaji.style.display = "block";
            } else {
                hataMesaji.style.display = "none";
                
                // Seçilen formu ekranda göster
                var acilacakForm = document.getElementById(secilenForm);
                if (acilacakForm) {
                    acilacakForm.style.display = "block";
                } else {
                    // Eğer seçilen form Bilgi İşlem formlarından biriyse
                    alert("Seçtiğiniz form (" + secilenForm + ") yapım aşamasındadır. Şimdilik sadece F-52, F-53, F-54 ve F-55 kart formları aktiftir.");
                }
            }
        }
    </script>

</body>
</html>