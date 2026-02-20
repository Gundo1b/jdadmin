<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Get Tutor ID
if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">No tutor ID provided.</div>';
    exit;
}

$id = (int) $_GET['id'];
$result = $conn->query("SELECT * FROM tutors WHERE id=$id");

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger">Tutor not found.</div>';
    exit;
}

$tutor = $result->fetch_assoc();

// Load all subjects, classes, and grades
$all_subjects = $conn->query("SELECT s.id, s.subject_name 
                               FROM subjects s 
                               ORDER BY s.subject_name");

$all_classes = $conn->query("SELECT c.id, c.class_name, g.name AS grade_name 
                              FROM classes c 
                              JOIN grades g ON c.grade_id = g.id 
                              ORDER BY g.name, c.class_name");

$all_grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");

// Get current assignments
$current_subjects = [];
$subj_result = $conn->query("SELECT subject_id FROM tutor_subjects WHERE tutor_id=$id");
while ($row = $subj_result->fetch_assoc()) {
    $current_subjects[] = $row['subject_id'];
}

$current_classes = [];
$current_grades = [];
$class_result = $conn->query("SELECT class_id FROM tutor_classes WHERE tutor_id=$id");
while ($row = $class_result->fetch_assoc()) {
    $current_classes[] = $row['class_id'];
}

$grade_res = $conn->query("SELECT grade_id FROM tutor_grades WHERE tutor_id=$id");
while ($gr = $grade_res->fetch_assoc()) {
    $current_grades[] = $gr['grade_id'];
}

// Handle Update
if (isset($_POST['update_tutor'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Update tutor basic info
    $conn->query("UPDATE tutors SET 
                  first_name='$first_name', 
                  last_name='$last_name', 
                  email='$email', 
                  phone='$phone' 
                  WHERE id=$id");

    // Update subject assignments
    $conn->query("DELETE FROM tutor_subjects WHERE tutor_id=$id");
    if (!empty($_POST['subjects'])) {
        foreach ($_POST['subjects'] as $subject_id) {
            $subject_id = (int) $subject_id;
            $conn->query("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES ($id, $subject_id)");
        }
    }

    // Update class assignments
    $conn->query("DELETE FROM tutor_classes WHERE tutor_id=$id");
    if (!empty($_POST['classes'])) {
        foreach ($_POST['classes'] as $class_id) {
            $class_id = (int) $class_id;
            $conn->query("INSERT INTO tutor_classes (tutor_id, class_id) VALUES ($id, $class_id)");
        }
    }

    // Update grade assignments
    $conn->query("DELETE FROM tutor_grades WHERE tutor_id=$id");
    if (!empty($_POST['grades'])) {
        foreach ($_POST['grades'] as $grade_id) {
            $grade_id = (int) $grade_id;
            $conn->query("INSERT INTO tutor_grades (tutor_id, grade_id) VALUES ($id, $grade_id)");
        }
    }

    echo '<div class="alert alert-success">Tutor updated successfully!</div>';
    
    // Refresh data
    $tutor = $conn->query("SELECT * FROM tutors WHERE id=$id")->fetch_assoc();
    
    // Refresh current assignments
    $current_subjects = [];
    $subj_result = $conn->query("SELECT subject_id FROM tutor_subjects WHERE tutor_id=$id");
    while ($row = $subj_result->fetch_assoc()) {
        $current_subjects[] = $row['subject_id'];
    }
    
    $current_classes = [];
    $class_result = $conn->query("SELECT class_id FROM tutor_classes WHERE tutor_id=$id");
    while ($row = $class_result->fetch_assoc()) {
        $current_classes[] = $row['class_id'];
    }

    $current_grades = [];
    $grade_res = $conn->query("SELECT grade_id FROM tutor_grades WHERE tutor_id=$id");
    while ($gr = $grade_res->fetch_assoc()) {
        $current_grades[] = $gr['grade_id'];
    }
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Tutor</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" 
                               value="<?php echo $tutor['first_name']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?php echo $tutor['last_name']; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo $tutor['email']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" 
                               value="<?php echo $tutor['phone']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Assign Subjects</label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background: #fff;">
                            <?php while ($s = $all_subjects->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subjects[]"
                                        value="<?php echo $s['id']; ?>" id="subject_<?php echo $s['id']; ?>"
                                        <?php if (in_array($s['id'], $current_subjects)) echo 'checked'; ?>>
                                    <label class="form-check-label" for="subject_<?php echo $s['id']; ?>">
                                        <?php echo $s['subject_name']; ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Assign Curriculum</label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background: #fff;">
                            <?php while ($c = $all_classes->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="classes[]"
                                        value="<?php echo $c['id']; ?>" id="class_<?php echo $c['id']; ?>"
                                        <?php if (in_array($c['id'], $current_classes)) echo 'checked'; ?>>
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
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background: #fff;">
                            <?php while ($g = $all_grades->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="grades[]"
                                        value="<?php echo $g['id']; ?>" id="grade_<?php echo $g['id']; ?>"
                                        <?php if (in_array($g['id'], $current_grades)) echo 'checked'; ?>>
                                    <label class="form-check-label" for="grade_<?php echo $g['id']; ?>">
                                        <?php echo $g['name']; ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <small class="text-muted">Optional (for reference only)</small>
                    </div>
                </div>

                <button type="submit" name="update_tutor" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Tutor
                </button>
                <a href="add_tutor.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
