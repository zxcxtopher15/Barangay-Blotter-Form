<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json'); // Keep this line, it's crucial

// Include your database connection (or establish it here)
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";
$conn = null;

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// 1. Get the raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); // Decode as associative array

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit;
}

// 2. Validate input (essential for security and preventing SQL injection)
$case_no = $data['case_no'] ?? null;
$is_archive_mode = $data['is_archive_mode'] ?? false; // Get this from JS
// ... get other fields you expect to update ...

if (!$case_no) {
    echo json_encode(['success' => false, 'message' => 'Case number is required.']);
    exit;
}

$table_name = $is_archive_mode ? 'reports_archive' : 'complaints';

// Start building your SQL UPDATE query dynamically
// ONLY include fields that are actually present in the $data array AND that you want to allow updating.
$update_fields = [];
$bind_types = ""; // For mysqli_stmt_bind_param
$bind_values = [];

// Example fields. You need to add ALL fields you want to update from your form.
// Ensure you map JavaScript field names to your database column names.

// Incident Details
if (isset($data['incident_datetime'])) {
    $update_fields[] = "incident_datetime = ?";
    $bind_types .= "s";
    $bind_values[] = $data['incident_datetime'];
}
if (isset($data['incident_location'])) {
    $update_fields[] = "incident_location = ?";
    $bind_types .= "s";
    $bind_values[] = $data['incident_location'];
}
if (isset($data['complaint_description'])) {
    $update_fields[] = "complaint_description = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complaint_description'];
}

// Complainant Details
if (isset($data['complainant_first_name'])) {
    $update_fields[] = "complainant_first_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_first_name'];
}
if (isset($data['complainant_middle_name'])) {
    $update_fields[] = "complainant_middle_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_middle_name'];
}
if (isset($data['complainant_last_name'])) {
    $update_fields[] = "complainant_last_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_last_name'];
}
if (isset($data['complainant_age'])) {
    $update_fields[] = "complainant_age = ?";
    $bind_types .= "i"; // 'i' for integer
    $bind_values[] = $data['complainant_age'];
}
if (isset($data['complainant_gender'])) {
    $update_fields[] = "complainant_gender = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_gender'];
}
if (isset($data['complainant_phone'])) {
    $update_fields[] = "complainant_phone = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_phone'];
}
if (isset($data['complainant_address'])) {
    $update_fields[] = "complainant_address = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complainant_address'];
}

// Victim Details
if (isset($data['victim_first_name'])) {
    $update_fields[] = "victim_first_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_first_name'];
}
if (isset($data['victim_middle_name'])) {
    $update_fields[] = "victim_middle_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_middle_name'];
}
if (isset($data['victim_last_name'])) {
    $update_fields[] = "victim_last_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_last_name'];
}
if (isset($data['victim_age'])) {
    $update_fields[] = "victim_age = ?";
    $bind_types .= "i";
    $bind_values[] = $data['victim_age'];
}
if (isset($data['victim_gender'])) {
    $update_fields[] = "victim_gender = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_gender'];
}
if (isset($data['victim_phone'])) {
    $update_fields[] = "victim_phone = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_phone'];
}
if (isset($data['victim_address'])) {
    $update_fields[] = "victim_address = ?";
    $bind_types .= "s";
    $bind_values[] = $data['victim_address'];
}

// Witness Details
if (isset($data['witness_first_name'])) {
    $update_fields[] = "witness_first_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_first_name'];
}
if (isset($data['witness_middle_name'])) {
    $update_fields[] = "witness_middle_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_middle_name'];
}
if (isset($data['witness_last_name'])) {
    $update_fields[] = "witness_last_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_last_name'];
}
if (isset($data['witness_age'])) {
    $update_fields[] = "witness_age = ?";
    $bind_types .= "i";
    $bind_values[] = $data['witness_age'];
}
if (isset($data['witness_gender'])) {
    $update_fields[] = "witness_gender = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_gender'];
}
if (isset($data['witness_phone'])) {
    $update_fields[] = "witness_phone = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_phone'];
}
if (isset($data['witness_address'])) {
    $update_fields[] = "witness_address = ?";
    $bind_types .= "s";
    $bind_values[] = $data['witness_address'];
}

// Respondent Details
if (isset($data['respondent_first_name'])) {
    $update_fields[] = "respondent_first_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_first_name'];
}
if (isset($data['respondent_middle_name'])) {
    $update_fields[] = "respondent_middle_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_middle_name'];
}
if (isset($data['respondent_last_name'])) {
    $update_fields[] = "respondent_last_name = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_last_name'];
}
if (isset($data['respondent_age'])) {
    $update_fields[] = "respondent_age = ?";
    $bind_types .= "i";
    $bind_values[] = $data['respondent_age'];
}
if (isset($data['respondent_gender'])) {
    $update_fields[] = "respondent_gender = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_gender'];
}
if (isset($data['respondent_phone'])) {
    $update_fields[] = "respondent_phone = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_phone'];
}
if (isset($data['respondent_address'])) {
    $update_fields[] = "respondent_address = ?";
    $bind_types .= "s";
    $bind_values[] = $data['respondent_address'];
}

// Statement & Administration
if (isset($data['complaint_statement'])) {
    $update_fields[] = "complaint_statement = ?";
    $bind_types .= "s";
    $bind_values[] = $data['complaint_statement'];
}
if (isset($data['reported_by'])) {
    $update_fields[] = "reported_by = ?";
    $bind_types .= "s";
    $bind_values[] = $data['reported_by'];
}
if (isset($data['is_affirmed'])) {
    $update_fields[] = "is_affirmed = ?";
    $bind_types .= "i"; // 'i' for integer (boolean 0/1)
    $bind_values[] = $data['is_affirmed'];
}

if (empty($update_fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields provided for update.']);
    exit;
}

$sql = "UPDATE {$table_name} SET " . implode(', ', $update_fields) . " WHERE case_no = ?";
$bind_types .= "s"; // Add type for case_no
$bind_values[] = $case_no; // Add case_no to the values

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
    exit;
}

// Use variadic function call for mysqli_stmt_bind_param
mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_values);

if (mysqli_stmt_execute($stmt)) {
    // Set a session message for display after reload
    $_SESSION['message'] = 'Report ' . htmlspecialchars($case_no) . ' updated successfully!';
    echo json_encode(['success' => true, 'message' => 'Report updated successfully.']);
} else {
    $_SESSION['message'] = 'Error updating report ' . htmlspecialchars($case_no) . ': ' . mysqli_error($conn);
    echo json_encode(['success' => false, 'message' => 'Error updating report: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>