<?php
/**
 * Script untuk reset password user ke format password_hash
 * Jalankan sekali saja, lalu hapus file ini!
 */

$conn = mysqli_connect("localhost", "root", "", "RamalToko_db");

if (!$conn) {
    die("Koneksi gagal");
}

// Ganti 'admin' dengan username yang mau di-reset
// Ganti 'password_anda' dengan password baru
$username = 'admin';
$new_password = 'admin'; // Ganti dengan password baru

$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
mysqli_stmt_bind_param($stmt, "ss", $password_hash, $username);

if (mysqli_stmt_execute($stmt)) {
    echo "Password berhasil diupdate!<br>";
    echo "Username: $username<br>";
    echo "Password baru: $new_password<br>";
    echo "Hash: " . $password_hash;
} else {
    echo "Gagal update password";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
