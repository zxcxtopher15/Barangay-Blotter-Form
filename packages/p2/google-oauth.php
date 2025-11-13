<?php
// Initialize the session
session_start();

// Google OAuth Configuration (Place these at the top of your file or in a config file)
$google_oauth_client_id = '680691446532-5i5855fnioaje5kujjv57ikj121crfot.apps.googleusercontent.com';
$google_oauth_client_secret = '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';
$google_oauth_redirect_uri = 'https://barangay-blotter-form.penxel.ph/google-oauth.php';
//$google_oauth_redirect_uri = 'http://127.0.0.1:8080/p2/google-oauth.php';
$google_oauth_version = 'v3';


// Check if user is already logged in
if (isset($_SESSION['google_loggedin']) && $_SESSION['google_loggedin'] === TRUE) {
    // User is already logged in, redirect based on their stored role
    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: dashboardadmin.php');
        } else if ($_SESSION['user_role'] === 'desk officer') {
            header('Location: dashboard.php');
        } else {
            // Default redirect for other roles or if role is not recognized
            header('Location: dashboard.php'); // Or a generic user dashboard
        }
    } else {
        // If logged in but role isn't set (shouldn't happen with this code, but as a fallback)
        header('Location: dashboard.php');
    }
    exit;
}

// Database connection (example using PDO)
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1"; // Make sure this database exists and has your 'users' table

try {
    $pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // For security against SQL injection
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

// 4. The main Google OAuth logic
// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => $google_oauth_client_id,
        'client_secret' => $google_oauth_client_secret,
        'redirect_uri' => $google_oauth_redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);

    // Make sure access token is valid
    if (isset($response['access_token']) && !empty($response['access_token'])) {
        // Store the access token and refresh token for future use
        $_SESSION['google_access_token'] = $response['access_token'];
        if (isset($response['refresh_token'])) {
            $_SESSION['google_refresh_token'] = $response['refresh_token'];
        }

        // Execute cURL request to retrieve the user info associated with the Google account
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/' . $google_oauth_version . '/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        $response = curl_exec($ch);
        curl_close($ch);
        $profile = json_decode($response, true);

        // Make sure the profile data exists
        if (isset($profile['email'])) {
            $user_email = strtolower($profile['email']);
            $user_role = null;

            // Query the database for the user's role
            $stmt = $pdo->prepare("SELECT role FROM accounts WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $user_email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $user_role = strtolower($result['role']);
            }

            // Define allowed roles for access
            $allowed_roles = ['admin', 'desk officer'];

            // Check if the user's email was found and their role is one of the allowed roles
            if (!$user_role || !in_array($user_role, $allowed_roles)) {
                exit('Access denied! Your email address (' . htmlspecialchars($profile['email']) . ') is not authorized or does not have the required role to access this application.');
            }

            $google_name_parts = [];
            $google_name_parts[] = isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
            $google_name_parts[] = isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';

            // Authenticate the user and set session variables
            session_regenerate_id();
            $_SESSION['google_loggedin'] = TRUE;
            $_SESSION['google_email'] = $profile['email'];
            $_SESSION['google_name'] = implode(' ', $google_name_parts);
            $_SESSION['google_picture'] = isset($profile['picture']) ? $profile['picture'] : '';
            $_SESSION['login_time'] = time(); // Store login timestamp
            $_SESSION['user_role'] = $user_role; // Store the user's role in the session

            // Set a longer session lifetime (optional - 30 days)
            ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
            session_set_cookie_params(30 * 24 * 60 * 60);

            // Redirect based on the user's role
            if ($user_role === 'admin') {
                header('Location: dashboardadmin.php');
            } else if ($user_role === 'desk officer') {
                header('Location: dashboard.php');
            } else {
                // Fallback for other roles not explicitly handled
                header('Location: dashboard.php'); // Or a generic user dashboard
            }
            exit;

        } else {
            exit('Could not retrieve profile information! Please try again later!');
        }
    } else {
        exit('Invalid access token! Please try again later!');
    }
} else {
    // Define params and redirect to Google Authentication page
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    header('Location: https://accounts.google.com/o/oauth2/auth?' . http_build_query($params));
    exit;
}

?>