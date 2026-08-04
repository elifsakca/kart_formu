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
        
        /* Önemli Uyarı ve Checkbox Sınıfları */
        .form-bilgi { font-size: 11.5px; color: #d93025; margin-top: 4px; display: block; font-weight: 500; }
        .form-bilgi-liste { font-size: 11.5px; color: #d93025; background: #fce8e6; padding: 10px; border-radius: 5px; border-left: 3px solid #d93025; margin-bottom: 15px; }
        .resmi-yazi { font-size: 14px; color: #333; text-align: justify; line-height: 1.6; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin-bottom: 20px; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; font-size: 13px; color: #333; }
        .checkbox-grid label { font-weight: normal !important; display: flex; align-items: center; gap: 8px; cursor: pointer; color: #333 !important; }
        
        .form-satir { display: flex; gap: 15px; }
        .form-satir .form-grup { flex: 1; }

        /* Tablo Stil Ayarları */
        .form-tablosu-wrapper { overflow-x: auto; margin-bottom: 20px; }
        .form-tablosu { width: 100%; border-collapse: collapse; font-size: 12px; }
        .form-tablosu th, .form-tablosu td { border: 1px solid #ddd; padding: 8px 5px; text-align: center; }
        .form-tablosu th { background-color: #1b656e; color: white; font-weight: 600; white-space: nowrap; }
        .form-tablosu td input, .form-tablosu td select { width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; font-size: 11px; }

        /* Form 0071 Teknik Detay Tablosu Stilleri */
        .teknik-tablo { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; font-size: 13px; }
        .teknik-tablo td, .teknik-tablo th { border: 1px solid #333; padding: 8px; vertical-align: middle; }
        .teknik-tablo .baslik-gri { background-color: #e0e0e0; font-weight: bold; color: #000; width: 180px; }
        .teknik-tablo input[type="text"], .teknik-tablo textarea { width: 100%; border: 1px solid #ccc; padding: 6px; box-sizing: border-box; border-radius: 3px; }
        .teknik-tablo .secenek-kutusu { text-align: center; width: 110px; background-color: #fff; }
        .teknik-tablo .secenek-kutusu label { font-weight: bold; font-size: 12px; display: block; margin-bottom: 4px; }
        .teknik-tablo .imza-baslik { background-color: #e0e0e0; font-weight: bold; text-align: center; }
        .teknik-tablo .imza-alani { text-align: center; padding: 15px 8px; vertical-align: top; }
        .teknik-tablo .imza-alani input { width: 90%; margin-bottom: 8px; text-align: center; }
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

    <!-- =========================================
         GİZLİ FORMLAR
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

            <!-- GÖRSELDEKİ TEKNİK DEĞERLENDİRME VE TESLİM BÖLÜMÜ -->
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
                        <input type="text" placeholder="Ad Soyad / Tarih"><br>
                        <span style="color:#aaa; font-size:11px;">İmza</span>
                    </td>
                    <td class="imza-alani">
                        <strong>Teslim Eden Personel</strong><br><br>
                        <input type="text" placeholder="Ad Soyad / Tarih"><br>
                        <span style="color:#aaa; font-size:11px;">İmza</span>
                    </td>
                    <td class="imza-alani">
                        <strong>Teslim Alan Personel</strong><br><br>
                        <input type="text" placeholder="Ad Soyad / Tarih"><br>
                        <span style="color:#aaa; font-size:11px;">İmza</span>
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

            <div class="resmi-yazi" style="font-size: 12.5px;">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyoruz. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
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

            <div class="form-satir">
                <div class="form-grup">
                    <label>Görevi / Unvanı</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>Teslim Onayı</label>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; margin-top: 10px;">
                        <input type="checkbox" required> Teslim aldığımı onaylıyorum.
                    </label>
                </div>
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu -->
    <div id="form_0074.php" class="gizli-form">
        <h2>KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold;">
                E-İMZA BAŞVURU SAHİBİ / PERSONEL BİLGİ LİSTESİ
            </div>

            <div class="form-tablosu-wrapper">
                <table class="form-tablosu">
                    <thead>
                        <tr>
                            <th>S.N.</th>
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
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Satır -->
                        <tr>
                            <td>1</td>
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
                        </tr>
                        <!-- 2. Satır -->
                        <tr>
                            <td>2</td>
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
                        </tr>
                        <!-- 3. Satır -->
                        <tr>
                            <td>3</td>
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
                        </tr>
                        <!-- 4. Satır -->
                        <tr>
                            <td>4</td>
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
                        </tr>
                        <!-- 5. Satır -->
                        <tr>
                            <td>5</td>
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
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-bilgi-liste" style="background: #f9f9f9; border-left-color: #1b656e; color: #333;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: bold; cursor: pointer;">
                    <input type="checkbox" required> Tablodaki bilgilerin doğruluğunu ve e-imza başvuru şartlarını onaylıyorum.
                </label>
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi -->
    <div id="form_0077.php" class="gizli-form">
        <h2>KDYS.FR.0077 - Kişisel Web Adı ve Alanı Talep Formu</h2>
        <form>
            <div class="resmi-yazi" style="text-align: center; font-weight: bold;">
                KİŞİSEL WEB ADI ve ALANI TALEP BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı</label>
                    <input type="text" value="BALIKESİR ÜNİVERSİTESİ-" required>
                </div>
                <div class="form-grup">
                    <label>Personelin Adı-Soyadı</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>T.C Kimlik Numarası</label>
                    <input type="text" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu</label>
                    <input type="text">
                </div>
                <div class="form-grup">
                    <label>E-posta (Hesap bilgileri gönderilecek)</label>
                    <input type="text" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen Web Adı</label>
                <input type="text" placeholder="örnek.baun.edu.tr" required>
            </div>

            <div class="form-grup">
                <label>Kullanım Amacı</label>
                <textarea rows="3" required></textarea>
            </div>

            <div class="resmi-yazi" style="font-size: 12.5px;">
                Akademik/İdari çalışmalarımda kullanmak üzere, sistemde yukarıda belirtilen alan adının açılması, 150 MB web ve 20 MB veritabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaları okuduğumu ve bunlara uygun hareket edeceğimi taahhüt ederim.
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Başvuru Tarihi</label>
                    <input type="date" required>
                </div>
                <div class="form-grup">
                    <label>Onay</label>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; margin-top: 10px;">
                        <input type="checkbox" required> Şartları ve politikaları kabul ediyorum.
                    </label>
                </div>
            </div>

            <button type="button" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
        </form>
    </div>

    <!-- Yönlendirme JS Kodu -->
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
                    alert("Seçtiğiniz form (" + secilenForm + ") henüz hazırlanmaktadır. Şimdilik F-52, F-53, F-54, F-55, KDYS.FR.0071, KDYS.FR.0072, KDYS.FR.0073, KDYS.FR.0074 ve KDYS.FR.0077 formları aktiftir.");
                }
            }
        }
    </script>

</body>
</html>