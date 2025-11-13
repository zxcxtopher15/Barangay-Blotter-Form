<?php
    session_start();
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

    // Retrieve session variables
    $google_loggedin = $_SESSION['google_loggedin'];
    $google_email = $_SESSION['google_email'];
    $google_name = $_SESSION['google_name'];
    $google_picture = $_SESSION['google_picture'];

    // --- START: DATABASE CONNECTION AND STATS FETCHING ---
    $db_server = "localhost";
    $db_user = "u416486854_p1";
    $db_pass = "2&rnLACGCldK";
    $db_name = "u416486854_p1";
    $conn = "";

    try {
        $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
        // Set charset for proper handling of characters
        mysqli_set_charset($conn, "utf8");
    } catch (mysqli_sql_exception $e) { // Catch the exception
        die("Database connection failed: " . $e->getMessage()); // Display error
    }

    $message = "";

    // CREATE operation
    if (isset($_POST['add_user'])) {
        $email = trim($_POST['email_field']); // Trim whitespace
        if (!empty($email)) {
            // Check if email already exists before attempting to insert
            $check_stmt = mysqli_prepare($conn, "SELECT email FROM accounts WHERE email = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $message = "Error: Account with this email already exists.";
            } else {
                // Prepare statement to prevent SQL injection
                $stmt = mysqli_prepare($conn, "INSERT INTO accounts (email) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $email);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "User added successfully!";
                } else {
                    // General error during insert, capture MySQL error
                    $message = "Error adding user: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($check_stmt);
        } else {
            $message = "Email cannot be empty.";
        }
    }

    // UPDATE operation
    if (isset($_POST['update_user'])) {
        $old_email = trim($_POST['old_email']); // Trim whitespace
        $new_email = trim($_POST['email_field']); // Trim whitespace

        if (!empty($new_email)) {
            // Check if the new email already exists and is different from the old email
            if ($old_email !== $new_email) {
                $check_stmt = mysqli_prepare($conn, "SELECT email FROM accounts WHERE email = ?");
                mysqli_stmt_bind_param($check_stmt, "s", $new_email);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $message = "Error: Another account with the new email already exists.";
                    mysqli_stmt_close($check_stmt);
                } else {
                    mysqli_stmt_close($check_stmt);
                    // Prepare statement to prevent SQL injection
                    $stmt = mysqli_prepare($conn, "UPDATE accounts SET email = ? WHERE email = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $new_email, $old_email);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "User updated successfully!";
                    } else {
                        $message = "Error updating user: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                }
            } else {
                $message = "No changes detected for update.";
            }
        } else {
            $message = "Email cannot be empty.";
        }
    }

    // DELETE operation
    if (isset($_GET['delete_user'])) {
        $email_to_delete = trim($_GET['delete_user']); // Trim whitespace
        // Prepare statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "DELETE FROM accounts WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email_to_delete);
        if (mysqli_stmt_execute($stmt)) {
            $message = "User deleted successfully!";
        } else {
            $message = "Error deleting user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        // Redirect to clean up the URL after deletion
        header("Location: accountsadmin.php?message=" . urlencode($message));
        exit;
    }

    // Handle message from redirect after delete
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
    }


    // Fetch all users for READ operation
    $users = [];
    // Select only the email since there's no ID
    $admin_email_to_exclude = 'brgysanmiguelpasigblotter@gmail.com';
    $stmt_select = mysqli_prepare($conn, "SELECT email FROM accounts WHERE email != ? ORDER BY email ASC");
    mysqli_stmt_bind_param($stmt_select, "s", $admin_email_to_exclude);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        mysqli_free_result($result); // Free result set
    } else {
        $message = "Error fetching users: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Close connection at the end of the script
    
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
        <!-- START: FULL SIDEBAR HTML (RESTORED) -->
        <?php
            // Call the function to render the sidebar
            sidepanel($google_picture, $google_name);
        ?>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 ml-64 flex flex-col">
            
            <!-- START: Global Message Display -->
            <div id="global-message"class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[100] transition-all duration-300 ease-out transform opacity-0 scale-95 hidden">
                <div class="px-6 py-3 rounded-lg shadow-lg text-white text-center w-auto max-w-lg bg-gray-800">
                    <span id="global-message-text" class="font-medium"></span>
                </div>
            </div>


            <!-- START: FULL HEADER HTML (RESTORED) -->
            <header class="bg-primary text-white p-4 flex justify-between items-center shadow-md z-10">
                <div class="flex items-center">
                    <!-- Hamburger Toggle Button -->
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
            <!-- END: FULL HEADER HTML (RESTORED) -->

            <main class="flex-1 overflow-y-auto p-4">
                <div class="container mx-auto">

                    <!-- Create User Form -->
                    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                        <h2 class="text-xl font-semibold mb-4">Add New Account</h2>
                        <form action="" method="POST" class="space-y-4">
                            <div>
                                <label for="add_email_field" class="block text-sm font-medium text-gray-700">Email:</label>
                                <input type="text" id="add_email_field" name="email_field" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter new email">
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/sidebar.js" defer></script>
    <script src="js/charts.js" defer></script>

    <script>
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const globalMessageContainer = document.getElementById('global-message');
        const globalMessageContent = globalMessageContainer.querySelector('div'); // *** Add this line ***
        const globalMessageText = document.getElementById('global-message-text');
        
        // PHP-injected message from server-side
        const serverMessage = "<?php echo !empty($message) ? htmlspecialchars($message) : ''; ?>";

        if (serverMessage) {
            globalMessageText.textContent = serverMessage;

            // Clear previous background classes from the INNER div
            globalMessageContent.classList.remove('bg-red-500', 'bg-green-500', 'bg-gray-800'); // *** Modify this line ***

            if (serverMessage.includes('Error')) {
                globalMessageContent.classList.add('bg-red-500'); // *** Target inner div ***
            } else {
                globalMessageContent.classList.add('bg-green-500'); // *** Target inner div ***
            }
            globalMessageContent.classList.add('text-white'); // *** Target inner div ***

            // Ensure pointer-events is handled for proper interactivity
            globalMessageContainer.style.pointerEvents = 'none'; // Initially non-interactive

            // Show message with transition
            globalMessageContainer.classList.remove('hidden');
            void globalMessageContainer.offsetWidth; // Force reflow
            globalMessageContainer.classList.remove('opacity-0', 'scale-95');
            globalMessageContainer.classList.add('opacity-100', 'scale-100');
            globalMessageContainer.style.pointerEvents = 'auto'; // Make interactive

            // Automatically hide after a delay
            setTimeout(() => {
                globalMessageContainer.classList.remove('opacity-100', 'scale-100');
                globalMessageContainer.classList.add('opacity-0', 'scale-95');
                globalMessageContainer.style.pointerEvents = 'none'; // Make non-interactive again
                setTimeout(() => {
                    globalMessageContainer.classList.add('hidden');
                }, 300); // Wait for fade out
            }, 3000); // 3 seconds delay
        }

        // Clear the message query parameter from the URL without reloading
        if (window.history.replaceState) {
            const url = new URL(window.location);
            if (url.searchParams.has('message')) {
                url.searchParams.delete('message');
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }
    });
</script>
</body>
</html>