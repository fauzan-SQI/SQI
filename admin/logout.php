<?php
// admin/logout.php - Admin logout handler

// Gunakan pendekatan aman untuk memulai sesi
// Cek apakah sesi sudah aktif sebelum memulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all of the admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['login_time']);
unset($_SESSION['is_admin']);

// Also destroy the session jika masih aktif
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?>