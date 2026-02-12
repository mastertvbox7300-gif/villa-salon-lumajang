<?php
/**
 * INDEX PAGE - REDIRECT KEE LOGIN
 */

require_once 'config/config.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    $role = get_current_user_role();
    if ($role === 'admin') {
        header('Location: pages/admin/dashboard.php');
    } elseif ($role === 'kasir') {
        header('Location: pages/kasir/dashboard.php');
    } elseif ($role === 'terapis') {
        header('Location: pages/terapis/dashboard.php');
    }
} else {
    // Redirect ke login
    header('Location: pages/login.php');
}
exit;
?>
