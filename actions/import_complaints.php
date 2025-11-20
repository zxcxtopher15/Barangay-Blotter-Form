<?php
session_start();

// Set headers for streaming
header('Content-Type: application/json');
header('X-Accel-Buffering: no'); // Disable nginx buffering
if (ob_get_level()) {
    ob_end_flush();
}
ob_implicit_flush(true);

// Database connection
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['type' => 'error', 'message' => 'Database connection failed']) . "\n";
    exit;
}

// Check if user is admin
if (!isset($_SESSION['google_loggedin']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['type' => 'error', 'message' => 'Unauthorized access']) . "\n";
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['type' => 'error', 'message' => 'No file uploaded or upload error']) . "\n";
    exit;
}

$csvFile = $_FILES['import_file']['tmp_name'];

// Read CSV file with proper encoding handling
$csvData = [];
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        $csvData[] = $data;
    }
    fclose($handle);
}

$headers = array_shift($csvData); // Remove header row

$total = count($csvData);
$success = 0;
$failed = 0;
$current = 0;

$desk_officer_name = $_SESSION['google_name'];

// Send initial status
echo json_encode([
    'type' => 'info',
    'message' => "Starting import of $total rows..."
]) . "\n";
flush();

foreach ($csvData as $row) {
    $current++;

    // Skip empty rows
    if (count(array_filter($row)) === 0) {
        continue;
    }

    try {
        // Parse CSV row with new format
        $data = array_combine($headers, $row);

        // Extract data from CSV
        $incident_date = trim($data['DATE'] ?? '');
        $case_no = trim($data['CASE NO.'] ?? '');
        $case_title = trim($data['CASE TITLE'] ?? '');
        $complainant_last = trim($data['COMPLAINANT LAST NAME'] ?? '');
        $complainant_first = trim($data['COMPLAINANT GIVEN NAME'] ?? '');
        $complainant_middle = trim($data['COMPLAINANT MIDDLE NAME'] ?? '');
        $complainant_address = trim($data['COMPLAINANT ADDRESS'] ?? '');
        $complainant_phone = trim($data['COMPLAINANT CONTACT NO.'] ?? '');
        $respondent_last = trim($data['RESPONDENT LAST NAME'] ?? '');
        $respondent_first = trim($data['RESPONDENT GIVEN NAME'] ?? '');
        $respondent_middle = trim($data['RESPONDENT MIDDLE NAME'] ?? '');
        $respondent_address = trim($data['RESPONDENT ADDRESS'] ?? '');
        $respondent_phone = trim($data['RESPONDENT CONTACT NO.'] ?? '');

        // Validate required fields
        if (empty($case_title) || empty($incident_date) || empty($complainant_first) || empty($complainant_last)) {
            echo json_encode([
                'type' => 'error',
                'message' => "Row $current: Missing required fields (DATE, CASE TITLE, or COMPLAINANT NAME)"
            ]) . "\n";
            flush();
            $failed++;
            continue;
        }

        // Send progress update
        echo json_encode([
            'type' => 'progress',
            'current' => $current,
            'total' => $total,
            'message' => "Processing row $current/$total: $case_title"
        ]) . "\n";
        flush();

        // Create full names for AI generation
        $complainant_full = trim("$complainant_first $complainant_middle $complainant_last");
        $respondent_full = trim("$respondent_first $respondent_middle $respondent_last");

        // Default location to complainant address if not specified
        $location = !empty($complainant_address) && $complainant_address !== 'N/A'
            ? $complainant_address
            : 'San Miguel, Pasig City';

        // For now, skip AI and use fallback to test if import works
        $salaysay = "Ito ay reklamo tungkol sa: $case_title. Ang nag-reklamo ay si $complainant_full laban kay $respondent_full. Nangyari ito sa $location.";
        $complaint_description = 'Physical Injuries';
        $pnp_recommendation = 'BARANGAY_ACTION';

        /* AI generation disabled temporarily for testing
        // Generate AI statement (salaysay) based on case title
        try {
            $salaysay = generateSalaysay($case_title, $complainant_full, $complainant_full, $respondent_full, $location);
        } catch (Exception $e) {
            // Fallback if AI fails
            $salaysay = "Ito ay reklamo tungkol sa: $case_title. Ang nag-reklamo ay si $complainant_full laban kay $respondent_full. Nangyari ito sa $location.";
        }

        // Use AI to classify crime and get recommendation
        try {
            $classificationResult = classifyCrime($salaysay);
            $parts = explode('|', $classificationResult);
            $complaint_description = trim($parts[0] ?? 'Physical Injuries');
            $pnp_recommendation = trim($parts[1] ?? 'BARANGAY_ACTION');
        } catch (Exception $e) {
            // Fallback classification
            $complaint_description = 'Physical Injuries';
            $pnp_recommendation = 'BARANGAY_ACTION';
        }
        */

        // Generate realistic test data for missing fields
        $genders = ['Male', 'Female'];
        $complainant_gender = $genders[array_rand($genders)];
        $respondent_gender = $genders[array_rand($genders)];

        // Generate realistic ages (18-65)
        $complainant_age = rand(18, 65);
        $victim_age = rand(18, 65);
        $respondent_age = rand(18, 65);

        // Generate DOB from age
        $current_year = date('Y');
        $complainant_dob = ($current_year - $complainant_age) . '-' . str_pad(rand(1,12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1,28), 2, '0', STR_PAD_LEFT);
        $victim_dob = ($current_year - $victim_age) . '-' . str_pad(rand(1,12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1,28), 2, '0', STR_PAD_LEFT);
        $respondent_dob = ($current_year - $respondent_age) . '-' . str_pad(rand(1,12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1,28), 2, '0', STR_PAD_LEFT);

        // Create incident datetime (default time 12:00:00 if not specified)
        $incident_datetime = $incident_date . ' 12:00:00';

        // Use complainant as victim (same person for testing)
        $victim_first = $complainant_first;
        $victim_middle = $complainant_middle;
        $victim_last = $complainant_last;
        $victim_address = $complainant_address;
        $victim_phone = $complainant_phone;

        // Insert into database with all fields
        echo json_encode([
            'type' => 'info',
            'message' => "Preparing database insert..."
        ]) . "\n";
        flush();

        $stmt = $conn->prepare("INSERT INTO complaints (
            incident_datetime, complaint_description, pnp_recommendation, incident_location,
            complainant_first_name, complainant_middle_name, complainant_last_name,
            complainant_dob, complainant_age, complainant_gender, complainant_phone, complainant_address,
            victim_first_name, victim_middle_name, victim_last_name,
            victim_dob, victim_age, victim_gender, victim_phone, victim_address,
            respondent_first_name, respondent_middle_name, respondent_last_name,
            respondent_dob, respondent_age, respondent_gender, respondent_phone, respondent_address,
            complaint_statement, reported_by, is_affirmed, desk_officer_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?)");

        if (!$stmt) {
            echo json_encode([
                'type' => 'error',
                'message' => "Row $current: Prepare failed - " . $conn->error
            ]) . "\n";
            flush();
            $failed++;
            continue;
        }

        echo json_encode([
            'type' => 'info',
            'message' => "Binding parameters..."
        ]) . "\n";
        flush();

        $stmt->bind_param(
            "ssssssssisssssisssisssissss",
            $incident_datetime,
            $complaint_description,
            $pnp_recommendation,
            $location,
            $complainant_first,
            $complainant_middle,
            $complainant_last,
            $complainant_dob,
            $complainant_age,
            $complainant_gender,
            $complainant_phone,
            $complainant_address,
            $victim_first,
            $victim_middle,
            $victim_last,
            $victim_dob,
            $victim_age,
            $complainant_gender, // Same gender as complainant
            $victim_phone,
            $victim_address,
            $respondent_first,
            $respondent_middle,
            $respondent_last,
            $respondent_dob,
            $respondent_age,
            $respondent_gender,
            $respondent_phone,
            $respondent_address,
            $salaysay,
            $desk_officer_name
        );

        echo json_encode([
            'type' => 'info',
            'message' => "Executing insert..."
        ]) . "\n";
        flush();

        if ($stmt->execute()) {
            $success++;
            echo json_encode([
                'type' => 'success',
                'message' => "Row $current: Successfully imported - $case_title ($complaint_description)"
            ]) . "\n";
            flush();
        } else {
            $failed++;
            echo json_encode([
                'type' => 'error',
                'message' => "Row $current: Database error - " . $stmt->error
            ]) . "\n";
            flush();
        }

        $stmt->close();

    } catch (Exception $e) {
        $failed++;
        echo json_encode([
            'type' => 'error',
            'message' => "Row $current: " . $e->getMessage()
        ]) . "\n";
        flush();
    }
}

