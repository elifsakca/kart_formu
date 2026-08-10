<?php
// forms/74.php - KDYS.FR.0074 (Bilgi İşlem DB E-İmza Talep Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0074 - E-İmza Talep Formu -->
    <div id="74.php" class="gizli-form">
        <h2>KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0074">
            <input type="hidden" name="form_adi" value="Bilgi İşlem DB E-İmza Talep Formu">

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
                            <td><input type="text" name="eimza_tc[]" maxlength="11"></td>
                            <td><input type="date" name="eimza_dogum[]"></td>
                            <td><input type="text" name="eimza_ad[]"></td>
                            <td><input type="text" name="eimza_soyad[]"></td>
                            <td><input type="text" name="eimza_eposta[]"></td>
                            <td><input type="text" name="eimza_birim[]"></td>
                            <td><input type="text" name="eimza_gorev[]"></td>
                            <td><input type="text" name="eimza_telefon[]"></td>
                            <td>
                                <select name="eimza_basvuru_turu[]">
                                    <option value="İlk Sertifika">İlk Sertifika</option>
                                    <option value="Yenileme">Yenileme</option>
                                </select>
                            </td>
                            <td><input type="text" name="eimza_odeme[]"></td>
                            <td><input type="text" name="eimza_aciklama[]"></td>
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
                    <b>Doküman No:</b> KDYS.FR.0074<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Sayfa No:</b> 1/1
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            E-İMZA TALEP VE PERSONEL LİSTESİ FORMU
        </h3>

        <!-- E-İMZA TABLOSU GÖSTERİMİ -->
        <?php if (isset($veriler['eimza_tc']) && is_array($veriler['eimza_tc'])): ?>
            <table class="grid-table" style="font-size: 10px;">
                <thead>
                    <tr style="background:#f4f4f4;">
                        <th>S.N.</th>
                        <th>T.C. Kimlik No</th>
                        <th>Doğum Tarihi</th>
                        <th>Ad Soyad</th>
                        <th>E-Posta Adresi</th>
                        <th>Birim / Görev</th>
                        <th>Telefon</th>
                        <th>Başvuru Türü</th>
                        <th>Ödeme</th>
                        <th>Açıklama</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $tcler = $veriler['eimza_tc'];
                    for ($i = 0; $i < count($tcler); $i++): 
                        if (empty(trim($tcler[$i]))) continue;
                    ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($tcler[$i]); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_dogum'][$i] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($veriler['eimza_ad'][$i] ?? '') . ' ' . ($veriler['eimza_soyad'][$i] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_eposta'][$i] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($veriler['eimza_birim'][$i] ?? '') . ' / ' . ($veriler['eimza_gorev'][$i] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_telefon'][$i] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_basvuru_turu'][$i] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_odeme'][$i] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($veriler['eimza_aciklama'][$i] ?? ''); ?></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>
