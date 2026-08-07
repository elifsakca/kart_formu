<?php
// forms/73.php - KDYS.FR.0073 (E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0073 - Bilgi İşlem DB E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı -->
    <div id="form_0073.php" class="gizli-form">
        <h2>KDYS.FR.0073 - E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0073">
            <input type="hidden" name="form_adi" value="E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı">

            <div class="resmi-yazi" style="text-align: center; font-weight: bold;">
                ÜRÜNÜ ALAN KİŞİ BİLGİLERİ
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>AD, SOYAD</label>
                    <input type="text" name="ad_soyad" required>
                </div>
                <div class="form-grup">
                    <label>TC KİMLİK NO</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Fakülte/YO/MYO/Birim</label>
                    <input type="text" name="birim" required>
                </div>
                <div class="form-grup">
                    <label>İrtibat Telefonu</label>
                    <input type="text" name="telefon">
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Tarihi</label>
                <input type="date" name="talep_tarihi" required>
            </div>

            <div class="resmi-yazi">
                Yukarıda belirtilen tarihte talep etmiş olduğum e-imza mini kart okuyucuyu TÜBİTAK Bilişim ve Bilgi Güvenliği İleri Teknolojileri Araştırma Merkezi firmasından tarafımca teslim aldığımı beyan ederim.
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Formu Gönder</button>
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
                    <b>Doküman No:</b> KDYS.FR.0073<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            E-İMZA MİNİ KART OKUYUCU TESLİM TESELLÜM TUTANAĞI
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 30%;">AD, SOYAD</th>
                <td><?php echo htmlspecialchars($veriler['ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>TC KİMLİK NO</th>
                <td><?php echo htmlspecialchars($veriler['tc_no'] ?? $basvuru['tc_no']); ?></td>
            </tr>
            <tr>
                <th>Birim</th>
                <td><?php echo htmlspecialchars($veriler['birim'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Telefon</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Talep Tarihi</th>
                <td><?php echo htmlspecialchars($veriler['talep_tarihi'] ?? '-'); ?></td>
            </tr>
        </table>

        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Yukarıda belirtilen tarihte talep etmiş olduğum e-imza mini kart okuyucuyu TÜBİTAK Bilişim ve Bilgi Güvenliği İleri Teknolojileri Araştırma Merkezi firmasından tarafımca teslim aldığımı beyan ve taahhüt ederim.
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; font-size:11.5px; color:#1b656e; font-weight:bold;">
                ✓ Çevrimiçi Dolduruldu ve Beyan Edildi - Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
