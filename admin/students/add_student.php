<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Load Grades
$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");

// Load Classes
$classes = $conn->query("SELECT c.*, g.name AS grade_name 
                         FROM classes c 
                         JOIN grades g ON c.grade_id = g.id 
                         ORDER BY g.name, c.class_name");

// Success message
$success = isset($_GET['success']) ? true : false;
?>

<div class="container-fluid">

    <h3 class="mb-4"><i class="bi bi-person-plus"></i> Manage Students</h3>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Student added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- ADD STUDENT FORM -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Student</h5>
                </div>
                <div class="card-body">

                    <form action="process_registration.php" method="POST">

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

                            <div class="col-md-6">
                                <label class="form-label">Grade</label>
                                <select name="grade_id" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <?php while ($g = $grades->fetch_assoc()): ?>
                                        <option value="<?php echo $g['id']; ?>">
                                            <?php echo $g['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Curriculum</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">Select Curriculum</option>
                                    <?php while ($c = $classes->fetch_assoc()): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo $c['grade_name'] . ' - ' . $c['class_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Save Student
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- VIEW STUDENTS TABLE -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Existing Students</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Grade</th>
                                <th>Curriculum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $students = $conn->query("SELECT s.*, g.name AS grade_name, c.class_name 
                                                       FROM students s
                                                       LEFT JOIN grades g ON s.grade_id = g.id
                                                       LEFT JOIN classes c ON s.class_id = c.id
                                                       ORDER BY s.id DESC
                                                       LIMIT 10");
                            if ($students->num_rows > 0):
                                $i = 1;
                                while ($row = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                        <td><?php echo $row['grade_name']; ?></td>
                                        <td><?php echo $row['class_name']; ?></td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No students found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="text-end mt-2">
                        <a href="manage_students.php" class="btn btn-sm btn-outline-primary">
                            View All Students <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../templates/footer.php'; ?>
