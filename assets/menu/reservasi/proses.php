<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['guest_data']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: reservasi.php');
    exit;
}

// Cek kolom jumlah_kamar
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'jumlah_kamar'");
if (!$check || $check->num_rows == 0) {
    die("Error: Kolom 'jumlah_kamar' tidak ditemukan. Jalankan: ALTER TABLE `bookings` ADD `jumlah_kamar` INT NOT NULL DEFAULT 1 AFTER `room_id`;");
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
    1 => ["stok" => 5, "harga" => 350000, "nama" => "Standard Room King Bed"],
    2 => ["stok" => 12, "harga" => 350000, "nama" => "Standard Room Twin Bed"],
    3 => ["stok" => 4, "harga" => 500000, "nama" => "Family Room"]
];

$guest_name = $conn->real_escape_string($guest['title'] . ' ' . $guest['first_name'] . ' ' . $guest['last_name']);
$email = $conn->real_escape_string($guest['email']);
$phone = $conn->real_escape_string($guest['phone']);

$total_all = 0;
$booked_rooms = []; // untuk menyimpan detail booking

$conn->begin_transaction();

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
    
    $booking_id = $conn->insert_id;
    $booked_rooms[] = [
        'id' => $booking_id,
        'room_id' => $room_id,
        'nama_kamar' => $room_config[$room_id]['nama'],
        'jumlah' => $jumlah_kamar,
        'harga_per_malam' => $harga,
        'subtotal' => $total_price
    ];
}

$conn->commit();

// Simpan data untuk invoice ke session
$_SESSION['last_booking'] = [
    'booking_ids' => array_column($booked_rooms, 'id'),
    'guest_name' => $guest_name,
    'guest_email' => $email,
    'guest_phone' => $phone,
    'check_in' => $check_in,
    'check_out' => $check_out,
    'nights' => $nights,
    'rooms' => $booked_rooms,
    'total' => $total_all,
    'booking_date' => date('Y-m-d H:i:s')
];

// Redirect ke halaman konfirmasi
header("Location: konfirmasi.php");
exit;
?>