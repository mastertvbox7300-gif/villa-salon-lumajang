<?php
/**
 * LOGIN PAGE
 */

// Include config
require_once '../config/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = get_current_user_role();
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($role === 'kasir') {
        header('Location: kasir/dashboard.php');
    } elseif ($role === 'terapis') {
        header('Location: terapis/dashboard.php');
    }
    exit;
}

// Process login
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong';
    } else {
        // Check user
        $query = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $user = query_select_one($query, [$username]);
        
        if ($user && md5($password) === $user['password']) {
            // Login success
            set_session('user_id', $user['id']);
            set_session('user_role', $user['role']);
            set_session('user_name', $user['name']);
            set_session('login_time', time());
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } elseif ($user['role'] === 'kasir') {
                header('Location: kasir/dashboard.php');
            } elseif ($user['role'] === 'terapis') {
                header('Location: terapis/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN - Villa Salon Lumajang</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🏢 Villa Salon</h1>
                <p>Lumajang</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                    <small>Admin: admin | Kasir: kasir1 | Terapis: siti/rina/dewi</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small>Admin: 1234 | Kasir/Terapis: kasir123 / terapis123</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">MASUK</button>
            </form>
            
            <div class="login-footer">
                <p>© 2024 Villa Salon Lumajang</p>
            </div>
        </div>
    </div>
</body>
</html>
