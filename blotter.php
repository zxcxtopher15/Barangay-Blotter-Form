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

    // Complaint description is now AI-detected from the statement
    $complaint_description = $_POST['complaint_description'];

    $stmt = $conn->prepare("INSERT INTO complaints (
        incident_datetime, complaint_description, incident_location, incident_latitude, incident_longitude,
        complainant_first_name, complainant_middle_name, complainant_last_name, complainant_dob, complainant_age, complainant_gender, complainant_phone, complainant_address,
        victim_first_name, victim_middle_name, victim_last_name, victim_dob, victim_age, victim_gender, victim_phone, victim_address,
        witness_first_name, witness_middle_name, witness_last_name, witness_dob, witness_age, witness_gender, witness_phone, witness_address,
        respondent_first_name, respondent_middle_name, respondent_last_name, respondent_dob, respondent_age, respondent_gender, respondent_phone, respondent_address,
        complaint_statement, reported_by, is_affirmed, desk_officer_name
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $desk_officer_name = $google_name;
    $reported_by = isset($_POST['reported_by']) ? 1 : 0;
    $is_affirmed = isset($_POST['is_affirmed']) ? 1 : 0;

    $params = [
        $incident_datetime,
        $complaint_description,
        empty($_POST['incident_location']) ? null : $_POST['incident_location'],
        empty($_POST['incident_latitude']) ? null : $_POST['incident_latitude'],
        empty($_POST['incident_longitude']) ? null : $_POST['incident_longitude'],
        empty($_POST['complainant_first_name']) ? null : $_POST['complainant_first_name'],
        empty($_POST['complainant_middle_name']) ? null : $_POST['complainant_middle_name'],
        empty($_POST['complainant_last_name']) ? null : $_POST['complainant_last_name'],
        empty($_POST['complainant_dob']) ? null : $_POST['complainant_dob'],
        empty($_POST['complainant_age']) ? null : $_POST['complainant_age'],
        empty($_POST['complainant_gender']) ? null : $_POST['complainant_gender'],
        empty($_POST['complainant_phone']) ? null : $_POST['complainant_phone'],
        empty($_POST['complainant_address']) ? null : $_POST['complainant_address'],
        empty($_POST['victim_first_name']) ? null : $_POST['victim_first_name'],
        empty($_POST['victim_middle_name']) ? null : $_POST['victim_middle_name'],
        empty($_POST['victim_last_name']) ? null : $_POST['victim_last_name'],
        empty($_POST['victim_dob']) ? null : $_POST['victim_dob'],
        empty($_POST['victim_age']) ? null : $_POST['victim_age'],
        empty($_POST['victim_gender']) ? null : $_POST['victim_gender'],
        empty($_POST['victim_phone']) ? null : $_POST['victim_phone'],
        empty($_POST['victim_address']) ? null : $_POST['victim_address'],
        empty($_POST['witness_first_name']) ? null : $_POST['witness_first_name'],
        empty($_POST['witness_middle_name']) ? null : $_POST['witness_middle_name'],
        empty($_POST['witness_last_name']) ? null : $_POST['witness_last_name'],
        empty($_POST['witness_dob']) ? null : $_POST['witness_dob'],
        empty($_POST['witness_age']) ? null : $_POST['witness_age'],
        empty($_POST['witness_gender']) ? null : $_POST['witness_gender'],
        empty($_POST['witness_phone']) ? null : $_POST['witness_phone'],
        empty($_POST['witness_address']) ? null : $_POST['witness_address'],
        empty($_POST['respondent_first_name']) ? null : $_POST['respondent_first_name'],
        empty($_POST['respondent_middle_name']) ? null : $_POST['respondent_middle_name'],
        empty($_POST['respondent_last_name']) ? null : $_POST['respondent_last_name'],
        empty($_POST['respondent_dob']) ? null : $_POST['respondent_dob'],
        empty($_POST['respondent_age']) ? null : $_POST['respondent_age'],
        empty($_POST['respondent_gender']) ? null : $_POST['respondent_gender'],
        empty($_POST['respondent_phone']) ? null : $_POST['respondent_phone'],
        empty($_POST['respondent_address']) ? null : $_POST['respondent_address'],
        empty($_POST['complaint_statement']) ? null : $_POST['complaint_statement'],
        $reported_by,
        $is_affirmed,
        $desk_officer_name,
    ];

    // Type definitions: s=string, d=double, i=integer
    // incident: datetime, desc, location, lat, lon = sssdd (5)
    // complainant: fname, mname, lname, dob, age, gender, phone, address = ssssiss (8)
    // victim: fname, mname, lname, dob, age, gender, phone, address = ssssiss (8)
    // witness: fname, mname, lname, dob, age, gender, phone, address = ssssiss (8)
    // respondent: fname, mname, lname, dob, age, gender, phone, address = ssssiss (8)
    // final: statement, reported_by, is_affirmed, officer = siis (4)
    // Total: 5 + 8 + 8 + 8 + 8 + 4 = 41
    $types = "sssddssssisssssssisssssssisssssssissssiis";
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

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

        #sidebar,
        #mainContent {
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
                                        <?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">MM</label>
                                    <select name="incident_minute" class="w-full p-2 border border-gray-300 rounded-md" required>
                                        <option value="">MM</option>
                                        <?php for ($i = 0; $i <= 59; $i++): ?><option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Magtype ng Lokasyon o Mag-pin sa Mapa</label>
                                <div style="position: relative;">
                                    <input type="text" name="incident_location" id="incident_location" class="w-full p-2 border border-gray-300 rounded-md mb-3" placeholder="Pumili ng kalsada/lugar sa San Miguel o mag-click sa mapa" required autocomplete="off">
                                    <div id="location_suggestions" style="position: absolute; z-index: 1000; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; max-height: 300px; overflow-y: auto; width: 100%; display: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"></div>
                                </div>
                                <div id="map" class="mb-2"></div>
                                <input type="hidden" name="incident_latitude" id="incident_latitude">
                                <input type="hidden" name="incident_longitude" id="incident_longitude">
                                <p class="text-xs text-gray-500 mt-2">
                                    <span class="text-blue-600">💡 Tip:</span> Mag-type ng address sa San Miguel, Pasig City o mag-click sa mapa para mag-pin ng eksaktong lokasyon
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" class="next-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Susunod</button>
                            </div>
                        </div>

                        <!-- Tab 2: Impormasyon ng Nagrereklamo -->
                        <div id="tab2" class="tab-content">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Impormasyon ng Nagrereklamo</h2>

                            <!-- Hidden field for AI-detected complaint type -->
                            <input type="hidden" name="complaint_description" id="complaint_description" value="">

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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petsa ng Kapanganakan (18 taong gulang pataas)</label>
                                    <input type="date" name="complainant_dob" id="complainant_dob" class="w-full p-2 border border-gray-300 rounded-md" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                                    <input type="hidden" name="complainant_age" id="complainant_age">
                                    <p class="text-xs text-gray-500 mt-1">Edad: <span id="complainant_age_display">-</span></p>
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
                                <input type="text" name="complainant_address" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Halimbawa: 123 Main St, San Miguel, Pasig City">
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petsa ng Kapanganakan (1 taong gulang pataas)</label>
                                    <input type="date" name="victim_dob" id="victim_dob" class="w-full p-2 border border-gray-300 rounded-md" max="<?php echo date('Y-m-d', strtotime('-1 year')); ?>" required>
                                    <input type="hidden" name="victim_age" id="victim_age">
                                    <p class="text-xs text-gray-500 mt-1">Edad: <span id="victim_age_display">-</span></p>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petsa ng Kapanganakan (10 taong gulang pataas)</label>
                                    <input type="date" name="witness_dob" id="witness_dob" class="w-full p-2 border border-gray-300 rounded-md" max="<?php echo date('Y-m-d', strtotime('-10 years')); ?>">
                                    <input type="hidden" name="witness_age" id="witness_age">
                                    <p class="text-xs text-gray-500 mt-1">Edad: <span id="witness_age_display">-</span></p>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Petsa ng Kapanganakan (18 taong gulang pataas)</label>
                                    <input type="date" name="respondent_dob" id="respondent_dob" class="w-full p-2 border border-gray-300 rounded-md" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                                    <input type="hidden" name="respondent_age" id="respondent_age">
                                    <p class="text-xs text-gray-500 mt-1">Edad: <span id="respondent_age_display">-</span></p>
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
                                <textarea name="complaint_statement" id="complaint_statement" rows="6" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                                <p class="text-xs text-gray-500 mt-2">Ang uri ng reklamo ay awtomatikong matatanggap batay sa iyong salaysay.</p>
                            </div>

                            <div id="detected_complaint_type" class="mb-6 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-blue-900">Natukoy na Uri ng Reklamo:</p>
                                            <p class="text-lg font-bold text-blue-700 mt-1" id="detected_type_display"></p>
                                        </div>
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
                                <button type="button" id="reviewBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">I-Check</button>
                            </div>
                        </div>

                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Initial Questions Modal -->
    <div id="initialQuestionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-40">
        <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Paunang mga Katanungan</h3>
                <p class="text-sm text-gray-600 mb-4">Pumili ng KAHIT ISA sa mga sumusunod (Piliin lang ang mga naaaangkop)</p>

                <div class="space-y-3 mb-2">
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer" onclick="toggleInitialCheckbox('sameAsVictim')">
                        <input type="checkbox" id="sameAsVictim" class="mt-1 pointer-events-none">
                        <label for="sameAsVictim" class="text-sm font-medium cursor-pointer flex-1">Ang nagrereklamo ay ang biktima rin</label>
                    </div>

                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer" onclick="toggleInitialCheckbox('noWitness')">
                        <input type="checkbox" id="noWitness" class="mt-1 pointer-events-none">
                        <label for="noWitness" class="text-sm font-medium cursor-pointer flex-1">Walang Saksi</label>
                    </div>

                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer" onclick="toggleInitialCheckbox('noRespondent')">
                        <input type="checkbox" id="noRespondent" class="mt-1 pointer-events-none">
                        <label for="noRespondent" class="text-sm font-medium cursor-pointer flex-1">Walang Inireklamo</label>
                    </div>
                </div>

                <p id="initialModalError" class="text-red-600 text-sm mb-3 hidden">Pumili ng kahit isa sa mga pagpipilian</p>

                <div class="flex justify-end">
                    <button id="startBlotterBtn" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">Magpatuloy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-6 border w-full max-w-3xl shadow-lg rounded-md bg-white my-10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-900">Suriin ang Reklamo</h3>
                <button id="closeReviewModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            <div class="max-h-96 overflow-y-auto mb-6">
                <!-- Pinangayarihan Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Pinangayarihan</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Petsa at Oras ng Insidente:</span> <span id="review_incident_datetime"></span></div>
                        <div><span class="font-semibold">Lugar ng Pinangyarihan:</span> <span id="review_incident_location"></span></div>
                    </div>
                </div>

                <!-- Nagrereklamo Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg" id="review_complainant_section">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Nagrereklamo</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Uri ng Reklamo:</span> <span id="review_complaint_type"></span></div>
                        <div><span class="font-semibold">Unang Pangalan:</span> <span id="review_complainant_first"></span></div>
                        <div><span class="font-semibold">Gitnang Pangalan:</span> <span id="review_complainant_middle"></span></div>
                        <div><span class="font-semibold">Apelyido:</span> <span id="review_complainant_last"></span></div>
                        <div><span class="font-semibold">Edad:</span> <span id="review_complainant_age"></span></div>
                        <div><span class="font-semibold">Kasarian:</span> <span id="review_complainant_gender"></span></div>
                        <div><span class="font-semibold">Telepono:</span> <span id="review_complainant_phone"></span></div>
                        <div class="col-span-2"><span class="font-semibold">Tirahan:</span> <span id="review_complainant_address"></span></div>
                    </div>
                </div>

                <!-- Biktima Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Biktima</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Unang Pangalan:</span> <span id="review_victim_first"></span></div>
                        <div><span class="font-semibold">Gitnang Pangalan:</span> <span id="review_victim_middle"></span></div>
                        <div><span class="font-semibold">Apelyido:</span> <span id="review_victim_last"></span></div>
                        <div><span class="font-semibold">Edad:</span> <span id="review_victim_age"></span></div>
                        <div><span class="font-semibold">Kasarian:</span> <span id="review_victim_gender"></span></div>
                        <div><span class="font-semibold">Telepono:</span> <span id="review_victim_phone"></span></div>
                        <div class="col-span-2"><span class="font-semibold">Tirahan:</span> <span id="review_victim_address"></span></div>
                    </div>
                </div>

                <!-- Saksi Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg" id="review_witness_section">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Saksi</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Unang Pangalan:</span> <span id="review_witness_first"></span></div>
                        <div><span class="font-semibold">Gitnang Pangalan:</span> <span id="review_witness_middle"></span></div>
                        <div><span class="font-semibold">Apelyido:</span> <span id="review_witness_last"></span></div>
                        <div><span class="font-semibold">Edad:</span> <span id="review_witness_age"></span></div>
                        <div><span class="font-semibold">Kasarian:</span> <span id="review_witness_gender"></span></div>
                        <div><span class="font-semibold">Telepono:</span> <span id="review_witness_phone"></span></div>
                        <div class="col-span-2"><span class="font-semibold">Tirahan:</span> <span id="review_witness_address"></span></div>
                    </div>
                </div>

                <!-- Inireklamo Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg" id="review_respondent_section">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Inireklamo</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Unang Pangalan:</span> <span id="review_respondent_first"></span></div>
                        <div><span class="font-semibold">Gitnang Pangalan:</span> <span id="review_respondent_middle"></span></div>
                        <div><span class="font-semibold">Apelyido:</span> <span id="review_respondent_last"></span></div>
                        <div><span class="font-semibold">Edad:</span> <span id="review_respondent_age"></span></div>
                        <div><span class="font-semibold">Kasarian:</span> <span id="review_respondent_gender"></span></div>
                        <div><span class="font-semibold">Telepono:</span> <span id="review_respondent_phone"></span></div>
                        <div class="col-span-2"><span class="font-semibold">Tirahan:</span> <span id="review_respondent_address"></span></div>
                    </div>
                </div>

                <!-- Salaysay Section -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-bold text-lg mb-3 text-blue-800">Salaysay</h4>
                    <div class="text-sm">
                        <p id="review_statement" class="whitespace-pre-wrap"></p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button id="editBtn" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">I-edit</button>
                <button type="submit" form="blotterForm" name="submit_complaint" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Magsalita</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Submitted Successfully</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">The blotter report has been saved.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="ok-btn" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script src="js/sidebar.js" defer></script>
    <script>
        // Toggle checkbox function for initial modal (only allow one selection)
        function toggleInitialCheckbox(checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            const allCheckboxes = [
                document.getElementById('sameAsVictim'),
                document.getElementById('noWitness'),
                document.getElementById('noRespondent')
            ];

            // Uncheck all others
            allCheckboxes.forEach(cb => {
                if (cb.id !== checkboxId) {
                    cb.checked = false;
                }
            });

            // Toggle the clicked one
            checkbox.checked = !checkbox.checked;

            // Hide error message
            document.getElementById('initialModalError').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initial Questions Modal Logic
            const initialQuestionsModal = document.getElementById('initialQuestionsModal');
            const startBlotterBtn = document.getElementById('startBlotterBtn');
            const sameAsVictimCheckbox = document.getElementById('sameAsVictim');
            const noWitnessCheckbox = document.getElementById('noWitness');
            const noRespondentCheckbox = document.getElementById('noRespondent');

            // Tab references
            const tabButtons = document.querySelectorAll('.tab-button');
            const nagrereklamo_tab = tabButtons[1]; // Nagrereklamo
            const saksi_tab = tabButtons[3]; // Saksi
            const inireklamo_tab = tabButtons[4]; // Inireklamo

            startBlotterBtn.addEventListener('click', function() {
                // Check if at least one checkbox is selected
                if (!sameAsVictimCheckbox.checked && !noWitnessCheckbox.checked && !noRespondentCheckbox.checked) {
                    document.getElementById('initialModalError').classList.remove('hidden');
                    return;
                }

                // Hide/show tabs based on selections
                if (sameAsVictimCheckbox.checked) {
                    // Keep Nagrereklamo tab visible - data will be auto-filled from Biktima
                    // Keep required validation
                }

                if (noWitnessCheckbox.checked) {
                    // Hide Saksi tab
                    saksi_tab.style.display = 'none';
                    // Remove required from Saksi fields
                    document.getElementById('tab4').querySelectorAll('[required]').forEach(field => {
                        field.removeAttribute('required');
                    });
                }

                if (noRespondentCheckbox.checked) {
                    // Hide Inireklamo tab
                    inireklamo_tab.style.display = 'none';
                    // Remove required from Inireklamo fields
                    document.getElementById('tab5').querySelectorAll('[required]').forEach(field => {
                        field.removeAttribute('required');
                    });
                }

                // If complainant is same as victim, copy victim data to complainant on form submit
                if (sameAsVictimCheckbox.checked) {
                    window.complainantIsVictim = true;
                }

                // Close modal
                initialQuestionsModal.style.display = 'none';
            });

            let map, marker;

            // Barangay San Miguel, Pasig City - Complete boundary coverage
            // Coordinates traced to cover the entire barangay area
            const barangayBounds = L.polygon([
                [14.57010363728592, 121.0818473841725], // North 1
                [14.56794188700946, 121.08072370823959], // North 2
                [14.567844642158477, 121.08125120248977], // North 3
                [14.567917575800728, 121.08137679635885], // Northeast 1
                [14.567844642158477, 121.08165310287083], // Northeast 2
                [14.567625841086917, 121.08157774654939], // East 1
                [14.567723086034412, 121.08079906456103], // East 2
                [14.567115304408842, 121.08064835191814],
                [14.566922561130774, 121.0811209342382],
                [14.566819841173118, 121.08126686516077],
                [14.565997356654702, 121.08066853785174],
                [14.56526271163826, 121.08098776045625],
                [14.565355608171254, 121.08174655063992],
                [14.565635976700102, 121.08243051390514],
                [14.566393455215408, 121.08376156002336],
                [14.565579011109662, 121.08425981111765],
                [14.564185876060487, 121.08451447278806],
                [14.564175159602922, 121.0861199485364],
                [14.564785996843405, 121.08783990192924],
                [14.564014412667293, 121.09022043493535],
                [14.562503385835896, 121.09043080761961],
                [14.562203322511074, 121.09401821569278],
                [14.561238830574585, 121.09431716634937],
                [14.562347177018786, 121.09793885572962],
                [14.563859984830886, 121.095967311869],
                [14.564410094189665, 121.09593178855619],
                [14.56511491980012, 121.09626926002782],
                [14.56508053811528, 121.09660673149948],
                [14.569279702260326, 121.09658532005055],
                [14.56912625713439, 121.0947276774116],
                [14.5710940628687, 121.09446015214326],
                [14.571786086990267, 121.08774561479912],
                [14.568590240213766, 121.08591114833624],
                [14.570204310033438, 121.08187532211778]
            ], {
                color: '#1e40af',
                weight: 3,
                opacity: 0.8,
                fillColor: '#3b82f6',
                fillOpacity: 0.15,
                interactive: false
            });

            map = L.map('map').setView([14.5700, 121.0850], 15);

            // Add OpenStreetMap tiles (free!)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Add barangay boundary to map (no popup since it's non-interactive)
            barangayBounds.addTo(map);

            // Function to check if point is ACTUALLY within the polygon (not just bounding box)
            function isWithinBarangay(lat, lng) {
                // Use Leaflet's built-in point-in-polygon check
                const point = L.latLng(lat, lng);
                const polygonCoords = barangayBounds.getLatLngs()[0];

                // Ray casting algorithm for point-in-polygon
                let inside = false;
                for (let i = 0, j = polygonCoords.length - 1; i < polygonCoords.length; j = i++) {
                    const xi = polygonCoords[i].lat,
                        yi = polygonCoords[i].lng;
                    const xj = polygonCoords[j].lat,
                        yj = polygonCoords[j].lng;

                    const intersect = ((yi > lng) !== (yj > lng)) &&
                        (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
                    if (intersect) inside = !inside;
                }
                return inside;
            }

            // Add click event to place pin
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Check if click is within barangay boundaries
                if (!isWithinBarangay(lat, lng)) {
                    alert('Mangyaring pumili ng lokasyon sa loob ng Barangay San Miguel lamang.\nPlease select a location within Barangay San Miguel only.');
                    return;
                }

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
                            document.getElementById('incident_location').value = data.display_name;
                        }
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        document.getElementById('incident_location').value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    });

                // Make marker draggable and update location on drag
                marker.on('dragend', function(e) {
                    const newLat = e.target.getLatLng().lat;
                    const newLng = e.target.getLatLng().lng;

                    // Check if new position is within barangay
                    if (!isWithinBarangay(newLat, newLng)) {
                        alert('Mangyaring ilagay ang marker sa loob ng Barangay San Miguel lamang.\nPlease place the marker within Barangay San Miguel only.');
                        // Reset marker to previous valid position or center
                        marker.setLatLng([14.5700, 121.0850]);
                        return;
                    }

                    document.getElementById('incident_latitude').value = newLat;
                    document.getElementById('incident_longitude').value = newLng;

                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${newLat}&lon=${newLng}&zoom=18&addressdetails=1`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.display_name) {
                                document.getElementById('incident_location').value = data.display_name;
                            }
                        })
                        .catch(error => console.error('Geocoding error:', error));
                });
            });

            // Dynamic location autocomplete with Nominatim API
            const locationInput = document.getElementById('incident_location');
            const suggestionsDiv = document.getElementById('location_suggestions');
            let searchTimeout;
            let selectedSuggestion = null;

            // Search for addresses in San Miguel, Pasig City as user types
            locationInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 3) {
                    suggestionsDiv.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    // Search using Nominatim API filtered for San Miguel, Pasig City
                    const searchQuery = `${query}, San Miguel, Pasig City, Philippines`;
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=10&addressdetails=1`)
                        .then(response => response.json())
                        .then(data => {
                            // Filter results to only show San Miguel, Pasig City addresses
                            const filteredResults = data.filter(item => {
                                const address = item.address || {};
                                const displayName = item.display_name.toLowerCase();
                                return (
                                    (address.suburb === 'San Miguel' || displayName.includes('san miguel')) &&
                                    (address.city === 'Pasig' || address.city === 'Pasig City' || displayName.includes('pasig'))
                                );
                            });

                            displaySuggestions(filteredResults);
                        })
                        .catch(error => {
                            console.error('Error fetching locations:', error);
                            suggestionsDiv.style.display = 'none';
                        });
                }, 500); // Debounce for 500ms
            });

            function displaySuggestions(results) {
                suggestionsDiv.innerHTML = '';

                if (results.length === 0) {
                    suggestionsDiv.innerHTML = '<div style="padding: 8px; color: #6b7280;">Walang nahanap na address sa San Miguel, Pasig City</div>';
                    suggestionsDiv.style.display = 'block';
                    return;
                }

                results.forEach(result => {
                    const div = document.createElement('div');
                    div.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid #e5e7eb;';
                    div.innerHTML = `
                        <div style="font-weight: 500; color: #1f2937;">${result.display_name}</div>
                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;">Lat: ${parseFloat(result.lat).toFixed(6)}, Lng: ${parseFloat(result.lon).toFixed(6)}</div>
                    `;

                    div.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f3f4f6';
                    });

                    div.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = 'white';
                    });

                    div.addEventListener('click', function() {
                        selectLocation(result);
                    });

                    suggestionsDiv.appendChild(div);
                });

                suggestionsDiv.style.display = 'block';
            }

            function selectLocation(location) {
                const lat = parseFloat(location.lat);
                const lon = parseFloat(location.lon);

                // Check if within barangay
                if (!isWithinBarangay(lat, lon)) {
                    alert('Ang napiling lokasyon ay nasa labas ng Barangay San Miguel.\nThe selected location is outside Barangay San Miguel.');
                    suggestionsDiv.style.display = 'none';
                    return;
                }

                locationInput.value = location.display_name;
                document.getElementById('incident_latitude').value = lat;
                document.getElementById('incident_longitude').value = lon;
                suggestionsDiv.style.display = 'none';

                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }

                // Add marker at selected location
                marker = L.marker([lat, lon], {
                    draggable: true
                }).addTo(map);

                // Pan map to marker
                map.setView([lat, lon], 17);

                // Make marker draggable
                marker.on('dragend', function(e) {
                    const newLat = e.target.getLatLng().lat;
                    const newLng = e.target.getLatLng().lng;

                    if (!isWithinBarangay(newLat, newLng)) {
                        alert('Mangyaring ilagay ang marker sa loob ng Barangay San Miguel lamang.\nPlease place the marker within Barangay San Miguel only.');
                        marker.setLatLng([lat, lon]);
                        return;
                    }

                    document.getElementById('incident_latitude').value = newLat;
                    document.getElementById('incident_longitude').value = newLng;

                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${newLat}&lon=${newLng}&zoom=18&addressdetails=1`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.display_name) {
                                document.getElementById('incident_location').value = data.display_name;
                            }
                        })
                        .catch(error => console.error('Geocoding error:', error));
                });
            }

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== locationInput && e.target !== suggestionsDiv) {
                    suggestionsDiv.style.display = 'none';
                }
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
                tab.addEventListener('click', () => {
                    // Don't allow clicking on tabs if they're hidden
                    if (tab.style.display === 'none') {
                        return;
                    }

                    // If clicking a forward tab (higher index), validate current tab first
                    if (index > currentTab) {
                        if (!validateCurrentTab()) {
                            return;
                        }
                    }

                    // If clicking backward, allow it without validation
                    showTab(index);
                });
            });

            // Next/Previous buttons
            document.querySelectorAll('.next-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    // Validate current tab before moving to next
                    if (!validateCurrentTab()) {
                        return;
                    }

                    if (currentTab < tabs.length - 1) {
                        // Find next visible tab
                        let nextTab = currentTab + 1;
                        while (nextTab < tabs.length && tabs[nextTab].style.display === 'none') {
                            nextTab++;
                        }
                        if (nextTab < tabs.length) {
                            showTab(nextTab);
                        }
                    }
                });
            });

            document.querySelectorAll('.prev-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (currentTab > 0) {
                        // Find previous visible tab
                        let prevTab = currentTab - 1;
                        while (prevTab >= 0 && tabs[prevTab].style.display === 'none') {
                            prevTab--;
                        }
                        if (prevTab >= 0) {
                            showTab(prevTab);
                        }
                    }
                });
            });

            // Complaint Description "Others" field toggle
            const complaintDescSelect = document.getElementById('complaint_description');
            const otherComplaintInput = document.getElementById('other_complaint');

            if (complaintDescSelect) {
                complaintDescSelect.addEventListener('change', function() {
                    if (this.value === 'Others') {
                        otherComplaintInput.classList.remove('hidden');
                        otherComplaintInput.setAttribute('required', 'required');
                    } else {
                        otherComplaintInput.classList.add('hidden');
                        otherComplaintInput.removeAttribute('required');
                        otherComplaintInput.value = '';
                    }
                });
            }

            // Form Validation Function
            function validateCurrentTab() {
                const currentTabContent = tabContents[currentTab];
                const requiredFields = currentTabContent.querySelectorAll('[required]');

                for (let field of requiredFields) {
                    if (!field.value || field.value.trim() === '') {
                        field.focus();
                        field.classList.add('border-red-500');
                        setTimeout(() => field.classList.remove('border-red-500'), 3000);

                        // Show error message
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'fixed top-20 right-5 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        errorMsg.textContent = 'Pakipunan ang lahat ng kinakailangang field';
                        document.body.appendChild(errorMsg);
                        setTimeout(() => errorMsg.remove(), 3000);

                        return false;
                    }
                }

                // Additional validation: Check if DOB fields have corresponding age calculated
                const dobFields = currentTabContent.querySelectorAll('input[type="date"][name$="_dob"]');
                for (let dobField of dobFields) {
                    if (dobField.value && dobField.hasAttribute('required')) {
                        const personType = dobField.name.replace('_dob', '');
                        const ageField = document.getElementById(personType + '_age');
                        if (ageField && !ageField.value) {
                            dobField.focus();
                            dobField.classList.add('border-red-500');
                            setTimeout(() => dobField.classList.remove('border-red-500'), 3000);

                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'fixed top-20 right-5 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                            errorMsg.textContent = 'Pakipili ng wastong petsa ng kapanganakan';
                            document.body.appendChild(errorMsg);
                            setTimeout(() => errorMsg.remove(), 3000);

                            return false;
                        }
                    }
                }

                return true;
            }

            // Review Modal Logic
            const reviewBtn = document.getElementById('reviewBtn');
            const reviewModal = document.getElementById('reviewModal');
            const closeReviewModal = document.getElementById('closeReviewModal');
            const editBtn = document.getElementById('editBtn');

            reviewBtn.addEventListener('click', function() {
                // Validate that checkboxes are checked
                const reportedBy = document.querySelector('[name="reported_by"]');
                const isAffirmed = document.querySelector('[name="is_affirmed"]');

                if (!reportedBy.checked) {
                    reportedBy.focus();
                    alert('Mangyaring i-check ang "Inuulat sa pamamagitan ng: Personal"');
                    return;
                }

                if (!isAffirmed.checked) {
                    isAffirmed.focus();
                    alert('Mangyaring i-check ang patunay na ang mga detalye ay totoo at tama');
                    return;
                }

                // Validate all required fields in the form
                const allRequiredFields = document.getElementById('blotterForm').querySelectorAll('[required]');
                for (let field of allRequiredFields) {
                    // Skip fields in hidden tabs
                    const parentTab = field.closest('.tab-content');
                    if (parentTab && parentTab.style.display === 'none') continue;

                    // Skip hidden fields
                    if (field.classList.contains('hidden')) continue;

                    if (!field.value || field.value.trim() === '') {
                        // Find which tab this field belongs to
                        const tabIndex = Array.from(tabContents).indexOf(parentTab);
                        if (tabIndex !== -1) {
                            showTab(tabIndex);
                        }

                        field.focus();
                        field.classList.add('border-red-500');
                        setTimeout(() => field.classList.remove('border-red-500'), 3000);

                        alert('Pakipunan ang lahat ng kinakailangang field sa lahat ng mga tab');
                        return;
                    }
                }

                // Populate review modal with form data
                const incidentDate = document.querySelector('[name="incident_date"]').value;
                const incidentHour = document.querySelector('[name="incident_hour"]').value;
                const incidentMinute = document.querySelector('[name="incident_minute"]').value;
                const incidentPeriod = document.querySelector('[name="incident_period"]').value;

                document.getElementById('review_incident_datetime').textContent = `${incidentDate} ${incidentHour}:${incidentMinute} ${incidentPeriod}`;
                document.getElementById('review_incident_location').textContent = document.querySelector('[name="incident_location"]').value;

                // Complaint Type (AI-detected)
                const complaintType = document.getElementById('complaint_description').value;
                document.getElementById('review_complaint_type').textContent = complaintType || 'Hindi pa natukoy';

                // Complainant
                document.getElementById('review_complainant_first').textContent = document.querySelector('[name="complainant_first_name"]').value || 'N/A';
                document.getElementById('review_complainant_middle').textContent = document.querySelector('[name="complainant_middle_name"]').value || 'N/A';
                document.getElementById('review_complainant_last').textContent = document.querySelector('[name="complainant_last_name"]').value || 'N/A';
                document.getElementById('review_complainant_age').textContent = document.querySelector('[name="complainant_age"]').value || 'N/A';
                document.getElementById('review_complainant_gender').textContent = document.querySelector('[name="complainant_gender"]').value || 'N/A';
                document.getElementById('review_complainant_phone').textContent = document.querySelector('[name="complainant_phone"]').value || 'N/A';
                document.getElementById('review_complainant_address').textContent = document.querySelector('[name="complainant_address"]').value || 'N/A';

                // Victim
                document.getElementById('review_victim_first').textContent = document.querySelector('[name="victim_first_name"]').value;
                document.getElementById('review_victim_middle').textContent = document.querySelector('[name="victim_middle_name"]').value || 'N/A';
                document.getElementById('review_victim_last').textContent = document.querySelector('[name="victim_last_name"]').value;
                document.getElementById('review_victim_age').textContent = document.querySelector('[name="victim_age"]').value;
                document.getElementById('review_victim_gender').textContent = document.querySelector('[name="victim_gender"]').value;
                document.getElementById('review_victim_phone').textContent = document.querySelector('[name="victim_phone"]').value;
                document.getElementById('review_victim_address').textContent = document.querySelector('[name="victim_address"]').value;

                // Witness
                document.getElementById('review_witness_first').textContent = document.querySelector('[name="witness_first_name"]').value || 'N/A';
                document.getElementById('review_witness_middle').textContent = document.querySelector('[name="witness_middle_name"]').value || 'N/A';
                document.getElementById('review_witness_last').textContent = document.querySelector('[name="witness_last_name"]').value || 'N/A';
                document.getElementById('review_witness_age').textContent = document.querySelector('[name="witness_age"]').value || 'N/A';
                document.getElementById('review_witness_gender').textContent = document.querySelector('[name="witness_gender"]').value || 'N/A';
                document.getElementById('review_witness_phone').textContent = document.querySelector('[name="witness_phone"]').value || 'N/A';
                document.getElementById('review_witness_address').textContent = document.querySelector('[name="witness_address"]').value || 'N/A';

                // Respondent
                document.getElementById('review_respondent_first').textContent = document.querySelector('[name="respondent_first_name"]').value || 'N/A';
                document.getElementById('review_respondent_middle').textContent = document.querySelector('[name="respondent_middle_name"]').value || 'N/A';
                document.getElementById('review_respondent_last').textContent = document.querySelector('[name="respondent_last_name"]').value || 'N/A';
                document.getElementById('review_respondent_age').textContent = document.querySelector('[name="respondent_age"]').value || 'N/A';
                document.getElementById('review_respondent_gender').textContent = document.querySelector('[name="respondent_gender"]').value || 'N/A';
                document.getElementById('review_respondent_phone').textContent = document.querySelector('[name="respondent_phone"]').value || 'N/A';
                document.getElementById('review_respondent_address').textContent = document.querySelector('[name="respondent_address"]').value || 'N/A';

                // Statement
                document.getElementById('review_statement').textContent = document.querySelector('[name="complaint_statement"]').value;

                // Hide sections that were disabled in initial modal
                if (window.complainantIsVictim || nagrereklamo_tab.style.display === 'none') {
                    document.getElementById('review_complainant_section').style.display = 'none';
                }
                if (saksi_tab.style.display === 'none') {
                    document.getElementById('review_witness_section').style.display = 'none';
                }
                if (inireklamo_tab.style.display === 'none') {
                    document.getElementById('review_respondent_section').style.display = 'none';
                }

                // Show modal
                reviewModal.classList.remove('hidden');
            });

            closeReviewModal.addEventListener('click', function() {
                reviewModal.classList.add('hidden');
            });

            editBtn.addEventListener('click', function() {
                reviewModal.classList.add('hidden');
            });

            // Prevent closing review modal by clicking outside
            reviewModal.addEventListener('click', function(e) {
                if (e.target === reviewModal) {
                    e.stopPropagation();
                }
            });

            // Auto-calculate age from DOB
            function calculateAge(dob) {
                if (!dob) return null;
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age >= 0 ? age : null;
            }

            // Complainant DOB
            document.getElementById('complainant_dob')?.addEventListener('change', function() {
                const age = calculateAge(this.value);
                document.getElementById('complainant_age').value = age || '';
                document.getElementById('complainant_age_display').textContent = age !== null ? age + ' taong gulang' : '-';
            });

            // Victim DOB
            document.getElementById('victim_dob')?.addEventListener('change', function() {
                const age = calculateAge(this.value);
                document.getElementById('victim_age').value = age || '';
                document.getElementById('victim_age_display').textContent = age !== null ? age + ' taong gulang' : '-';
            });

            // Witness DOB
            document.getElementById('witness_dob')?.addEventListener('change', function() {
                const age = calculateAge(this.value);
                document.getElementById('witness_age').value = age || '';
                document.getElementById('witness_age_display').textContent = age !== null ? age + ' taong gulang' : '-';
            });

            // Respondent DOB
            document.getElementById('respondent_dob')?.addEventListener('change', function() {
                const age = calculateAge(this.value);
                document.getElementById('respondent_age').value = age || '';
                document.getElementById('respondent_age_display').textContent = age !== null ? age + ' taong gulang' : '-';
            });

            // AI-based Complaint Type Detection
            let detectionTimeout;
            const complaintStatementField = document.getElementById('complaint_statement');
            const detectedTypeContainer = document.getElementById('detected_complaint_type');
            const detectedTypeDisplay = document.getElementById('detected_type_display');
            const complaintDescriptionField = document.getElementById('complaint_description');

            complaintStatementField?.addEventListener('input', function() {
                clearTimeout(detectionTimeout);

                const statement = this.value.trim();

                if (statement.length < 20) {
                    detectedTypeContainer.classList.add('hidden');
                    return;
                }

                // Show loading state
                detectedTypeContainer.classList.remove('hidden');
                detectedTypeDisplay.innerHTML = '<span class="text-gray-500">Tinutukoy ang uri ng reklamo...</span>';

                detectionTimeout = setTimeout(() => {
                    detectComplaintType(statement);
                }, 1000); // Debounce for 1 second
            });

            async function detectComplaintType(statement) {
                try {
                    // Using Groq API - Get your free API key from https://console.groq.com/
                    const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer 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",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            "model": "llama-3.3-70b-versatile",
                            "messages": [{
                                    "role": "system",
                                    "content": `You are an expert crime classifier for Philippine Barangay incidents. Analyze the complaint carefully and identify the PRIMARY crime.

CRITICAL CLASSIFICATION RULES:
1. THEFT = Taking property WITHOUT force/threat (nakaw, magnanakaw)
2. ROBBERY = Taking property WITH force/weapons (holdap, armadong pagnanakaw)
3. KIDNAPPING = Illegally taking/transporting person to another location (dinukot, dinala sa ibang lugar)
4. HOSTAGE TAKING = Holding person by force/threat at the scene (ginahasa, tinutukan, walang ninakaw)
5. PHYSICAL ASSAULT = Attacking someone WITH intent to harm (suntok, sipa, bugbog)
6. PHYSICAL INJURIES = Harm from accident or minor altercation (sugat, gasgas, nabundol)
7. MURDER = Intentional killing with planning (pinatay, pinaslang)
8. HOMICIDE = Killing without premeditation (aksidenteng namatay)
9. DOMESTIC VIOLENCE = Violence within family/household (asawa, anak, magulang)

RESPONSE FORMAT:
- Return ONLY ONE crime type from the list
- NO explanations, categories, or extra words
- Use EXACT spelling from the list

CRIME TYPES:
Murder | Homicide | Rape | Robbery | Theft | Physical Assault | Carnapping | Arson | Kidnapping | Hostage Taking | Drug-related | Illegal Gambling | Illegal Possession of Firearms | Violation of Special Laws | Physical Injuries | Vandalism | Noise Complaints | Domestic Violence | Trespassing | Boundary Disputes | Property Disputes

EXAMPLES:
"Ninakaw ang wallet ko sa jeep" → Theft
"Hinoldap ako, tinakot ng baril at kinuha ang pera" → Robbery
"May lalaking pumasok sa bahay at nagnakaw ng laptop" → Theft
"Biglang hinila ako at tinutukan ng kutsilyo sa leeg habang sinasabi sa pulis na patakasin sya" → Hostage Taking
"Dinukot ako at dinala sa malayong lugar" → Kidnapping
"Sinuntok ako ng kapitbahay dahil sa alitan" → Physical Assault
"Nasaktan ako nung nabundol ako ng bisikleta" → Physical Injuries
"Pinatay ng asawa ang kanyang misis" → Murder
"Nagtulakan kami at nabaril ko siya ng aksidente" → Homicide
"Sinaktan ako ng asawa ko" → Domestic Violence
"Nagnakaw ng motor gamit ang baril" → Robbery
"Kinidnap ang anak ko at dinala sa ibang lugar at hiningan ng ransom" → Kidnapping
"Ginahasa ako ng lalaking di ko kilala" → Rape
"Sinunog ang bahay ng kapitbahay" → Arson
"Ninakaw ang kotse ko sa parking" → Carnapping
"Hinawakan niya ako at tinakot ng kutsilyo para makuha yung bag" → Robbery
"Pinagtago niya ako sa kuwarto at tinutukan ng baril" → Hostage Taking`
                                },
                                {
                                    "role": "user",
                                    "content": `Classify this complaint statement:\n\n${statement}`
                                }
                            ],
                            "temperature": 0.3,
                            "max_tokens": 50
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || data.error) {
                        console.error('API Error:', data);
                        throw new Error(data.error?.message || `HTTP ${response.status}: ${response.statusText}`);
                    }

                    const detectedType = data.choices[0].message.content.trim();

                    // Update UI
                    detectedTypeDisplay.textContent = detectedType;
                    complaintDescriptionField.value = detectedType;

                    console.log('Detected complaint type:', detectedType);

                } catch (error) {
                    console.error('Error detecting complaint type:', error);
                    const errorMessage = error.message || 'Unknown error';
                    detectedTypeDisplay.innerHTML = `<span class="text-red-500 text-xs">Error: ${errorMessage}</span>`;

                    // Don't retry on authentication errors (401)
                    if (!errorMessage.includes('not found') && !errorMessage.includes('401')) {
                        setTimeout(() => {
                            if (complaintStatementField.value.trim().length >= 20) {
                                detectComplaintType(complaintStatementField.value.trim());
                            }
                        }, 3000);
                    }
                }
            }

            // Form submission: Copy victim data to complainant if they're the same
            document.getElementById('blotterForm').addEventListener('submit', function(e) {
                if (window.complainantIsVictim) {
                    // Copy victim data to complainant fields
                    document.querySelector('[name="complainant_first_name"]').value = document.querySelector('[name="victim_first_name"]').value;
                    document.querySelector('[name="complainant_middle_name"]').value = document.querySelector('[name="victim_middle_name"]').value;
                    document.querySelector('[name="complainant_last_name"]').value = document.querySelector('[name="victim_last_name"]').value;
                    document.querySelector('[name="complainant_dob"]').value = document.querySelector('[name="victim_dob"]').value;
                    document.querySelector('[name="complainant_age"]').value = document.querySelector('[name="victim_age"]').value;
                    document.querySelector('[name="complainant_gender"]').value = document.querySelector('[name="victim_gender"]').value;
                    document.querySelector('[name="complainant_phone"]').value = document.querySelector('[name="victim_phone"]').value;
                    document.querySelector('[name="complainant_address"]').value = document.querySelector('[name="victim_address"]').value;
                }
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