// Send completion summary
echo json_encode([
    'type' => 'complete',
    'total' => $total,
    'success' => $success,
    'failed' => $failed
]) . "\n";
flush();

mysqli_close($conn);

// Helper function to generate salaysay using AI
function generateSalaysay($case_title, $complainant, $victim, $respondent, $location) {
    $groq_api_key = "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";

    $prompt = "You are a Filipino police report writer. Based on this case title, write a detailed salaysay (statement) in Tagalog.\n\n";
    $prompt .= "Case Title: $case_title\n";
    $prompt .= "Complainant: $complainant\n";
    $prompt .= "Victim: $victim\n";
    $prompt .= "Respondent: $respondent\n";
    $prompt .= "Location: $location\n\n";
    $prompt .= "Write a detailed 3-5 sentence statement in Tagalog describing what happened. Make it realistic and professional. DO NOT include any labels or prefixes, just the statement itself.";

    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a professional police report writer in the Philippines. Write detailed, realistic statements in Tagalog based on case information. Keep it concise (3-5 sentences) and professional.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 200
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 second connection timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $groq_api_key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("cURL Error: $curlError");
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }
    }

    // Throw exception if AI fails so we can use fallback
    throw new Exception("AI API returned HTTP $httpCode");
}

// Helper function to classify crime and get recommendation
function classifyCrime($statement) {
    $groq_api_key = "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";

    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an expert crime classifier and legal advisor for Philippine Barangay incidents. Analyze the complaint and provide TWO things:

1. CRIME TYPE: Identify the primary crime
2. RECOMMENDATION: Determine if it needs PNP endorsement or Barangay action

CLASSIFICATION RULES:
1. THEFT = Taking property WITHOUT force/threat (nakaw, magnanakaw)
2. ROBBERY = Taking property WITH force/weapons (holdap, armadong pagnanakaw)
3. KIDNAPPING = Illegally taking/transporting person to another location (dinukot, dinala)
4. HOSTAGE TAKING = Holding person by force/threat at scene (tinutukan, hostage)
5. PHYSICAL ASSAULT = Attacking WITH intent to harm (suntok, sipa, bugbog)
6. PHYSICAL INJURIES = Harm from accident or minor fight (sugat, nabundol)
7. MURDER = Intentional killing with planning (pinatay, pinaslang)
8. HOMICIDE = Killing without premeditation (aksidenteng namatay)
9. DOMESTIC VIOLENCE = Violence within family (asawa, anak, magulang)

PNP ENDORSEMENT CRITERIA:
- Serious crimes: Murder, Homicide, Rape, Robbery (with weapon), Kidnapping, Hostage Taking, Carnapping, Arson, Drug-related, Illegal Firearms, Violation of Special Laws
- High-value theft (>50,000 pesos or valuable items)
- Crimes with weapons involved
- Organized crime activities
- Crimes requiring forensic investigation

BARANGAY ACTION CRITERIA:
- Minor disputes between neighbors
- Small property damage
- Noise complaints
- Minor injuries without weapons
- Petty theft (<5,000 pesos)
- Trespassing without violence
- Boundary disputes

RESPONSE FORMAT (EXACT):
CRIME_TYPE|RECOMMENDATION

Examples:
"Ninakaw wallet ko sa jeep" → Theft|BARANGAY_ACTION
"Hinoldap ako, tinakot ng baril" → Robbery|PNP_ENDORSEMENT
"Biglang hinila at tinutukan ng kutsilyo habang sinasabi sa pulis na patakasin" → Hostage Taking|PNP_ENDORSEMENT
"Sinuntok ako ng kapitbahay dahil sa alitan" → Physical Assault|BARANGAY_ACTION
"Pinatay ng asawa ang misis" → Murder|PNP_ENDORSEMENT
"Nabundol ako ng bisikleta, nasugatan" → Physical Injuries|BARANGAY_ACTION
"Ninakaw ang kotse ko" → Carnapping|PNP_ENDORSEMENT
"Maingay ang kapitbahay every night" → Noise Complaints|BARANGAY_ACTION'
            ],
            [
                'role' => 'user',
                'content' => "Classify this complaint statement:\n\n$statement"
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 50
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 second connection timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $groq_api_key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("cURL Error: $curlError");
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }
    }

    // Throw exception if AI fails so we can use fallback
    throw new Exception("AI API returned HTTP $httpCode");
}
?>
