<?php
header('Content-Type: application/json');

// Default error response
$errorResponse = json_encode(['error' => 'Invalid parameters', 'country_code' => 'Unknown']);

// Sanitize and validate input
$lat = filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$lon = filter_input(INPUT_GET, 'lon', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

if (!$lat || !$lon) {
    echo $errorResponse;
    exit;
}

// Function to get country code by IP using ipinfo.io
function getCountryByIP($ip) {
    $apiKey = '9d4d5d9eb6137d';  // Replace with your actual API key
    $url = "https://ipinfo.io/{$ip}/json?token={$apiKey}";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);  // Set a timeout
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Bypass the SSL verification if necessary

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("cURL Error #: " . $err);
        return 'Unknown';
    } else {
        $data = json_decode($response, true);
        return $data['country'] ?? 'Unknown';
    }
}

// Function to fetch country code by geolocation using OpenCage Geocoder
function getCountryByCoordinates($lat, $lon) {
    $apiKey = 'r4A8DR3IowIitlPAWQ__5GS3jnuju1dn4OM5ipOuJu8';
    $url = "https://api.opencagedata.com/geocode/v1/json?q={$lat}+{$lon}&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return 'Unknown';
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (!isset($data['results'][0]['components']['country_code'])) {
        error_log("Failed to find country code in the response: " . json_encode($data));
        return 'Unknown';
    }
    return $data['results'][0]['components']['country_code'];
}

// Determine the country code based on lat/lon or IP
$countryCode = 'Unknown';  // Default to 'Unknown'
if (isset($_GET['lat']) && isset($_GET['lon'])) {
    $countryCode = getCountryByCoordinates($_GET['lat'], $_GET['lon']);
} else {
    $ip = $_SERVER['REMOTE_ADDR'];  // Get client IP address
    $countryCode = getCountryByIP($ip);
}

// Output the country code in JSON format
echo json_encode(['country_code' => $countryCode]);
exit;
