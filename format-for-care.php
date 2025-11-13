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

// Fetch all active cases for dropdown
$cases_result = $conn->query("SELECT case_no, complaint_description, victim_first_name, victim_last_name FROM complaints ORDER BY case_no DESC");

// Fetch care formats with case details
$care_sql = "
    SELECT cf.*, c.complaint_description, c.victim_first_name, c.victim_last_name
    FROM care_formats cf
    LEFT JOIN complaints c ON cf.case_no = c.case_no
    ORDER BY cf.created_datetime DESC
";
$care_result = $conn->query($care_sql);

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
            <a href="format-for-care.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . $activeClasses . '">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="sidebar-text ml-3">Care Management</span>
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
    <title>Care Management - Barangay San Miguel</title>
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
                    <h1 class="text-3xl font-bold">Care Management</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <main class="p-6 flex-1 overflow-y-auto">
                <!-- Add New Care Entry Form -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Add Care Entry</h2>
                    <form id="careForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Case Number <span class="text-red-500">*</span></label>
                            <select name="case_no" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Case</option>
                                <?php
                                while ($case = $cases_result->fetch_assoc()) {
                                    $case_label = "Case #{$case['case_no']} - {$case['complaint_description']}";
                                    echo "<option value='{$case['case_no']}'>" . htmlspecialchars($case_label) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Care Type <span class="text-red-500">*</span></label>
                            <select name="care_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="mediation">Mediation</option>
                                <option value="counseling">Counseling</option>
                                <option value="legal_assistance">Legal Assistance</option>
                                <option value="referral">Referral</option>
                                <option value="follow_up">Follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Care Date <span class="text-red-500">*</span></label>
                            <input type="date" name="care_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Care Provider</label>
                            <input type="text" name="care_provider" placeholder="Optional" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="care_status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="scheduled">Scheduled</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea name="care_description" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                Add Care Entry
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Care Entries List -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Care Entries</h2>
                    </div>
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Case #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Care Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            if ($care_result && $care_result->num_rows > 0) {
                                while ($row = $care_result->fetch_assoc()) {
                                    $status_colors = [
                                        'scheduled' => 'bg-yellow-100 text-yellow-800',
                                        'ongoing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $status_class = $status_colors[$row['care_status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium">#<?php echo $row['case_no']; ?></td>
                                        <td class="px-6 py-4 text-sm"><?php echo ucfirst(str_replace('_', ' ', $row['care_type'])); ?></td>
                                        <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($row['care_provider'] ?: 'N/A'); ?></td>
                                        <td class="px-6 py-4 text-sm"><?php echo date('M d, Y', strtotime($row['care_date'])); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                                <?php echo ucfirst($row['care_status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars(substr($row['care_description'], 0, 100)) . (strlen($row['care_description']) > 100 ? '...' : ''); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No care entries found</td></tr>';
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
        document.getElementById('careForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            try {
                const formData = new FormData(this);

                const response = await fetch('actions/add_care_format.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('Care entry added successfully!');
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to add care entry');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
