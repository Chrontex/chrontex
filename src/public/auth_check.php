<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_role($required_role) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    if ($_SESSION['role'] !== $required_role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
