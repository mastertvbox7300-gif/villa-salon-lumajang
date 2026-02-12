<?php
/**
 * KONFIGURASI UMUM VILLA SALON
 */

// ===== DATABASE CONFIGURATION =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'villa_salon');
define('DB_PORT', 3306);

// ===== APPLICATION CONFIGURATION =====
define('APP_NAME', 'Villa Salon Lumajang');
define('APP_URL', 'http://localhost:8000');
define('APP_TIMEZONE', 'Asia/Jakarta');

// ===== SESSION CONFIGURATION =====
define('SESSION_TIMEOUT', 3600); // 1 jam
define('SESSION_NAME', 'villa_salon_session');

// ===== SECURITY =====
define('PASSWORD_HASH_ALGO', 'md5'); // Bisa upgrade ke bcrypt
define('CSRF_TOKEN_LENGTH', 32);

// ===== UPLOAD CONFIGURATION =====
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// ===== DATETIME FORMAT =====
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// ===== PAGINATION =====
define('ITEMS_PER_PAGE', 20);

// ===== ROLE PERMISSIONS =====
$ROLE_PERMISSIONS = [
    'admin' => ['all'],
    'kasir' => ['transaction', 'member', 'report', 'dashboard'],
    'terapis' => ['dashboard', 'commission', 'task']
];

// ===== ERROR REPORTING =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== TIMEZONE =====
date_default_timezone_set(APP_TIMEZONE);

// ===== AUTO LOAD REQUIRED FILES =====
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

?>
