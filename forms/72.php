<?php
// forms/72.php - KDYS.FR.0072 (Kurumsal E-posta Hesabı Açma Başvuru Formu)
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- KDYS.FR.0072 - Kurumsal E-posta Hesabı Açma Başvuru Formu -->
    <div id="72.php" class="gizli-form">
        <h2>KDYS.FR.0072 - KURUMSAL E-POSTA HESABI AÇMA BAŞVURU FORMU</h2>
        <form method="POST" action="islem.php">
            <input type="hidden" name="form_kodu" value="KDYS.FR.0072">
            <input type="hidden" name="form_adi" value="Kurumsal E-posta Hesabı Açma Başvuru Formu">

            <div class="resmi-yazi" style="text-align: center; font-weight: bold; margin-bottom: 15px;">
                BALIKESİR ÜNİVERSİTESİ REKTÖRLÜĞÜ<br>Bilgi İşlem Daire Başkanlığına
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Birim Adı (BALIKESİR ÜNİVERSİTESİ - ...)</label>
                    <input type="text" name="birim" placeholder="Örn: Mühendislik Fakültesi" required>
                </div>
                <div class="form-grup">
                    <label>Sorumlu Personelin Adı Soyadı</label>
                    <input type="text" name="ad_soyad" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Unvanı</label>
                    <input type="text" name="unvan" required>
                </div>
                <div class="form-grup">
                    <label>T.C. Kimlik Numarası</label>
                    <input type="text" name="tc_no" maxlength="11" required>
                </div>
            </div>

            <div class="form-satir">
                <div class="form-grup">
                    <label>Telefonu</label>
                    <input type="text" name="telefon">
                </div>
                <div class="form-grup">
                    <label>E-posta adresi (Hesap bilgileri gönderilecek)</label>
                    <input type="text" name="eposta" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Talep Edilen E-posta Adresi</label>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <input type="text" name="talep_eposta" style="flex: 1;" placeholder="örnek" required>
                    <span>@balikesir.edu.tr</span>
                </div>
            </div>

            <div class="form-grup">
                <label>Kurumsal E-posta Kullanım Amacı</label>
                <div class="checkbox-grid">
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Fakülte/YO Adına"> Fakülte/YO Adına</label>
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Bölüm/Birim Adına"> Bölüm/Birim Adına</label>
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Topluluk/Dernek"> Topluluk/Dernek</label>
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Proje Grubu"> Proje Grubu</label>
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Konferans/Kongre/Sempozyum"> Konferans/Kongre/Sempozyum</label>
                    <label><input type="checkbox" name="kullanim_amaci[]" value="Diğer"> Diğer</label>
                </div>
                <textarea name="aciklama" rows="2" placeholder="Diğer veya ek açıklamalarınız..."></textarea>
            </div>

            <button type="button" class="accordion-btn" onclick="toggleAccordion(this)">
                <span>BALIKESİR ÜNİVERSİTESİ ELEKTRONİK POSTA (e-mail) ADRESİ KULLANIM KURALLARI</span>
                <span class="icon">▼</span>
            </button>
            <div class="accordion-panel">
                <div style="padding-top: 15px; padding-bottom: 15px; font-size:12px; line-height:1.6; color:#333;">
                    <p style="margin-bottom:8px;"><strong>1- KANUNİ YÜKÜMLÜLÜK:</strong></p>
                    <p style="margin:3px 0;"><strong>1.1-</strong> @balikesir.edu.tr domain’i T.C. Balıkesir Üniversitesi personeline (Akademik ve İdari) hizmet vermektedir. Bu hizmet akademik eğitim-öğretim amaçlı araştırma ve geliştirme faaliyetleri içermektedir.</p>
                    <p style="margin:3px 0;"><strong>1.2-</strong> @balikesir.edu.tr domain’ine ait e-posta hesaplarını kullanan şahıslar Türkiye Cumhuriyeti kanun ve bunlara bağlı olan yönetmeliklere, TÜBİTAK ULAKBİM tarafından işletilen Ulusal Akademik Ağ'ın (ULAKNET) kullanımına ilişkin usul ve esaslara, T.C. Balıkesir Üniversitesi yönetmeliklerine aykırı hareket edemezler.</p>
                    <p style="margin:3px 0 3px 15px;"><strong>1.2.1-</strong> İnternet Ortamında Yapılan Yayınların Düzenlenmesi ve Bu Yayınlar Yoluyla İşlenen Suçlarla Mücadele Edilmesi Hakkında Kanun. (Kanun/Karar No: 5651, Tarih: 23.05.2007)</p>
                    <p style="margin:3px 0 3px 15px;"><strong>1.2.2-</strong> İnternet Ortamında Yapılan Yayınların Düzenlenmesine Dair Usul ve Esaslar Hakkında Yönetmelik (Tarih: 30.11.2007)</p>
                    <p style="margin:3px 0 3px 15px;"><strong>1.2.3-</strong> Birlikte Çalışabilirlik Esasları Rehberi ile İlgili 2005/20 Sayılı Başbakanlık Genelgesi. (Tarih: 05.08.2005)</p>
                    <p style="margin:3px 0 3px 15px;"><strong>1.2.4-</strong> TÜBİTAK ULAKBİM Ulusal Akademik Ağ (ULAKNET) kullanım usul ve esasları.</p>
                    
                    <p style="margin-top:12px; margin-bottom:8px;"><strong>2- GİZLİLİK ve GÜVENLİK:</strong></p>
                    <p style="margin:3px 0;"><strong>2.1-</strong> T.C. Balıkesir Üniversitesinden personel e-posta adresi talep eden şahıslar, bu formu doldurup imzalayarak, personel kimlikleri ile birlikte Bilgi İşlem Dairesi Başkanlığına müracaat ederler.</p>
                    <p style="margin:3px 0;"><strong>2.2-</strong> Kullanıcı adı ve şifrenin seçimi ve korunması tamamıyla kullanıcının sorumluluğundadır.</p>

                    <p style="margin-top:12px; margin-bottom:8px;"><strong>3- E-POSTA ADRESİ ALAN KİŞİNİN YÜKÜMLÜLÜKLERİ:</strong></p>
                    <p style="margin:2px 0;"><strong>3.1-</strong> E-posta hesabı sahibi, bu servisi kullanırken ileri sürdüğü fikir ve ifadelerden şahsen sorumludur.</p>
                    <p style="margin:2px 0;"><strong>3.2-</strong> Genel ahlak ve yasalara aykırı materyal bulundurulamaz ve paylaşılamaz.</p>

                    <p style="margin-top:12px; margin-bottom:8px;"><strong>4- YÜRÜRLÜLÜK:</strong></p>
                    <p style="margin:3px 0;">Form doldurulup onaylandıktan sonra bu sözleşme yürürlüğe girer.</p>
                </div>
            </div>

            <div class="resmi-yazi" style="font-size: 12.5px;">
                Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyoruz. Ayrıca yukarıda bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikaların okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Talebi ve Sözleşmeyi Gönder</button>
        </form>
    </div>

