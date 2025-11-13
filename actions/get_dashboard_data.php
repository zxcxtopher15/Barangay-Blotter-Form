<?php
session_start();
// Check if the user is logged in, if not then redirect to login page
// if (isset($_SESSION['google_loggedin']) && $_SESSION['user_role'] === 'desk officer' || $_SESSION['user_role'] === 'admin') {

//     } 
//     // If not logged in or any other role not explicitly handled, redirect to index.php
//     else {
//         header('Location: index.php');
//         exit;
//     }

if (!isset($_SESSION['google_loggedin'])) {
    header('Location: index.php');
    exit;
}

// Retrieve session variables
$google_loggedin = $_SESSION['google_loggedin'];
$google_email = $_SESSION['google_email'];
$google_name = $_SESSION['google_name'];
$google_picture = $_SESSION['google_picture'];

header('Content-Type: application/json');

$response = [
    'lineChart' => ['labels' => [], 'data' => []],
    'doughnutChart' => ['labels' => [], 'data' => []],
    'topIncidents' => [],
    'error' => null
];

// --- Database Connection ---
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // --- Get Parameters from URL ---
    $period = $_GET['period'] ?? 'monthly';
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

    $line_sql = '';
    $top_incidents_sql = '';
    $params = [];
    $param_types = '';

    switch ($period) {
        case 'yearly':
            // Line Chart: Show incident counts for each month of the selected year
            $line_sql = "SELECT MONTH(incident_datetime) as month_num, COUNT(*) as count 
                         FROM complaints 
                         WHERE YEAR(incident_datetime) = ?
                         GROUP BY MONTH(incident_datetime) 
                         ORDER BY MONTH(incident_datetime);";
            $param_types = "i";
            $params = [$year];

            // Top Incidents for the whole selected year
            $top_incidents_sql = "SELECT
                                    CASE
                                        WHEN complaint_description = 'Others' THEN other_complaint
                                        ELSE complaint_description
                                    END as name,
                                    COUNT(*) as count
                                  FROM complaints
                                  WHERE YEAR(incident_datetime) = ?
                                  GROUP BY name
                                  ORDER BY count DESC;";
            break;

        case 'monthly':
        default:
             // Line Chart: Show incident counts for each day of the selected month and year
            $line_sql = "SELECT DAY(incident_datetime) as label, COUNT(*) as count
                         FROM complaints
                         WHERE YEAR(incident_datetime) = ? AND MONTH(incident_datetime) = ?
                         GROUP BY DAY(incident_datetime)
                         ORDER BY DAY(incident_datetime);";
            $param_types = "ii";
            $params = [$year, $month];

             // Top Incidents for the selected month and year
            $top_incidents_sql = "SELECT
                                    CASE
                                        WHEN complaint_description = 'Others' THEN other_complaint
                                        ELSE complaint_description
                                    END as name,
                                    COUNT(*) as count
                                  FROM complaints
                                  WHERE YEAR(incident_datetime) = ? AND MONTH(incident_datetime) = ?
                                  GROUP BY name
                                  ORDER BY count DESC;";
            break;
    }

    // --- Execute Line Chart Query using Prepared Statements ---
    $stmt = $conn->prepare($line_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        if ($period === 'yearly') {
            // Create a placeholder array for all 12 months
            $monthly_counts = array_fill(1, 12, 0);
            while ($row = $result->fetch_assoc()) {
                $monthly_counts[(int)$row['month_num']] = (int)$row['count'];
            }
            $response['lineChart']['labels'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $response['lineChart']['data'] = array_values($monthly_counts);
        } else { // Monthly
            while ($row = $result->fetch_assoc()) {
                $response['lineChart']['labels'][] = $row['label'];
                $response['lineChart']['data'][] = (int)$row['count'];
            }
        }
    } else {
        throw new Exception("Line chart query failed: " . $conn->error);
    }
    $stmt->close();

    // --- Execute Top Incidents Query using Prepared Statements ---
    $total_incidents_in_period = 0;
    $top_incidents_data = [];
    $max_char_length = 100; // Define the maximum character length

    $stmt = $conn->prepare($top_incidents_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while($row = $result->fetch_assoc()) {
            $incident_name = htmlspecialchars($row['name']);
            if (strlen($incident_name) > $max_char_length) {
                $incident_name = substr($incident_name, 0, $max_char_length) . '...';
            }

            $incident_data = [
                'name' => $incident_name,
                'count' => (int)$row['count']
            ];
            
            $top_incidents_data[] = $incident_data;
            $total_incidents_in_period += $incident_data['count'];
        }
        
        foreach ($top_incidents_data as &$incident) {
            $incident['total'] = $total_incidents_in_period;
        }
        unset($incident); 
        $response['topIncidents'] = $top_incidents_data;

        // Prepare Doughnut Chart Data (Top 5 + "Others")
        $doughnut_items = array_slice($top_incidents_data, 0, 5);
        $other_incidents = array_slice($top_incidents_data, 5);
        $other_count = array_reduce($other_incidents, fn($sum, $item) => $sum + $item['count'], 0);

        foreach ($doughnut_items as $item) {
            // Apply trimming to doughnut chart labels as well
            $doughnut_label = $item['name'];
            if (strlen($doughnut_label) > $max_char_length && $doughnut_label !== 'Others') {
                $doughnut_label = substr($doughnut_label, 0, $max_char_length) . '...';
            }
            $response['doughnutChart']['labels'][] = $doughnut_label;
            $response['doughnutChart']['data'][] = $item['count'];
        }

        if ($other_count > 0) {
            $response['doughnutChart']['labels'][] = 'Others';
            $response['doughnutChart']['data'][] = $other_count;
        }
    } else {
         throw new Exception("Top incidents query failed: " . $conn->error);
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(500); // Set response code to indicate a server error
}

echo json_encode($response);