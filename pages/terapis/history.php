<?php
/**
 * TERAPIS - RIWAYAT PELANGGAN & KOMISI
 */

require_once '../../config/config.php';
require_login();
require_permission('terapis');

$therapist_id = get_current_user_id();

// Get history with detail
$query = "SELECT t.*, tc.commission_amount, s.name as service_name, m.name as member_name
          FROM transactions t
          JOIN therapist_commission tc ON t.id = tc.transaction_id
          JOIN services s ON t.service_id = s.id
          LEFT JOIN members m ON t.member_id = m.id
          WHERE t.therapist_id = ?
          ORDER BY t.created_at DESC
          LIMIT 100";
$history = query_select($query, [$therapist_id]);

// Statistics
$stats_query = "SELECT 
                COUNT(*) as total_layanan,
                SUM(tc.commission_amount) as total_komisi
                FROM transactions t
                JOIN therapist_commission tc ON t.id = tc.transaction_id
                WHERE t.therapist_id = ?";
$stats = query_select_one($stats_query, [$therapist_id]);

// Layanan terbanyak
$service_query = "SELECT s.name, COUNT(*) as count
                  FROM transactions t
                  JOIN services s ON t.service_id = s.id
                  WHERE t.therapist_id = ?
                  GROUP BY s.name
                  ORDER BY count DESC
                  LIMIT 5";
$top_services = query_select($service_query, [$therapist_id]);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Terapis</title>
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
            <li><a href="history.php" class="active">Riwayat</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>📜 Riwayat Pelanggan & Komisi</h1>

                <!-- STATISTIK UMUM -->
                <h3>📊 Statistik Umum</h3>
                <div class="row">
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Total Layanan</div>
                            <div class="card-body text-center">
                                <h2><?php echo $stats['total_layanan'] ?? 0; ?></h2>
                                <p>Pelanggan yang dilayani</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Total Komisi</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($stats['total_komisi'] ?? 0); ?></h2>
                                <p>Semua waktu</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Rata-rata per Layanan</div>
                            <div class="card-body text-center">
                                <h2>
                                    <?php 
                                    $avg = ($stats['total_layanan'] ?? 0) > 0 ? ($stats['total_komisi'] ?? 0) / $stats['total_layanan'] : 0;
                                    echo format_currency($avg);
                                    ?>
                                </h2>
                                <p>Per layanan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LAYANAN TERBANYAK -->
                <?php if (!empty($top_services)): ?>
                    <h3 style="margin-top: 30px;">🏆 Layanan Terbanyak</h3>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_services as $service): ?>
                                    <tr>
                                        <td><strong><?php echo $service['name']; ?></strong></td>
                                        <td><?php echo $service['count']; ?> kali</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- DAFTAR LENGKAP RIWAYAT -->
                <h3 style="margin-top: 30px;">📋 Riwayat Lengkap</h3>
                <?php if (!empty($history)): ?>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Layanan</th>
                                    <th>Member</th>
                                    <th>Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $item): ?>
                                    <tr>
                                        <td><?php echo format_date($item['transaction_date']); ?></td>
                                        <td><?php echo date('H:i', strtotime($item['created_at'])); ?></td>
                                        <td><strong><?php echo $item['service_name']; ?></strong></td>
                                        <td><?php echo $item['member_name'] ?? 'Walk In'; ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?php echo format_currency($item['commission_amount']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Belum ada riwayat layanan</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
