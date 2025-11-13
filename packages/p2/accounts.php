<?php
    session_start();
    // Check if the user is logged in, if not then redirect to login page
    if (!isset($_SESSION['google_loggedin'])) {
        header('Location: index.php');
        exit;
    }

    // Retrieve session variables
    $google_loggedin = $_SESSION['google_loggedin'];
    $google_email = $_SESSION['google_email'];
    $google_name = $_SESSION['google_name'];
    $google_picture = $_SESSION['google_picture'];

    // --- START: DATABASE CONNECTION AND STATS FETCHING ---
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

    $message = "";

    // CREATE operation
    if (isset($_POST['add_user'])) {
        $email = $_POST['email_field'];
        if (!empty($email)) {
            // Prepare statement to prevent SQL injection
            $stmt = mysqli_prepare($conn, "INSERT INTO accounts (email) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                $message = "User added successfully!";
            } else {
                $message = "Error adding user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "email cannot be empty.";
        }
    }

    // UPDATE operation
    if (isset($_POST['update_user'])) {
        $old_email = $_POST['old_email']; // Get the original email to identify the record
        $new_email = $_POST['email_field'];
        if (!empty($new_email)) {
            // Prepare statement to prevent SQL injection
            // Using the old email to identify the record for update
            $stmt = mysqli_prepare($conn, "UPDATE accounts SET email = ? WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $new_email, $old_email);
            if (mysqli_stmt_execute($stmt)) {
                $message = "User updated successfully!";
            } else {
                $message = "Error updating user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "email cannot be empty.";
        }
    }

    // DELETE operation
    if (isset($_GET['delete_user'])) {
        $email_to_delete = $_GET['delete_user']; // Using email to delete
        // Prepare statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "DELETE FROM accounts WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email_to_delete);
        if (mysqli_stmt_execute($stmt)) {
            $message = "User deleted successfully!";
        } else {
            $message = "Error deleting user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch all users for READ operation
    $users = [];
    // Select only the email since there's no ID
    $result = mysqli_query($conn, "SELECT email FROM accounts");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
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
        
        // This logic prevents reloading the page when clicking the active link. It is correct and should be kept.
        $dashboardClick = ($currentPage === 'dashboard.php') ? 'onclick="event.preventDefault()"' : '';
        $blotterClick = ($currentPage === 'blotter.php') ? 'onclick="event.preventDefault()"' : '';
        $reportsClick = ($currentPage === 'reports.php') ? 'onclick="event.preventDefault()"' : '';
        $accountsClick = ($currentPage === 'accounts.php') ? 'onclick="event.preventDefault()"' : '';
        $settingsClick = ($currentPage === 'settings.php') ? 'onclick="event.preventDefault()"' : '';
    ?>
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
                <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium <?php echo $currentPage === 'dashboard.php' ? $activeClasses : $inactiveClasses; ?>" <?php echo $dashboardClick; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="blotter.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium <?php echo $currentPage === 'blotter.php' ? $activeClasses : $inactiveClasses; ?>" <?php echo $blotterClick; ?>>
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="sidebar-text ml-3">Blotter</span>
                </a>
                <a href="reports.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium <?php echo $currentPage === 'reports.php' ? $activeClasses : $inactiveClasses; ?>" <?php echo $reportsClick; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="accounts.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium <?php echo $currentPage === 'accounts.php' ? $activeClasses : $inactiveClasses; ?>" <?php echo $accountsClick; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span class="sidebar-text ml-3">Accounts</span>
                </a>
                <a href="settings.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-left font-medium <?php echo $currentPage === 'settings.php' ? $activeClasses : $inactiveClasses; ?>" <?php echo $settingsClick; ?>>
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
                    <img src="<?php echo htmlspecialchars($google_picture ?? 'pics/default-avatar.png'); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full border-2 border-gray-300 shrink-0">
                    <span class="sidebar-text font-medium text-gray-800"><?php echo htmlspecialchars($google_name ?? 'User'); ?></span>
                </div>
            </div>
        </div>
        <!-- END: Sidebar -->
    <?php
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay San Miguel Dashboard</title>
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
</head>
<body class="bg-light-gray">

    <!-- START: FIX - THIS SCRIPT IS THE KEY -->
    <?php
    // This script block is identical to the one in your working dashboard.php.
    // It runs before any HTML is rendered to prevent the sidebar from flickering on page load.
    echo '<script>';
    echo 'if (localStorage.getItem(\'sidebarState\') === \'collapsed\') {';
    // This specific class name is likely used by your CSS to apply the initial state without animation.
    echo '    document.documentElement.classList.add(\'js-sidebar-initial-collapsed\');';
    echo '}';
    echo '</script>';
    ?>
    <!-- END: FIX -->

    <div class="flex h-screen overflow-hidden">
        
        <?php
            // Call the function to render the sidebar
            sidepanel($google_picture, $google_name);
        ?>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 ml-64 flex flex-col">
            <header class="bg-primary text-white p-4 flex justify-between items-center shadow-md z-10">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="mr-4 text-white hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-3xl font-bold">Manage Accounts</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4">
                <div class="container mx-auto">
                    <?php if (!empty($message)): ?>
                        <div class="p-3 mb-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Create User Form -->
                    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                        <h2 class="text-xl font-semibold mb-4">Add New Account</h2>
                        <form action="" method="POST" class="space-y-4">
                            <div>
                                <label for="email_field" class="block text-sm font-medium text-gray-700">email:</label>
                                <input type="text" id="email_field" name="email_field" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>
                            <button type="submit" name="add_user" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-secondary">Add Account</button>
                        </form>
                    </div>

                    <!-- Read and Update Users -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-semibold mb-4">Existing Accounts</h2>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No accounts found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <form action="" method="POST" class="flex items-center space-x-2">
                                                    <input type="hidden" name="old_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                    <input type="text" name="email_field" value="<?php echo htmlspecialchars($user['email']); ?>" class="border border-gray-300 rounded-md shadow-sm p-1">
                                                    <button type="submit" name="update_user" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">Update</button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="?delete_user=<?php echo urlencode(htmlspecialchars($user['email'])); ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/sidebar.js" defer></script>
</body>
</html>