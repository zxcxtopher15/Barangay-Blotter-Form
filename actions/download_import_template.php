<?php
// CSV Template for Importing Complaints

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="complaint_import_template.csv"');

// CSV Headers - Matching exact format from user's template
$headers = [
    'DATE',
    'CASE NO.',
    'CASE TITLE',
    'COMPLAINANT LAST NAME',
    'COMPLAINANT GIVEN NAME',
    'COMPLAINANT MIDDLE NAME',
    'COMPLAINANT ADDRESS',
    'COMPLAINANT CONTACT NO.',
    'RESPONDENT LAST NAME',
    'RESPONDENT GIVEN NAME',
    'RESPONDENT MIDDLE NAME',
    'RESPONDENT ADDRESS',
    'RESPONDENT CONTACT NO.'
];

// Sample data rows
$samples = [
    [
        '2024-01-15',
        'BLT-2024-001',
        'Theft of Mobile Phone',
        'Dela Cruz',
        'Juan',
        'Santos',
        '123 Main St, San Miguel, Pasig City',
        '09171234567',
        'Unknown',
        'Suspect',
        '',
        'N/A',
        'N/A'
    ],
    [
        '2024-01-16',
        'BLT-2024-002',
        'Noise Complaint from Neighbor',
        'Santos',
        'Maria',
        'Reyes',
        '456 Maligaya St, San Miguel, Pasig City',
        '09189876543',
        'Reyes',
        'Pedro',
        'Garcia',
        '789 Masaya St, San Miguel, Pasig City',
        '09181234567'
    ],
    [
        '2024-01-17',
        'BLT-2024-003',
        'Physical Assault During Argument',
        'Garcia',
        'Carlos',
        'Mendoza',
        '321 Tahimik St, San Miguel, Pasig City',
        '09181112222',
        'Mendoza',
        'Jose',
        'Santos',
        '654 Payapa St, San Miguel, Pasig City',
        '09189998888'
    ]
];

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, $headers);

// Write sample rows
foreach ($samples as $sample) {
    fputcsv($output, $sample);
}

fclose($output);
exit;
?>
