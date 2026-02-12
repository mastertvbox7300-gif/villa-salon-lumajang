<?php
/**
 * LOGOUT PAGE
 */

require_once 'config/config.php';

// Destroy session
destroy_session();

// Redirect ke login
header('Location: pages/login.php');
exit;
?>
