<?php
include 'config.php';

function fetchData($table, $search = '', $columns = '*') {
    global $conn;
    $query = "SELECT $columns FROM $table";
    if (!empty($search)) {
        $query .= " WHERE name LIKE ? ORDER BY name ASC";
        $stmt = $conn->prepare($query);
        $likeSearch = '%' . $search . '%';
        $stmt->bind_param('s', $likeSearch);
    } else {
        $query .= " ORDER BY name ASC";
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}


function insertData($table, $data) {
    global $conn;
    $fields = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), '?'));
    $types = str_repeat('s', count($data));
    $values = array_values($data);

    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $inserted = $stmt->affected_rows > 0;
    $stmt->close();
    return $inserted;
}
?>
