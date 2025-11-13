<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $case_no = $input['case_no'] ?? null;

    if (!$case_no) {
        $_SESSION['message'] = 'Error: No case number provided for unarchiving.';
        echo json_encode(['success' => false, 'message' => 'No case number provided.']);
        exit;
    }

    // Database connection
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "p1";
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

    if (!$conn) {
        $_SESSION['message'] = 'Error: Database connection failed.';
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. Fetch the complaint details from 'reports_archive'
        $stmt_select = mysqli_prepare($conn, "SELECT * FROM reports_archive WHERE case_no = ?");
        mysqli_stmt_bind_param($stmt_select, "s", $case_no);
        mysqli_stmt_execute($stmt_select);
        $result_select = mysqli_stmt_get_result($stmt_select);
        $complaint_data = mysqli_fetch_assoc($result_select);
        mysqli_stmt_close($stmt_select);

        if (!$complaint_data) {
            throw new Exception("Archived complaint not found.");
        }

        // 2. Insert into 'complaints'
        $columns = implode(", ", array_keys($complaint_data));
        $placeholders = implode(", ", array_fill(0, count($complaint_data), "?"));
        $values = array_values($complaint_data);

        $stmt_insert = mysqli_prepare($conn, "INSERT INTO complaints ($columns) VALUES ($placeholders)");
        $types = str_repeat('s', count($values)); // Assuming all are strings
        mysqli_stmt_bind_param($stmt_insert, $types, ...$values);
        
        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception("Error inserting into active reports: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt_insert);

        // 3. Delete from 'reports_archive'
        $stmt_delete = mysqli_prepare($conn, "DELETE FROM reports_archive WHERE case_no = ?");
        mysqli_stmt_bind_param($stmt_delete, "s", $case_no);
        if (!mysqli_stmt_execute($stmt_delete)) {
            throw new Exception("Error deleting from archive: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt_delete);

        mysqli_commit($conn);
        $_SESSION['message'] = 'Complaint ' . htmlspecialchars($case_no) . ' successfully unarchived!';
        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error unarchiving complaint ' . htmlspecialchars($case_no) . ': ' . $e->getMessage();
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