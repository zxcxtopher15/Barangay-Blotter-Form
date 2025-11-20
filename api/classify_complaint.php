<?php
/**
 * ML-based Complaint Classification API
 * Uses OpenRouter AI API to classify complaints as requiring PNP endorsement or Barangay action
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['complaint_description']) || !isset($input['complaint_statement'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: complaint_description and complaint_statement']);
    exit;
}

$complaint_description = $input['complaint_description'];
$complaint_statement = $input['complaint_statement'];
$incident_location = $input['incident_location'] ?? '';

// OpenRouter API Configuration
$api_key = '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';
$model = 'nvidia/nemotron-nano-12b-v2-vl:free';

// Construct the classification prompt
$prompt = "You are an AI assistant helping a Barangay (village) office in the Philippines classify incident reports.

Your task is to analyze the following complaint and determine whether it should be:
1. **ENDORSE_TO_PNP** - Serious crimes that must be referred to the Philippine National Police
2. **BARANGAY_ACTION** - Minor disputes/issues that can be handled at the Barangay level

Serious crimes that MUST be endorsed to PNP include:
- Murder, Homicide, Rape
- Robbery, Theft, Carnapping, Arson
- Kidnapping, Physical Assault (with serious injuries)
- Drug-related offenses, Illegal Gambling
- Illegal Possession of Firearms
- Violation of Special Laws
- Any crime involving weapons, violence, or significant harm

Barangay-level issues include:
- Physical Injuries (minor)
- Noise Complaints, Vandalism
- Domestic disputes (non-violent)
- Trespassing, Boundary/Property Disputes
- Neighborhood conflicts

**Complaint Type:** $complaint_description
**Complaint Statement:** $complaint_statement
**Location:** $incident_location

Analyze the severity, nature, and legal implications of this complaint. Respond in JSON format with:
{
  \"classification\": \"ENDORSE_TO_PNP\" or \"BARANGAY_ACTION\",
  \"confidence\": 0.0 to 1.0,
  \"reasoning\": \"Brief explanation of why this classification was chosen\",
  \"recommended_action\": \"Specific recommended next steps\"
}

Be strict in your classification - when in doubt about severity, prefer ENDORSE_TO_PNP to ensure proper legal handling.";

// Prepare API request
$data = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'reasoning' => ['enabled' => true],
    'temperature' => 0.3, // Lower temperature for more consistent classification
    'response_format' => ['type' => 'json_object']
];

// Make API request to OpenRouter
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
    'HTTP-Referer: https://barangay-blotter-form.penxel.ph',
    'X-Title: Barangay Blotter Classification System'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API request failed',
        'details' => $curl_error
    ]);
    exit;
}

if ($http_code !== 200) {
    http_response_code($http_code);
    echo json_encode([
        'error' => 'API returned error',
        'status_code' => $http_code,
        'response' => $response
    ]);
    exit;
}

$result = json_decode($response, true);

if (!isset($result['choices'][0]['message']['content'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Invalid API response',
        'response' => $result
    ]);
    exit;
}

// Parse the AI response
$ai_response = json_decode($result['choices'][0]['message']['content'], true);

if (!$ai_response || !isset($ai_response['classification'])) {
    // Fallback classification based on complaint type
    $pnp_keywords = ['murder', 'homicide', 'rape', 'robbery', 'theft', 'carnapping',
                     'arson', 'kidnapping', 'drug', 'firearm', 'assault', 'weapon'];

    $requires_pnp = false;
    $description_lower = strtolower($complaint_description . ' ' . $complaint_statement);

    foreach ($pnp_keywords as $keyword) {
        if (strpos($description_lower, $keyword) !== false) {
            $requires_pnp = true;
            break;
        }
    }

    $ai_response = [
        'classification' => $requires_pnp ? 'ENDORSE_TO_PNP' : 'BARANGAY_ACTION',
        'confidence' => 0.7,
        'reasoning' => 'Fallback keyword-based classification',
        'recommended_action' => $requires_pnp
            ? 'Forward this case to the PNP for investigation'
            : 'Handle through Barangay mediation and settlement procedures'
    ];
}

// Log the classification for dataset building
try {
    $db_server = "localhost";
    $db_user = "u416486854_p1";
    $db_pass = "2&rnLACGCldK";
    $db_name = "u416486854_p1";

    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        // Don't fail the request if logging fails
        error_log('Database connection failed for ML logging: ' . $conn->connect_error);
    } else {
        // Get case_no if provided
        $case_no = isset($input['case_no']) ? intval($input['case_no']) : null;

        $stmt = $conn->prepare("INSERT INTO ml_classifications (case_no, complaint_description, complaint_statement, classification, confidence, reasoning, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('isssds',
            $case_no,
            $complaint_description,
            $complaint_statement,
            $ai_response['classification'],
            $ai_response['confidence'],
            $ai_response['reasoning']
        );
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    // Silently fail logging - don't interrupt classification
    error_log('ML classification logging error: ' . $e->getMessage());
}

// Return the classification result
http_response_code(200);
echo json_encode([
    'success' => true,
    'classification' => $ai_response['classification'],
    'confidence' => $ai_response['confidence'],
    'reasoning' => $ai_response['reasoning'],
    'recommended_action' => $ai_response['recommended_action'],
    'model_used' => $model,
    'reasoning_details' => $result['choices'][0]['message']['reasoning_details'] ?? null
]);
