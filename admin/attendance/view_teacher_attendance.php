<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Ensure teacher attendance table exists
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

$attendance_date = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$attendance_date_safe = $conn->real_escape_string($attendance_date);

$teachers = $conn->query("SELECT t.id, t.first_name, t.last_name, t.email, t.phone,
                                 a.status AS attendance_status
                          FROM tutors t
                          LEFT JOIN teacher_attendance a 
                                 ON a.tutor_id = t.id AND a.attendance_date = '$attendance_date_safe'
                          ORDER BY t.last_name, t.first_name");
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-eye"></i> View Teacher Attendance</h3>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($attendance_date); ?>" class="form-control" required>
                </div>
                <div class="col-md-8 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Attendance Status</h5>
        </div>
        <div class="card-body">
            <?php if ($teachers && $teachers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Teacher</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th style="width: 120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php while ($row = $teachers->fetch_assoc()): ?>
                                <?php $status = $row['attendance_status'] ? $row['attendance_status'] : 'Absent'; ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['email'] ?? 'N/A'; ?></td>
                                    <td><?php echo $row['phone'] ?? 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $status === 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    No teachers found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
