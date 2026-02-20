<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Get timetable ID
if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">No timetable ID provided.</div>';
    exit;
}

$id = (int) $_GET['id'];

// Fetch timetable entry
$result = $conn->query("SELECT * FROM timetable WHERE id=$id");
if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger">Timetable entry not found.</div>';
    exit;
}
$t = $result->fetch_assoc();

// Load Classes
$classes = $conn->query("SELECT c.id, c.class_name, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id = g.id 
                         ORDER BY g.name, c.class_name");

// Load Tutors
$tutors = $conn->query("SELECT * FROM tutors ORDER BY first_name");

// Load Subjects
$subjects = $conn->query("SELECT s.id, s.subject_name 
                          FROM subjects s 
                          ORDER BY s.subject_name");

// Handle form submission
if (isset($_POST['update_timetable'])) {
    $class_id = (int) $_POST['class_id'];
    $tutor_id = (int) $_POST['tutor_id'];
    $subject_id = (int) $_POST['subject_id'];
    $day_of_week = $conn->real_escape_string($_POST['day_of_week']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Check for overlapping timetable for same class (excluding current entry)
    $check = $conn->query("
        SELECT * FROM timetable
        WHERE class_id=$class_id 
          AND day_of_week='$day_of_week' 
          AND id != $id
          AND ((start_time <= '$start_time' AND end_time > '$start_time') 
               OR (start_time < '$end_time' AND end_time >= '$end_time'))
    ");

    if ($check->num_rows > 0) {
        echo '<div class="alert alert-danger">This class already has a subject scheduled during this time.</div>';
    } else {
        $conn->query("
            UPDATE timetable 
            SET class_id=$class_id, tutor_id=$tutor_id, subject_id=$subject_id, 
                day_of_week='$day_of_week', start_time='$start_time', end_time='$end_time'
            WHERE id=$id
        ");
        echo '<div class="alert alert-success">Timetable updated successfully!</div>';

        // Refresh the entry
        $result = $conn->query("SELECT * FROM timetable WHERE id=$id");
        $t = $result->fetch_assoc();
    }
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Timetable</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">

                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php while ($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if ($t['class_id'] == $c['id'])
                                       echo 'selected'; ?>>
                                    <?php echo $c['grade_name'] . " - " . $c['class_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tutor</label>
                        <select name="tutor_id" class="form-select" required>
                            <option value="">Select Tutor</option>
                            <?php while ($tu = $tutors->fetch_assoc()): ?>
                                <option value="<?php echo $tu['id']; ?>" <?php if ($t['tutor_id'] == $tu['id'])
                                       echo 'selected'; ?>>
                                    <?php echo $tu['first_name'] . ' ' . $tu['last_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php while ($s = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>" <?php if ($t['subject_id'] == $s['id'])
                                       echo 'selected'; ?>>
                                    <?php echo $s['subject_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>

                <div class="row mb-3">

                    <div class="col-md-4">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-select" required>
                            <option value="">Select Day</option>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            foreach ($days as $day): ?>
                                <option value="<?php echo $day; ?>" <?php if ($t['day_of_week'] == $day)
                                       echo 'selected'; ?>>
                                    <?php echo $day; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control"
                            value="<?php echo $t['start_time']; ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="<?php echo $t['end_time']; ?>"
                            required>
                    </div>

                </div>

                <button type="submit" name="update_timetable" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Timetable
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
