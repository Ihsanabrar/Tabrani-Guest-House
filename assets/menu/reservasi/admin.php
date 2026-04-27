<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin_login.php');
    exit;
}
include 'db_config.php';

// Fungsi membuat nomor invoice (sama seperti di generate_invoice.php)
function generateInvoiceNumberFromIds($ids_array) {
    $ids_str = implode(',', $ids_array);
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(md5($ids_str), 0, 6));
}

// Fungsi untuk konfirmasi grup (beberapa ID)
if (isset($_GET['confirm_group'])) {
    $ids = $_GET['confirm_group'];
    $id_arr = explode(',', $ids);
    $id_arr = array_map('intval', $id_arr);
    $placeholders = implode(',', array_fill(0, count($id_arr), '?'));
    $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($id_arr)), ...$id_arr);
    $stmt->execute();
    header('Location: admin.php?msg=confirmed');
    exit;
}

// Fungsi hapus grup
if (isset($_GET['delete_group'])) {
    $ids = $_GET['delete_group'];
    $id_arr = explode(',', $ids);
    $id_arr = array_map('intval', $id_arr);
    $placeholders = implode(',', array_fill(0, count($id_arr), '?'));
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($id_arr)), ...$id_arr);
    $stmt->execute();
    header('Location: admin.php?msg=deleted');
    exit;
}

// Ambil semua booking
$sql = "SELECT b.*, r.room_name, r.room_number 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        ORDER BY b.booking_date DESC, b.check_in ASC";
$result = $conn->query($sql);

// Kelompokkan berdasarkan guest_name, guest_email, guest_phone, check_in, check_out, dan tanggal booking (approximate)
$groups = [];
while ($row = $result->fetch_assoc()) {
    // Buat key unik untuk grup (gunakan data utama)
    $key = $row['guest_name'] . '|' . $row['guest_email'] . '|' . $row['guest_phone'] . '|' . $row['check_in'] . '|' . $row['check_out'] . '|' . date('Y-m-d H:i', strtotime($row['booking_date']));
    if (!isset($groups[$key])) {
        $groups[$key] = [
            'ids' => [],
            'guest_name' => $row['guest_name'],
            'guest_email' => $row['guest_email'],
            'guest_phone' => $row['guest_phone'],
            'check_in' => $row['check_in'],
            'check_out' => $row['check_out'],
            'booking_date' => $row['booking_date'],
            'rooms' => [],
            'total_price' => 0,
            'status' => $row['status'] // asumsi semua status sama; jika berbeda, prioritaskan pending
        ];
    }
    $groups[$key]['ids'][] = $row['id'];
    $groups[$key]['rooms'][] = $row['room_name'] . ' (' . $row['room_number'] . ') x' . $row['jumlah_kamar'];
    $groups[$key]['total_price'] += $row['total_price'];
    // Update status: jika ada yg pending, grup jadi pending
    if ($row['status'] == 'pending') $groups[$key]['status'] = 'pending';
}

$stats = ['total' => count($groups), 'pending' => 0, 'confirmed' => 0];
foreach ($groups as $g) {
    if ($g['status'] == 'pending') $stats['pending']++;
    else $stats['confirmed']++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen Pemesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: #F0F0F0;">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-hotel"></i> Tabrani Guest House - Admin Panel</h1>
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="number"><?= $stats['total'] ?></div>
                    <div class="label">Total Transaksi</div>
                </div>
                <div class="admin-stat-card">
                    <div class="number" style="color:#ffc107;"><?= $stats['pending'] ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="admin-stat-card">
                    <div class="number" style="color:#28a745;"><?= $stats['confirmed'] ?></div>
                    <div class="label">Confirmed</div>
                </div>
            </div>
            <a href="admin_login.php?logout=1" class="admin-logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($groups)): ?>
                <p style="text-align:center; padding:40px;"><i class="fas fa-inbox"></i> Belum ada pemesanan.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Nama Tamu</th>
                            <th>Kontak</th>
                            <th>Kamar (jumlah)</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): 
                            $invoice_no = generateInvoiceNumberFromIds($group['ids']);
                            $rooms_str = implode('<br>', $group['rooms']);
                        ?>
                        <tr>
                            <td><?= $invoice_no ?></td>
                            <td><?= htmlspecialchars($group['guest_name']) ?></td>
                            <td><?= htmlspecialchars($group['guest_email']) ?><br><small><?= $group['guest_phone'] ?></small></td>
                            <td><?= $rooms_str ?></td>
                            <td><?= date('d M Y', strtotime($group['check_in'])) ?></td>
                            <td><?= date('d M Y', strtotime($group['check_out'])) ?></td>
                            <td>Rp <?= number_format($group['total_price'],0,',','.') ?></td>
                            <td>
                                <span class="admin-status-badge <?= $group['status'] == 'confirmed' ? 'admin-status-confirmed' : 'admin-status-pending' ?>">
                                    <?= ucfirst($group['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($group['status'] == 'pending'): ?>
                                    <a href="?confirm_group=<?= implode(',', $group['ids']) ?>" class="admin-btn admin-btn-confirm" onclick="return confirm('Konfirmasi seluruh pemesanan ini?')"><i class="fas fa-check-circle"></i> Konfirmasi</a>
                                <?php endif; ?>
                                <a href="?delete_group=<?= implode(',', $group['ids']) ?>" class="admin-btn admin-btn-delete" onclick="return confirm('Hapus seluruh pesanan ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                <a href="generate_invoice.php?ids=<?= implode(',', $group['ids']) ?>" class="admin-btn" style="background:#17a2b8; color:white; margin-top:5px; display:inline-block;" target="_blank"><i class="fas fa-file-pdf"></i> Invoice</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="../../../index.html" class="admin-btn-back" style="display:inline-block; padding:10px 25px; border-radius:40px; background:#6c757d; color:white; text-decoration:none;"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ once: true });</script>
</body>
</html>