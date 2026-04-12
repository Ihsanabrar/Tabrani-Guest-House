<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin_login.php');
    exit;
}
include 'db_config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM bookings WHERE id = $id";
    if ($conn->query($sql)) {
        header('Location: admin.php?msg=deleted');
        exit;
    } else {
        echo "Gagal menghapus: " . $conn->error;
    }
} else {
    header('Location: admin.php');
}
?>