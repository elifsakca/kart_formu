<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>BAÜN Akıllı Kart Başvuru Formu</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; padding: 20px; }
        .form-konteyner { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .bolum-baslik { background-color: #e9ecef; padding: 10px; font-weight: bold; margin-top: 20px; margin-bottom: 15px; border-radius: 5px; color: #333; }
        .form-grup { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .bilgi-notu { font-size: 12px; color: #d9534f; margin-top: 5px; display: block; }
        button { width: 100%; padding: 15px; background-color: #0056b3; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        button:hover { background-color: #004494; }
    </style>
</head>
<body>

<div class="form-konteyner">
    <h2>Balıkesir Üniversitesi Akıllı Kart Başvuru Formu</h2>
    
    <!-- 3. kişinin yazacağı islem.php dosyasına verileri gönderiyoruz -->
    <form action="islem.php" method="POST" enctype="multipart/form-data">

        <div class="bolum-baslik">Kişisel Bilgiler</div>
        <div class="form-grup">
            <label for="tc_no">TC Kimlik No (Zorunlu)[cite: 1]</label>
            <input type="text" name="tc_no" id="tc_no" required maxlength="11" pattern="\d{11}" title="Lütfen 11 haneli TC numaranızı girin">
        </div>
        <div class="form-grup">
            <label for="ad_soyad">Adı, Soyadı (Zorunlu)[cite: 1]</label>
            <input type="text" name="ad_soyad" id="ad_soyad" required>
        </div>
        <div class="form-grup">
            <label for="telefon">İrtibat Telefonu[cite: 1]</label>
            <input type="tel" name="telefon" id="telefon">
        </div>

        <div class="bolum-baslik">Kart ve İşlem Bilgileri</div>
        <div class="form-grup">
            <label for="kart_tipi">Kart (Kişi) Tipi[cite: 1]</label>
            <select name="kart_tipi" id="kart_tipi" required>
                <option value="">Lütfen Seçiniz...</option>
                <option value="Akademik Personel">Akademik Personel[cite: 1]</option>
                <option value="İdari Personel">İdari Personel[cite: 1]</option>
                <option value="Hizmet Alımı Personeli">Hizmet Alımı Personeli[cite: 1]</option>
                <option value="Firma Personeli">Firma Personeli[cite: 1]</option>
                <option value="Diğer Kurum Personeli">Diğer Kurum Personeli[cite: 1]</option>
                <option value="Misafir Personel">Misafir Personel[cite: 1]</option>
                <option value="Koruma ve Güvenlik Personeli">Koruma ve Güvenlik Personeli[cite: 1]</option>
                <option value="Özel Güvenlik Personeli">Özel Güvenlik Personeli[cite: 1]</option>
                <option value="Emekli Personel">Emekli Personel[cite: 1]</option>
                <option value="Onursal">Onursal[cite: 1]</option>
                <option value="Kütüphane">Kütüphane[cite: 1]</option>
            </select>
        </div>
        <div class="form-grup">
            <label for="islem_turu">Yapılacak İşlem Türü[cite: 1]</label>
            <select name="islem_turu" id="islem_turu" required>
                <option value="">Lütfen Seçiniz...</option>
                <option value="İlk Kez Verilmesi">Akıllı kartın ilk kez verilmesi[cite: 1]</option>
                <option value="Hatalı Basım">Hatalı Basılan Kart Bilgisinin Düzeltilmesi[cite: 1]</option>
                <option value="Bilgi Değişikliği">Bilgi Değişikliği (Soyad, Kadro, vb.)[cite: 1]</option>
                <option value="Ayrılış">Ayrılış (İstifa, Emeklilik, vb.)[cite: 1]</option>
            </select>
        </div>

        <div class="bolum-baslik">Detaylı Kurum Bilgileri (Kart tipinize göre doldurunuz)[cite: 1]</div>
        <div class="form-grup">
            <label for="unvan">Unvanı[cite: 1]</label>
            <input type="text" name="unvan" id="unvan">
        </div>
        <div class="form-grup">
            <label for="birim">Fakülte/YO/MYO/Birim[cite: 1]</label>
            <input type="text" name="birim" id="birim">
        </div>
        <div class="form-grup">
            <label for="bolum">Bölüm[cite: 1]</label>
            <input type="text" name="bolum" id="bolum">
        </div>
        <div class="form-grup">
            <label for="sicil_no">Kurum Sicil No[cite: 1]</label>
            <input type="text" name="sicil_no" id="sicil_no">
        </div>
        <div class="form-grup">
            <label for="ek_gosterge">Ödemeye Esas Ek Göstergesi[cite: 1]</label>
            <input type="text" name="ek_gosterge" id="ek_gosterge">
        </div>
        <div class="form-grup">
            <label for="hizmet_yeri">Hizmet Yeri (Hizmet/Firma kartları için)[cite: 1]</label>
            <input type="text" name="hizmet_yeri" id="hizmet_yeri">
        </div>
        <div class="form-grup">
            <label for="firma_adi">Firma Adı (Firma personeli için)[cite: 1]</label>
            <input type="text" name="firma_adi" id="firma_adi">
        </div>
        <div class="form-grup">
            <label for="kurumu">Kurumu (Diğer kurum personeli için)[cite: 1]</label>
            <input type="text" name="kurumu" id="kurumu">
        </div>
        <div class="form-grup">
            <label for="kan_grubu">Kan Grubu (Sadece Güvenlik personeli için)[cite: 1]</label>
            <input type="text" name="kan_grubu" id="kan_grubu">
        </div>
        <div class="form-grup">
            <label for="gorev">Görev (Sadece Misafir personel için)[cite: 1]</label>
            <input type="text" name="gorev" id="gorev">
        </div>

        <div class="bolum-baslik">Fotoğraf Yükleme</div>
        <div class="form-grup">
            <label for="fotograf">Vesikalık Fotoğraf[cite: 1]</label>
            <input type="file" name="fotograf" id="fotograf" accept=".jpg, .jpeg" required>
            <span class="bilgi-notu">* Fotoğrafınız dijital/taranmış (en az 300dpi) ve .jpg formatında olmalıdır.[cite: 1]</span>
        </div>

        <button type="submit">Başvuruyu Gönder</button>
    </form>
</div>

</body>
</html>