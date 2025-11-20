<?php
session_start();

// Database connection
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$case_no = $input['case_no'] ?? '';
$is_archive_mode = $input['is_archive_mode'] ?? false;

if (empty($case_no)) {
    echo json_encode(['success' => false, 'message' => 'Case number is required']);
    exit;
}

// Determine table
$table = $is_archive_mode ? 'reports_archive' : 'complaints';

// Fetch complaint details
$query = "SELECT * FROM $table WHERE case_no = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $case_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$complaint = mysqli_fetch_assoc($result);

if (!$complaint) {
    echo json_encode(['success' => false, 'message' => 'Complaint not found']);
    exit;
}

// Load PHPMailer
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Update with your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'uniquequeen358@gmail.com'; // Update with your Gmail
    $mail->Password = 'bxos sesq uzcb crjs'; // Update with your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('uniquequeen358@gmail.com', 'Barangay San Miguel Blotter System');
    $mail->addAddress('epd.pio@gmail.com', 'Eastern Police District');

    // Format case number
    $displayCaseNo = $complaint['case_no'];
    if ($complaint['complaint_no'] && $complaint['incident_datetime']) {
        $date = new DateTime($complaint['incident_datetime']);
        $displayCaseNo = sprintf('%s-%s',
            $complaint['complaint_no'],
            $date->format('Y-m-d-H-i-s')
        );
    }

    // Format datetime
    $incidentDate = new DateTime($complaint['incident_datetime']);
    $formattedDateTime = $incidentDate->format('F j, Y \a\t g:i A');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "URGENT: Crime Report Endorsement - Case No. $displayCaseNo";

    // Create beautiful HTML email template
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-center; border-radius: 10px 10px 0 0; }
            .header h1 { margin: 0; font-size: 24px; }
            .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
            .badge { background: #ef4444; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; margin-top: 10px; }
            .content { background: #fff; padding: 30px; border: 1px solid #e5e7eb; }
            .section { margin-bottom: 25px; }
            .section-title { font-size: 18px; font-weight: bold; color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
            .detail-row { margin-bottom: 12px; display: flex; }
            .detail-label { font-weight: bold; min-width: 150px; color: #4b5563; }
            .detail-value { color: #1f2937; flex: 1; }
            .case-number { background: #f3f4f6; padding: 15px; border-left: 4px solid #667eea; margin-bottom: 20px; border-radius: 5px; }
            .case-number strong { font-size: 18px; color: #667eea; }
            .statement-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 5px; }
            .contact-info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .footer { background: #1f2937; color: white; padding: 20px; text-center; border-radius: 0 0 10px 10px; font-size: 12px; }
            .footer a { color: #60a5fa; text-decoration: none; }
            .map-link { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
            .map-link:hover { background: #2563eb; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🚨 CRIME REPORT ENDORSEMENT</h1>
                <p>Barangay San Miguel Blotter System</p>
                <span class='badge'>REQUIRES PNP ACTION</span>
            </div>

            <div class='content'>
                <div class='case-number'>
                    <strong>Case No:</strong> $displayCaseNo<br>
                    <strong>Incident Date:</strong> $formattedDateTime
                </div>

                <div class='section'>
                    <div class='section-title'>📋 Incident Details</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Type of Incident:</span>
                        <span class='detail-value'><strong>" . htmlspecialchars($complaint['complaint_description']) . "</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Location:</span>
                        <span class='detail-value'>" . htmlspecialchars($complaint['incident_location'] ?? 'N/A') . "</span>
                    </div>";

    // Add map link if coordinates available
    if ($complaint['incident_latitude'] && $complaint['incident_longitude']) {
        $mapUrl = "https://www.google.com/maps?q={$complaint['incident_latitude']},{$complaint['incident_longitude']}";
        $emailBody .= "<div class='detail-row'>
            <span class='detail-label'>Coordinates:</span>
            <span class='detail-value'>
                Lat: {$complaint['incident_latitude']}, Lng: {$complaint['incident_longitude']}
                <br><a href='$mapUrl' class='map-link' target='_blank'>📍 View on Map</a>
            </span>
        </div>";
    }

    $emailBody .= "</div>

                <div class='section'>
                    <div class='section-title'>👤 Complainant Information</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Name:</span>
                        <span class='detail-value'>" . htmlspecialchars(trim($complaint['complainant_first_name'] . ' ' . $complaint['complainant_middle_name'] . ' ' . $complaint['complainant_last_name'])) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Age/Gender:</span>
                        <span class='detail-value'>" . ($complaint['complainant_age'] ?? 'N/A') . " / " . ($complaint['complainant_gender'] ?? 'N/A') . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Contact:</span>
                        <span class='detail-value'>" . htmlspecialchars($complaint['complainant_phone'] ?? 'N/A') . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Address:</span>
                        <span class='detail-value'>" . htmlspecialchars($complaint['complainant_address'] ?? 'N/A') . "</span>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>🎯 Victim Information</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Name:</span>
                        <span class='detail-value'>" . htmlspecialchars(trim($complaint['victim_first_name'] . ' ' . $complaint['victim_middle_name'] . ' ' . $complaint['victim_last_name'])) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Age/Gender:</span>
                        <span class='detail-value'>" . ($complaint['victim_age'] ?? 'N/A') . " / " . ($complaint['victim_gender'] ?? 'N/A') . "</span>
                    </div>
                </div>";

    // Add respondent info if available
    if (!empty($complaint['respondent_first_name'])) {
        $emailBody .= "
                <div class='section'>
                    <div class='section-title'>⚠️ Respondent Information</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Name:</span>
                        <span class='detail-value'>" . htmlspecialchars(trim($complaint['respondent_first_name'] . ' ' . $complaint['respondent_middle_name'] . ' ' . $complaint['respondent_last_name'])) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Age/Gender:</span>
                        <span class='detail-value'>" . ($complaint['respondent_age'] ?? 'N/A') . " / " . ($complaint['respondent_gender'] ?? 'N/A') . "</span>
                    </div>
                </div>";
    }

    // Statement
    if (!empty($complaint['complaint_statement'])) {
        $emailBody .= "
                <div class='section'>
                    <div class='section-title'>📝 Statement</div>
                    <div class='statement-box'>
                        " . nl2br(htmlspecialchars($complaint['complaint_statement'])) . "
                    </div>
                </div>";
    }

    $emailBody .= "
                <div class='contact-info'>
                    <strong>📞 For Coordination:</strong><br>
                    Barangay San Miguel, Pasig City<br>
                    Desk Officer: " . htmlspecialchars($complaint['desk_officer_name'] ?? 'N/A') . "<br>
                    Report Received: " . (new DateTime($complaint['received_datetime'] ?? 'now'))->format('F j, Y \a\t g:i A') . "
                </div>
            </div>

            <div class='footer'>
                <p><strong>Barangay San Miguel Blotter System</strong></p>
                <p>This is an automated endorsement from the Barangay Blotter System.</p>
                <p>For immediate concerns, please contact the barangay office directly.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags($emailBody);

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Email sent successfully to PNP']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"]);
}

mysqli_close($conn);
?>
