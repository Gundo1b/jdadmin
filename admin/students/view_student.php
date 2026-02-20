<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

// Get Student ID
if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">No student ID provided.</div>';
    exit;
}

$id = (int) $_GET['id'];
$query = "SELECT s.*, g.name AS grade_name, c.class_name 
          FROM students s
          LEFT JOIN grades g ON s.grade_id = g.id
          LEFT JOIN classes c ON s.class_id = c.id
          WHERE s.id = $id";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit;
}

$student = $result->fetch_assoc();

$subject_names = [];
$subject_query = $conn->query("
    SELECT sub.subject_name
    FROM student_subjects ss
    JOIN subjects sub ON ss.subject_id = sub.id
    WHERE ss.student_id = $id
    ORDER BY sub.subject_name ASC
");
if ($subject_query) {
    while ($subject = $subject_query->fetch_assoc()) {
        $subject_names[] = $subject['subject_name'];
    }
}
?>

<div class="container-fluid">

    <h3 class="mb-4"><i class="bi bi-eye"></i> Student Details</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- TABS -->
            <ul class="nav nav-tabs" id="studentTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                        type="button" role="tab" aria-controls="details" aria-selected="true">
                        <i class="bi bi-person-lines-fill"></i> Student Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kin-tab" data-bs-toggle="tab" data-bs-target="#kin" type="button"
                        role="tab" aria-controls="kin" aria-selected="false">
                        <i class="bi bi-people"></i> Next of Kin
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button"
                        role="tab" aria-controls="docs" aria-selected="false">
                        <i class="bi bi-file-earmark-text"></i> Documents
                    </button>
                </li>
            </ul>

            <!-- TAB CONTENT -->
            <div class="tab-content pt-3" id="studentTabContent">

                <!-- Student Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Personal Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr><th width="180">First Name</th><td>: <?php echo $student['first_name']; ?></td></tr>
                                <tr><th>Last Name</th><td>: <?php echo $student['last_name']; ?></td></tr>
                                <tr><th>Date of Birth</th><td>: <?php echo $student['date_of_birth'] ? date('F j, Y', strtotime($student['date_of_birth'])) : '-'; ?></td></tr>
                                <tr><th>Gender</th><td>: <?php echo $student['gender'] ?: '-'; ?></td></tr>
                                <tr><th>Home Language</th><td>: <?php echo $student['home_language'] ?: '-'; ?></td></tr>
                                <tr><th>Email Address</th><td>: <?php echo $student['email'] ?: '-'; ?></td></tr>
                                <tr><th>Phone Number</th><td>: <?php echo $student['phone'] ?: '-'; ?></td></tr>
                                <tr><th>Physical Address</th><td>: <?php echo $student['physical_address'] ?: '-'; ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Academic Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr><th width="180">Student ID</th><td>: <?php echo $student['id']; ?></td></tr>
                                <tr><th>Grade</th><td>: <?php echo $student['grade_name'] ?: '-'; ?></td></tr>
                                <tr><th>Curriculum</th><td>: <?php echo $student['class_name'] ?: '-'; ?></td></tr>
                                <tr><th>Program</th><td>: <?php echo $student['program'] ?: '-'; ?></td></tr>
                                <tr><th>Learning Mode</th><td>: <?php echo $student['learning_mode'] ?: '-'; ?></td></tr>
                                <tr><th>School Name</th><td>: <?php echo $student['school_name'] ?: '-'; ?></td></tr>
                                <tr>
                                    <th>Subjects</th>
                                    <td>:
                                        <?php if (!empty($subject_names)): ?>
                                            <?php foreach ($subject_names as $subject_name): ?>
                                                <span class="badge bg-info text-dark me-1"><?php echo htmlspecialchars($subject_name); ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mt-4">Additional Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr><th width="180">Referral Source</th><td>: <?php echo $student['referral_source'] ?: '-'; ?></td></tr>
                                <?php if (!empty($student['referral_other'])): ?>
                                    <tr><th>Referral Details</th><td>: <?php echo $student['referral_other']; ?></td></tr>
                                <?php endif; ?>
                                <tr><th>Additional Notes</th><td>: <?php echo $student['additional_notes'] ?: '-'; ?></td></tr>
                                <tr><th>Date Registered</th><td>: <?php echo date('F j, Y', strtotime($student['created_at'])); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Next of Kin Tab -->
                <div class="tab-pane fade" id="kin" role="tabpanel" aria-labelledby="kin-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Next of Kin 1</h5>
                            <table class="table table-sm table-borderless">
                                <tr><th width="180">Full Name</th><td>: <?php echo ($student['kin1_name'] || $student['kin1_surname']) ? $student['kin1_name'] . ' ' . $student['kin1_surname'] : '-'; ?></td></tr>
                                <tr><th>Relation</th><td>: <?php echo $student['kin1_relation'] ?: '-'; ?></td></tr>
                                <tr><th>Personal Contact</th><td>: <?php echo $student['kin1_personal_contact'] ?: '-'; ?></td></tr>
                                <tr><th>Work Contact</th><td>: <?php echo $student['kin1_work_contact'] ?: '-'; ?></td></tr>
                                <tr><th>Email Address</th><td>: <?php echo $student['kin1_email'] ?: '-'; ?></td></tr>
                                <tr><th>Occupation</th><td>: <?php echo $student['kin1_occupation'] ?: '-'; ?></td></tr>
                                <tr><th>Home Language</th><td>: <?php echo $student['kin1_home_language'] ?: '-'; ?></td></tr>
                                <tr><th>Physical Address</th><td>: <?php echo $student['kin1_physical_address'] ?: '-'; ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Next of Kin 2</h5>
                            <?php if (!empty($student['kin2_name'])): ?>
                                <table class="table table-sm table-borderless">
                                    <tr><th width="180">Full Name</th><td>: <?php echo ($student['kin2_name'] || $student['kin2_surname']) ? $student['kin2_name'] . ' ' . $student['kin2_surname'] : '-'; ?></td></tr>
                                    <tr><th>Relation</th><td>: <?php echo $student['kin2_relation'] ?: '-'; ?></td></tr>
                                    <tr><th>Personal Contact</th><td>: <?php echo $student['kin2_personal_contact'] ?: '-'; ?></td></tr>
                                    <tr><th>Work Contact</th><td>: <?php echo $student['kin2_work_contact'] ?: '-'; ?></td></tr>
                                    <tr><th>Email Address</th><td>: <?php echo $student['kin2_email'] ?: '-'; ?></td></tr>
                                    <tr><th>Occupation</th><td>: <?php echo $student['kin2_occupation'] ?: '-'; ?></td></tr>
                                    <tr><th>Home Language</th><td>: <?php echo $student['kin2_home_language'] ?: '-'; ?></td></tr>
                                    <tr><th>Physical Address</th><td>: <?php echo $student['kin2_physical_address'] ?: '-'; ?></td></tr>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No information provided for Next of Kin 2.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div class="tab-pane fade" id="docs" role="tabpanel" aria-labelledby="docs-tab">
                    <h5>Uploaded Documents</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Filename</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Latest School Results</td>
                                <td>
                                    <?php if ($student['latest_results_file']): ?>
                                        <?php echo basename($student['latest_results_file']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['latest_results_file']): ?>
                                        <a href="../../uploads/students/<?php echo basename($student['latest_results_file']); ?>" class="btn btn-sm btn-success" download>
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>ID Document</td>
                                <td>
                                    <?php if ($student['id_document_file']): ?>
                                        <?php echo basename($student['id_document_file']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['id_document_file']): ?>
                                        <a href="../../uploads/students/<?php echo basename($student['id_document_file']); ?>" class="btn btn-sm btn-success" download>
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="mt-4 border-top pt-3">
                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning text-white">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </a>
                <a href="manage_students.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
    </div>

</div>

<?php require_once '../../templates/footer.php'; ?>
