<?php
/**
 * KASIR - MEMBUAT TRANSAKSI BARU
 */

require_once '../../config/config.php';
require_login();
require_permission('kasir');

$success = get_success();
$error = get_error();
$kasir_id = get_current_user_id();

// Handle create transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $service_id = intval($_POST['service_id'] ?? 0);
    $therapist_id = intval($_POST['therapist_id'] ?? 0);
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $member_id = !empty($_POST['member_id']) ? intval($_POST['member_id']) : null;
    
    if ($service_id > 0 && $therapist_id > 0 && in_array($payment_method, ['cash', 'qris'])) {
        // Get service fee
        $service = query_select_one("SELECT * FROM services WHERE id = ?", [$service_id]);
        if (!$service) {
            $error = 'Layanan tidak ditemukan!';
        } else {
            $fee = $service['fee'];
            $today = date('Y-m-d');
            
            // Create transaction
            $query = "INSERT INTO transactions (transaction_date, member_id, service_id, therapist_id, kasir_id, fee, payment_method, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')";
            
            if (query_execute($query, [$today, $member_id, $service_id, $therapist_id, $kasir_id, $fee, $payment_method])) {
                $transaction_id = get_last_insert_id();
                
                // Create commission for therapist
                $commission_query = "INSERT INTO therapist_commission (therapist_id, transaction_id, commission_amount, status) 
                                    VALUES (?, ?, ?, 'accumulated')";
                query_execute($commission_query, [$therapist_id, $transaction_id, $fee]);
                
                // Update therapist status to busy
                query_execute("UPDATE therapist_status SET status = 'busy' WHERE user_id = ?", [$therapist_id]);
                
                set_success('Transaksi berhasil dibuat! Terapis: ' . $service['name']);
                header('Location: transaction.php');
                exit;
            }
        }
    } else {
        $error = 'Semua field harus dipilih!';
    }
}

// Get data untuk form
$services = get_services();
$therapists = get_available_therapists();

// Get members untuk autocomplete
$members_query = "SELECT id, name, phone FROM members ORDER BY name LIMIT 20";
$members = query_select($members_query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Baru - Kasir</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Kasir</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="transaction.php" class="active">Transaksi Baru</a></li>
            <li><a href="history.php">Riwayat</a></li>
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
                <h1>➕ Transaksi Baru</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- KIOSK MODE - PILIH LAYANAN -->
                <h3>📋 Pilih Layanan (KIOSK Mode)</h3>
                <p style="color: #666; margin-bottom: 20px;">Pelanggan/Kasir memilih layanan yang diinginkan</p>
                
                <div class="kiosk-grid">
                    <?php foreach ($services as $service): ?>
                        <button type="button" class="kiosk-btn btn-info" onclick="selectService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>', <?php echo $service['fee']; ?>)">
                            <div class="icon">💇</div>
                            <strong><?php echo $service['name']; ?></strong>
                            <div style="font-size: 1.2rem; margin-top: 10px;">
                                <?php echo format_currency($service['fee']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #666;">
                                <?php echo $service['duration_minutes']; ?> menit
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- FORM TRANSAKSI -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">💳 Detail Transaksi</div>
                    <div class="card-body">
                        <form method="POST" id="transactionForm">
                            <input type="hidden" name="action" value="create">
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Layanan*</label>
                                        <select name="service_id" id="service_id" class="form-control lg" required onchange="updateFee()">
                                            <option value="">-- Pilih Layanan --</option>
                                            <?php foreach ($services as $service): ?>
                                                <option value="<?php echo $service['id']; ?>" data-fee="<?php echo $service['fee']; ?>">
                                                    <?php echo $service['name']; ?> - <?php echo format_currency($service['fee']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Terapis*</label>
                                        <select name="therapist_id" class="form-control lg" required>
                                            <option value="">-- Pilih Terapis --</option>
                                            <?php foreach ($therapists as $therapist): ?>
                                                <option value="<?php echo $therapist['id']; ?>">
                                                    <?php echo $therapist['name']; ?> (<?php echo ucfirst($therapist['work_status']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Metode Pembayaran*</label>
                                        <select name="payment_method" class="form-control lg" required>
                                            <option value="">-- Pilih Metode --</option>
                                            <option value="cash">💵 Tunai</option>
                                            <option value="qris">📱 QRIS</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Biaya</label>
                                        <div style="padding: 10px; background: #f5f5f5; border-radius: 5px; font-weight: bold; font-size: 1.3rem; color: var(--primary-color);">
                                            <span id="totalFee">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Member (Opsional)</label>
                                        <input type="text" name="member_name" class="form-control" placeholder="Cari atau ketik nama member...">
                                        <input type="hidden" name="member_id" id="member_id">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" class="btn btn-success btn-lg">✅ Buat Transaksi</button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function selectService(serviceId, serviceName, fee) {
        document.getElementById('service_id').value = serviceId;
        updateFee();
    }
    
    function updateFee() {
        const select = document.getElementById('service_id');
        const option = select.options[select.selectedIndex];
        const fee = option.dataset.fee || 0;
        document.getElementById('totalFee').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(fee);
    }
    </script>
</body>
</html>
