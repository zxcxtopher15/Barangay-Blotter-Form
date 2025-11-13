<?php
session_start();
header('Content-Type: application/json'); // Keep this for consistency or remove if always redirecting

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you get case_no from POST or JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $case_no = $input['case_no'] ?? null;

    if (!$case_no) {
        $_SESSION['message'] = 'Error: No case number provided for archiving.';
        echo json_encode(['success' => false, 'message' => 'No case number provided.']); // Still send JSON for JS fallback/logging
        exit;
    }

    // Database connection
    $db_server = "localhost";
    $db_user = "u416486854_p1";
    $db_pass = "2&rnLACGCldK";
    $db_name = "u416486854_p1";
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

    if (!$conn) {
        $_SESSION['message'] = 'Error: Database connection failed.';
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. Fetch the complaint details from 'complaints'
        $stmt_select = mysqli_prepare($conn, "SELECT * FROM complaints WHERE case_no = ?");
        mysqli_stmt_bind_param($stmt_select, "s", $case_no);
        mysqli_stmt_execute($stmt_select);
        $result_select = mysqli_stmt_get_result($stmt_select);
        $complaint_data = mysqli_fetch_assoc($result_select);
        mysqli_stmt_close($stmt_select);

        if (!$complaint_data) {
            throw new Exception("Complaint not found.");
        }

        // 2. Insert into 'reports_archive'
        $columns = implode(", ", array_keys($complaint_data));
        $placeholders = implode(", ", array_fill(0, count($complaint_data), "?"));
        $values = array_values($complaint_data);

        $stmt_insert = mysqli_prepare($conn, "INSERT INTO reports_archive ($columns) VALUES ($placeholders)");
        // Dynamically bind parameters (all values are strings for simplicity here, adjust if types vary)
        $types = str_repeat('s', count($values)); // Assuming all are strings, adjust as needed
        mysqli_stmt_bind_param($stmt_insert, $types, ...$values);
        
        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception("Error inserting into archive: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt_insert);

        // 3. Delete from 'complaints'
        $stmt_delete = mysqli_prepare($conn, "DELETE FROM complaints WHERE case_no = ?");
        mysqli_stmt_bind_param($stmt_delete, "s", $case_no);
        if (!mysqli_stmt_execute($stmt_delete)) {
            throw new Exception("Error deleting from active reports: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt_delete);

        mysqli_commit($conn);
        $_SESSION['message'] = 'Complaint ' . htmlspecialchars($case_no) . ' successfully archived!';
        echo json_encode(['success' => true]); // Return success JSON
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error archiving complaint ' . htmlspecialchars($case_no) . ': ' . $e->getMessage();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;

    } finally {
        mysqli_close($conn);
    }
} else {
    $_SESSION['message'] = 'Error: Invalid request method.';
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}
?>