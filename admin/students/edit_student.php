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
$student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit;
}

// Load Programs
$programs = $conn->query("SELECT program_name FROM programs ORDER BY program_name ASC");

// Load Grades
$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");

// Load Classes
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");

// Load Subjects
$subjects = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name ASC");
$current_subjects = [];
$subject_result = $conn->query("SELECT subject_id FROM student_subjects WHERE student_id=$id");
while ($row = $subject_result->fetch_assoc()) {
    $current_subjects[] = (int) $row['subject_id'];
}
?>

<div class="container-fluid">

    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Student</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form action="process_registration.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">

                <h5 class="mb-3">Student Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="studentName" class="form-control"
                            value="<?php echo $student['first_name']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="studentSurname" class="form-control"
                            value="<?php echo $student['last_name']; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label d-block">Gender</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" value="Male"
                                <?php if ($student['gender'] == 'Male') echo 'checked'; ?>>
                            <label class="form-check-label">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" value="Female"
                                <?php if ($student['gender'] == 'Female') echo 'checked'; ?>>
                            <label class="form-check-label">Female</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" value="Other"
                                <?php if ($student['gender'] == 'Other') echo 'checked'; ?>>
                            <label class="form-check-label">Other</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control"
                            value="<?php echo $student['date_of_birth']; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $student['phone']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $student['email']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Home Language</label>
                        <input type="text" name="homeLanguage" class="form-control"
                            value="<?php echo $student['home_language']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Physical Address</label>
                    <textarea name="physicalAddress" class="form-control" rows="2"><?php echo $student['physical_address']; ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Upload Latest Results</label>
                        <input type="file" name="latestResults" class="form-control">
                        <?php if (!empty($student['latest_results_file'])): ?>
                            <small class="text-muted">Current: <?php echo basename($student['latest_results_file']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Upload ID</label>
                        <input type="file" name="idDocument" class="form-control">
                        <?php if (!empty($student['id_document_file'])): ?>
                            <small class="text-muted">Current: <?php echo basename($student['id_document_file']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Academic Information</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program" class="form-select">
                            <option value="">Select Program</option>
                            <?php while ($p = $programs->fetch_assoc()): ?>
                                <option value="<?php echo $p['program_name']; ?>" <?php if ($p['program_name'] == $student['program'])
                                       echo 'selected'; ?>>
                                    <?php echo $p['program_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Grade</label>
                        <select name="grade_id" class="form-select">
                            <option value="">Select Grade</option>
                            <?php while ($g = $grades->fetch_assoc()): ?>
                                <option value="<?php echo $g['id']; ?>" <?php if ($g['id'] == $student['grade_id'])
                                       echo 'selected'; ?>>
                                    <?php echo $g['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Curriculum</label>
                        <select name="class_id" class="form-select">
                            <option value="">Select Curriculum</option>
                            <?php while ($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if ($c['id'] == $student['class_id'])
                                       echo 'selected'; ?>>
                                    <?php echo $c['class_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Learning Mode</label>
                        <select name="learningMode" class="form-select">
                            <option value="">Select Learning Mode</option>
                            <option value="Face to Face" <?php if ($student['learning_mode'] == 'Face to Face')
                                   echo 'selected'; ?>>Face to Face</option>
                            <option value="Online" <?php if ($student['learning_mode'] == 'Online')
                                   echo 'selected'; ?>>Online</option>
                            <option value="Home Tutoring" <?php if ($student['learning_mode'] == 'Home Tutoring')
                                   echo 'selected'; ?>>Home Tutoring</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">School Name</label>
                        <input type="text" name="schoolName" class="form-control"
                            value="<?php echo $student['school_name']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject(s)</label>
                    <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto; background: #fff;">
                        <?php while ($s = $subjects->fetch_assoc()): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subjects[]"
                                    value="<?php echo $s['id']; ?>" id="subject_<?php echo $s['id']; ?>"
                                    <?php if (in_array((int) $s['id'], $current_subjects)) echo 'checked'; ?>>
                                <label class="form-check-label" for="subject_<?php echo $s['id']; ?>">
                                    <?php echo $s['subject_name']; ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Next of Kin 1</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="nextKin1Name" class="form-control"
                            value="<?php echo $student['kin1_name']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Surname</label>
                        <input type="text" name="nextKin1Surname" class="form-control"
                            value="<?php echo $student['kin1_surname']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Personal Contact</label>
                        <input type="text" name="nextKin1PersonalContact" class="form-control"
                            value="<?php echo $student['kin1_personal_contact']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Work Contact</label>
                        <input type="text" name="nextKin1WorkContact" class="form-control"
                            value="<?php echo $student['kin1_work_contact']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="nextKin1Email" class="form-control"
                            value="<?php echo $student['kin1_email']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Relation to Student</label>
                        <input type="text" name="nextKin1RelationToStudent" class="form-control"
                            value="<?php echo $student['kin1_relation']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="nextKin1Occupation" class="form-control"
                            value="<?php echo $student['kin1_occupation']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Home Language</label>
                        <input type="text" name="nextKin1HomeLanguage" class="form-control"
                            value="<?php echo $student['kin1_home_language']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Physical Address</label>
                    <textarea name="nextKin1PhysicalAddress" class="form-control" rows="2"><?php echo $student['kin1_physical_address']; ?></textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Next of Kin 2 (Optional)</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="nextKin2Name" class="form-control"
                            value="<?php echo $student['kin2_name']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Surname</label>
                        <input type="text" name="nextKin2Surname" class="form-control"
                            value="<?php echo $student['kin2_surname']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Personal Contact</label>
                        <input type="text" name="nextKin2PersonalContact" class="form-control"
                            value="<?php echo $student['kin2_personal_contact']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Work Contact</label>
                        <input type="text" name="nextKin2WorkContact" class="form-control"
                            value="<?php echo $student['kin2_work_contact']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="nextKin2Email" class="form-control"
                            value="<?php echo $student['kin2_email']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Relation to Student</label>
                        <input type="text" name="nextKin2RelationToStudent" class="form-control"
                            value="<?php echo $student['kin2_relation']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="nextKin2Occupation" class="form-control"
                            value="<?php echo $student['kin2_occupation']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Home Language</label>
                        <input type="text" name="nextKin2HomeLanguage" class="form-control"
                            value="<?php echo $student['kin2_home_language']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Physical Address</label>
                    <textarea name="nextKin2PhysicalAddress" class="form-control" rows="2"><?php echo $student['kin2_physical_address']; ?></textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Referral</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Referral Source</label>
                        <select name="referralSource" class="form-select">
                            <option value="">Select Source</option>
                            <?php
                            $referrals = ['Google', 'Facebook', 'Friend or Family', 'School', 'Other'];
                            foreach ($referrals as $referral): ?>
                                <option value="<?php echo $referral; ?>" <?php if ($student['referral_source'] == $referral)
                                       echo 'selected'; ?>>
                                    <?php echo $referral; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Referral Other</label>
                        <input type="text" name="referralOther" class="form-control"
                            value="<?php echo $student['referral_other']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="additionalNotes" class="form-control" rows="3"><?php echo $student['additional_notes']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Student
                </button>
                <a href="manage_students.php" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>

</div>

<?php require_once '../../templates/footer.php'; ?>
