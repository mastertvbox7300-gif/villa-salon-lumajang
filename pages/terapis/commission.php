<?php
/**
 * TERAPIS - AMBIL KOMISI / PENCAIRAN
 */

require_once '../../config/config.php';
require_login();
require_permission('terapis');

$therapist_id = get_current_user_id();
$success = get_success();
$error = get_error();

// Handle withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'withdraw') {
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Get current balance
    $balance_query = "SELECT SUM(commission_amount) as balance FROM therapist_commission 
                      WHERE therapist_id = ? AND status = 'withdrawn'";
    $balance_data = query_select_one($balance_query, [$therapist_id]);
    $current_balance = $balance_data['balance'] ?? 0;
    
    if ($amount <= 0) {
        $error = 'Nominal harus lebih dari 0!';
    } elseif ($amount > $current_balance) {
        $error = 'Saldo komisi tidak cukup! Saldo Anda: ' . format_currency($current_balance);
    } else {
        // Create withdrawal record
        $query = "INSERT INTO therapist_withdrawal (therapist_id, amount, notes) VALUES (?, ?, ?)";
        if (query_execute($query, [$therapist_id, $amount, $notes])) {
            set_success('Pencairan komisi berhasil! Jumlah: ' . format_currency($amount));
            header('Location: commission.php');
            exit;
        }
    }
}

// Get commission balance
$balance_query = "SELECT status, SUM(commission_amount) as total FROM therapist_commission 
                  WHERE therapist_id = ? 
                  GROUP BY status";
$balance_data = query_select($balance_query, [$therapist_id]);

$accumulated = 0;
$withdrawn = 0;

foreach ($balance_data as $data) {
    if ($data['status'] === 'accumulated') {
        $accumulated = $data['total'];
    } else {
        $withdrawn = $data['total'];
    }
}

$total_earnings = $accumulated + $withdrawn;

// Get withdrawal history
$history_query = "SELECT * FROM therapist_withdrawal WHERE therapist_id = ? ORDER BY withdrawal_date DESC LIMIT 20";
$withdrawal_history = query_select($history_query, [$therapist_id]);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Komisi - Terapis</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Terapis</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="task.php">Tugas</a></li>
            <li><a href="commission.php" class="active">Komisi</a></li>
            <li><a href="history.php">Riwayat</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>💰 Ambil Komisi</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- SALDO KOMISI -->
                <h3>📊 Saldo Komisi Anda</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Komisi Terkumpul</div>
                            <div class="card-body text-center">
                                <h2 style="color: var(--primary-color);">
                                    <?php echo format_currency($accumulated); ?>
                                </h2>
                                <p>Belum Diambil</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Sudah Diambil</div>
                            <div class="card-body text-center">
                                <h2 style="color: var(--success-color);">
                                    <?php echo format_currency($withdrawn); ?>
                                </h2>
                                <p>Total Pencairan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Komisi</div>
                            <div class="card-body text-center">
                                <h2 style="color: var(--info-color);">
                                    <?php echo format_currency($total_earnings); ?>
                                </h2>
                                <p>Semua Waktu</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Status</div>
                            <div class="card-body text-center">
                                <?php if ($accumulated > 0): ?>
                                    <span class="badge badge-success" style="font-size: 1.1rem; padding: 10px;">
                                        ✅ Ada Komisi
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 1.1rem; padding: 10px;">
                                        ⚠️ Kosong
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM AMBIL KOMISI -->
                <?php if ($accumulated > 0): ?>
                    <div class="card" style="margin-top: 30px;">
                        <div class="card-header">💳 Ambil Komisi</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="withdraw">
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Nominal Komisi (Maks: <?php echo format_currency($accumulated); ?>)*</label>
                                            <input type="number" name="amount" class="form-control lg" step="1000" 
                                                   placeholder="Contoh: 100000" required>
                                            <small>Masukkan nominal yang ingin diambil</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Catatan / Keterangan</label>
                                            <input type="text" name="notes" class="form-control lg" 
                                                   placeholder="Contoh: Pencairan untuk kebutuhan sehari-hari">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Ambil komisi sebesar ini?')">
                                        💰 Ambil Komisi
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- RIWAYAT PENCAIRAN -->
                <h3 style="margin-top: 30px;">📜 Riwayat Pencairan Komisi</h3>
                <?php if (!empty($withdrawal_history)): ?>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($withdrawal_history as $withdrawal): ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y H:i', strtotime($withdrawal['withdrawal_date'])); ?></td>
                                        <td>
                                            <strong style="color: var(--success-color);">
                                                <?php echo format_currency($withdrawal['amount']); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo $withdrawal['notes']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Belum ada riwayat pencairan</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
