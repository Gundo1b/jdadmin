<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Load Tutors
$tutors = $conn->query("SELECT * FROM tutors ORDER BY first_name");

// Load Subjects
$subjects = $conn->query("SELECT s.id, s.subject_name 
                          FROM subjects s 
                          ORDER BY s.subject_name");

// Handle form submission
if (isset($_POST['add_timetable'])) {
    $tutor_id = (int) $_POST['tutor_id'];
    $subject_id = (int) $_POST['subject_id'];
    $day_of_week = $conn->real_escape_string($_POST['day_of_week']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($start_time >= $end_time) {
        echo '<div class="alert alert-danger">End time must be after start time.</div>';
    } else {
        if ($conn->query("INSERT INTO timetable (tutor_id, subject_id, day_of_week, start_time, end_time)
                          VALUES ($tutor_id, $subject_id, '$day_of_week', '$start_time', '$end_time')")) {
            echo '<div class="alert alert-success">Timetable added successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error saving timetable: ' . $conn->error . '</div>';
        }
    }
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-calendar-plus"></i> Create Timetable</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">

                    <div class="col-md-6">
                        <label class="form-label">Tutor</label>
                        <select name="tutor_id" id="tutor_id" class="form-select" required>
                            <option value="">Select Tutor</option>
                            <?php while ($t = $tutors->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['first_name'] . " " . $t['last_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php while ($s = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>">
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
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                </div>

                <button type="submit" name="add_timetable" class="btn btn-primary">
                    <i class="bi bi-save"></i> Add Timetable
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
