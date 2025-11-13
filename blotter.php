<?php
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['google_loggedin']) && !isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit;
}

// Retrieve session variables
$google_loggedin = $_SESSION['google_loggedin'];
$google_email = $_SESSION['google_email'];
$google_name = $_SESSION['google_name'];
$google_picture = $_SESSION['google_picture'];

ini_set('display_errors', 1); // For debugging purposes
error_reporting(E_ALL);     // For debugging purposes

// Check if the form has been submitted
if (isset($_POST['submit_complaint'])) {
    // Database connection details
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "p1";
    $conn = null;

    try {
        $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    } catch (mysqli_sql_exception) {
        die("Database connection failed.");
    }

    // --- Data Processing ---
    // Combine date and time fields for incident_datetime
    $incident_date = $_POST['incident_date'];
    $hour = intval($_POST['incident_hour']);
    $minute = $_POST['incident_minute'];
    $period = $_POST['incident_period'];

    if ($period == 'PM' && $hour < 12) {
        $hour += 12;
    }
    if ($period == 'AM' && $hour == 12) {
        $hour = 0;
    }
    $hour_formatted = str_pad($hour, 2, '0', STR_PAD_LEFT);
    $incident_datetime = "$incident_date $hour_formatted:$minute:00";

    // Get complaint description (handle 'Others')
    $complaint_description = $_POST['complaint_description'];
    if ($complaint_description === 'Others') {
        $complaint_description = $_POST['other_complaint'];
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO complaints (
        incident_datetime, complaint_description, incident_location,
        complainant_first_name, complainant_middle_name, complainant_last_name, complainant_age, complainant_gender, complainant_phone, complainant_address,
        victim_first_name, victim_middle_name, victim_last_name, victim_age, victim_gender, victim_phone, victim_address,
        witness_first_name, witness_middle_name, witness_last_name, witness_age, witness_gender, witness_phone, witness_address,
        respondent_first_name, respondent_middle_name, respondent_last_name, respondent_age, respondent_gender, respondent_phone, respondent_address,
        complaint_statement, reported_by, is_affirmed, desk_officer_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Set values for fields not in the form
    $desk_officer_name = $google_name; // As seen in the HTML, or can be dynamic
    $reported_by = isset($_POST['reported_by']) ? 1 : 0;
    $is_affirmed = isset($_POST['is_affirmed']) ? 1 : 0;
    
    // To handle optional fields, set them to null if empty.
    $params = [
        $incident_datetime,
        $complaint_description,
        empty($_POST['incident_location']) ? null : $_POST['incident_location'],
        empty($_POST['complainant_first_name']) ? null : $_POST['complainant_first_name'],
        empty($_POST['complainant_middle_name']) ? null : $_POST['complainant_middle_name'],
        empty($_POST['complainant_last_name']) ? null : $_POST['complainant_last_name'],
        empty($_POST['complainant_age']) ? null : $_POST['complainant_age'],
        empty($_POST['complainant_gender']) ? null : $_POST['complainant_gender'],
        empty($_POST['complainant_phone']) ? null : $_POST['complainant_phone'],
        empty($_POST['complainant_address']) ? null : $_POST['complainant_address'],
        empty($_POST['victim_first_name']) ? null : $_POST['victim_first_name'],
        empty($_POST['victim_middle_name']) ? null : $_POST['victim_middle_name'],
        empty($_POST['victim_last_name']) ? null : $_POST['victim_last_name'],
        empty($_POST['victim_age']) ? null : $_POST['victim_age'],
        empty($_POST['victim_gender']) ? null : $_POST['victim_gender'],
        empty($_POST['victim_phone']) ? null : $_POST['victim_phone'],
        empty($_POST['victim_address']) ? null : $_POST['victim_address'],
        empty($_POST['witness_first_name']) ? null : $_POST['witness_first_name'],
        empty($_POST['witness_middle_name']) ? null : $_POST['witness_middle_name'],
        empty($_POST['witness_last_name']) ? null : $_POST['witness_last_name'],
        empty($_POST['witness_age']) ? null : $_POST['witness_age'],
        empty($_POST['witness_gender']) ? null : $_POST['witness_gender'],
        empty($_POST['witness_phone']) ? null : $_POST['witness_phone'],
        empty($_POST['witness_address']) ? null : $_POST['witness_address'],
        empty($_POST['respondent_first_name']) ? null : $_POST['respondent_first_name'],
        empty($_POST['respondent_middle_name']) ? null : $_POST['respondent_middle_name'],
        empty($_POST['respondent_last_name']) ? null : $_POST['respondent_last_name'],
        empty($_POST['respondent_age']) ? null : $_POST['respondent_age'],
        empty($_POST['respondent_gender']) ? null : $_POST['respondent_gender'],
        empty($_POST['respondent_phone']) ? null : $_POST['respondent_phone'],
        empty($_POST['respondent_address']) ? null : $_POST['respondent_address'],
        empty($_POST['complaint_statement']) ? null : $_POST['complaint_statement'],
        $reported_by,
        $is_affirmed,
        $desk_officer_name,
    ];

    // Corrected type string for bind_param. Must match the number of columns (35).
    $types = "ssssssissssssissssssissssssisssiiss";
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['status'] = 'error: ' . $stmt->error; 
    }

    $stmt->close();
    $conn->close();

    // Redirect to the same page to prevent form resubmission
    header("Location: blotter.php");
    exit();
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
        $dashboardClick = ($currentPage === 'dashboard.php') ? 'onclick="event.preventDefault()"' : '';
        $blotterClick   = ($currentPage === 'blotter.php')   ? 'onclick="event.preventDefault()"' : '';
        $reportsClick   = ($currentPage === 'reports.php')   ? 'onclick="event.preventDefault()"' : '';
        $accountsClick  = ($currentPage === 'accounts.php')  ? 'onclick="event.preventDefault()"' : '';
        $settingsClick  = ($currentPage === 'settings.php')  ? 'onclick="event.preventDefault()"' : '';

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
                <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "dashboard.php" ? $activeClasses : $inactiveClasses) . '" ' . $dashboardClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="blotter.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "blotter.php" ? $activeClasses : $inactiveClasses) . '" ' . $blotterClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="sidebar-text ml-3">Blotter</span>
                </a>
                <a href="reports.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "reports.php" ? $activeClasses : $inactiveClasses) . '" ' . $reportsClick . '>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="settings.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium ' . ($currentPage === "settings.php" ? $activeClasses : $inactiveClasses) . '" ' . $settingsClick . '>
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
    <title>Barangay Blotter Form</title>
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
        body { font-family: 'Poppins', sans-serif; }
        input:required, select:required, textarea:required { border-left: 3px solid #EF4444; }
        input:valid, select:valid, textarea:valid { border-left: 3px solid #22C55E; }

        /* Custom invalid state for validation */
        .field-invalid {
            border-color: #EF4444;
            border-left: 3px solid #EF4444;
        }
        .error-message {
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* --- START: Sidebar CSS Fix --- */
        #sidebar, #mainContent {
            transition: all 0.3s ease-in-out;
        }
        
        /* Styles for the collapsed state, triggered by the .sidebar-collapsed class on the <html> tag */
        .sidebar-collapsed #sidebar {
            width: 80px; /* New, smaller width for the sidebar */
        }

        .sidebar-collapsed #mainContent {
            margin-left: 80px; /* This value must match the collapsed sidebar's width */
        }

        /* Hide text elements smoothly when the sidebar is collapsed */
        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-header-text {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        
        /* Center the navigation icons when the sidebar is collapsed */
        .sidebar-collapsed .nav-link,
        .sidebar-collapsed .logout-link {
            justify-content: center;
        }
        
        /* Remove the left margin from icons when text is hidden */
        .sidebar-collapsed .nav-link .ml-3,
        .sidebar-collapsed .logout-link .ml-3 {
            margin-left: 0;
        }
        /* --- END: Sidebar CSS Fix --- */
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
            <!-- Top Header -->
            <header class="bg-primary text-white p-4 flex justify-between items-center shadow-md z-10">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="mr-4 text-white hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-3xl font-bold">Blotter Form</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <!-- Form Content -->
            <main class="p-6 flex-1 overflow-y-auto">
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <form method="POST" action="blotter.php" id="blotterForm" novalidate>
                        
                        <!-- Page 1: Complaint and Complainant Info -->
                        <div id="Page_1">
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Pangunahing Impormasyon</h2>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="md:col-span-1">
                                        <label for="incident_date" class="block text-sm font-medium text-gray-700 mb-1">Petsa at Oras ng Insidente</label>
                                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                            <input type="date" name="incident_date" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 col-span-2" required>
                                            <select name="incident_hour" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                                <option value="">HH</option>
                                                <?php for($i = 1; $i <= 12; $i++): ?><option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                            </select>
                                            <select name="incident_minute" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                                <option value="">MM</option>
                                                <?php for($i = 0; $i <= 59; $i++): ?><option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                            </select>
                                            <select name="incident_period" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                                <option value="AM">AM</option>
                                                <option value="PM">PM</option>
                                            </select>
                                        </div>
                                        <div id="incident_datetime_error" class="error-message hidden">This field is required.</div>
                                    </div>
                                    <div>
                                        <label for="complaint_description" class="block text-sm font-medium text-gray-700 mb-1">Uri ng Reklamo</label>
                                        <select name="complaint_description" id="complaint_description" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required onchange="checkOther(this)">
                                            <option value="" disabled selected>Pumili ng Uri ng Reklamo</option>
                                            <option value="Noise Complaints">Noise Complaints</option>
                                            <option value="Neighbor Disputes">Neighbor Disputes</option>
                                            <option value="Mischief/Vandalism">Mischief/Vandalism</option>
                                            <option value="Pet-Related Incidents">Pet-Related Incidents</option>
                                            <option value="Parking Issues">Parking Issues</option>
                                            <option value="Minor Theft">Minor Theft</option>
                                            <option value="Assault (Minor)">Assault (Minor)</option>
                                            <option value="Domestic Disputes">Domestic Disputes</option>
                                            <option value="Others">Others</option>
                                        </select>
                                        <input type="text" name="other_complaint" id="other_complaint" placeholder="Please specify" class="w-full p-2 mt-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" style="display:none;">
                                        <div id="complaint_description_error" class="error-message hidden">This field is required.</div>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label for="incident_location" class="block text-sm font-medium text-gray-700 mb-1">Lugar ng Pinangyarihan</label>
                                    <textarea name="incident_location" id="incident_location" placeholder="Ilagay ang buong address ng insidente" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required></textarea>
                                    <div id="incident_location_error" class="error-message hidden">This field is required.</div>
                                </div>
                            </section>

                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Impormasyon ng Nagrereklamo</h2>
                                <div class="grid md:grid-cols-3 gap-6 mb-4">
                                    <div>
                                        <label for="complainant_first_name" class="block text-sm font-medium text-gray-700 mb-1">Pangalan</label>
                                        <input type="text" name="complainant_first_name" id="complainant_first_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="complainant_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Gitnang Pangalan</label>
                                        <input type="text" name="complainant_middle_name" id="complainant_middle_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="complainant_last_name" class="block text-sm font-medium text-gray-700 mb-1">Apelyido</label>
                                        <input type="text" name="complainant_last_name" id="complainant_last_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="complainant_age" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                        <select name="complainant_age" id="complainant_age" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                            <option value="">Pumili ng Edad</option>
                                            <?php for($i = 18; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="complainant_gender" class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                        <select name="complainant_gender" id="complainant_gender" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                            <option value="">Pumili ng Kasarian</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="complainant_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                        <input type="tel" name="complainant_phone" id="complainant_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" maxlength="13" pattern="0\d{3}-\d{3}-\d{4}" title="Format: 0xxx-xxx-xxxx" oninput="formatPhoneNumber(this)">
                                        <div class="error-message"></div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="complainant_address" class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                    <textarea name="complainant_address" id="complainant_address" placeholder="Ilagay ang buong tirahan" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                                </div>
                            </section>
                            
                            <div class="flex justify-end pt-4 border-t">
                                <button type="button" id="nextBtn" onclick="nextStep()" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-opacity duration-300">Next Step</button>
                            </div>
                        </div>

                        <!-- Page 2 -->
                        <div id="Page_2" style="display:none;">
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Impormasyon ng Biktima</h2>
                                <div class="grid md:grid-cols-3 gap-6 mb-4">
                                    <div>
                                        <label for="victim_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                        <input type="text" name="victim_first_name" id="victim_first_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                                    </div>
                                    <div>
                                        <label for="victim_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                        <input type="text" name="victim_middle_name" id="victim_middle_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="victim_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                        <input type="text" name="victim_last_name" id="victim_last_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="victim_age" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                        <select name="victim_age" id="victim_age" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                                            <option value="">Pumili ng Edad</option>
                                            <?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="victim_gender" class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                        <select name="victim_gender" id="victim_gender" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                                            <option value="">Pumili ng Kasarian</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="victim_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                        <input type="tel" name="victim_phone" id="victim_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required maxlength="13" pattern="0\d{3}-\d{3}-\d{4}" title="Format: 0xxx-xxx-xxxx" oninput="formatPhoneNumber(this)">
                                        <div class="error-message"></div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="victim_address" class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                    <textarea name="victim_address" id="victim_address" placeholder="Ilagay ang buong tirahan" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required></textarea>
                                </div>
                            </section>

                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Impormasyon ng Saksi (kung meron)</h2>
                                <div class="grid md:grid-cols-3 gap-6 mb-4">
                                    <div><label for="witness_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label><input type="text" name="witness_first_name" id="witness_first_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                    <div><label for="witness_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label><input type="text" name="witness_middle_name" id="witness_middle_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                    <div><label for="witness_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label><input type="text" name="witness_last_name" id="witness_last_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="witness_age" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                        <select name="witness_age" id="witness_age" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"><option value="">Pumili ng Edad</option><?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?></select>
                                    </div>
                                    <div>
                                        <label for="witness_gender" class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                        <select name="witness_gender" id="witness_gender" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"><option value="">Pumili ng Kasarian</option><option value="Male">Male</option><option value="Female">Female</option></select>
                                    </div>
                                    <div>
                                        <label for="witness_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                        <input type="tel" name="witness_phone" id="witness_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" maxlength="13" pattern="0\d{3}-\d{3}-\d{4}" title="Format: 0xxx-xxx-xxxx" oninput="formatPhoneNumber(this)">
                                        <div class="error-message"></div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="witness_address" class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                    <textarea name="witness_address" id="witness_address" placeholder="Ilagay ang buong tirahan" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                                </div>
                            </section>

                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Impormasyon ng Inireklamo (kung meron)</h2>
                                <div class="grid md:grid-cols-3 gap-6 mb-4">
                                    <div><label for="respondent_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label><input type="text" name="respondent_first_name" id="respondent_first_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                    <div><label for="respondent_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label><input type="text" name="respondent_middle_name" id="respondent_middle_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                    <div><label for="respondent_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label><input type="text" name="respondent_last_name" id="respondent_last_name" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="respondent_age" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                        <select name="respondent_age" id="respondent_age" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"><option value="">Pumili ng Edad</option><?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?></select>
                                    </div>
                                    <div>
                                        <label for="respondent_gender" class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                        <select name="respondent_gender" id="respondent_gender" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"><option value="">Pumili ng Kasarian</option><option value="Male">Male</option><option value="Female">Female</option></select>
                                    </div>
                                    <div>
                                        <label for="respondent_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                        <input type="tel" name="respondent_phone" id="respondent_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" maxlength="13" pattern="0\d{3}-\d{3}-\d{4}" title="Format: 0xxx-xxx-xxxx" oninput="formatPhoneNumber(this)">
                                        <div class="error-message"></div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="respondent_address" class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                    <textarea name="respondent_address" id="respondent_address" placeholder="Ilagay ang buong tirahan" rows="3" class="w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                                </div>
                            </section>

                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-3 mb-6">Salaysay</h2>
                                <div>
                                    <label for="complaint_statement" class="block text-sm font-medium text-gray-700 mb-1">Maikling Salaysay ng Pangyayari</label>
                                    <textarea name="complaint_statement" id="complaint_statement" placeholder="Isalaysay ang buong detalye ng insidente." rows="6" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" required></textarea>
                                </div>
                            </section>

                            <div class="bg-gray-50 p-4 rounded-lg flex items-start space-x-3 mt-8">
                                <input type="checkbox" name="reported_by" id="reported_by" value="1" class="h-5 w-5 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500" required>
                                <label for="reported_by" class="text-sm text-gray-700">Inuulat sa pamamagitan ng: Personal</label>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg flex items-start space-x-3 mt-4">
                                <input type="checkbox" name="is_affirmed" id="is_affirmed" value="1" class="h-5 w-5 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500" required>
                                <label for="is_affirmed" class="text-sm text-gray-700">Pinapatunayan ko na ang mga detalye na nakalagay sa reklamo na ito ay totoo at tama sa abot ng aking kaalaman.</label>
                            </div>

                            <div class="flex justify-between items-center pt-6 border-t mt-6">
                                <button type="button" onclick="prevStep()" class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">Back</button>
                                <button type="button" id="openModalBtn" class="px-8 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Submit Report</button>
                            </div>
                        </div>

                    </form>
                </div>
            </main>
        </div>
    </div>

   <!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden flex items-center justify-center z-[100] p-4 transition-opacity duration-300 opacity-0">
    <div class="bg-gray-50 p-8 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Review Your Complaint</h2>
            <button type="button" id="closeModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Dynamic Content Area -->
        <div id="reviewContent" class="space-y-8">
            <!-- Content will be dynamically generated here -->
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Confirmation</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span>Reported in Person</span>
                </div>
                <div class="flex items-start">
                     <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span>I affirm that the details provided are true and correct to the best of my knowledge.</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-8">
            <button type="button" id="editBtn" class="px-6 py-3 bg-white text-gray-800 font-semibold rounded-lg border border-gray-300 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                Edit
            </button>
            <button type="submit" form="blotterForm" name="submit_complaint" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Confirm & Submit
            </button>
        </div>
    </div>
</div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Submitted Successfully</h3>
                <div class="mt-2 px-7 py-3"><p class="text-sm text-gray-500">The blotter report has been saved.</p></div>
                <div class="items-center px-4 py-3">
                    <button id="ok-btn" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">OK</button>
                </div>
            </div>
        </div>
    </div>

<script>
    // --- START: Phone Number Formatting ---
    function formatPhoneNumber(input) {
        // 1. Strip all non-numeric characters from the input value.
        let numbers = input.value.replace(/\D/g, '');
        // 2. Truncate to a maximum of 11 digits (e.g., 09171234567).
        numbers = numbers.substring(0, 11);

        // 3. Apply the "0xxx-xxx-xxxx" format.
        let formatted = '';
        if (numbers.length > 7) {
            formatted = `${numbers.substring(0, 4)}-${numbers.substring(4, 7)}-${numbers.substring(7)}`;
        } else if (numbers.length > 4) {
            formatted = `${numbers.substring(0, 4)}-${numbers.substring(4)}`;
        } else {
            formatted = numbers;
        }
        
        // 4. Update the input field with the formatted value.
        input.value = formatted;

        // 5. Custom validation message handling
        const errorDiv = input.nextElementSibling;
        if (input.value.length > 0 && !input.checkValidity()) {
            errorDiv.textContent = 'Invalid number format, please use 0xxx-xxx-xxxx.';
            input.classList.add('field-invalid');
        } else {
            errorDiv.textContent = '';
             input.classList.remove('field-invalid');
        }
    }
    // --- END: Phone Number Formatting ---

    document.addEventListener('DOMContentLoaded', function() {
        // --- Sidebar Logic ---
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const root = document.documentElement;

        const handleSidebar = () => {
            if (root.classList.contains('sidebar-collapsed')) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                localStorage.setItem('sidebarState', 'expanded');
            }
        };

        sidebarToggle.addEventListener('click', () => {
            root.classList.toggle('sidebar-collapsed');
            handleSidebar();
        });

        if (localStorage.getItem('sidebarState') === 'collapsed') {
            root.classList.add('sidebar-collapsed');
        }
        handleSidebar();

        // --- START: Form Validation & Navigation Logic ---
        const page1 = document.getElementById('Page_1');
        const nextButton = document.getElementById('nextBtn');
        const page1RequiredFields = Array.from(page1.querySelectorAll('[required]'));
        
        function checkPage1Validity() {
            const isPage1Valid = page1RequiredFields.every(field => field.checkValidity());
            
            if (isPage1Valid) {
                nextButton.disabled = false;
                nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                nextButton.disabled = true;
                nextButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        page1RequiredFields.forEach(field => {
            field.addEventListener('input', checkPage1Validity);
            field.addEventListener('change', checkPage1Validity);
        });

        checkPage1Validity();

        window.nextStep = function() {
            let isFormValid = true;
            
            const errorMap = {
                'incident_date': 'incident_datetime_error',
                'incident_hour': 'incident_datetime_error',
                'incident_minute': 'incident_datetime_error',
                'incident_period': 'incident_datetime_error',
                'complaint_description': 'complaint_description_error',
                'incident_location': 'incident_location_error'
            };

            // Hide all error messages first
            Object.values(errorMap).forEach(id => {
                const errorEl = document.getElementById(id);
                if (errorEl) errorEl.classList.add('hidden');
            });

            page1RequiredFields.forEach(input => {
                input.classList.remove('field-invalid');
                if (!input.checkValidity()) {
                    isFormValid = false;
                    input.classList.add('field-invalid');
                    const errorId = errorMap[input.name];
                    if (errorId) {
                        document.getElementById(errorId).classList.remove('hidden');
                    }
                }
            });

            if (isFormValid) {
                document.getElementById('Page_1').style.display = 'none';
                document.getElementById('Page_2').style.display = 'block';
            }
        };
        // --- END: Form Validation & Navigation Logic ---

        window.prevStep = function() {
            document.getElementById('Page_2').style.display = 'none';
            document.getElementById('Page_1').style.display = 'block';
        };

        window.checkOther = function(selectElement) {
            const otherTextBox = document.getElementById('other_complaint');
            if (selectElement.value === 'Others') {
                otherTextBox.style.display = 'block';
                otherTextBox.setAttribute('required', 'required');
            } else {
                otherTextBox.style.display = 'none';
                otherTextBox.removeAttribute('required');
                otherTextBox.value = '';
            }
            checkPage1Validity(); // Re-validate when this changes
        };


        // --- Review Modal Logic ---
        const openModalBtn = document.getElementById("openModalBtn");
        const reviewModal = document.getElementById("reviewModal");
        const reviewContent = document.getElementById("reviewContent");
        const editBtn = document.getElementById("editBtn");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const form = document.getElementById("blotterForm");
        const modalInner = reviewModal.querySelector('div:first-child');

        function openReviewModal() {
            reviewModal.classList.remove("hidden");
            setTimeout(() => {
                reviewModal.classList.remove("opacity-0");
                modalInner.classList.remove("scale-95");
            }, 10);
        }

        function closeReviewModal() {
            reviewModal.classList.add("opacity-0");
            modalInner.classList.add("scale-95");
            setTimeout(() => {
                reviewModal.classList.add("hidden");
            }, 300); // Match transition duration
        }

        openModalBtn.addEventListener("click", () => {
            if (form.checkValidity()) {
                const formData = new FormData(form);
                let html = "";
                const keyMapping = {
                    'incident_date': 'Date of Incident', 'incident_hour': 'Hour', 'incident_minute': 'Minute', 'incident_period': 'AM/PM',
                    'complaint_description': 'Type of Complaint', 'other_complaint': 'Other Complaint Details', 'incident_location': 'Location of Incident',
                    'complainant_first_name': 'Complainant First Name', 'complainant_middle_name': 'Complainant Middle Name', 'complainant_last_name': 'Complainant Last Name',
                    'complainant_age': 'Complainant Age', 'complainant_gender': 'Complainant Gender', 'complainant_phone': 'Complainant Phone', 'complainant_address': 'Complainant Address',
                    'victim_first_name': 'Victim First Name', 'victim_middle_name': 'Victim Middle Name', 'victim_last_name': 'Victim Last Name',
                    'victim_age': 'Victim Age', 'victim_gender': 'Victim Gender', 'victim_phone': 'Victim Phone', 'victim_address': 'Victim Address',
                    'witness_first_name': 'Witness First Name', 'witness_middle_name': 'Witness Middle Name', 'witness_last_name': 'Witness Last Name',
                    'witness_age': 'Witness Age', 'witness_gender': 'Witness Gender', 'witness_phone': 'Witness Phone', 'witness_address': 'Witness Address',
                    'respondent_first_name': 'Respondent First Name', 'respondent_middle_name': 'Respondent Middle Name', 'respondent_last_name': 'Respondent Last Name',
                    'respondent_age': 'Respondent Age', 'respondent_gender': 'Respondent Gender', 'respondent_phone': 'Respondent Phone', 'respondent_address': 'Respondent Address',
                    'complaint_statement': 'Complaint Statement', 'reported_by': 'Reported in Person', 'is_affirmed': 'Information Affirmed'
                };
                
                for (const [key, value] of formData.entries()) {
                    if (value.trim() !== "") {
                        let displayValue = value;
                        if (key === 'reported_by' || key === 'is_affirmed') {
                            displayValue = 'Yes';
                        }
                        const label = keyMapping[key] || key.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());
                        html += `<div class="grid grid-cols-3 gap-4"><strong class="col-span-1 text-gray-900">${label}:</strong> <span class="col-span-2">${displayValue}</span></div>`;
                    }
                }

                reviewContent.innerHTML = html || "<p class='text-gray-500 italic'>No data entered.</p>";
                openReviewModal();
            } else {
                form.reportValidity();
            }
        });

        editBtn.addEventListener("click", closeReviewModal);
        closeModalBtn.addEventListener("click", closeReviewModal);
        reviewModal.addEventListener("click", (e) => {
            if (e.target === reviewModal) {
                closeReviewModal();
            }
        });

        <?php
        if (isset($_SESSION['status']) && $_SESSION['status'] == 'success') {
            echo "
            const successModal = document.getElementById('successModal');
            const okBtn = document.getElementById('ok-btn');
            
            successModal.style.display = 'block';

            okBtn.onclick = function() {
                successModal.style.display = 'none';
                form.reset();
                document.getElementById('Page_2').style.display = 'none';
                document.getElementById('Page_1').style.display = 'block';
            }
            window.onclick = function(event) {
                if (event.target == successModal) {
                    successModal.style.display = 'none';
                }
            }
            ";
            unset($_SESSION['status']);
        }
        ?>
    });
</script>

</body>
</html>