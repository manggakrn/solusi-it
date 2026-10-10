<?php
$host = '172.16.70.12';   
$user = 'navicat';        
$pass = 'passwordku';    
$db   = 'troubleshooting'; // Kembalikan ke nama database kamu

try {
    // Membuat koneksi menggunakan PDO sesuai kebutuhan index.php
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // Mengatur mode error PDO ke Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>