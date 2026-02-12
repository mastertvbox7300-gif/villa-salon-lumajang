<?php
/**
 * TERAPIS - DAFTAR TUGAS
 */

require_once '../../config/config.php';
require_login();
require_permission('terapis');

$therapist_id = get_current_user_id();
$success = get_success();
$error = get_error();

// Handle mark task as done
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_done') {
    $commission_id = intval($_POST['commission_id']);
    $transaction_id = intval($_POST['transaction_id']);
    
    // Mark commission as withdrawn
    $query = "UPDATE therapist_commission SET status = 'withdrawn', withdrawal_date = NOW() WHERE id = ?";
    if (query_execute($query, [$commission_id])) {
        // Update therapist status back to available
        query_execute("UPDATE therapist_status SET status = 'available' WHERE user_id = ?", [$therapist_id]);
        
        set_success('Tugas ditandai selesai! Komisi telah terakumulasi.');
        header('Location: task.php');
        exit;
    }
}

// Get pending tasks (commissions with accumulated status)
$query = "SELECT tc.*, t.transaction_date, s.name as service_name, m.name as member_name
          FROM therapist_commission tc
          JOIN transactions t ON tc.transaction_id = t.id
          JOIN services s ON t.service_id = s.id
          LEFT JOIN members m ON t.member_id = m.id
          WHERE tc.therapist_id = ? AND tc.status = 'accumulated'
          ORDER BY tc.accumulated_date DESC";
$pending_tasks = query_select($query, [$therapist_id]);

// Get current status
$status_query = "SELECT status FROM therapist_status WHERE user_id = ?";
$current_status = query_select_one($status_query, [$therapist_id]);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - Terapis</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Terapis</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="task.php" class="active">Tugas</a></li>
            <li><a href="commission.php">Komisi</a></li>
            <li><a href="history.php">Riwayat</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span>
            <span class="badge <?php echo $current_status['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>" style="margin: 0 10px;">
                <?php echo strtoupper($current_status['status']); ?>
            </span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>📋 Daftar Tugas</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- INFO -->
                <div class="alert alert-info">
                    <strong>📌 Cara Kerja:</strong><br>
                    Ketika Anda ditugaskan untuk melayani pelanggan, data akan muncul di sini. 
                    Setelah selesai, tekan tombol "✅ SELESAI" untuk menyelesaikan tugas dan komisi akan terakumulasi.
                </div>

                <!-- TUGAS YANG SEDANG BERJALAN / PENDING -->
                <?php if (!empty($pending_tasks)): ?>
                    <h3>⏳ Tugas yang Menunggu Penyelesaian (<?php echo count($pending_tasks); ?>)</h3>
                    
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Layanan</th>
                                    <th>Member</th>
                                    <th>Biaya</th>
                                    <th>Komisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_tasks as $task): ?>
                                    <tr style="background-color: #fffacd; border-left: 5px solid var(--warning-color);">
                                        <td><?php echo format_date($task['transaction_date']); ?></td>
                                        <td><strong><?php echo $task['service_name']; ?></strong></td>
                                        <td><?php echo $task['member_name'] ?? 'Tidak Terdaftar'; ?></td>
                                        <td><?php echo format_currency($task['commission_amount']); ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?php echo format_currency($task['commission_amount']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Tandai tugas ini sebagai selesai?');">
                                                <input type="hidden" name="action" value="mark_done">
                                                <input type="hidden" name="commission_id" value="<?php echo $task['id']; ?>">
                                                <input type="hidden" name="transaction_id" value="<?php echo $task['transaction_id']; ?>">
                                                <button type="submit" class="btn btn-success">✅ SELESAI</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        ✅ Tidak ada tugas yang pending. Anda siap menerima tugas baru!
                    </div>
                <?php endif; ?>

                <!-- STATISTIK -->
                <h3 style="margin-top: 30px;">📊 Statistik Anda</h3>
                <div class="row">
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Tugas Pending</div>
                            <div class="card-body text-center">
                                <h2><?php echo count($pending_tasks); ?></h2>
                                <p>Menunggu diselesaikan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Total Komisi Pending</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency(array_sum(array_column($pending_tasks, 'commission_amount'))); ?></h2>
                                <p>Siap diambil kapan saja</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">Link Cepat</div>
                            <div class="card-body text-center">
                                <a href="commission.php" class="btn btn-success btn-sm">Lihat Komisi</a><br><br>
                                <a href="history.php" class="btn btn-info btn-sm">Lihat Riwayat</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
