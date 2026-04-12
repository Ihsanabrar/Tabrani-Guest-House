<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['guest_data']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: reservasi.php');
    exit;
}

$room_id = (int)$_POST['room_id'];
$nights = (int)$_POST['nights'];
$guest = $_SESSION['guest_data'];
$check_in = $guest['check_in'];
$check_out = $guest['check_out'];

// Cek ulang ketersediaan
$sql_check = "SELECT id FROM bookings 
              WHERE room_id = $room_id AND status IN ('pending','confirmed')
              AND check_in < '$check_out' AND check_out > '$check_in'";
$check_result = $conn->query($sql_check);
if ($check_result->num_rows > 0) {
    // Kamar penuh
    echo "<!DOCTYPE html>
          <html>
          <head><title>Kamar Penuh</title>
          <script>
              alert('Kamar sudah penuh!');
              setTimeout(function() { window.location.href = 'reservasi.php'; }, 5000);
          </script>
          </head>
          <body><p>Kamar sudah dipesan. Anda akan dialihkan ke halaman utama.</p></body>
          </html>";
    exit;
}

// Ambil harga kamar
$sql_room = "SELECT price_per_night, room_name FROM rooms WHERE id = $room_id";
$room = $conn->query($sql_room)->fetch_assoc();
$total_price = $room['price_per_night'] * $nights;

// Simpan booking
$guest_name = $conn->real_escape_string($guest['title'] . ' ' . $guest['first_name'] . ' ' . $guest['last_name']);
$sql_insert = "INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price)
               VALUES ($room_id, '$guest_name', '{$guest['email']}', '{$guest['phone']}', '$check_in', '$check_out', $total_price)";
if ($conn->query($sql_insert)) {
    $booking_id = $conn->insert_id;
    header('Location: confirm.php?id=' . $booking_id);
    exit;
} else {
    echo "Error: " . $conn->error;
}
?>