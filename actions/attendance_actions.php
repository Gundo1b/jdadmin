<?php
require_once '../config/db.php';

if (!isset($_GET['action'])) {
    header('Location: ../admin/attendance/daily_student.php');
    exit;
}

// Ensure attendance table exists
$conn->query("CREATE TABLE IF NOT EXISTS student_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present','Absent') NOT NULL DEFAULT 'Absent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_student_date (student_id, attendance_date),
    KEY student_id (student_id),
    CONSTRAINT fk_student_attendance_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = $_GET['action'];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : '';
    $attendance_date = $conn->real_escape_string($attendance_date);

    $statuses = isset($_POST['status']) && is_array($_POST['status']) ? $_POST['status'] : [];

    if ($attendance_date !== '' && count($statuses) > 0) {
        foreach ($statuses as $student_id => $status) {
            $student_id = (int) $student_id;
            $status = $status === 'Present' ? 'Present' : 'Absent';

            $conn->query("INSERT INTO student_attendance (student_id, attendance_date, status)
                          VALUES ($student_id, '$attendance_date', '$status')
                          ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = CURRENT_TIMESTAMP");
        }
    }

    header('Location: ../admin/attendance/daily_student.php?success=1&date=' . urlencode($attendance_date));
    exit;
}

header('Location: ../admin/attendance/daily_student.php');
exit;
?>
