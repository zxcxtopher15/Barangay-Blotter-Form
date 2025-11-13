<?php
session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    if (!isset($_SESSION['google_loggedin'])) {
        throw new Exception("Unauthorized access");
    }

    // Validate required fields
    $required_fields = ['case_no', 'care_type', 'care_date', 'care_description', 'care_status'];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Database connection
    $db_server = "localhost";
    $db_user = "u416486854_p1";
    $db_pass = "2&rnLACGCldK";
    $db_name = "u416486854_p1";

    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Sanitize inputs
    $case_no = (int)$_POST['case_no'];
    $care_type = trim($_POST['care_type']);
    $care_date = $_POST['care_date'];
    $care_provider = !empty($_POST['care_provider']) ? trim($_POST['care_provider']) : null;
    $care_description = trim($_POST['care_description']);
    $care_status = trim($_POST['care_status']);
    $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
    $created_by = $_SESSION['google_name'];

    // Validate case exists
    $case_check = $conn->prepare("SELECT case_no FROM complaints WHERE case_no = ?");
    $case_check->bind_param("i", $case_no);
    $case_check->execute();
    $case_check_result = $case_check->get_result();

    if ($case_check_result->num_rows === 0) {
        throw new Exception("Case not found");
    }
    $case_check->close();

    // Insert care format
    $sql = "INSERT INTO care_formats (
        case_no,
        care_type,
        care_provider,
        care_date,
        care_description,
        care_status,
        notes,
        created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    $stmt->bind_param(
        "isssssss",
        $case_no,
        $care_type,
        $care_provider,
        $care_date,
        $care_description,
        $care_status,
        $notes,
        $created_by
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Care entry added successfully';
        $response['care_id'] = $stmt->insert_id;
    } else {
        throw new Exception("Failed to add care entry");
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
