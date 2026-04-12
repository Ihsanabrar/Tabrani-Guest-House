<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin_login.php');
    exit;
}
include 'db_config.php';

// Handle konfirmasi jika ada parameter confirm
if (isset($_GET['confirm'])) {
    $id = (int)$_GET['confirm'];
    $conn->query("UPDATE bookings SET status = 'confirmed' WHERE id = $id");
    header('Location: admin.php?msg=confirmed');
    exit;
}

$sql = "SELECT b.*, r.room_name, r.room_number 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        ORDER BY b.check_in ASC";
$result = $conn->query($sql);

$stats = ['total' => 0, 'pending' => 0, 'confirmed' => 0];
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
    $stats['total']++;
    if ($row['status'] == 'pending') $stats['pending']++;
    elseif ($row['status'] == 'confirmed') $stats['confirmed']++;
}
$result->data_seek(0);
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
                    <div class="label">Total Pesanan</div>
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
            <?php if ($stats['total'] == 0): ?>
                <p style="text-align:center; padding:40px;"><i class="fas fa-inbox"></i> Belum ada pemesanan.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Nama Tamu</th><th>Kontak</th><th>Kamar</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['guest_name']) ?></td>
                            <td><?= htmlspecialchars($row['guest_email']) ?><br><small><?= $row['guest_phone'] ?></small></td>
                            <td><?= $row['room_name'] ?> (<?= $row['room_number'] ?>)</td>
                            <td><?= date('d M Y', strtotime($row['check_in'])) ?></td>
                            <td><?= date('d M Y', strtotime($row['check_out'])) ?></td>
                            <td>Rp <?= number_format($row['total_price'],0,',','.') ?></td>
                            <td>
                                <span class="admin-status-badge <?= $row['status'] == 'confirmed' ? 'admin-status-confirmed' : 'admin-status-pending' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="?confirm=<?= $row['id'] ?>" class="admin-btn admin-btn-confirm" onclick="return confirm('Konfirmasi pemesanan ini?')"><i class="fas fa-check-circle"></i> Konfirmasi</a>
                                <?php endif; ?>
                                <a href="admin_delete.php?id=<?= $row['id'] ?>" class="admin-btn admin-btn-delete" onclick="return confirm('Hapus pesanan ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="../reservasi/reservasi.php" class="admin-btn-back" style="display:inline-block; padding:10px 25px; border-radius:40px; background:#6c757d; color:white; text-decoration:none;"><i class="fas fa-arrow-left"></i> Kembali ke Halaman Reservasi</a>
        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ once: true });</script>
</body>
</html>