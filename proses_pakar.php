<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require_once 'koneksi.php';

$id_kriteria = isset($_POST['id_kriteria']) ? intval($_POST['id_kriteria']) : 1;
$budget      = isset($_POST['budget']) ? intval($_POST['budget']) : 15000000;

// 1. Ambil Bobot Pakar (W) dari tabel tb_kriteria
$sql_kriteria = "SELECT * FROM tb_kriteria WHERE id_kriteria = $id_kriteria";
$res_kriteria = $conn->query($sql_kriteria);

if (!$res_kriteria || $res_kriteria->num_rows == 0) {
    $res_kriteria = $conn->query("SELECT * FROM tb_kriteria LIMIT 1");
}

$kriteria = $res_kriteria->fetch_assoc();
$w1 = floatval($kriteria['bobot_gaming']);
$w2 = floatval($kriteria['bobot_rendering']);
$w3 = floatval($kriteria['bobot_value']);

// 2. Ambil Semua Komponen & Hitung Skor Weighting (W x S)
$sql_komponen = "SELECT * FROM tb_komponen";
$res_komponen = $conn->query($sql_komponen);

$katalog = [];
while ($row = $res_komponen->fetch_assoc()) {
    $kat = $row['kategori'];
    if (!isset($katalog[$kat])) {
        $katalog[$kat] = [];
    }

    $s1 = intval($row['skor_gaming']);
    $s2 = intval($row['skor_rendering']);
    $s3 = intval($row['skor_value']);

    // RUMUS INTI METODE WEIGHTING: Total = (W1*S1) + (W2*S2) + (W3*S3)
    $skor_w1 = round($w1 * $s1, 2);
    $skor_w2 = round($w2 * $s2, 2);
    $skor_w3 = round($w3 * $s3, 2);
    $total_skor = round($skor_w1 + $skor_w2 + $skor_w3, 2);

    $row['harga']      = intval($row['harga']);
    $row['skor_w1']    = $skor_w1;
    $row['skor_w2']    = $skor_w2;
    $row['skor_w3']    = $skor_w3;
    $row['total_skor'] = $total_skor;
    $row['rumus_teks'] = "($w1 × $s1) + ($w2 × $s2) + ($w3 × $s3) = $total_skor";

    $katalog[$kat][] = $row;
}

$kategori_tersedia = array_keys($katalog);

// 3. Urutkan setiap kategori berdasarkan Total Skor Tertinggi (Descending)
foreach ($kategori_tersedia as $kat) {
    usort($katalog[$kat], function($a, $b) {
        if ($b['total_skor'] == $a['total_skor']) {
            return $a['harga'] <=> $b['harga'];
        }
        return $b['total_skor'] <=> $a['total_skor'];
    });
}

// 4. Cari komponen paling murah di tiap kategori sebagai batas anggaran minimum
$rakitan_terpilih = [];
$total_harga_minimum = 0;

foreach ($kategori_tersedia as $kat) {
    $komponen_termurah = null;
    foreach ($katalog[$kat] as $item) {
        if ($komponen_termurah === null || $item['harga'] < $komponen_termurah['harga']) {
            $komponen_termurah = $item;
        }
    }
    $rakitan_terpilih[$kat] = $komponen_termurah;
    $total_harga_minimum += $komponen_termurah['harga'];
}

if ($budget < $total_harga_minimum) {
    echo json_encode([
        "status" => "budget_kurang",
        "pesan"  => "Anggaran Rp " . number_format($budget, 0, ',', '.') . " belum cukup untuk merakit 1 set PC lengkap (" . count($kategori_tersedia) . " komponen). Minimal anggaran adalah Rp " . number_format($total_harga_minimum, 0, ',', '.') . ".",
        "min_budget" => $total_harga_minimum
    ]);
    exit;
}

// 5. Iterasi Upgrade Pintar berdasarkan kenaikan Skor Weighting tertinggi
$sisa_budget = $budget - $total_harga_minimum;
$masih_bisa_upgrade = true;

while ($masih_bisa_upgrade) {
    $masih_bisa_upgrade = false;
    $kandidat_terbaik_kat = null;
    $kandidat_terbaik_item = null;
    $kenaikan_skor_tertinggi = 0;

    foreach ($kategori_tersedia as $kat) {
        $item_sekarang = $rakitan_terpilih[$kat];
        foreach ($katalog[$kat] as $opsi_komponen) {
            $selisih_harga = $opsi_komponen['harga'] - $item_sekarang['harga'];
            $selisih_skor  = $opsi_komponen['total_skor'] - $item_sekarang['total_skor'];

            if ($selisih_skor > 0 && $selisih_harga <= $sisa_budget) {
                if ($selisih_skor > $kenaikan_skor_tertinggi) {
                    $kenaikan_skor_tertinggi = $selisih_skor;
                    $kandidat_terbaik_kat = $kat;
                    $kandidat_terbaik_item = $opsi_komponen;
                }
            }
        }
    }

    if ($kandidat_terbaik_kat !== null) {
        $selisih_bayar = $kandidat_terbaik_item['harga'] - $rakitan_terpilih[$kandidat_terbaik_kat]['harga'];
        $rakitan_terpilih[$kandidat_terbaik_kat] = $kandidat_terbaik_item;
        $sisa_budget -= $selisih_bayar;
        $masih_bisa_upgrade = true;
    }
}

$total_biaya_rakitan = 0;
$total_skor_rakitan  = 0;
$daftar_rakitan_final = [];

foreach ($kategori_tersedia as $kat) {
    $komp = $rakitan_terpilih[$kat];
    $total_biaya_rakitan += $komp['harga'];
    $total_skor_rakitan  += $komp['total_skor'];
    $daftar_rakitan_final[] = $komp;
}

$rata_rata_skor = round($total_skor_rakitan / count($kategori_tersedia), 2);

echo json_encode([
    "status" => "success",
    "kriteria" => $kriteria,
    "budget_input" => $budget,
    "total_harga" => $total_biaya_rakitan,
    "sisa_budget" => $budget - $total_biaya_rakitan,
    "total_skor_akumulasi" => round($total_skor_rakitan, 2),
    "rata_rata_skor" => $rata_rata_skor,
    "rakitan" => $daftar_rakitan_final,
    "semua_katalog" => $katalog
]);
?>
