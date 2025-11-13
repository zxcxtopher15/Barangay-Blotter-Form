<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['google_loggedin']) && !isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit;
}

// Retrieve session variables
$google_loggedin = $_SESSION['google_loggedin'];
$google_email = $_SESSION['google_email'];
$google_name = $_SESSION['google_name'];
$google_picture = $_SESSION['google_picture'];

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the form has been submitted
if (isset($_POST['submit_complaint'])) {
    $db_server = "localhost";
    $db_user = "u416486854_p1";
    $db_pass = "2&rnLACGCldK";
    $db_name = "u416486854_p1";
    $conn = null;

    try {
        $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    } catch (mysqli_sql_exception) {
        die("Database connection failed.");
    }

    // Data Processing
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

    $complaint_description = $_POST['complaint_description'];
    if ($complaint_description === 'Others') {
        $complaint_description = $_POST['other_complaint'];
    }

    $stmt = $conn->prepare("INSERT INTO complaints (
        incident_datetime, complaint_description, incident_location,
        complainant_first_name, complainant_middle_name, complainant_last_name, complainant_age, complainant_gender, complainant_phone, complainant_address,
        victim_first_name, victim_middle_name, victim_last_name, victim_age, victim_gender, victim_phone, victim_address,
        witness_first_name, witness_middle_name, witness_last_name, witness_age, witness_gender, witness_phone, witness_address,
        respondent_first_name, respondent_middle_name, respondent_last_name, respondent_age, respondent_gender, respondent_phone, respondent_address,
        complaint_statement, reported_by, is_affirmed, desk_officer_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $desk_officer_name = $google_name;
    $reported_by = isset($_POST['reported_by']) ? 1 : 0;
    $is_affirmed = isset($_POST['is_affirmed']) ? 1 : 0;

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

    $types = "ssssssissssssissssssissssssisssiiss";
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['status'] = 'error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: blotter.php");
    exit();
}

function sidepanel($google_picture, $google_name) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $activeClasses = 'bg-blue-500 text-white shadow';
    $inactiveClasses = 'text-gray-600 hover:bg-gray-100';

    $dashboardClick = ($currentPage === 'dashboard.php') ? 'onclick="event.preventDefault()"' : '';
    $blotterClick   = ($currentPage === 'blotter.php')   ? 'onclick="event.preventDefault()"' : '';
    $reportsClick   = ($currentPage === 'reports.php')   ? 'onclick="event.preventDefault()"' : '';
    $settingsClick  = ($currentPage === 'settings.php')  ? 'onclick="event.preventDefault()"' : '';

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
    ';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Form - Barangay San Miguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#1e3a5f',
                        'secondary': '#1D4ED8',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <link rel="stylesheet" href="css/main.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }

        .tab-button {
            padding: 12px 24px;
            background: #e5e7eb;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }

        .tab-button.completed {
            background: #22c55e;
            color: white;
        }

        .tab-button.active {
            background: #ef4444;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        #sidebar, #mainContent {
            transition: all 0.3s ease-in-out;
        }

        .sidebar-collapsed #sidebar {
            width: 80px;
        }

        .sidebar-collapsed #mainContent {
            margin-left: 80px;
        }

        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-header-text {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .sidebar-collapsed .nav-link,
        .sidebar-collapsed .logout-link {
            justify-content: center;
        }

        /* Map styling */
        #map {
            height: 350px;
            width: 100%;
            border-radius: 0.375rem;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php
    echo '<script>';
    echo 'if (localStorage.getItem(\'sidebarState\') === \'collapsed\') {';
    echo '    document.documentElement.classList.add(\'js-sidebar-initial-collapsed\');';
    echo '}';
    echo '</script>';
    ?>

    <div class="flex h-screen overflow-hidden">
        <?php sidepanel($google_picture, $google_name); ?>

        <div id="mainContent" class="flex-1 ml-64 flex flex-col">
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
                    <span class="text-white">PASIG</span>
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain bg-white rounded-full p-1">
                </div>
            </header>

            <main class="p-6 flex-1 overflow-y-auto">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Tab Navigation -->
                    <div class="flex overflow-x-auto bg-gray-200">
                        <button class="tab-button active" data-tab="tab1">Pinangayarihan</button>
                        <button class="tab-button" data-tab="tab2">Nagrereklamo</button>
                        <button class="tab-button" data-tab="tab3">Biktima</button>
                        <button class="tab-button" data-tab="tab4">Saksi</button>
                        <button class="tab-button" data-tab="tab5">Inireklamo</button>
                        <button class="tab-button" data-tab="tab6">Salaysay</button>
                    </div>

                    <!-- Form Content -->
                    <form method="POST" action="blotter.php" id="blotterForm" class="p-8">

                        <!-- Tab 1: Pinangayarihan (Initial Questions) -->
                        <div id="tab1" class="tab-content active">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Pinangayarihan</h2>

                            <div class="grid md:grid-cols-4 gap-4 mb-6">
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petsa at Oras ng Insidente</label>
                                    <input type="date" name="incident_date" class="w-full p-2 border border-gray-300 rounded-md" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">HH</label>
                                    <select name="incident_hour" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="">HH</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">MM</label>
                                    <select name="incident_minute" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="">MM</option>
                                        <?php for($i = 0; $i <= 59; $i++): ?><option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">AM/PM</label>
                                    <select name="incident_period" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lugar ng Pinangyarihan</label>
                                <p class="text-xs text-gray-500 mb-2">I-click ang mapa upang pumili ng lokasyon</p>
                                <div id="map" class="mb-3"></div>
                                <input type="text" name="incident_location_display" id="incident_location_display" class="w-full p-2 border border-gray-300 rounded-md mb-2 bg-gray-50" placeholder="Awtomatikong papunan mula sa mapa..." readonly>
                                <input type="text" name="incident_location" id="incident_location" class="w-full p-2 border border-gray-300 rounded-md" placeholder="O mag-type ng lokasyon dito" required>
                                <input type="hidden" name="incident_latitude" id="incident_latitude">
                                <input type="hidden" name="incident_longitude" id="incident_longitude">
                            </div>

                            <div class="flex justify-end">
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 2: Impormasyon ng Nagrereklamo -->
                        <div id="tab2" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Impormasyon ng Nagrereklamo</h2>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unang Pangalan</label>
                                    <input type="text" name="complainant_first_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gitnang Pangalan</label>
                                    <input type="text" name="complainant_middle_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apelyido</label>
                                    <input type="text" name="complainant_last_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                    <select name="complainant_age" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Edad</option>
                                        <?php for($i = 18; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                    <select name="complainant_gender" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Kasarian</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                    <input type="tel" name="complainant_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Region *</label>
                                        <input type="text" class="w-full p-2 border border-gray-300 rounded-md" value="National Capital Region" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">City / Municipality *</label>
                                        <input type="text" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Select City/Municipality">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-xs text-gray-600 mb-1">Barangay *</label>
                                    <input type="text" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Select Barangay">
                                </div>
                                <div class="mt-2">
                                    <label class="block text-xs text-gray-600 mb-1">Street Name, Building, House No. *</label>
                                    <input type="text" name="complainant_address" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Enter detailed street address">
                                </div>
                                <button type="button" class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm">Bumalik</button>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" class="prev-btn px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Bumalik</button>
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 3: Impormasyon ng Biktima -->
                        <div id="tab3" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Impormasyon ng Biktima</h2>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unang Pangalan</label>
                                    <input type="text" name="victim_first_name" class="w-full p-2 border border-gray-300 rounded-md" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                    <input type="text" name="victim_middle_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apelyido</label>
                                    <input type="text" name="victim_last_name" class="w-full p-2 border border-gray-300 rounded-md" required>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                    <select name="victim_age" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="">Pumili ng Edad</option>
                                        <?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                    <select name="victim_gender" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="">Pumili ng Kasarian</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                    <input type="tel" name="victim_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                <textarea name="victim_address" rows="3" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" class="prev-btn px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Bumalik</button>
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 4: Impormasyon ng Saksi -->
                        <div id="tab4" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Impormasyon ng Saksi (kung meron)</h2>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unang Pangalan</label>
                                    <input type="text" name="witness_first_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                    <input type="text" name="witness_middle_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apelyido</label>
                                    <input type="text" name="witness_last_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                    <select name="witness_age" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Edad</option>
                                        <?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                    <select name="witness_gender" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Kasarian</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                    <input type="tel" name="witness_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                <textarea name="witness_address" rows="3" class="w-full p-2 border border-gray-300 rounded-md"></textarea>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" class="prev-btn px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Bumalik</button>
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 5: Impormasyon ng Inireklamo -->
                        <div id="tab5" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Impormasyon ng Inireklamo (kung meron)</h2>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unang Pangalan</label>
                                    <input type="text" name="respondent_first_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                    <input type="text" name="respondent_middle_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apelyido</label>
                                    <input type="text" name="respondent_last_name" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                                    <select name="respondent_age" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Edad</option>
                                        <?php for($i = 1; $i <= 100; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasarian</label>
                                    <select name="respondent_gender" class="w-full p-2 border border-gray-300 rounded-md">
                                        <option value="">Pumili ng Kasarian</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepono</label>
                                    <input type="tel" name="respondent_phone" placeholder="0xxx-xxx-xxxx" class="w-full p-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tirahan</label>
                                <textarea name="respondent_address" rows="3" class="w-full p-2 border border-gray-300 rounded-md"></textarea>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" class="prev-btn px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Bumalik</button>
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 6: Salaysay -->
                        <div id="tab6" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Salaysay</h2>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Maikling Salaysay ng Pangyayari</label>
                                <textarea name="complaint_statement" rows="6" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Paunang mga Katanungan</label>
                                <p class="text-sm text-gray-600 mb-2">Pumili ng mga angkop sa pagpapahayag bago magsalita</p>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="complaint_description" value="Noise Complaints" class="mr-2">
                                        <label class="text-sm">Ang nagrereklamo ay ang biktima rin</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" class="mr-2">
                                        <label class="text-sm">Walang Saksi</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" class="mr-2">
                                        <label class="text-sm">Walang Inireklamo</label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="reported_by" value="1" class="mt-1" required>
                                    <label class="text-sm">Inuulat sa pamamagitan ng: Personal</label>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="is_affirmed" value="1" class="mt-1" required>
                                    <label class="text-sm">Pinapatunayan ko na ang mga detalye na nakalagay sa reklamo na ito ay totoo at tama sa abot ng aking kaalaman.</label>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" class="prev-btn px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Bumalik</button>
                                <button type="submit" name="submit_complaint" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Magsalita</button>
                            </div>
                        </div>

                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Submitted Successfully</h3>
                <div class="mt-2 px-7 py-3"><p class="text-sm text-gray-500">The blotter report has been saved.</p></div>
                <div class="items-center px-4 py-3">
                    <button id="ok-btn" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-TIBPTWINmPouBtVmBuCLsYGgPZLyIvF4RzDzIHfPnfGGnRdx2BHEE2l3TqkUZBsO" crossorigin=""></script>

    <script src="js/sidebar.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map with Leaflet + OpenStreetMap
        let map, marker;

        // Initialize map centered on Barangay San Miguel, Pasig City
        map = L.map('map').setView([14.5678, 121.0854], 16);

        // Add OpenStreetMap tiles (free!)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Add click event to place pin
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Remove existing marker if any
            if (marker) {
                map.removeLayer(marker);
            }

            // Add new marker
            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            // Save coordinates to hidden fields
            document.getElementById('incident_latitude').value = lat;
            document.getElementById('incident_longitude').value = lng;

            // Reverse geocode using Nominatim (free OpenStreetMap service)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('incident_location_display').value = data.display_name;
                        document.getElementById('incident_location').value = data.display_name;
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    document.getElementById('incident_location_display').value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                });

            // Make marker draggable and update location on drag
            marker.on('dragend', function(e) {
                const newLat = e.target.getLatLng().lat;
                const newLng = e.target.getLatLng().lng;

                document.getElementById('incident_latitude').value = newLat;
                document.getElementById('incident_longitude').value = newLng;

                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${newLat}&lon=${newLng}&zoom=18&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('incident_location_display').value = data.display_name;
                            document.getElementById('incident_location').value = data.display_name;
                        }
                    })
                    .catch(error => console.error('Geocoding error:', error));
            });
        });

        // Tab Navigation
        const tabs = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        let currentTab = 0;

        function showTab(index) {
            tabContents.forEach((content, i) => {
                content.classList.remove('active');
                if (i === index) {
                    content.classList.add('active');
                }
            });

            tabs.forEach((tab, i) => {
                tab.classList.remove('active', 'completed');
                if (i < index) {
                    tab.classList.add('completed');
                } else if (i === index) {
                    tab.classList.add('active');
                }
            });

            currentTab = index;

            // Refresh map when switching to tab 1 (Pinangayarihan)
            if (index === 0 && map) {
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }
        }

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => showTab(index));
        });

        // Next/Previous buttons
        document.querySelectorAll('.next-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentTab < tabs.length - 1) {
                    showTab(currentTab + 1);
                }
            });
        });

        document.querySelectorAll('.prev-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentTab > 0) {
                    showTab(currentTab - 1);
                }
            });
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const root = document.documentElement;

        sidebarToggle.addEventListener('click', () => {
            root.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', root.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });

        if (localStorage.getItem('sidebarState') === 'collapsed') {
            root.classList.add('sidebar-collapsed');
        }

        // Success Modal
        <?php
        if (isset($_SESSION['status']) && $_SESSION['status'] == 'success') {
            echo "
            const successModal = document.getElementById('successModal');
            const okBtn = document.getElementById('ok-btn');

            successModal.style.display = 'block';

            okBtn.onclick = function() {
                successModal.style.display = 'none';
                document.getElementById('blotterForm').reset();
                showTab(0);
            }
            ";
            unset($_SESSION['status']);
        }
        ?>
    });
    </script>
</body>
</html>
