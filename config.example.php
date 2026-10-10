<?php
$host = 'YOUR_DB_HOST';       // Ganti dengan IP/Host database Anda
$user = 'YOUR_DB_USER';       // Ganti dengan username database Anda
$pass = 'YOUR_DB_PASSWORD';   // Ganti dengan password database Anda
$db   = 'troubleshooting';    // Nama database

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
