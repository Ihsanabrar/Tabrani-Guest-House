<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['guest_data'] = [
        'email' => $_POST['email'],
        'title' => $_POST['title'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'phone' => $_POST['phone'],
        'check_in' => $_POST['check_in'],
        'check_out' => $_POST['check_out']
    ];
} elseif (!isset($_SESSION['guest_data'])) {
    header('Location: index.php');
    exit;
}

$check_in = $_SESSION['guest_data']['check_in'];
$check_out = $_SESSION['guest_data']['check_out'];
if (strtotime($check_out) <= strtotime($check_in)) die("Error: Check-out harus setelah check-in.");

$nights = (strtotime($check_out) - strtotime($check_in)) / 86400;

$sql = "SELECT r.*, 
       (SELECT COUNT(*) FROM bookings b 
        WHERE b.room_id = r.id AND b.status IN ('pending','confirmed') 
        AND b.check_in < '$check_out' AND b.check_out > '$check_in') as is_booked
        FROM rooms r";
$result = $conn->query($sql);
$available_rooms = [];
while ($row = $result->fetch_assoc()) {
    if ($row['is_booked'] == 0) $available_rooms[] = $row;
}

function getRoomImage($room) {
    if (!empty($room['image']) && file_exists($room['image'])) {
        return $room['image'];
    }
    $basePath = 'GUEST_HOUSE/';
    $imageName = str_replace(' ', '_', strtolower($room['room_name'])) . '.jpg';
    $customPath = $basePath . $imageName;
    if (file_exists($customPath)) {
        return $customPath;
    }
    return 'GUEST_HOUSE/TGH-13.jpg';
}

function getRoomBadge($roomName) {
    if (stripos($roomName, 'family') !== false) return '👨‍👩‍👧‍👦 Family Room';
    if (stripos($roomName, 'double') !== false) return '💑 Double Bed';
    if (stripos($roomName, 'twin') !== false) return '👥 Twin Bed';
    return '⭐ Standard Room';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kamar - Tabrani Guest House</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Agar judul halaman tidak tertutup tombol fixed */
        .page-header {
            padding-top: 70px;
        }
        @media (max-width: 768px) {
            .page-header {
                padding-top: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Tombol kembali fixed di pojok kiri atas -->
        <a href="index.php" class="back-arrow-btn back-arrow-fixed">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Form Reservasi</span>
        </a>

        <div class="page-header">
            <h1><i class="fas fa-bed"></i> Pilih Kamar Favorit Anda</h1>
            <p>Kami menyediakan berbagai tipe kamar untuk kenyamanan menginap Anda</p>
        </div>

        <?php if (empty($available_rooms)): ?>
        <div class="error">
            <p>Maaf, tidak ada kamar tersedia untuk periode yang dipilih.</p>
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Form</a>
        </div>
        <?php else: ?>
        <form action="proses.php" method="POST">
            <input type="hidden" name="nights" value="<?= $nights ?>">
            <div class="two-columns">
                <!-- SIDEBAR KIRI (sticky) -->
                <aside class="sidebar">
                    <div class="guest-summary">
                        <h3><i class="fas fa-user-check"></i> Detail Pemesan</h3>
                        <p><i class="fas fa-user"></i>
                            <?= htmlspecialchars($_SESSION['guest_data']['title'] . ' ' . $_SESSION['guest_data']['first_name'] . ' ' . $_SESSION['guest_data']['last_name']) ?>
                        </p>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($_SESSION['guest_data']['email']) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($_SESSION['guest_data']['phone']) ?></p>
                    </div>
                    <div class="date-box">
                        <i class="fas fa-calendar-alt"></i> Check-in: <span><?= htmlspecialchars($check_in) ?></span><br>
                        <i class="fas fa-calendar-check"></i> Check-out: <span><?= htmlspecialchars($check_out) ?></span><br>
                        <i class="fas fa-moon"></i> Lama menginap: <span><?= $nights ?> malam</span>
                    </div>
                    <div class="sidebar-footer">
                        <p><i class="fas fa-concierge-bell"></i> Layanan kamar 24 jam</p>
                        <p><i class="fas fa-parking"></i> Parkir gratis untuk tamu</p>
                        <p><i class="fas fa-wifi"></i> WiFi gratis</p>
                        <p><i class="fas fa-coffee"></i> Coffee & Tea maker</p>
                        <a href="index.php" class="btn-back" style="display:inline-block; margin-top:15px;"><i
                                class="fas fa-edit"></i> Edit data pemesan</a>
                    </div>
                </aside>

                <!-- DAFTAR KAMAR DI KANAN -->
                <div class="rooms-content">
                    <div class="room-list">
                        <?php foreach ($available_rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-badge">
                                <i class="fas fa-tag"></i> <?= getRoomBadge($room['room_name']) ?>
                            </div>
                            <img src="<?= htmlspecialchars(getRoomImage($room)) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>">
                            <div class="room-info">
                                <h3><?= htmlspecialchars($room['room_name']) ?></h3>
                                <div class="room-desc"><?= htmlspecialchars($room['description']) ?></div>
                                <div class="capacity"><i class="fas fa-users"></i> Kapasitas: <?= (int)$room['capacity'] ?> orang</div>
                                <div class="price">Rp <?= number_format($room['price_per_night'],0,',','.') ?> <span
                                        style="font-size:0.8rem;">/ malam</span></div>
                                <div class="total-price">Total <?= $nights ?> malam: <strong>Rp
                                        <?= number_format($room['price_per_night'] * $nights,0,',','.') ?></strong>
                                </div>
                                <label class="radio-label">
                                    <input type="radio" name="room_id" value="<?= $room['id'] ?>" required>
                                    <span>Pilih kamar ini <i class="fas fa-arrow-right"></i></span>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit"><i class="fas fa-credit-card"></i> Lanjutkan ke Pembayaran</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>

</html>