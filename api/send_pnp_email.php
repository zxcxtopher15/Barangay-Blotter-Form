<?php
/**
 * Send PNP Endorsement Email
 * Sends complaint details to Philippine National Police (Eastern Police District)
 * Uses PHPMailer for reliable email delivery
 */

session_start();

// Check if user is logged in and authorized
if (!isset($_SESSION['google_loggedin']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'desk officer')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['case_no'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing case_no']);
    exit;
}

$case_no = intval($input['case_no']);

// Database connection
$db_server = "localhost";
$db_user = "u416486854_p1";
$db_pass = "2&rnLACGCldK";
$db_name = "u416486854_p1";

try {
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Fetch complaint details
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE case_no = ?");
    $stmt->bind_param('i', $case_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Complaint not found']);
        exit;
    }

    $complaint = $result->fetch_assoc();
    $stmt->close();

    // Check if already endorsed
    if ($complaint['status'] === 'Endorsed to PNP') {
        echo json_encode([
            'success' => false,
            'message' => 'This complaint has already been endorsed to PNP',
            'endorsed_date' => $complaint['endorsed_date']
        ]);
        exit;
    }

    // Load email configuration
    $email_config_path = __DIR__ . '/../config/email.config.php';
    if (!file_exists($email_config_path)) {
        throw new Exception('Email configuration file not found. Please copy email.config.example.php to email.config.php and configure it.');
    }
    $email_config = require $email_config_path;

    // PNP Email Configuration
    $pnp_email = $email_config['pnp_email'];
    $pnp_name = $email_config['pnp_name'];
    $from_email = $email_config['from_email'];
    $from_name = $email_config['from_name'];

    // Prepare email content
    $subject = "Blotter Report Endorsement - Case #" . $case_no . " - " . $complaint['complaint_description'];
    $email_body = generateEmailBody($complaint);

    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    $mail_sent = false;
    $error_msg = '';

    try {
        // Server settings
        if ($email_config['enable_debug']) {
            $mail->SMTPDebug = $email_config['debug_level'];
            $mail->Debugoutput = function($str, $level) {
                error_log("SMTP Debug level $level: $str");
            };
        }

        $mail->isSMTP();
        $mail->Host       = $email_config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_config['smtp_username'];
        $mail->Password   = $email_config['smtp_password'];
        $mail->SMTPSecure = $email_config['smtp_secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $email_config['smtp_port'];

        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($pnp_email, $pnp_name);
        $mail->addReplyTo($from_email, $from_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $email_body;
        $mail->AltBody = strip_tags($email_body); // Plain text alternative

        // Send email
        $mail->send();
        $mail_sent = true;

    } catch (Exception $e) {
        $mail_sent = false;
        $error_msg = "PHPMailer Error: {$mail->ErrorInfo}";
        error_log($error_msg);
    }

    if ($mail_sent) {
        // Update complaint status
        $update_stmt = $conn->prepare("UPDATE complaints SET status = 'Endorsed to PNP', endorsed_date = NOW(), endorsed_by = ? WHERE case_no = ?");
        $endorsed_by = $_SESSION['google_name'];
        $update_stmt->bind_param('si', $endorsed_by, $case_no);
        $update_stmt->execute();
        $update_stmt->close();

        // Log email
        $log_stmt = $conn->prepare("INSERT INTO email_logs (case_no, recipient_email, subject, status, sent_by) VALUES (?, ?, ?, 'sent', ?)");
        $log_stmt->bind_param('isss', $case_no, $pnp_email, $subject, $endorsed_by);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Email successfully sent to PNP',
            'recipient' => $pnp_email,
            'case_no' => $case_no
        ]);
    } else {
        // Log failed email
        $error_msg = error_get_last()['message'] ?? 'Unknown error';
        $log_stmt = $conn->prepare("INSERT INTO email_logs (case_no, recipient_email, subject, status, sent_by, error_message) VALUES (?, ?, ?, 'failed', ?, ?)");
        $endorsed_by = $_SESSION['google_name'];
        $log_stmt->bind_param('issss', $case_no, $pnp_email, $subject, $endorsed_by, $error_msg);
        $log_stmt->execute();
        $log_stmt->close();

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send email',
            'details' => $error_msg
        ]);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Generate HTML email body for PNP endorsement
 */
function generateEmailBody($complaint) {
    $incident_datetime = date('F d, Y h:i A', strtotime($complaint['incident_datetime']));
    $received_datetime = date('F d, Y h:i A', strtotime($complaint['received_datetime']));

    $complainant_name = trim($complaint['complainant_first_name'] . ' ' . $complaint['complainant_middle_name'] . ' ' . $complaint['complainant_last_name']);
    $victim_name = trim($complaint['victim_first_name'] . ' ' . $complaint['victim_middle_name'] . ' ' . $complaint['victim_last_name']);
    $respondent_name = trim($complaint['respondent_first_name'] . ' ' . $complaint['respondent_middle_name'] . ' ' . $complaint['respondent_last_name']);
    $witness_name = trim($complaint['witness_first_name'] . ' ' . $complaint['witness_middle_name'] . ' ' . $complaint['witness_last_name']);

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1e3a5f; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; margin-top: 20px; }
            .section { margin-bottom: 20px; }
            .section-title { font-weight: bold; color: #1e3a5f; font-size: 16px; margin-bottom: 10px; border-bottom: 2px solid #1e3a5f; padding-bottom: 5px; }
            .info-row { margin: 8px 0; }
            .label { font-weight: bold; display: inline-block; width: 180px; }
            .value { display: inline-block; }
            .alert { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>BARANGAY BLOTTER REPORT ENDORSEMENT</h1>
                <p>Barangay San Miguel, Pasig City, Metro Manila</p>
            </div>

            <div class="alert">
                <strong>⚠️ URGENT: PNP Action Required</strong><br>
                This blotter report has been classified as requiring Philippine National Police investigation and intervention.
            </div>

            <div class="content">
                <!-- Complaint Information -->
                <div class="section">
                    <div class="section-title">COMPLAINT INFORMATION</div>
                    <div class="info-row"><span class="label">Case Number:</span> <span class="value">#<?= $complaint['case_no'] ?></span></div>
                    <div class="info-row"><span class="label">Type of Complaint:</span> <span class="value"><?= htmlspecialchars($complaint['complaint_description']) ?></span></div>
                    <div class="info-row"><span class="label">Incident Date & Time:</span> <span class="value"><?= $incident_datetime ?></span></div>
                    <div class="info-row"><span class="label">Incident Location:</span> <span class="value"><?= htmlspecialchars($complaint['incident_location']) ?></span></div>
                    <?php if ($complaint['incident_latitude'] && $complaint['incident_longitude']): ?>
                    <div class="info-row"><span class="label">GPS Coordinates:</span> <span class="value"><?= $complaint['incident_latitude'] ?>, <?= $complaint['incident_longitude'] ?></span></div>
                    <?php endif; ?>
                    <div class="info-row"><span class="label">Date Reported:</span> <span class="value"><?= $received_datetime ?></span></div>
                    <div class="info-row"><span class="label">Desk Officer:</span> <span class="value"><?= htmlspecialchars($complaint['desk_officer_name']) ?></span></div>
                </div>

                <!-- Complainant Information -->
                <?php if ($complainant_name): ?>
                <div class="section">
                    <div class="section-title">COMPLAINANT INFORMATION</div>
                    <div class="info-row"><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($complainant_name) ?></span></div>
                    <div class="info-row"><span class="label">Age:</span> <span class="value"><?= $complaint['complainant_age'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Gender:</span> <span class="value"><?= $complaint['complainant_gender'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($complaint['complainant_phone']) ?></span></div>
                    <div class="info-row"><span class="label">Address:</span> <span class="value"><?= htmlspecialchars($complaint['complainant_address']) ?></span></div>
                </div>
                <?php endif; ?>

                <!-- Victim Information -->
                <div class="section">
                    <div class="section-title">VICTIM INFORMATION</div>
                    <div class="info-row"><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($victim_name) ?></span></div>
                    <div class="info-row"><span class="label">Age:</span> <span class="value"><?= $complaint['victim_age'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Gender:</span> <span class="value"><?= $complaint['victim_gender'] ?></span></div>
                    <div class="info-row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($complaint['victim_phone']) ?></span></div>
                    <div class="info-row"><span class="label">Address:</span> <span class="value"><?= htmlspecialchars($complaint['victim_address']) ?></span></div>
                </div>

                <!-- Respondent Information -->
                <?php if ($respondent_name): ?>
                <div class="section">
                    <div class="section-title">RESPONDENT INFORMATION</div>
                    <div class="info-row"><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($respondent_name) ?></span></div>
                    <div class="info-row"><span class="label">Age:</span> <span class="value"><?= $complaint['respondent_age'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Gender:</span> <span class="value"><?= $complaint['respondent_gender'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($complaint['respondent_phone']) ?></span></div>
                    <div class="info-row"><span class="label">Address:</span> <span class="value"><?= htmlspecialchars($complaint['respondent_address']) ?></span></div>
                </div>
                <?php endif; ?>

                <!-- Witness Information -->
                <?php if ($witness_name): ?>
                <div class="section">
                    <div class="section-title">WITNESS INFORMATION</div>
                    <div class="info-row"><span class="label">Name:</span> <span class="value"><?= htmlspecialchars($witness_name) ?></span></div>
                    <div class="info-row"><span class="label">Age:</span> <span class="label"><?= $complaint['witness_age'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Gender:</span> <span class="value"><?= $complaint['witness_gender'] ?? 'N/A' ?></span></div>
                    <div class="info-row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($complaint['witness_phone']) ?></span></div>
                    <div class="info-row"><span class="label">Address:</span> <span class="value"><?= htmlspecialchars($complaint['witness_address']) ?></span></div>
                </div>
                <?php endif; ?>

                <!-- Statement -->
                <div class="section">
                    <div class="section-title">INCIDENT STATEMENT</div>
                    <p style="white-space: pre-wrap; background-color: white; padding: 15px; border-left: 4px solid #1e3a5f;"><?= htmlspecialchars($complaint['complaint_statement']) ?></p>
                </div>

                <!-- ML Classification -->
                <?php if ($complaint['ml_classification']): ?>
                <div class="section">
                    <div class="section-title">AI CLASSIFICATION</div>
                    <div class="info-row"><span class="label">Classification:</span> <span class="value"><?= $complaint['ml_classification'] ?></span></div>
                    <div class="info-row"><span class="label">Confidence Level:</span> <span class="value"><?= ($complaint['ml_confidence'] * 100) ?>%</span></div>
                    <?php if ($complaint['ml_reasoning']): ?>
                    <div class="info-row"><span class="label">Reasoning:</span> <span class="value"><?= htmlspecialchars($complaint['ml_reasoning']) ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="footer">
                <p><strong>Barangay San Miguel</strong><br>
                Pasig City, Metro Manila, Philippines<br>
                This is an automated email from the Barangay Blotter Management System</p>
                <p style="font-size: 11px; color: #999;">
                    For questions or additional information, please contact the Barangay office.<br>
                    Report Reference: BLT-<?= str_pad($complaint['case_no'], 6, '0', STR_PAD_LEFT) ?>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
