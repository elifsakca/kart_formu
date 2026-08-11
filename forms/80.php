<?php
// forms/80.php - KDYS.FR.0080 (Mernis / KPS Taahhütnamesi)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0080 - Bilgi İşlem DB Mernis / KPS Taahhütnamesi -->
    <div id="80.php" class="gizli-form">
        <h2>KDYS.FR.0080 - Bilgi İşlem DB Mernis / KPS Taahhütnamesi</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0080">
            <input type="hidden" name="form_adi" value="Bilgi İşlem DB Mernis / KPS Taahhütnamesi">

            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KİMLİK PAYLAŞIM SİSTEMİ (KPS) KULLANICI TAAHHÜTNAMESİ<br>
                <span style="font-weight: normal; font-style: italic; font-size: 12px;">- Gizlilik Taahhüt Belgesi -</span>
            </div>

            <div class="resmi-yazi" style="font-size: 12.5px; line-height: 1.6;">
                10/07/2005 tarih ve 25871 sayılı resmi gazetede yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında “ikili anlaşma” imzalanmıştır.<br><br>
                İlgili Yönetmeliğe ilişkin usul ve esasları içerisinde yer alan “Özel Hayatın Gizliliği” ve “Kişisel Verilerin Korunması” hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız için aşağıdaki taahhütname hazırlanmıştır.
            </div>

            <div class="resmi-yazi" style="font-weight: bold; font-size: 12.5px; line-height: 1.6; background-color: #fffde8; border-left: 4px solid #1b656e; padding: 12px;">
                TAAHHÜTNAME<br><br>
                Anayasamızın 20 nci maddesinde “Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz.” denilmektedir. Bu kapsamda KPS’den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.
            </div>

            <div class="form-grup">
                <label>Taahhüt Tarihi *</label>
                <input type="date" name="taahhut_tarihi" required>
            </div>

            <h3 style="color:#1b656e; font-size:15px; border-bottom:1px solid #eee; padding-bottom:5px; margin-top:25px;">Personel Bilgisi</h3>
            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" name="personel_ad_soyad" required>
                </div>
                <div class="form-grup">
                    <label>Kurum Sicili, Unvanı *</label>
                    <input type="text" name="personel_sicil_unvan" required>
                </div>
            </div>
            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" name="personel_tc_no" maxlength="11" required>
                </div>
                <div class="form-grup">
                    <label>E-posta *</label>
                    <input type="email" name="personel_eposta" required>
                </div>
            </div>
            <div class="form-grup">
                <label>Birim *</label>
                <input type="text" name="personel_birim" required>
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Taahhütnameyi Gönder</button>
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
                    <b>Doküman No:</b> KDYS.FR.0080<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            MERNİS / KPS KULLANICI TAAHHÜTNAMESİ
        </h3>

        <div style="margin-top:15px; font-size:12px; line-height:1.6; text-align:justify;">
            10/07/2005 tarih ve 25871 sayılı resmi gazetede yayımlanan T.C. Nüfus ve Vatandaşlık İşleri Genel Müdürlüğüne ait Kimlik Paylaşım Sistemi (KPS) Uygulama Yönetmeliği kapsamında Bakanlığımız ile ilgili iş ve işlem süreçlerindeki vatandaşlarımızın nüfus ve adres bilgilerinin paylaşımı hakkında “ikili anlaşma” imzalanmıştır.<br><br>
            İlgili Yönetmeliğe ilişkin usul ve esasları içerisinde yer alan “Özel Hayatın Gizliliği” ve “Kişisel Verilerin Korunması” hükümleriyle Balıkesir Üniversitesine ve görevli personele bazı sorumluluklar getirilmiştir. Bu sorumlulukların paylaşımı çerçevesinde iş süreçlerinde KPS üzerinden nüfus ve adres bilgilerine erişen çalışanlarımız için aşağıdaki taahhütname hazırlanmıştır.
        </div>

        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            <b>TAAHHÜTNAME</b><br><br>
            Anayasamızın 20 nci maddesinde “Herkes, özel hayatına ve aile hayatına saygı gösterilmesini isteme hakkına sahiptir. Özel hayatın ve aile hayatının gizliliğine dokunulamaz.” denilmektedir. Bu kapsamda KPS’den elde edilen tüm nüfus ve adres bilgilerini sadece T.C. Balıkesir Üniversitesi ve bağlı birimlerdeki iş süreçleri içerisinde kullanacağımı, kullanıcı parolamın güvenliğini sağlayacağımı aksi takdirde idari, hukuki ve mali sorumluluğun tarafıma ait olduğunu beyan ve taahhüt ederim.
            
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#1b656e; font-weight:bold;">
                <span> Personel Tarafından Çevrimiçi Dolduruldu ve Onaylandı</span>
                <span>Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
