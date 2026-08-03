<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAÜN Form İşlem Merkezi</title>
    <style>
        /* Genel Sayfa Ayarları */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        /* Üst Menü (Navbar) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: bold;
            color: #1b656e;
            font-size: 18px;
        }

        /* Banner Alanı */
        .banner {
            background-color: rgb(3, 149, 159);
            color: white;
            text-align: center;
            padding: 80px 20px 120px 20px;
            border-bottom-left-radius: 50% 20%;
            border-bottom-right-radius: 50% 20%;
            margin-bottom: -40px;
        }

        .banner h1 {
            font-size: 36px;
            margin: 0 0 10px 0;
        }

        .banner p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Form Seçim Kutusu */
        .secim-kutusu {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            position: relative;
            z-index: 10;
            /* LİSTENİN AŞAĞI AÇILMASI İÇİN EKLENEN BOŞLUK */
            margin-bottom: 400px;
        }

        /* Açılır Liste (Select) */
        .form-select {
            flex: 1;
            padding: 15px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            color: #333;
            appearance: none;
            /* Açılır ok işaretinin rengi URL encoding içinde %231b656e olarak güncellendi */
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231b656e%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: 15px auto;
        }

        .form-select:focus {
            border-color: #1b656e;
        }
        
        .form-select optgroup {
            font-weight: bold;
            color: #1b656e;
        }

        /* Tamam Butonu */
        .btn-tamam {
            background-color: #1b656e;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
        }

        .btn-tamam:hover {
            background-color: rgb(3, 149, 159);
        }
        
        /* Hata Mesajı */
        #hata-mesaji {
            color: #e74c3c;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Üst Menü -->
    <div class="navbar">
        <div class="navbar-logo">
            <!-- Güncellenmiş Logo URL'si -->
            <img src="https://baunwebapi.balikesir.edu.tr/uploads/1729083231270.png" alt="BAÜN Logo" height="50">
            BALIKESİR ÜNİVERSİTESİ
        </div>
        <div style="font-size: 14px; color: #555;">
            ANASAYFA &nbsp;&nbsp;|&nbsp;&nbsp; FORMLAR HAKKINDA
            
        </div>
    </div>

    <!-- Banner -->
    <div class="banner">
        <h1>Üniversitemiz Form İşlem Merkezi</h1>
        <p>ANASAYFA > FORMLAR</p>
    </div>

    <!-- Form Seçim Kutusu -->
    <div class="secim-kutusu">
        <select id="formSecici" class="form-select">
            <option value="">-- Doldurmak İstediğiniz Formu Seçiniz --</option>
            
            <!-- Önceki Akıllı Kart Formları -->
            <optgroup label="Akıllı Kart Formları">
                <option value="form_f52.php">Akıllı Kart İşlem Formu (F-52)</option>
                <option value="form_f53.php">Akıllı Kart Öğrenci İşlem Formu (F-53)</option>
                <option value="form_f54.php">Kayıp Akıllı Kart Müracaat Formu (F-54)</option>
                <option value="form_f55.php">Arızalı Akıllı Kart Müracaat Formu (F-55)</option>
            </optgroup>

            <!-- Görselden Eklenen Yeni Bilgi İşlem Formları -->
            <optgroup label="Bilgi İşlem Daire Başkanlığı Formları">
                <option value="form_0071.php">KDYS.FR.0071 - Bilgi İşlem DB Bakım Onarım Takip Formu</option>
                <option value="form_0072.php">KDYS.FR.0072 - Bilgi İşlem DB Kurumsal E-Posta Talep Formu</option>
                <option value="form_0073.php">KDYS.FR.0073 - Bilgi İşlem DB E-İmza Mini Kart Okuyucu Teslim Tesellüm Tutanağı</option>
                <option value="form_0074.php">KDYS.FR.0074 - Bilgi İşlem DB E-İmza Talep Formu</option>
                <option value="form_0077.php">KDYS.FR.0077 - Bilgi İşlem DB Kişisel Web Adı ve Alanı Sözleşmesi</option>
                <option value="form_0078.php">KDYS.FR.0078 - Bilgi İşlem DB Kurumsal Statik IP Sözleşmesi</option>
                <option value="form_0079.php">KDYS.FR.0079 - Bilgi İşlem DB Kurumsal Web Adı ve Alanı Sözleşmesi</option>
                <option value="form_0080.php">KDYS.FR.0080 - Bilgi İşlem DB Mernis Taahhütnamesi</option>
                <option value="form_0082.php">KDYS.FR.0082 - Bilgi İşlem DB Personel Elektronik Posta Başvuru Formu</option>
                <option value="form_0087.php">KDYS.FR.0087 - Bilgi İşlem UAM Mernis Taahhütnamesi</option>
            </optgroup>
        </select>
        
        <!-- İkon kaldırılmış Tamam butonu -->
        <button class="btn-tamam" onclick="formYonetlendir()">
            Tamam
        </button>
    </div>
    
    <div id="hata-mesaji">Lütfen listeden bir form seçiniz!</div>

    <script>
        function formYonetlendir() {
            var secilenForm = document.getElementById("formSecici").value;
            var hataMesaji = document.getElementById("hata-mesaji");
            
            if (secilenForm === "") {
                hataMesaji.style.display = "block";
            } else {
                hataMesaji.style.display = "none";
                alert("Sistem bu aşamada " + secilenForm + " dosyasına yönlendirme yapacaktır. Formlar oluşturulduğunda bu uyarı kalkacaktır.");
            }
        }
    </script>

</body>
</html>