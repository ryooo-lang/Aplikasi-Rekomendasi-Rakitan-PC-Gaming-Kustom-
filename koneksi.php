<?php
error_reporting(0);
ini_set('display_errors', 0);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_pc_expert";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "pesan"  => "Koneksi MySQL gagal: Pastikan MySQL di XAMPP sudah di-Start."
    ]));
}

// Buat database otomatis jika belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
$conn->select_db($db);

// Buat tabel tb_kriteria otomatis
$conn->query("CREATE TABLE IF NOT EXISTS tb_kriteria (
    id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
    profil_pengguna VARCHAR(100) NOT NULL,
    bobot_gaming DECIMAL(3,2) NOT NULL,
    bobot_rendering DECIMAL(3,2) NOT NULL,
    bobot_value DECIMAL(3,2) NOT NULL
)");

// Buat tabel tb_komponen otomatis
$conn->query("CREATE TABLE IF NOT EXISTS tb_komponen (
    id_komponen INT AUTO_INCREMENT PRIMARY KEY,
    nama_komponen VARCHAR(100) NOT NULL,
    kategori ENUM('CPU', 'GPU', 'RAM', 'Motherboard', 'Storage', 'PSU') NOT NULL,
    harga INT NOT NULL,
    skor_gaming INT NOT NULL,
    skor_rendering INT NOT NULL,
    skor_value INT NOT NULL
)");

$conn->query("ALTER TABLE tb_komponen MODIFY COLUMN kategori ENUM('CPU', 'GPU', 'RAM', 'Motherboard', 'Storage', 'PSU') NOT NULL");

// Isi data kriteria jika kosong
$cek_kriteria = $conn->query("SELECT COUNT(*) as total FROM tb_kriteria")->fetch_assoc();
if ($cek_kriteria['total'] == 0) {
    $conn->query("INSERT INTO tb_kriteria (profil_pengguna, bobot_gaming, bobot_rendering, bobot_value) VALUES
    ('Gaming Kompetitif (Fokus FPS & Low Latency)', 0.60, 0.20, 0.20),
    ('Kreator Konten (Fokus 3D, Video & Multitasking)', 0.20, 0.60, 0.20),
    ('Kere Hore / Budget (Fokus Rasio Harga & Performa)', 0.30, 0.10, 0.60)");
}

// Isi data komponen jika belum lengkap 6 kategori
$cek_komponen = $conn->query("SELECT COUNT(DISTINCT kategori) as jml_kat FROM tb_komponen")->fetch_assoc();
if ($cek_komponen['jml_kat'] < 6) {
    $conn->query("TRUNCATE TABLE tb_komponen");
    $conn->query("INSERT INTO tb_komponen (nama_komponen, kategori, harga, skor_gaming, skor_rendering, skor_value) VALUES
    ('Intel Core i3-12100F (4 Core / 8 Thread)', 'CPU', 1350000, 70, 55, 95),
    ('AMD Ryzen 5 5600 (6 Core / 12 Thread)', 'CPU', 1850000, 82, 75, 92),
    ('AMD Ryzen 5 7600X (6 Core / 12 Thread AM5)', 'CPU', 3400000, 90, 82, 85),
    ('Intel Core i7-13700K (16 Core / 24 Thread)', 'CPU', 6500000, 95, 98, 72),
    ('AMD Ryzen 7 7800X3D (8 Core / 16 Thread 3D V-Cache)', 'CPU', 7100000, 100, 85, 78),
    ('AMD Radeon RX 6500 XT 4GB GDDR6', 'GPU', 2100000, 62, 50, 88),
    ('AMD Radeon RX 6600 8GB GDDR6', 'GPU', 3100000, 78, 70, 96),
    ('NVIDIA GeForce RTX 4060 8GB GDDR6', 'GPU', 4850000, 85, 82, 88),
    ('AMD Radeon RX 7800 XT 16GB GDDR6', 'GPU', 8800000, 92, 86, 84),
    ('NVIDIA GeForce RTX 4070 Super 12GB GDDR6X', 'GPU', 10500000, 96, 95, 78),
    ('ASRock H610M-HDV/M.2', 'Motherboard', 1050000, 65, 65, 94),
    ('MSI B550M PRO-VDH WIFI', 'Motherboard', 1650000, 78, 80, 90),
    ('ASRock B650M PG Lightning', 'Motherboard', 2250000, 85, 85, 86),
    ('MSI MAG B650 Tomahawk WiFi', 'Motherboard', 3750000, 95, 95, 76),
    ('Kingston Fury Beast 16GB (2x8GB) DDR4 3200MHz', 'RAM', 620000, 72, 68, 95),
    ('Corsair Vengeance LPX 32GB (2x16GB) DDR4 3600MHz', 'RAM', 1150000, 82, 88, 90),
    ('TeamGroup T-Force Delta RGB 32GB (2x16GB) DDR5 6000MHz', 'RAM', 1850000, 94, 92, 84),
    ('G.Skill Trident Z5 Neo RGB 64GB (2x32GB) DDR5 6000MHz', 'RAM', 3600000, 98, 100, 70),
    ('ADATA Legend 710 512GB NVMe PCIe Gen3', 'Storage', 520000, 68, 65, 95),
    ('TeamGroup MP44L 1TB NVMe PCIe Gen4', 'Storage', 1050000, 85, 85, 92),
    ('Samsung 990 PRO 1TB NVMe PCIe Gen4', 'Storage', 1850000, 96, 98, 78),
    ('WD Black SN850X 2TB NVMe PCIe Gen4', 'Storage', 2850000, 98, 98, 75),
    ('Deepcool PK550D 550W 80+ Bronze', 'PSU', 680000, 70, 70, 94),
    ('FSP HV PRO 650W 80+ Bronze', 'PSU', 850000, 78, 78, 90),
    ('MSI MAG A750GL PCIE5 750W 80+ Gold Modular', 'PSU', 1550000, 92, 92, 85),
    ('Corsair RM850e 850W 80+ Gold ATX 3.0 Modular', 'PSU', 2100000, 98, 98, 76)");
}
?>
