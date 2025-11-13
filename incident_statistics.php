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
$user_role = $_SESSION['user_role'];

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

// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get most reported incidents
$sql_most_reported = "
    SELECT
        CASE
            WHEN complaint_description = 'Others' THEN other_complaint
            ELSE complaint_description
        END as incident_type,
        COUNT(*) as count
    FROM complaints
    WHERE incident_datetime BETWEEN ? AND ?
    GROUP BY incident_type
    ORDER BY count DESC
    LIMIT 10
";

$stmt = $conn->prepare($sql_most_reported);
$start_datetime = $start_date . ' 00:00:00';
$end_datetime = $end_date . ' 23:59:59';
$stmt->bind_param('ss', $start_datetime, $end_datetime);
$stmt->execute();
$result_most_reported = $stmt->get_result();
$most_reported = [];
while ($row = $result_most_reported->fetch_assoc()) {
    $most_reported[] = $row;
}
$stmt->close();

// Get total incidents count
$sql_total = "SELECT COUNT(*) as total FROM complaints WHERE incident_datetime BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param('ss', $start_datetime, $end_datetime);
$stmt->execute();
$total_result = $stmt->get_result();
$total_incidents = $total_result->fetch_assoc()['total'];
$stmt->close();

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Statistics - Barangay San Miguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Incident Statistics</h1>
                        <p class="text-gray-600">Most Reported Incidents</p>
                    </div>
                    <a href="<?= $user_role === 'admin' ? 'reportsadmin.php' : 'reports.php' ?>" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                        Back to Reports
                    </a>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">Apply Filter</button>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <!-- Total Incidents -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2">Total Incidents</h3>
                    <p class="text-4xl font-bold"><?= $total_incidents ?></p>
                    <p class="text-sm opacity-90 mt-2">
                        <?= date('M j, Y', strtotime($start_date)) ?> - <?= date('M j, Y', strtotime($end_date)) ?>
                    </p>
                </div>

                <!-- Most Reported Type -->
                <?php if (!empty($most_reported)): ?>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2">Most Reported</h3>
                    <p class="text-2xl font-bold"><?= htmlspecialchars($most_reported[0]['incident_type']) ?></p>
                    <p class="text-3xl font-bold mt-2"><?= $most_reported[0]['count'] ?> incidents</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Chart -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Incident Distribution</h3>
                <canvas id="incidentChart" class="w-full" style="max-height: 400px;"></canvas>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Detailed Breakdown</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incident Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $rank = 1;
                            foreach ($most_reported as $incident):
                                $percentage = $total_incidents > 0 ? round(($incident['count'] / $total_incidents) * 100, 1) : 0;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $rank++ ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold"><?= htmlspecialchars($incident['incident_type']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= $incident['count'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <span class="mr-2"><?= $percentage ?>%</span>
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($most_reported)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No incidents found for the selected date range.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for chart
        const labels = <?= json_encode(array_column($most_reported, 'incident_type')) ?>;
        const data = <?= json_encode(array_column($most_reported, 'count')) ?>;

        // Create chart
        const ctx = document.getElementById('incidentChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Incidents',
                    data: data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(249, 115, 22)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)',
                        'rgb(234, 179, 8)',
                        'rgb(236, 72, 153)',
                        'rgb(20, 184, 166)',
                        'rgb(251, 146, 60)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Incidents: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
