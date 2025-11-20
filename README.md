# Barangay San Miguel Blotter Form System

A modern, AI-powered blotter form management system for Barangay San Miguel, Pasig City.

## Features

### ✅ Core Features
- **Digital Blotter Form**: Multi-step complaint submission with validation
- **Google OAuth Authentication**: Secure login for Admin and Desk Officers
- **Role-Based Access Control**: Different permissions for Admin vs Desk Officer
- **Interactive Map**: Pin incident locations with Leaflet and OpenStreetMap
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### 🆕 New Features (Version 2.0)

#### 1. **Date of Birth with Auto-Calculated Age**
- Input birth date instead of manual age entry
- JavaScript automatically calculates current age
- Age updates automatically as time passes
- Applies to: Complainant, Victim, Witness, Respondent

#### 2. **AI-Powered Complaint Classification**
- Uses OpenRouter AI (NVIDIA Nemotron model)
- Classifies complaints as:
  - **ENDORSE_TO_PNP**: Serious crimes requiring police action
  - **BARANGAY_ACTION**: Minor issues handled at barangay level
- Provides confidence scores and reasoning
- Builds dataset for future improvements

#### 3. **PNP Email Endorsement**
- Send professional HTML emails to PNP (epd.pio@gmail.com)
- Includes complete case details, GPS coordinates, and ML classification
- Uses PHPMailer for reliable delivery
- Tracks email delivery status in database
- Prevents duplicate endorsements

#### 4. **Geographical Restrictions**
- Map shows Barangay San Miguel boundary (blue polygon)
- Users can only place incident pins within barangay
- Visual boundary reference
- Bilingual alerts (Filipino/English)

#### 5. **Address Autocomplete**
- Dropdown suggestions for City/Municipality (NCR cities)
- All 30 Pasig City barangays available
- Auto-concatenates full address
- Standardized address format

## System Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Composer (PHP dependency manager)
- Apache/Nginx web server
- Internet connection (for ML classification)

## Quick Start

### 1. Install Dependencies

```bash
cd c:\xampp\htdocs\Barangay-Blotter-Form
composer install
```

### 2. Database Setup

Run these SQL files in order:
1. `Database query/add_dob_fields.sql`
2. `Database query/add_ml_and_status_tables.sql`

### 3. Email Configuration

```bash
copy "config\email.config.example.php" "config\email.config.php"
```

Edit `config/email.config.php` with your SMTP credentials.

### 4. Access the System

```
http://localhost/Barangay-Blotter-Form/
```

## Documentation

- **[SETUP_GUIDE.md](SETUP_GUIDE.md)**: Complete setup instructions
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)**: Feature details and technical specs
- **[ML_DOCUMENTATION.md](ML_DOCUMENTATION.md)**: Machine Learning system documentation

## Technology Stack

### Frontend
- HTML5, CSS3, JavaScript (ES6+)
- Tailwind CSS (via CDN)
- Leaflet.js for maps
- OpenStreetMap tiles

### Backend
- PHP 7.4+
- MySQL/MariaDB
- PHPMailer (via Composer)

### AI/ML
- OpenRouter AI API
- NVIDIA Nemotron Nano 12B model
- Natural language understanding

### Email
- PHPMailer 6.9+
- SMTP support (Gmail, SendGrid, etc.)

## Database Schema

### Main Tables
- `complaints` (case_no, complainant info, victim info, witness info, respondent info)
- `accounts` (user accounts for Google OAuth)
- `ml_classifications` (ML classification logs and dataset)
- `email_logs` (PNP endorsement email tracking)

### New Fields in `complaints`
- `complainant_dob`, `victim_dob`, `witness_dob`, `respondent_dob`
- `status`, `ml_classification`, `ml_confidence`, `ml_reasoning`
- `endorsed_date`, `endorsed_by`

## API Endpoints

### ML Classification
```
POST /api/classify_complaint.php
Content-Type: application/json

{
  "complaint_description": "Physical Assault",
  "complaint_statement": "Description of incident",
  "incident_location": "San Miguel, Pasig City"
}
```

### PNP Email Endorsement
```
POST /api/send_pnp_email.php
Content-Type: application/json

{
  "case_no": 123
}
```

## Configuration

### Email Settings
Edit `config/email.config.php`:
- SMTP credentials
- From/To email addresses
- Debug settings

### ML API
OpenRouter API key in `api/classify_complaint.php`

### Google OAuth
Update credentials in `google-oauth.php`

## Security

- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ SQL injection prevention (prepared statements)
- ✅ Configuration files in `.gitignore`
- ✅ HTTPS recommended for production
- ✅ API key protection

## Barangay San Miguel Coordinates

**Approximate Boundary**:
- Northwest: 14.5745°N, 121.0795°E
- Northeast: 14.5755°N, 121.0885°E
- Southeast: 14.5680°N, 121.0910°E
- Southwest: 14.5635°N, 121.0835°E

**Center**: 14.5690°N, 121.0850°E

## Email Configuration Examples

### Gmail
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_username' => 'your-email@gmail.com',
'smtp_password' => 'your-app-password', // 16-char App Password
```

### SendGrid
```php
'smtp_host' => 'smtp.sendgrid.net',
'smtp_username' => 'apikey',
'smtp_password' => 'YOUR_SENDGRID_API_KEY',
```

## Troubleshooting

### Email Not Sending
1. Check SMTP credentials in `config/email.config.php`
2. Enable debug mode: `'enable_debug' => true`
3. Check PHP error logs
4. For Gmail: Use App Password, not regular password

### ML Classification Failing
1. Verify internet connection
2. Check OpenRouter API key validity
3. Review API error logs

### Database Errors
1. Ensure all migrations executed
2. Verify `case_no` column exists (not `complaint_no`)
3. Check foreign key constraints

## Development

### Project Structure
```
Barangay-Blotter-Form/
├── api/
│   ├── classify_complaint.php    # ML classification endpoint
│   └── send_pnp_email.php        # PNP email endpoint
├── config/
│   ├── email.config.example.php  # Email config template
│   └── email.config.php          # Actual config (gitignored)
├── Database query/
│   ├── add_dob_fields.sql
│   └── add_ml_and_status_tables.sql
├── vendor/                        # Composer dependencies
├── blotter.php                    # Main form (Desk Officer)
├── blotteradmin.php              # Admin form
├── google-oauth.php              # OAuth handler
├── composer.json                  # PHP dependencies
├── .gitignore                    # Git ignore rules
├── SETUP_GUIDE.md                # Setup instructions
├── IMPLEMENTATION_SUMMARY.md     # Feature documentation
├── ML_DOCUMENTATION.md           # ML system docs
└── README.md                     # This file
```

## Contributors

- **AI Development Assistant**: Initial implementation and documentation
- **Barangay San Miguel IT Team**: Requirements and deployment

## Version History

- **v2.0** (2025-11-20): ML classification, email endorsement, DOB fields, location restrictions, address autocomplete
- **v1.0** (2025-08-22): Initial release with basic blotter form

## License

Proprietary - Barangay San Miguel, Pasig City

## Support

For questions or issues:
1. Check documentation files
2. Review code comments
3. Contact Barangay IT team

---

**Last Updated**: November 20, 2025
**Version**: 2.0
**Status**: Production Ready
