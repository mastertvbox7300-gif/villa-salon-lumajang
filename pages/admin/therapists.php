<?php
/**
 * ADMIN - MANAJEMEN TERAPIS
 */

require_once '../../config/config.php';
require_login();
require_permission('admin');

$success = get_success();
$error = get_error();

// Handle add therapist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if (!empty($name) && !empty($username) && !empty($password)) {
        // Check if username exists
        $check = query_select_one("SELECT id FROM users WHERE username = ?", [$username]);
        if ($check) {
            $error = 'Username sudah digunakan!';
        } else {
            $query = "INSERT INTO users (name, username, password, role, phone, status) VALUES (?, ?, ?, 'terapis', ?, 'active')";
            if (query_execute($query, [$name, $username, md5($password), $phone])) {
                $therapist_id = get_last_insert_id();
                // Create therapist status
                query_execute("INSERT INTO therapist_status (user_id, status) VALUES (?, 'available')", [$therapist_id]);
                
                set_success('Terapis berhasil ditambahkan!');
                header('Location: therapists.php');
                exit;
            }
        }
    } else {
        $error = 'Semua field harus diisi!';
    }
}

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $therapist_id = intval($_POST['id']);
    $status = sanitize($_POST['status']);
    
    $query = "UPDATE therapist_status SET status = ? WHERE user_id = ?";
    if (query_execute($query, [$status, $therapist_id])) {
        set_success('Status terapis berhasil diupdate!');
        header('Location: therapists.php');
        exit;
    }
}

// Get all therapists with status
$query = "SELECT u.*, ts.status as work_status FROM users u 
          LEFT JOIN therapist_status ts ON u.id = ts.user_id 
          WHERE u.role = 'terapis' 
          ORDER BY u.name ASC";
$therapists = query_select($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Terapis - Admin</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">🏢 Villa Salon - Admin</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php">Layanan</a></li>
            <li><a href="therapists.php" class="active">Terapis</a></li>
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
                <h1>👥 Manajemen Terapis</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- FORM TAMBAH TERAPIS -->
                <div class="card">
                    <div class="card-header">➕ Tambah Terapis Baru</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Nama Lengkap*</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Username*</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Password*</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>No. Telp</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">➕ Tambah Terapis</button>
                        </form>
                    </div>
                </div>

                <!-- DAFTAR TERAPIS -->
                <h3 style="margin-top: 30px;">📊 Daftar Terapis</h3>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>No. Telp</th>
                                <th>Status Kerja</th>
                                <th>Status Akun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($therapists as $therapist): ?>
                                <tr>
                                    <td><strong><?php echo $therapist['name']; ?></strong></td>
                                    <td><?php echo $therapist['username']; ?></td>
                                    <td><?php echo $therapist['phone']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?php echo $therapist['id']; ?>">
                                            <select name="status" class="form-control" style="display: inline; width: auto;" onchange="this.form.submit();">
                                                <option value="available" <?php echo $therapist['work_status'] === 'available' ? 'selected' : ''; ?>>🟢 Available</option>
                                                <option value="busy" <?php echo $therapist['work_status'] === 'busy' ? 'selected' : ''; ?>>🟡 Busy</option>
                                                <option value="off" <?php echo $therapist['work_status'] === 'off' ? 'selected' : ''; ?>>⭕ Off</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $therapist['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($therapist['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-info btn-sm">Detail</a>
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
