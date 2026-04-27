<?php
session_start();
if (!isset($_SESSION['last_booking'])) {
    header('Location: index.php');
    exit;
}

$booking = $_SESSION['last_booking'];
$invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(md5($booking['booking_ids'][0]), 0, 6));

// Buat link absolut ke generate_invoice.php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$invoice_link = $protocol . "://" . $host . $path . "/generate_invoice.php?ids=" . implode(',', $booking['booking_ids']);

// Bangun pesan WhatsApp tanpa simbol aneh, rapi
$wa_message = "KONFIRMASI PEMESANAN KAMAR\n";
$wa_message .= "========================\n\n";
$wa_message .= "TABRANI GUEST HOUSE\n";
$wa_message .= "No. Invoice: " . $invoice_no . "\n";
$wa_message .= "Nama Pemesan: " . $booking['guest_name'] . "\n";
$wa_message .= "Email: " . $booking['guest_email'] . "\n";
$wa_message .= "Telepon: " . $booking['guest_phone'] . "\n";
$wa_message .= "Check-in: " . date('d F Y', strtotime($booking['check_in'])) . "\n";
$wa_message .= "Check-out: " . date('d F Y', strtotime($booking['check_out'])) . " (" . $booking['nights'] . " malam)\n";
$wa_message .= "\nDetail Kamar:\n";
foreach ($booking['rooms'] as $room) {
    $wa_message .= "- " . $room['nama_kamar'] . "\n";
    $wa_message .= "  Jumlah: " . $room['jumlah'] . " kamar x " . $booking['nights'] . " malam\n";
    $wa_message .= "  Harga per malam: Rp " . number_format($room['harga_per_malam'], 0, ',', '.') . "\n";
    $wa_message .= "  Subtotal: Rp " . number_format($room['subtotal'], 0, ',', '.') . "\n";
}
$wa_message .= "\nTotal Pembayaran: Rp " . number_format($booking['total'], 0, ',', '.') . "\n";
$wa_message .= "\nLink Invoice PDF:\n" . $invoice_link . "\n";
$wa_message .= "\nSilakan klik link di atas untuk melihat/mendownload invoice.\n";
$wa_message .= "Harap konfirmasi pembayaran ke nomor ini.\n";
$wa_message .= "Terima kasih.";

$wa_url = "https://wa.me/62811763566?text=" . urlencode($wa_message);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking - Tabrani Guest House</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmation-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .btn-download {
            background: #1A1A1A;
            color: #C5A059;
            border: 1px solid #C5A059;
        }
        .btn-download:hover {
            background: #C5A059;
            color: #1A1A1A;
        }
        /* Hilangkan emote bintang di header konfirmasi */
        .confirmation-header::before,
        .confirmation-header::after {
            content: none !important;
        }
    </style>
</head>
<body class="confirmation-page">
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Booking Berhasil!</h1>
                <p>Terima kasih telah memesan di Tabrani Guest House</p>
            </div>
            <div class="booking-detail-card">
                <h3>Detail Pemesanan</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <div class="detail-label">Nama Pemesan</div>
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
                    <div class="detail-item">
                        <i class="fas fa-file-invoice"></i>
                        <div>
                            <div class="detail-label">Nomor Invoice</div>
                            <div class="detail-value"><?= $invoice_no ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="room-list-summary" style="margin: 20px 0;">
                    <h4 style="margin-bottom: 10px;">Kamar yang dipesan:</h4>
                    <?php foreach ($booking['rooms'] as $room): ?>
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 12px; margin-bottom: 10px;">
                        <strong><?= htmlspecialchars($room['nama_kamar']) ?></strong><br>
                        Jumlah: <?= $room['jumlah'] ?> kamar x <?= $booking['nights'] ?> malam<br>
                        Harga/malam: Rp <?= number_format($room['harga_per_malam'], 0, ',', '.') ?><br>
                        Subtotal: Rp <?= number_format($room['subtotal'], 0, ',', '.') ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="total-box">
                    <span>Total Pembayaran</span>
                    <span>Rp <?= number_format($booking['total'], 0, ',', '.') ?></span>
                </div>
                
                <div class="action-buttons">
                    <a href="generate_invoice.php?ids=<?= implode(',', $booking['booking_ids']) ?>" class="btn-wa btn-download" style="background:#1A1A1A; color:#C5A059;">
                        <i class="fas fa-file-pdf"></i> Download Invoice PDF
                    </a>
                    <a href="<?= $wa_url ?>" class="btn-wa" target="_blank">
                        <i class="fab fa-whatsapp"></i> Konfirmasi via WhatsApp (dengan detail booking & link invoice)
                    </a>
                </div>
                <a href="../../../index.html" class="btn-home" style="display: block; text-align: center; margin-top: 20px;">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php 
// Hapus session jika ingin user tidak bisa reload konfirmasi setelah download
// unset($_SESSION['last_booking']);
?>