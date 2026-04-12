<?php
session_start();
unset($_SESSION['guest_data']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - Tabrani Guest House</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="reservation-page">
    <div class="container">
        <div class="reservation-wrapper">
            <div class="form-section">
                <!-- Tombol kembali dipindahkan ke dalam form-section, tanpa class fixed -->
                <a href="../../../index.html" class="back-arrow-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Reservasi</span>
                </a>
                <h1>Form Reservasi</h1>
                <p>Isi data diri Anda untuk melanjutkan pemesanan kamar.</p>
                <form action="pilihkamar.php" method="POST">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="contoh@email.com">

                    <label>Judul Panggilan</label>
                    <select name="title">
                        <option>Tuan</option>
                        <option>Nyonya</option>
                        <option>Nona</option>
                        <option>Dr</option>
                    </select>

                    <label>Nama Depan</label>
                    <input type="text" name="first_name" required>

                    <label>Nama Belakang</label>
                    <input type="text" name="last_name" required>

                    <label>Nomor Telepon</label>
                    <input type="tel" name="phone" required>

                    <label>Check-in</label>
                    <input type="date" name="check_in" required min="<?= date('Y-m-d') ?>">

                    <label>Check-out</label>
                    <input type="date" name="check_out" required>

                    <button type="submit">Cek Ketersediaan</button>
                </form>
            </div>
            <div class="info-section">
                <h2>Tabrani Guest House</h2>
                <p>Nikmati kenyamanan menginap di pusat kota Pekanbaru dengan sentuhan elegan dan fasilitas lengkap.</p>
                <ul class="feature-list">
                    <li><i class="fas fa-wifi"></i> Tersedia WiFi</li>
                    <li><i class="fas fa-tv"></i> Smart TV 40"</li>
                    <li><i class="fas fa-coffee"></i> Coffee & Tea Maker</li>
                    <li><i class="fas fa-shower"></i> Bathroom Modern</li>
                    <li><i class="fas fa-parking"></i> Parkir Luas</li>
                </ul>
                <p style="margin-top: 30px;"><i class="fas fa-phone-alt"></i> Butuh bantuan? Hubungi (0761) 38762</p>
            </div>
        </div>
    </div>
</body>

</html>