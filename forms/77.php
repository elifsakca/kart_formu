<?php
// forms/77.php - KDYS.FR.0077 (Kişisel Web Adı ve Alanı Sözleşmesi)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi -->
    <div id="77.php" class="gizli-form">
        <h2>KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0077">
            <input type="hidden" name="form_adi" value="Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi">

            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KİŞİSEL WEB ADI VE ALANI TALEP BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı *</label>
                    <input type="text" name="birim_adi" placeholder="BALIKESİR ÜNİVERSİTESİ - ..." required>
                </div>
                <div class="form-grup">
                    <label>Personelin Adı-Soyadı *</label>
                    <input type="text" name="personel_ad_soyad" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı *</label>
                    <input type="text" name="unvan" required>
                </div>
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası *</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu *</label>
                    <input type="text" name="telefon" required>
                </div>
                <div class="form-grup">
                    <label>E-posta Adresi (Hesap bilgileri bu adrese gönderilecektir) *</label>
                    <input type="email" name="eposta" placeholder="ornek@balikesir.edu.tr" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen Web Adı *</label>
                <input type="text" name="web_adi" placeholder="kullaniciadi.baun.edu.tr" required>
            </div>

            <div class="form-grup">
                <label>Kullanım Amacı *</label>
                <textarea name="kullanim_amaci" rows="3" placeholder="Web alanının kullanım amacını detaylıca açıklayınız..." required></textarea>
            </div>

            <div class="resmi-yazi">
                Akademik/İdari çalışmalarımda kullanmak üzere, sistemde yukarıda belirtilen alan adının açılması, 150 MB web ve 20 MB veritabanı (istenirse) kotalı alanın tahsis edilmesi ve bu alanların kullanımı için gerekli web kullanıcısının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca bu sayfanın altında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası ve <a href="http://bid.balikesir.edu.tr" target="_blank" style="color: #1b656e; font-weight: bold;">http://bid.balikesir.edu.tr</a> adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaları okuduğumu ve bunlara uygun hareket edeceğimi taahhüt ederim.
            </div>

            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BAÜN Bilişim Kaynakları Kullanım Politikası</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <div style="padding-top: 15px; padding-bottom: 15px; font-size:12px; line-height:1.6; color:#333;">
                    <p style="margin-bottom:8px;"><strong>1. Tanımlamalar</strong></p>
                    <p style="margin:3px 0;"><strong>BAÜN Bilişim Kaynakları:</strong> Mülkiyet hakları BAÜN’ye ait olan, BAÜN tarafından lisanslanan/kiralanan ya da BAÜN tarafından kullanım hakkına sahip olunan her türlü bilgisayar/bilgisayar ağı, donanım, yazılım ve servislerini ifade eder.</p>
                    <p style="margin:3px 0;"><strong>BAÜN Bilişim Kaynakları Kullanıcıları:</strong> BAÜN Bilişim Kaynaklarını kullanmak üzere, bu kaynaklar üzerinde gerekli yetkilendirme tanımları yapılarak belirlenen özel ve tüzel kişilerdir.</p>
                    <p style="margin:3px 0;"><strong>BAÜN Kullanıcıları:</strong> BAÜN’nün idari yapısı içinde yer alan birimlerde akademik ve idari görevlerde bulunan kadrolu/geçici personel ile BAÜN’de öğrenim hayatını sürdürmekte olan tüm lisans ve lisansüstü öğrenciler “BAÜN Kullanıcıları” olarak tanımlanır.</p>
                    
                    <p style="margin-top:12px; margin-bottom:8px;"><strong>2. Kullanım</strong></p>
                    <p style="margin:3px 0;"><strong>Temel Kullanım:</strong> Üniversitenin eğitim, öğretim, araştırma ve idari faaliyetleri ile doğrudan ilişkili olan kullanımı kapsar.</p>

                    <p style="margin-top:12px; margin-bottom:8px;"><strong>3. Genel İlkeler & Yaptırımlar</strong></p>
                    <p style="margin:3px 0;">BAÜN Bilişim Kaynakları genel ahlak ilkelerine aykırı materyal üretmek, ticari veya siyasi propaganda yapmak amacıyla kullanılamaz.</p>
                </div>
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>

<?php elseif ($mode === 'print'): ?>
    <div style="font-family: Arial, sans-serif; color: #000;">
        <table class="grid-table">
            <tr>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" alt="BAÜN Logo" style="width: 75px; height: auto;">
                </td>
                <td style="width: 55%; text-align: center; vertical-align: middle;">
                    <h3 style="margin: 0; font-size: 13px; font-weight: bold;">T.C.<br>BALIKESİR ÜNİVERSİTESİ REKTÖRLÜĞÜ</h3>
                    <h4 style="margin: 5px 0 0 0; font-size: 11px; font-weight: bold;">BİLGİ İŞLEM DAİRE BAŞKANLIĞI</h4>
                </td>
                <td style="width: 25%; font-size: 9.5px; padding: 4px;">
                    <b>Doküman No:</b> KDYS.FR.0077<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/4
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            KİŞİSEL WEB ADI VE ALANI SÖZLEŞMESİ FORMU
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 30%;">Birim Adı</th>
                <td><?php echo htmlspecialchars($veriler['birim_adi'] ?? $veriler['birim'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Personelin Adı-Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['personel_ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>Unvanı</th>
                <td><?php echo htmlspecialchars($veriler['unvan'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>T.C. Kimlik Numarası</th>
                <td><?php echo htmlspecialchars($veriler['tc_no'] ?? $basvuru['tc_no']); ?></td>
            </tr>
            <tr>
                <th>Telefonu</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>E-posta Adresi</th>
                <td><?php echo htmlspecialchars($veriler['eposta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Talep Edilen Web Adı</th>
                <td><b><?php echo htmlspecialchars($veriler['web_adi'] ?? '-'); ?></b></td>
            </tr>
            <tr>
                <th>Kullanım Amacı</th>
                <td><?php echo htmlspecialchars($veriler['kullanim_amaci'] ?? '-'); ?></td>
            </tr>
        </table>

        <!-- PAGE 1 BOTTOM: PERSONEL TAAHHÜT KUTUSU -->
        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Akademik/İdari çalışmalarımda kullanılmak üzere, sitemizde yukarıda belirtilen alan adının, 150 MB web ve 20 MB veritabanı (otomasyon) kuralı alan olarak tahsis edilmesini ve bu alanların kullanımına izin verilerek web alanının açılarak erişim bilgilerinin tarafıma teslim edilmesini talep ediyorum. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, ve http://bid.balikesir.edu.tr adresinde bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikalara okunduğu ve bunlara uygun hareket edileceğini taahhüt ederim.
            
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#1b656e; font-weight:bold;">
                <span> Başvuran Personel Tarafından Çevrimiçi Dolduruldu ve Onaylandı</span>
                <span>Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
