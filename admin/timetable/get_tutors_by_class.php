<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    echo json_encode([]);
    exit;
}

$class_id = (int) $_GET['class_id'];
$fallback = isset($_GET['fallback']) && $_GET['fallback'] === '1';

// Fetch tutors assigned to this class
$query = "SELECT DISTINCT t.id, t.first_name, t.last_name 
          FROM tutors t
          JOIN tutor_classes tc ON t.id = tc.tutor_id
          WHERE tc.class_id = $class_id
          ORDER BY t.first_name, t.last_name";

$result = $conn->query($query);

$tutors = [];
while ($row = $result->fetch_assoc()) {
    $tutors[] = [
        'id' => $row['id'],
        'name' => $row['first_name'] . ' ' . $row['last_name']
    ];
}

if ($fallback && empty($tutors)) {
    $all = $conn->query("SELECT id, first_name, last_name FROM tutors ORDER BY first_name, last_name");
    while ($row = $all->fetch_assoc()) {
        $tutors[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }
}

echo json_encode($tutors);
?>
