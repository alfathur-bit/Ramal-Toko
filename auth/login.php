<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// koneksi database
$conn = mysqli_connect("localhost","root","","RamalToko_db");

if(!$conn){
    die("Koneksi database gagal");
}

// proses login
if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek username dan password langsung (admin/admin)
    if($username == 'admin' && $password == 'admin'){
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = 1;
        header("Location: ../pages/dashboard.php");
        exit;
    }
    
    // Alternatif: cek dari database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if($row = mysqli_fetch_assoc($query)){
        // Cek apakah password hash atau md5
        if(substr($row['password'], 0, 2) === '$2'){
            if(password_verify($password, $row['password'])){
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $row['id'];
                header("Location: ../pages/dashboard.php");
                exit;
            }
        } else {
            if(md5($password) === $row['password']){
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $row['id'];
                header("Location: ../pages/dashboard.php");
                exit;
            }
        }
    }
    $error = "Username atau password salah";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; }
    </style>
    <link href="/RamalToko/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="login-box">
    <h3 class="text-center mb-4">Login Admin</h3>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
