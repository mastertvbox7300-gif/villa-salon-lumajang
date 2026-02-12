<?php
/**
 * HELPER FUNCTIONS
 * Fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

/**
 * Sanitize input
 */
function sanitize($data) {
    global $connection;
    return $connection->real_escape_string(trim($data));
}

/**
 * Format currency (IDR)
 */
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format date to Indonesia format
 */
function format_date($date) {
    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    
    $date_obj = new DateTime($date);
    $day = $date_obj->format('d');
    $month = (int)$date_obj->format('m');
    $year = $date_obj->format('Y');
    
    return "$day " . $bulan[$month] . " $year";
}

/**
 * Get user by ID
 */
function get_user($user_id) {
    $query = "SELECT * FROM users WHERE id = ?";
    return query_select_one($query, [$user_id]);
}

/**
 * Get all services
 */
function get_services() {
    $query = "SELECT * FROM services WHERE status = 'active' ORDER BY name ASC";
    return query_select($query);
}

/**
 * Get service by ID
 */
function get_service($service_id) {
    $query = "SELECT * FROM services WHERE id = ?";
    return query_select_one($query, [$service_id]);
}

/**
 * Get all active therapists
 */
function get_therapists() {
    $query = "SELECT u.*, ts.status as work_status FROM users u 
              LEFT JOIN therapist_status ts ON u.id = ts.user_id
              WHERE u.role = 'terapis' AND u.status = 'active' 
              ORDER BY u.name ASC";
    return query_select($query);
}

/**
 * Get available therapists
 */
function get_available_therapists() {
    $query = "SELECT u.*, ts.status as work_status FROM users u 
              LEFT JOIN therapist_status ts ON u.id = ts.user_id
              WHERE u.role = 'terapis' AND u.status = 'active' AND ts.status = 'available'
              ORDER BY u.name ASC";
    return query_select($query);
}

/**
 * Get therapist status
 */
function get_therapist_status($therapist_id) {
    $query = "SELECT * FROM therapist_status WHERE user_id = ?";
    return query_select_one($query, [$therapist_id]);
}

/**
 * Update therapist status
 */
function update_therapist_status($therapist_id, $status) {
    $query = "UPDATE therapist_status SET status = ? WHERE user_id = ?";
    return query_execute($query, [$status, $therapist_id]);
}

/**
 * Get therapist commission balance
 */
function get_therapist_commission_balance($therapist_id) {
    $query = "SELECT 
                SUM(CASE WHEN status = 'accumulated' THEN commission_amount ELSE 0 END) as balance,
                SUM(CASE WHEN status = 'withdrawn' THEN commission_amount ELSE 0 END) as total_withdrawn,
                COUNT(CASE WHEN status = 'accumulated' THEN 1 END) as pending_count
              FROM therapist_commission 
              WHERE therapist_id = ?";
    return query_select_one($query, [$therapist_id]);
}

/**
 * Get pending payments for therapist
 */
function get_pending_commission($therapist_id) {
    $query = "SELECT tc.*, t.name as transaction_service, s.name as service_name
              FROM therapist_commission tc
              JOIN transactions t ON tc.transaction_id = t.id
              JOIN services s ON t.service_id = s.id
              WHERE tc.therapist_id = ? AND tc.status = 'accumulated'
              ORDER BY tc.accumulated_date DESC";
    return query_select($query, [$therapist_id]);
}

/**
 * Get today's transactions
 */
function get_today_transactions() {
    $today = date('Y-m-d');
    $query = "SELECT t.*, u.name as therapist_name, s.name as service_name
              FROM transactions t
              JOIN users u ON t.therapist_id = u.id
              JOIN services s ON t.service_id = s.id
              WHERE DATE(t.transaction_date) = ?
              ORDER BY t.created_at DESC";
    return query_select($query, [$today]);
}

/**
 * Get monthly report
 */
function get_monthly_report($year, $month) {
    $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $query = "SELECT 
                COUNT(*) as total_transaction,
                SUM(CASE WHEN payment_method = 'cash' THEN fee ELSE 0 END) as cash_income,
                SUM(CASE WHEN payment_method = 'qris' THEN fee ELSE 0 END) as qris_income,
                COUNT(DISTINCT therapist_id) as total_therapist
              FROM transactions 
              WHERE transaction_date BETWEEN ? AND ? AND status = 'completed'";
    
    return query_select_one($query, [$start_date, $end_date]);
}

/**
 * Generate transaction ID
 */
function generate_transaction_id() {
    return 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
}

/**
 * Validate input
 */
function validate_required($data, $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        return false;
    }
    return true;
}

/**
 * Validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone
 */
function validate_phone($phone) {
    return preg_match('/^[0-9+\-\s\(\)]*$/', $phone) && strlen($phone) >= 10;
}

/**
 * Generate random string
 */
function generate_random_string($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $result;
}

?>
