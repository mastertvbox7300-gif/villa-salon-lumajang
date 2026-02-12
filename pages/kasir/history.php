<?php
/**
 * KASIR - RIWAYAT TRANSAKSI
 */

require_once '../../config/config.php';
require_login();
require_permission('kasir');

// Get transaction history
$query = "SELECT t.*, u.name as therapist_name, s.name as service_name, m.name as member_name
          FROM transactions t
          JOIN users u ON t.therapist_id = u.id
          JOIN services s ON t.service_id = s.id
          LEFT JOIN members m ON t.member_id = m.id
          ORDER BY t.created_at DESC
          LIMIT 100";
$transactions = query_select($query);

// Statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN payment_method = 'cash' THEN fee ELSE 0 END) as cash_total,
                SUM(CASE WHEN payment_method = 'qris' THEN fee ELSE 0 END) as qris_total,
                SUM(fee) as grand_total
                FROM transactions 
                WHERE DATE(transaction_date) = CURDATE()";
$stats = query_select_one($stats_query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Kasir</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Kasir</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="transaction.php">Transaksi Baru</a></li>
            <li><a href="history.php" class="active">Riwayat</a></li>
            <li><a href="report.php">Laporan</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>📋 Riwayat Transaksi</h1>

                <!-- STATISTIK HARIAN -->
                <h3 style="margin-top: 30px;">📊 Statistik Hari Ini</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Transaksi</div>
                            <div class="card-body text-center">
                                <h2><?php echo $stats['total'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Tunai</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($stats['cash_total'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">QRIS</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($stats['qris_total'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Pendapatan</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($stats['grand_total'] ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR TRANSAKSI -->
                <h3 style="margin-top: 30px;">📝 Daftar Transaksi</h3>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Layanan</th>
                                <th>Terapis</th>
                                <th>Member</th>
                                <th>Biaya</th>
                                <th>Metode</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td><?php echo format_date($trans['transaction_date']); ?></td>
                                    <td><?php echo date('H:i', strtotime($trans['created_at'])); ?></td>
                                    <td><strong><?php echo $trans['service_name']; ?></strong></td>
                                    <td><?php echo $trans['therapist_name']; ?></td>
                                    <td><?php echo $trans['member_name'] ?? '-'; ?></td>
                                    <td><?php echo format_currency($trans['fee']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $trans['payment_method'] === 'cash' ? 'badge-warning' : 'badge-info'; ?>">
                                            <?php echo strtoupper($trans['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?php echo ucfirst($trans['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
