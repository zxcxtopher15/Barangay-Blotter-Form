<?php
session_start();

// Basic authentication check (similar to reportsadmin.php)
if (!isset($_SESSION['google_loggedin']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

// Database connection details
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "p1";
$conn = null;

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get the case_no from the POST request
$input = json_decode(file_get_contents('php://input'), true);
$case_no = isset($input['case_no']) ? mysqli_real_escape_string($conn, $input['case_no']) : '';
$is_archive_mode = isset($input['is_archive_mode']) && $input['is_archive_mode'];

if (empty($case_no)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Case number is required.']);
    mysqli_close($conn);
    exit;
}

// Determine which table to query
$table_to_query = $is_archive_mode ? 'reports_archive' : 'complaints';

$sql = "SELECT * FROM " . $table_to_query . " WHERE case_no = '$case_no'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $report_details = mysqli_fetch_assoc($result);
    if ($report_details) {
        echo json_encode(['success' => true, 'data' => $report_details]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Report not found.']);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>