<?php
// Database configuration for Ubuntu deployment
$host = 'localhost'; // Changed from 'db' to 'localhost' for native Ubuntu setup
$db   = 'task_manager';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Test the connection
    $pdo->query('SELECT 1');
} catch (\PDOException $e) {
    // More detailed error handling for debugging
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your database configuration.<br>Error: " . $e->getMessage());
}
?>