<?php
// forms/554.php - KDYS.FR.0554 (Arızalı Akıllı Kart Müracaat Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0554 FORMU (ARIZALI KART) -->
    <div id="554.php" class="gizli-form">
        <h2>Arızalı Akıllı Kart Müracaat Formu (KDYS.FR.0554)</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0554">
            <input type="hidden" name="form_adi" value="Arızalı Akıllı Kart Müracaat Formu (KDYS.FR.0554)">

            <div class="resmi-yazi">
                Eski kimlik kartımın AKS sisteminden iptal edilmesi ve akıllı kart merkezince yapılan teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            </div>

            <div class="form-grup"><label>Görev Yapılan / Öğrenim Görülen Yer</label><input type="text" name="gorev_ogrenim_yeri" required></div>
            
            <div class="form-satir">
                <div class="form-grup"><label>TC Kimlik Numarası</label><input type="text" name="tc_no" maxlength="11" required></div>
                <div class="form-grup"><label>Ad Soyad (Adıma Kayıtlı Olan)</label><input type="text" name="ad_soyad" required></div>
            </div>
            
            <div class="form-satir">
                <div class="form-grup"><label>Kart Seri No</label><input type="text" name="kart_seri_no"></div>
                <div class="form-grup"><label>Arızalanma Tarihi</label><input type="date" name="ariza_tarihi"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text" name="telefon"></div>
            </div>
            
            <div class="form-bilgi" style="margin-bottom:15px; font-size:12px;">
                <b>Uyarı:</b> Arızalı kart bu form ile birlikte Bilgi işlem Dairesi Başkanlığına gönderilecektir. BAUN akıllı merkezine gönderilmeyen veya getirilmeyen, hatalı basılan veya değişecek kartlar ile ilgili herhangi bir işlem yapılmayacaktır.
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center;">Formu Gönder</button>
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
                    <b>Doküman No:</b> KDYS.FR.0554<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            ARIZALI AKILLI KART MÜRACAAT FORMU
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 35%;">Görev Yapılan / Öğrenim Görülen Yer</th>
                <td><?php echo htmlspecialchars($veriler['gorev_ogrenim_yeri'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>T.C. Kimlik Numarası</th>
                <td><?php echo htmlspecialchars($veriler['tc_no'] ?? $basvuru['tc_no']); ?></td>
            </tr>
            <tr>
                <th>Adı Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>Kart Seri No</th>
                <td><?php echo htmlspecialchars($veriler['kart_seri_no'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Arızalanma Tarihi</th>
                <td><?php echo htmlspecialchars($veriler['ariza_tarihi'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>İrtibat Telefonu</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
        </table>

        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Eski kimlik kartımın AKS sisteminden iptal edilmesi ve akıllı kart merkezince yapılan teknik inceleme sonucunda, kart arızasının tarafımdan kaynakladığı takdirde bedeli karşılığında yeni akıllı kimlik kartımın tanzim edilerek tarafıma verilmesini rica ederim.
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; font-size:11.5px; color:#1b656e; font-weight:bold;">
                ✓ Çevrimiçi Başvuru Yapıldı - Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
