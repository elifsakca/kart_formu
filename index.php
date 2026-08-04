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
        
        .form-bilgi-liste { font-size: 11.5px; color: #d93025; background: #fce8e6; padding: 10px; border-radius: 5px; border-left: 3px solid #d93025; margin-bottom: 15px; }
        .resmi-yazi { font-size: 14px; color: #333; text-align: justify; line-height: 1.6; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin-bottom: 20px; }
        
        .form-satir { display: flex; gap: 15px; }
        .form-satir .form-grup { flex: 1; }

        /* Tablo Stil Ayarları */
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
                <span>📜 BAÜN Bilişim Kaynakları Kullanım Politikası</span>
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