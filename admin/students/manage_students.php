<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Load filter options
$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");
$classes = $conn->query("SELECT c.*, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id = g.id 
                         ORDER BY g.name, c.class_name");

// Filters
$search_raw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = $search_raw !== '' ? $conn->real_escape_string($search_raw) : '';
$grade_id = isset($_GET['grade_id']) ? (int) $_GET['grade_id'] : 0;
$class_id = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;

$where = [];
if ($search !== '') {
    $where[] = "(s.first_name LIKE '%$search%' 
            OR s.last_name LIKE '%$search%' 
            OR s.email LIKE '%$search%' 
            OR s.phone LIKE '%$search%')";
}
if ($grade_id > 0) {
    $where[] = "s.grade_id = $grade_id";
}
if ($class_id > 0) {
    $where[] = "s.class_id = $class_id";
}

$query = "
    SELECT s.*, g.name AS grade_name, c.class_name 
    FROM students s
    LEFT JOIN grades g ON s.grade_id = g.id
    LEFT JOIN classes c ON s.class_id = c.id
";
if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}
$query .= " ORDER BY s.id DESC";

$students = $conn->query($query);
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-people"></i> Manage Students</h3>
        <a href="registration_type.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add New Student
        </a>
    </div>

    <?php if (isset($_GET['registered'])): ?>
        <?php
        $whatsapp_link = null;
        if (isset($_GET['student_id'])) {
            $student_id = (int) $_GET['student_id'];
            $student_result = $conn->query("
                SELECT s.first_name, s.last_name, g.name AS grade_name, c.class_name
                FROM students s
                LEFT JOIN grades g ON s.grade_id = g.id
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.id = $student_id
                LIMIT 1
            ");
            if ($student_result && $student_result->num_rows > 0) {
                $student = $student_result->fetch_assoc();
                $student_name = trim($student['first_name'] . ' ' . $student['last_name']);
                $grade_name = $student['grade_name'] ?: 'Select Grade';
                $class_name = $student['class_name'] ?: 'Curriculum';
                $message = "$student_name registered. Grade: $grade_name. Curriculum: $class_name.";
                $whatsapp_number = '0722407796';
                $digits = preg_replace('/\D+/', '', $whatsapp_number);
                if (strpos($digits, '0') === 0) {
                    $digits = '27' . substr($digits, 1);
                }
                $whatsapp_link = "https://wa.me/$digits?text=" . rawurlencode($message);
            }
        }
        ?>
        <div class="alert alert-success alert-dismissible fade show">
            Student registered successfully!
            <?php if ($whatsapp_link): ?>
                <a href="<?php echo $whatsapp_link; ?>" class="btn btn-success btn-sm ms-2" target="_blank"
                    rel="noopener">
                    <i class="bi bi-whatsapp"></i> Click to WhatsApp
                </a>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Student updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Student deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- SEARCH + FILTERS -->
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search student..."
                    value="<?php echo htmlspecialchars($search_raw); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Grade</label>
                <select name="grade_id" class="form-select">
                    <option value="0">All Grades</option>
                    <?php while ($g = $grades->fetch_assoc()): ?>
                        <option value="<?php echo $g['id']; ?>" <?php echo $grade_id == $g['id'] ? 'selected' : ''; ?>>
                            <?php echo $g['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Curriculum</label>
                <select name="class_id" class="form-select">
                    <option value="0">All Curriculum</option>
                    <?php while ($c = $classes->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $class_id == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo $c['grade_name'] . ' - ' . $c['class_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a class="btn btn-outline-secondary w-100" href="manage_students.php">Clear</a>
            </div>
        </div>
    </form>

    <!-- STUDENTS TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Existing Students</h5>
        </div>
        <div class="card-body">

            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Grade</th>
                        <th>Curriculum</th>
                        <th style="width:150px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($students->num_rows > 0): ?>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>

                                <td>
                                    <?php echo $row['first_name'] . " " . $row['last_name']; ?>
                                </td>

                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td><?php echo $row['grade_name']; ?></td>
                                <td><?php echo $row['class_name']; ?></td>

                                <td>

                                    <a href="view_student.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-warning text-white">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a href="../../actions/student_actions.php?action=delete&id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');">
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No students found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>

        </div>
    </div>

</div>

<?php require_once '../../templates/footer.php'; ?>
