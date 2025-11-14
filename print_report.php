<?php
session_start();

// Check authentication
if (!isset($_SESSION['google_loggedin']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'desk officer')) {
    header('Location: index.php');
    exit;
}

$google_loggedin = $_SESSION['google_loggedin'];
$google_email = $_SESSION['google_email'];
$google_name = $_SESSION['google_name'];
$google_picture = $_SESSION['google_picture'];

// Database connection
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception) {
    die("Database connection failed.");
}

// Get report data
$case_no = isset($_GET['case_no']) ? mysqli_real_escape_string($conn, $_GET['case_no']) : '';
$is_archive = isset($_GET['archive']) && $_GET['archive'] === 'true';
$table = $is_archive ? 'reports_archive' : 'complaints';

if (empty($case_no)) {
    die("Case number is required.");
}

$sql = "SELECT * FROM $table WHERE case_no = '$case_no'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Report not found.");
}

$report = mysqli_fetch_assoc($result);

// Get complaint type display
$complaint_type = $report['complaint_description'];
if ($complaint_type === 'Others' && !empty($report['other_complaint'])) {
    $complaint_type = $report['other_complaint'];
}

// Format case number as: complaint_no-YYYY-MM-DD-HH-MM-SS
$display_case_no = $report['case_no']; // Default
if (!empty($report['complaint_no']) && !empty($report['incident_datetime'])) {
    $date = new DateTime($report['incident_datetime']);
    $display_case_no = $report['complaint_no'] . '-' . $date->format('Y-m-d-H-i-s');
}

// Determine if incident should be endorsed to PNP
// Incidents that should be endorsed to PNP (serious crimes)
$pnp_endorsement_types = [
    'Theft',
    'Robbery',
    'Physical Assault',
    'Murder',
    'Homicide',
    'Rape',
    'Drug-related',
    'Illegal Gambling',
    'Carnapping',
    'Arson',
    'Kidnapping',
    'Illegal Possession of Firearms',
    'Violation of Special Laws'
];

