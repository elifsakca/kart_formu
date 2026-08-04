<?php
$host     = "localhost";
$db_name  = "kart_sistemi";
$username = "root";
$password = ""; // XAMPP varsayılan şifre boştur

try {
    $db = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>