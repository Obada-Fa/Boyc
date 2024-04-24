<?php
require_once '../vendor/autoload.php';  // Load Composer's autoloader

use GeoIp2\Database\Reader;

if (isset($_GET['lat']) && isset($_GET['lon'])){
    header('Content-Type: application/json');

// Prepare the path to the GeoLite2 database file
    $reader = new Reader('/GeoLite2-Country.mmdb');

    $errorResponse = json_encode(['error' => 'Invalid parameters', 'country_code' => 'Unknown']);


// Function to get country by IP using GeoLite2
    function getCountryByIP($ip, $errorResponse, $reader) {
        try {
            $response = $reader->country($ip);
            $countryCode = $response->country->isoCode;
            return json_encode(['country_code' => $countryCode]);
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            return $errorResponse;  // IP not found in the database
        } catch (Exception $e) {
            return $errorResponse;  // General errors
        }
    }

// Get the IP address from the server environment
    $ip = $_SERVER['REMOTE_ADDR'];
    $countryCode = getCountryByIP($ip, $errorResponse, $reader);

// Output the country code in JSON format
//echo json_encode(['country_code' => $countryCode]);
    exit;
}