$should_endorse_to_pnp = in_array($complaint_type, $pnp_endorsement_types);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report - <?= htmlspecialchars($display_case_no) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        #map { height: 250px; width: 100%; border: 2px solid #e5e7eb; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 text-white p-6 flex justify-between items-center no-print">
            <h1 class="text-2xl font-bold">Incident Report Format</h1>
            <div class="flex gap-3">
                <button onclick="window.print()" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 font-semibold">
                    Print Report
                </button>
                <button onclick="window.close()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
                    Close
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="p-8">
            <!-- Barangay Header -->
            <div class="text-center mb-6 border-b-2 border-gray-300 pb-4">
                <h2 class="text-2xl font-bold text-gray-800">BARANGAY SAN MIGUEL</h2>
                <p class="text-gray-600">Pasig City, Metro Manila</p>
                <p class="text-lg font-semibold text-blue-600 mt-2">INCIDENT REPORT</p>
            </div>

            <!-- Case Number -->
            <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm text-gray-600">Care Number:</p>
                <p class="text-2xl font-bold text-blue-700"><?= htmlspecialchars($display_case_no) ?></p>
            </div>

            <!-- PNP Endorsement Status -->
            <div class="mb-6 p-4 rounded-lg border-2 <?= $should_endorse_to_pnp ? 'bg-red-50 border-red-300' : 'bg-green-50 border-green-300' ?>">
                <p class="text-sm font-semibold mb-1 <?= $should_endorse_to_pnp ? 'text-red-800' : 'text-green-800' ?>">PNP Endorsement Status:</p>
                <p class="text-lg font-bold <?= $should_endorse_to_pnp ? 'text-red-900' : 'text-green-900' ?>">
                    <?= htmlspecialchars($endorsement_status) ?>
                </p>
                <?php if ($should_endorse_to_pnp): ?>
                <p class="text-sm text-red-700 mt-2">
                    ⚠️ This case involves a serious crime and should be referred to the Philippine National Police for investigation and proper legal action.
                </p>
                <?php else: ?>
                <p class="text-sm text-green-700 mt-2">
                    ✓ This case can be handled through barangay mediation and settlement procedures.
                </p>
                <?php endif; ?>
            </div>

            <!-- Incident Details -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Detalye ng Insidente</h3>

                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Petsa at Oras ng Insidente:</p>
                        <p class="font-semibold"><?= date('F j, Y g:i A', strtotime($report['incident_datetime'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Uri ng Reklamo:</p>
                        <p class="font-semibold"><?= htmlspecialchars($complaint_type) ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Lugar ng Pinangyarihan:</p>
                    <p class="font-semibold mb-3"><?= htmlspecialchars($report['incident_location']) ?></p>

                    <?php if (!empty($report['incident_latitude']) && !empty($report['incident_longitude'])): ?>
                    <div id="map"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const lat = <?= $report['incident_latitude'] ?>;
                            const lng = <?= $report['incident_longitude'] ?>;

                            const map = L.map('map').setView([lat, lng], 16);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(map);

                            L.marker([lat, lng]).addTo(map)
                                .bindPopup('Incident Location')
                                .openPopup();
                        });
                    </script>
                    <?php else: ?>
                    <p class="text-sm text-gray-500 italic">Map location not available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Complainant Information -->
            <?php if (!empty($report['complainant_first_name'])): ?>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Impormasyon ng Nagrereklamo</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Pangalan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['complainant_first_name'] . ' ' . $report['complainant_middle_name'] . ' ' . $report['complainant_last_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Edad / Kasarian:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['complainant_age']) ?> / <?= htmlspecialchars($report['complainant_gender']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Telepono:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['complainant_phone'] ?: 'N/A') ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Tirahan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['complainant_address'] ?: 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Victim Information -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Impormasyon ng Biktima</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Pangalan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['victim_first_name'] . ' ' . $report['victim_middle_name'] . ' ' . $report['victim_last_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Edad / Kasarian:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['victim_age']) ?> / <?= htmlspecialchars($report['victim_gender']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Telepono:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['victim_phone'] ?: 'N/A') ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Tirahan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['victim_address'] ?: 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <!-- Witness Information -->
            <?php if (!empty($report['witness_first_name'])): ?>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Impormasyon ng Saksi</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Pangalan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['witness_first_name'] . ' ' . $report['witness_middle_name'] . ' ' . $report['witness_last_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Edad / Kasarian:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['witness_age']) ?> / <?= htmlspecialchars($report['witness_gender']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Telepono:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['witness_phone'] ?: 'N/A') ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Tirahan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['witness_address'] ?: 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Respondent Information -->
            <?php if (!empty($report['respondent_first_name'])): ?>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Impormasyon ng Inireklamo</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Pangalan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['respondent_first_name'] . ' ' . $report['respondent_middle_name'] . ' ' . $report['respondent_last_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Edad / Kasarian:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['respondent_age']) ?> / <?= htmlspecialchars($report['respondent_gender']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Telepono:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['respondent_phone'] ?: 'N/A') ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Tirahan:</p>
                        <p class="font-semibold"><?= htmlspecialchars($report['respondent_address'] ?: 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statement -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-3 border-b pb-2">Salaysay</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-gray-800 whitespace-pre-wrap"><?= htmlspecialchars($report['complaint_statement']) ?></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t-2 border-gray-300">
                <div class="grid md:grid-cols-2 gap-6 text-sm text-gray-600">
                    <div>
                        <p><strong>Received by:</strong> <?= htmlspecialchars($report['desk_officer_name'] ?: 'N/A') ?></p>
                        <p><strong>Date Received:</strong> <?= date('F j, Y g:i A', strtotime($report['received_datetime'])) ?></p>
                    </div>
                    <div class="text-right">
                        <p><strong>Reported by:</strong> <?= $report['reported_by'] ? 'Personal' : 'Other' ?></p>
                        <p><strong>Affirmed:</strong> <?= $report['is_affirmed'] ? 'Yes' : 'No' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>