<?php elseif ($mode === 'print'): ?>
    <!-- DETAY / RESMİ ÇIKTI ŞABLONU -->
    <div style="font-family: Arial, sans-serif; color: #000;">
        <!-- TABLO 1: BAŞLIK -->
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
                    <b>Doküman No:</b> KDYS.FR.0072<br>
                    <b>Yayın Tarihi:</b> 25.12.2023<br>
                    <b>Revizyon No:</b> 00<br>
                    <b>Rev. Tarihi:</b> -<br>
                    <b>Sayfa No:</b> 1/3
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            KURUMSAL ELEKTRONİK POSTA (e-mail) HESABI AÇMA BAŞVURU FORMU
        </h3>

        <!-- TABLO 2: KULLANICI BİLGİLERİ -->
        <table class="grid-table">
            <tr>
                <th style="width: 30%;">Birim Adı</th>
                <td>BALIKESİR ÜNİVERSİTESİ - <?php echo htmlspecialchars($veriler['birim'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Sorumlu Personelin Adı Soyadı</th>
                <td><?php echo htmlspecialchars($veriler['ad_soyad'] ?? $basvuru['ad_soyad']); ?></td>
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
                <th>E-posta adresi (Hesap gönderimi için)</th>
                <td><?php echo htmlspecialchars($veriler['eposta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Talep Edilen E-posta adresi</th>
                <td><b><?php echo htmlspecialchars($veriler['talep_eposta'] ?? '-'); ?>@balikesir.edu.tr</b></td>
            </tr>
            <tr>
                <th>Kurumsal E-posta Kullanım Amacı</th>
                <td>
                    <?php 
                    $amackutulari = ['Fakülte/YO Adına', 'Bölüm/Birim Adına', 'Topluluk/Dernek', 'Proje Grubu', 'Konferans/Kongre/Sempozyum', 'Diğer'];
                    $secilenler = is_array($veriler['kullanim_amaci'] ?? null) ? $veriler['kullanim_amaci'] : explode(',', $veriler['kullanim_amaci'] ?? '');
                    $secilenler = array_map('trim', $secilenler);
                    
                    foreach ($amackutulari as $ak) {
                        $is_checked = in_array($ak, $secilenler);
                        $box = $is_checked ? '☑' : '☐';
                        echo "<span style='margin-right: 12px; display: inline-block;'>{$box} {$ak}</span>";
                    }
                    ?>
                    <?php if (!empty($veriler['aciklama'])): ?>
                        <br><b>Açıklama:</b> <?php echo htmlspecialchars($veriler['aciklama']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- PAGE 1 BOTTOM: TAAHHÜT KUTUSU -->
        <div style="margin-top:20px; border:1px solid #000; padding:12px; font-size:12px; line-height:1.5; text-align:justify; background:#fff;">
            Birimimiz adına kullanılmak üzere, sistemde yukarıda belirtilen e-posta hesabının açılmasını talep ediyoruz. Ayrıca bu sayfanın arkasında bulunan T.C. Balıkesir Üniversitesi Bilişim Kaynakları Kullanım Politikası, T.C. Balıkesir Üniversitesi E-posta Kullanım Politikası ve Bilgi İşlem Daire Başkanlığı web sayfasında bulunan yasal düzenlemelerdeki kanun, yönetmelik ve politikalara okunduğu ve bunlara uygun hareket edileceğini taahhüt ederiz.
            
            <div style="margin-top:15px; padding-top:8px; border-top:1px dashed #ccc; display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#1b656e; font-weight:bold;">
                <span>✓ Başvuru Sahibi Tarafından Çevrimiçi Dolduruldu ve Onaylandı</span>
                <span>Tarih: <?php echo date('d.m.Y H:i', strtotime($basvuru['kayit_tarihi'])); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
