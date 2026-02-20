<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

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

$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");
$classes = $conn->query("SELECT c.*, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id = g.id 
                         ORDER BY g.name, c.class_name");

$attendance_date = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$attendance_date_safe = $conn->real_escape_string($attendance_date);

$selected_grade_id = isset($_GET['grade_id']) && $_GET['grade_id'] !== '' ? (int) $_GET['grade_id'] : 0;
$selected_class_id = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int) $_GET['class_id'] : 0;

$where = [];
if ($selected_grade_id > 0) {
    $where[] = "s.grade_id = $selected_grade_id";
}
if ($selected_class_id > 0) {
    $where[] = "s.class_id = $selected_class_id";
}
$where_sql = count($where) > 0 ? ("WHERE " . implode(" AND ", $where)) : "";

$students = $conn->query("SELECT s.id, s.first_name, s.last_name, g.name AS grade_name, c.class_name,
                                 a.status AS attendance_status
                          FROM students s
                          LEFT JOIN grades g ON s.grade_id = g.id
                          LEFT JOIN classes c ON s.class_id = c.id
                          LEFT JOIN student_attendance a 
                                 ON a.student_id = s.id AND a.attendance_date = '$attendance_date_safe'
                          $where_sql
                          ORDER BY s.last_name, s.first_name");

$success = isset($_GET['success']) && $_GET['success'] == '1';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-clipboard-check"></i> Daily Student Attendance</h3>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Attendance saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($attendance_date); ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grade</label>
                    <select name="grade_id" class="form-select">
                        <option value="">All Grades</option>
                        <?php if ($grades): ?>
                            <?php while ($g = $grades->fetch_assoc()): ?>
                                <option value="<?php echo $g['id']; ?>" <?php echo $selected_grade_id == $g['id'] ? 'selected' : ''; ?>>
                                    <?php echo $g['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curriculum</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Curriculums</option>
                        <?php if ($classes): ?>
                            <?php while ($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $selected_class_id == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo $c['grade_name'] . ' - ' . $c['class_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Load Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Students</h5>
            <a class="btn btn-sm btn-outline-primary"
               href="<?php echo BASE_URL; ?>admin/attendance/view_student_attendance.php?date=<?php echo urlencode($attendance_date); ?>&grade_id=<?php echo $selected_grade_id; ?>&class_id=<?php echo $selected_class_id; ?>">
                <i class="bi bi-eye"></i> View Attendance
            </a>
        </div>
        <div class="card-body">
            <?php if ($students && $students->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Student</th>
                                <th>Grade</th>
                                <th>Curriculum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['grade_name'] ?? 'N/A'; ?></td>
                                    <td><?php echo $row['class_name'] ?? 'N/A'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    No students found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
