<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

try {
    // Fetch Programs from database
    $programs_query = $conn->query("SELECT program_name FROM programs ORDER BY program_name ASC");
    $programs = [];
    while ($row = $programs_query->fetch_assoc()) {
        $programs[] = $row['program_name'];
    }

    // Fetch Grades
    $grades_query = $conn->query("SELECT name FROM grades ORDER BY name ASC");
    $grades = [];
    while ($row = $grades_query->fetch_assoc()) {
        $grades[] = $row['name'];
    }

    // Fetch Curriculums (Classes)
    $curriculums_query = $conn->query("SELECT DISTINCT c.class_name 
                                       FROM classes c 
                                       ORDER BY c.class_name ASC");
    $curriculums = [];
    while ($row = $curriculums_query->fetch_assoc()) {
        $curriculums[] = $row['class_name'];
    }

    // Fetch Subjects
    $subjects_query = $conn->query("SELECT subject_name FROM subjects ORDER BY subject_name ASC");
    $subjects = [];
    while ($row = $subjects_query->fetch_assoc()) {
        $subjects[] = $row['subject_name'];
    }

    // Return JSON response
    echo json_encode([
        'programs' => $programs,
        'grades' => $grades,
        'curriculums' => $curriculums,
        'subjects' => $subjects
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data: ' . $e->getMessage()]);
}
?>