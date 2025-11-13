<?php
/**
 * Database Configuration and Helper Functions
 * Centralized database queries to ensure consistency across all dashboards
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USER', 'u416486854_p1');
define('DB_PASS', '2&rnLACGCldK');
define('DB_NAME', 'u416486854_p1');

/**
 * Create database connection
 * @return mysqli
 */
function getDbConnection() {
    try {
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Get count of incidents for current month
 * @param mysqli $conn
 * @return int
 */
function getThisMonthCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM complaints
            WHERE MONTH(incident_datetime) = MONTH(CURDATE())
            AND YEAR(incident_datetime) = YEAR(CURDATE())";
    $result = mysqli_query($conn, $sql);
    return $result ? (mysqli_fetch_assoc($result)['count'] ?? 0) : 0;
}

/**
 * Get count of incidents for last month
 * @param mysqli $conn
 * @return int
 */
function getLastMonthCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM complaints
            WHERE YEAR(incident_datetime) = YEAR(CURDATE() - INTERVAL 1 MONTH)
            AND MONTH(incident_datetime) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
    $result = mysqli_query($conn, $sql);
    return $result ? (mysqli_fetch_assoc($result)['count'] ?? 0) : 0;
}

/**
 * Get total count of all incidents
 * @param mysqli $conn
 * @return int
 */
function getTotalCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM complaints";
    $result = mysqli_query($conn, $sql);
    return $result ? (mysqli_fetch_assoc($result)['count'] ?? 0) : 0;
}

/**
 * Get the most reported incident type (all time)
 * @param mysqli $conn
 * @return array ['description' => string, 'count' => int]
 */
function getTopIncident($conn) {
    $sql = "SELECT complaint_description, COUNT(*) as count
            FROM complaints
            GROUP BY complaint_description
            ORDER BY count DESC
            LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && $data = mysqli_fetch_assoc($result)) {
        return [
            'description' => $data['complaint_description'] ?? 'No Data',
            'count' => $data['count'] ?? 0
        ];
    }

    return ['description' => 'No Data', 'count' => 0];
}
