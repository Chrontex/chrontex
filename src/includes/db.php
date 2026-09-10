<?php
require_once __DIR__ . '/../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (DB_SOCKET) {
        $conn = new mysqli(null, DB_USER, DB_PASS, DB_NAME, null, DB_SOCKET);
    } elseif (DB_SSL) {
        $conn = mysqli_init();
        $conn->ssl_set(null, null, null, null, null);
        $conn->real_connect(
            DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT, null,
            MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
        );
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>