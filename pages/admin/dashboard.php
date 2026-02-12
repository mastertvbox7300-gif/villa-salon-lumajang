<?php
/**
 * ADMIN DASHBOARD
 */

require_once '../../config/config.php';
require_login();
require_permission('admin');

// Get data
$month = date('m');
$year = date('Y');
$monthly_report = get_monthly_report($year, $month);

// Get all users
$query = "SELECT * FROM users WHERE status = 'active'";
$all_users = query_select($query);

$total_users = count($all_users);
$therapists = array_filter($all_users, fn($u) => $u['role'] === 'terapis');
$kasirs = array_filter($all_users, fn($u) => $u['role'] === 'kasir');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Villa Salon</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Admin Panel</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php">Layanan</a></li>
            <li><a href="therapists.php">Terapis</a></li>
            <li><a href="users.php">User Management</a></li>
            <li><a href="members.php">Member</a></li>
            <li><a href="expenses.php">Pengeluaran</a></li>
            <li><a href="settings.php">Pengaturan</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>Admin Dashboard</h1>
                <p>Selamat datang di panel kontrol Villa Salon!</p>
                
                <!-- STATISTIK UTAMA -->
                <h3 style="margin-top: 30px;">📊 Statistik</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total User</div>
                            <div class="card-body text-center">
                                <h2><?php echo $total_users; ?></h2>
                                <p>Pengguna Aktif</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Terapis</div>
                            <div class="card-body text-center">
                                <h2><?php echo count($therapists); ?></h2>
                                <p>Terapis Aktif</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Kasir</div>
                            <div class="card-body text-center">
                                <h2><?php echo count($kasirs); ?></h2>
                                <p>Kasir Aktif</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Data Bulan Ini</div>
                            <div class="card-body text-center">
                                <h2><?php echo $monthly_report['total_transaction'] ?? 0; ?></h2>
                                <p>Transaksi</p>
                                <small style="margin-top: 10px; display: block;">
                                    Rp <?php echo number_format($monthly_report['cash_income'] ?? 0, 0, ',', '.'); ?> (Tunai) + 
                                    Rp <?php echo number_format($monthly_report['qris_income'] ?? 0, 0, ',', '.'); ?> (QRIS)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- MENU MANAJEMEN -->
                <h3 style="margin-top: 30px;">⚙️ Menu Manajemen</h3>
                <div class="kiosk-grid">
                    <a href="services.php" class="kiosk-btn btn-primary">
                        <div class="icon">💇</div>
                        <strong>Layanan</strong>
                        <small style="font-size: 0.8rem;">Tambah/Edit Layanan</small>
                    </a>
                    
                    <a href="therapists.php" class="kiosk-btn btn-secondary">
                        <div class="icon">👥</div>
                        <strong>Terapis</strong>
                        <small style="font-size: 0.8rem;">Kelola Terapis</small>
                    </a>
                    
                    <a href="users.php" class="kiosk-btn btn-info">
                        <div class="icon">👤</div>
                        <strong>User</strong>
                        <small style="font-size: 0.8rem;">Manajemen User</small>
                    </a>
                    
                    <a href="members.php" class="kiosk-btn btn-warning">
                        <div class="icon">💳</div>
                        <strong>Member</strong>
                        <small style="font-size: 0.8rem;">Data Member</small>
                    </a>
                    
                    <a href="expenses.php" class="kiosk-btn btn-danger">
                        <div class="icon">💰</div>
                        <strong>Pengeluaran</strong>
                        <small style="font-size: 0.8rem;">Catat Pengeluaran</small>
                    </a>
                    
                    <a href="settings.php" class="kiosk-btn btn-dark" style="background-color: #666;">
                        <div class="icon">⚙️</div>
                        <strong>Pengaturan</strong>
                        <small style="font-size: 0.8rem;">Konfigurasi Sistem</small>
                    </a>
                </div>
                
                <!-- INFO SISTEM -->
                <h3 style="margin-top: 30px;">ℹ️ Informasi Sistem</h3>
                <div class="card">
                    <div class="card-body">
                        <p><strong>Nama Aplikasi:</strong> Villa Salon Lumajang Management System</p>
                        <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                        <p><strong>Server:</strong> <?php echo APP_URL; ?></p>
                        <p><strong>Timezone:</strong> <?php echo APP_TIMEZONE; ?></p>
                        <p style="margin-top: 20px; color: #999;">
                            <small>© 2024 Villa Salon Lumajang. All rights reserved.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
