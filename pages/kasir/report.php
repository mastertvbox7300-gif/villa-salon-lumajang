<?php
/**
 * KASIR - LAPORAN
 */

require_once '../../config/config.php';
require_login();
require_permission('kasir');

// Get report period
$period = sanitize($_GET['period'] ?? 'today');
$year = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('m'));

if ($period === 'today') {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
} elseif ($period === 'month') {
    $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end_date = date('Y-m-t', strtotime($start_date));
} elseif ($period === 'year') {
    $start_date = "$year-01-01";
    $end_date = "$year-12-31";
}

// Get transaction data
$query = "SELECT t.*, u.name as therapist_name, s.name as service_name
          FROM transactions t
          JOIN users u ON t.therapist_id = u.id
          JOIN services s ON t.service_id = s.id
          WHERE DATE(t.transaction_date) BETWEEN ? AND ?
          ORDER BY t.transaction_date DESC";
$transactions = query_select($query, [$start_date, $end_date]);

// Calculate summary
$summary = [
    'total_transaction' => 0,
    'cash_income' => 0,
    'qris_income' => 0,
    'total_income' => 0,
    'by_service' => [],
    'by_therapist' => []
];

foreach ($transactions as $trans) {
    $summary['total_transaction']++;
    
    if ($trans['payment_method'] === 'cash') {
        $summary['cash_income'] += $trans['fee'];
    } else {
        $summary['qris_income'] += $trans['fee'];
    }
    $summary['total_income'] += $trans['fee'];
    
    // By service
    if (!isset($summary['by_service'][$trans['service_name']])) {
        $summary['by_service'][$trans['service_name']] = ['count' => 0, 'total' => 0];
    }
    $summary['by_service'][$trans['service_name']]['count']++;
    $summary['by_service'][$trans['service_name']]['total'] += $trans['fee'];
    
    // By therapist
    if (!isset($summary['by_therapist'][$trans['therapist_name']])) {
        $summary['by_therapist'][$trans['therapist_name']] = ['count' => 0, 'total' => 0];
    }
    $summary['by_therapist'][$trans['therapist_name']]['count']++;
    $summary['by_therapist'][$trans['therapist_name']]['total'] += $trans['fee'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Kasir</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Kasir</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="transaction.php">Transaksi Baru</a></li>
            <li><a href="history.php">Riwayat</a></li>
            <li><a href="report.php" class="active">Laporan</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>📊 Laporan Penjualan</h1>

                <!-- FILTER PERIODE -->
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-body">
                        <form method="GET" style="display: flex; gap: 10px; align-items: flex-end;">
                            <div class="form-group" style="margin: 0; flex: 1;">
                                <label>Periode</label>
                                <select name="period" onchange="this.form.submit();" class="form-control">
                                    <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Hari Ini</option>
                                    <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Bulan Ini</option>
                                    <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Tahun Ini</option>
                                </select>
                            </div>
                            
                            <?php if ($period !== 'today'): ?>
                                <div class="form-group" style="margin: 0; flex: 1;">
                                    <label>Bulan</label>
                                    <select name="month" onchange="this.form.submit();" class="form-control">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo $month === $m ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">🔄 Filter</button>
                        </form>
                    </div>
                </div>

                <!-- RINGKASAN -->
                <h3>📈 Ringkasan Penjualan</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Transaksi</div>
                            <div class="card-body text-center">
                                <h2><?php echo $summary['total_transaction']; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Tunai</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($summary['cash_income']); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">QRIS</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($summary['qris_income']); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Pendapatan</div>
                            <div class="card-body text-center">
                                <h2 style="color: var(--success-color);"><?php echo format_currency($summary['total_income']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LAPORAN PER LAYANAN -->
                <h3 style="margin-top: 30px;">🎯 Per Layanan</h3>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Jumlah</th>
                                <th>Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['by_service'] as $service_name => $data): ?>
                                <tr>
                                    <td><strong><?php echo $service_name; ?></strong></td>
                                    <td><?php echo $data['count']; ?> transaksi</td>
                                    <td><?php echo format_currency($data['total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- LAPORAN PER TERAPIS -->
                <h3 style="margin-top: 30px;">👤 Per Terapis</h3>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Terapis</th>
                                <th>Jumlah Layanan</th>
                                <th>Total Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['by_therapist'] as $therapist_name => $data): ?>
                                <tr>
                                    <td><strong><?php echo $therapist_name; ?></strong></td>
                                    <td><?php echo $data['count']; ?> layanan</td>
                                    <td><?php echo format_currency($data['total']); ?></td>
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
