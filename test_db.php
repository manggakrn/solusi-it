<?php
$host = '172.16.70.12';
$user = 'navicat';
$pass = 'passwordku';
$db   = 'troubleshooting';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi Berhasil!";
} catch (PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage();
}
?>