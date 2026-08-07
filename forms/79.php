<?php
// forms/79.php - KDYS.FR.0079 (Kurumsal Web Adı ve Alanı Sözleşmesi)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi -->
    <div id="form_0079.php" class="gizli-form">
        <h2>KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0079">
            <input type="hidden" name="form_adi" value="Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi">

            <div class="resmi-yazi" style="text-align: center; font-weight: bold; background-color: #e8f4f8; color: #1b656e;">
                KURUMSAL WEB ADI VE ALANI TALEP BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı *</label>
                    <input type="text" name="birim_adi" placeholder="BALIKESİR ÜNİVERSİTESİ - ..." required>
                </div>
                <div class="form-grup">
                    <label>Sorumlu Personelin Adı-Soyadı *</label>
                    <input type="text" name="personel_ad_soyad" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı *</label>
                    <input type="text" name="unvan" required>
                </div>
                <div class="form-grup">
                    <label>T.C Kimlik Numarası *</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu *</label>
                    <input type="text" name="telefon" required>
                </div>
                <div class="form-grup">
                    <label>E-posta (Hesap bilgileri gönderilecektir) *</label>
                    <input type="email" name="eposta" placeholder="ornek@balikesir.edu.tr" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen Web Adı *</label>
                <input type="text" name="web_adi" placeholder="birimadi.baun.edu.tr" required>
            </div>

            <div class="form-grup">
                <label>Kullanım Amacı *</label>
                <select name="kullanim_amaci" required>
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
                <textarea name="aciklama" rows="2" placeholder="Kullanım amacınızı detaylandırınız..."></textarea>
            </div>

            <div class="resmi-yazi">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen alan adının açılması, 250 MB web ve 100 MB veri tabanı (istenirse) kotalı alanın tahsis edilmesini talep ediyoruz.
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>

<?php elseif ($mode === 'print'): ?>
    <div style="font-family: Arial, sans-serif; color: #000;">
        <table class="grid-table">
            <tr>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="logo.png" alt="BAÜN Logo" style="width: 75px; height: auto;">
                </td>
                <td style="width: 55%; text-align: center; vertical-align: middle;">
                    <h3 style="margin: 0; font-size: 13px; font-weight: bold;">T.C.<br>BALIKESİR ÜNİVERSİTESİ REKTÖRLÜĞÜ</h3>
                    <h4 style="margin: 5px 0 0 0; font-size: 11px; font-weight: bold;">BİLGİ İŞLEM DAİRE BAŞKANLIĞI</h4>
                </td>
                <td style="width: 25%; font-size: 9.5px; padding: 4px;">
                    <b>Doküman No:</b> KDYS.FR.0079<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/4
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            KURUMSAL WEB ADI VE ALANI SÖZLEŞMESİ FORMU
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 30%;">Birim Adı</th>
                <td><?php echo htmlspecialchars($veriler['birim_adi'] ?? $veriler['birim'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Sorumlu Personelin Adı-Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['personel_ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>Unvanı</th>
                <td><?php echo htmlspecialchars($veriler['unvan'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>T.C Kimlik Numarası</th>
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

        <!-- PAGE 1 BOTTOM: TAAHHÜT KUTUSU -->
        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen alan adının açılması, 250 MB web ve 100 MB veri tabanı (istenirse) kotalı alanın tahsis edilmesini ve bu alanların kullanımı için gerekli web kullanıcısının açılarak teslim edilmesini talep ediyoruz.
            
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#1b656e; font-weight:bold;">
                <span>✓ Çevrimiçi Dolduruldu ve Onaylandı</span>
                <span>Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
