<?php
include './includes/db.php';
/**@var mysqli $conn*/
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR company_name LIKE ?";
$stmt = $conn->prepare($sql);
$likeSearch = '%' . $search . '%';
$stmt->bind_param('sss', $likeSearch, $likeSearch, $likeSearch);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);

$conn->close();
?>
