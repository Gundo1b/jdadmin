<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM timetable WHERE id=$id");
    echo '<div class="alert alert-success">Timetable entry deleted successfully!</div>';
}

// Fetch filter values
$filter_day = $_GET['day'] ?? '';
$filter_tutor = $_GET['tutor_id'] ?? '';

// Build Query
$where_clauses = [];
if (!empty($filter_day)) {
    $day_esc = $conn->real_escape_string($filter_day);
    $where_clauses[] = "t.day_of_week = '$day_esc'";
}
if (!empty($filter_tutor)) {
    $tutor_id_esc = (int)$filter_tutor;
    $where_clauses[] = "t.tutor_id = $tutor_id_esc";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Fetch tutors for filter dropdown
$tutors_res = $conn->query("SELECT id, first_name, last_name FROM tutors ORDER BY first_name");

// Fetch timetable entries (NO class joins)
$timetable_query = "
    SELECT t.*, sub.subject_name,
           tu.first_name AS tutor_first, tu.last_name AS tutor_last
    FROM timetable t
    JOIN subjects sub ON t.subject_id=sub.id
    JOIN tutors tu ON t.tutor_id=tu.id
    $where_sql
    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
             t.start_time
";
$timetable = $conn->query($timetable_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-calendar-week"></i> View Timetable</h3>
    </div>

    <!-- FILTERS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter by Day</label>
                    <select name="day" class="form-select">
                        <option value="">All Days</option>
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo ($filter_day == $d) ? 'selected' : ''; ?>>
                                <?php echo $d; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter by Tutor</label>
                    <select name="tutor_id" class="form-select">
                        <option value="">All Tutors</option>
                        <?php while ($t = $tutors_res->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($filter_tutor == $t['id']) ? 'selected' : ''; ?>>
                                <?php echo $t['first_name'] . ' ' . $t['last_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <a href="view_timetable.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Tutor</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($timetable && $timetable->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $timetable->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><span class="badge bg-light text-dark shadow-sm"><?php echo $row['day_of_week']; ?></span></td>
                                    <td>
                                        <strong><?php echo date('g:i A', strtotime($row['start_time'])); ?></strong><br>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($row['end_time'])); ?></small>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?php echo $row['subject_name']; ?></span></td>
                                    <td><?php echo $row['tutor_first'] . ' ' . $row['tutor_last']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit_timetable.php?id=<?php echo $row['id']; ?>"
                                               class="btn btn-sm btn-warning text-white" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="view_timetable.php?delete_id=<?php echo $row['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this timetable entry?');" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x display-6 mb-3 d-block"></i>
                                    No timetable entries found matching your filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
