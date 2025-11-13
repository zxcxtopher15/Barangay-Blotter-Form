<?php
session_start();

if (!isset($_SESSION['google_loggedin'])) {
    header('Location: ../index.php');
    exit;
}

// Get case number and template from URL
$case_no = $_GET['case_no'] ?? null;
$template_type = $_GET['template'] ?? 'standard';

if (!$case_no) {
    die("Case number is required");
}

// Database connection
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }

    // Fetch complaint data
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE case_no = ?");
    $stmt->bind_param("i", $case_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Case not found");
    }

    $complaint = $result->fetch_assoc();
    $stmt->close();

    // Fetch template
    $stmt = $conn->prepare("SELECT * FROM report_templates WHERE template_type = ? LIMIT 1");
    $stmt->bind_param("s", $template_type);
    $stmt->execute();
    $template_result = $stmt->get_result();

    if ($template_result->num_rows === 0) {
        die("Template not found");
    }

    $template = $template_result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Helper function to format names
    function formatName($first, $middle, $last) {
        $parts = array_filter([$first, $middle, $last]);
        return implode(' ', $parts) ?: 'N/A';
    }

    // Prepare replacement data
    $replacements = [
        '{case_no}' => $complaint['case_no'],
        '{incident_datetime}' => date('F d, Y h:i A', strtotime($complaint['incident_datetime'])),
        '{incident_location}' => $complaint['incident_location'] ?: 'N/A',
        '{complaint_description}' => $complaint['complaint_description'] ?: 'N/A',
        '{complainant_name}' => formatName($complaint['complainant_first_name'], $complaint['complainant_middle_name'], $complaint['complainant_last_name']),
        '{complainant_age}' => $complaint['complainant_age'] ?: 'N/A',
        '{complainant_gender}' => $complaint['complainant_gender'] ?: 'N/A',
        '{complainant_phone}' => $complaint['complainant_phone'] ?: 'N/A',
        '{complainant_address}' => $complaint['complainant_address'] ?: 'N/A',
        '{victim_name}' => formatName($complaint['victim_first_name'], $complaint['victim_middle_name'], $complaint['victim_last_name']),
        '{victim_age}' => $complaint['victim_age'] ?: 'N/A',
        '{victim_gender}' => $complaint['victim_gender'] ?: 'N/A',
        '{victim_phone}' => $complaint['victim_phone'] ?: 'N/A',
        '{victim_address}' => $complaint['victim_address'] ?: 'N/A',
        '{witness_name}' => formatName($complaint['witness_first_name'], $complaint['witness_middle_name'], $complaint['witness_last_name']),
        '{witness_age}' => $complaint['witness_age'] ?: 'N/A',
        '{witness_gender}' => $complaint['witness_gender'] ?: 'N/A',
        '{witness_phone}' => $complaint['witness_phone'] ?: 'N/A',
        '{witness_address}' => $complaint['witness_address'] ?: 'N/A',
        '{respondent_name}' => formatName($complaint['respondent_first_name'], $complaint['respondent_middle_name'], $complaint['respondent_last_name']),
        '{respondent_age}' => $complaint['respondent_age'] ?: 'N/A',
        '{respondent_gender}' => $complaint['respondent_gender'] ?: 'N/A',
        '{respondent_phone}' => $complaint['respondent_phone'] ?: 'N/A',
        '{respondent_address}' => $complaint['respondent_address'] ?: 'N/A',
        '{complaint_statement}' => $complaint['complaint_statement'] ?: 'N/A',
        '{desk_officer_name}' => $complaint['desk_officer_name'] ?: 'N/A',
        '{received_datetime}' => date('F d, Y h:i A', strtotime($complaint['received_datetime']))
    ];

    // Generate formatted report
    $formatted_content = str_replace(array_keys($replacements), array_values($replacements), $template['template_content']);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formatted Report - Case #<?php echo $case_no; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Action Buttons (no-print) -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4 no-print flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($template['template_name']); ?></h2>
                <p class="text-sm text-gray-600">Case #<?php echo $case_no; ?></p>
            </div>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Print
                </button>
                <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                    Close
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="bg-white p-8 rounded-lg shadow-md">
            <!-- Header -->
            <div class="text-center mb-6 border-b-2 border-gray-300 pb-4">
                <img src="../pics/brgylogo.png" alt="Logo" class="h-20 mx-auto mb-2">
                <h1 class="text-xl font-bold">BARANGAY SAN MIGUEL</h1>
                <p class="text-sm">Pasig City, Metro Manila</p>
                <p class="text-sm text-gray-600">Barangay Blotter Report</p>
            </div>

            <!-- Formatted Content -->
            <div class="whitespace-pre-wrap text-sm leading-relaxed">
                <?php echo nl2br(htmlspecialchars($formatted_content)); ?>
            </div>

            <!-- Footer Signatures -->
            <div class="mt-12 grid grid-cols-2 gap-8">
                <div class="text-center">
                    <div class="border-t-2 border-gray-800 pt-2 mt-12">
                        <p class="font-semibold">Complainant Signature</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-gray-800 pt-2 mt-12">
                        <p class="font-semibold">Desk Officer Signature</p>
                        <p class="text-sm"><?php echo htmlspecialchars($complaint['desk_officer_name']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Document Footer -->
            <div class="mt-8 text-center text-xs text-gray-500">
                <p>Generated on <?php echo date('F d, Y h:i A'); ?></p>
                <p>This is a computer-generated document.</p>
            </div>
        </div>
    </div>
</body>
</html>
