<?php
/**
 * KASIR DASHBOARD
 * Halaman utama untuk kasir dengan kiosk mode + transaksi
 */

require_once '../../config/config.php';
require_login();
require_permission('kasir');

$success = get_success();
$error = get_error();

// Get data untuk dashboard
$today_transactions = get_today_transactions();
$services = get_services();
$therapists = get_available_therapists();

// Count data
$total_transaction_today = count($today_transactions);
$total_income_today = 0;
$cash_today = 0;
$qris_today = 0;

foreach ($today_transactions as $trans) {
    if ($trans['status'] === 'completed') {
        $total_income_today += $trans['fee'];
        if ($trans['payment_method'] === 'cash') {
            $cash_today += $trans['fee'];
        } else {
            $qris_today += $trans['fee'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Villa Salon</title>
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
            <li><a href="report.php">Laporan</a></li>
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
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- HEADER -->
                <h1>Dashboard Kasir</h1>
                <p>Selamat datang, <?php echo get_current_user_name(); ?>!</p>
                
                <!-- STATISTIK HARI INI -->
                <h3 style="margin-top: 30px;">📊 Statistik Hari Ini</h3>
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Transaksi Hari Ini</div>
                            <div class="card-body text-center">
                                <h2><?php echo $total_transaction_today; ?></h2>
                                <p>Transaksi</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Total Pendapatan</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($total_income_today); ?></h2>
                                <p>Tunai & QRIS</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Tunai</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($cash_today); ?></h2>
                                <p>Kas Masuk</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">QRIS</div>
                            <div class="card-body text-center">
                                <h2><?php echo format_currency($qris_today); ?></h2>
                                <p>Non-Tunai</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- KIOSK MODE - PILIHAN LAYANAN -->
                <h3 style="margin-top: 30px;">🎯 Mode Kiosk - Pilih Layanan</h3>
                <p>Pelanggan dapat memilih layanan dari tombol di bawah:</p>
                
                <div class="kiosk-grid">
                    <?php foreach ($services as $service): ?>
                        <button class="kiosk-btn btn-info" onclick="selectService(<?php echo $service['id']; ?>, '<?php echo $service['name']; ?>', <?php echo $service['fee']; ?>)">
                            <div class="icon">💇</div>
                            <strong><?php echo $service['name']; ?></strong>
                            <div style="font-size: 1.2rem; margin-top: 10px;">
                                <?php echo format_currency($service['fee']); ?>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- TOMBOL AKSI CEPAT -->
                <h3 style="margin-top: 30px;">⚡ Aksi Cepat</h3>
                <div class="btn-group">
                    <a href="transaction.php" class="btn btn-primary btn-lg" style="flex: 1;">
                        ➕ Transaksi Baru
                    </a>
                    <a href="history.php" class="btn btn-secondary btn-lg" style="flex: 1;">
                        📋 Lihat Riwayat
                    </a>
                    <a href="report.php" class="btn btn-info btn-lg" style="flex: 1;">
                        📊 Laporan
                    </a>
                </div>
                
                <!-- TRANSAKSI TERAKHIR -->
                <h3 style="margin-top: 30px;">📝 Transaksi Terakhir</h3>
                <?php if (!empty($today_transactions)): ?>
                    <div class="card">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>Layanan</th>
                                    <th>Terapis</th>
                                    <th>Biaya</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $limit = array_slice($today_transactions, 0, 5);
                                foreach ($limit as $trans): 
                                ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($trans['created_at'])); ?></td>
                                        <td><?php echo $trans['service_name']; ?></td>
                                        <td><?php echo $trans['therapist_name']; ?></td>
                                        <td><?php echo format_currency($trans['fee']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $trans['payment_method'] === 'cash' ? 'badge-warning' : 'badge-info'; ?>">
                                                <?php echo strtoupper($trans['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $trans['status'] === 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo strtoupper($trans['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Belum ada transaksi hari ini</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- MODAL UNTUK PILIH TERAPIS -->
    <div id="therapistModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h2>Pilih Terapis</h2>
            <div id="therapistList" style="margin: 20px 0; max-height: 300px; overflow-y: auto;"></div>
            <button onclick="closeModal()" class="btn btn-danger">Batal</button>
        </div>
    </div>
    
    <script>
        function selectService(serviceId, serviceName, fee) {
            // Show available therapists
            const therapists = <?php echo json_encode(get_available_therapists()); ?>;
            const modal = document.getElementById('therapistModal');
            const list = document.getElementById('therapistList');
            
            list.innerHTML = '';
            therapists.forEach(therapist => {
                const btn = document.createElement('button');
                btn.className = 'kiosk-btn btn-primary';
                btn.innerHTML = '<strong>' + therapist.name + '</strong><div style="margin-top: 10px; font-size: 1rem;">👤</div>';
                btn.onclick = () => createTransaction(serviceId, serviceName, fee, therapist.id, therapist.name);
                list.appendChild(btn);
            });
            
            modal.style.display = 'block';
        }
        
        function createTransaction(serviceId, serviceName, fee, therapistId, therapistName) {
            const paymentMethod = prompt(`Layanan: ${serviceName}\nBiaya: Rp ${fee}\nTerapis: ${therapistName}\n\nMetode Pembayaran?\n(Ketik: cash / qris)`);
            
            if (paymentMethod && (paymentMethod === 'cash' || paymentMethod === 'qris')) {
                // Call API atau submit form untuk create transaction
                window.location.href = `transaction_create.php?service_id=${serviceId}&therapist_id=${therapistId}&payment_method=${paymentMethod}`;
            } else {
                alert('Pilihan tidak valid');
            }
        }
        
        function closeModal() {
            document.getElementById('therapistModal').style.display = 'none';
        }
    </script>
</body>
</html>
