<?php
/**
 * SESSION MANAGEMENT
 */

session_start();

/**
 * Set session value
 * @param string $key
 * @param mixed $value
 */
function set_session($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get session value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_session($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Check if session exists
 * @param string $key
 * @return bool
 */
function has_session($key) {
    return isset($_SESSION[$key]);
}

/**
 * Delete session value
 * @param string $key
 */
function delete_session($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Clear all session
 */
function clear_session() {
    $_SESSION = [];
}

/**
 * Destroy session
 */
function destroy_session() {
    clear_session();
    session_destroy();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return has_session('user_id') && has_session('user_role');
}

/**
 * Check if user has permission
 * @param string $required_role
 * @return bool
 */
function has_permission($required_role) {
    if (!is_logged_in()) return false;
    
    $user_role = get_session('user_role');
    
    if ($required_role === 'admin') {
        return $user_role === 'admin';
    } elseif ($required_role === 'kasir') {
        return in_array($user_role, ['admin', 'kasir']);
    } elseif ($required_role === 'terapis') {
        return in_array($user_role, ['admin', 'terapis']);
    }
    
    return false;
}

/**
 * Get current user ID
 * @return int|null
 */
function get_current_user_id() {
    return get_session('user_id');
}

/**
 * Get current user role
 * @return string|null
 */
function get_current_user_role() {
    return get_session('user_role');
}

/**
 * Get current user name
 * @return string|null
 */
function get_current_user_name() {
    return get_session('user_name');
}

/**
 * Redirect if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/pages/login.php');
        exit;
    }
}

/**
 * Redirect if not have permission
 * @param string $required_role
 */
function require_permission($required_role) {
    require_login();
    
    if (!has_permission($required_role)) {
        header('Location: ' . APP_URL . '/pages/unauthorized.php');
        exit;
    }
}

/**
 * Set error message
 * @param string $message
 */
function set_error($message) {
    set_session('error_message', $message);
}

/**
 * Get and clear error message
 * @return string|null
 */
function get_error() {
    $error = get_session('error_message');
    delete_session('error_message');
    return $error;
}

/**
 * Set success message
 * @param string $message
 */
function set_success($message) {
    set_session('success_message', $message);
}

/**
 * Get and clear success message
 * @return string|null
 */
function get_success() {
    $success = get_session('success_message');
    delete_session('success_message');
    return $success;
}

?>
