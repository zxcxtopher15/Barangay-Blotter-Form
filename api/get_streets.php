<?php
/**
 * Fetch all streets in Barangay San Miguel, Pasig City
 * Uses OpenStreetMap Overpass API to get street names
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// San Miguel, Pasig City boundary coordinates (28-point polygon)
$boundary_coords = [
    [14.5765, 121.0825], [14.5763, 121.0848], [14.5759, 121.0870],
    [14.5753, 121.0892], [14.5745, 121.0905], [14.5735, 121.0912],
    [14.5720, 121.0918], [14.5705, 121.0920], [14.5690, 121.0918],
    [14.5678, 121.0910], [14.5670, 121.0900], [14.5665, 121.0888],
    [14.5660, 121.0875], [14.5655, 121.0860], [14.5650, 121.0845],
    [14.5648, 121.0830], [14.5650, 121.0815], [14.5655, 121.0800],
    [14.5662, 121.0788], [14.5670, 121.0780], [14.5680, 121.0775],
    [14.5692, 121.0773], [14.5705, 121.0775], [14.5718, 121.0780],
    [14.5730, 121.0788], [14.5742, 121.0798], [14.5752, 121.0808],
    [14.5760, 121.0817]
];

// Convert to Overpass API polygon format (lon lat pairs)
$polygon_string = '';
foreach ($boundary_coords as $coord) {
    $polygon_string .= $coord[0] . ' ' . $coord[1] . ' ';
}
$polygon_string = trim($polygon_string);

// Build Overpass API query
// Search for all highways (streets) within the San Miguel polygon
$overpass_query = <<<QUERY
[out:json][timeout:25];
(
  way["highway"]["name"](poly:"$polygon_string");
);
out body;
>;
out skel qt;
QUERY;

$overpass_url = 'https://overpass-api.de/api/interpreter';

// Make request to Overpass API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $overpass_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode($overpass_query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'BarangayBlotterSystem/1.0');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    // Fallback to curated list if API fails
    echo json_encode([
        'success' => true,
        'source' => 'fallback',
        'streets' => getCuratedStreets()
    ]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['elements'])) {
    echo json_encode([
        'success' => true,
        'source' => 'fallback',
        'streets' => getCuratedStreets()
    ]);
    exit;
}

// Extract unique street names with coordinates
$streets = [];
$street_names = [];

foreach ($data['elements'] as $element) {
    if (isset($element['tags']['name']) && $element['type'] === 'way') {
        $street_name = $element['tags']['name'];

        // Skip duplicates
        if (in_array($street_name, $street_names)) {
            continue;
        }

        $street_names[] = $street_name;

        // Get center coordinates if available
        $lat = null;
        $lon = null;

        if (isset($element['center'])) {
            $lat = $element['center']['lat'];
            $lon = $element['center']['lon'];
        } elseif (isset($element['nodes']) && count($element['nodes']) > 0) {
            // Calculate approximate center from nodes
            // (We'd need to fetch node details, so we'll use fallback)
            $lat = 14.5700; // Default center
            $lon = 121.0850;
        }

        $streets[] = [
            'name' => $street_name,
            'display' => $street_name . ', San Miguel, Pasig City',
            'lat' => $lat,
            'lon' => $lon
        ];
    }
}

// Add curated streets if API returned few results
if (count($streets) < 10) {
    $curated = getCuratedStreets();
    foreach ($curated as $street) {
        if (!in_array($street['name'], $street_names)) {
            $streets[] = $street;
        }
    }
}

// Sort alphabetically
usort($streets, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

echo json_encode([
    'success' => true,
    'source' => 'overpass',
    'count' => count($streets),
    'streets' => $streets
]);

/**
 * Curated list of known streets in San Miguel, Pasig City
 * Used as fallback if Overpass API fails or returns incomplete data
 */
