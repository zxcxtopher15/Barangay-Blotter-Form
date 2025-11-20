<?php
/**
 * Email Configuration
 * Copy this file to email.config.php and update with your SMTP settings
 *
 * IMPORTANT: Add email.config.php to .gitignore to keep credentials secure
 */

return [
    // SMTP Server Settings
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // 'tls' or 'ssl'

    // SMTP Authentication
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password', // Use App Password for Gmail

    // From Email Settings
    'from_email' => 'noreply@barangay-blotter-form.penxel.ph',
    'from_name' => 'Barangay San Miguel - Blotter System',

    // PNP Email
    'pnp_email' => 'epd.pio@gmail.com',
    'pnp_name' => 'Eastern Police District PIO',

    // Email Options
    'enable_debug' => false, // Set to true for SMTP debugging
    'debug_level' => 2, // 0 = off, 1 = client, 2 = server
];
