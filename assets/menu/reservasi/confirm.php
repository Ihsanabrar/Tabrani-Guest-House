<?php
session_start();
include 'db_config.php';

if (!isset($_GET['id'])) {
    header('Location: reservasi.php');
    exit;
}

$booking_id = (int)$_GET['id'];
$sql = "SELECT b.*, r.room_name 
        FROM bookings b JOIN rooms r ON b.room_id = r.id 
        WHERE b.id = $booking_id";
$result = $conn->query($sql);
if ($result->num_rows == 0) die("Booking tidak ditemukan.");
$booking = $result->fetch_assoc();

// Nomor admin (ganti dengan nomor tujuan)
$admin_phone = '6282387037225';
$message = "Halo Admin, ada booking baru:%0A";
$message .= "Nama: {$booking['guest_name']}%0A";
$message .= "Email: {$booking['guest_email']}%0A";
$message .= "No. HP: {$booking['guest_phone']}%0A";
$message .= "Check-in: {$booking['check_in']}%0A";
$message .= "Check-out: {$booking['check_out']}%0A";
$message .= "Kamar: {$booking['room_name']}%0A";
$message .= "Total Harga: Rp " . number_format($booking['total_price'],0,',','.') . "%0A";
$message .= "Silakan konfirmasi.";
$wa_link = "https://wa.me/$admin_phone?text=$message";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking - Tabrani Guest House</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="confirmation-page">
    <div class="confirmation-container">
        <div class="confirmation-card" data-aos="fade-up" data-aos-duration="600">
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Booking Berhasil!</h1>
                <p>Terima kasih telah memesan di Tabrani Guest House</p>
            </div>
            <div class="booking-detail-card">
                <h3 style="text-align:center; font-family:'Cormorant Garamond', serif;">Detail Pemesanan</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <div class="detail-label">Nama Tamu</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['guest_name']) ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['guest_email']) ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <div class="detail-label">Telepon</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['guest_phone']) ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-bed"></i>
                        <div>
                            <div class="detail-label">Kamar</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['room_name']) ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-check"></i>
                        <div>
                            <div class="detail-label">Check-in</div>
                            <div class="detail-value"><?= date('d M Y', strtotime($booking['check_in'])) ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-times"></i>
                        <div>
                            <div class="detail-label">Check-out</div>
                            <div class="detail-value"><?= date('d M Y', strtotime($booking['check_out'])) ?></div>
                        </div>
                    </div>
                </div>
                <div class="total-box">
                    <span><i class="fas fa-money-bill-wave"></i> Total Pembayaran</span>
                    <span>Rp <?= number_format($booking['total_price'],0,',','.') ?></span>
                </div>
                <div class="action-buttons">
                    <a href="<?= $wa_link ?>" class="btn-wa" target="_blank"><i class="fab fa-whatsapp"></i> Kirim ke Admin</a>
                    <a href="cancel.php?id=<?= $booking_id ?>" class="btn-cancel" onclick="return confirm('Yakin ingin membatalkan pemesanan?')"><i class="fas fa-trash-alt"></i> Batalkan</a>
                </div>
                <div style="text-align: center;">
                    <a href="../catalog.html" class="btn-home"><i class="fas fa-home"></i> Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true });
    </script>
</body>
</html>