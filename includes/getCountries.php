<?php
include 'includes/db.php';  // Make sure this file contains your database connection settings
/**@var mysqli $conn*/
// Fetch countries from the database
$sql = "SELECT country_code, country_name FROM apps_countries ORDER BY country_name ASC";
$result = $conn->query($sql);

$countries = [];
while ($row = $result->fetch_assoc()) {
    $countries[] = $row;
}

header('Content-Type: application/json');
echo json_encode($countries);
