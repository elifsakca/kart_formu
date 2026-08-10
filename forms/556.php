<?php
// forms/556.php - KDYS.FR.0556 (Akıllı Kart Öğrenci İşlem Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0556 FORMU (ÖĞRENCİ İŞLEMLERİ) -->
    <div id="556.php" class="gizli-form">
        <h2>Akıllı Kart Öğrenci İşlem Formu (KDYS.FR.0556)</h2>
        <form method="POST" action="islem.php" enctype="multipart/form-data">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0556">
            <input type="hidden" name="form_adi" value="Akıllı Kart Öğrenci İşlem Formu (KDYS.FR.0556)">

            <div class="form-satir">
                <div class="form-grup">
                    <label>Adı, Soyadı *</label>
                    <input type="text" name="ad_soyad" required>
                </div>
                <div class="form-grup">
                    <label>T.C. Kimlik No *</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Öğrenci Numarası *</label>
                    <input type="text" name="ogrenci_no" required>
                </div>
                <div class="form-grup">
                    <label>Fakülte / Yüksekokul / Enstitü *</label>
                    <input type="text" name="birim" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Bölüm / Program *</label>
                    <input type="text" name="bolum" required>
                </div>
                <div class="form-grup">
                    <label>İrtibat Telefonu *</label>
                    <input type="text" name="telefon" required>
                </div>
            </div>

            <div class="form-grup">
                <label>E-Posta Adresi *</label>
                <input type="email" name="eposta" required>
            </div>

            <div class="form-grup">
                <label>Yapılacak İşlem Türü *</label>
                <select name="islem_turu" required>
                    <option value="">Seçiniz...</option>
                    <option value="İlk Kez Kart Alma">İlk Kez Kart Alma (Yeni Kayıt)</option>
                    <option value="Kayıp/Çalıntı Kart Yenileme">Kayıp / Çalıntı Kart Yenileme</option>
                    <option value="Arızalı Kart Yenileme">Arızalı Kart Yenileme</option>
                    <option value="Bilgi Değişikliği / Düzeltme">Bilgi Değişikliği / Düzeltme</option>
                </select>
            </div>

            <div class="form-grup">
                <label>Vesikalık Fotoğraf (Opsiyonel)</label>
                <input type="file" name="fotograf" accept="image/*">
                <span class="form-bilgi">Jpg veya png formatında net çekilmiş biyometrik/vesikalık fotoğraf yükleyebilirsiniz.</span>
            </div>

            <div class="form-grup">
                <label>Açıklama / Ek Notlar</label>
                <textarea name="aciklama" rows="3" placeholder="Var ise eklemek istediğiniz notlar..."></textarea>
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center;">Başvuruyu Gönder</button>
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
                    <b>Doküman No:</b> KDYS.FR.0556<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            AKILLI KART ÖĞRENCİ İŞLEM FORMU
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
                <th>Öğrenci Numarası</th>
                <td><?php echo htmlspecialchars($veriler['ogrenci_no'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Fakülte / Birim</th>
                <td><?php echo htmlspecialchars($veriler['birim'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Bölüm</th>
                <td><?php echo htmlspecialchars($veriler['bolum'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>İrtibat Telefonu</th>
                <td><?php echo htmlspecialchars($veriler['telefon'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>E-Posta Adresi</th>
                <td><?php echo htmlspecialchars($veriler['eposta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>İşlem Türü</th>
                <td><?php echo htmlspecialchars($veriler['islem_turu'] ?? '-'); ?></td>
            </tr>
        </table>
    </div>
<?php endif; ?>
