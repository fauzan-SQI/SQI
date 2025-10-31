<?php
// auth/logout.php - Fungsi logout pengguna

// Include konfigurasi otentikasi yang sudah menangani semua ketergantungan dengan aman
require_once 'config.php';

// Lakukan logout
logoutUser();

// Redirect ke halaman login
header("Location: login.php?message=logout_success");
exit();
?>