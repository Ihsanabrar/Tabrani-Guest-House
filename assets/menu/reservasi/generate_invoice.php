<?php
require_once('fpdf.php');
include 'db_config.php';

$ids = $_GET['ids'] ?? '';
if (!$ids) die("ID booking tidak ditemukan");

$id_arr = explode(',', $ids);
$id_arr = array_map('intval', $id_arr);
$id_list = implode(',', $id_arr);

// Ambil data booking dari database
$sql = "SELECT b.*, r.room_name, r.price_per_night, b.booking_date 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.id IN ($id_list)";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) die("Data booking tidak ditemukan");

$bookings = [];
$total_all = 0;
$guest_name = $guest_email = $guest_phone = $check_in = $check_out = $booking_date = '';
$nights = 0;

while ($row = $result->fetch_assoc()) {
    if (empty($guest_name)) {
        $guest_name = $row['guest_name'];
        $guest_email = $row['guest_email'];
        $guest_phone = $row['guest_phone'];
        $check_in = $row['check_in'];
        $check_out = $row['check_out'];
        $booking_date = $row['booking_date'];
        $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
    }
    $subtotal = $row['total_price'];
    $total_all += $subtotal;
    $harga_per_malam = $subtotal / ($row['jumlah_kamar'] * $nights);
    $bookings[] = [
        'nama_kamar' => $row['room_name'],
        'jumlah' => $row['jumlah_kamar'],
        'harga_per_malam' => $harga_per_malam,
        'subtotal' => $subtotal
    ];
}

$invoice_no = 'INV-' . date('Ymd', strtotime($booking_date)) . '-' . strtoupper(substr(md5($id_list), 0, 6));

class PDF extends FPDF
{
    function Header()
    {
        // Logo STCH
        if (file_exists('GUEST_HOUSE/logo-stch.jpeg')) {
            $this->Image('GUEST_HOUSE/logo-stch.jpeg', 10, 6, 30);
        }
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(26, 26, 26);
        $this->Cell(0, 10, 'TABRANI GUEST HOUSE', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Jl. Bakti No.32, Tengkerang Barat, Marpoyan Damai, Pekanbaru City, Riau 28289', 0, 1, 'C');
        $this->Cell(0, 5, 'Telp: 0811-763-566 | Email: susianatabrani.conventionhall@gmail.com', 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(197, 160, 89);
        $this->Line(10, 30, 200, 30);
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Terima kasih telah menginap di Tabrani Guest House', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'INVOICE PEMESANAN KAMAR', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Nomor Invoice: ' . $invoice_no, 0, 1, 'C');
$pdf->Ln(10);

// Data pemesan
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Data Pemesan:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, 'Nama Lengkap', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, htmlspecialchars($guest_name), 0, 1);
$pdf->Cell(40, 7, 'Email', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, htmlspecialchars($guest_email), 0, 1);
$pdf->Cell(40, 7, 'Nomor Telepon', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, htmlspecialchars($guest_phone), 0, 1);
$pdf->Ln(5);

// Tanggal
$pdf->Cell(40, 7, 'Tanggal Booking', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d F Y H:i', strtotime($booking_date)), 0, 1);
$pdf->Cell(40, 7, 'Check-in', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d F Y', strtotime($check_in)), 0, 1);
$pdf->Cell(40, 7, 'Check-out', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, date('d F Y', strtotime($check_out)) . ' (' . $nights . ' malam)', 0, 1);
$pdf->Ln(8);

// Tabel kamar
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(197, 160, 89);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Tipe Kamar', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Harga/malam', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Subtotal', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
foreach ($bookings as $room) {
    $pdf->Cell(80, 7, $room['nama_kamar'], 1, 0);
    $pdf->Cell(30, 7, $room['jumlah'] . ' kamar', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Rp ' . number_format($room['harga_per_malam'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(40, 7, 'Rp ' . number_format($room['subtotal'], 0, ',', '.'), 1, 1, 'R');
}

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(150, 8, 'Total Pembayaran', 1, 0, 'R');
$pdf->Cell(40, 8, 'Rp ' . number_format($total_all, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, 'Catatan: Invoice ini adalah bukti pemesanan sementara. Harap melakukan konfirmasi pembayaran ke nomor WhatsApp yang tersedia.', 0, 'C');

$pdf->Output('I', 'Invoice_' . $invoice_no . '.pdf');
?>