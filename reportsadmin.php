<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    $message = '';
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']); // Clear the message after use
    }

    // If the user is an admin, do nothing (continue script execution)
    if (isset($_SESSION['google_loggedin']) && $_SESSION['user_role'] === 'admin') {

    }
    // If the user is logged in AND their role is 'Desk Officer', redirect to test.php
    else if (isset($_SESSION['google_loggedin']) && $_SESSION['user_role'] === 'desk officer') {
        header('Location: dashboard.php');
        exit;
    }
    // If not logged in or any other role not explicitly handled, redirect to index.php
    else {
        header('Location: index.php');
        exit;
    }

    // Retrieve session variables (these will only be accessible if an admin or if the script continues for other reasons)
    $google_loggedin = $_SESSION['google_loggedin'];
    $google_email = $_SESSION['google_email'];
    $google_name = $_SESSION['google_name'];
    $google_picture = $_SESSION['google_picture'];
    $user_role = $_SESSION['user_role'];

    // Database connection details
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "p1";
    $conn = "";

    try {
        $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    } catch (mysqli_sql_exception) {
        die("Database connection failed.");
    }

    // Determine which table to query
    $table_to_query = 'complaints';
    $is_archive_mode = false;
    if (isset($_GET['mode']) && $_GET['mode'] === 'archive') {
        $table_to_query = 'reports_archive';
        $is_archive_mode = true;
    }


    // Pagination variables
    $limit = 7; // Number of records per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page, default to 1
    $offset = ($page - 1) * $limit; // Calculate the offset

    // --- SEARCH AND FILTERING LOGIC ---
    $search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';

    $where_clauses = [];
    $query_params = []; // To store query parameters for pagination links

        if (!empty($search_query)) {
        $where_clauses[] = "(case_no LIKE '%$search_query%' OR complainant_first_name LIKE '%$search_query%' OR complainant_last_name LIKE '%$search_query%' OR complaint_description LIKE '%$search_query%' OR desk_officer_name LIKE '%$search_query%')";
        $query_params['search'] = $search_query;
    }

    if (!empty($start_date)) {
        $where_clauses[] = "incident_datetime >= '$start_date 00:00:00'";
        $query_params['start_date'] = $start_date;
    }

    if (!empty($end_date)) {
        $where_clauses[] = "incident_datetime <= '$end_date 23:59:59'";
        $query_params['end_date'] = $end_date;
    }

    // Add archive mode to query params
    if ($is_archive_mode) {
        $query_params['mode'] = 'archive';
    }

    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = " WHERE " . implode(" AND ", $where_clauses);
    }

    // Construct query string for pagination links
    $query_string = http_build_query($query_params);
    if (!empty($query_string)) {
        $query_string = '&' . $query_string; // Prepend & for URL
    }
    // --- END SEARCH AND FILTERING LOGIC ---


    // Get total number of complaints for pagination links (with filters applied)
    $total_sql = "SELECT COUNT(*) AS total FROM " . $table_to_query . $where_sql;
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_complaints = $total_row['total'];
    $total_pages = ceil($total_complaints / $limit);

    // Fetch complaints with LIMIT and OFFSET (with filters applied)
    $sql = "SELECT case_no, complainant_first_name, complainant_last_name, incident_datetime, complaint_description, desk_officer_name, received_datetime
        FROM " . $table_to_query . "
        " . $where_sql . "
        ORDER BY case_no DESC
        LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);

    // Function to truncate text
    function truncate_text($text, $max_length = 15) {
        if (strlen($text) > $max_length) {
            return substr($text, 0, $max_length) . '...';
        }
        return $text;
    }
    /**
     * Renders the sidebar navigation panel.
     *
     * @param string $google_picture The URL for the user's profile picture.
     * @param string $google_name The name of the logged-in user.
     * @return void
     */
    function sidepanel($google_picture, $google_name) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        $activeClasses = 'bg-blue-500 text-white shadow';
        $inactiveClasses = 'text-gray-600 hover:bg-gray-100';

        // Prevents reloading the page when clicking the active link
        $dashboardClick = ($currentPage === 'dashboardadmin.php') ? 'onclick="event.preventDefault()"' : '';
        $blotterClick   = ($currentPage === 'blotteradmin.php')   ? 'onclick="event.preventDefault()"' : '';
        $reportsClick   = ($currentPage === 'reportsadmin.php')   ? 'onclick="event.preventDefault()"' : '';
        $accountsClick  = ($currentPage === 'accountsadmin.php')  ? 'onclick="event.preventDefault()"' : '';
        $settingsClick  = ($currentPage === 'settingsadmin.php')  ? 'onclick="event.preventDefault()"' : '';

        echo '
        <!-- START: Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white text-secondary flex flex-col p-4 items-center shadow-lg z-20">
            <div class="text-center py-4">
                <img src="pics/brgylogo.png" alt="Logo" class="w-24 mx-auto mb-2">
                <div class="sidebar-header-text">
                    <h2 class="text-xl font-bold text-gray-800">Barangay San Miguel</h2>
                    <small class="text-gray-500">Pasig City, Metro Manila</small>
                </div>
            </div>

            <nav class="flex flex-col space-y-2 w-full mt-6 text-lg">
                <a href="dashboardadmin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "dashboardadmin.php" ? $activeClasses : $inactiveClasses) . '" ' . $dashboardClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="blotteradmin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "blotteradmin.php" ? $activeClasses : $inactiveClasses) . '" ' . $blotterClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="sidebar-text ml-3">Blotter</span>
                </a>
                <a href="reportsadmin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "reportsadmin.php" ? $activeClasses : $inactiveClasses) . '" ' . $reportsClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="accountsadmin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "accountsadmin.php" ? $activeClasses : $inactiveClasses) . '" ' . $accountsClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span class="sidebar-text ml-3">Accounts</span>
                </a>
                <a href="settingsadmin.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "settingsadmin.php" ? $activeClasses : $inactiveClasses) . '" ' . $settingsClick . '>
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
                    <span class="sidebar-text font-medium text-gray-800">' . htmlspecialchars($google_name ?? "User") . '</span>
                </div>
            </div>
        </div>
        <!-- END: Sidebar -->
        ';
}

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay San Miguel Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0E2F65',
                        'secondary': '#1D4ED8',
                        'light-gray': '#F3F4F6',
                        'active-blue': '#BFDBFE',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="css/main.css">
   <style>
        /* Optional: Basic tooltip styling for better visibility */
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            white-space: nowrap;
            z-index: 50; /* Ensure tooltip is above other content */
            pointer-events: none; /* Allows interactions with elements behind the tooltip */
            transform: translateY(-50%); /* Adjust positioning */
            left: 50%;
            transform: translateX(-50%) translateY(calc(-100% - 5px)); /* Center above */
        }
        [data-tooltip] {
            position: relative;
        }
        .clickable-row:hover {
        background-color: #f0f0f0; /* Light gray on hover */
        cursor: pointer;
    }
    </style>
