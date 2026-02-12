<?php
/**
 * ADMIN - MANAJEMEN LAYANAN
 */

require_once '../../config/config.php';
require_login();
require_permission('admin');

$success = get_success();
$error = get_error();

// Handle add service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $fee = floatval($_POST['fee'] ?? 0);
    $duration = intval($_POST['duration'] ?? 30);
    
    if (!empty($name) && $fee > 0) {
        $query = "INSERT INTO services (name, description, fee, duration_minutes) VALUES (?, ?, ?, ?)";
        if (query_execute($query, [$name, $description, $fee, $duration])) {
            set_success('Layanan berhasil ditambahkan!');
            header('Location: services.php');
            exit;
        }
    } else {
        $error = 'Nama dan fee tidak boleh kosong!';
    }
}

// Handle edit service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $fee = floatval($_POST['fee'] ?? 0);
    $duration = intval($_POST['duration'] ?? 30);
    
    if (!empty($name) && $fee > 0) {
        $query = "UPDATE services SET name = ?, description = ?, fee = ?, duration_minutes = ? WHERE id = ?";
        if (query_execute($query, [$name, $description, $fee, $duration, $id])) {
            set_success('Layanan berhasil diupdate!');
            header('Location: services.php');
            exit;
        }
    }
}

// Handle delete service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $query = "UPDATE services SET status = 'inactive' WHERE id = ?";
    if (query_execute($query, [$id])) {
        set_success('Layanan berhasil dihapus!');
        header('Location: services.php');
        exit;
    }
}

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$services = query_select($query);

// Get service by id if editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $query = "SELECT * FROM services WHERE id = ?";
    $edit_service = query_select_one($query, [$id]);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Layanan - Admin</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Admin</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php" class="active">Layanan</a></li>
            <li><a href="therapists.php">Terapis</a></li>
            <li><a href="users.php">User</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
        <div class="navbar-user">
            <span><?php echo get_current_user_name(); ?></span> |
            <a href="../logout.php" style="color: white; margin-left: 10px;">Logout</a>
        </div>
    </nav>

    <div class="main-layout">
        <div class="content">
            <div class="container">
                <h1>📋 Manajemen Layanan</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- FORM TAMBAH/EDIT -->
                <div class="card">
                    <div class="card-header">
                        <?php echo $edit_service ? '✏️ Edit Layanan' : '➕ Tambah Layanan Baru'; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_service ? 'edit' : 'add'; ?>">
                            <?php if ($edit_service): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nama Layanan*</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo $edit_service['name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Fee (Rp)*</label>
                                        <input type="number" name="fee" class="form-control" step="1000" value="<?php echo $edit_service['fee'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Durasi (menit)</label>
                                        <input type="number" name="duration" class="form-control" value="<?php echo $edit_service['duration_minutes'] ?? 30; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <input type="text" name="description" class="form-control" value="<?php echo $edit_service['description'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $edit_service ? '💾 Update' : '➕ Tambah'; ?>
                                </button>
                                <?php if ($edit_service): ?>
                                    <a href="services.php" class="btn btn-secondary">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DAFTAR LAYANAN -->
                <h3 style="margin-top: 30px;">📊 Daftar Layanan</h3>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Fee</th>
                                <th>Durasi</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong><?php echo $service['name']; ?></strong></td>
                                    <td><?php echo format_currency($service['fee']); ?></td>
                                    <td><?php echo $service['duration_minutes']; ?> menit</td>
                                    <td><?php echo $service['description']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $service['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($service['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="services.php?edit=<?php echo $service['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
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
