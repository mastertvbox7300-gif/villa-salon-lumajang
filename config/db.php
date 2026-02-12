<?php
/**
 * DATABASE CONNECTION
 * Koneksi ke MySQL Database
 */

// Get config
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;
$db_port = DB_PORT;

// Create connection
$connection = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set charset to utf8
$connection->set_charset("utf8mb4");

/**
 * QUERY EXECUTION FUNCTIONS
 */

/**
 * Execute SELECT query
 * @param string $query
 * @param array $params
 * @return array|false
 */
function query_select($query, $params = []) {
    global $connection;
    
    $stmt = $connection->prepare($query);
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            else $types .= 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

/**
 * Execute single row SELECT query
 * @param string $query
 * @param array $params
 * @return array|null
 */
function query_select_one($query, $params = []) {
    $result = query_select($query, $params);
    return !empty($result) ? $result[0] : null;
}

/**
 * Execute INSERT/UPDATE/DELETE query
 * @param string $query
 * @param array $params
 * @return bool
 */
function query_execute($query, $params = []) {
    global $connection;
    
    $stmt = $connection->prepare($query);
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            else $types .= 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt->execute();
}

/**
 * Get last insert ID
 * @return int
 */
function get_last_insert_id() {
    global $connection;
    return $connection->insert_id;
}

/**
 * Get affected rows
 * @return int
 */
function get_affected_rows() {
    global $connection;
    return $connection->affected_rows;
}

/**
 * Close database connection
 */
function close_db() {
    global $connection;
    if ($connection) {
        $connection->close();
    }
}

// Uncomment jika ingin auto-close pada script end
// register_shutdown_function('close_db');

?>
