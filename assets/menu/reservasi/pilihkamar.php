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

$room_config = [
    1 => ["stok" => 5, "harga" => 350000],  // Double
    2 => ["stok" => 12, "harga" => 350000], // Twin
    3 => ["stok" => 4, "harga" => 500000]   // Family
];

$sql = "SELECT r.*, 
       COALESCE((SELECT COUNT(*) FROM bookings b 
        WHERE b.room_id = r.id AND b.status IN ('pending','confirmed') 
        AND b.check_in < '$check_out' AND b.check_out > '$check_in'), 0) as total_booking
        FROM rooms r";
$result = $conn->query($sql);
if (!$result) {
    die("Error loading rooms: " . $conn->error);
}
$available_rooms = [];
while ($row = $result->fetch_assoc()) {
    $room_id = $row['id'];
    $stok = $room_config[$room_id]['stok'] ?? 0;
    $row['sisa_kamar'] = $stok - $row['total_booking'];
    $available_rooms[] = $row;
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
    if (stripos($roomName, 'family') !== false) return ' Family Room';
    if (stripos($roomName, 'king') !== false) return ' King Bed';
    if (stripos($roomName, 'twin') !== false) return ' Twin Bed';
    return '⭐ Standard Room';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Pilih Kamar - Tabrani Guest House</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="shortcut icon" href="../../logo-stch.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
    /* ========== SEMUA GARIS DIHILANGKAN ========== */
    .pk-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .pk-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .pk-header h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.5rem;
        color: #1A1A1A;
        margin-bottom: 10px;
        border-bottom: none;
    }

    .pk-header p {
        color: #666;
        font-size: 1rem;
    }

    .pk-booking-summary {
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        padding: 20px 30px;
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        border: none;
    }

    .pk-date-info {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }

    .pk-date-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pk-date-item i {
        font-size: 1.8rem;
        color: #C5A059;
    }

    .pk-date-item .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
    }

    .pk-date-item .value {
        font-weight: 600;
        font-size: 1rem;
        color: #1A1A1A;
    }

    .pk-nights {
        background: #1A1A1A;
        color: #C5A059;
        padding: 8px 20px;
        border-radius: 40px;
        font-weight: 600;
    }

    .pk-two-columns {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 40px;
    }

    .pk-room-list {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .pk-room-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: 0.3s;
        border: none;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .pk-room-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
    }

    .pk-room-image {
        flex: 0 0 280px;
        height: 220px;
        overflow: hidden;
    }

    .pk-room-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s;
    }

    .pk-room-card:hover .pk-room-image img {
        transform: scale(1.03);
    }

    .pk-room-details {
        flex: 1;
        padding: 20px 25px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pk-room-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #1A1A1A;
    }

    .pk-room-badge {
        display: inline-block;
        background: rgba(197, 160, 89, 0.15);
        color: #C5A059;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 12px;
        border: none;
    }

    .pk-room-desc {
        color: #666;
        font-size: 0.85rem;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    .pk-room-facilities {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .pk-room-facilities span {
        font-size: 0.75rem;
        color: #555;
        background: #f5f5f5;
        padding: 4px 10px;
        border-radius: 20px;
        border: none;
    }

    .pk-room-facilities i {
        color: #C5A059;
        margin-right: 5px;
    }

    .pk-room-price {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        flex-wrap: wrap;
        margin-top: 10px;
        border-top: none;
        padding-top: 0;
    }

    .pk-price-per-night {
        font-size: 1.2rem;
        font-weight: 600;
        color: #C5A059;
    }

    .pk-total-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1A1A1A;
    }

    .pk-select-btn {
        background: #1A1A1A;
        color: white;
        border: none;
        padding: 8px 25px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        margin-left: 20px;
    }

    .pk-select-btn:hover {
        background: #C5A059;
        color: #1A1A1A;
    }

    .pk-sidebar {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 25px;
        position: sticky;
        top: 20px;
        height: fit-content;
        border: none;
    }

    .pk-sidebar h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: #1A1A1A;
        border-left: 4px solid #C5A059;
        padding-left: 15px;
    }

    .pk-guest-detail {
        background: #F9F9F9;
        padding: 15px;
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .pk-guest-detail p {
        margin-bottom: 8px;
        font-size: 0.85rem;
    }

    .pk-guest-detail i {
        width: 25px;
        color: #C5A059;
    }

    .pk-sidebar-date {
        background: #1A1A1A;
        color: white;
        padding: 15px;
        border-radius: 16px;
        text-align: center;
        margin: 20px 0;
    }

    .pk-sidebar-date span {
        color: #C5A059;
        font-weight: bold;
    }

    .pk-total-section {
        margin-top: 20px;
        padding-top: 0;
        border-top: none;
    }

    .pk-total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .pk-grand-total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #C5A059;
        margin-top: 10px;
        padding-top: 0;
        border-top: none;
    }

    .pk-submit-btn {
        width: 100%;
        background: #C5A059;
        color: #ffffffff;
        border: none;
        padding: 14px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .pk-submit-btn:hover {
        background: #1A1A1A;
        color: #C5A059;
    }

    .pk-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #1A1A1A;
        color: #C5A059;
        padding: 8px 20px;
        border-radius: 40px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 20px;
        transition: 0.2s;
        border: none;
    }

    .pk-back-link:hover {
        background: #C5A059;
        color: #1A1A1A;
    }

    hr,
    .pk-container hr,
    .pk-header hr,
    .pk-room-card hr,
    .pk-sidebar hr {
        display: none;
    }

    .pk-header h1::after,
    .pk-header p::after,
    .pk-header::before,
    .pk-header::after {
        content: none;
    }

    /* ========== PENYESUAIAN LAYAR KECIL (iPhone 13) ========== */
    @media (max-width: 768px) {
        .pk-container {
            padding: 15px;
        }
        .pk-header h1 {
            font-size: 1.8rem;
        }
        .pk-header p {
            font-size: 0.85rem;
        }
        .pk-booking-summary {
            padding: 15px;
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        .pk-date-info {
            justify-content: center;
            gap: 20px;
        }
        .pk-date-item i {
            font-size: 1.4rem;
        }
        .pk-date-item .label {
            font-size: 0.65rem;
        }
        .pk-date-item .value {
            font-size: 0.85rem;
        }
        .pk-nights {
            font-size: 0.85rem;
            padding: 6px 16px;
        }
        .pk-two-columns {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        .pk-sidebar {
            position: static;
            margin-top: 0;
            padding: 20px;
        }
        .pk-sidebar h3 {
            font-size: 1.3rem;
        }
        .pk-guest-detail p {
            font-size: 0.8rem;
        }
        .pk-room-card {
            flex-direction: column;
        }
        .pk-room-image {
            flex: 0 0 auto;
            height: 200px;
        }
        .pk-room-details {
            padding: 15px;
        }
        .pk-room-title {
            font-size: 1.3rem;
        }
        .pk-room-desc {
            font-size: 0.8rem;
        }
        .pk-room-facilities span {
            font-size: 0.7rem;
        }
        .pk-price-per-night {
            font-size: 1rem;
        }
        .pk-total-price {
            font-size: 0.9rem;
        }
        .pk-select-btn {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
            text-align: center;
            padding: 8px 15px;
        }
        .pk-room-price {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .pk-room-price > div {
            width: 100%;
        }
        label[style*="font-size:0.8rem"] {
            font-size: 0.7rem !important;
        }
        input[type="number"] {
            width: 55px;
            padding: 6px;
        }
        .checkbox-label {
            margin-top: 8px;
        }
        .pk-submit-btn {
            padding: 12px;
            font-size: 0.9rem;
        }
        .pk-sidebar-date {
            padding: 12px;
            font-size: 0.85rem;
        }
        .pk-total-row {
            font-size: 0.85rem;
        }
        .pk-grand-total {
            font-size: 1rem;
        }
        .pk-back-link {
            padding: 6px 16px;
            font-size: 0.75rem;
            margin-bottom: 15px;
        }
    }

    @media (max-width: 480px) {
        .pk-header h1 {
            font-size: 1.5rem;
        }
        .pk-date-info {
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .pk-date-item {
            gap: 8px;
        }
        .pk-room-facilities {
            gap: 8px;
        }
        .pk-room-facilities span {
            padding: 3px 8px;
        }
        .pk-sidebar {
            padding: 15px;
        }
        .pk-guest-detail {
            padding: 10px;
        }
        .pk-guest-detail i {
            width: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="pk-container">
        <a href="../../../index.html" class="pk-back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Form Reservasi
        </a>

        <div class="pk-header">
            <h1>Pilih Kamar Favorit Anda</h1>
            <p>Kami menyediakan berbagai tipe kamar untuk kenyamanan menginap Anda</p>
        </div>

        <div class="pk-booking-summary">
            <div class="pk-date-info">
                <div class="pk-date-item">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <div class="label">Check-in</div>
                        <div class="value"><?= htmlspecialchars(date('d M Y', strtotime($check_in))) ?></div>
                    </div>
                </div>
                <div class="pk-date-item">
                    <i class="fas fa-calendar-times"></i>
                    <div>
                        <div class="label">Check-out</div>
                        <div class="value"><?= htmlspecialchars(date('d M Y', strtotime($check_out))) ?></div>
                    </div>
                </div>
            </div>
            <div class="pk-nights">
                <i class="fas fa-moon"></i> <?= $nights ?> Malam
            </div>
        </div>

        <?php if (empty($available_rooms)): ?>
        <div class="error">
            <p>Maaf, tidak ada kamar tersedia untuk periode yang dipilih.</p>
            <a href="../../../index.html" class="pk-back-link"><i class="fas fa-arrow-left"></i> Kembali ke Form</a>
        </div>
        <?php else: ?>
        <form action="proses.php" method="POST">
            <input type="hidden" name="nights" value="<?= $nights ?>">
            <div class="pk-two-columns">
                <div class="pk-room-list">
                    <?php foreach ($available_rooms as $room): ?>
                    <div class="pk-room-card">
                        <div class="pk-room-image">
                            <img src="<?= htmlspecialchars(getRoomImage($room)) ?>"
                                alt="<?= htmlspecialchars($room['room_name']) ?>">
                        </div>
                        <div class="pk-room-details">
                            <div>
                                <div class="pk-room-badge"><?= getRoomBadge($room['room_name']) ?></div>
                                <div class="pk-room-title"><?= htmlspecialchars($room['room_name']) ?></div>
                                <div class="pk-room-desc"><?= htmlspecialchars($room['description']) ?></div>
                                <div class="pk-room-facilities">
                                    <span><i class="fas fa-user-friends"></i> <?= (int)$room['capacity'] ?> orang</span>
                                    <span><i class="fas fa-wifi"></i> Free WiFi</span>
                                    <span><i class="fas fa-tv"></i> Smart TV</span>
                                </div>
                            </div>
                            <div class="pk-room-price">
                                <div>
                                    <div class="pk-price-per-night">
                                        <?php $harga = $room_config[$room['id']]['harga'] ?? 0; ?>
                                        Rp <?= number_format($harga,0,',','.') ?> <span style="font-size:0.7rem;">/
                                            malam</span></div>
                                    <div class="pk-total-price">Total Rp
                                        <?= number_format($harga * $nights,0,',','.') ?></div>
                                </div>
                                <?php if ($room['sisa_kamar'] <= 0): ?>
                                <p style="color:red; font-weight:600;">Kamar tidak tersedia</p>
                                <?php else: ?>
                                <p style="color:green;">Sisa kamar: <?= $room['sisa_kamar'] ?></p>
                                <?php endif; ?>
                                <div>
                                    <label style="font-size:0.8rem;">Jumlah kamar:</label><br>
                                    <input type="number" name="jumlah_kamar[<?= $room['id'] ?>]" min="1"
                                        max="<?= $room['sisa_kamar'] ?>" value="1"
                                        style="width:60px; padding:4px; border-radius:6px;">
                                </div>

                                <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="room_id[]" value="<?= $room['id'] ?>"
                                        <?= ($room['sisa_kamar'] <= 0 ? 'disabled' : '') ?>>
                                    <span class="pk-select-btn">Pilih Kamar ini</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <aside class="pk-sidebar">
                    <h3>Ringkasan Pemesanan</h3>
                    <div class="pk-guest-detail">
                        <p><i class="fas fa-user"></i>
                            <?= htmlspecialchars($_SESSION['guest_data']['title'] . ' ' . $_SESSION['guest_data']['first_name'] . ' ' . $_SESSION['guest_data']['last_name']) ?>
                        </p>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($_SESSION['guest_data']['email']) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($_SESSION['guest_data']['phone']) ?></p>
                    </div>
                    <div class="pk-sidebar-date">
                        <i class="fas fa-calendar-alt"></i> Check-in:
                        <span><?= date('d M Y', strtotime($check_in)) ?></span><br>
                        <i class="fas fa-calendar-check"></i> Check-out:
                        <span><?= date('d M Y', strtotime($check_out)) ?></span><br>
                        <i class="fas fa-moon"></i> Lama menginap: <span><?= $nights ?> malam</span>
                    </div>
                    <div class="pk-total-section">
                        <div class="pk-total-row">
                            <span>Harga kamar per malam</span>
                            <span id="selectedPricePerNight">Rp 0</span>
                        </div>
                        <div class="pk-total-row">
                            <span>Jumlah malam</span>
                            <span><?= $nights ?> malam</span>
                        </div>
                        <div class="pk-grand-total" id="selectedTotal">
                            Total: Rp 0
                        </div>
                    </div>
                    <button type="submit" class="pk-submit-btn">
                        <i class="fas fa-credit-card"></i> Lanjutkan ke Pembayaran
                    </button>
                </aside>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
    const selectedPriceSpan = document.getElementById('selectedPricePerNight');
    const selectedTotalSpan = document.getElementById('selectedTotal');
    const nights = <?= $nights ?>;

    const roomPrices = {};
    <?php foreach ($available_rooms as $room): ?>
    roomPrices[<?= $room['id'] ?>] = <?= $room_config[$room['id']]['harga'] ?? 0 ?>;
    <?php endforeach; ?>

    function updateTotal() {
        let total = 0;
        let priceDisplay = 0;

        document.querySelectorAll('input[name="room_id[]"]:checked').forEach(cb => {
            const roomId = cb.value;
            const price = roomPrices[roomId] || 0;
            const jumlahInput = document.querySelector(`input[name="jumlah_kamar[${roomId}]"]`);
            const jumlah = parseInt(jumlahInput.value) || 1;
            total += price * nights * jumlah;
            priceDisplay += price * jumlah;
        });

        selectedPriceSpan.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(priceDisplay);
        selectedTotalSpan.innerText = 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    document.querySelectorAll('input[name="room_id[]"]').forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            const roomId = this.name.match(/\d+/)[0];
            const checkbox = document.querySelector(`input[name="room_id[]"][value="${roomId}"]`);
            if (this.value > 0) {
                checkbox.checked = true;
            }
            updateTotal();
        });
    });
    </script>
</body>

</html>