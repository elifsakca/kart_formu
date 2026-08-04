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
        .form-grup input[type="text"], .form-grup input[type="date"], .form-grup input[type="time"], .form-grup select, .form-grup textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: inherit; }
        
        /* Önemli Uyarı ve Checkbox Sınıfları (Akıllı Kart Formları İçin) */
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

        /* Teknik Detay ve İdari Tablo Stilleri */
        .teknik-tablo { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; font-size: 13px; }
        .teknik-tablo td, .teknik-tablo th { border: 1px solid #ccc; padding: 8px; vertical-align: middle; }
        .teknik-tablo .baslik-gri { background-color: #e0e0e0; font-weight: bold; color: #000; width: 200px; }
        .teknik-tablo .baslik-ortali { text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; background-color: #f5f5f5; color: #1b656e; }
        .teknik-tablo input[type="text"], .teknik-tablo input[type="date"], .teknik-tablo textarea, .teknik-tablo select { width: 100%; border: 1px solid #ccc; padding: 6px; box-sizing: border-box; border-radius: 3px; }

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
            
            <optgroup label="Akıllı Kart Formları">
                <option value="form_f52.php">Akıllı Kart İşlem Formu (F-52)</option>
                <option value="form_f53.php">Akıllı Kart Öğrenci İşlem Formu (F-53)</option>
                <option value="form_f54.php">Kayıp Akıllı Kart Müracaat Formu (F-54)</option>
                <option value="form_f55.php">Arızalı Akıllı Kart Müracaat Formu (F-55)</option>
            </optgroup>

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

    <!-- GİZLİ FORMLAR -->

    <!-- =========================================
         AKILLI KART FORMLARI EKLENDİ
         ========================================= -->

    <!-- F-52 FORMU (PERSONEL İŞLEMLERİ) -->
    <div id="form_f52.php" class="gizli-form">
        <h2>Akıllı Kart İşlem Formu (F-52)</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Ad, Soyad</label>
                    <input type="text" required>
                    <span class="form-bilgi">Bu kısmın tüm kart tipleri için doldurulması zorunludur.</span>
                </div>
                <div class="form-grup">
                    <label>TC Kimlik No</label>
                    <input type="text" maxlength="11" required>
                    <span class="form-bilgi">Bu kısmın tüm kart tipleri için doldurulması zorunludur.</span>
                </div>
            </div>
            
            <div class="form-satir">
                <div class="form-grup"><label>Fakülte/YO/MYO/Birim</label><input type="text"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>

            <!-- Kart (Kişi) Tipi Alanı -->
            <div class="form-grup">
                <label>Kart (Kişi) Tipi (Fareyi seçeneklerin üzerinde bekleterek açıklamaları görebilirsiniz)</label>
                <select required>
                    <option value="">Seçiniz...</option>
                    <option>Akademik Personel</option>
                    <option>İdari Personel</option>
                    <option title="Temizlik, Hastane Destek vb.">Hizmet Alımı Personeli</option>
                    <option title="müteahhit firma, kiralama, özel yurt personeli, satın alma, yapım, altyapı, onarım, bakım firması">Firma Personeli</option>
                    <option title="Üniversitemiz kadrosunda bulunmayan diğer devlet memurları">Diğer Kurum Personeli</option>
                    <option title="Üniversitemiz kadrosunda öğretim görevlileri veya farklı amaçlarla geçici süre çalışan personel">Misafir Personel</option>
                    <option>Koruma ve Güvenlik Personeli</option>
                    <option>Özel Güvenlik Personeli</option>
                    <option>Emekli Personel</option>
                    <option title="Rektörlük Makamınca uygun görülen ve Üniversiteye maddi, manevi katkıları bulunmuş kişiler">Onursal</option>
                    <option>Kütüphane</option>
                </select>
            </div>

            <!-- İşlem Türü Alanı -->
            <div class="form-grup">
                <label>Yapılacak İşlem Türü</label>
                <select required>
                    <option value="">Seçiniz...</option>
                    <option title="Yeni Başlayan Personel/kişiler için">Akıllı kartın ilk kez verilmesi</option>
                    <option>Hatalı Basılan Kart Bilgisinin Düzeltilmesi</option>
                    <option title="Soyad, Kadro Yeri, ek gösterge vb. Değişikliği">Bilgi Değişikliği</option>
                    <option title="Sebebi: İstifa, Emeklilik, Tayin, Nakil vb.">Ayrılış</option>
                </select>
            </div>
            
            <hr style="border:1px solid #eee; margin: 20px 0;">

            <!-- Detaylı Kurum Bilgileri -->
            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı</label>
                    <input type="text">
                    <span class="form-bilgi">Bu kısım Akademik, İdari Personel ve Yerleşke Onursal, Emekli, Hizmet, Firma, Kurum, Misafir, Kütüphane Giriş Kartları içindir.</span>
                </div>
                <div class="form-grup">
                    <label>Birim</label>
                    <input type="text">
                    <span class="form-bilgi">Akademik ve İdari Personelin kadrosunun olduğu birim, diğer kart tipleri için personelin çalıştığı birim yazılmalıdır.</span>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Bölüm</label>
                    <input type="text">
                    <span class="form-bilgi">Akademik Personelin kadrosunun olduğu bölüm yazılmalıdır.</span>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicil No'su</label>
                    <input type="text">
                    <span class="form-bilgi">Bu kısım Akademik, İdari Personel, Koruma Güvenlik görevlisi ve Yerleşke Kurum Giriş Kartları içindir.</span>
                </div>
            </div>

            <div class="form-grup">
                <label>Ödemeye Esas Ek Göstergesi</label>
                <input type="text">
                <span class="form-bilgi">Personelin ödemeye esas ek göstergesi yemek ücretinin belirlenmesinde baz alınacağı için doğruluğundan ilgili birim sorumlu olacaktır.</span>
            </div>

            <div class="form-satir">
                <div class="form-grup"><label>Hizmet Yeri</label><input type="text"><span class="form-bilgi">Sadece Yerleşke Hizmet, Firma, Kurum Giriş Kartları içindir.</span></div>
                <div class="form-grup"><label>Firma Adı</label><input type="text"><span class="form-bilgi">Sadece Yerleşke Firma Giriş Kartları içindir.</span></div>
            </div>

            <div class="form-satir">
                <div class="form-grup"><label>Kurumu</label><input type="text"><span class="form-bilgi">Yerleşke Kurum Giriş Kartları içindir.</span></div>
                <div class="form-grup"><label>Kan Grubu</label><input type="text"><span class="form-bilgi">Sadece Koruma Güvenlik veya Özel Güvenlik kartı alacak personel doldurmalıdır.</span></div>
            </div>
            
            <div class="form-grup">
                <label>Görev</label>
                <input type="text">
                <span class="form-bilgi">Bu kısmı sadece Yerleşke Misafir Giriş Kartı alacak personel doldurmalıdır.</span>
            </div>

            <!-- Bilgi Değişikliği Özel Alanı -->
            <div class="form-bilgi-liste" style="background:#e8f4f8; border-left-color:#1b656e;">
                <label style="color:#1b656e; font-weight:bold; font-size:14px; margin-bottom:10px; display:block;">Hatalı Basılan Kart veya Bilgi Değişikliği Yapılacaksa Düzeltilecek / Değişecek Kısmı Seçiniz:</label>
                
                <div class="checkbox-grid">
                    <label><input type="checkbox"> Ad, Soyad</label>
                    <label><input type="checkbox"> Unvan</label>
                    <label><input type="checkbox"> Görev</label>
                    <label><input type="checkbox"> Birim</label>
                    <label><input type="checkbox"> Bölüm</label>
                    <label><input type="checkbox"> Kurum Sicil No</label>
                    <label><input type="checkbox"> TC Kimlik No</label>
                    <label><input type="checkbox"> Hizmet Yeri</label>
                    <label><input type="checkbox"> Firma Adı</label>
                    <label><input type="checkbox"> Kurum Adı</label>
                    <label><input type="checkbox"> Kan Grubu</label>
                    <label><input type="checkbox"> Fotoğraf</label>
                    <label style="grid-column: span 2;"><input type="checkbox"> Ödemeye Esas Ek Göstergesi</label>
                    <label style="grid-column: span 3; display: flex; gap: 10px;">
                        <input type="checkbox"> Diğer: 
                        <input type="text" style="width: 250px; padding: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;">
                    </label>
                </div>

                <label style="color:#1b656e; font-weight:bold; font-size:13px; margin-bottom:5px; margin-top: 15px; display:block;">Yeni Bilgi (Seçtiğiniz alanın doğru halini aşağıya yazınız):</label>
                <textarea rows="2" style="width:100%; border:1px solid #ccc; border-radius:4px; padding:5px;"></textarea>
            </div>

            <!-- Form AÇIKLAMA Listesi -->
            <div class="form-bilgi-liste">
                <strong>AÇIKLAMA (Lütfen kart tipinize göre zorunlu alanları kontrol ediniz):</strong><br>
                + <b>Akademik Personel Kimlik Kartı</b> için Ad, Soyad, Unvan, Görev, Birim, Bölüm, Kurum sicil no ve T.C Kimlik No kısımları doldurulacaktır.<br>
                + <b>İdari Personel Kimlik Kartı</b> için Ad, Soyad, Unvan, Kadrosunun Olduğu Birim/Bölüm, Kurum sicil no ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Yerleşke Hizmet Giriş Kartı</b> için Ad, Soyad, Unvan, Firma Adı, Birim, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Yerleşke Firma Giriş Kartı</b> için Ad, Soyad, Unvan, Firma Adı, Birim, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Yerleşke Kurum Giriş Kartı</b> için Ad, Soyad, Unvan, Kurum, Kurum Sicil no, Hizmet Yeri ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Yerleşke Misafir Giriş Kartı</b> için Ad, Soyad, Unvan, Görev, Birim ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Yerleşke Emekli, Onursal ve Kütüphane Giriş Kartı</b> için Ad, Soyad, Unvan ve T.C Kimlik no kısımları doldurulacaktır.<br>
                + <b>Koruma ve Güvenlik Görevlisi</b> için Ad, Soyad, Kan Grubu, Kurum Sicil no ve T.C Kimlik no doldurulacaktır.<br>
                + <b>Özel Güvenlik Görevlisi</b> için Ad, Soyad, Kan Grubu ve T.C Kimlik no doldurulacaktır.
            </div>

            <div class="form-grup">
                <label>Fotoğraf Yükle</label>
                <input type="file" accept=".jpg, .jpeg">
                <span class="form-bilgi"><b>Fotoğraflar için önemli not:</b> Gönderilecek fotoğraflar; yakın tarihli, vesikalık standardında, dijital olarak çekilmiş veya iyi taranmış (en az 300dpi) olmalıdır. Fotoğraf bilgileri; T.C kimlik no, dosya adı olmak üzere; jpg dosyası biçiminde (örnek: 12345678901.jpg) olmalıdır. Uygun Fotoğraf bilgisi olmayan kişiler için kart basımı yapılamamaktadır.</span>
            </div>
            
            <div class="form-bilgi" style="margin-bottom:15px;">
                <b>Önemli not:</b> Hatalı basılan veya değişecek kart bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır. Ödemeye esas ek göstergenin değişimi için akıllı kart gönderilmeyecektir.
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-53 FORMU (ÖĞRENCİ) -->
    <div id="form_f53.php" class="gizli-form">
        <h2>Akıllı Kart Öğrenci İşlem Formu (F-53)</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup"><label>AD SOYAD</label><input type="text" required></div>
                <div class="form-grup"><label>OKUL NO</label><input type="text" required></div>
                <div class="form-grup"><label>TC Kimlik No</label><input type="text" maxlength="11" required></div>
            </div>
            
            <div class="form-satir">
                <div class="form-grup"><label>Fakülte/Yüksekokul/MYO/Enstitü</label><input type="text"></div>
                <div class="form-grup"><label>BÖLÜM</label><input type="text"></div>
                <div class="form-grup"><label>PROGRAM</label><input type="text"></div>
            </div>
            
            <div class="form-grup">
                <label title="Yeni kayıt (kart), lisans, yüksek lisans, yaz okulu, düzeltme, fotoğraf, mezun, kayıt dondurmuş, uzaklaştırma vb.">
                    AÇIKLAMA (Öğrencinin son durumu ile ilgili bilgiyi yazınız)
                </label>
                <textarea rows="3"></textarea>
                <span class="form-bilgi">Fare imlecini 'AÇIKLAMA' başlığının üzerinde bekleterek açıklama örneklerini görebilirsiniz.</span>
            </div>
            
            <div class="form-bilgi" style="margin-bottom:15px;">
                <b>Önemli Not:</b> Hatalı basılan veya değişecek kartlar bu form ile birlikte bir üst yazı ekinde Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-54 FORMU (KAYIP KART) -->
    <div id="form_f54.php" class="gizli-form">
        <h2>Kayıp Akıllı Kart Müracaat Formu (F-54)</h2>
        <form>
            <div class="resmi-yazi">
                Aşağıda belirttiğim adıma kayıtlı olan akıllı kimlik kartımı kaybettim. Eski kimlik kartımın AKS sisteminden iptal edilmesini ve bedeli karşılığında yeni kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </div>
            
            <div class="form-grup"><label>Görev Yapılan / Öğrenim Görülen Yer</label><input type="text" required></div>
            
            <div class="form-satir">
                <div class="form-grup"><label>TC Kimlik Numarası</label><input type="text" maxlength="11" required></div>
                <div class="form-grup"><label>Ad Soyad (Adıma Kayıtlı Olan)</label><input type="text" required></div>
            </div>
            
            <div class="form-satir">
                <div class="form-grup"><label>Kart Seri No</label><input type="text"></div>
                <div class="form-grup"><label>Kayıp Tarihi</label><input type="date"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- F-55 FORMU (ARIZALI KART) -->
    <div id="form_f55.php" class="gizli-form">
        <h2>Arızalı Akıllı Kart Müracaat Formu (F-55)</h2>
        <form>
            <div class="resmi-yazi">
                Eski kimlik kartımın AKS sisteminden iptal edilmesi ve akıllı kart merkezince yapılan teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </div>

            <div class="form-grup"><label>Görev Yapılan / Öğrenim Görülen Yer</label><input type="text" required></div>
            
            <div class="form-satir">
                <div class="form-grup"><label>TC Kimlik Numarası</label><input type="text" maxlength="11" required></div>
                <div class="form-grup"><label>Ad Soyad (Adıma Kayıtlı Olan)</label><input type="text" required></div>
            </div>
            
            <div class="form-satir">
                <div class="form-grup"><label>Kart Seri No</label><input type="text"></div>
                <div class="form-grup"><label>Arızalanma Tarihi</label><input type="date"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text"></div>
            </div>
            
            <div class="form-bilgi" style="margin-bottom:15px; font-size:12px;">
                <b>Uyarı:</b> Arızalı kart bu form ile birlikte Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen, hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0071 - Bilgi İşlem DB Bakım Onarım Takip Formu -->
    <div id="form_0071.php" class="gizli-form">
        <h2>KDYS.FR.0071 - Bilgi İşlem DB Bakım Onarım Takip Formu</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı</label>
                    <input type="text" value="BALIKESİR ÜNİVERSİTESİ-" required>
                </div>
                <div class="form-grup">
                    <label>Talebi İleten Personelin Adı-Soyadı</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>Cihazın Marka/Modeli ve Seri No</label>
                    <input type="text" placeholder="Marka/Model - Seri No: ...">
                </div>
            </div>

            <div class="form-grup">
                <label>Arıza / Bakım / Tesis Tanımı (Şikâyet sebebi)</label>
                <textarea rows="3" required></textarea>
            </div>

            <div class="form-grup">
                <label>Donanım Destek Grubuna İletmeniz Gereken Bir Durum Var mı?</label>
                <textarea rows="2"></textarea>
            </div>

            <div class="form-bilgi-liste" style="background: #f9f9f9; border-left-color: #1b656e; color: #333;">
                <p style="margin: 0 0 8px 0;">* Donanım destek birimine teslim edilen cihaz içerisinde destek personelinin görmesinde sakınca olan hiçbir veri bulunmamalıdır.</p>
                <p style="margin: 0 0 8px 0;">* Formatlanması için teslim edilen cihazdaki tüm veriler cihazın sahibi tarafından yedeklenmelidir (Donanım destek biriminde yedek alınmayacaktır).</p>
                <p style="margin: 0 0 10px 0;">* Donanım destek birimi personeli tarafından cihazdaki verinin kopyalanmayacağı ve hiçbir şekilde paylaşılmayacağı taahhüt edilir.</p>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: bold; cursor: pointer;">
                    <input type="checkbox" required> Açıklamaları okudum, onaylıyorum.
                </label>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Bildirimde Bulunan Ad-Soyad</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Tarih ve Saat</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="date" required>
                        <input type="time" required>
                    </div>
                </div>
            </div>

            <!-- TEKNİK DEĞERLENDİRME VE TESLİM BÖLÜMÜ -->
            <table class="teknik-tablo">
                <tr>
                    <td class="baslik-gri">Tespit Edilen Durum</td>
                    <td><textarea rows="2"></textarea></td>
                    <td class="secenek-kutusu">
                        <label>Antivirüs Var</label>
                        <input type="checkbox">
                    </td>
                    <td class="secenek-kutusu">
                        <label>Garanti Var</label>
                        <input type="checkbox">
                    </td>
                </tr>
                <tr>
                    <td class="baslik-gri">Arıza Bakım Tesis Sebebi</td>
                    <td colspan="3"><input type="text"></td>
                </tr>
                <tr>
                    <td class="baslik-gri">Yapılan İşlemler</td>
                    <td colspan="3"><textarea rows="2"></textarea></td>
                </tr>
                <tr>
                    <td class="baslik-gri">Kullanılan Malzemeler</td>
                    <td colspan="3"><input type="text"></td>
                </tr>
                <tr>
                    <td colspan="2" class="imza-baslik">İş Bitirme</td>
                    <td colspan="2" class="imza-baslik">Teslim<br><span style="font-size:11px; font-weight:normal;">(Cihaz Donanım Destek Grubuna Bırakıldıysa)</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="imza-alani">
                        <strong>İşlemi Yapan Personel</strong><br><br>
                        <input type="text" placeholder="Ad Soyad / Tarih">
                    </td>
                    <td class="imza-alani">
                        <strong>Teslim Eden Personel</strong><br><br>
                        <input type="text" placeholder="Ad Soyad / Tarih">
                    </td>
                    <td class="imza-alani">
                        <strong>Teslim Alan Personel</strong><br><br>
                        <input type="text" placeholder="Ad Soyad / Tarih">
                    </td>
                </tr>
            </table>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0072 - Bilgi İşlem DB Kurumsal E-Posta Talep Formu -->
    <div id="form_0072.php" class="gizli-form">
        <h2>KDYS.FR.0072 - Bilgi İşlem DB Kurumsal E-Posta Talep Formu</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı</label>
                    <input type="text" value="BALIKESİR ÜNİVERSİTESİ-" required>
                </div>
                <div class="form-grup">
                    <label>Sorumlu Personelin Adı Soyadı</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası</label>
                    <input type="text" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>E-posta adresi (Hesap bilgileri gönderilecek)</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen E-posta Adresi</label>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <input type="text" style="flex: 1;" placeholder="örnek" required>
                    <span>@balikesir.edu.tr</span>
                </div>
            </div>

            <div class="form-grup">
                <label>Kurumsal E-posta Kullanım Amacı</label>
                <div class="checkbox-grid">
                    <label><input type="checkbox"> Fakülte/YO Adına</label>
                    <label><input type="checkbox"> Bölüm/Birim Adına</label>
                    <label><input type="checkbox"> Topluluk/Dernek</label>
                    <label><input type="checkbox"> Proje Grubu</label>
                    <label><input type="checkbox"> Konferans/Kongre/Sempozyum</label>
                    <label><input type="checkbox"> Diğer</label>
                </div>
                <textarea rows="2" placeholder="Diğer veya ek açıklamalarınız..."></textarea>
            </div>

            <!-- AÇILIR / KAPANIR E-POSTA KULLANIM KURALLARI BUTONU -->
            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span>BALIKESİR ÜNİVERSİTESİ ELEKTRONİK POSTA (e-mail) ADRESİ KULLANIM KURALLARI</span>
                <span class="icon">▼</span>
            </button>
            <div class="accordion-panel">
                <div style="padding-top: 15px; padding-bottom: 15px;">
                    <p><strong>1- KANUNİ YÜKÜMLÜLÜK:</strong></p>
                    <p>1.1- @balikesir.edu.tr domain’i T.C. Balıkesir Üniversitesi personeline (Akademik vce İdari) hizmet vermektedir. Bu hizmet akademik eğitim- öğretim amaçlı araştırma ve geliştirme faaliyetleri içermektedir.</p>
                    <p>1.2- @balikesir.edu.tr domain’ine ait e-posta hesaplarını kullanan şahıslar Türkiye Cumhuriyeti kanun ve bunlara bağlı olan yönetmeliklere, Türkiye Bilimsel ve Teknik Araştırma Kurumu'nun (TÜBİTAK) bir enstitüsü olan Ulusal Akademik Ağ ve Bilgi Merkezi (ULAKBİM) tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esaslara, T.C. Balıkesir Üniversitesi yönetmeliklerine aykırı hareket edemezler.</p>
                    <ul style="list-style-type: none; padding-left: 10px;">
                        <li style="margin-bottom: 8px;">1.2.1- İnternet Ortamında Yapılan Yayınların Düzenlenmesi ve Bu Yayınlar Yoluyla İşlenen Suçlarla Mücadele Edilmesi Hakkında Kanun. (Kanun/Karar No: 5651, Tarih: 23.05.2007) <br><a href="http://www.resmigazete.gov.tr/main.aspx?home=http://www.resmigazete.gov.tr/eskiler/2007/11/20071130-6.htm/20071130.htm&main=http://www.resmigazete.gov.tr/eskiler/2007/11/20071130-6.htm" target="_blank" style="color:#1b656e; text-decoration: underline; font-weight: bold;">Resmi Gazete Linki İçin Tıklayınız</a></li>
                        <li style="margin-bottom: 8px;">1.2.2- İnternet Ortamında Yapılan Yayınların Düzenlenmesine Dair Usul ve Esaslar Hakkında Yönetmelik (Tarih:30.11.2007) <br><a href="http://www.resmigazete.gov.tr/main.aspx?home=http://www.resmigazete.gov.tr/eskiler/2007/11/20071130-6.htm/20071130.htm&main=http://www.resmigazete.gov.tr/eskiler/2007/11/20071130-6.htm" target="_blank" style="color:#1b656e; text-decoration: underline; font-weight: bold;">Resmi Gazete Linki İçin Tıklayınız</a></li>
                        <li style="margin-bottom: 8px;">1.2.3- Birlikte Çalışabilirlik Esasları Rehberi ile İlgili 2005/20 Sayılı Başbakanlık Genelgesi. (Tarih: 05.08.2005) <br><a href="http://www.resmigazete.gov.tr/main.aspx?home=http://www.resmigazete.gov.tr/eskiler/2005/08/20050805-11.htm/20050805.htm&main=http://www.resmigazete.gov.tr/eskiler/2005/08/20050805-11.htm" target="_blank" style="color:#1b656e; text-decoration: underline; font-weight: bold;">Resmi Gazete Linki İçin Tıklayınız</a></li>
                        <li style="margin-bottom: 8px;">1.2.4- Türkiye Bilimsel ve Teknik Araştırma Kurumu'nun (TÜBİTAK) bir enstitüsü olan Ulusal Akademik Ağ ve Bilgi Merkezi (ULAKBİM) tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esasları <br><a href="http://ulakbim.tubitak.gov.tr/sites/images/Ulakbim/ukp-v2011.pdf" target="_blank" style="color:#1b656e; text-decoration: underline; font-weight: bold;">ULAKBİM Belge Linki İçin Tıklayınız</a></li>
                    </ul>

                    <p><strong>2- GİZLİLİK ve GÜVENLİK:</strong></p>
                    <p>2.1- T.C. Balıkesir Üniversitesinden personel e-posta adresi talep eden şahıslar, bu formu doldurup personel kimlikleri ile birlikte Bilgi İşlem Dairesi Başkanlığına şahsen müracaat etmeleri gerekmektedir. Diğer talepler değerlendirmeye alınmayacaktır.</p>
                    <p>2.2- Balıkesir Üniversitesinden e-posta adresi alan kişi, Bilgi İşlem Daire Başkanlığı’nın belirleyeceği bir e-posta hesap adı, öğrenci numarasından oluşan bir kullanıcı adı ve kendisinin belirleyeceği bir kullanıcı şifresine sahip olur.</p>
                    <p>2.3- Kullanıcı adı ve e-posta adı kişiye özeldir ve @balikesir.edu.tr domainin de bir benzeri daha yoktur.</p>
                    <p>2.4- Kullanıcı şifresi sadece kullanıcı tarafından bilinir. Kullanıcı dilediği zaman şifresini değiştirebilir. Şifrenin seçimi ve korunması tamamıyla kullanıcının sorumluluğundadır. Bilgi İşlem Daire Başkanlığı, şifre kullanımından doğacak problemlerden kesinlikle sorumlu değildir.</p>
                    <p>2.5- E-posta şifresini unutan kullanıcılar, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığına bizzat müracaat etmek zorundadır.</p>

                    <p><strong>3- E-POSTA ADRESİ ALAN KİŞİNİN YÜKÜMLÜLÜKLERİ: Kişi;</strong></p>
                    <p>3.1- E-posta hesabı sahibi, bu servisi kullanırken ileri sürdüğü şahsi fikir, düşünce ve ifadeler ile elektronik ortama eklediği dosya ve/veya bilgilerin sorumluluğunun şahsına ait olduğunu ve bundan dolayı bu e-posta ile ekli dosyalardan dolayı hiçbir şekilde Balıkesir Üniversitesinin sorumlu tutulmayacağını kabul eder.</p>
                    <p>3.2- Balıkesir Üniversitesi e-posta hizmetlerinde, e-posta sitesinin geneline zarar verecek veya Balıkesir Üniversitesi’ni başka şahıs ya da kuruluşlarla adli (mahkemelik) duruma getirecek herhangi bir yazılım veya materyal bulunduramayacağını, paylaşamayacağını ve hukuki bir durum doğarsa tüm adli ve cezai sorumlulukları üstüne aldığını kabul eder.</p>
                    <p>3.3- E-posta servisinin kullanımı sırasında kaybolacak ve/veya eksik alınacak, yanlış adrese iletilecek bilgi, mesaj ve dosyalardan Balıkesir Üniversitesi Bilgi İşlem dairesi Başkanlığının sorumlu olmayacağını kabul eder.</p>
                    <p>3.4- E-posta hesabı sahibi, teknik nedenlerden (arıza, güncelleme, aktarma vb.) dolayı e-posta lardaki gecikme ve kayıplardan dolayı Balıkesir Üniversitesi Bilgi İşlem Dairesi Bşk. sorumlu olmayacağını kabul eder.</p>
                    <p>3.5- E-posta hesabı sahibi, posta hesaplarındaki verilerinin, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın ihmali görülmeden, yetkisiz kişilerce okunmasından (e-posta sahiplerinin, gizli bilgilerini başka kişiler ile paylaşması, siteden ayrılırken çıkış yapmaması, vb. durumlardan) dolayı gelebilecek maddi ve manevi zararlardan ötürü, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın sorumlu olmadığını kabul eder.</p>
                    <p>3.6- E-posta hesabı sahibi, başka şahıs veya kuruluşlardaki bilgisayara, bu bilgisayarlardaki bilgilere ya da yazılıma zarar verecek bilgi veya programlar göndermemeyi ve barındırmamayı, aksi takdirde doğacak tüm hukuki ve cezai sorumluluğun şahsına ait olduğunu kabul eder.</p>
                    <p>3.7- Üniversite e-posta servisini kullanarak elde edilen herhangi bir bilgi veya materyalin tamamıyla kullanıcının rızası dahilinde olduğunu, kullanıcı bilgisayarında yaratacağı arızalar, bilgi kaybı ve diğer kayıpların sorumluluğunun tamamıyla kendisine ait olduğunu, eposta servisinin kullanımından dolayı uğrayabileceği zararlardan Balıkesir Üniversitesinin sorumlu olmadığını kabul eder.</p>
                    <p>3.8- E-posta hesabı sahibi, genel ahlak ve adaba aykırı, ırkçı, ayrımcı, ticari, siyasi propaganda, taciz ve tehdit edici ile Türkiye Cumhuriyeti yasalarına, vatandaşı olduğu diğer ülkelerin yasalarına ve uluslararası anlaşmalara aykırı e-posta göndermemeyi, barındırmamayı ve bunlara aykırı her türlü uygulamalardan doğacak cezai ve hukuki sorumluluğun şahsına ait olduğunu kabul eder.</p>
                    <p>3.9- E-posta hesabı sahibi, T.C. Kanunlara göre postalanması yasak, gizli olan bilgileri postalamamayı, barındırmamayı ve gönderilme yetkisi olamayan postaları dağıtmamayı ile bunlara ait yasal yükümlülüğü kabul eder.</p>
                    <p>3.10- E-posta hesabı sahibi, zincir posta (chain mail), yazılım virüsü vb. postaları başka posta hesaplarına dağıtmamayı, barındırmamayı ve bunlara ait cezai ve yasal yükümlülüğü kabul eder.</p>
                    <p>3.11- E-posta hesabı sahibi, rastgele ve alıcının istemi dışında mesaj (spam iletiler) göndermeyceğini ve bunlara ait yasal yükümlülüğü kabul eder.</p>
                    <p>3.12- E-posta hesabı sahibi, e-posta kullanıcı adıyla yapacağı her türlü işlemden bizzat kendisinin sorumlu olduğunu kabul eder.</p>
                    <p>3.13- E-posta hesabı sahipleri, kullanım haklarını, doğrudan ya da dolaylı olarak 3. şahıslara devredemez ve kiralayamazlar.</p>
                    <p>3.14- E-posta hesabı sahibi yasa ve kurallara aykırı davrandığı takdirde Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığı’nın gerekli müdahalelerde bulunma, kişiyi hizmet dışına çıkarma ve üyeliğine son verme hakkına sahip olduğunu kabul eder.</p>
                    <p>3.15- E-posta hesabı sahibi, yasa ve kurallara aykırı davrandığı takdirde T.C. Balıkesir Üniversitesi makamlarının; gerekli sözlü ve yazılı uyarıda bulunmaya, kişiyi sınırlı veya sınırsız hizmet dışına çıkarmaya, üniversite içi idari soruşturma başlatmaya ya da adli yargıya bildirimde bulunma hakkına sahip olduğunu kabul eder.</p>
                    <p>3.16- E-posta hesabı sahibi, e-posta hesabını tek taraflı olarak iptal ettirse bile, bu iptal işleminden önce, üyeliği sırasında gerçekleştirdiği icraatlardan kendisinin sorumlu olacağını kabul eder.</p>
                    <p>3.17- E-posta sahibi, Balıkesir Üniversitesi e-posta hizmetinden yararlandığı sırada, kayıt formunda yer alan bilgilerin doğru olduğunu ve bu bilgilerin gerekli olduğu (şifre unutma gibi) durumlarda, bilginin hatalı veya noksan olmasından doğacak zararlardan dolayı sorumluluğun kendisine ait olduğunu, bu hallerde e-mail adresinin iptal edileceğini kabul eder.</p>
                    <p>3.18- T.C. Balıkesir Üniversitesi Bilgi İşlem Dairesi Başkanlığı, kullanıcıların bilgisi olmaksızın E-posta hizmetleri servis politikasında değişiklik yapma hakkına sahiptir. Kullanıcılar bu metinde yer alan bilgileri izlemek ve olası değişikliklerden haberdar olmakla yükümlüdürler.</p>
                    <p>3.19- Başvuran Taraf, ULAKBİM'in yasal ve teknolojik gelişmeleri gözönünde tutarak bu Kullanım Politikası'nı kısmen değiştirebileceğinden haberdardır ve bunu açıkça kabul eder. Değiştirilen Kullanım Politikası, <a href="http://www.ulakbim.gov.tr/ulaknet/kullanim-politikasi.uhtml" target="_blank" style="color:#1b656e; text-decoration: underline; font-weight: bold;">http://www.ulakbim.gov.tr/ulaknet/kullanim-politikasi.uhtml</a> adresinde yeralması ile birlikte yürürlüğe girer. Başvuran Taraf ULAKBİM'in Kullanım Politikası'nı tam olarak anladığını, tanıdığını, uyacağını kabul eder.</p>
                    <p>3.20- Tüm bu maddeleri daha sonra hiçbir itiraza mahal vermeyecek şekilde okuduğunu, KABUL ve TAAHHÜT ETMİŞTİR.</p>

                    <p><strong>4- YÜRÜRLÜLÜK:</strong></p>
                    <p>Kullanıcı, adına düzenlenmiş bu formu doldurduktan sonra bu sözleşme yürürlüğe girer ve T.C. Balıkesir Üniversitesi birimi olduğu sürece devam eder.</p>
                </div>
            </div>

            <div class="resmi-yazi" style="font-size: 12.5px;">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyoruz. Ayrıca yukarıda bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Tarih</label>
                    <input type="date" required>
                </div>
            </div>

            <!-- BİLGİ İŞLEM DAİRESİ İŞLEMLERİ BÖLÜMÜ -->
            <table class="teknik-tablo">
                <tr>
                    <td colspan="2" class="baslik-ortali">BİLGİ İŞLEM DAİRESİ İŞLEMLERİ</td>
                </tr>
                <tr>
                    <td class="baslik-gri">İşlem Tarihi *</td>
                    <td><input type="date" style="max-width: 200px;"></td>
                </tr>
                <tr>
                    <td class="baslik-gri">E-posta Adresi *</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <input type="text" style="flex: 1;" placeholder="........................">
                            <strong>@balikesir.edu.tr</strong>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="baslik-gri">E-posta adresinin geçerlilik süresi *</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                                <input type="checkbox"> Süresiz
                            </label>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <input type="text" style="width: 150px;" placeholder="____">
                                <span>ay/gün/yıl</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="baslik-gri">Kullanıcı Şifresi *</td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td class="baslik-gri">İşlemi Yapan Personel *</td>
                    <td><input type="text" placeholder="Ad Soyad"></td>
                </tr>
            </table>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0073 - Bilgi İşlem DB E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı -->
    <div id="form_0073.php" class="gizli-form">
        <h2>KDYS.FR.0073 - E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold;">
                ÜRÜNÜ ALAN KİŞİ BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>AD, SOYAD</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>TC KİMLİK NO</label>
                    <input type="text" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Fakülte/YO/MYO/Birim</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>İrtibat Telefonu</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Tarihi</label>
                <input type="date" required>
            </div>

            <div class="resmi-yazi">
                Yukarıda belirtilen tarihte talep etmiş olduğum e-imza mini kart okuyucuyu TÜBİTAK Bilişim ve Bilgi Güvenliği İleri Teknolojileri Araştırma Merkezi firmasından tarafımca teslim aldığımı beyan ederim.
            </div>

            

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0074 - E-İmza Talep Formu -->
    <div id="form_0074.php" class="gizli-form">
        <h2>KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold;">
                E-İMZA BAŞVURU SAHİBİ / PERSONEL BİLGİ LİSTESİ
            </div>
            <div class="form-tablosu-wrapper">
                <table class="form-tablosu" id="eImzaTablosu">
                    <thead>
                        <tr>
                            <th style="min-width: 40px;">S.N.</th>
                            <th>T.C. Kimlik No</th>
                            <th>Doğum Tarihi (Gün/Ay/Yıl)</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>E-Posta Adresi</th>
                            <th>Çalıştığı Birimi</th>
                            <th>Görevi</th>
                            <th>Cep Tel. No</th>
                            <th>Başvuru Türü</th>
                            <th>Ödeme</th>
                            <th>Açıklama</th>
                            <th style="width: 30px;">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="sn-hucre">1</td>
                            <td><input type="text" maxlength="11"></td>
                            <td><input type="date"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td>
                                <select>
                                    <option value="İlk Sertifika">İlk Sertifika</option>
                                    <option value="Yenileme">Yenileme</option>
                                </select>
                            </td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="13" style="text-align: left; background-color: #fcfcfc; padding: 10px;">
                                <button type="button" class="btn-alt-satir-ekle" onclick="yeniSatirEkle()">
                                    <span>+</span> Yeni Satır Ekle
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi -->
    <div id="form_0077.php" class="gizli-form">
        <h2>KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KİŞİSEL WEB ADI VE ALANI TALEP BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı *</label>
                    <input type="text" placeholder="BALIKESİR ÜNİVERSİTESİ - ..." required>
                </div>
                <div class="form-grup">
                    <label>Personelin Adı-Soyadı *</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası *</label>
                    <input type="text" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>E-posta Adresi (Hesap bilgileri bu adrese gönderilecektir) *</label>
                    <input type="email" placeholder="ornek@balikesir.edu.tr" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen Web Adı *</label>
                <input type="text" placeholder="kullaniciadi.baun.edu.tr" required>
            </div>

            <div class="form-grup">
                <label>Kullanım Amacı *</label>
                <textarea rows="3" placeholder="Web alanının kullanım amacını detaylıca açıklayınız..." required></textarea>
            </div>

            <!-- Taahhüt Metni -->
            <div class="resmi-yazi">
                Akademik/İdari çalışmalarımda kullanmak üzere, sistemde yukarıda belirtilen alan adının açılması, 150 MB web ve 20 MB veritabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca bu sayfanın altında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve <a href="http://bid.balikesir.edu.tr" target="_blank" style="color: #1b656e; font-weight: bold;">http://bid.balikesir.edu.tr</a> adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaları okuduğumu ve bunlara uygun hareket edeceğimi taahhüt ederim.
            </div>

            <!-- Açılır/Kapanır Bilişim Politikası Paneli -->
            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BAÜN Bilişim Kaynakları Kullanım Politikası</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <h4>1. Tanımlamalar</h4>
                <p><strong>BAÜN Bilişim Kaynakları:</strong> Mülkiyet hakları BAÜN’ ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                <p><strong>BAÜN Bilişim Kaynakları Kullanıcıları:</strong> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                <p><strong>BAÜN Kullanıcıları:</strong> BAÜN’ nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN’ de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır. Bu kullanıcılar, BAÜN Bilişim Kaynaklarını doğrudan kullanım hakkına sahiptir.</p>
                <p><strong>Kapsamdışı Kullanıcılar:</strong> BAÜN Bilişim Kaynaklarını, BAÜN Kullanıcıları ve Özel Kullanıcılar başlığı altında tanımlandığı biçimiyle kullanım hakkına sahip olmayan, sadece genel kullanıma açık kaynak ya da servisleri (Örneğin; BAÜN web sayfaları, BAÜN Elektronik Liste Servisi, ftp servisi vb.) kullanan kişi ve kuruluşlar Kapsamdışı Kullanıcılar olarak tanımlanır.</p>

                <h4>2. Kullanım</h4>
                <p><strong>Temel Kullanım:</strong> BAÜN Bilişim Kaynaklarının, Üniversitenin eğitim, öğretim, araştırma, geliştirme, toplumsal hizmet ve idari/yönetimsel faaliyetleri ile doğrudan ilişkili olan kullanımı “Temel Kullanım” olarak tanımlanır.</p>
                <p><strong>İkincil (tali) Kullanım:</strong> Temel Kullanım tanımı dışında kalan her türlü kullanım, “İkincil (tali) Kullanım” olarak tanımlanır. Kaynakların, ancak Temel Kullanım kapsamında ihtiyaç duyulmayan atıl kapasitesinin bu amaç için kullanılabilmesi söz konusudur. İkincil Kullanım, Temel Kullanımı kısıtlayıcı/engelleyici boyutlara ulaştığında “genel ilkelere aykırı kullanım” kapsamına girer.</p>

                <h4>3. Genel İlkeler</h4>
                <ol>
                    <li>BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynaklarını kullanıma sunan birimler; Kullanıcı bilgilerinin gizliliğini, mahremiyetini korumalı, kaynakların adil olarak paylaştırılmasını sağlamalı, kaynağa yönelik tehditleri en aza indirebilmek için risk düzeylerine göre güvenlik önlemlerini almalı, kritik olma düzeyine göre kaynakları yedeklemeli, güvenliği ilgilendiren durumlarda kanıt özelliği taşıyabilecek bilgileri, kaynakları kullananların kimliğinin tespit edilmesini sağlayacak düzende tutmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları kullanıcıları, Temel Kullanım kapsamında kullanımlarına tahsis edilen mülkiyetin, kendilerine ait olan kaynakların güvenliği ile ilgili kişisel önlemlerini almalı, bu kaynaklar üzerinde yer alan bilgileri, kritik olma düzeyine göre yedeklemelidir.</li>
                    <li>BAÜN Bilişim Kaynakları, BAÜN yönetiminin yetkilendirdiği makamlarca belirlenmiş kurallar ve yönergeler çerçevesinde, yetkinin veriliş amacını aşmayacak şekilde ve yapılacak her iş için uygun yetkilendirme ile kullanılmalı, yetki almadan değiştirilmemeli, ortadan kaldırılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, bu kaynaklar kullanılarak oluşturulan ve bu kaynaklar üzerinde barındırılan/kullanılan her türlü kaynağın (yazılım, donanım, ağ kaynağı, ...) kullanım kurallarına ve koşullarına (izin, kaynak gösterim koşulu, telif hakkı, lisans koşulları, ağ kullanım kuralları, vb.) uyularak kullanılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Kullanım hakkını, doğrudan ya da dolaylı olarak devretmek ya da kiralamak amacıyla, ticari nitelik taşıyan ve gelir teminine yönelik kullanımlar için, Rektörlük makamından izin alınmadan kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; BAÜN içi bilgi kaynaklarını (duyuru, haber, doküman vb.), yetkisiz ve/veya izinsiz olarak 3. kişilere/kuruluşlara dağıtmak amacıyla, BAÜN’ ye ve 3. kişilere/kuruluşlara ait bilgilere ve kaynaklara izinsiz ve/veya yetkisiz erişim sağlamak amacıyla, diğer kullanıcıların kaynak kullanım hakkını engelleyici faaliyetlerde bulunmak amacıyla, kaynaklara zarar verici/kaynakların güvenliğini tehdit edici biçimde kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Genel ahlak ilkelerine aykırı materyal üretmek, barındırmak, iletmek, siyasi propaganda yapmak, rastgele ve alıcının istemi dışında mesaj (SPAM iletiler) göndermek amacıyla kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, Üniversite yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve bunlara bağlı olan yönetmeliklere aykırı faaliyetlerde bulunmak amacıyla kullanılamaz.</li>
                    <li>“Yasal Sorumluluk Reddi (Disclaimer)” metinleri, Genel İlkelere aykırı kullanımların kabul edilebilir olduğunu göstermez.</li>
                </ol>

                <h4>4. Yetki ve Sorumluluklar</h4>
                <p>Üniversite, temel amaçları doğrultusunda, BAÜN Bilişim Kaynaklarını kullanıcılarına sunar, bu hizmetlerin çalışırlığını ve sürekliliğini sağlar.</p>
                <p>BAÜN Bilişim Kaynakları kullanıcıları, BAÜN sunucuları üzerinde kendilerine tahsis edilen “Kullanıcı Adı/Şifre” ikilisi ve/veya IP (Internet Protokol) adresi kullanılarak gerçekleştirdikleri her türlü etkinlikten, BAÜN Bilişim Kaynaklarını kullanarak oluşturdukları ve/veya kendilerine tahsis edilen BAÜN Bilişim Kaynağı üzerinde bulundurdukları her türlü kaynağın (belge, doküman, yazılım, vb.) içeriğinden, kaynağın kullanımı hakkında yetkili makamlar tarafından talep edilen bilgilerin doğru ve eksiksiz verilmesinden, yedeklerinin tutulmasından, ilgili kaynağın kullanım kurallarına, Üniversite Yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve yasal mevzuata karşı birebir kendileri sorumludur.</p>
                <p>BAÜN yönetimi, BAÜN Kullanıcıları ve Özel Kullanıcılar ile üçüncü kişi veya kuruluşlar arasında doğabilecek her türlü ihtilaf durumunda doğrudan taraf olma hakkını saklı tutar.</p>
                <p>BAÜN Rektörlüğü ve/veya yetkilendirdiği birimler, BAÜN Bilişim Kaynakları kullanımı hakkında genel-geçer kuralları belirleyip, bu kuralları gelişen teknolojinin öngördüğü biçimde sürekli olarak değerlendirir ve gerekli değişiklikleri hayata geçirir. Bu tür değişiklikler yapıldığında genel duyuru mekanizmaları ile kullanıcılar bilgilendirilir.</p>

                <h4>5. Uygulama ve Yaptırımlar</h4>
                <p>BAÜN makamları, BAÜN Bilişim Kaynaklarının “Genel İlkelere” aykırı etkinlikler dâhilinde kullanılması durumunda, gerçekleştirilen eylemin yoğunluğuna, kaynaklara veya kişi/kurumlara verilen zararın boyutuna ve tekrarına aşağıdaki işlemlerin bir ya da birden fazla maddesini, sıra ile ya da sırasız uygulayabilir;</p>
                <ul>
                    <li>Kullanıcı sözlü ve/veya yazılı olarak uyarılır</li>
                    <li>Kullanıcıya tahsis edilmiş BAÜN Bilişim Kaynakları sınırlı veya sınırsız süre ile kapatılabilir</li>
                    <li>Üniversite bünyesindeki akademik/idari soruşturma mekanizmaları harekete geçirilebilir</li>
                    <li>Adli yargı mekanizmaları harekete geçirilebilir.</li>
                </ul>
                <p>Kullanım ve Kullanıcı tanımlarının yetersiz kaldığı ya da “BAÜN Bilişim Kaynakları Kullanım Politikası” belgesi dâhilinde tanımlı olmayan durumlar BAÜN makamlarınca değerlendirilir.</p>
            </div>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>
    <!-- KDYS.FR.0078 - Bilgi İşlem DB Kurumsal Statik IP Sözleşmesi -->
    <div id="form_0078.php" class="gizli-form">
        <h2>KDYS.FR.0078 - Bilgi İşlem DB Kurumsal Statik IP Sözleşmesi</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KURUMSAL STATİK IP TALEP BİLGİLERİ
            </div>
 
            <div class="form-grup">
                <label>Birim Adı *</label>
                <input type="text" placeholder="Balıkesir Üniversitesi - ..." required>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>Sorumlu Personelin Adı-Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>Telefonu *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-grup">
                <label>E-posta (IP bilgileri bu adrese gönderilecektir) *</label>
                <input type="email" placeholder="ornek@balikesir.edu.tr" required>
            </div>
 
            <div class="form-grup">
                <label>Kullanım Amacı *</label>
                <select required>
                    <option value="">-- Seçiniz --</option>
                    <option value="Fakülte/YO Adına">Fakülte/YO Adına</option>
                    <option value="Bölüm/Birim Adına">Bölüm/Birim Adına</option>
                    <option value="Topluluk Adına">Topluluk Adına</option>
                    <option value="Konferans/Kongre/Sempozyum">Konferans/Kongre/Sempozyum</option>
                    <option value="Proje Grubu">Proje Grubu</option>
                    <option value="Diğer">Diğer</option>
                </select>
            </div>
            <div class="form-grup">
                <label>Açıklama</label>
                <textarea rows="2" placeholder="Kullanım amacınızı detaylandırınız..."></textarea>
            </div>
 
            <!-- Taahhüt Metni -->
            <div class="resmi-yazi">
                Birimimiz adına kullanılmak üzere, bir adet statik ip'nin tarafımıza tahsis edilmesini talep ediyoruz. Kullanacağımız tüm bilgisayar, sunucu ve cihazlar birimimiz tarafından temin edilecektir. Bu statik ip'nin erişim sağlayıcı (gateway) olarak kullanılmayacağını, ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve <a href="http://bid.balikesir.edu.tr" target="_blank" style="color: #1b656e; font-weight: bold;">http://bid.balikesir.edu.tr</a> adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
            </div>
 
            <div class="form-grup">
                <label>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı - Onay Tarihi *</label>
                <input type="date" required>
            </div>
 
            <!-- Açılır/Kapanır Bilişim Politikası Paneli -->
            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BAÜN Bilişim Kaynakları Kullanım Politikası</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <h4>1. Tanımlamalar</h4>
                <p><strong>BAÜN Bilişim Kaynakları:</strong> Mülkiyet hakları BAÜN' ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                <p><strong>BAÜN Bilişim Kaynakları Kullanıcıları:</strong> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                <p><strong>BAÜN Kullanıcıları:</strong> BAÜN' nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN' de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır. Bu kullanıcılar, BAÜN Bilişim Kaynaklarını doğrudan kullanım hakkına sahiptir.</p>
                <p><strong>Kapsamdışı Kullanıcılar:</strong> BAÜN Bilişim Kaynaklarını, BAÜN Kullanıcıları ve Özel Kullanıcılar başlığı altında tanımlandığı biçimiyle kullanım hakkına sahip olmayan, sadece genel kullanıma açık kaynak ya da servisleri (Örneğin; BAÜN web sayfaları, BAÜN Elektronik Liste Servisi, ftp servisi vb.) kullanan kişi ve kuruluşlar Kapsamdışı Kullanıcılar olarak tanımlanır.</p>
 
                <h4>2. Kullanım</h4>
                <p><strong>Temel Kullanım:</strong> BAÜN Bilişim Kaynaklarının, Üniversitenin eğitim, öğretim, araştırma, geliştirme, toplumsal hizmet ve idari/yönetimsel faaliyetleri ile doğrudan ilişkili olan kullanımı “Temel Kullanım” olarak tanımlanır.</p>
                <p><strong>İkincil (tali) Kullanım:</strong> Temel Kullanım tanımı dışında kalan her türlü kullanım, “İkincil (tali) Kullanım” olarak tanımlanır. Kaynakların, ancak Temel Kullanım kapsamında ihtiyaç duyulmayan atıl kapasitesinin bu amaç için kullanılabilmesi söz konusudur. İkincil Kullanım, Temel Kullanımı kısıtlayıcı/engelleyici boyutlara ulaştığında “genel ilkelere aykırı kullanım” kapsamına girer.</p>
 
                <h4>3. Genel İlkeler</h4>
                <ol>
                    <li>BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynaklarını kullanıma sunan birimler; Kullanıcı bilgilerinin gizliliğini, mahremiyetini korumalı, kaynakların adil olarak paylaştırılmasını sağlamalı, kaynağa yönelik tehditleri en aza indirebilmek için risk düzeylerine göre güvenlik önlemlerini almalı, kritik olma düzeyine göre kaynakları yedeklemeli, güvenliği ilgilendiren durumlarda kanıt özelliği taşıyabilecek bilgileri, kaynakları kullananların kimliğinin tespit edilmesini sağlayacak düzende tutmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları kullanıcıları, Temel Kullanım kapsamında kullanımlarına tahsis edilen mülkiyetin, kendilerine ait olan kaynakların güvenliği ile ilgili kişisel önlemlerini almalı, bu kaynaklar üzerinde yer alan bilgileri, kritik olma düzeyine göre yedeklemelidir.</li>
                    <li>BAÜN Bilişim Kaynakları, BAÜN yönetiminin yetkilendirdiği makamlarca belirlenmiş kurallar ve yönergeler çerçevesinde, yetkinin veriliş amacını aşmayacak şekilde ve yapılacak her iş için uygun yetkilendirme ile kullanılmalı, yetki almadan değiştirilmemeli, ortadan kaldırılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, bu kaynaklar kullanılarak oluşturulan ve bu kaynaklar üzerinde barındırılan/kullanılan her türlü kaynağın (yazılım, donanım, ağ kaynağı, ...) kullanım kurallarına ve koşullarına (izin, kaynak gösterim koşulu, telif hakkı, lisans koşulları, ağ kullanım kuralları, vb.) uyularak kullanılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Kullanım hakkını, doğrudan ya da dolaylı olarak devretmek ya da kiralamak amacıyla, ticari nitelik taşıyan ve gelir teminine yönelik kullanımlar için, Rektörlük makamından izin alınmadan kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; BAÜN içi bilgi kaynaklarını (duyuru, haber, doküman vb.), yetkisiz ve/veya izinsiz olarak 3. kişilere/kuruluşlara dağıtmak amacıyla, BAÜN’ ye ve 3. kişilere/kuruluşlara ait bilgilere ve kaynaklara izinsiz ve/veya yetkisiz erişim sağlamak amacıyla, diğer kullanıcıların kaynak kullanım hakkını engelleyici faaliyetlerde bulunmak amacıyla, kaynaklara zarar verici/kaynakların güvenliğini tehdit edici biçimde kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Genel ahlak ilkelerine aykırı materyal üretmek, barındırmak, iletmek, siyasi propaganda yapmak, rastgele ve alıcının istemi dışında mesaj (SPAM iletiler) göndermek amacıyla kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, Üniversite yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve bunlara bağlı olan yönetmeliklere aykırı faaliyetlerde bulunmak amacıyla kullanılamaz.</li>
                    <li>“Yasal Sorumluluk Reddi (Disclaimer)” metinleri, Genel İlkelere aykırı kullanımların kabul edilebilir olduğunu göstermez.</li>
                </ol>
 
                <h4>4. Yetki ve Sorumluluklar</h4>
                <p>Üniversite, temel amaçları doğrultusunda, BAÜN Bilişim Kaynaklarını kullanıcılarına sunar, bu hizmetlerin çalışırlığını ve sürekliliğini sağlar.</p>
                <p>BAÜN Bilişim Kaynakları kullanıcıları, BAÜN sunucuları üzerinde kendilerine tahsis edilen “Kullanıcı Adı/Şifre” ikilisi ve/veya IP (Internet Protokol) adresi kullanılarak gerçekleştirdikleri her türlü etkinlikten, BAÜN Bilişim Kaynaklarını kullanarak oluşturdukları ve/veya kendilerine tahsis edilen BAÜN Bilişim Kaynağı üzerinde bulundurdukları her türlü kaynağın (belge, doküman, yazılım, vb.) içeriğinden, kaynağın kullanımı hakkında yetkili makamlar tarafından talep edilen bilgilerin doğru ve eksiksiz verilmesinden, yedeklerinin tutulmasından, ilgili kaynağın kullanım kurallarına, Üniversite Yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve yasal mevzuata karşı birebir kendileri sorumludur.</p>
                <p>BAÜN yönetimi, BAÜN Kullanıcıları ve Özel Kullanıcılar ile üçüncü kişi veya kuruluşlar arasında doğabilecek her türlü ihtilaf durumunda doğrudan taraf olma hakkını saklı tutar.</p>
                <p>BAÜN Rektörlüğü ve/veya yetkilendirdiği birimler, BAÜN Bilişim Kaynakları kullanımı hakkında genel-geçer kuralları belirleyip, bu kuralları gelişen teknolojinin öngördüğü biçimde sürekli olarak değerlendirir ve gerekli değişiklikleri hayata geçirir. Bu tür değişiklikler yapıldığında genel duyuru mekanizmaları ile kullanıcılar bilgilendirilir.</p>
 
                <h4>5. Uygulama ve Yaptırımlar</h4>
                <p>BAÜN makamları, BAÜN Bilişim Kaynaklarının “Genel İlkelere” aykırı etkinlikler dâhilinde kullanılması durumunda, gerçekleştirilen eylemin yoğunluğuna, kaynaklara veya kişi/kurumlara verilen zararın boyutuna ve tekrarına aşağıdaki işlemlerin bir ya da birden fazla maddesini, sıra ile ya da sırasız uygulayabilir;</p>
                <ul>
                    <li>Kullanıcı sözlü ve/veya yazılı olarak uyarılır</li>
                    <li>Kullanıcıya tahsis edilmiş BAÜN Bilişim Kaynakları sınırlı veya sınırsız süre ile kapatılabilir</li>
                    <li>Üniversite bünyesindeki akademik/idari soruşturma mekanizmaları harekete geçirilebilir</li>
                    <li>Adli yargı mekanizmaları harekete geçirilebilir.</li>
                </ul>
                <p>Kullanım ve Kullanıcı tanımlarının yetersiz kaldığı ya da “BAÜN Bilişim Kaynakları Kullanım Politikası” belgesi dâhilinde tanımlı olmayan durumlar BAÜN makamlarınca değerlendirilir.</p>
            </div>
 
    
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>
 
    <!-- KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi -->
    <div id="form_0079.php" class="gizli-form">
        <h2>KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KURUMSAL WEB ADI VE ALANI TALEP BİLGİLERİ
            </div>
 
            <div class="form-grup">
                <label>Birim Adı *</label>
                <input type="text" value="BALIKESİR ÜNİVERSİTESİ" required>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>Sorumlu Personelin Adı-Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>Telefonu *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>E-posta (Hesap bilgileri bu adrese gönderilecektir) *</label>
                    <input type="email" placeholder="ornek@balikesir.edu.tr" required>
                </div>
                <div class="form-grup">
                    <label>Talep Edilen Web Adı *</label>
                    <input type="text" placeholder="birimadi.balikesir.edu.tr" required>
                </div>
            </div>
 
            <div class="form-grup">
                <label>Kullanım Amacı *</label>
                <select required>
                    <option value="">-- Seçiniz --</option>
                    <option value="Fakülte/YO Adına">Fakülte/YO Adına</option>
                    <option value="Birim/Bölüm Adına">Birim/Bölüm Adına</option>
                    <option value="Topluluk Adına">Topluluk Adına</option>
                    <option value="Konferans/Kongre/Sempozyum">Konferans/Kongre/Sempozyum</option>
                    <option value="Proje Grubu">Proje Grubu</option>
                    <option value="Diğer">Diğer</option>
                </select>
            </div>
            <div class="form-grup">
                <label>Açıklama</label>
                <textarea rows="2" placeholder="Kullanım amacınızı detaylandırınız..."></textarea>
            </div>
 
            <!-- Taahhüt Metni -->
            <div class="resmi-yazi">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen alan adının açılması, 250 MB web ve 100 MB veri tabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak yukarıda adı belirtilen personele teslim edilmesini talep ediyoruz. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, eki Web Kullanıcıları Servis Politikası ve <a href="http://bid.balikesir.edu.tr" target="_blank" style="color: #1b656e; font-weight: bold;">http://bid.balikesir.edu.tr</a> adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
            </div>
 
            <div class="form-grup">
                <label>Birim Yöneticisi / Proje Sorumlusu / Düzenleme Kurulu Başkanı - Onay Tarihi *</label>
                <input type="date" required>
            </div>
 
            <!-- Açılır/Kapanır Bilişim Politikası Paneli -->
            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BAÜN Bilişim Kaynakları Kullanım Politikası</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <h4>1. Tanımlamalar</h4>
                <p><strong>BAÜN Bilişim Kaynakları:</strong> Mülkiyet hakları BAÜN' ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                <p><strong>BAÜN Bilişim Kaynakları Kullanıcıları:</strong> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                <p><strong>BAÜN Kullanıcıları:</strong> BAÜN' nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN' de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır. Bu kullanıcılar, BAÜN Bilişim Kaynaklarını doğrudan kullanım hakkına sahiptir.</p>
                <p><strong>Kapsamdışı Kullanıcılar:</strong> BAÜN Bilişim Kaynaklarını, BAÜN Kullanıcıları ve Özel Kullanıcılar başlığı altında tanımlandığı biçimiyle kullanım hakkına sahip olmayan, sadece genel kullanıma açık kaynak ya da servisleri (Örneğin; BAÜN web sayfaları, BAÜN Elektronik Liste Servisi, ftp servisi vb.) kullanan kişi ve kuruluşlar Kapsamdışı Kullanıcılar olarak tanımlanır.</p>
 
                <h4>2. Kullanım</h4>
                <p><strong>Temel Kullanım:</strong> BAÜN Bilişim Kaynaklarının, Üniversitenin eğitim, öğretim, araştırma, geliştirme, toplumsal hizmet ve idari/yönetimsel faaliyetleri ile doğrudan ilişkili olan kullanımı “Temel Kullanım” olarak tanımlanır.</p>
                <p><strong>İkincil (tali) Kullanım:</strong> Temel Kullanım tanımı dışında kalan her türlü kullanım, “İkincil (tali) Kullanım” olarak tanımlanır. Kaynakların, ancak Temel Kullanım kapsamında ihtiyaç duyulmayan atıl kapasitesinin bu amaç için kullanılabilmesi söz konusudur. İkincil Kullanım, Temel Kullanımı kısıtlayıcı/engelleyici boyutlara ulaştığında “genel ilkelere aykırı kullanım” kapsamına girer.</p>
 
                <h4>3. Genel İlkeler</h4>
                <ol>
                    <li>BAÜN Bilişim Kaynakları, Temel Kullanım kapsamındaki ihtiyaçlar için hizmete sunulmaktadır. Bu kaynakların israfından kaçınılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynaklarını kullanıma sunan birimler; Kullanıcı bilgilerinin gizliliğini, mahremiyetini korumalı, kaynakların adil olarak paylaştırılmasını sağlamalı, kaynağa yönelik tehditleri en aza indirebilmek için risk düzeylerine göre güvenlik önlemlerini almalı, kritik olma düzeyine göre kaynakları yedeklemeli, güvenliği ilgilendiren durumlarda kanıt özelliği taşıyabilecek bilgileri, kaynakları kullananların kimliğinin tespit edilmesini sağlayacak düzende tutmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları kullanıcıları, Temel Kullanım kapsamında kullanımlarına tahsis edilen mülkiyetin, kendilerine ait olan kaynakların güvenliği ile ilgili kişisel önlemlerini almalı, bu kaynaklar üzerinde yer alan bilgileri, kritik olma düzeyine göre yedeklemelidir.</li>
                    <li>BAÜN Bilişim Kaynakları, BAÜN yönetiminin yetkilendirdiği makamlarca belirlenmiş kurallar ve yönergeler çerçevesinde, yetkinin veriliş amacını aşmayacak şekilde ve yapılacak her iş için uygun yetkilendirme ile kullanılmalı, yetki almadan değiştirilmemeli, ortadan kaldırılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, bu kaynaklar kullanılarak oluşturulan ve bu kaynaklar üzerinde barındırılan/kullanılan her türlü kaynağın (yazılım, donanım, ağ kaynağı, ...) kullanım kurallarına ve koşullarına (izin, kaynak gösterim koşulu, telif hakkı, lisans koşulları, ağ kullanım kuralları, vb.) uyularak kullanılmalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Kullanım hakkını, doğrudan ya da dolaylı olarak devretmek ya da kiralamak amacıyla, ticari nitelik taşıyan ve gelir teminine yönelik kullanımlar için, Rektörlük makamından izin alınmadan kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; BAÜN içi bilgi kaynaklarını (duyuru, haber, doküman vb.), yetkisiz ve/veya izinsiz olarak 3. kişilere/kuruluşlara dağıtmak amacıyla, BAÜN’ ye ve 3. kişilere/kuruluşlara ait bilgilere ve kaynaklara izinsiz ve/veya yetkisiz erişim sağlamak amacıyla, diğer kullanıcıların kaynak kullanım hakkını engelleyici faaliyetlerde bulunmak amacıyla, kaynaklara zarar verici/kaynakların güvenliğini tehdit edici biçimde kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları; Genel ahlak ilkelerine aykırı materyal üretmek, barındırmak, iletmek, siyasi propaganda yapmak, rastgele ve alıcının istemi dışında mesaj (SPAM iletiler) göndermek amacıyla kullanılmamalıdır.</li>
                    <li>BAÜN Bilişim Kaynakları, Üniversite yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve bunlara bağlı olan yönetmeliklere aykırı faaliyetlerde bulunmak amacıyla kullanılamaz.</li>
                    <li>“Yasal Sorumluluk Reddi (Disclaimer)” metinleri, Genel İlkelere aykırı kullanımların kabul edilebilir olduğunu göstermez.</li>
                </ol>
 
                <h4>4. Yetki ve Sorumluluklar</h4>
                <p>Üniversite, temel amaçları doğrultusunda, BAÜN Bilişim Kaynaklarını kullanıcılarına sunar, bu hizmetlerin çalışırlığını ve sürekliliğini sağlar.</p>
                <p>BAÜN Bilişim Kaynakları kullanıcıları, BAÜN sunucuları üzerinde kendilerine tahsis edilen “Kullanıcı Adı/Şifre” ikilisi ve/veya IP (Internet Protokol) adresi kullanılarak gerçekleştirdikleri her türlü etkinlikten, BAÜN Bilişim Kaynaklarını kullanarak oluşturdukları ve/veya kendilerine tahsis edilen BAÜN Bilişim Kaynağı üzerinde bulundurdukları her türlü kaynağın (belge, doküman, yazılım, vb.) içeriğinden, kaynağın kullanımı hakkında yetkili makamlar tarafından talep edilen bilgilerin doğru ve eksiksiz verilmesinden, yedeklerinin tutulmasından, ilgili kaynağın kullanım kurallarına, Üniversite Yönetmeliklerine, Türkiye Cumhuriyeti yasalarına ve yasal mevzuata karşı birebir kendileri sorumludur.</p>
                <p>BAÜN yönetimi, BAÜN Kullanıcıları ve Özel Kullanıcılar ile üçüncü kişi veya kuruluşlar arasında doğabilecek her türlü ihtilaf durumunda doğrudan taraf olma hakkını saklı tutar.</p>
                <p>BAÜN Rektörlüğü ve/veya yetkilendirdiği birimler, BAÜN Bilişim Kaynakları kullanımı hakkında genel-geçer kuralları belirleyip, bu kuralları gelişen teknolojinin öngördüğü biçimde sürekli olarak değerlendirir ve gerekli değişiklikleri hayata geçirir. Bu tür değişiklikler yapıldığında genel duyuru mekanizmaları ile kullanıcılar bilgilendirilir.</p>
 
                <h4>5. Uygulama ve Yaptırımlar</h4>
                <p>BAÜN makamları, BAÜN Bilişim Kaynaklarının “Genel İlkelere” aykırı etkinlikler dâhilinde kullanılması durumunda, gerçekleştirilen eylemin yoğunluğuna, kaynaklara veya kişi/kurumlara verilen zararın boyutuna ve tekrarına aşağıdaki işlemlerin bir ya da birden fazla maddesini, sıra ile ya da sırasız uygulayabilir;</p>
                <ul>
                    <li>Kullanıcı sözlü ve/veya yazılı olarak uyarılır</li>
                    <li>Kullanıcıya tahsis edilmiş BAÜN Bilişim Kaynakları sınırlı veya sınırsız süre ile kapatılabilir</li>
                    <li>Üniversite bünyesindeki akademik/idari soruşturma mekanizmaları harekete geçirilebilir</li>
                    <li>Adli yargı mekanizmaları harekete geçirilebilir.</li>
                </ul>
                <p>Kullanım ve Kullanıcı tanımlarının yetersiz kaldığı ya da “BAÜN Bilişim Kaynakları Kullanım Politikası” belgesi dâhilinde tanımlı olmayan durumlar BAÜN makamlarınca değerlendirilir.</p>
            </div>
 
            <div class="form-bilgi-liste" style="background: #f9f9f9; border-left-color: #1b656e; color: #333;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: bold; cursor: pointer;">
                    <input type="checkbox" required> Yukarıdaki bilgilerin doğruluğunu ve BAÜN Bilişim Kaynakları Kullanım Politikasını kabul ediyorum.
                </label>
            </div>
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>
 
    <!-- KDYS.FR.0080 - Bilgi İşlem DB Mernis Taahhütnamesi -->
    <div id="form_0080.php" class="gizli-form">
        <h2>KDYS.FR.0080 - Bilgi İşlem DB Mernis Taahhütnamesi</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KİMLİK PAYLAŞIM SİSTEMİ (KPS) KULLANICI TAAHHÜTNAMESİ<br>
                <span style="font-weight: normal; font-style: italic; font-size: 12px;">- Gizlilik Taahhüt Belgesi -</span>
            </div>
 
            <div class="resmi-yazi">
                <strong>AÇIKLAMA:</strong> 10/07/2005 tarih ve 25871 sayılı Resmi Gazete'de yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında "ikili anlaşma" imzalanmıştır. İlgili Yönetmeliğe ilişkin usul ve esaslar içerisinde yer alan "Özel Hayatın Gizliliği" ve "Kişisel Verilerin Korunması" hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız için aşağıdaki taahhütname hazırlanmıştır.
            </div>
 
            <div class="resmi-yazi" style="font-weight: bold;">
                TAAHHÜTNAME: Anayasamızın 20. maddesinde "Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz." denilmektedir. Bu kapsamda KPS'den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı, aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.
            </div>
 
            <div class="form-grup">
                <label>Taahhüt Tarihi *</label>
                <input type="date" required>
            </div>
 
            <h3 style="color:#1b656e; font-size:15px; border-bottom:1px solid #eee; padding-bottom:5px; margin-top:25px;">Personel Bilgisi</h3>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicili, Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>E-posta *</label>
                    <input type="email" required>
                </div>
            </div>
            <div class="form-grup">
                <label>Birim *</label>
                <input type="text" required>
            </div>
 
            <h3 style="color:#1b656e; font-size:15px; border-bottom:1px solid #eee; padding-bottom:5px; margin-top:25px;">Birim Yetkilisi</h3>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicili, Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>E-posta *</label>
                    <input type="email" required>
                </div>
            </div>
            <div class="form-grup">
                <label>Birim *</label>
                <input type="text" required>
            </div>
 
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Taahhütnameyi Gönder</button>
        </form>
    </div>
 
    <!-- KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu -->
    <div id="form_0082.php" class="gizli-form">
        <h2>KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu</h2>
        <form>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Başvuru Tarihi *</label>
                    <input type="date" required>
                </div>
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. No - Kurum Sicil No *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Fakülte/Yüksekokul *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı - Bölümü/Birimi *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Ev / Cep Telefonu *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <div class="form-satir">
                <div class="form-grup">
                    <label>Diğer E-posta *</label>
                    <input type="email" required>
                </div>
                <div class="form-grup">
                    <label>Bölüm Başkanının Adı Soyadı (Onay) *</label>
                    <input type="text" required>
                </div>
            </div>
 
            <!-- Açılır/Kapanır Kullanım Kuralları Paneli -->
            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BALIKESİR ÜNİVERSİTESİ Elektronik Posta (e-mail) Adresi Kullanım Kuralları</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <h4>1. Kanuni Yükümlülük</h4>
                <ol>
                    <li>@balikesir.edu.tr domaini T.C. Balıkesir Üniversitesi personeline (akademik ve idari) hizmet vermektedir. Bu hizmet akademik eğitim-öğretim amaçlı araştırma ve geliştirme faaliyetleri içermektedir.</li>
                    <li>@balikesir.edu.tr domainine ait e-posta hesaplarını kullanan şahıslar; Türkiye Cumhuriyeti kanun ve bunlara bağlı yönetmeliklere, TÜBİTAK-ULAKBİM tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esaslara ve T.C. Balıkesir Üniversitesi yönetmeliklerine aykırı hareket edemezler. İlgili mevzuat: 5651 sayılı Kanun (23.05.2007), İnternet Ortamında Yapılan Yayınların Düzenlenmesine Dair Usul ve Esaslar Hakkında Yönetmelik (30.11.2007), Birlikte Çalışabilirlik Esasları Rehberi (2005/20 sayılı Genelge) ve ULAKBİM Ulusal Akademik Ağ Kullanım Politikası.</li>
                </ol>
 
                <h4>2. Gizlilik ve Güvenlik</h4>
                <ol>
                    <li>Personel e-posta adresi talep eden şahısların bu formu doldurup imzalayarak, personel kimlikleri ile birlikte Bilgi İşlem Dairesi Başkanlığına şahsen müracaat etmeleri gerekmektedir. Diğer talepler değerlendirmeye alınmayacaktır.</li>
                    <li>Balıkesir Üniversitesi'nden e-posta adresi alan kişi, Bilgi İşlem Daire Başkanlığı'nın belirleyeceği bir e-posta hesap adı ve kendisinin belirleyeceği bir kullanıcı şifresine sahip olur.</li>
                    <li>Kullanıcı adı ve e-posta adresi kişiye özeldir ve @balikesir.edu.tr domaininde bir benzeri daha yoktur.</li>
                    <li>Kullanıcı şifresi sadece kullanıcı tarafından bilinir. Kullanıcı dilediği zaman şifresini değiştirebilir. Şifrenin seçimi ve korunması tamamıyla kullanıcının sorumluluğundadır. Bilgi İşlem Daire Başkanlığı, şifre kullanımından doğacak problemlerden sorumlu değildir.</li>
                    <li>E-posta şifresini unutan kullanıcılar, Balıkesir Üniversitesi Bilgi İşlem Daire Başkanlığına bizzat müracaat etmek zorundadır.</li>
                </ol>
 
                <h4>3. E-posta Adresi Alan Kişinin Yükümlülükleri</h4>
                <ol>
                    <li>E-posta hesabı sahibi, bu servisi kullanırken ileri sürdüğü şahsi fikir, düşünce ve ifadeler ile eklediği dosya ve/veya bilgilerin sorumluluğunun şahsına ait olduğunu kabul eder.</li>
                    <li>Balıkesir Üniversitesi'ni adli duruma getirecek herhangi bir yazılım veya materyal bulunduramayacağını, paylaşamayacağını ve hukuki bir durum doğarsa tüm sorumlulukları üstlendiğini kabul eder.</li>
                    <li>E-posta servisinin kullanımı sırasında kaybolacak, eksik alınacak veya yanlış adrese iletilecek bilgi, mesaj ve dosyalardan Bilgi İşlem Dairesi Başkanlığı'nın sorumlu olmayacağını kabul eder.</li>
                    <li>Teknik nedenlerden (arıza, güncelleme, aktarma vb.) dolayı oluşacak gecikme ve kayıplardan Bilgi İşlem Dairesi Başkanlığı'nın sorumlu olmayacağını kabul eder.</li>
                    <li>Posta hesabındaki verilerin, Üniversitenin ihmali olmaksızın yetkisiz kişilerce okunmasından doğacak zararlardan Bilgi İşlem Dairesi Başkanlığı'nın sorumlu olmadığını kabul eder.</li>
                    <li>Başka şahıs veya kuruluşlardaki bilgisayara zarar verecek bilgi veya program göndermemeyi ve barındırmamayı, aksi halde doğacak hukuki ve cezai sorumluluğun şahsına ait olduğunu kabul eder.</li>
                    <li>Üniversite e-posta servisini kullanarak elde edilen bilgi/materyalin kullanıcının rızası dahilinde olduğunu, doğacak zararlardan Üniversitenin sorumlu olmadığını kabul eder.</li>
                    <li>Genel ahlaka aykırı, ırkçı, ayrımcı, ticari, siyasi propaganda, taciz ve tehdit edici içerikli e-posta göndermemeyi ve bundan doğacak sorumluluğun şahsına ait olduğunu kabul eder.</li>
                    <li>Kanunlara göre postalanması yasak veya gizli bilgileri postalamamayı ve dağıtmamayı kabul eder.</li>
                    <li>Zincir posta (chain mail) ve yazılım virüsü gibi postaları dağıtmamayı ve barındırmamayı kabul eder.</li>
                    <li>Rastgele ve alıcının istemi dışında mesaj (spam) göndermeyeceğini kabul eder.</li>
                    <li>Kullanıcı adıyla yapacağı her türlü işlemden bizzat kendisinin sorumlu olduğunu kabul eder.</li>
                    <li>Kullanım haklarını doğrudan ya da dolaylı olarak 3. şahıslara devredemeyeceğini ve kiralayamayacağını kabul eder.</li>
                    <li>Yasa ve kurallara aykırı davrandığı takdirde, Bilgi İşlem Daire Başkanlığı'nın gerekli müdahalede bulunma, hizmet dışına çıkarma ve üyeliğe son verme hakkına sahip olduğunu kabul eder.</li>
                    <li>Yasa ve kurallara aykırı davrandığı takdirde Üniversite makamlarının sözlü/yazılı uyarı, hizmet dışına çıkarma, idari soruşturma veya adli yargıya bildirim hakkına sahip olduğunu kabul eder.</li>
                    <li>Hesabını tek taraflı iptal ettirse dahi, iptalden önceki icraatlardan kendisinin sorumlu olacağını kabul eder.</li>
                    <li>Kayıt formunda yer alan bilgilerin doğruluğundan sorumlu olduğunu, hatalı/noksan bilgi halinde hesabın iptal edileceğini kabul eder.</li>
                    <li>E-posta hesabının 3 ay süreyle kullanılmaması halinde hesap içeriğinin silineceğini; üniversiteden ayrılma (emeklilik, tayin, istifa vb.) durumunda 30 gün içinde hesabın iptal edileceğini kabul eder.</li>
                    <li>Bilgi İşlem Dairesi Başkanlığı'nın, kullanıcıların bilgisi olmaksızın e-posta hizmetleri servis politikasında değişiklik yapma hakkına sahip olduğunu, değişiklikleri takip etmekle yükümlü olduğunu kabul eder.</li>
                    <li>ULAKBİM'in yasal ve teknolojik gelişmeler doğrultusunda Kullanım Politikası'nı değiştirebileceğini, değişikliklerin yürürlüğe gireceğini bilir ve kabul eder.</li>
                    <li>Tüm bu maddeleri okuduğunu, itiraz hakkı olmaksızın kabul ve taahhüt ettiğini beyan eder.</li>
                </ol>
 
                <h4>4. Yürürlük</h4>
                <p>Kullanıcı, adına düzenlenmiş bu formu doldurup imzaladıktan sonra bu sözleşme yürürlüğe girer ve T.C. Balıkesir Üniversitesi personeli olduğu sürece devam eder.</p>
            </div>
 
            
 
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Başvuruyu Gönder</button>
        </form>
    </div>
 
    <!-- KDYS.FR.0087 - Bilgi İşlem UAM Mernis Taahhütnamesi -->
    <div id="form_0087.php" class="gizli-form">
        <h2>KDYS.FR.0087 - Bilgi İşlem UAM Mernis Taahhütnamesi</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KİMLİK PAYLAŞIM SİSTEMİ (KPS) KULLANICI TAAHHÜTNAMESİ<br>
                <span style="font-weight: normal; font-style: italic; font-size: 12px;">- Gizlilik Taahhüt Belgesi -</span>
            </div>
 
            <div class="resmi-yazi">
                <strong>AÇIKLAMA:</strong> 10/07/2005 tarih ve 25871 sayılı Resmi Gazete'de yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında "ikili anlaşma" imzalanmıştır. İlgili Yönetmeliğe ilişkin usul ve esaslar içerisinde yer alan "Özel Hayatın Gizliliği" ve "Kişisel Verilerin Korunması" hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız (UAM personeli dahil) için aşağıdaki taahhütname hazırlanmıştır.
            </div>
 
            <div class="resmi-yazi" style="font-weight: bold;">
                TAAHHÜTNAME: Anayasamızın 20. maddesinde "Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz." denilmektedir. Bu kapsamda KPS'den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı, aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.
            </div>
 
            <div class="form-grup">
                <label>Taahhüt Tarihi *</label>
                <input type="date" required>
            </div>
 
            <h3 style="color:#1b656e; font-size:15px; border-bottom:1px solid #eee; padding-bottom:5px; margin-top:25px;">Personel Bilgisi</h3>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicili, Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>E-posta *</label>
                    <input type="email" required>
                </div>
            </div>
            <div class="form-grup">
                <label>Birim *</label>
                <input type="text" placeholder="Uzaktan Ağ ve Merkez (UAM) ..." required>
            </div>
 
            <h3 style="color:#1b656e; font-size:15px; border-bottom:1px solid #eee; padding-bottom:5px; margin-top:25px;">Birim Yetkilisi</h3>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" required>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicili, Unvanı *</label>
                    <input type="text" required>
                </div>
            </div>
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>E-posta *</label>
                    <input type="email" required>
                </div>
            </div>
            <div class="form-grup">
                <label>Birim *</label>
                <input type="text" required>
            </div>
 
            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Taahhütnameyi Gönder</button>
        </form>
    </div>

    <!-- JavaScript Kodları -->
    <script>
        function formYonetlendir() {
            var secilenForm = document.getElementById("formSecici").value;
            var hataMesaji = document.getElementById("hata-mesaji");
            var tumFormlar = document.querySelectorAll(".gizli-form");
            
            tumFormlar.forEach(function(form) { form.style.display = "none"; });
            
            if (secilenForm === "") {
                hataMesaji.style.display = "block";
            } else {
                hataMesaji.style.display = "none";
                var acilacakForm = document.getElementById(secilenForm);
                if (acilacakForm) {
                    acilacakForm.style.display = "block";
                } else {
                    alert("Seçtiğiniz form (" + secilenForm + ") henüz hazırlanmaktadır.");
                }
            }
        }

        // KDYS.FR.0074 Formu İçin Satır Ekleme/Silme İşlemleri
        function yeniSatirEkle() {
            var tabloBody = document.querySelector("#eImzaTablosu tbody");
            var yeniSatir = document.createElement("tr");
            yeniSatir.innerHTML = `
                <td class="sn-hucre"></td>
                <td><input type="text" maxlength="11"></td>
                <td><input type="date"></td>
                <td><input type="text"></td>
                <td><input type="text"></td>
                <td><input type="text"></td>
                <td><input type="text"></td>
                <td><input type="text"></td>
                <td><input type="text"></td>
                <td>
                    <select>
                        <option value="İlk Sertifika">İlk Sertifika</option>
                        <option value="Yenileme">Yenileme</option>
                    </select>
                </td>
                <td><input type="text"></td>
                <td><input type="text"></td>
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