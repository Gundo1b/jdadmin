<?php
require_once '../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if ($q === '' || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

if (ctype_digit($q)) {
    $id = (int) $q;
    $like = '%' . $q . '%';
    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name
         FROM students
         WHERE id = ? OR first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?
         ORDER BY first_name, last_name
         LIMIT 20"
    );
    $stmt->bind_param('isss', $id, $like, $like, $like);
} else {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name
         FROM students
         WHERE first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?
         ORDER BY first_name, last_name
         LIMIT 20"
    );
    $stmt->bind_param('sss', $like, $like, $like);
}

$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    $results[] = [
        'id' => (int) $row['id'],
        'name' => $fullName,
        'label' => $fullName . ' (#' . (int) $row['id'] . ')',
    ];
}
$stmt->close();

echo json_encode($results);
?>
