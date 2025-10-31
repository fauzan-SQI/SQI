<?php
// Script untuk membuat user admin dengan password hash yang benar

// Ganti dengan password yang Anda inginkan
$password = 'admin123';

// Generate hash
$hash = password_hash($password, PASSWORD_ARGON2ID);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

echo "\nSQL Query untuk membuat admin user:\n";
echo "INSERT INTO users (username, email, password_hash) VALUES ('admin', 'admin@sqi.local', '" . $hash . "');\n";
?>