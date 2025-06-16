<?php
$host = 'localhost';
$db = 'confession_wall'; // ✅ <-- CHANGE this to your actual database name
$user = 'root';          // Default username in XAMPP
$pass = '';              // Default password in XAMPP (usually empty)
$charset = 'utf8mb4';    // Use utf8 if this causes charset errors

$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // now uses the defined $db

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