function getCuratedStreets() {
    return [
        ['name' => 'Kanlaon Street', 'display' => 'Kanlaon Street, San Miguel, Pasig City', 'lat' => 14.5710, 'lon' => 121.0840],
        ['name' => 'Sierra Madre Street', 'display' => 'Sierra Madre Street, San Miguel, Pasig City', 'lat' => 14.5705, 'lon' => 121.0845],
        ['name' => 'Mayon Street', 'display' => 'Mayon Street, San Miguel, Pasig City', 'lat' => 14.5700, 'lon' => 121.0850],
        ['name' => 'Taal Street', 'display' => 'Taal Street, San Miguel, Pasig City', 'lat' => 14.5695, 'lon' => 121.0855],
        ['name' => 'Pinatubo Street', 'display' => 'Pinatubo Street, San Miguel, Pasig City', 'lat' => 14.5690, 'lon' => 121.0860],
        ['name' => 'Apo Street', 'display' => 'Apo Street, San Miguel, Pasig City', 'lat' => 14.5685, 'lon' => 121.0865],
        ['name' => 'Banahaw Street', 'display' => 'Banahaw Street, San Miguel, Pasig City', 'lat' => 14.5680, 'lon' => 121.0870],
        ['name' => 'Makiling Street', 'display' => 'Makiling Street, San Miguel, Pasig City', 'lat' => 14.5675, 'lon' => 121.0875],
        ['name' => 'Pulag Street', 'display' => 'Pulag Street, San Miguel, Pasig City', 'lat' => 14.5715, 'lon' => 121.0835],
        ['name' => 'Isarog Street', 'display' => 'Isarog Street, San Miguel, Pasig City', 'lat' => 14.5720, 'lon' => 121.0830],
        ['name' => 'Mariveles Street', 'display' => 'Mariveles Street, San Miguel, Pasig City', 'lat' => 14.5725, 'lon' => 121.0825],
        ['name' => 'Natib Street', 'display' => 'Natib Street, San Miguel, Pasig City', 'lat' => 14.5730, 'lon' => 121.0820],
        ['name' => 'Samat Street', 'display' => 'Samat Street, San Miguel, Pasig City', 'lat' => 14.5735, 'lon' => 121.0815],
        ['name' => 'Batulao Street', 'display' => 'Batulao Street, San Miguel, Pasig City', 'lat' => 14.5740, 'lon' => 121.0810],
        ['name' => 'Maculot Street', 'display' => 'Maculot Street, San Miguel, Pasig City', 'lat' => 14.5665, 'lon' => 121.0880],
        ['name' => 'Tapyas Street', 'display' => 'Tapyas Street, San Miguel, Pasig City', 'lat' => 14.5660, 'lon' => 121.0885],
        ['name' => 'Halcon Street', 'display' => 'Halcon Street, San Miguel, Pasig City', 'lat' => 14.5655, 'lon' => 121.0890],
        ['name' => 'Malasimbo Street', 'display' => 'Malasimbo Street, San Miguel, Pasig City', 'lat' => 14.5650, 'lon' => 121.0895],
        // Landmarks
        ['name' => 'San Miguel Elementary School', 'display' => 'San Miguel Elementary School, San Miguel, Pasig City', 'lat' => 14.5702, 'lon' => 121.0858],
        ['name' => 'San Miguel Public Market', 'display' => 'San Miguel Public Market, San Miguel, Pasig City', 'lat' => 14.5695, 'lon' => 121.0852],
        ['name' => 'San Miguel Barangay Hall', 'display' => 'San Miguel Barangay Hall, San Miguel, Pasig City', 'lat' => 14.5698, 'lon' => 121.0848],
        ['name' => 'San Miguel Chapel', 'display' => 'San Miguel Chapel, San Miguel, Pasig City', 'lat' => 14.5705, 'lon' => 121.0842],
        ['name' => 'San Miguel Industrial Area', 'display' => 'San Miguel Industrial Area, San Miguel, Pasig City', 'lat' => 14.5680, 'lon' => 121.0890],
        ['name' => 'San Miguel Riverside', 'display' => 'San Miguel Riverside, San Miguel, Pasig City', 'lat' => 14.5720, 'lon' => 121.0900],
    ];
}
