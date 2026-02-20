<?php
require_once '../config/db.php';
require_once '../templates/header.php';
require_once '../templates/sidebar.php';

// Count Students
$students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];

// Count Tutors
$tutors = $conn->query("SELECT COUNT(*) AS total FROM tutors")->fetch_assoc()['total'];

// Count Grades
$grades = $conn->query("SELECT COUNT(*) AS total FROM grades")->fetch_assoc()['total'];

// Count Subjects
$subjects = $conn->query("SELECT COUNT(*) AS total FROM subjects")->fetch_assoc()['total'];
?>

<div class="container-fluid">

    <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>

    <div class="row">

        <!-- STUDENTS -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-people display-5 text-primary"></i>
                    <h3><?php echo $students; ?></h3>
                    <p class="text-muted">Total Students</p>
                </div>
            </div>
        </div>

        <!-- TUTORS -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-person-badge display-5 text-success"></i>
                    <h3><?php echo $tutors; ?></h3>
                    <p class="text-muted">Total Tutors</p>
                </div>
            </div>
        </div>

        <!-- GRADES -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-list-ol display-5 text-info"></i>
                    <h3><?php echo $grades; ?></h3>
                    <p class="text-muted">Grades</p>
                </div>
            </div>
        </div>

        <!-- SUBJECTS -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-journal-bookmark display-5 text-danger"></i>
                    <h3><?php echo $subjects; ?></h3>
                    <p class="text-muted">Subjects</p>
                </div>
            </div>
        </div>

    </div>

    <!-- TODAY'S TIMETABLE -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Today's Timetable - <?php echo date('l, F j, Y'); ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $today = date('l');

                    // NO class join
                    $timetable_query = "SELECT t.*,
                                               s.subject_name,
                                               CONCAT(tu.first_name, ' ', tu.last_name) AS tutor_name
                                        FROM timetable t
                                        JOIN subjects s ON t.subject_id = s.id
                                        JOIN tutors tu ON t.tutor_id = tu.id
                                        WHERE t.day_of_week = '$today'
                                        ORDER BY t.start_time ASC";

                    $timetable = $conn->query($timetable_query);

                    if ($timetable && $timetable->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Subject</th>
                                        <th>Tutor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $timetable->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('g:i A', strtotime($row['start_time'])); ?></strong>
                                                -
                                                <?php echo date('g:i A', strtotime($row['end_time'])); ?>
                                            </td>
                                            <td><span class="badge bg-primary"><?php echo $row['subject_name']; ?></span></td>
                                            <td><?php echo $row['tutor_name']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x display-6"></i>
                            <p class="mt-2">No sessions scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../templates/footer.php'; ?>
