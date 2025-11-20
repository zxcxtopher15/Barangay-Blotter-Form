# Email Setup Instructions for PNP Endorsement

## Overview
The system now automatically detects whether an incident should be endorsed to PNP or handled at the barangay level based on the crime type. It can send beautiful HTML email reports directly to the Eastern Police District.

## Features Implemented

### 1. **Automatic Recommendation Display**
- When viewing a report in the modal, the system shows a recommendation box
- **RED box**: "Endorse to PNP" for serious crimes
- **GREEN box**: "Barangay Action" for minor complaints

### 2. **Serious Crimes (PNP Endorsement Required)**
- Murder
- Homicide
- Rape
- Robbery
- Theft
- Physical Assault
- Carnapping
- Arson
- Kidnapping
- Hostage Taking
- Drug-related
- Illegal Gambling
- Illegal Possession of Firearms
- Violation of Special Laws

### 3. **Minor Complaints (Barangay Level)**
- Physical Injuries
- Vandalism
- Noise Complaints
- Domestic Violence
- Trespassing
- Boundary Disputes
- Property Disputes

### 4. **PNP Contact Information Displayed**
When a serious crime is detected, the modal shows:
- 📧 Email: epd.pio@gmail.com
- 📞 EPD Headquarters: 0998-598-7874
- 📞 Pasig City Police: 0998-598-7880
- 📞 Mandaluyong City Police: 0998-598-7882

### 5. **Send Email Button (Admin Only)**
- Only appears for serious crimes in reportsadmin.php
- Sends a beautifully formatted HTML email to PNP
- Includes all incident details, map link, and contact information

## Email Setup (REQUIRED)

### Step 1: Get Gmail App Password

1. Go to your Gmail account settings
2. Enable 2-Factor Authentication if not already enabled
3. Go to: https://myaccount.google.com/apppasswords
4. Select "Mail" and "Other (Custom name)"
5. Name it "Barangay Blotter System"
6. Click "Generate"
7. Copy the 16-character app password (format: xxxx-xxxx-xxxx-xxxx)

### Step 2: Update Email Configuration

Open the file: `actions/send_pnp_email.php`

Find these lines (around line 56-60):

```php
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com'; // UPDATE THIS
$mail->Password = 'your-app-password'; // UPDATE THIS
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

Replace:
- `'your-email@gmail.com'` with your actual Gmail address (appears twice, lines 60 and 64)
- `'your-app-password'` with the 16-character app password from Step 1

### Step 3: Update "From" Email

Find this line (around line 64):

```php
$mail->setFrom('your-email@gmail.com', 'Barangay San Miguel Blotter System');
```

Replace `'your-email@gmail.com'` with your Gmail address.

### Example Configuration:

```php
$mail->Username = 'barangaysanmiguel@gmail.com';
$mail->Password = 'abcd efgh ijkl mnop'; // Your 16-char app password
$mail->setFrom('barangaysanmiguel@gmail.com', 'Barangay San Miguel Blotter System');
```

## Email Template Features

The email sent to PNP includes:

1. **Header**: Gradient purple background with "CRIME REPORT ENDORSEMENT" and "REQUIRES PNP ACTION" badge
2. **Case Information**: Case number and incident date/time
3. **Incident Details**: Crime type, location, and interactive map link
4. **Complainant Information**: Full details with contact info
5. **Victim Information**: Full victim details
6. **Respondent Information**: If available
7. **Statement**: Full complaint statement in a highlighted box
8. **Coordination Info**: Barangay contact details and desk officer name
9. **Footer**: Professional branding

## Testing the Email

### Test Email Setup:

1. Log in as Admin
2. Go to Reports Admin page
3. Find a serious crime report (Murder, Robbery, etc.)
4. Click on the row to open the modal
5. You should see:
   - RED recommendation box saying "Endorse to PNP"
   - PNP contact information
   - "📧 Send Email to PNP" button
6. Click the button
7. Confirm the dialog
8. Wait for success message

### Troubleshooting:

**Error: "Authentication failed"**
- Check that you're using the App Password, not your regular Gmail password
- Make sure 2FA is enabled on your Gmail account

**Error: "Could not connect to SMTP host"**
- Check your internet connection
- Verify Port 587 is not blocked by firewall
- Try using Port 465 with `SMTPSecure = PHPMailer::ENCRYPTION_SMTPS`

**Email not received:**
- Check PNP spam/junk folder
- Verify email address epd.pio@gmail.com is correct
- Check Gmail "Sent" folder to confirm it was sent

## Files Modified

1. **actions/send_pnp_email.php** (NEW) - Email sending functionality
2. **reportsadmin.php** - Added recommendation box and email button
3. **reports.php** - Added recommendation box (view only, no email)

## Security Notes

- ⚠️ Never commit your Gmail password to Git
- ⚠️ Use environment variables for production
- ⚠️ Consider using a dedicated Gmail account for the system
- ⚠️ The email feature is only available to admin users

## Alternative SMTP Providers

If you prefer not to use Gmail, you can use:

### SendGrid (Free tier: 100 emails/day)
```php
$mail->Host = 'smtp.sendgrid.net';
$mail->Username = 'apikey';
$mail->Password = 'YOUR_SENDGRID_API_KEY';
$mail->Port = 587;
```

### Mailgun (Free tier: 5,000 emails/month)
```php
$mail->Host = 'smtp.mailgun.org';
$mail->Username = 'postmaster@your-domain.mailgun.org';
$mail->Password = 'YOUR_MAILGUN_PASSWORD';
$mail->Port = 587;
```

## Support

If you need help setting up the email, check:
1. PHPMailer documentation: https://github.com/PHPMailer/PHPMailer
2. Gmail App Password guide: https://support.google.com/accounts/answer/185833
3. Make sure Composer dependencies are installed: `composer install`
