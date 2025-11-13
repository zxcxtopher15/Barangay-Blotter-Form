<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['google_loggedin'])) {
    header('Location: index.php');
    exit;
}

$google_picture = $_SESSION['google_picture'];
$google_name = $_SESSION['google_name'];
$user_role = $_SESSION['user_role'] ?? 'desk officer';

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
} catch (Exception $e) {
    die("Database connection failed");
}

// Fetch online reports
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM online_reports WHERE 1=1";

if ($status_filter !== 'all') {
    $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $sql .= " AND (submitter_name LIKE '%$search_term%' OR submitter_email LIKE '%$search_term%' OR complaint_type LIKE '%$search_term%')";
}

$sql .= " ORDER BY submission_datetime DESC";

$result = $conn->query($sql);

// Get counts by status
$pending_count = $conn->query("SELECT COUNT(*) as count FROM online_reports WHERE status = 'pending'")->fetch_assoc()['count'];
$reviewed_count = $conn->query("SELECT COUNT(*) as count FROM online_reports WHERE status = 'reviewed'")->fetch_assoc()['count'];
$converted_count = $conn->query("SELECT COUNT(*) as count FROM online_reports WHERE status = 'converted'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM online_reports WHERE status = 'rejected'")->fetch_assoc()['count'];

function sidepanel($google_picture, $google_name, $user_role) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $activeClasses = 'bg-blue-500 text-white shadow';
    $inactiveClasses = 'text-gray-600 hover:bg-gray-100';

    $isAdmin = ($user_role === 'admin');
    $dashboardFile = $isAdmin ? 'dashboardadmin.php' : 'dashboard.php';
    $blotterFile = $isAdmin ? 'blotteradmin.php' : 'blotter.php';
    $reportsFile = $isAdmin ? 'reportsadmin.php' : 'reports.php';
    $accountsFile = $isAdmin ? 'accountsadmin.php' : 'accounts.php';
    $settingsFile = $isAdmin ? 'settingsadmin.php' : 'settings.php';

    echo '
    <div id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white text-secondary flex flex-col p-4 items-center shadow-lg z-20">
        <div class="text-center py-4">
            <img src="pics/brgylogo.png" alt="Logo" class="w-24 mx-auto mb-2">
            <div class="sidebar-header-text">
                <h2 class="text-xl font-bold text-gray-800">Barangay San Miguel</h2>
                <small class="text-gray-500">Pasig City, Metro Manila</small>
            </div>
        </div>

        <nav class="flex flex-col space-y-2 w-full mt-6 text-lg">
            <a href="' . $dashboardFile . '" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $inactiveClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="sidebar-text ml-3">Dashboard</span>
            </a>
            <a href="' . $blotterFile . '" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $inactiveClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="sidebar-text ml-3">Blotter</span>
            </a>
            <a href="' . $reportsFile . '" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $inactiveClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                <span class="sidebar-text ml-3">Reports</span>
            </a>
            <a href="online-reports-admin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $activeClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="sidebar-text ml-3">Online Reports</span>
            </a>
            ' . ($isAdmin ? '<a href="' . $accountsFile . '" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $inactiveClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <span class="sidebar-text ml-3">Accounts</span>
            </a>' : '') . '
            <a href="' . $settingsFile . '" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $inactiveClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="sidebar-text ml-3">Settings</span>
            </a>
        </nav>

        <div class="mt-auto w-full border-t pt-4 space-y-4">
            <a href="logout.php" class="logout-link flex items-center px-4 py-3 rounded-lg text-left font-medium text-gray-600 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span class="sidebar-text ml-3">Logout</span>
            </a>
            <div class="flex items-center space-x-3">
                <img src="' . htmlspecialchars($google_picture) . '" alt="Profile Picture" class="w-10 h-10 rounded-full border-2 border-gray-300 shrink-0">
                <span class="sidebar-text font-medium text-gray-800">' . htmlspecialchars($google_name) . '</span>
            </div>
        </div>
    </div>
    ';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Reports - Barangay San Miguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0E2F65',
                        'secondary': '#1D4ED8',
                        'light-gray': '#F3F4F6',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body class="bg-light-gray">
    <div class="flex h-screen overflow-hidden">
        <?php sidepanel($google_picture, $google_name, $user_role); ?>

        <div id="mainContent" class="flex-1 ml-64 flex flex-col">
            <header class="bg-primary text-white p-4 flex justify-between items-center shadow-md z-10">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="mr-4 text-white hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-3xl font-bold">Online Reports</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <main class="p-6 flex-1 overflow-y-auto">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                        <p class="text-sm text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $pending_count; ?></p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">Reviewed</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $reviewed_count; ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">Converted to Case</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $converted_count; ?></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                        <p class="text-sm text-gray-600">Rejected</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $rejected_count; ?></p>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" placeholder="Search by name, email, or type..." value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="reviewed" <?php echo $status_filter === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            Filter
                        </button>
                        <a href="online-reports-admin.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                            Clear
                        </a>
                    </form>
                </div>

                <!-- Reports Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitter</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complaint Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'reviewed' => 'bg-blue-100 text-blue-800',
                                        'converted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800'
                                    ];
                                    $status_class = $status_colors[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm">#<?php echo $row['report_id']; ?></td>
                                        <td class="px-6 py-4 text-sm font-medium"><?php echo htmlspecialchars($row['submitter_name']); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php echo htmlspecialchars($row['submitter_email']); ?><br>
                                            <span class="text-gray-500"><?php echo htmlspecialchars($row['submitter_phone']); ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($row['complaint_type']); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo date('M d, Y h:i A', strtotime($row['submission_datetime'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <button onclick="viewReport(<?php echo $row['report_id']; ?>)"
                                                    class="text-blue-600 hover:underline mr-2">View</button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No online reports found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="js/sidebar.js" defer></script>
    <script>
        function viewReport(reportId) {
            // You can implement a modal or redirect to a detail page
            window.location.href = 'online-report-details.php?id=' + reportId;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
