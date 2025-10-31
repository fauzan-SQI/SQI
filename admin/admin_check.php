<?php
// admin_check.php - Check if user is logged in as admin

// Gunakan pendekatan aman untuk memulai sesi
// Cek apakah sesi sudah aktif sebelum memulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        // Cek apakah sesi sudah aktif sebelum memeriksa variabel sesi
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
}

if (!function_exists('requireAdminLogin')) {
    function requireAdminLogin() {
        // Cek apakah sesi sudah aktif sebelum memeriksa login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isAdminLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
}

// Check if session is still valid (within 30 minutes)
if (!function_exists('isAdminSessionValid')) {
    function isAdminSessionValid() {
        // Cek apakah sesi sudah aktif sebelum memeriksa variabel sesi
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Sesi berlaku 30 menit
        if (time() - $_SESSION['login_time'] > (30 * 60)) {
            return false;
        }
        
        return true;
    }
}

// Redirect if session is not valid
if (isAdminLoggedIn() && !isAdminSessionValid()) {
    // Cek apakah sesi sudah aktif sebelum menghancurkan
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header("Location: login.php?expired=1");
    exit();
}
?>