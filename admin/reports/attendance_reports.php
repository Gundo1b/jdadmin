<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Ensure attendance tables exist
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

$conn->query("CREATE TABLE IF NOT EXISTS teacher_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present','Absent') NOT NULL DEFAULT 'Absent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tutor_date (tutor_id, attendance_date),
    KEY tutor_id (tutor_id),
    CONSTRAINT fk_teacher_attendance_tutor FOREIGN KEY (tutor_id) REFERENCES tutors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");
$classes = $conn->query("SELECT c.*, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id = g.id 
                         ORDER BY g.name, c.class_name");

$today = date('Y-m-d');

// Student filters
$s_from = isset($_GET['s_from']) && $_GET['s_from'] !== '' ? $_GET['s_from'] : $today;
$s_to = isset($_GET['s_to']) && $_GET['s_to'] !== '' ? $_GET['s_to'] : $today;
$s_status = isset($_GET['s_status']) ? $_GET['s_status'] : '';
$s_grade_id = isset($_GET['s_grade_id']) && $_GET['s_grade_id'] !== '' ? (int) $_GET['s_grade_id'] : 0;
$s_class_id = isset($_GET['s_class_id']) && $_GET['s_class_id'] !== '' ? (int) $_GET['s_class_id'] : 0;

$s_where = [];
$s_from_safe = $conn->real_escape_string($s_from);
$s_to_safe = $conn->real_escape_string($s_to);
$s_where[] = "a.attendance_date BETWEEN '$s_from_safe' AND '$s_to_safe'";

if ($s_status === 'Present' || $s_status === 'Absent') {
    $s_status_safe = $conn->real_escape_string($s_status);
    $s_where[] = "a.status = '$s_status_safe'";
}
if ($s_grade_id > 0) {
    $s_where[] = "s.grade_id = $s_grade_id";
}
if ($s_class_id > 0) {
    $s_where[] = "s.class_id = $s_class_id";
}
$s_where_sql = count($s_where) > 0 ? ("WHERE " . implode(" AND ", $s_where)) : "";

$student_report = $conn->query("SELECT s.id, s.first_name, s.last_name, g.name AS grade_name, c.class_name,
                                       a.attendance_date, a.status
                                FROM student_attendance a
                                JOIN students s ON a.student_id = s.id
                                LEFT JOIN grades g ON s.grade_id = g.id
                                LEFT JOIN classes c ON s.class_id = c.id
                                $s_where_sql
                                ORDER BY a.attendance_date DESC, s.last_name, s.first_name");

$student_summary = $conn->query("SELECT 
                                    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
                                    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
                                 FROM student_attendance a
                                 JOIN students s ON a.student_id = s.id
                                 $s_where_sql")->fetch_assoc();

// Tutor filters
$t_from = isset($_GET['t_from']) && $_GET['t_from'] !== '' ? $_GET['t_from'] : $today;
$t_to = isset($_GET['t_to']) && $_GET['t_to'] !== '' ? $_GET['t_to'] : $today;
$t_status = isset($_GET['t_status']) ? $_GET['t_status'] : '';

$t_where = [];
$t_from_safe = $conn->real_escape_string($t_from);
$t_to_safe = $conn->real_escape_string($t_to);
$t_where[] = "a.attendance_date BETWEEN '$t_from_safe' AND '$t_to_safe'";

if ($t_status === 'Present' || $t_status === 'Absent') {
    $t_status_safe = $conn->real_escape_string($t_status);
    $t_where[] = "a.status = '$t_status_safe'";
}
$t_where_sql = count($t_where) > 0 ? ("WHERE " . implode(" AND ", $t_where)) : "";

$tutor_report = $conn->query("SELECT t.id, t.first_name, t.last_name, a.attendance_date, a.status
                              FROM teacher_attendance a
                              JOIN tutors t ON a.tutor_id = t.id
                              $t_where_sql
                              ORDER BY a.attendance_date DESC, t.last_name, t.first_name");

$tutor_summary = $conn->query("SELECT 
                                  SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
                                  SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
                               FROM teacher_attendance a
                               $t_where_sql")->fetch_assoc();
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-clipboard-check"></i> Attendance Reports</h3>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Student Attendance Report</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="s_from" value="<?php echo htmlspecialchars($s_from); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="s_to" value="<?php echo htmlspecialchars($s_to); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grade</label>
                    <select name="s_grade_id" class="form-select">
                        <option value="">All Grades</option>
                        <?php if ($grades): ?>
                            <?php while ($g = $grades->fetch_assoc()): ?>
                                <option value="<?php echo $g['id']; ?>" <?php echo $s_grade_id == $g['id'] ? 'selected' : ''; ?>>
                                    <?php echo $g['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Curriculum</label>
                    <select name="s_class_id" class="form-select">
                        <option value="">All Curriculums</option>
                        <?php if ($classes): ?>
                            <?php while ($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $s_class_id == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo $c['grade_name'] . ' - ' . $c['class_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="s_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Present" <?php echo $s_status === 'Present' ? 'selected' : ''; ?>>Present</option>
                        <option value="Absent" <?php echo $s_status === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>

            <div class="d-flex gap-3 mb-3">
                <div class="badge bg-success">
                    Present: <?php echo (int) ($student_summary['present_count'] ?? 0); ?>
                </div>
                <div class="badge bg-danger">
                    Absent: <?php echo (int) ($student_summary['absent_count'] ?? 0); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Curriculum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($student_report && $student_report->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $student_report->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['attendance_date']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] === 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['grade_name'] ?? 'N/A'; ?></td>
                                    <td><?php echo $row['class_name'] ?? 'N/A'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Tutor Attendance Report</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="t_from" value="<?php echo htmlspecialchars($t_from); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="t_to" value="<?php echo htmlspecialchars($t_to); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="t_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Present" <?php echo $t_status === 'Present' ? 'selected' : ''; ?>>Present</option>
                        <option value="Absent" <?php echo $t_status === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>

            <div class="d-flex gap-3 mb-3">
                <div class="badge bg-success">
                    Present: <?php echo (int) ($tutor_summary['present_count'] ?? 0); ?>
                </div>
                <div class="badge bg-danger">
                    Absent: <?php echo (int) ($tutor_summary['absent_count'] ?? 0); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Tutor</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tutor_report && $tutor_report->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $tutor_report->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['attendance_date']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] === 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
