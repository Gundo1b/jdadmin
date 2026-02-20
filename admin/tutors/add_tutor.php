<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Load Grades
$grades_list_query = $conn->query("SELECT * FROM grades ORDER BY name ASC");
$conn->query("CREATE TABLE IF NOT EXISTS tutor_grades (id INT AUTO_INCREMENT PRIMARY KEY, tutor_id INT NOT NULL, grade_id INT NOT NULL)");

// Load Classes
$classes = $conn->query("SELECT c.id, c.class_name, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id=g.id
                         ORDER BY g.name, c.class_name");

// Load Subjects
$subjects = $conn->query("SELECT s.id, s.subject_name 
                          FROM subjects s 
                          ORDER BY s.subject_name");

// Handle form submission
if (isset($_POST['add_tutor'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Insert Tutor
    $conn->query("
        INSERT INTO tutors (first_name, last_name, email, phone)
        VALUES ('$first_name', '$last_name', '$email', '$phone')
    ");
    $tutor_id = $conn->insert_id;

    // Assign Subjects
    if (!empty($_POST['subjects'])) {
        foreach ($_POST['subjects'] as $subject_id) {
            $subject_id = (int) $subject_id;
            $conn->query("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES ($tutor_id, $subject_id)");
        }
    }

    // Assign Classes
    if (!empty($_POST['classes'])) {
        foreach ($_POST['classes'] as $class_id) {
            $class_id = (int) $class_id;
            $conn->query("INSERT INTO tutor_classes (tutor_id, class_id) VALUES ($tutor_id, $class_id)");
        }
    }

    // Assign Grades
    if (!empty($_POST['grades'])) {
        foreach ($_POST['grades'] as $grade_id) {
            $grade_id = (int) $grade_id;
            $conn->query("INSERT INTO tutor_grades (tutor_id, grade_id) VALUES ($tutor_id, $grade_id)");
        }
    }

    echo '<div class="alert alert-success">Tutor added and assigned successfully!</div>';
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-person-badge"></i> Manage Tutors</h3>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addTutorForm"
            aria-expanded="false" aria-controls="addTutorForm">
            <i class="bi bi-plus-lg"></i> Add New Tutor
        </button>
    </div>

    <!-- ADD TUTOR FORM (Hidden by default) -->
    <div class="collapse mb-4" id="addTutorForm">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Add New Tutor</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Assign Subjects</label>
                            <div class="border rounded p-2"
                                style="max-height: 150px; overflow-y: auto; background: #fff;">
                                <?php while ($s = $subjects->fetch_assoc()): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subjects[]"
                                            value="<?php echo $s['id']; ?>" id="subject_<?php echo $s['id']; ?>">
                                        <label class="form-check-label" for="subject_<?php echo $s['id']; ?>">
                                            <?php echo $s['subject_name']; ?>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assign Curriculum</label>
                            <div class="border rounded p-2"
                                style="max-height: 150px; overflow-y: auto; background: #fff;">
                                <?php while ($c = $classes->fetch_assoc()): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="classes[]"
                                            value="<?php echo $c['id']; ?>" id="class_<?php echo $c['id']; ?>">
                                        <label class="form-check-label" for="class_<?php echo $c['id']; ?>">
                                            <?php echo $c['grade_name'] . " - " . $c['class_name']; ?>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <small class="text-muted">Optional</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assign Grades</label>
                            <div class="border rounded p-2"
                                style="max-height: 150px; overflow-y: auto; background: #fff;">
                                <?php
                                while ($g = $grades_list_query->fetch_assoc()): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="grades[]"
                                            value="<?php echo $g['id']; ?>" id="grade_<?php echo $g['id']; ?>">
                                        <label class="form-check-label" for="grade_<?php echo $g['id']; ?>">
                                            <?php echo $g['name']; ?>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <small class="text-muted">Optional</small>
                        </div>
                    </div>

                    <button type="submit" name="add_tutor" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Tutor
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW TUTORS TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Existing Tutors</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subjects</th>
                        <th>Curriculum</th>
                        <th>Grades</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tutors = $conn->query("SELECT * FROM tutors ORDER BY first_name ASC");
                    if ($tutors->num_rows > 0):
                        $i = 1;
                        while ($row = $tutors->fetch_assoc()):
                            $tutor_id = $row['id'];

                            // Fetch assigned subjects
                            $subjects_query = $conn->query("SELECT s.subject_name 
                                                             FROM tutor_subjects ts 
                                                             JOIN subjects s ON ts.subject_id = s.id 
                                                             WHERE ts.tutor_id = $tutor_id");
                            $subjects_list = [];
                            while ($subj = $subjects_query->fetch_assoc()) {
                                $subjects_list[] = $subj['subject_name'];
                            }

                            // Fetch assigned classes
                            $classes_query = $conn->query("SELECT c.class_name, g.name AS grade_name 
                                                           FROM tutor_classes tc 
                                                           JOIN classes c ON tc.class_id = c.id 
                                                           JOIN grades g ON c.grade_id = g.id 
                                                           WHERE tc.tutor_id = $tutor_id");
                            $classes_list = [];
                            while ($cls = $classes_query->fetch_assoc()) {
                                $classes_list[] = $cls['class_name'];
                            }

                            // Fetch assigned grades
                            $tutor_grades_query = $conn->query("SELECT g.name 
                                                                FROM tutor_grades tg 
                                                                JOIN grades g ON tg.grade_id = g.id 
                                                                WHERE tg.tutor_id = $tutor_id");
                            $tg_list = [];
                            while ($tg = $tutor_grades_query->fetch_assoc()) {
                                $tg_list[] = $tg['name'];
                            }
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td>
                                    <?php if (!empty($subjects_list)): ?>
                                        <?php foreach ($subjects_list as $s): ?>
                                            <span class="badge bg-info text-dark mb-1"><?php echo $s; ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($classes_list)): ?>
                                        <?php foreach ($classes_list as $c): ?>
                                            <span class="badge bg-secondary mb-1"><?php echo $c; ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tg_list)): ?>
                                        <?php foreach ($tg_list as $g): ?>
                                            <span class="badge bg-primary mb-1"><?php echo $g; ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No tutors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>