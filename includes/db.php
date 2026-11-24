<?php
// Database connection (PDO + prepared statements)
// XAMPP default: user = root, password = empty
$host = 'localhost';
$dbname = 'coral_nest';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed. Check XAMPP MySQL and includes/db.php');
}
