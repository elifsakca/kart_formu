<?php
// forms/82.php - KDYS.FR.0082 (Personel Elektronik Posta Başvuru Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu -->
    <div id="82.php" class="gizli-form">
        <h2>KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0082">
            <input type="hidden" name="form_adi" value="Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu">

            <div class="form-satir">
                <div class="form-grup">
                    <label>Başvuru Tarihi *</label>
                    <input type="date" name="basvuru_tarihi" required>
                </div>
                <div class="form-grup">
                    <label>Adı Soyadı *</label>
                    <input type="text" name="ad_soyad" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>T.C. No - Kurum Sicil No *</label>
                    <input type="text" name="tc_sicil_no" required>
                </div>
                <div class="form-grup">
                    <label>Fakülte/Yüksekokul *</label>
                    <input type="text" name="fakulte_yo" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı - Bölümü/Birimi *</label>
                    <input type="text" name="unvan_bolum" required>
                </div>
                <div class="form-grup">
                    <label>Ev / Cep Telefonu *</label>
                    <input type="text" name="telefon" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Diğer E-posta *</label>
                    <input type="email" name="diger_eposta" required>
                </div>
                <div class="form-grup">
                    <label>Bölüm Başkanının Adı Soyadı (Onay) *</label>
                    <input type="text" name="bolum_baskani_onay" required>
                </div>
            </div>

            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span> BALIKESİR ÜNİVERSİTESİ Elektronik Posta (e-mail) Adresi Kullanım Kuralları</span>
                <span style="font-size: 16px;">▼</span>
            </button>
            <div class="accordion-panel">
                <div style="padding-top: 15px; padding-bottom: 15px; font-size:12px; line-height:1.6; color:#333;">
                    <p style="margin-bottom:8px;"><strong>1- KANUNİ YÜKÜMLÜLÜK:</strong></p>
                    <p style="margin:3px 0;"><strong>1.1-</strong> @balikesir.edu.tr domain’i T.C. Balıkesir Üniversitesi personeline hizmet vermektedir.</p>
                    <p style="margin:3px 0;"><strong>1.2-</strong> E-posta hesaplarını kullananlar ilgili mevzuat ve yasalara uymakla yükümlüdür.</p>

                    <p style="margin-top:12px; margin-bottom:8px;"><strong>2- GİZLİLİK ve GÜVENLİK:</strong></p>
                    <p style="margin:3px 0;"><strong>2.1-</strong> Kullanıcı adı ve şifrenin gizliliği kullanıcının sorumluluğundadır.</p>
                </div>
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Başvuruyu Gönder</button>
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
                    <b>Doküman No:</b> KDYS.FR.0082<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/3
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            PERSONEL ELEKTRONİK POSTA (e-mail) ADRESİ BAŞVURU FORMU
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 30%;">Başvuru Tarihi</th>
                <td><?php echo htmlspecialchars($veriler['basvuru_tarihi'] ?? date('d.m.Y', strtotime($basvuru['kayit_tarihi']))); ?></td>
            </tr>
            <tr>
                <th>Adı Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>T.C. No - Kurum Sicil No</th>
                <td><?php echo htmlspecialchars($veriler['tc_sicil_no'] ?? $basvuru['tc_no']); ?></td>
            </tr>
            <tr>
                <th>Fakülte / Yüksekokul</th>
                <td><?php echo htmlspecialchars($veriler['fakulte_yo'] ?? $basvuru['birim']); ?></td>
            </tr>
            <tr>
                <th>Unvanı - Bölümü / Birimi</th>
                <td><?php echo htmlspecialchars($veriler['unvan_bolum'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Ev / Cep Telefonu</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Diğer E-posta</th>
                <td><?php echo htmlspecialchars($veriler['diger_eposta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Bölüm Başkanının Adı Soyadı Onayı</th>
                <td><?php echo htmlspecialchars($veriler['bolum_baskani_onay'] ?? '-'); ?></td>
            </tr>
        </table>

        <!-- PAGE 1 BOTTOM: TAAHHÜT KUTUSU -->
        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Şahsım adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyorum. Ayrıca T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve ilgili tüm yasal düzenlemeleri okuduğumu ve bunlara uygun hareket edeceğimi taahhüt ederim.
            
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#1b656e; font-weight:bold;">
                <span> Personel Tarafından Çevrimiçi Dolduruldu ve Onaylandı</span>
                <span>Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
