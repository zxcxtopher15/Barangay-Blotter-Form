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
    <title>Settings - Barangay San Miguel</title>
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
        /* Simple toggle switch style */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #68D391;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #68D391;
        }
    </style>
</head>
<body class="bg-light-gray">

    <?php
    // Read the sidebar state from localStorage via a small script
    // This must run before any HTML for the sidebar is rendered to prevent flicker
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
                    <!-- Hamburger Toggle Button -->
                    <button id="sidebarToggle" class="mr-4 text-white hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-3xl font-bold">Settings</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <!-- Settings Content -->
            <main class="p-6 flex-1 overflow-y-auto">
                <div class="max-w-4xl mx-auto">

                    <!-- Profile Settings Card -->
                    <div class="bg-white p-8 rounded-xl shadow-md mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6">Profile Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="fullName" value="<?= htmlspecialchars($google_name ?? 'Error') ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" id="email" value="<?= htmlspecialchars($google_email ?? 'Error') ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" readonly>
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                            <div class="mt-2 flex items-center space-x-4">
                                <img class="h-16 w-16 rounded-full" src="<?= htmlspecialchars($google_picture ?? 'Error') ?>" alt="Current profile photo">

                </div>
            </main>
        </div>
    </div>
    <script src="js/sidebar.js" defer></script>
    <script>
    // This script runs before the page is rendered
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>
</body>
</html>