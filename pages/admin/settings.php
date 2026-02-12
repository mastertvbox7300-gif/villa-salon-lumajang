<?php
/**
 * ADMIN - PENGATURAN SISTEM
 */

require_once '../../config/config.php';
require_login();
require_permission('admin');

$success = get_success();
$error = get_error();

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_password = sanitize($_POST['old_password'] ?? '');
    $new_password = sanitize($_POST['new_password'] ?? '');
    $confirm_password = sanitize($_POST['confirm_password'] ?? '');
    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru tidak cocok!';
    } elseif (md5($old_password) !== md5(APP_SETTING('admin_password'))) {
        $error = 'Password lama salah!';
    } else {
        $query = "UPDATE users SET password = ? WHERE id = ?";
        if (query_execute($query, [md5($new_password), get_current_user_id()])) {
            set_success('Password admin berhasil diubah!');
            header('Location: settings.php');
            exit;
        }
    }
}

// Handle update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $salon_name = sanitize($_POST['salon_name'] ?? '');
    $salon_phone = sanitize($_POST['salon_phone'] ?? '');
    $salon_address = sanitize($_POST['salon_address'] ?? '');
    $salon_city = sanitize($_POST['salon_city'] ?? '');
    
    if (!empty($salon_name)) {
        // Update atau insert settings
        query_execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", 
                     ['salon_name', $salon_name, $salon_name]);
        query_execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", 
                     ['salon_phone', $salon_phone, $salon_phone]);
        query_execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", 
                     ['salon_address', $salon_address, $salon_address]);
        query_execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", 
                     ['salon_city', $salon_city, $salon_city]);
        
        set_success('Pengaturan salon berhasil disimpan!');
        header('Location: settings.php');
        exit;
    }
}

// Get current settings
$query = "SELECT * FROM settings";
$all_settings = query_select($query);
$settings = [];
foreach ($all_settings as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Admin</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Admin</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php">Layanan</a></li>
            <li><a href="therapists.php">Terapis</a></li>
            <li><a href="users.php">User</a></li>
            <li><a href="settings.php" class="active">Settings</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>⚙️ Pengaturan Sistem</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- PENGATURAN SALON -->
                <div class="card">
                    <div class="card-header">🏢 Pengaturan Salon</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="save_settings">
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nama Salon*</label>
                                        <input type="text" name="salon_name" class="form-control" 
                                               value="<?php echo $settings['salon_name'] ?? 'Villa Salon'; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>No. Telp Salon</label>
                                        <input type="text" name="salon_phone" class="form-control" 
                                               value="<?php echo $settings['salon_phone'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <input type="text" name="salon_address" class="form-control" 
                                               value="<?php echo $settings['salon_address'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <input type="text" name="salon_city" class="form-control" 
                                               value="<?php echo $settings['salon_city'] ?? 'Lumajang'; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan</button>
                        </form>
                    </div>
                </div>

                <!-- GANTI PASSWORD ADMIN -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">🔐 Ganti Password Admin</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Password Lama*</label>
                                        <input type="password" name="old_password" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Password Baru*</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Konfirmasi Password*</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">🔄 Ubah Password</button>
                        </form>
                    </div>
                </div>

                <!-- INFO SISTEM -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">ℹ️ Informasi Sistem</div>
                    <div class="card-body">
                        <p><strong>Versi PHP:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                        <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                        <p><strong>Timezone:</strong> <?php echo APP_TIMEZONE; ?></p>
                        <hr>
                        <p style="color: #666; font-size: 0.9rem;">
                            <strong>⚠️ Security Tips:</strong> Pastikan password admin sudah diubah dari default value!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
