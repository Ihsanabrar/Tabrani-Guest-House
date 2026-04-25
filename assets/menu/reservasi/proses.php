<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['guest_data']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: reservasi.php');
    exit;
}

// Cek apakah kolom 'jumlah_kamar' ada di tabel bookings
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'jumlah_kamar'");
if (!$check || $check->num_rows == 0) {
    die("Error: Kolom 'jumlah_kamar' tidak ditemukan di tabel bookings.<br>
         Jalankan SQL ini:<br>
         <code>ALTER TABLE `bookings` ADD `jumlah_kamar` INT NOT NULL DEFAULT 1 AFTER `room_id`;</code>");
}

$room_ids = $_POST['room_id'] ?? [];
if (empty($room_ids)) {
    die("Pilih minimal 1 kamar");
}

$guest = $_SESSION['guest_data'];
$check_in = $guest['check_in'];
$check_out = $guest['check_out'];

$nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
$nights = max($nights, 1);

$room_config = [
    1 => ["stok" => 5, "harga" => 250000],
    2 => ["stok" => 12, "harga" => 250000],
    3 => ["stok" => 4, "harga" => 500000]
];

$guest_name = $conn->real_escape_string($guest['title'] . ' ' . $guest['first_name'] . ' ' . $guest['last_name']);
$email = $conn->real_escape_string($guest['email']);
$phone = $conn->real_escape_string($guest['phone']);

$total_all = 0;
$pesan = "Reservasi Baru\n\n";
$pesan .= "Nama: $guest_name\n";

$valid = false;
foreach ($room_ids as $rid) {
    if (!empty($_POST['jumlah_kamar'][$rid]) && $_POST['jumlah_kamar'][$rid] > 0) {
        $valid = true;
        break;
    }
}
if (!$valid) {
    die("Jumlah kamar tidak valid");
}

$conn->begin_transaction();

$pesan .= "Check-in: $check_in\n";
$pesan .= "Check-out: $check_out\n\n";

// Prepared statements
$stmt_check = $conn->prepare("SELECT SUM(jumlah_kamar) as total FROM bookings WHERE room_id = ? AND status IN ('pending','confirmed') AND check_in < ? AND check_out > ?");
$stmt_insert = $conn->prepare("INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price, jumlah_kamar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt_check || !$stmt_insert) {
    $conn->rollback();
    die("Prepare statement gagal: " . $conn->error);
}

foreach ($room_ids as $room_id) {
    $room_id = (int)$room_id;
    $jumlah_kamar = (int)($_POST['jumlah_kamar'][$room_id] ?? 1);
    if ($jumlah_kamar <= 0) continue;

    if (!isset($room_config[$room_id])) {
        $conn->rollback();
        die("Kamar tidak valid");
    }

    $stok = $room_config[$room_id]['stok'];
    $harga = $room_config[$room_id]['harga'];

    // Cek ketersediaan
    $stmt_check->bind_param("iss", $room_id, $check_out, $check_in);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_assoc();
    $total_booking = $data['total'] ?? 0;

    if (($total_booking + $jumlah_kamar) > $stok) {
        $conn->rollback();
        die("Kamar ID $room_id tidak cukup (sisa: " . ($stok - $total_booking) . ")");
    }

    $total_price = $harga * $nights * $jumlah_kamar;
    $total_all += $total_price;

    // Simpan booking
    $stmt_insert->bind_param("isssssdi", $room_id, $guest_name, $email, $phone, $check_in, $check_out, $total_price, $jumlah_kamar);
    if (!$stmt_insert->execute()) {
        $conn->rollback();
        die("Gagal menyimpan booking: " . $stmt_insert->error);
    }

    $kamar = $room_id == 1 ? "Standard Double Bed" : ($room_id == 2 ? "Standard Twin Bed" : "Family Room");
    $pesan .= "\nKamar: $kamar";
    $pesan .= "\nJumlah: $jumlah_kamar\n";
}

$conn->commit();

$pesan .= "\nTotal: Rp " . number_format($total_all, 0, ',', '.');
$pesan = urlencode($pesan);
$no_wa = "6282387037225";

header("Location: https://wa.me/$no_wa?text=$pesan");
exit;
?>