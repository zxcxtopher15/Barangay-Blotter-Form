# Barangay Blotter Form - Setup Guide

This guide will help you set up and configure the Barangay Blotter Form system.

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database
- Composer (PHP dependency manager)
- Web server (Apache/Nginx)

---

## Step 1: Install Composer Dependencies

PHPMailer is required for sending emails. Install it using Composer:

```bash
cd c:\xampp\htdocs\Barangay-Blotter-Form
composer install
```

This will install PHPMailer and create a `vendor` directory.

---

## Step 2: Database Setup

### A. Run the Database Migration Scripts

Execute the following SQL files in your MySQL database (in order):

1. **Add DOB fields**:
   ```sql
   -- File: Database query/add_dob_fields.sql
   ```
   This adds date of birth fields for automatic age calculation.

2. **Add ML and Status tables**:
   ```sql
   -- File: Database query/add_ml_and_status_tables.sql
   ```
   This creates:
   - `ml_classifications` table for ML dataset
   - `email_logs` table for email tracking
   - Adds status, ML classification, and endorsement fields to `complaints` table

### B. Verify Database Schema

After running the migrations, verify your `complaints` table has these fields:
- `case_no` (primary key)
- `complainant_dob`, `victim_dob`, `witness_dob`, `respondent_dob`
- `status`, `ml_classification`, `ml_confidence`, `ml_reasoning`
- `endorsed_date`, `endorsed_by`

---

## Step 3: Email Configuration

### A. Create Email Configuration File

1. Copy the example configuration:
   ```bash
   copy "config\email.config.example.php" "config\email.config.php"
   ```

2. Edit `config/email.config.php` with your SMTP settings:

```php
return [
    // SMTP Server Settings
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',

    // SMTP Authentication (use your actual credentials)
    'smtp_username' => 'your-barangay-email@gmail.com',
    'smtp_password' => 'your-app-password',

    // From Email Settings
    'from_email' => 'noreply@barangay-blotter-form.penxel.ph',
    'from_name' => 'Barangay San Miguel - Blotter System',

    // PNP Email
    'pnp_email' => 'epd.pio@gmail.com',
    'pnp_name' => 'Eastern Police District PIO',

    // Debugging (set to true during setup)
    'enable_debug' => false,
    'debug_level' => 2,
];
```

### B. Gmail App Password Setup (if using Gmail)

If using Gmail SMTP:

1. Go to your Google Account: https://myaccount.google.com/
2. Click "Security" → "2-Step Verification" (enable if not already)
3. Scroll to "App passwords"
4. Generate a new app password for "Mail" on "Other (Custom name)"
5. Copy the 16-character password
6. Use this password in `smtp_password` field

### C. Alternative SMTP Providers

You can use other SMTP providers:

**SendGrid**:
```php
'smtp_host' => 'smtp.sendgrid.net',
'smtp_port' => 587,
'smtp_username' => 'apikey',
'smtp_password' => 'YOUR_SENDGRID_API_KEY',
```

**Mailgun**:
```php
'smtp_host' => 'smtp.mailgun.org',
'smtp_port' => 587,
'smtp_username' => 'postmaster@your-domain.mailgun.org',
'smtp_password' => 'YOUR_MAILGUN_PASSWORD',
```

**SMTP2GO**:
```php
'smtp_host' => 'mail.smtp2go.com',
'smtp_port' => 2525,
'smtp_username' => 'your-username',
'smtp_password' => 'your-password',
```

---

## Step 4: Test the System

### A. Test Email Sending

1. Create a test PHP file (`test_email.php`):

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$email_config = require __DIR__ . '/config/email.config.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $email_config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $email_config['smtp_username'];
    $mail->Password   = $email_config['smtp_password'];
    $mail->SMTPSecure = $email_config['smtp_secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $email_config['smtp_port'];

    $mail->setFrom($email_config['from_email'], $email_config['from_name']);
    $mail->addAddress('your-test-email@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Barangay Blotter System';
    $mail->Body    = 'This is a test email. If you receive this, email is configured correctly.';

    $mail->send();
    echo 'Test email sent successfully!';
} catch (Exception $e) {
    echo "Email failed: {$mail->ErrorInfo}";
}
```

2. Run the test:
   ```
   php test_email.php
   ```

### B. Test ML Classification

Test the ML classification API:

```bash
curl -X POST http://localhost/Barangay-Blotter-Form/api/classify_complaint.php \
  -H "Content-Type: application/json" \
  -d "{\"complaint_description\":\"Murder\",\"complaint_statement\":\"Victim found with gunshot wounds\"}"
```

Expected response:
```json
{
  "success": true,
  "classification": "ENDORSE_TO_PNP",
  "confidence": 0.99,
  "reasoning": "Murder is a grave felony requiring immediate PNP investigation"
}
```

---

## Step 5: Security Hardening

### A. Protect Configuration Files

Add to `.gitignore`:
```
config/email.config.php
vendor/
*.log
```

### B. Set Proper File Permissions

```bash
# Make config directory readable only by web server
chmod 750 config/
chmod 640 config/email.config.php
```

### C. API Key Security

The OpenRouter API key is currently hardcoded in `api/classify_complaint.php`. For production:

1. Create `config/ml.config.php`:
```php
<?php
return [
    'api_key' => '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',
    'model' => 'nvidia/nemotron-nano-12b-v2-vl:free'
];
```

2. Update `api/classify_complaint.php` to load from config.

---

## Step 6: Production Checklist

Before deploying to production:

- [ ] All database migrations executed
- [ ] Composer dependencies installed
- [ ] Email configuration file created and tested
- [ ] Test email successfully sent to PNP email
- [ ] ML classification API tested
- [ ] Configuration files excluded from version control
- [ ] File permissions set correctly
- [ ] SSL/HTTPS enabled on web server
- [ ] Database backups configured
- [ ] Error logging configured

---

## Troubleshooting

### Email Not Sending

1. **Check SMTP credentials**: Verify username/password in `config/email.config.php`
2. **Enable debugging**:
   ```php
   'enable_debug' => true,
   'debug_level' => 2,
   ```
   Check logs in PHP error log

3. **Test SMTP connection**:
   ```bash
   telnet smtp.gmail.com 587
   ```

4. **Gmail blocking**: Check Gmail "Less secure app access" or use App Password

### ML Classification Failing

1. **Check API key**: Verify OpenRouter API key is valid
2. **Check internet connection**: ML API requires internet
3. **View error logs**: Check browser console and PHP error logs

### Database Errors

1. **Check column names**: Use `case_no` not `complaint_no`
2. **Run migrations**: Ensure all SQL files executed
3. **Check foreign keys**: Ensure `case_no` exists in parent table

---

## Support

For issues or questions:
- Check `ML_DOCUMENTATION.md` for ML system details
- Check `IMPLEMENTATION_SUMMARY.md` for feature overview
- Review code comments in API files

---

## Next Steps

After setup is complete:

1. Test the complete flow:
   - Submit a blotter form
   - Check ML classification
   - Send test PNP endorsement email

2. Train staff on new features:
   - DOB-based age calculation
   - Address autocomplete
   - Map boundary restrictions
   - ML classification interpretation
   - PNP email endorsement process

3. Monitor system:
   - Check `ml_classifications` table for dataset growth
   - Review `email_logs` for delivery success
   - Monitor API error rates

---

**System Version**: 2.0
**Last Updated**: November 20, 2025
**Setup Guide Version**: 1.0
