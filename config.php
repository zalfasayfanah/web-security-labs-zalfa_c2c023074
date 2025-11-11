<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_PORT', 3307); // gunakan port 3307
define('DB_USER', 'root');
define('DB_PASS', ''); // Kosongkan jika tidak ada password
define('DB_NAME', 'security_lab');

// Koneksi menggunakan MySQLi (untuk query vulnerable)
// new mysqli(host, user, pass, db, port)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Koneksi database gagal (MySQLi): " . $conn->connect_error);
}

// Koneksi menggunakan PDO (untuk prepared statements)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}

// Start session untuk semua halaman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function untuk escape output (XSS prevention)
function safe_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
