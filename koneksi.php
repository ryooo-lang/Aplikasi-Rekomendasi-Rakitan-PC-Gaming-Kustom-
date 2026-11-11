<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_pc_expert";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "pesan"  => "Koneksi database gagal: " . $conn->connect_error
    ]));
}
?>