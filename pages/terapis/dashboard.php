<?php
/**
 * TERAPIS DASHBOARD
 */

require_once '../../config/config.php';
require_login();
require_permission('terapis');

$therapist_id = get_current_user_id();

// Get commission balance
$commission_balance = get_therapist_commission_balance($therapist_id);
$balance = $commission_balance['balance'] ?? 0;
$total_withdrawn = $commission_balance['total_withdrawn'] ?? 0;
$pending = $commission_balance['pending_count'] ?? 0;

// Get pending commissions
$pending_commissions = get_pending_commission($therapist_id);

// Get therapist status
$therapist_status = get_therapist_status($therapist_id);
$current_status = $therapist_status['status'] ?? 'available';

// Handle mark as done
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_done' && isset($_POST['commission_id'])) {
        $commission_id = sanitize($_POST['commission_id']);
        $query = "UPDATE therapist_commission SET status = 'withdrawn' WHERE id = ?";
        if (query_execute($query, [$commission_id])) {
            set_success('Tugas ditandai selesai!');
            header('Location: dashboard.php');
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Terapis - Villa Salon</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Terapis</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="task.php">Tugas</a></li>
            <li><a href="commission.php">Komisi</a></li>
            <li><a href="history.php">Riwayat</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <span class="badge <?php echo $current_status === 'available' ? 'badge-success' : 'badge-warning'; ?>" style="margin: 0 10px;">
                Status: <?php echo strtoupper($current_status); ?>
            </span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>Dashboard Terapis</h1>
                <p>Selamat datang, <?php echo get_current_user_name(); ?>!</p>
                
                <!-- STATUS TERAPIS -->
                <h3 style="margin-top: 30px;">👤 Status Anda</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Status Kerja</div>
                            <div class="card-body text-center">
                                <h2 style="color: var(--primary-color);">
                                    <?php 
                                    if ($current_status === 'available') echo '🟢';
                                    elseif ($current_status === 'busy') echo '🟡';
                                    else echo '⭕';
                                    ?>
                                </h2>
                                <p><?php echo ucfirst($current_status); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Komisi Terkumpul</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($balance); ?></h2>
                                <p>Belum Diambil</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Komisi Diambil</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($total_withdrawn); ?></h2>
                                <p>Total Pencairan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Tugas Pending</div>
                            <div class="card-body text-center">
                                <h2><?php echo $pending; ?></h2>
                                <p>Belum Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- TOMBOL AKSI CEPAT -->
                <h3 style="margin-top: 30px;">⚡ Aksi Cepat</h3>
                <div class="btn-group">
                    <a href="task.php" class="btn btn-primary btn-lg">
                        📋 Lihat Tugas
                    </a>
                    <a href="commission.php" class="btn btn-success btn-lg">
                        💰 Ambil Komisi
                    </a>
                    <a href="history.php" class="btn btn-info btn-lg">
                        📜 Riwayat
                    </a>
                </div>
                
                <!-- TUGAS PENDING -->
                <h3 style="margin-top: 30px;">📝 Tugas Yang Sedang Berjalan</h3>
                <?php if (!empty($pending_commissions)): ?>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Layanan</th>
                                    <th>Komisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $limit = array_slice($pending_commissions, 0, 5);
                                foreach ($limit as $commission): 
                                ?>
                                    <tr>
                                        <td><?php echo format_date($commission['accumulated_date']); ?></td>
                                        <td><?php echo $commission['service_name']; ?></td>
                                        <td><?php echo format_currency($commission['commission_amount']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="mark_done">
                                                <input type="hidden" name="commission_id" value="<?php echo $commission['id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">Selesai</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Tidak ada tugas pending</div>
                <?php endif; ?>
                
                <!-- INFO KOMISI -->
                <h3 style="margin-top: 30px;">💡 Info Komisi</h3>
                <div class="card">
                    <div class="card-body">
                        <p><strong>📌 Cara Kerja Komisi:</strong></p>
                        <ul style="margin: 15px 0; padding-left: 20px;">
                            <li>Setiap layanan yang Anda tangani akan menghasilkan komisi</li>
                            <li>Komisi akan terkumpul ketika Anda tekan tombol "SELESAI"</li>
                            <li>Komisi dapat diambil kapan saja melalui menu "Ambil Komisi"</li>
                            <li>Status Anda akan otomatis berubah menjadi "Tersedia" setelah transaksi selesai</li>
                        </ul>
                        <p style="margin-top: 20px; color: #666;">
                            <strong>Saldo Komisi Anda Saat Ini:</strong><br>
                            Terkumpul: <?php echo format_currency($balance); ?> | 
                            Sudah Diambil: <?php echo format_currency($total_withdrawn); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
