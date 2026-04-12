<?php
session_start();
include 'db_config.php';

if (!isset($_GET['id'])) {
    header('Location: reservasi.php');
    exit;
}

$booking_id = (int)$_GET['id'];
$sql = "DELETE FROM bookings WHERE id = $booking_id AND status = 'pending'";
if ($conn->query($sql) && $conn->affected_rows > 0) {
    echo "<script>alert('Pemesanan dibatalkan. Kamar tersedia kembali.'); window.location.href='reservasi.php';</script>";
} else {
    echo "<script>alert('Gagal membatalkan.'); window.location.href='reservasi.php';</script>";
}
?>