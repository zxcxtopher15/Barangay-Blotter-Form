<?php
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Validate required fields
    $required_fields = ['submitter_name', 'submitter_email', 'submitter_phone', 'incident_datetime', 'complaint_type', 'incident_location', 'incident_description'];

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
    $submitter_name = trim($_POST['submitter_name']);
    $submitter_email = trim($_POST['submitter_email']);
    $submitter_phone = trim($_POST['submitter_phone']);
    $incident_datetime = $_POST['incident_datetime'];
    $complaint_type = $_POST['complaint_type'];
    $incident_location = trim($_POST['incident_location']);
    $incident_description = trim($_POST['incident_description']);

    // Handle "Others" complaint type
    if ($complaint_type === 'Others' && !empty($_POST['other_complaint_type'])) {
        $complaint_type = trim($_POST['other_complaint_type']);
    }

    // Validate email
    if (!filter_var($submitter_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email address");
    }

    // Validate phone number (Philippine format)
    if (!preg_match('/^09[0-9]{9}$/', $submitter_phone)) {
        throw new Exception("Invalid phone number format. Use 11 digits starting with 09");
    }

    // Insert into database using prepared statement
    $sql = "INSERT INTO online_reports (
        submitter_name,
        submitter_email,
        submitter_phone,
        incident_datetime,
        incident_location,
        incident_description,
        complaint_type,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    $stmt->bind_param(
        "sssssss",
        $submitter_name,
        $submitter_email,
        $submitter_phone,
        $incident_datetime,
        $incident_location,
        $incident_description,
        $complaint_type
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Report submitted successfully';
        $response['report_id'] = $stmt->insert_id;
    } else {
        throw new Exception("Failed to submit report");
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
