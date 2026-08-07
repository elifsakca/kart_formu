<?php
// forms/genel.php - Genel / Dinamik Başvuru Formu Şablonu
$mode = $mode ?? 'input';

if ($mode === 'input'):
?>
    <!-- Genel Form (Dinamik/Yeni Formlar İçin Fallback) -->
    <div id="form_genel.php" class="gizli-form">
        <h2>Genel Başvuru Formu</h2>
        <form method="POST" action="islem.php" enctype="multipart/form-data">
            <input type="hidden" name="form_kodu" value="">
            <input type="hidden" name="form_adi" value="">

            <div id="dinamik_alanlar_konteyner">
                <!-- Javascript ile dinamik bilgi kutucukları buraya eklenecek -->
            </div>

            <button type="submit" name="form_gonder" class="btn-tamam" style="width: 100%; justify-content: center; margin-top: 15px;">Başvuruyu Gönder</button>
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
                    <b>Form Kodu:</b> <?php echo htmlspecialchars($basvuru['form_kodu']); ?><br>
                    <b>Tarih:</b> <?php echo date('d.m.Y', strtotime($basvuru['kayit_tarihi'])); ?><br>
                    <b>Takip No:</b> #<?php echo htmlspecialchars($basvuru['takip_no'] ?: $basvuru['id']); ?>
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 15px 0; font-size: 13px; font-weight: bold;">
            <?php echo mb_strtoupper(htmlspecialchars($basvuru['form_adi']), 'UTF-8'); ?>
        </h3>

        <table class="grid-table">
            <?php foreach ($veriler as $key => $val): ?>
                <?php 
                if (in_array($key, ['form_kodu', 'form_adi', 'fotograf', 'dekont'])) continue;
                $label_name = ucwords(str_replace('_', ' ', $key));
                $display_val = is_array($val) ? implode(', ', $val) : $val;
                ?>
                <tr>
                    <th style="width: 35%;"><?php echo htmlspecialchars($label_name); ?></th>
                    <td><?php echo htmlspecialchars($display_val ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>
