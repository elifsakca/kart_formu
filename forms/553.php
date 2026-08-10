<?php
// forms/553.php - KDYS.FR.0553 (Akıllı Kart İşlem Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0553 FORMU (PERSONEL İŞLEMLERİ) -->
    <div id="553.php" class="gizli-form">
        <h2>Akıllı Kart İşlem Formu (KDYS.FR.0553)</h2>
        <form method="POST" action="islem.php" enctype="multipart/form-data">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0553">
            <input type="hidden" name="form_adi" value="Akıllı Kart İşlem Formu (KDYS.FR.0553)">

            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı, Soyadı</label>
                    <input type="text" name="ad_soyad" required>
                    <span class="form-bilgi">Bu kısmın tüm kart tipleri için doldurulması zorunludur.</span>
                </div>
                <div class="form-grup">
                    <label>TC Kimlik No</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                    <span class="form-bilgi">Bu kısmın tüm kart tipleri için doldurulması zorunludur.</span>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup"><label>Fakülte/YO/MYO/Birim</label><input type="text" name="fakulte_birim"></div>
                <div class="form-grup"><label>İrtibat Telefonu</label><input type="text" name="telefon"></div>
            </div>

            <div class="form-grup">
                <label>Kart (Kişi) Tipi</label>
                <select name="kart_tipi" required>
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
                <select name="islem_turu" id="f52_islem_turu" onchange="islemTuruGuncelle(this)" required>
                    <option value="">Seçiniz...</option>
                    <option value="Akıllı kartın ilk kez verilmesi">Akıllı kartın ilk kez verilmesi</option>
                    <option>Hatalı Basılan Kart Bilgisinin Düzeltilmesi</option>
                    <option>Bilgi Değişikliği</option>
                    <option>Ayrılış</option>
                </select>
            </div>

            <div class="form-grup">
                <label>Açıklama</label>
                <textarea name="aciklama" rows="3"></textarea>
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
                    <b>Doküman No:</b> KDYS.FR.0553<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            AKILLI KART İŞLEM FORMU
        </h3>

        <table class="grid-table">
            <tr>
                <th style="width: 35%;">Adı Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
            </tr>
            <tr>
                <th>T.C. Kimlik Numarası</th>
                <td><?php echo htmlspecialchars($veriler['tc_no'] ?? $basvuru['tc_no']); ?></td>
            </tr>
            <tr>
                <th>Fakülte/YO/MYO/Birim</th>
                <td><?php echo htmlspecialchars($veriler['fakulte_birim'] ?? $basvuru['birim']); ?></td>
            </tr>
            <tr>
                <th>İrtibat Telefonu</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Kart (Kişi) Tipi</th>
                <td><?php echo htmlspecialchars($veriler['kart_tipi'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Yapılacak İşlem Türü</th>
                <td><?php echo htmlspecialchars($veriler['islem_turu'] ?? '-'); ?></td>
            </tr>
            <?php if (!empty($veriler['aciklama'])): ?>
                <tr>
                    <th>Açıklama</th>
                    <td><?php echo htmlspecialchars($veriler['aciklama']); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
<?php endif; ?>