</head>
<body class="bg-light-gray">

    <?php
    echo '<script>';
    echo 'if (localStorage.getItem(\'sidebarState\') === \'collapsed\') {';
    echo '    document.documentElement.classList.add(\'js-sidebar-initial-collapsed\');';
    echo '}';
    echo '</script>';
    ?>

    <div class="flex h-screen overflow-hidden">
        <?php
            // Call the function to render the sidebar
            sidepanel($google_picture, $google_name);
        ?>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 ml-64 flex flex-col">

            <!-- Global Message Container -->
            <div id="global-message"class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[100] transition-all duration-300 ease-out transform opacity-0 scale-95 hidden">
                <div class="px-6 py-3 rounded-lg shadow-lg text-white text-center w-auto max-w-lg bg-gray-800">
                    <span id="global-message-text" class="font-medium"></span>
                </div>
            </div>
            
            <!-- Top Header -->
            <header class="bg-primary text-white p-4 flex justify-between items-center shadow-md z-10">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="mr-4 text-white hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-3xl font-bold">Incident Reports (<?= $is_archive_mode ? 'Archived' : 'Active' ?>)</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <main class="p-6 flex-1 overflow-y-auto">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <!-- Filters and Search Section -->
                    <div class="flex flex-col md:flex-row items-center justify-between mb-4 gap-4">
                        <div class="relative w-full md:w-1/3">
                            <input type="text" id="search-input" placeholder="Search by Case No, Complainant..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary" value="<?= htmlspecialchars($search_query) ?>">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <div class="flex flex-col sm:flex-row w-full md:w-auto gap-4">
                            <input type="date" id="start-date" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-secondary" value="<?= htmlspecialchars($start_date) ?>">
                            <input type="date" id="end-date" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-secondary" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="flex-grow flex justify-end gap-4">
                            <button id="archive-toggle-btn" class="w-32 h-10 flex items-center justify-center rounded-lg <?= $is_archive_mode ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                <?= $is_archive_mode ? 'View Active' : 'View Archived' ?>
                            </button>
                            <button id="apply-filters" class="w-32 h-10 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Apply Filters</button>
                            <button id="clear-filters" class="w-32 h-10 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Clear Filters</button>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">Case No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Complainant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">Date of Incident</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[30%]">Type of Incident</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Desk Officer</th>
                                </tr>
                            </thead>
                            <tbody id="reports-tbody" class="divide-y divide-gray-200">
                                <?php
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
					$case_no_format = htmlspecialchars($row['case_no'] . '-' . date('Y-m-d-H-i-s', strtotime($row['incident_datetime'])));
                                        $incident_date = date('Y-m-d', strtotime($row['incident_datetime']));
                                        $complainant_name = htmlspecialchars($row['complainant_first_name'] . ' ' . $row['complainant_last_name']);
                                        $full_description = htmlspecialchars($row['complaint_description']);
                                        $truncated_description = truncate_text($full_description, 20);
                                ?>
                                    <tr class="clickable-row"
                                    data-case-no="<?= htmlspecialchars($row['case_no']) ?>" 
                                    data-archived="<?= $is_archive_mode ? 'true' : 'false' ?>"
                                    data-received-datetime="<?= htmlspecialchars($row['received_datetime']) ?>">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate w-[15%]"><?= $case_no_format ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600 truncate w-[20%]"><?= $complainant_name ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600 truncate w-[15%]"><?= $incident_date ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600 truncate w-[30%]">
                                        <span class="block max-w-full overflow-hidden text-ellipsis whitespace-nowrap" data-tooltip="<?= $full_description ?>">
                                            <?= $truncated_description ?>
                                        </span>
                                        <td class="px-6 py-4 text-sm text-gray-600 w-[25%]"><?= htmlspecialchars($row['desk_officer_name'] ?? 'N/A') ?></td>
                                            
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center px-6 py-4 text-gray-500'>No records found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex justify-center mt-4">
                        <div class="flex space-x-2">
                            <a href="?page=<?= max(1, $page - 1) . $query_string ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 <?= ($page <= 1) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </a>
                            <a href="?page=<?= min($total_pages, $page + 1) . $query_string ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 <?= ($page >= $total_pages) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                Next
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Report Details Modal -->
    <div id="details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-2xl font-bold text-gray-800">Incident Report Details</h3>
                <button id="close-details-modal" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="details-modal-content" class="text-gray-700 space-y-2">
                <!-- Details will be loaded here -->
            </div>
             <div class="flex justify-end mt-6 pt-4 border-t space-x-3">
                <!-- NEW BUTTON ADDED HERE -->
                <button id="action-button-in-details-modal" class="px-6 py-2 rounded-lg"></button>
                <button id="close-details-modal-footer" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Close</button>
            </div>
        </div>
    </div>

    
    <!-- For now, I'll keep it, but we'll stop triggering it in the JS -->
    <div id="archive-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full">
            <h3 id="modal-title" class="text-lg font-bold mb-4"></h3>
            <p class="mb-4"><span id="modal-message"></span> Case No: <span id="modal-case-no" class="font-semibold"></span>?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-action" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Cancel</button>
                <button id="confirm-action" class="px-4 py-2 rounded-lg"></button>
            </div>
        </div>
    </div>

    <script src="js/sidebar.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const archiveToggleBtn = document.getElementById('archive-toggle-btn');
    let currentMode = '<?= $is_archive_mode ? "archive" : "active" ?>';

    function buildUrl(includeMode = true) {
        const searchValue = searchInput.value;
        const startDateValue = startDateInput.value;
        const endDateValue = endDateInput.value;

        let params = new URLSearchParams();
        params.set('page', '1'); // Always go to page 1 when applying new filters

        if (searchValue) {
            params.set('search', searchValue);
        }
        if (startDateValue) {
            params.set('start_date', startDateValue);
        }
        if (endDateValue) {
            params.set('end_date', endDateValue);
        }
        if (includeMode && currentMode === 'archive') {
            params.set('mode', 'archive');
        }

        return 'reportsadmin.php?' + params.toString();
    }

    function applyFilters() {
        window.location.href = buildUrl(true); // Always include mode when applying filters
    }

    function clearFilters() {
        // To clear filters, we reset to the default view (page 1, active mode, no search/dates)
        // We need to decide if clearing filters should revert to active mode or stay in archive mode
        // For now, let's assume clearing filters means going back to active reports.
        window.location.href = 'reportsadmin.php'; // Clears all parameters including 'mode'
    }

    function toggleArchiveMode() {
        currentMode = (currentMode === 'active') ? 'archive' : 'active';
        // When toggling, we want to keep current search/date filters if they exist
        window.location.href = buildUrl(true);
    }

    applyFiltersBtn.addEventListener('click', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
    archiveToggleBtn.addEventListener('click', toggleArchiveMode);

    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            applyFilters();
        }
    });

    // --- GLOBAL MESSAGE LOGIC START ---
    const globalMessageContainer = document.getElementById('global-message');
    const globalMessageContent = globalMessageContainer.querySelector('div'); // Get the inner div
    const globalMessageText = document.getElementById('global-message-text');

    // PHP-injected message from server-side (only if it exists)
    const serverMessage = "<?php echo !empty($message) ? htmlspecialchars($message) : ''; ?>";

    if (serverMessage) {
        globalMessageText.textContent = serverMessage;

        // Clear previous background classes
        globalMessageContent.classList.remove('bg-red-500', 'bg-green-500');

        if (serverMessage.includes('Error')) {
            globalMessageContent.classList.add('bg-red-500');
        } else {
            globalMessageContent.classList.add('bg-green-500');
        }
        globalMessageContent.classList.add('text-white'); // Apply text color to the inner div

        // 1. Ensure pointer-events is initially none (as it's hidden)
        globalMessageContainer.style.pointerEvents = 'none';

        // 2. Remove 'hidden' and apply initial styles for transition
        globalMessageContainer.classList.remove('hidden');
        // Force a reflow to ensure initial styles are applied before transition starts
        void globalMessageContainer.offsetWidth;

        // 3. Start the fade-in and make it interactive
        globalMessageContainer.classList.remove('opacity-0', 'scale-95');
        globalMessageContainer.classList.add('opacity-100', 'scale-100');
        globalMessageContainer.style.pointerEvents = 'auto'; // Make it clickable (if needed, but mainly to block underneath)


        // Automatically hide after a delay
        setTimeout(() => {
            // Start fade out and make it non-interactive immediately
            globalMessageContainer.classList.remove('opacity-100', 'scale-100');
            globalMessageContainer.classList.add('opacity-0', 'scale-95');
            globalMessageContainer.style.pointerEvents = 'none'; // Crucial: disable pointer events *as soon as* fade-out starts

            setTimeout(() => {
                globalMessageContainer.classList.add('hidden'); // Fully hide after fade out animation
            }, 300); // This should match your transition duration (duration-300)
        }, 3000); // 3 seconds delay before starting to fade out
    }
    // --- GLOBAL MESSAGE LOGIC END ---


    // MODAL LOGIC FOR ARCHIVE/UNARCHIVE
    let caseNoToActOn = null;
    let isReportCurrentlyArchived = false; // Track the status of the report shown in details modal

    // Details Modal elements
    const detailsModal = document.getElementById('details-modal');
    const detailsModalContent = document.getElementById('details-modal-content');
    const closeDetailsModalBtn = document.getElementById('close-details-modal');
    const closeDetailsModalFooterBtn = document.getElementById('close-details-modal-footer');
    const actionButtonInDetailsModal = document.getElementById('action-button-in-details-modal'); // The new button in details modal

    const editButtonInDetailsModal = document.createElement('button');
    editButtonInDetailsModal.id = 'edit-report-button';
    editButtonInDetailsModal.className = 'px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600';
    editButtonInDetailsModal.textContent = 'Edit Report';

    const saveButtonInDetailsModal = document.createElement('button');
    saveButtonInDetailsModal.id = 'save-report-button';
    saveButtonInDetailsModal.className = 'px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 hidden';
    saveButtonInDetailsModal.textContent = 'Save Changes';

    // Insert the new buttons before the close button in the footer
    const detailsModalFooter = detailsModal.querySelector('.flex.justify-end.mt-6.pt-4.border-t.space-x-3');
    detailsModalFooter.insertBefore(editButtonInDetailsModal, detailsModalFooter.children[0]);
    detailsModalFooter.insertBefore(saveButtonInDetailsModal, detailsModalFooter.children[1]);


    let isEditMode = false; // State variable for edit mode

    function setEditMode(enable) {
        isEditMode = enable;
        const inputs = detailsModalContent.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name !== 'case_no' && input.name !== 'created_at' && input.name !== 'desk_officer_name') { // Keep case_no non-editable
                input.readOnly = !enable;
                input.disabled = !enable;
                input.classList.toggle('bg-gray-50', !enable); // Visual cue for non-editable
                input.classList.toggle('bg-white', enable); // <--- ADD THIS LINE: Visual cue for editable fields
            }
        });

        editButtonInDetailsModal.classList.toggle('hidden', enable);
        actionButtonInDetailsModal.classList.toggle('hidden', enable); // Hide archive/unarchive when editing
        saveButtonInDetailsModal.classList.toggle('hidden', !enable);

         // Toggle text for the close button
        if (enable) {
            closeDetailsModalFooterBtn.textContent = 'Cancel Edit';
        } else {
            closeDetailsModalFooterBtn.textContent = 'Close';
        }
    }

    // Function to handle the actual archive/unarchive action
    function performArchiveUnarchiveAction() {
        if (caseNoToActOn) {
            let endpoint = isReportCurrentlyArchived ? 'actions/unarchive_complaint.php' : 'actions/archive_complaint.php';
            // No need for success/error messages here, as PHP handles the message and reload
            
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ case_no: caseNoToActOn })
            })
            .then(response => response.json()) // Still parse JSON even if we reload, for debugging/fallback
            .then(data => {
                // Regardless of 'data.success' we initiate a page reload.
                // The PHP action scripts are now responsible for setting $_SESSION['message']
                // and the PHP at the top of reportsadmin.php will pick it up.
                window.location.reload(); 
            })
            .catch(error => {
                console.error('Error:', error);
                // If the fetch itself fails (e.g., network error), we might want a client-side alert
                // as the PHP message won't be set.
                alert('An unexpected error occurred during the action. Please try again.');
                // Consider reloading here too if you want to ensure state consistency
                window.location.reload();
            });
        }
    }


    const keyMapping = {
        'case_no': 'Case Number',
        'incident_datetime': 'Date & Time of Incident',
        'incident_location': 'Location of Incident',
        'complaint_description': 'Type of Complaint',
        'other_complaint': 'Other Complaint Details',

        // Complainant Details
        'complainant_first_name': 'Complainant First Name',
        'complainant_middle_name': 'Complainant Middle Name',
        'complainant_last_name': 'Complainant Last Name',
        'complainant_age': 'Complainant Age',
        'complainant_gender': 'Complainant Gender',
        'complainant_phone': 'Complainant Phone',
        'complainant_address': 'Complainant Address',

        // Victim Details
        'victim_first_name': 'Victim First Name',
        'victim_middle_name': 'Victim Middle Name',
        'victim_last_name': 'Victim Last Name',
        'victim_age': 'Victim Age',
        'victim_gender': 'Victim Gender',
        'victim_phone': 'Victim Phone',
        'victim_address': 'Victim Address',

        // Witness Details
        'witness_first_name': 'Witness First Name',
        'witness_middle_name': 'Witness Middle Name',
        'witness_last_name': 'Witness Last Name',
        'witness_age': 'Witness Age',
        'witness_gender': 'Witness Gender',
        'witness_phone': 'Witness Phone',
        'witness_address': 'Witness Address',

        // Respondent Details
        'respondent_first_name': 'Respondent First Name',
        'respondent_middle_name': 'Respondent Middle Name',
        'respondent_last_name': 'Respondent Last Name',
        'respondent_age': 'Respondent Age',
        'respondent_gender': 'Respondent Gender',
        'respondent_phone': 'Respondent Phone',
        'respondent_address': 'Respondent Address',

        // Complaint Statement & Other
        'complaint_statement': 'Complaint Statement',
        'reported_by': 'Reported By',
        'is_affirmed': 'Information Affirmed',
        'desk_officer_name': 'Desk Officer Name',
        'created_at': 'Report Filed On'
    };

        // Utility function for HTML escaping, as template literals don't escape automatically
    function htmlspecialchars(str) {
        if (typeof str !== 'string' && typeof str !== 'number') { // Also handle numbers as-is
            return str; // Return non-string/non-number values as-is (like null, undefined)
        }
        // Convert numbers to string before escaping
        const s = String(str);
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return s.replace(/[&<>"']/g, function(m) { return map[m]; });
    }


    function formatDetail(key, value) {
        // Treat null/undefined as empty string for display/input, but allow 0 for age
        if (value === null || value === undefined) {
            value = '';
        }

        let displayValue = value; // This is primarily for non-editable display
        let inputType = 'text';
        let isSelect = false;
        let selectOptions = [];
        let isEditable = true; // Most fields will be editable by default

        // Specific handling for certain keys
        if (key === 'reported_by' || key === 'is_affirmed') {
            isSelect = true;
            selectOptions = [{value: '1', text: 'Yes'}, {value: '0', text: 'No'}];
            // For display: 'Yes'/'No', for input: '1'/'0'
            displayValue = (value == 1) ? 'Yes' : (value == 0 ? 'No' : '');
        } else if (key === 'complainant_gender' || key === 'victim_gender' || key === 'witness_gender' || key === 'respondent_gender') {
            isSelect = true;
            selectOptions = [{value: '', text: 'Select Gender'}, {value: 'Male', text: 'Male'}, {value: 'Female', text: 'Female'}, {value: 'Other', text: 'Other'}];
        } else if (key.includes('_age')) {
            inputType = 'number';
            // HTML number inputs can have empty value, no need to default 0
        } else if (key === 'incident_datetime') {
            inputType = 'datetime-local';
            // Format incident_datetime for datetime-local input
            if (value) {
                const date = new Date(value);
                // Ensure two digits for month, day, hour, minute
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                displayValue = `${year}-${month}-${day}T${hours}:${minutes}`;
            } else {
                displayValue = '';
            }
        } else if (key === 'created_at' || key === 'case_no' || key === 'desk_officer_name') {
            isEditable = false; // These fields should not be editable
        }

        const label = keyMapping[key] || key.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());

        let inputElement;
        if (!isEditable) {
            // Non-editable fields are displayed as plain text (or N/A)
            inputElement = `<span class="col-span-2 text-gray-700">${value ? (key === 'incident_datetime' || key === 'created_at' ? new Date(value).toLocaleString() : htmlspecialchars(value)) : 'N/A'}</span>`;
        } else if (isSelect) {
            inputElement = `<select name="${key}" class="col-span-2 form-select border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-secondary">`;
            selectOptions.forEach(option => {
                // Compare string value for selected state
                inputElement += `<option value="${htmlspecialchars(option.value)}" ${String(value) === String(option.value) ? 'selected' : ''}>${htmlspecialchars(option.text)}</option>`;
            });
            inputElement += `</select>`;
        } else if (key === 'complaint_statement' || key === 'complaint_description' || key === 'other_complaint') {
            // Use textarea for multi-line text fields
            inputElement = `<textarea name="${key}" class="col-span-2 form-textarea border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-secondary" rows="3">${htmlspecialchars(value)}</textarea>`;
        }
        else {
            // Default to text input for other editable fields
            inputElement = `<input type="${inputType}" name="${key}" value="${htmlspecialchars(value)}" class="col-span-2 form-input border rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-secondary">`;
        }

        return `<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-1 border-b border-gray-100">
                    <strong class="col-span-1 text-gray-900">${label}:</strong>
                    ${inputElement}
                </div>`;
    }


    document.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent modal from triggering if something inside the row is clicked

            caseNoToActOn = this.dataset.caseNo;
            isReportCurrentlyArchived = this.dataset.archived === 'true'; // Set global status
            const receivedDatetimeStr = this.dataset.receivedDatetime; // Get the received_datetime
            let canEdit = true; // Assume editable by default

            // Check if the report is not archived AND within the 30-minute window
            if (!isReportCurrentlyArchived && receivedDatetimeStr) {
                const receivedDate = new Date(receivedDatetimeStr);
                const currentTime = new Date();
                const thirtyMinutesAgo = new Date(currentTime.getTime() - (30 * 60 * 1000)); // 30 minutes in milliseconds

                if (receivedDate < thirtyMinutesAgo) {
                    canEdit = false; // Outside the 30-minute window
                }
            } else if (isReportCurrentlyArchived) {
                canEdit = false; // Archived reports cannot be edited
            }

            // Enable/disable the edit button based on 'canEdit'
            if (canEdit) {
                editButtonInDetailsModal.classList.remove('hidden', 'opacity-50', 'cursor-not-allowed');
                editButtonInDetailsModal.removeAttribute('disabled');
                editButtonInDetailsModal.setAttribute('title', 'Edit this report');
            } else {
                editButtonInDetailsModal.classList.add('hidden'); // Use hidden to fully remove from layout
                editButtonInDetailsModal.classList.add('opacity-50', 'cursor-not-allowed');
                editButtonInDetailsModal.setAttribute('disabled', 'true');
                // Provide a tooltip explaining why it's disabled
                if (isReportCurrentlyArchived) {
                    editButtonInDetailsModal.setAttribute('title', 'Archived reports cannot be edited.');
                } else {
                    editButtonInDetailsModal.setAttribute('title', 'Reports can only be edited within 30 minutes of receipt.');
                }
            }


            // Update the action button based on the report's current status
            if (isReportCurrentlyArchived) {
                actionButtonInDetailsModal.textContent = 'Unarchive';
                actionButtonInDetailsModal.className = 'px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600';
            } else {
                actionButtonInDetailsModal.textContent = 'Archive';
                actionButtonInDetailsModal.className = 'px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600';
            }

            //detailsModalContent.innerHTML = '<p class="text-center text-gray-500">Loading details...</p>';
            detailsModal.classList.remove('hidden');
             //Fix Scrolling of modal, start at top
            if (detailsModal) {
                detailsModal.scrollTop = 0;
            }

            fetch('actions/get_report_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ case_no: caseNoToActOn, is_archive_mode: isReportCurrentlyArchived })
            })
            .then(response => response.json())
            .then(data => {
            if (data.success && data.data) {
                    let htmlContent = `<form id="edit-report-form" data-case-no="${data.data.case_no}">`; // Wrap in a form
                    htmlContent += `<p class="text-lg font-semibold mb-4">Case Number: ${data.data.case_no}</p>`;

                    const sections = {
                        'Incident Details': ['incident_datetime', 'incident_location', 'complaint_description', 'other_complaint'],
                        'Complainant Information': ['complainant_first_name', 'complainant_middle_name', 'complainant_last_name', 'complainant_age', 'complainant_gender', 'complainant_phone', 'complainant_address'],
                        'Victim Information': ['victim_first_name', 'victim_middle_name', 'victim_last_name', 'victim_age', 'victim_gender', 'victim_phone', 'victim_address'],
                        'Witness Information': ['witness_first_name', 'witness_middle_name', 'witness_last_name', 'witness_age', 'witness_gender', 'witness_phone', 'witness_address'],
                        'Respondent Information': ['respondent_first_name', 'respondent_middle_name', 'respondent_last_name', 'respondent_age', 'respondent_gender', 'respondent_phone', 'respondent_address'],
                        'Statement & Administration': ['complaint_statement', 'reported_by', 'is_affirmed', 'desk_officer_name', 'created_at']
                    };

                    for (const sectionTitle in sections) {
                        let sectionHtml = '';
                        sections[sectionTitle].forEach(key => {
                            const detailHtml = formatDetail(key, data.data[key]);
                            if (detailHtml) { // Only add if not empty
                                sectionHtml += detailHtml;
                            }
                        });

                        if (sectionHtml) { // Only show section if it has content
                            htmlContent += `<h4 class="text-lg font-semibold mt-4 mb-2 text-primary border-b pb-1">${sectionTitle}</h4>`;
                            htmlContent += sectionHtml;
                        }
                    }
                    htmlContent += `</form>`; // Close the form

                    detailsModalContent.innerHTML = htmlContent;
                    setEditMode(false); // Initially set to view mode when modal opens

                } else {
                    detailsModalContent.innerHTML = `<p class="text-center text-red-600">Failed to load details: ${data.message || 'Unknown error.'}</p>`;
                    setEditMode(false); // Ensure buttons are in correct state
                }
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                detailsModalContent.innerHTML = '<p class="text-center text-red-600">An error occurred while fetching report details.</p>';
            });
        });
    });

    // Action button inside the details modal
    editButtonInDetailsModal.addEventListener('click', function() {
        setEditMode(true);
    });

    // Add event listener for the "Save Changes" button
    saveButtonInDetailsModal.addEventListener('click', function() {
    const form = document.getElementById('edit-report-form');
    const formData = new FormData(form);
    const jsonData = {};

    // Convert FormData to JSON
    // IMPORTANT: Ensure the names in your HTML inputs/selects match your DB column names
    // or your PHP script needs to handle the mapping.
    for (let [key, value] of formData.entries()) {
        // Special handling for boolean-like values if needed, e.g., 'on' for checkboxes
        // For 'is_affirmed' from a select, '0' or '1' string values are fine.
        jsonData[key] = value;
    }
    jsonData['case_no'] = form.dataset.caseNo; // Ensure case_no is included

    // Pass the current archive status to the server, so it knows which table to update
    jsonData['is_archive_mode'] = isReportCurrentlyArchived; // This is good, ensure it's passed

    const endpoint = 'actions/update_report.php';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', // This header is correct for sending JSON
        },
        body: JSON.stringify(jsonData) // Send JSON string
    })
    .then(response => {
        // If the response is not ok (e.g., 404, 500) AND it's not JSON,
        // response.json() will throw an error. Catch it.
        if (!response.ok) {
            // Try to read as text first to see the PHP error message
            return response.text().then(text => {
                throw new Error(`HTTP error! Status: ${response.status}. Response: ${text}`);
            });
        }
        return response.json(); // Attempt to parse as JSON
    })
    .then(data => {
        if (data.success) {
            // The PHP script should now be setting the session message before reload
            window.location.reload();
        } else {
            alert('Error updating report: ' + (data.message || 'Unknown error.'));
        }
    })
    .catch(error => {
        console.error('Error saving changes:', error);
        alert('An unexpected error occurred while saving changes: ' + error.message);
    });
});
    actionButtonInDetailsModal.addEventListener('click', function() {
        performArchiveUnarchiveAction();
    });

    // Close details modal event listeners
    closeDetailsModalBtn.addEventListener('click', function() {
        detailsModal.classList.add('hidden');
        setEditMode(false); // <--- ADD THIS LINE
    });
    closeDetailsModalFooterBtn.addEventListener('click', function() {
        detailsModal.classList.add('hidden');
        setEditMode(false); // <--- ADD THIS LINE
    });
    detailsModal.addEventListener('click', function(event) {
        if (event.target === detailsModal) {
            detailsModal.classList.add('hidden');
            setEditMode(false); // <--- ADD THIS LINE
        }
    });

});
    </script>
</body>
</html>
<?php
// Close the database connection here, after all PHP processing is done
mysqli_close($conn);
?>